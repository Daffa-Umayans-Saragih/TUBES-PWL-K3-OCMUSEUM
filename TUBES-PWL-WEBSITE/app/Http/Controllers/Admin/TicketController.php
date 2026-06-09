<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderDetail;
use App\Models\TicketType;
use App\Models\VisitSchedule;
use App\Models\TicketAvailability;
use App\Models\Ticket;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        // Load ticket types
        $ticketTypes = TicketType::where('deleted_at', null)
            ->orderBy('ticket_type_name')
            ->get();

        // Get all visit schedules with location info for the POS dropdown selection
        $schedules = VisitSchedule::with('location')
            ->orderBy('visit_date', 'asc')
            ->get();

        // Determine which schedule is selected (default to the first one)
        $selectedScheduleId = $request->query('schedule_id');
        $selectedSchedule = null;

        if ($selectedScheduleId) {
            $selectedSchedule = VisitSchedule::with('location')->find($selectedScheduleId);
        }

        if (!$selectedSchedule && $schedules->isNotEmpty()) {
            $selectedSchedule = $schedules->first();
        }

        // Fetch ticket availabilities for the selected schedule
        $availabilities = [];
        if ($selectedSchedule) {
            $availabilities = TicketAvailability::where('visit_schedule_id', $selectedSchedule->visit_schedule_id)
                ->with(['ticketType'])
                ->get()
                ->map(function ($availability) use ($selectedSchedule) {
                    $totalStock = $availability->capacity_limit ?? $selectedSchedule->capacity_limit;
                    $sold = Ticket::where('status', '!=', 'cancelled')
                        ->where('ticket_availability_id', $availability->ticket_availability_id)
                        ->whereHas('order', function ($query) {
                            $query->whereRaw('LOWER(order_status) IN (?, ?)', ['paid', 'completed']);
                        })
                        ->count();
                    
                    $remaining = max(0, $totalStock - $sold);

                    // Status Rule
                    if ($remaining > 20) {
                        $status = 'Available';
                    } elseif ($remaining >= 1) {
                        $status = 'Low Stock';
                    } else {
                        $status = 'Sold Out';
                    }

                    return [
                        'ticket_availability_id' => $availability->ticket_availability_id,
                        'ticket_type_id' => $availability->ticket_type_id,
                        'name' => $availability->ticketType->ticket_type_name,
                        'price' => $availability->ticketType->base_price,
                        'remaining' => $remaining,
                        'status' => $status,
                    ];
                });
        }

        return view('admin.tickets.index', [
            'title'              => 'Ticket Sales',
            'subtitle'           => 'Point-of-sale interface for onsite ticket purchases',
            'activeNav'          => 'tickets',
            'ticketTypes'        => $ticketTypes,
            'schedules'          => $schedules,
            'selectedSchedule'   => $selectedSchedule,
            'availabilities'     => $availabilities,
            'breadcrumbs'        => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Tickets', 'isCurrent' => true],
            ],
        ]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'visit_schedule_id' => 'required|integer|exists:visit_schedules,visit_schedule_id',
            'tickets' => 'required|array', // Key is ticket_availability_id, value is quantity
            'tickets.*' => 'integer|min:0',
        ]);

        $schedule = VisitSchedule::findOrFail($request->visit_schedule_id);
        $userId = Auth::id();

        try {
            $order = DB::transaction(function () use ($request, $schedule, $userId) {
                $totalAmount = 0.0;
                $ticketsToGenerate = [];

                foreach ($request->input('tickets', []) as $availabilityId => $qty) {
                    if ($qty <= 0) continue;

                    $availability = TicketAvailability::with('ticketType')
                        ->lockForUpdate()
                        ->findOrFail($availabilityId);
                    
                    // Live oversell check
                    $totalStock = $availability->capacity_limit ?? $schedule->capacity_limit;
                    $sold = Ticket::where('status', '!=', 'cancelled')
                        ->where('ticket_availability_id', $availabilityId)
                        ->whereHas('order', function ($query) {
                            $query->whereRaw('LOWER(order_status) IN (?, ?)', ['paid', 'completed']);
                        })
                        ->count();
                    
                    $remaining = max(0, $totalStock - $sold);

                    if ($qty > $remaining) {
                        throw new \Exception("Cannot purchase {$qty} tickets of type {$availability->ticketType->ticket_type_name}. Only {$remaining} left.");
                    }

                    $totalAmount += ((float) ($availability->ticketType->base_price ?? 0)) * $qty;
                    $ticketsToGenerate[] = [
                        'availability' => $availability,
                        'qty' => $qty,
                    ];
                }

                if (empty($ticketsToGenerate)) {
                    throw new \Exception("Please select at least one ticket to purchase.");
                }

                // Disabilities vs Companion quantity check
                // NOTE: Companion is OPTIONAL. Do NOT auto-generate companion tickets.
                // Only validate the constraint: companion MUST NOT exceed disabilities.
                $disabilitiesQty = 0;
                $companionQty    = 0;

                foreach ($ticketsToGenerate as $item) {
                    $typeName = strtolower($item['availability']->ticketType->ticket_type_name);
                    if ($typeName === 'disabilities') {
                        $disabilitiesQty += $item['qty'];
                    } elseif ($typeName === 'companion') {
                        $companionQty += $item['qty'];
                    }
                }

                // RULE: companion <= disabilities (companion is optional, 0 is valid)
                if ($companionQty > $disabilitiesQty) {
                    throw new \Exception("Companion quantity ({$companionQty}) must not exceed disabilities quantity ({$disabilitiesQty}).");
                }

                // Create Order
                $order = Order::create([
                    'order_code'   => (string) Str::uuid(),
                    'user_id'      => $userId,
                    'guest_id'     => null,
                    'order_date'   => now(),
                    'expired_at'   => now()->addMinutes(30),
                    'total_amount' => $totalAmount,
                    'order_status' => 'paid', // Cashier checkout is instantly paid!
                ]);

                // Create Paid Payment immediately (POS)
                Payment::create([
                    'order_id'       => $order->order_id,
                    'payment_method' => 'Cash',
                    'amount'         => $totalAmount,
                    'payment_status' => 'Paid',
                    'paid_at'        => now(),
                ]);

                // Generate Tickets + order_details (one row per ticket line item)
                foreach ($ticketsToGenerate as $item) {
                    $createdTicketIds = collect();
                    for ($i = 0; $i < $item['qty']; $i++) {
                        $ticket = Ticket::create([
                            'order_id'               => $order->order_id,
                            'ticket_availability_id' => $item['availability']->ticket_availability_id,
                            'qr_code'                => (string) Str::uuid(),
                            'status'                 => 'valid',
                        ]);
                        $createdTicketIds->push($ticket->ticket_id);
                    }

                    $originalPrice = (float) ($item['availability']->ticketType->base_price ?? 0);
                    $unitPrice = $originalPrice; // Admin POS always uses base price
                    $discountAmount = 0;

                    // Record the line item in order_details
                    OrderDetail::create([
                        'order_id'        => $order->order_id,
                        'ticket_id'       => $createdTicketIds->first(),
                        'quantity'        => $item['qty'],
                        'original_price'  => $originalPrice,
                        'unit_price'      => $unitPrice,
                        'discount_amount' => $discountAmount,
                    ]);
                }

                return $order;
            });

            return redirect()->route('admin.tickets.index', ['schedule_id' => $schedule->visit_schedule_id])
                ->with('success', 'Sale completed successfully! Order #' . $order->order_id . ' generated.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Sale failed: ' . $e->getMessage());
        }
    }

    public function management(Request $request)
    {
        // Load ticket types
        $ticketTypes = TicketType::where('deleted_at', null)
            ->orderBy('ticket_type_name')
            ->get();

        $locations = \App\Models\Location::all();

        // Get all visit schedules with location info for the selector dropdown
        $schedules = VisitSchedule::with('location')
            ->orderBy('visit_date', 'asc')
            ->get();

        // Determine which schedule is selected (default to the first one)
        $selectedScheduleId = $request->query('schedule_id');
        $selectedSchedule = null;

        if ($selectedScheduleId) {
            $selectedSchedule = VisitSchedule::with('location')->find($selectedScheduleId);
        }

        if (!$selectedSchedule && $schedules->isNotEmpty()) {
            $selectedSchedule = $schedules->first();
        }

        // Fetch ticket availabilities for the selected schedule
        $availabilities = [];
        if ($selectedSchedule) {
            $availabilities = TicketAvailability::where('visit_schedule_id', $selectedSchedule->visit_schedule_id)
                ->with(['ticketType'])
                ->get()
                ->map(function ($availability) use ($selectedSchedule) {
                    $totalStock = $availability->capacity_limit ?? $selectedSchedule->capacity_limit;
                    $sold = Ticket::where('status', '!=', 'cancelled')
                        ->where('ticket_availability_id', $availability->ticket_availability_id)
                        ->whereHas('order', function ($query) {
                            $query->whereRaw('LOWER(order_status) IN (?, ?)', ['paid', 'completed']);
                        })
                        ->count();
                    $remaining = max(0, $totalStock - $sold);

                    // Status Rule
                    if ($remaining > 20) {
                        $status = 'Available';
                        $statusClass = 'status-active';
                    } elseif ($remaining >= 1) {
                        $status = 'Low Stock';
                        $statusClass = 'status-warning';
                    } else {
                        $status = 'Sold Out';
                        $statusClass = 'status-inactive';
                    }

                    return [
                        'ticket_availability_id' => $availability->ticket_availability_id,
                        'ticket_type_id' => $availability->ticket_type_id,
                        'name' => $availability->ticketType->ticket_type_name,
                        'price' => $availability->ticketType->base_price,
                        'total_stock' => $totalStock,
                        'remaining' => $remaining,
                        'status' => $status,
                        'status_class' => $statusClass,
                    ];
                });
        }

        // Calculate aggregate statistics
        $totalTickets = Ticket::count();
        $soldTickets = Ticket::where('status', '!=', 'valid')->count();
        $availableTickets = TicketAvailability::count() - $totalTickets;

        return view('admin.tickets.management', [
            'title'              => 'Ticket Management',
            'subtitle'           => 'Manage ticket stock and prices',
            'activeNav'          => 'tickets',
            'ticketTypes'        => $ticketTypes,
            'locations'          => $locations,
            'schedules'          => $schedules,
            'selectedSchedule'   => $selectedSchedule,
            'availabilities'     => $availabilities,
            'totalStock'         => $totalTickets,
            'ticketsSold'        => $soldTickets,
            'availableStock'     => $availableTickets,
            'breadcrumbs'        => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Ticket Management', 'isCurrent' => true],
            ],
        ]);
    }

    /**
     * Get available dates from database visit schedules
     */
    private function getAvailableDatesFromDB($visitSchedules)
    {
        $dates = [];
        
        foreach ($visitSchedules as $schedule) {
            // Count available tickets for this schedule
            $availableCount = TicketAvailability::where('visit_schedule_id', $schedule->visit_schedule_id)
                ->count();

            $visitDate = $schedule->visit_date;
            $locationName = $schedule->location ? $schedule->location->location_name : 'Unknown Location';
            $dates[] = [
                'date'      => $visitDate->format('Y-m-d'),
                'day'       => $visitDate->format('l'),
                'display'   => $visitDate->format('M d') . ' - ' . $locationName,
                'available' => $availableCount,
                'visit_schedule_id' => $schedule->visit_schedule_id,
            ];
        }

        return $dates;
    }

    /**
     * Get daily stocks organized by date and ticket type
     */
    private function getDailyStocks($ticketTypes)
    {
        $visitSchedules = VisitSchedule::with('location')
            ->orderBy('visit_date')
            ->limit(14)
            ->get();

        $stocks = [];

        foreach ($visitSchedules as $schedule) {
            $locationName = $schedule->location ? $schedule->location->location_name : 'Unknown Location';
            $dayStocks = [
                'visit_schedule_id' => $schedule->visit_schedule_id,
                'capacity_limit' => $schedule->capacity_limit,
                'date' => $schedule->visit_date->format('M d, Y') . ' - ' . $locationName,
                'visit_date' => $schedule->visit_date->format('Y-m-d'),
                'types' => []  // Array of {type_id, type_name, availability_count}
            ];
            
            $totalForDay = 0;

            foreach ($ticketTypes as $type) {
                $availability = TicketAvailability::where('visit_schedule_id', $schedule->visit_schedule_id)
                    ->where('ticket_type_id', $type->ticket_type_id)
                    ->count();

                $dayStocks['types'][] = [
                    'ticket_type_id' => $type->ticket_type_id,
                    'ticket_type_name' => $type->ticket_type_name,
                    'base_price' => $type->base_price,
                    'availability' => $availability
                ];
                
                $totalForDay += $availability;
            }

            $dayStocks['total'] = $totalForDay;
            $stocks[] = $dayStocks;
        }

        return $stocks;
    }

    /**
     * API: Get all available dates with ticket availability count
     */
    public function getAvailableDates()
    {
        $dates = VisitSchedule::select('visit_schedule_id', 'visit_date')
            ->orderBy('visit_date')
            ->limit(30)
            ->get()
            ->map(function ($schedule) {
                $availabilityCount = TicketAvailability::where('visit_schedule_id', $schedule->visit_schedule_id)
                    ->count();
                
                return [
                    'visit_schedule_id' => $schedule->visit_schedule_id,
                    'visit_date' => $schedule->visit_date->format('Y-m-d'),
                    'display_date' => $schedule->visit_date->format('M d, Y'),
                    'day_of_week' => $schedule->visit_date->format('l'),
                    'available_count' => $availabilityCount,
                    'is_available' => $availabilityCount > 0
                ];
            });

        return response()->json($dates);
    }

    /**
     * API: Get ticket types available for a specific date
     */
    public function getTicketTypesForDate($visitScheduleId)
    {
        $ticketTypes = TicketAvailability::where('visit_schedule_id', $visitScheduleId)
            ->with('ticketType')
            ->get()
            ->map(function ($availability) {
                return [
                    'ticket_type_id' => $availability->ticketType->ticket_type_id,
                    'ticket_type_name' => $availability->ticketType->ticket_type_name,
                    'base_price' => (float) $availability->ticketType->base_price,
                    'formatted_price' => '$' . number_format($availability->ticketType->base_price, 2)
                ];
            });

        return response()->json($ticketTypes);
    }

    public function storeType(Request $request)
    {
        $request->validate([
            'ticket_type_name' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $exists = TicketType::whereRaw('LOWER(ticket_type_name) = ?', [strtolower($value)])
                        ->whereNull('deleted_at')
                        ->exists();
                    if ($exists) {
                        $fail('The ticket type name has already been taken.');
                    }
                }
            ],
            'base_price' => 'required|numeric|min:0',
            'is_membership_discount_active' => 'nullable|boolean',
            'membership_discount_type' => 'nullable|in:percentage,fixed',
            'membership_discount_value' => 'nullable|numeric|min:0',
        ]);

        TicketType::create([
            'ticket_type_name' => $request->ticket_type_name,
            'base_price' => $request->base_price,
            'is_membership_discount_active' => $request->boolean('is_membership_discount_active'),
            'membership_discount_type' => $request->membership_discount_type,
            'membership_discount_value' => $request->membership_discount_value ?? 0,
        ]);

        return redirect()->route('admin.tickets.management')->with('success', 'Ticket type added successfully.');
    }

    public function updateType(Request $request, $id)
    {
        $ticketType = TicketType::findOrFail($id);

        $request->validate([
            'ticket_type_name' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($id) {
                    $exists = TicketType::whereRaw('LOWER(ticket_type_name) = ?', [strtolower($value)])
                        ->where('ticket_type_id', '!=', $id)
                        ->whereNull('deleted_at')
                        ->exists();
                    if ($exists) {
                        $fail('The ticket type name has already been taken.');
                    }
                }
            ],
            'base_price' => 'required|numeric|min:0',
            'is_membership_discount_active' => 'nullable|boolean',
            'membership_discount_type' => 'nullable|in:percentage,fixed',
            'membership_discount_value' => 'nullable|numeric|min:0',
        ]);

        $ticketType->update([
            'ticket_type_name' => $request->ticket_type_name,
            'base_price' => $request->base_price,
            'is_membership_discount_active' => $request->boolean('is_membership_discount_active'),
            'membership_discount_type' => $request->membership_discount_type,
            'membership_discount_value' => $request->membership_discount_value ?? 0,
        ]);

        return redirect()->route('admin.tickets.management')->with('success', 'Ticket type updated successfully.');
    }

    public function deleteType($id)
    {
        $ticketType = TicketType::findOrFail($id);
        $ticketType->delete();

        return redirect()->route('admin.tickets.management')->with('success', 'Ticket type deleted successfully.');
    }

    public function addStock(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'location_id' => 'required|integer|exists:locations,location_id',
            'ticket_type_id' => 'required|integer|exists:ticket_types,ticket_type_id',
            'quantity' => 'required|integer|min:1',
        ]);

        // Find or create visit schedule
        $schedule = VisitSchedule::firstOrCreate(
            [
                'location_id' => $request->location_id,
                'visit_date' => $request->date,
            ],
            [
                'capacity_limit' => $request->quantity,
            ]
        );

        // Enable availability for this ticket type
        $availability = TicketAvailability::firstOrCreate([
            'visit_schedule_id' => $schedule->visit_schedule_id,
            'ticket_type_id' => $request->ticket_type_id,
        ]);

        // Set or increment availability's own capacity limit
        $currentLimit = $availability->capacity_limit ?? $schedule->capacity_limit;
        $availability->update([
            'capacity_limit' => $currentLimit + $request->quantity,
        ]);

        return redirect()->route('admin.tickets.management', ['schedule_id' => $schedule->visit_schedule_id])
            ->with('success', 'Stock added successfully.');
    }

    public function updateStock(Request $request)
    {
        $request->validate([
            'ticket_availability_id' => 'required|integer|exists:ticket_availability,ticket_availability_id',
            'capacity_limit' => 'required|integer|min:0',
        ]);

        $availability = TicketAvailability::findOrFail($request->ticket_availability_id);
        $availability->update([
            'capacity_limit' => $request->capacity_limit,
        ]);

        return redirect()->route('admin.tickets.management', ['schedule_id' => $availability->visit_schedule_id])
            ->with('success', 'Stock quantity updated successfully.');
    }
}
