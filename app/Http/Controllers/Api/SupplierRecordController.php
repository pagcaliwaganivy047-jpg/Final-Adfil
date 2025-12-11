<?php

namespace App\Http\Controllers\Api;

use App\Models\SupplierRecord;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SupplierRecordController extends Controller
{
    // Show list of supply history with filters
    public function index(Request $request)
    {
        $query = SupplierRecord::with([
            'supplier:id,supplier_name',
            'item:id,item_name,item_code'
        ])->orderBy('date', 'desc');

        // Filter by supplier
        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter by item
        if ($request->has('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        // Filter by date range
        if ($request->has('from') && $request->has('to')) {
            $query->whereBetween('date', [$request->from, $request->to]);
        }

        return $query->paginate(10);
    }

    // Show single record detail
    public function show($id)
    {
        return SupplierRecord::with(['supplier', 'item'])->findOrFail($id);
    }
}
