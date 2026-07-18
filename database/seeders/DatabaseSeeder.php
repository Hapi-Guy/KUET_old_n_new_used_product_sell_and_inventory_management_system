<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Runs on every `php artisan migrate:fresh --seed`. It first guarantees a
     * default admin account and one demo user (both with hashed passwords),
     * then loads the base categories and the sample marketplace data.
     */
    public function run(): void
    {
        // Default accounts -- always available after a fresh seed.
        // NOTE: emails must end with @stud.kuet.ac.bd (DB CHECK constraint).
        User::firstOrCreate(
            ['email' => 'admin@stud.kuet.ac.bd'],
            [
                'name'          => 'System Admin',
                'mobile_no'     => '01700000000',
                'password_hash' => Hash::make('admin1234'),
                'is_admin'      => 1,
            ]
        );

        User::firstOrCreate(
            ['email' => 'user@stud.kuet.ac.bd'],
            [
                'name'          => 'Demo User',
                'mobile_no'     => '01700000009',
                'password_hash' => Hash::make('user1234'),
            ]
        );

        $this->call([
            CategorySeeder::class,
            DemoSeeder::class,
        ]);
    }
}
