<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Rating;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RatingController extends Controller
{
    /**
     * Trusted rating after a transaction. The seller rates the buyer and the
     * buyer rates the seller (1-5 stars). Self-rating is impossible here and
     * is also blocked by the chk_not_self_rating DB constraint.
     */
    public function store(Request $request, Product $product): RedirectResponse
    {
        $product->loadMissing('transaction');

        abort_unless($product->transaction, 403, 'You can only rate after the sale is completed.');

        $sellerId = (int) $product->seller_id;
        $buyerId  = (int) $product->transaction->buyer_id;
        $me       = (int) Auth::id();

        if ($me === $sellerId) {
            $ratedUserId = $buyerId;
            $type        = Rating::TYPE_BUYER;   // seller rates the buyer
        } elseif ($me === $buyerId) {
            $ratedUserId = $sellerId;
            $type        = Rating::TYPE_SELLER;  // buyer rates the seller
        } else {
            abort(403, 'Only the buyer or seller of this product can leave a rating.');
        }

        if ($me === $ratedUserId) {
            return back()->withErrors(['rating_value' => 'You cannot rate yourself.']);
        }

        $alreadyRated = (int) DB::scalar(
            'SELECT COUNT(*) FROM ratings WHERE product_id = ? AND rater_id = ? AND rating_type = ?',
            [$product->id, $me, $type]
        ) > 0;

        if ($alreadyRated) {
            return back()->withErrors(['rating_value' => 'You have already submitted this rating.']);
        }

        $data = $request->validate([
            'rating_value' => ['required', 'integer', 'between:1,5'],
            'review_text'  => ['nullable', 'string', 'max:500'],
        ]);

        // Raw Oracle INSERT; the ratings_bir trigger fills the id from the sequence.
        DB::insert(
            'INSERT INTO ratings (product_id, rater_id, rated_user_id, rating_type, rating_value, review_text)
             VALUES (?, ?, ?, ?, ?, ?)',
            [$product->id, $me, $ratedUserId, $type, $data['rating_value'], $data['review_text'] ?? null]
        );

        return back()->with('status', 'Thank you! Your rating has been recorded.');
    }
}
