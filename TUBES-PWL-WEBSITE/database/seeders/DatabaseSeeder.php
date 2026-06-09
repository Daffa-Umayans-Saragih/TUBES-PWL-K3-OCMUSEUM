<?php
namespace Database\Seeders;

use App\Models\PostalCode;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $postalCode = PostalCode::firstOrCreate([
            'postal_code'    => '10028',
            'postal_city'    => 'New York',
            'postal_state'   => 'NY',
            'postal_country' => 'United States',
        ]);

        // Create corresponding user profile
        UserProfile::create([
            'user_id'        => $user->user_id,
            'first_name'     => 'Test',
            'last_name'      => 'User',
            'phone_number'   => '+1 (555) 123-4567',
            'address1'       => '1000 5th Avenue',
            'address2'       => null,
            'postal_code_id' => $postalCode->postal_code_id,
        ]);

        // Uncomment to seed multiple users with profiles
        // User::factory(10)->create()->each(function (User $user) {
        //     UserProfile::create([
        //         'user_id' => $user->user_id,
        //         'first_name' => fake()->firstName(),
        //         'last_name' => fake()->lastName(),
        //         'phone_number' => fake()->phoneNumber(),
        //         'address1' => fake()->streetAddress(),
        //         'address2' => fake()->secondaryAddress(),
        //         'postal_code_id' => PostalCode::firstOrCreate([
        //             'postal_code' => fake()->postcode(),
        //             'postal_city' => fake()->city(),
        //             'postal_state' => fake()->state(),
        //             'postal_country' => 'United States',
        //         ])->postal_code_id,
        //     ]);
        // });
        $this->call([
            AdminAccountSeeder::class,
            CategorySeeder::class,
            TicketSystemSeeder::class,
            DepartmentSeeder::class,
            GeographySeeder::class,
            MaterialSeeder::class,
            ClassificationSeeder::class,
            ObjectTypeSeeder::class,
            SearchFieldSeeder::class,
            SortOptionSeeder::class,
            ShowOnlySeeder::class,
            Department190Seeder::class,             // [NEW] Ingests foundational 190 departmental artworks base
            DepartmentDescriptionSeeder::class,     // [NEW] Ingests foundational 190 artwork descriptions
            MetMuseumData1000PipelineSeeder::class, // [NEW] Strict 1000 base + JSON enrichment pipeline
            CuratedMetMuseumSeeder::class,          // Must run first — creates art_works rows
            MetMuseumDataPipelineSeeder::class,     // CSV enrichment — runs after artworks exist
        ]);
    }
}
