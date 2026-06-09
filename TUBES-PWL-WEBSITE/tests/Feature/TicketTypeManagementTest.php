<?php

namespace Tests\Feature;

use App\Models\TicketType;
use App\Models\User;
use App\Models\Location;
use App\Models\VisitSchedule;
use App\Models\TicketAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTypeManagementTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $location;
    private $schedule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->admin = User::factory()->create([
            'is_admin' => true
        ]);

        $this->location = Location::create([
            'location_name' => 'Main Gallery Museum',
            'address' => '1000 5th Avenue',
            'capacity_limit' => 300
        ]);

        $this->schedule = VisitSchedule::create([
            'location_id' => $this->location->location_id,
            'visit_date' => now()->addDays(2)->format('Y-m-d'),
            'capacity_limit' => 300
        ]);
    }

    /**
     * TEST 1: Permanent disabilities ticket type exists
     */
    public function test_disabilities_type_exists_permanently()
    {
        // Run migration to seed disabilities if not already done
        $this->artisan('migrate');

        $exists = TicketType::whereRaw('LOWER(ticket_type_name) = ?', ['disabilities'])->exists();
        $this->assertTrue($exists);
    }

    /**
     * TEST 2: Add dynamic ticket type
     */
    public function test_add_ticket_type_dynamic()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.tickets.types.store'), [
            'ticket_type_name' => 'Premium Extra',
            'base_price' => 75.50
        ]);

        $response->assertRedirect(route('admin.tickets.management'));
        
        $this->assertDatabaseHas('ticket_types', [
            'ticket_type_name' => 'Premium Extra',
            'base_price' => 75.50
        ]);
    }

    /**
     * TEST 2 (Validation): Case-insensitive uniqueness
     */
    public function test_add_ticket_type_case_insensitive_validation()
    {
        TicketType::create([
            'ticket_type_name' => 'Premium',
            'base_price' => 50.00
        ]);

        // Attempting to add "premium" (lowercase) should fail validation
        $response = $this->actingAs($this->admin)->post(route('admin.tickets.types.store'), [
            'ticket_type_name' => 'premium',
            'base_price' => 45.00
        ]);

        $response->assertSessionHasErrors(['ticket_type_name']);
    }

    /**
     * TEST 3: Edit ticket type
     */
    public function test_edit_ticket_type()
    {
        $type = TicketType::create([
            'ticket_type_name' => 'Early Bird',
            'base_price' => 15.00
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.tickets.types.update', $type->ticket_type_id), [
            'ticket_type_name' => 'Early Bird Premium',
            'base_price' => 22.00
        ]);

        $response->assertRedirect(route('admin.tickets.management'));

        $this->assertDatabaseHas('ticket_types', [
            'ticket_type_id' => $type->ticket_type_id,
            'ticket_type_name' => 'Early Bird Premium',
            'base_price' => 22.00
        ]);
    }

    /**
     * TEST 4: Delete ticket type using SoftDeletes
     */
    public function test_delete_ticket_type_soft_deletes()
    {
        $type = TicketType::create([
            'ticket_type_name' => 'Temporary Pass',
            'base_price' => 12.00
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.tickets.types.destroy', $type->ticket_type_id));

        $response->assertRedirect(route('admin.tickets.management'));

        $this->assertSoftDeleted('ticket_types', [
            'ticket_type_id' => $type->ticket_type_id
        ]);
    }

    /**
     * TEST 5: Update stock capacity and availability
     */
    public function test_update_stock_and_availabilities()
    {
        $type = TicketType::create([
            'ticket_type_name' => 'VIP Exclusive',
            'base_price' => 100.00
        ]);

        $avail = TicketAvailability::create([
            'visit_schedule_id' => $this->schedule->visit_schedule_id,
            'ticket_type_id' => $type->ticket_type_id,
            'capacity_limit' => 100,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.tickets.stock.update'), [
            'ticket_availability_id' => $avail->ticket_availability_id,
            'capacity_limit' => 250,
        ]);

        $response->assertRedirect(route('admin.tickets.management', ['schedule_id' => $this->schedule->visit_schedule_id]));

        // Assert capacity limit changed
        $avail->refresh();
        $this->assertEquals(250, $avail->capacity_limit);
    }

    /**
     * TEST 6: Availability badge visible
     */
    public function test_availability_badge_visible_on_management()
    {
        $type = TicketType::create([
            'ticket_type_name' => 'VIP Exclusive',
            'base_price' => 100.00
        ]);

        // Enable availability
        TicketAvailability::create([
            'visit_schedule_id' => $this->schedule->visit_schedule_id,
            'ticket_type_id' => $type->ticket_type_id,
            'capacity_limit' => 100,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.tickets.management', ['schedule_id' => $this->schedule->visit_schedule_id]));
        
        $response->assertStatus(200);
        $response->assertSee('Available');
    }
}
