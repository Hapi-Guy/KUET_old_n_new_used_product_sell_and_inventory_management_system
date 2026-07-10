<?php

namespace Database\Seeders;

use App\Models\Bargain;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Sample data so the dashboard, bargaining and ratings can be explored
 * immediately. All emails use the mandatory @stud.kuet.ac.bd domain.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        $sellers = [
            ['name' => 'Shomik Shahriar', 'email' => 'shomik@stud.kuet.ac.bd', 'mobile_no' => '01700000001'],
            ['name' => 'Ayesha Rahman',   'email' => 'ayesha@stud.kuet.ac.bd', 'mobile_no' => '01700000002'],
        ];
        $buyers = [
            ['name' => 'Rafiul Islam', 'email' => 'rafiul@stud.kuet.ac.bd', 'mobile_no' => '01700000003'],
            ['name' => 'Nusrat Jahan', 'email' => 'nusrat@stud.kuet.ac.bd', 'mobile_no' => '01700000004'],
        ];

        $createUser = function (array $data) use ($password): User {
            return User::firstOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password_hash' => $password, 'mobile_no' => $data['mobile_no']]
            );
        };

        $sellerModels = array_map($createUser, $sellers);
        $buyerModels  = array_map($createUser, $buyers);

        $catId = fn (string $name) => Category::where('category_name', $name)->value('id');

        $products = [
            ['title' => 'Dell Latitude 5490 (i5, 8GB)', 'cat' => 'Laptop', 'cond' => 'OLD', 'price' => 24000, 'desc' => 'Lightly used office laptop, great for coding.'],
            ['title' => 'Casio fx-991EX Classwiz',      'cat' => 'Calculator', 'cond' => 'NEW', 'price' => 1200, 'desc' => 'Brand new scientific calculator, sealed.'],
            ['title' => 'Data Structures (Tanenbaum)',   'cat' => 'Books', 'cond' => 'OLD', 'price' => 350, 'desc' => 'Good condition, minimal highlights.'],
            ['title' => 'Phoenix Hercules Cycle',        'cat' => 'Cycle', 'cond' => 'OLD', 'price' => 5500, 'desc' => 'Single speed, recently serviced.'],
            ['title' => 'Redmi Note 12 (128GB)',         'cat' => 'Mobile', 'cond' => 'OLD', 'price' => 14000, 'desc' => 'No scratches, with box and charger.'],
        ];

        foreach ($products as $i => $p) {
            $seller = $sellerModels[$i % count($sellerModels)];

            $product = Product::firstOrCreate(
                ['title' => $p['title'], 'seller_id' => $seller->id],
                [
                    'category_id'        => $catId($p['cat']),
                    'description'        => $p['desc'],
                    'product_condition'  => $p['cond'],
                    'min_proposed_price' => $p['price'],
                    'status'             => Product::STATUS_AVAILABLE,
                ]
            );

            // A couple of pending bids per product from the buyers.
            foreach ($buyerModels as $j => $buyer) {
                Bargain::firstOrCreate(
                    ['product_id' => $product->id, 'buyer_id' => $buyer->id],
                    [
                        'bid_amount' => $p['price'] + (($j + 1) * 250),
                        'bid_status' => Bargain::STATUS_PENDING,
                    ]
                );
            }
        }
    }
}
