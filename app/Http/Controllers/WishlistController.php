<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WishlistController extends Controller
{
    public function index(): View
    {
        $items = Wishlist::where('user_id', Auth::id())
            ->with(['product.images', 'product.category', 'product.seller'])
            ->orderByDesc('id')
            ->get();

        return view('wishlist.index', compact('items'));
    }

    /** Manual wishlist add (raw Oracle SQL; respects the user+product unique key). */
    public function store(Product $product): RedirectResponse
    {
        $exists = (int) DB::scalar(
            'SELECT COUNT(*) FROM wishlists WHERE user_id = ? AND product_id = ?',
            [Auth::id(), $product->id]
        ) > 0;

        if (! $exists) {
            // id is filled by the wishlists_bir trigger.
            DB::insert(
                'INSERT INTO wishlists (user_id, product_id) VALUES (?, ?)',
                [Auth::id(), $product->id]
            );
        }

        return back()->with('status', 'Added to your wishlist.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        DB::delete(
            'DELETE FROM wishlists WHERE user_id = ? AND product_id = ?',
            [Auth::id(), $product->id]
        );

        return back()->with('status', 'Removed from your wishlist.');
    }
}
