<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * User (KUET student).
 *
 * Mapped to the Oracle "users" table. The primary key is fed by the
 * users_id_seq sequence through a BEFORE INSERT trigger, so we keep
 * $incrementing = true and never set the id manually.
 */
class User extends Authenticatable
{
    use HasFactory;

    protected $connection = 'oracle';
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;

    // The table only has created_at (no updated_at), so let the DB default fill it.
    public $timestamps = false;

    protected $fillable = [
        'name',
        'email',
        'password_hash',
        'mobile_no',
        'is_admin',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'is_admin'   => 'boolean',
        ];
    }

    /** Is this user an administrator? Drives the login redirect + admin area. */
    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /* ----------------------------------------------------------------
     | Auth: our password column is "password_hash", not "password".
     | ---------------------------------------------------------------- */
    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    // No remember_token column in this schema -> disable "remember me".
    public function getRememberToken(): ?string
    {
        return null;
    }

    public function setRememberToken($value): void
    {
        // no-op
    }

    public function getRememberTokenName(): ?string
    {
        return null;
    }

    /* ----------------------------------------------------------------
     | Relationships
     | ---------------------------------------------------------------- */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'seller_id');
    }

    public function bargains(): HasMany
    {
        return $this->hasMany(Bargain::class, 'buyer_id');
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class, 'user_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Transaction::class, 'buyer_id');
    }

    /** Ratings this user has written. */
    public function givenRatings(): HasMany
    {
        return $this->hasMany(Rating::class, 'rater_id');
    }

    /** Ratings other users have written about this user. */
    public function receivedRatings(): HasMany
    {
        return $this->hasMany(Rating::class, 'rated_user_id');
    }

    public function reportsMade(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function reportsAgainst(): HasMany
    {
        return $this->hasMany(Report::class, 'reported_id');
    }

    /* ----------------------------------------------------------------
     | Convenience accessors (read from the Oracle views)
     | ---------------------------------------------------------------- */
    public function sellerRating(): ?ViewSellerRating
    {
        return ViewSellerRating::find($this->id);
    }
}
