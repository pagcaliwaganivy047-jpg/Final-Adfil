<?php

namespace App\Http\Controllers\Api;

use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SupplierController extends Controller
{
    // List suppliers + allow search
    public function index(Request $request)
    {
        $query = Supplier::withCount('items');

        if ($request->has('search') && $request->search != '') {
            $query->where('supplier_name', 'like', '%'.$request->search.'%');
        }

        return $query->paginate(10);
    }

    // Show single supplier + items + records history
    public function show($id)
    {
        $supplier = Supplier::with([
            'items:id,item_name,item_code,supplier_id,quantity',
            'supplierRecords' => fn($q) =>
                $q->with('item:id,item_name')->orderBy('date','desc')
        ])->findOrFail($id);

        return response()->json($supplier);
    }

    // Create supplier (NOW includes cost_per_unit)
    public function store(Request $request)
    {
        $request->validate([
            'supplier_name' => 'required|string',
            'contact'       => 'nullable|string',
            'email'         => 'nullable|email',
            'cost_per_unit' => 'required|numeric|min:0'   // <---- added
        ]);

        $supplier = Supplier::create($request->only([
            'supplier_name','contact','email','cost_per_unit' // <---- added
        ]));

        return response()->json([
            'message' => 'Supplier created successfully',
            'supplier'=> $supplier
        ]);
    }

    // Update supplier (must include cost_per_unit for price changes)
    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $request->validate([
            'supplier_name' => 'sometimes|string',
            'contact'       => 'nullable|string',
            'email'         => 'nullable|email',
            'cost_per_unit' => 'sometimes|numeric|min:0'  // <---- added
        ]);

        $supplier->update($request->only([
            'supplier_name','contact','email','cost_per_unit' // <---- added
        ]));

        return response()->json(['message' => 'Supplier updated','supplier'=>$supplier]);
    }

    // Delete supplier
    public function destroy($id)
    {
        $supplier = Supplier::withCount(['items','supplierRecords'])->findOrFail($id);

        if($supplier->items_count > 0 || $supplier->supplier_records_count > 0){
            return response()->json([
                'message' => 'Cannot delete supplier — items or records linked'
            ],400);
        }

        $supplier->delete();

        return response()->json(['message'=>'Supplier deleted successfully']);
    }
}
