<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;

    protected $connection = 'oracle';
    protected $table = 'products';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = false;

    public const STATUS_AVAILABLE   = 'AVAILABLE';
    public const STATUS_SOLD        = 'SOLD';
    public const STATUS_UNAVAILABLE = 'UNAVAILABLE';

    public const CONDITION_NEW = 'NEW';
    public const CONDITION_OLD = 'OLD';

    protected $fillable = [
        'seller_id',
        'category_id',
        'title',
        'description',
        'product_condition',
        'min_proposed_price',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'min_proposed_price' => 'decimal:2',
            'created_at'         => 'datetime',
        ];
    }

    /* ----------------------------- Relationships ----------------------------- */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    public function bargains(): HasMany
    {
        return $this->hasMany(Bargain::class, 'product_id');
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class, 'product_id');
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class, 'product_id');
    }

    /* ------------------------------- Helpers -------------------------------- */
    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    public function isSold(): bool
    {
        return $this->status === self::STATUS_SOLD;
    }

    /** First image path, or null. */
    public function primaryImage(): ?string
    {
        return optional($this->images->first())->image_path;
    }

    /* -------------------------------- Scopes -------------------------------- */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }
}
