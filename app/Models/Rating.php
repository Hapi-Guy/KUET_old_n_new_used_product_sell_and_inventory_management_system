<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    protected $connection = 'oracle';
    protected $table = 'ratings';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = false;

    public const TYPE_BUYER  = 'BUYER_RATING';
    public const TYPE_SELLER = 'SELLER_RATING';

    protected $fillable = [
        'product_id',
        'rater_id',
        'rated_user_id',
        'rating_type',
        'rating_value',
        'review_text',
    ];

    protected function casts(): array
    {
        return [
            'rating_value' => 'float',
            'created_at'   => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function rater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rater_id');
    }

    public function ratedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rated_user_id');
    }
}
