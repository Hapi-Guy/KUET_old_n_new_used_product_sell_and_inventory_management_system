<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Photo management for a product.
 *
 * Allowed for the product's own seller OR any admin. This is what lets an
 * admin add/remove photos on *all* products, while a seller manages only
 * their own listings.
 */
class ProductImageController extends Controller
{
    /** Upload one or more photos to a product. */
    public function store(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeManage($product);

        $request->validate([
            'images'   => ['required', 'array', 'min:1'],
            'images.*' => ['image', 'max:4096'],
        ]);

        foreach ((array) $request->file('images', []) as $image) {
            $path = $image->store('products', 'public');
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $path,
            ]);
        }

        return back()->with('status', 'Photo(s) added to "' . $product->title . '".');
    }

    /** Remove a single photo (and delete the file from disk). */
    public function destroy(Product $product, ProductImage $image): RedirectResponse
    {
        $this->authorizeManage($product);

        // Guard against tampering: the image must belong to this product.
        abort_unless((int) $image->product_id === (int) $product->id, 404);

        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return back()->with('status', 'Photo removed.');
    }

    /** Seller of this product, or any admin, may manage its photos. */
    private function authorizeManage(Product $product): void
    {
        $user = Auth::user();
        abort_unless(
            $user && ($user->isAdmin() || (int) $user->id === (int) $product->seller_id),
            403,
            'You cannot manage photos for this product.'
        );
    }
}
