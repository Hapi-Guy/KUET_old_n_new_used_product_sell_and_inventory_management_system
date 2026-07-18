<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TransactionController extends Controller
{
    /** "My deals": products I bought and products I sold. */
    public function index(): View
    {
        $me = Auth::id();

        $purchases = Transaction::where('buyer_id', $me)
            ->with(['product.seller', 'product.category', 'product.images'])
            ->orderByDesc('id')
            ->get();

        $sales = Transaction::whereHas('product', fn ($q) => $q->where('seller_id', $me))
            ->with(['product.category', 'product.images', 'buyer'])
            ->orderByDesc('id')
            ->get();

        return view('transactions.index', compact('purchases', 'sales'));
    }
}
