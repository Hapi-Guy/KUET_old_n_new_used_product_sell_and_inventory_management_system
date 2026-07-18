<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RerdirectResponse;
use Illuminate\Contracts\View\View;
use app\Models\Product;
use app\Models\Wishlist;


class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search    = trim((string) $request->query('q', ''));
        $category  = $request->query('category');
        $condition = $request->query('condition');
        $sort      = $request->query('sort', 'latest');

        $query = ViewAllProducts::query();

        if ($search !== '') {
            // Case-insensitive title search.
            $query->whereRaw('UPPER(title) LIKE ?', ['%' . strtoupper($search) . '%']);
        }
        if ($category) {
            $query->where('category_name', $category);
        }
        if (in_array($condition, [Product::CONDITION_NEW, Product::CONDITION_OLD], true)) {
            $query->where('product_condition', $condition);
        }

        match ($sort) {
            'price_low'  => $query->orderBy('min_proposed_price', 'asc'),
            'price_high' => $query->orderBy('min_proposed_price', 'desc'),
            'bid_high'   => $query->orderBy('max_current_bid', 'desc'),
            'condition'  => $query->orderBy('product_condition', 'asc')->orderBy('product_id', 'desc'),
            default      => $query->orderBy('product_id', 'desc'), // latest
        };

        $rows = $query->paginate(9)->withQueryString();

        // Attach a thumbnail for each listed product (view has no image column).
        $ids = collect($rows->items())->pluck('product_id')->all();
        $thumbs = $ids
            ? ProductImage::whereIn('product_id', $ids)
                ->get()
                ->groupBy('product_id')
                ->map(fn ($g) => $g->first()->image_path)
            : collect();

        $wishlisted = Auth::check()
            ? Wishlist::where('user_id', Auth::id())->pluck('product_id')->all()
            : [];

        return view('products.index', [
            'rows'        => $rows,
            'thumbs'      => $thumbs,
            'categories'  => Category::orderBy('category_name')->get(),
            'filters'     => compact('search', 'category', 'condition', 'sort'),
            'wishlisted'  => $wishlisted,
        ]);
    }


    public function show(Product $product): View
    {
        $product->load([
            'seller', 'category', 'images',
            'bargains' => fn ($q) => $q->orderBy('bid_amount', 'desc'),
            'bargains.buyer',
            'transaction.buyer',
        ]);

        $sellerRating = $product->seller->sellerRating();

        $isSeller = Auth::id() === (int) $product->seller_id;
        $myBid = Auth::check()
            ? $product->bargains->firstWhere('buyer_id', Auth::id())
            : null;
        $inWishlist = Auth::check()
            && Wishlist::where('user_id', Auth::id())->where('product_id', $product->id)->exists();

        return view('products.show', compact(
            'product', 'sellerRating', 'isSeller', 'myBid', 'inWishlist'
        ));
    }

    public function myProducts(): View
    {
        $products = Product::where('seller_id', Auth::id())
            ->withCount(['bargains as pending_bids_count' => fn ($q) => $q->where('bid_status', 'PENDING')])
            ->with(['category', 'images', 'transaction'])
            ->orderByDesc('id')
            ->get();

        return view('products.mine', compact('products'));
    }

    public function create(): View
    {
        return view('products.create', [
            'categories' => Category::orderBy('category_name')->get(),
        ]);
    }
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'              => ['required', 'string', 'max:150'],
            'category_id'        => ['required', 'integer', 'exists:categories,id'],
            'description'        => ['nullable', 'string', 'max:1000'],
            'product_condition'  => ['required', 'in:NEW,OLD'],
            'min_proposed_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'images.*'           => ['nullable', 'image', 'max:4096'],
        ]);

        $product = DB::transaction(function () use ($data, $request) {
            $product = Product::create([
                'seller_id'          => Auth::id(),
                'category_id'        => $data['category_id'],
                'title'              => $data['title'],
                'description'        => $data['description'] ?? null,
                'product_condition'  => $data['product_condition'],
                'min_proposed_price' => $data['min_proposed_price'],
                'status'             => Product::STATUS_AVAILABLE,
            ]);

            foreach ((array) $request->file('images', []) as $image) {
                $path = $image->store('products', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                ]);
            }

            return $product;
        });

        return redirect()->route('products.show', $product)
            ->with('status', 'Your product has been listed.');
    }

    public function edit(Product $product): View
    {
        $this->authorizeSeller($product);

        return view('products.edit', [
            'product'    => $product->load('images'),
            'categories' => Category::orderBy('category_name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeSeller($product);

        $data = $request->validate([
            'title'              => ['required', 'string', 'max:150'],
            'category_id'        => ['required', 'integer', 'exists:categories,id'],
            'description'        => ['nullable', 'string', 'max:1000'],
            'product_condition'  => ['required', 'in:NEW,OLD'],
            'min_proposed_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'status'             => ['required', 'in:AVAILABLE,UNAVAILABLE'],
            'images.*'           => ['nullable', 'image', 'max:4096'],
        ]);

        DB::transaction(function () use ($data, $request, $product) {
            $product->update($data);

            foreach ((array) $request->file('images', []) as $image) {
                $path = $image->store('products', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                ]);
            }
        });

        return redirect()->route('products.show', $product)
            ->with('status', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorizeSeller($product);

        if ($product->isSold()) {
            return back()->withErrors(['product' => 'A sold product cannot be deleted.']);
        }

        $product->delete();

        return redirect()->route('products.mine')->with('status', 'Product removed.');
    }
    private function authorizeSeller(Product $product): void
    {
        if (Auth::id() !== (int) $product->seller_id) {
            abort(403, 'You are not authorized to perform this action.');
        }
    }
}
