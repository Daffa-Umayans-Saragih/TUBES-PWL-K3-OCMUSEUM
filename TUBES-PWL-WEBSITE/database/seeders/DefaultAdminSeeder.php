<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'password' => Hash::make('admin123'),
                'is_admin' => true,
            ]
        );

        $postalCode = \App\Models\PostalCode::firstOrCreate([
            'postal_code'    => '10028',
            'postal_city'    => 'New York',
            'postal_state'   => 'NY',
            'postal_country' => 'United States',
        ]);

        UserProfile::updateOrCreate(
            ['user_id' => $user->user_id],
            [
                'first_name'     => 'System',
                'last_name'      => 'Admin',
                'phone_number'   => '+1 (555) 000-0000',
                'address1'       => 'MET Admin Office',
                'postal_code_id' => $postalCode->postal_code_id,
            ]
        );
    }
}
