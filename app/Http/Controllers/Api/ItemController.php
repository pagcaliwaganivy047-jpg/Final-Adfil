<?php

namespace App\Http\Controllers\Api;

use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\SupplierRecord;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    // List items (for inventory page)
    public function index()
    {
        //return Item::with(['supplier', 'category', 'location'])->get();
        return Item::with(['supplier','category','location'])->paginate(10);
    }

    // Add new item
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

        // Record transaction (item added)
        Transaction::create([
            'item_id'       => $item->id,
            'supplier_id'   => $item->supplier_id,
            'supplier_name' => $item->supplier->supplier_name,
            'user_id'       => Auth::id(),
            'type'          => 'item_added',
            'date'          => now()
        ]);

        return response()->json(['message' => 'Item added successfully', 'item' => $item]);
    }

    // Update item
    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        $request->validate([
            'item_name'   => 'sometimes|required|string',
            'item_code'   => 'sometimes|nullable|string',
            'supplier_id' => 'sometimes|required|exists:suppliers,id',
            'category_id' => 'sometimes|required|exists:categories,id',
            'location_id' => 'sometimes|required|exists:locations,id',
        ]);

        $item->update($request->only([
            'item_name','item_code','supplier_id','category_id','location_id','quantity'
        ]));

        Transaction::create([
            'item_id'       => $item->id,
            'supplier_id'   => $item->supplier_id,
            'supplier_name' => optional($item->supplier)->supplier_name,
            'user_id'       => Auth::id(),
            'type'          => 'item_edited',
            'date'          => now()
        ]);

        return response()->json(['message' => 'Item updated successfully', 'item' => $item]);
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
            'date'          => now()
        ]);

        $item->delete();

        return response()->json(['message' => 'Item deleted successfully']);
    }

    // STOCK IN (integrated into ItemController)
    public function stockIn(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $item = Item::findOrFail($id);

        // Update quantity
        $item->quantity += (int)$request->quantity;
        $item->save();

        // Add supplier record (delivery)
        SupplierRecord::create([
            'supplier_id'       => $item->supplier_id,
            'item_id'           => $item->id,
            'quantity_supplied' => $request->quantity,
            'date'              => now()
        ]);

        // Add transaction
        Transaction::create([
            'item_id'       => $item->id,
            'supplier_id'   => $item->supplier_id,
            'supplier_name' => optional($item->supplier)->supplier_name,
            'user_id'       => Auth::id(),
            'type'          => 'stock_in',
            'quantity'      => $request->quantity,
            'date'          => now()
        ]);

        return response()->json(['message' => 'Stock in successful', 'item' => $item]);
    }

    // STOCK OUT (integrated into ItemController)
    public function stockOut(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'purpose'  => 'required|string'
        ]);

        $item = Item::findOrFail($id);

        if ($item->quantity < $request->quantity) {
            return response()->json(['message' => 'Not enough stock'], 400);
        }

        $item->quantity -= (int)$request->quantity;
        $item->save();

        // Add transaction
        Transaction::create([
            'item_id'       => $item->id,
            'supplier_id'   => $item->supplier_id,
            'supplier_name' => optional($item->supplier)->supplier_name,
            'user_id'       => Auth::id(),
            'type'          => 'stock_out',
            'quantity'      => $request->quantity,
            'purpose'       => $request->purpose,
            'date'          => now()
        ]);

        return response()->json(['message' => 'Stock out successful', 'item' => $item]);
    }
}
