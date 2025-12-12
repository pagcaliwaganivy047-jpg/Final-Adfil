<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'supplier_id',
        'supplier_name',
        'user_id',
        'type',
        'quantity',
        'purpose',
        'date'
    ];

    // 👇 Automatically load related models every time
    protected $with = ['item', 'supplier', 'user'];

    // This transaction is for an item
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    // Supplier who handled the transaction
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // System user (optional)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
