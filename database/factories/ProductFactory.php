<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 *
 * If seller_id / category_id are not supplied, a related User / Category is
 * generated automatically via their own factories.
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'seller_id'          => User::factory(),
            'category_id'        => Category::factory(),
            'title'              => ucfirst(fake()->words(3, true)),
            'description'        => fake()->sentence(12),
            'product_condition'  => fake()->randomElement([Product::CONDITION_NEW, Product::CONDITION_OLD]),
            'min_proposed_price' => fake()->numberBetween(100, 50000),
            'status'             => Product::STATUS_AVAILABLE,
        ];
    }

    /** State: a sold product. */
    public function sold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Product::STATUS_SOLD,
        ]);
    }
}
