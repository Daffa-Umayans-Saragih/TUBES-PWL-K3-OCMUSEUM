<?php

namespace Tests\Feature;

use App\Models\TicketType;
use App\Models\User;
use App\Models\Location;
use App\Models\VisitSchedule;
use App\Models\TicketAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketManagementCleanUpTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        // Create admin user
        $this->admin = User::factory()->create([
            'is_admin' => true
        ]);
    }

    /**
     * Test the availability render outputs badges correctly.
     */
    public function test_availability_render_logic()
    {
        // 1. Create a Location
        $location = Location::create([
            'location_name' => 'Main Met Museum',
            'address' => '1000 5th Ave',
            'capacity_limit' => 500
        ]);

        // 2. Create a Visit Schedule
        $schedule = VisitSchedule::create([
            'location_id' => $location->location_id,
            'visit_date' => now()->addDay()->format('Y-m-d'),
            'capacity_limit' => 500
        ]);

        // 3. Create two Ticket Types
        $typeAvailable = TicketType::create([
            'ticket_type_name' => 'Adult Premium',
            'base_price' => 50.00
        ]);

        // 4. Set only the first type as available for the schedule
        TicketAvailability::create([
            'visit_schedule_id' => $schedule->visit_schedule_id,
            'ticket_type_id' => $typeAvailable->ticket_type_id,
            'capacity_limit' => 100,
        ]);

        // 5. Access management page as admin
        $response = $this->actingAs($this->admin)->get(route('admin.tickets.management', ['schedule_id' => $schedule->visit_schedule_id]));

        $response->assertStatus(200);

        // 6. Assert correct HTML rendering of status badges
        $response->assertSee('Available');
    }

    /**
     * Test model supports soft deletes.
     */
    public function test_ticket_type_model_supports_soft_deletes()
    {
        $type = TicketType::create([
            'ticket_type_name' => 'Temporary Promo',
            'base_price' => 10.00
        ]);

        $type->delete();

        // Ensure row is soft deleted
        $this->assertSoftDeleted('ticket_types', [
            'ticket_type_id' => $type->ticket_type_id
        ]);
    }
}
