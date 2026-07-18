<?php

namespace App\Http\Controllers;

use App\Models\Bargain;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BargainController extends Controller
{
    /**
     * A buyer places (or updates) a bid on an available product.
     * A user cannot bid on their own product.
     */
    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_if(Auth::id() === (int) $product->seller_id, 403, 'You cannot bid on your own product.');

        if (! $product->isAvailable()) {
            return back()->withErrors(['bid_amount' => 'This product is no longer available for bidding.']);
        }

        $data = $request->validate([
            'bid_amount' => ['required', 'numeric', 'min:1', 'max:99999999.99'],
        ]);

        // One active bid per buyer per product (re-bidding updates the amount).
        // Raw Oracle SQL: look up any existing bid, then UPDATE it or INSERT a new one.
        $existingId = DB::scalar(
            'SELECT id FROM bargains WHERE product_id = ? AND buyer_id = ?',
            [$product->id, Auth::id()]
        );

        if ($existingId) {
            DB::update(
                'UPDATE bargains SET bid_amount = ?, bid_status = ? WHERE id = ?',
                [$data['bid_amount'], Bargain::STATUS_PENDING, $existingId]
            );
        } else {
            // id is filled by the bargains_bir trigger.
            DB::insert(
                'INSERT INTO bargains (product_id, buyer_id, bid_amount, bid_status) VALUES (?, ?, ?, ?)',
                [$product->id, Auth::id(), $data['bid_amount'], Bargain::STATUS_PENDING]
            );
        }

        return back()->with('status', 'Your bid of ৳' . number_format((float) $data['bid_amount'], 2) . ' has been placed.');
    }

    /**
     * The seller CHOOSES a bidder to deal with. This is deliberately
     * non-destructive: it does NOT sell the product and does NOT reject the
     * other bids. Only one bid is "selected" at a time, so choosing a new
     * bidder simply moves the selection (e.g. if the top bidder turns out to
     * be a scam, the seller shifts to the next one). The sale is finalised
     * separately when the seller manually marks the product SOLD.
     */
    public function accept(Bargain $bargain): RedirectResponse
    {
        $product = $bargain->product;

        abort_unless(Auth::id() === (int) $product->seller_id, 403, 'Only the seller can choose a bid.');

        if (! $product->isAvailable()) {
            return back()->withErrors(['bargain' => 'This product is not open for bidding.']);
        }
        if ($bargain->bid_status === Bargain::STATUS_REJECTED) {
            return back()->withErrors(['bargain' => 'Restore this bid before choosing it.']);
        }

        DB::transaction(function () use ($bargain, $product) {
            // Revert any previously chosen bid: only one selection at a time.
            DB::update(
                'UPDATE bargains SET bid_status = ? WHERE product_id = ? AND id != ? AND bid_status = ?',
                [Bargain::STATUS_PENDING, $product->id, $bargain->id, Bargain::STATUS_ACCEPTED]
            );

            DB::update(
                'UPDATE bargains SET bid_status = ? WHERE id = ?',
                [Bargain::STATUS_ACCEPTED, $bargain->id]
            );
        });

        return back()->with('status',
            'You are now dealing with ' . $bargain->buyer->name .
            '. Other bids stay open — mark the product Sold once the deal is complete.');
    }

    /** Seller rejects a bid (e.g. a suspected scammer). Reversible via reset(). */
    public function reject(Bargain $bargain): RedirectResponse
    {
        $product = $bargain->product;
        abort_unless(Auth::id() === (int) $product->seller_id, 403);

        if ($bargain->bid_status !== Bargain::STATUS_REJECTED) {
            DB::update('UPDATE bargains SET bid_status = ? WHERE id = ?', [Bargain::STATUS_REJECTED, $bargain->id]);
        }

        return back()->with('status', 'Bid rejected.');
    }

    /** Seller sends a bid back to PENDING (undo a choose, or restore a reject). */
    public function reset(Bargain $bargain): RedirectResponse
    {
        $product = $bargain->product;
        abort_unless(Auth::id() === (int) $product->seller_id, 403);

        if ($bargain->bid_status !== Bargain::STATUS_PENDING) {
            DB::update('UPDATE bargains SET bid_status = ? WHERE id = ?', [Bargain::STATUS_PENDING, $bargain->id]);
        }

        return back()->with('status', 'Bid set back to pending.');
    }
}
