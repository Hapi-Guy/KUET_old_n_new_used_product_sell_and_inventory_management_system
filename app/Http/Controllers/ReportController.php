<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /** Report a malicious buyer/seller or a fake product. */
    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'reported_id' => ['required', 'integer', 'exists:users,id'],
            'report_type' => ['required', 'in:BUYER,SELLER'],
            'reason'      => ['required', 'string', 'max:500'],
        ]);

        if ((int) $data['reported_id'] === (int) Auth::id()) {
            return back()->withErrors(['reason' => 'You cannot report yourself.']);
        }

        // Raw Oracle INSERT; the reports_bir trigger fills the id from the sequence.
        DB::insert(
            'INSERT INTO reports (reporter_id, reported_id, product_id, report_type, reason)
             VALUES (?, ?, ?, ?, ?)',
            [Auth::id(), $data['reported_id'], $product->id, $data['report_type'], $data['reason']]
        );

        return back()->with('status', 'Your report has been submitted to the administrators.');
    }
}
