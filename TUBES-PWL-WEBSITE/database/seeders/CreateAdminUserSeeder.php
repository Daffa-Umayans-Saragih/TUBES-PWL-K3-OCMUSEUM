<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@museum.local'],
            [
                'password' => Hash::make('admin123456'),
                'is_admin' => true,
            ]
        );

        echo "Admin user created successfully!\n";
        echo "Email: admin@museum.local\n";
        echo "Password: admin123456\n";
    }
}
