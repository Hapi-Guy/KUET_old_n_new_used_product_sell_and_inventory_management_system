<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /** Base categories required by the system. */
    public function run(): void
    {
        $categories = [
            'Books', 'Laptop', 'Mobile', 'Electronics',
            'Furniture', 'Cycle', 'Calculator', 'Others',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(['category_name' => $name]);
        }
    }
}
