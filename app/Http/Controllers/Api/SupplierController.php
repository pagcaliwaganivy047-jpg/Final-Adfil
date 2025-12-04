<?php

namespace App\Http\Controllers\Api;

use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SupplierController extends Controller
{
    // List suppliers (for supplier listing)
    public function index()
    {
        return Supplier::all();
    }

    // Show supplier with items and supplier records (click -> records)
    public function show($id)
    {
        $supplier = Supplier::with([
            'items' => function($q) {
                $q->select('id','item_name','item_code','supplier_id','quantity');
            },
            'supplierRecords' => function($q) {
                $q->with('item:id,item_name')->orderBy('date', 'desc');
            }
        ])->findOrFail($id);

        return $supplier;
    }

    // Create supplier
    public function store(Request $request)
    {
        $request->validate([
            'supplier_name' => 'required|string',
            'contact'       => 'nullable|string',
            'email'         => 'nullable|email'
        ]);

        $supplier = Supplier::create($request->only(['supplier_name','contact','email']));

        return response()->json(['message' => 'Supplier created', 'supplier' => $supplier]);
    }

    // Update supplier
    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $supplier->update($request->only(['supplier_name','contact','email']));

        return response()->json(['message' => 'Supplier updated', 'supplier' => $supplier]);
    }

    // Delete supplier
    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        return response()->json(['message' => 'Supplier deleted']);
    }
}
