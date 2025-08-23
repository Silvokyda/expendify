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

       // Default (global) categories available to everyone, with icons
$defaults = [
    ['name'=>'Salary',       'type'=>'income',  'icon'=> 'ph:money'],
    ['name'=>'Freelance',    'type'=>'income',  'icon'=> 'ph:briefcase'],
    ['name'=>'Food',         'type'=>'expense', 'icon'=> 'ph:fork-knife'],
    ['name'=>'Rent',         'type'=>'expense', 'icon'=> 'ph:house'],
    ['name'=>'Transport',    'type'=>'expense', 'icon'=> 'ph:car'],
    ['name'=>'Travel',       'type'=>'expense', 'icon'=> 'ph:airplane'],
    ['name'=>'Utilities',    'type'=>'expense', 'icon'=> 'ph:plug'],
    ['name'=>'Entertainment','type'=>'expense', 'icon'=> 'ph:film-strip'],
    ['name'=>'Health',       'type'=>'expense', 'icon'=> 'ph:heartbeat'],
    ['name'=>'Education',    'type'=>'expense', 'icon'=> 'ph:book'],
    ['name'=>'Emergency Fund','type'=>'saving', 'icon'=> 'ph:piggy-bank'],
];

foreach ($defaults as $cat) {
    Category::firstOrCreate(
        ['user_id' => null, 'name' => $cat['name'], 'type' => $cat['type']],
        ['icon' => $cat['icon']]
    );
}

    }
}
