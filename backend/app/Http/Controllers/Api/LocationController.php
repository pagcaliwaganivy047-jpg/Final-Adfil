<?php

namespace App\Http\Controllers\Api;

use App\Models\Location;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LocationController extends Controller
{
    public function index()
    {
        return Location::all();
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string']);
        $location = Location::create(['name' => $request->name]);
        return response()->json(['message' => 'Location created', 'location' => $location]);
    }

    public function update(Request $request, $id)
    {
        $location = Location::findOrFail($id);
        $request->validate(['name' => 'required|string']);
        $location->update(['name' => $request->name]);
        return response()->json(['message' => 'Location updated', 'location' => $location]);
    }

    public function destroy($id)
    {
        $location = Location::findOrFail($id);
        $location->delete();
        return response()->json(['message' => 'Location deleted']);
    }
}
