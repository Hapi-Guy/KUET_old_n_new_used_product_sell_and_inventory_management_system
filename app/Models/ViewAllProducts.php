<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only model mapped to the Oracle view "view_all_products".
 * Powers the smart search / filter / sort dashboard.
 */
class ViewAllProducts extends Model
{
    protected $connection = 'oracle';
    protected $table = 'view_all_products';
    protected $primaryKey = 'product_id';
    protected $keyType = 'int';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'min_proposed_price' => 'decimal:2',
            'max_current_bid'    => 'decimal:2',
        ];
    }

    /** Views are read-only. */
    public function save(array $options = [])
    {
        return false;
    }

    public function relatedProduct()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
