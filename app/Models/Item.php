<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_name',
        'item_code',
        'supplier_id',
        'category_id',
        'location_id',
        'quantity'
    ];

    // Item belongs to a supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Item belongs to a category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Item belongs to a location
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    // Item has many transactions
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // Item has many supplier records
    public function supplierRecords()
    {
        return $this->hasMany(SupplierRecord::class);
    }
}
