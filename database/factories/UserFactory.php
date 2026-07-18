<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 *
 * Produces KUET students: every email ends with @stud.kuet.ac.bd (enforced by
 * the DB CHECK constraint) and the password is stored hashed in password_hash.
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /** Hash once and reuse, so generating many users stays fast. */
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $local = fake()->unique()->userName();

        return [
            'name'          => fake()->name(),
            'email'         => $local . '@stud.kuet.ac.bd',
            'mobile_no'     => '017' . fake()->numerify('########'),
            'password_hash' => static::$password ??= Hash::make('password'),
            'is_admin'      => 0,
        ];
    }

    /** State: give this user a specific plain password (stored hashed). */
    public function password(string $plain): static
    {
        return $this->state(fn (array $attributes) => [
            'password_hash' => Hash::make($plain),
        ]);
    }

    /** State: an administrator. */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_admin' => 1,
        ]);
    }
}
