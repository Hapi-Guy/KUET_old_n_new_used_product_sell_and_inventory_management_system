<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only model mapped to the Oracle view "view_seller_ratings".
 * Provides each seller's average rating and review count.
 */
class ViewSellerRating extends Model
{
    protected $connection = 'oracle';
    protected $table = 'view_seller_ratings';
    protected $primaryKey = 'seller_id';
    protected $keyType = 'int';
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'avg_seller_rating' => 'float',
            'total_reviews'     => 'int',
        ];
    }

    public function save(array $options = [])
    {
        return false;
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
