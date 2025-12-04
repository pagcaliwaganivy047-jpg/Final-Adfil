<?php

namespace App\Http\Controllers\Api;

use App\Models\SupplierRecord;
use App\Http\Controllers\Controller;

class SupplierRecordController extends Controller
{
    public function index()
    {
        return SupplierRecord::with(['supplier', 'item'])->get();
    }
}
