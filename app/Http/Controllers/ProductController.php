<?php

namespace App\Http\Controllers;

use App\Models\Bargain;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Rating;
use App\Models\Transaction;
use App\Models\ViewAllProducts;
use App\Models\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * "All Products" dashboard with smart search, filter and sort.
     * Reads from the Oracle view `view_all_products` (only AVAILABLE products,
     * pre-joined with category and aggregated max current bid).
     */
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
        // Seller of this product, or any admin, may add/remove its photos.
        $canManagePhotos = $isSeller || Auth::user()->isAdmin();

        // Highest live bid (excludes REJECTED, matching the dashboard's max_current_bid).
        $highestBid = $product->bargains
            ->where('bid_status', '!=', 'REJECTED')
            ->max('bid_amount');
        $myBid = Auth::check()
            ? $product->bargains->firstWhere('buyer_id', Auth::id())
            : null;
        $inWishlist = Auth::check()
            && Wishlist::where('user_id', Auth::id())->where('product_id', $product->id)->exists();

        // The rating this user has already left on this sale (if any), so the
        // form can be replaced with a summary instead of allowing a re-rate.
        $myRating = ($product->transaction && Auth::check())
            ? Rating::where('product_id', $product->id)->where('rater_id', Auth::id())->first()
            : null;

        return view('products.show', compact(
            'product', 'sellerRating', 'isSeller', 'canManagePhotos', 'highestBid', 'myBid', 'inWishlist', 'myRating'
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

    /**
     * Seller manually switches the listing status.
     *  - SOLD      finalises the deal with the currently chosen (ACCEPTED) bid:
     *              it records the transaction and wishlists the other still-open
     *              bidders. Other bids are NOT rejected.
     *  - AVAILABLE reopens the listing (and removes the recorded sale if it was
     *              previously SOLD).
     *  - UNAVAILABLE just hides it from the marketplace.
     */
    public function updateStatus(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeSeller($product);

        $data = $request->validate([
            'status' => ['required', 'in:AVAILABLE,SOLD,UNAVAILABLE'],
        ]);
        $target = $data['status'];

        if ($target === $product->status) {
            return back();
        }

        if ($target === Product::STATUS_SOLD) {
            $accepted = $product->bargains()
                ->where('bid_status', Bargain::STATUS_ACCEPTED)
                ->first();

            if (! $accepted) {
                return back()->withErrors([
                    'status' => 'Choose the winning bid first, then mark the product as Sold.',
                ]);
            }

            // The whole finalisation (record the transaction, mark the product
            // SOLD, and wishlist the passed-over PENDING bidders) is done by the
            // Oracle stored procedure sp_finalize_sale. It does not COMMIT, so we
            // wrap the call in a Laravel transaction to control commit/rollback.
            DB::transaction(function () use ($product) {
                DB::statement('BEGIN sp_finalize_sale(?); END;', [$product->id]);
            });

            return back()->with('status', 'Product marked as Sold to ' . $accepted->buyer->name . '.');
        }

        if ($target === Product::STATUS_AVAILABLE) {
            DB::transaction(function () use ($product) {
                // Reopening a sold item removes the recorded transaction.
                if ($product->isSold()) {
                    DB::delete('DELETE FROM transactions WHERE product_id = ?', [$product->id]);
                }
                DB::update('UPDATE products SET status = ? WHERE id = ?', [Product::STATUS_AVAILABLE, $product->id]);
            });

            return back()->with('status', 'Product is available again.');
        }

        // UNAVAILABLE
        DB::update('UPDATE products SET status = ? WHERE id = ?', [Product::STATUS_UNAVAILABLE, $product->id]);

        return back()->with('status', 'Product marked as unavailable.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorizeSeller($product);

        if ($product->isSold()) {
            return back()->withErrors(['product' => 'A sold product cannot be deleted.']);
        }

        // Raw Oracle DELETE. Child rows (images, bargains, wishlists) are removed
        // by their ON DELETE CASCADE foreign keys.
        DB::delete('DELETE FROM products WHERE id = ?', [$product->id]);

        return redirect()->route('products.mine')->with('status', 'Product removed.');
    }

    private function authorizeSeller(Product $product): void
    {
        abort_unless(Auth::id() === (int) $product->seller_id, 403, 'You do not own this product.');
    }
}
