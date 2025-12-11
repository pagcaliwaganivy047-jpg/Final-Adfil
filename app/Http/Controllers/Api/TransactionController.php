<?php

namespace App\Http\Controllers\Api;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TransactionController extends Controller
{
    // List transactions with filtering
    public function index(Request $request)
    {
        $query = Transaction::orderBy('date', 'desc');

        // Filter by type (item_added, stock_in, stock_out, item_deleted, etc.)
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by Item
        if ($request->has('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        // Filter by Supplier
        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter by User (handled_by)
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Date range filter
        if ($request->has('from') && $request->has('to')) {
            $query->whereBetween('date', [$request->from, $request->to]);
        }

        return $query->paginate(10);
    }

    // Single transaction detail
    public function show($id)
    {
        return Transaction::with(['item', 'supplier', 'user'])->findOrFail($id);
    }
}
