<?php

namespace App\Http\Controllers\Api;

use App\Models\Item;
use App\Models\Transaction;
use App\Models\SupplierRecord;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    // Get items with relationships
    public function index()
    {
        return Item::with(['supplier','category','location'])->paginate(10);
    }

    // Create new item
    public function store(Request $request)
    {
        $request->validate([
            'item_name'   => 'required|string',
            'supplier_id' => 'required|exists:suppliers,id',
            'category_id' => 'required|exists:categories,id',
            'location_id' => 'required|exists:locations,id'
        ]);

        $item = Item::create([
            'item_name'   => $request->item_name,
            'item_code'   => $request->item_code,
            'supplier_id' => $request->supplier_id,
            'category_id' => $request->category_id,
            'location_id' => $request->location_id,
            'quantity'    => 0
        ]);

        // Log transaction
        Transaction::create([
            'item_id'       => $item->id,
            'supplier_id'   => $item->supplier_id,
            'supplier_name' => $item->supplier->supplier_name,
            'user_id'       => Auth::id(),
            'type'          => 'item_added',
            'date'          => now(),
        ]);

        return response()->json(['message' => 'Item successfully added', 'item' => $item]);
    }

    // Update item
    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        $item->update($request->only([
            'item_name','item_code','supplier_id','category_id','location_id','quantity'
        ]));

        Transaction::create([
            'item_id'       => $item->id,
            'supplier_id'   => $item->supplier_id,
            'supplier_name' => optional($item->supplier)->supplier_name,
            'user_id'       => Auth::id(),
            'type'          => 'item_edited',
            'date'          => now(),
        ]);

        return response()->json(['message' => 'Item updated successfully','item'=>$item]);
    }

    // Delete item
    public function destroy($id)
    {
        $item = Item::findOrFail($id);

        Transaction::create([
            'item_id'       => $item->id,
            'supplier_id'   => $item->supplier_id,
            'supplier_name' => optional($item->supplier)->supplier_name,
            'user_id'       => Auth::id(),
            'type'          => 'item_deleted',
            'date'          => now(),
        ]);

        $item->delete();

        return response()->json(['message' => 'Item deleted successfully']);
    }

    // Stock In — also adds supplier record
    public function stockIn(Request $request, $id)
    {
         $request->validate([
        'quantity' => 'required|integer|min:1'
         ]);

        $item = Item::findOrFail($id);
        $supplier = $item->supplier;
        
        if(!$supplier->cost_per_unit){
        return response()->json(['message' => 'Supplier has no cost per unit. Please set price first.'], 400);
        }


        // compute total cost
        $totalCost = $request->quantity * ($supplier->cost_per_unit ?? 0);

        // update inventory quantity
        $item->quantity += $request->quantity;
        $item->save();

        // save supplier record
        SupplierRecord::create([
            'supplier_id'       => $supplier->id,
            'item_id'           => $item->id,
            'quantity_supplied' => $request->quantity,
            'cost_per_unit'     => $supplier->cost_per_unit, // snapshot
            'total_cost'        => $totalCost,
            'date'              => now()
        ]);

        // create transaction
        Transaction::create([
            'item_id'       => $item->id,
            'supplier_id'   => $supplier->id,
            'supplier_name' => $supplier->supplier_name,
            'user_id'       => Auth::id(),
            'type'          => 'stock_in',
            'quantity'      => $request->quantity,
            'date'          => now()
        ]);

    return response()->json(['message' => 'Stock in successful', 'item' => $item]);
    }

    // Stock Out — requires a reason
    public function stockOut(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'purpose'  => 'required|string'
        ]);

        $item = Item::findOrFail($id);

        if($item->quantity < $request->quantity){
            return response()->json(['message'=>'Insufficient stock'],400);
        }

        $item->quantity -= $request->quantity;
        $item->save();

        Transaction::create([
            'item_id'       => $item->id,
            'supplier_id'   => $item->supplier_id,
            'supplier_name' => optional($item->supplier)->supplier_name,
            'user_id'       => Auth::id(),
            'type'          => 'stock_out',
            'quantity'      => $request->quantity,
            'purpose'       => $request->purpose,
            'date'          => now(),
        ]);

        return response()->json(['message'=>'Stock out successful','item'=>$item]);
    }
}
