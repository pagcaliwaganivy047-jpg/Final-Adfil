<?php

use App\Models\Item;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\SupplierRecordController;


// ----------------- AUTH -----------------
Route::post('/login', [AuthController::class, 'login']);

// All protected routes need token
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);

    // ----------------- ITEMS -----------------
    Route::get('/items', [ItemController::class, 'index']);          
    Route::post('/items', [ItemController::class, 'store']);         
    Route::put('/items/{id}', [ItemController::class, 'update']);    
    Route::delete('/items/{id}', [ItemController::class, 'destroy']); 

    // Stock In / Out
    Route::post('/items/{id}/stock-in', [ItemController::class, 'stockIn']);
    Route::post('/items/{id}/stock-out', [ItemController::class, 'stockOut']);

    // ----------------- CATEGORIES -----------------
    Route::get('/categories', [CategoryController::class, 'index']);    
    Route::post('/categories', [CategoryController::class, 'store']);   
    Route::put('/categories/{id}', [CategoryController::class, 'update']); 
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']); 

    // ----------------- LOCATIONS -----------------
    Route::get('/locations', [LocationController::class, 'index']);    
    Route::post('/locations', [LocationController::class, 'store']);   
    Route::put('/locations/{id}', [LocationController::class, 'update']); 
    Route::delete('/locations/{id}', [LocationController::class, 'destroy']); 

    // ----------------- SUPPLIERS -----------------
    Route::get('/suppliers', [SupplierController::class, 'index']);           
    Route::post('/suppliers', [SupplierController::class, 'store']);          
    Route::get('/suppliers/{id}', [SupplierController::class, 'show']);       
    Route::put('/suppliers/{id}', [SupplierController::class, 'update']);     
    Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy']); 

    // ----------------- TRANSACTIONS -----------------
    Route::get('/transactions', [TransactionController::class, 'index']);     

    // ----------------- SUPPLIER RECORDS -----------------
    Route::get('/supplier-records', [SupplierRecordController::class, 'index']);

});
