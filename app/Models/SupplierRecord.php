<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SupplierRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'item_id',
        'quantity_supplied',
        'cost_per_unit',   // store historical cost!
        'total_cost',  // ADDED

        'date'
    ];

    // protected $appends = ['total_cost']; // Auto-show in API JSON

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    // Auto computed total cost (quantity * supplier.cost_per_unit)
    // public function getTotalCostAttribute()
    // {
    //     return $this->quantity_supplied * ($this->supplier->cost_per_unit ?? 0);
    // }
}
