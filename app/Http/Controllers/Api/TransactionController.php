<?php

namespace App\Http\Controllers\Api;

use App\Models\Transaction;
use App\Http\Controllers\Controller;

class TransactionController extends Controller
{
    public function index()
    {
        // return Transaction::with(['item', 'supplier', 'user'])
        //     ->orderBy('date', 'desc')
        //     ->get();

        return Transaction::orderBy('date','desc')->get();
    }
}
