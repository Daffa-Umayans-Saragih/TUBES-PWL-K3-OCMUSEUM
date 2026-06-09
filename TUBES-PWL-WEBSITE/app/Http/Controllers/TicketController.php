<?php
namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartGroup;
use App\Models\CartItem;
use App\Models\Guest;
use App\Models\Location;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\VisitSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(): View | JsonResponse
    {
        $this->autoGenerateSchedules();

        $schedules = VisitSchedule::with([
            'location',
            'ticketAvailabilities' => function ($query) {
                $query->whereHas('ticketType', function ($q) {
                    $q->whereRaw('LOWER(ticket_type_name) != ?', ['disabilities'])
                      ->whereRaw('LOWER(ticket_type_name) != ?', ['companion']);
                });
            },
            'ticketAvailabilities.ticketType',
        ])
            ->where('visit_date', '>=', \Carbon\Carbon::today()->toDateString())
            ->orderBy('visit_date')
            ->get();

        if (request()->expectsJson()) {
            return response()->json([
                'data' => $schedules,
            ]);
        }

        // 🔥 DEFAULT STATE (WAJIB UNTUK UI AGAR TIDAK ERROR)
        $cartItems = session('cartItems', []);
        $subtotal  = collect($cartItems)->sum(function ($item) {
            return $item['total'] ?? 0;
        });

        return view('ordinary.admission.admission', [
            'schedules' => $schedules,
            'title'     => 'Select Visit Date',
            'cartItems' => $cartItems,
            'subtotal'  => $subtotal,
        ]);
    }

    public function show(VisitSchedule $schedule): View | JsonResponse
    {
        $schedule->load([
            'location',
            'ticketAvailabilities' => function ($query) {
                $query->whereHas('ticketType', function ($q) {
                    $q->whereRaw('LOWER(ticket_type_name) != ?', ['disabilities'])
                      ->whereRaw('LOWER(ticket_type_name) != ?', ['companion']);
                });
            },
            'ticketAvailabilities.ticketType',
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'data' => $schedule,
            ]);
        }

        return view('ordinary.admission.admission', [
            'schedule'             => $schedule,
            'ticketAvailabilities' => $schedule->ticketAvailabilities,
            'title'                => 'Select Tickets',
        ]);
    }

    public function scan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'qr_code' => ['required', 'string'],
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                // 1. Load ticket with lock for update to prevent double scan race conditions
                $ticket = Ticket::where('qr_code', $validated['qr_code'])
                    ->lockForUpdate()
                    ->first();

                // 2. Ticket existence check
                if (! $ticket) {
                    return response()->json(['message' => 'invalid ticket'], 404);
                }

                // 3. Status check (must be 'valid')
                // Tickets that are 'cancelled' or 'pending' (not yet paid) cannot be used
                if ($ticket->status === 'cancelled' || $ticket->status === 'pending') {
                    return response()->json(['message' => 'invalid ticket'], 422);
                }

                // 4. Already used check
                if ($ticket->status === 'used') {
                    return response()->json(['message' => 'already used'], 422);
                }

                // 5. Expiration check — +1 DAY STRATEGY
                //
                // Expiry boundary = visit_date + 1 day (at 00:00:00).
                // Ticket for visit_date '2026-05-20' is valid until 2026-05-20 23:59:59.
                // It becomes expired from 2026-05-21 00:00:00 onwards.
                //
                //   now() < 2026-05-21 00:00:00  → VALID
                //   now() >= 2026-05-21 00:00:00 → EXPIRED
                $availability = $ticket->ticketAvailability;
                $schedule     = $availability ? $availability->visitSchedule : null;

                if ($schedule && $schedule->visit_date) {
                    // visit_date is a Carbon date; addDay() shifts it to the next midnight.
                    $expiryBoundary = \Carbon\Carbon::parse($schedule->visit_date)->addDay(); // 2026-05-21 00:00:00

                    if (now()->gte($expiryBoundary)) {
                        return response()->json(['message' => 'expired'], 422);
                    }
                }


                // 6. Update status
                $ticket->update([
                    'status'  => 'used',
                    'used_at' => now(),
                ]);

                return response()->json([
                    'message' => 'Check-in successful',
                    'data'    => [
                        'ticket_id' => $ticket->ticket_id,
                        'qr_code'   => $ticket->qr_code,
                        'status'    => 'used',
                        'used_at'   => $ticket->used_at->toDateTimeString(),
                    ],
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Scan failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function storeCart(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'location_id'                    => ['required', 'integer', 'exists:locations,location_id'],
            'visit_schedule_id'              => ['required', 'integer', 'exists:visit_schedules,visit_schedule_id'],
            'items'                          => ['required', 'array', 'min:1'],
            'items.*.ticket_availability_id' => ['required', 'integer', 'exists:ticket_availabilities,ticket_availability_id'],
            'items.*.quantity'               => ['required', 'integer', 'min:1'],
        ]);

        try {
            $cartId = DB::transaction(function () use ($validated, $request) {
                // 🔍 Determine user or guest
                if ($request->user()) {
                    $user_id  = $request->user()->user_id;
                    $guest_id = null;
                } else {
                    $user_id = null;
                    // Get or create guest session
                    $guest_id = session('guest_id');
                    if (! $guest_id) {
                        $guest    = Guest::create([]);
                        $guest_id = $guest->guest_id;
                        session(['guest_id' => $guest_id]);
                    }
                }

                // 💾 Create or find cart
                $cart = Cart::firstOrCreate(
                    [
                        'user_id'  => $user_id,
                        'guest_id' => $guest_id,
                    ],
                    [
                        'expires_at' => now()->addDays(1),
                    ]
                );

                // Validation check for Disabilities vs Companion quantity
                $disabilitiesQty = 0;
                $companionQty = 0;
                foreach ($validated['items'] as $item) {
                    $avail = \App\Models\TicketAvailability::with('ticketType')
                        ->find($item['ticket_availability_id']);
                    if ($avail) {
                        $typeName = strtolower($avail->ticketType->ticket_type_name);
                        if ($typeName === 'disabilities') {
                            $disabilitiesQty += $item['quantity'];
                        } elseif ($typeName === 'companion') {
                            $companionQty += $item['quantity'];
                        }
                    }
                }
                if ($companionQty > $disabilitiesQty) {
                    throw new \Exception("Companion quantity ({$companionQty}) must not exceed disabilities quantity ({$disabilitiesQty}).");
                }

                // 📦 Create cart_group
                $cartGroup = CartGroup::create([
                    'cart_id' => $cart->cart_id,
                ]);

                // 🛒 Insert cart_items
                foreach ($validated['items'] as $item) {
                    CartItem::create([
                        'cart_group_id'          => $cartGroup->cart_group_id,
                        'ticket_availability_id' => $item['ticket_availability_id'],
                        'quantity'               => $item['quantity'],
                    ]);
                }

                return $cart->cart_id;
            });

            return response()->json([
                'message' => 'Cart saved successfully',
                'cart_id' => $cartId,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to save cart: ' . $e->getMessage(),
            ], 422);
        }
    }

    private function autoGenerateSchedules(): void
    {
        $today = \Carbon\Carbon::today();

        $hasFuture = VisitSchedule::where('visit_date', '>=', $today->toDateString())->exists();
        if ($hasFuture) {
            return;
        }

        $locations = Location::all();
        $ticketTypes = TicketType::all();

        DB::transaction(function () use ($today, $locations, $ticketTypes) {
            for ($i = 0; $i < 7; $i++) {
                $date = $today->copy()->addDays($i)->format('Y-m-d');

                foreach ($locations as $location) {
                    $schedule = VisitSchedule::firstOrCreate(
                        ['location_id' => $location->location_id, 'visit_date' => $date],
                        ['capacity_limit' => $location->capacity_limit ?? 500]
                    );

                    foreach ($ticketTypes as $type) {
                        \App\Models\TicketAvailability::firstOrCreate(
                            ['visit_schedule_id' => $schedule->visit_schedule_id, 'ticket_type_id' => $type->ticket_type_id],
                            ['capacity_limit' => 500]
                        );
                    }
                }
            }
        });
    }
}
