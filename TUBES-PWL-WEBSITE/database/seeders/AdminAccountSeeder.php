<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\PostalCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminAccountSeeder extends Seeder
{
    public function run(): void
    {
        $postalCode = PostalCode::firstOrCreate([
            'postal_code'    => '10028',
            'postal_city'    => 'New York',
            'postal_state'   => 'NY',
            'postal_country' => 'United States',
        ]);

        $accounts = [
            [
                'email' => 'superadmin@gmail.com',
                'password' => 'superadmin123',
                'role_admin' => 'superadmin',
                'first_name' => 'Super',
                'last_name' => 'Admin',
            ],
            [
                'email' => 'admin@gmail.com',
                'password' => 'admin123',
                'role_admin' => 'admin',
                'first_name' => 'System',
                'last_name' => 'Admin',
            ],
            [
                'email' => 'cashier@gmail.com',
                'password' => 'cashier123',
                'role_admin' => 'cashier',
                'first_name' => 'Front',
                'last_name' => 'Cashier',
            ],
        ];

        foreach ($accounts as $account) {
            $user = User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'password' => Hash::make($account['password']),
                    'is_admin' => true, // Retained for backward compatibility temporarily
                    'role_admin' => $account['role_admin'],
                ]
            );

            UserProfile::updateOrCreate(
                ['user_id' => $user->user_id],
                [
                    'first_name'     => $account['first_name'],
                    'last_name'      => $account['last_name'],
                    'phone_number'   => '+1 (555) 000-0000',
                    'address1'       => 'MET Office',
                    'postal_code_id' => $postalCode->postal_code_id,
                ]
            );
        }
    }
}
