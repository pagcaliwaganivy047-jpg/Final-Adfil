<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_name',
        'contact',
        'email',
        'item_supplied',
        'cost_per_unit', // <— new
    ];

    // Supplier supplies many items
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    // Supplier has many supplier records
    public function supplierRecords()

    {
        return $this->hasMany(SupplierRecord::class);
    }

    // Supplier is involved in many transactions
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
