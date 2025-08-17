<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create main user
        $user = User::firstOrCreate(
            ['email' => 'silvansowino1@gmail.com'],
            [
                'name' => 'Silvanus Owino',
                'phone' => '254703527474',
                'password' => Hash::make('4rever2moro'),
                'email_verified_at' => now(),
            ]
        );

        // Default categories
        $categories = [
            ['name'=>'Salary','type'=>'income'],
            ['name'=>'Freelance','type'=>'income'],
            ['name'=>'Food','type'=>'expense'],
            ['name'=>'Rent','type'=>'expense'],
            ['name'=>'Transport','type'=>'expense'],
            ['name'=>'Travel','type'=>'expense'],
            ['name'=>'Utilities','type'=>'expense'],
            ['name'=>'Entertainment','type'=>'expense'],
            ['name'=>'Health','type'=>'expense'],
            ['name'=>'Education','type'=>'expense'],
            ['name'=>'Misc','type'=>'expense'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['user_id' => $user->id, 'name' => $cat['name'], 'type' => $cat['type']]
            );
        }
    }
}
