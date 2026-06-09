<?php
namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartGroup;
use App\Models\CartItem;
use App\Models\TicketAvailability;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CartController extends Controller
{
    // =========================================================
    // SESSION CART HELPERS (for anonymous / unauthenticated users)
    // =========================================================

    private function isAnonymous(): bool
    {
        return ! Auth::id() && ! session('guest_id');
    }

    private function getSessionCart(): array
    {
        return session('session_cart', []);
    }

    private function saveSessionCart(array $groups): void
    {
        session(['session_cart' => array_values($groups)]);
    }

    private function buildSessionCartDisplay(): array
    {
        $groups         = $this->getSessionCart();
        $cartGroupsData = collect();
        $globalSubtotal = 0;

        foreach ($groups as $group) {
            $groupTotal   = 0;
            $groupItems   = collect();
            $locationName = 'Unknown Location';
            $visitDate    = 'Unknown Date';

            foreach ($group['items'] as $item) {
                $availability = TicketAvailability::with([
                    'ticketType',
                    'visitSchedule.location',
                ])->find($item['ticket_availability_id']);

                if (! $availability) {
                    continue;
                }

                $ticketType = $availability->ticketType;
                $schedule   = $availability->visitSchedule;
                $location   = $schedule?->location;

                if ($location && $locationName === 'Unknown Location') {
                    $locationName = $location->name;
                }
                if ($schedule && $visitDate === 'Unknown Date') {
                    $rawDate   = $schedule->visit_date;
                    $visitDate = $rawDate instanceof Carbon
                        ? $rawDate->format('l, F j, Y')
                        : Carbon::parse($rawDate)->format('l, F j, Y');
                }

                $originalPrice = (float) ($ticketType?->base_price ?? 0);
                $price     = (float) ($ticketType?->getEffectivePrice(Auth::user()) ?? 0);
                $itemTotal = $price * $item['quantity'];
                $groupTotal += $itemTotal;

                $groupItems->push([
                    'id'                     => 'session:' . $item['ticket_availability_id'],
                    'ticket_type'            => $ticketType?->name,
                    'quantity'               => $item['quantity'],
                    'original_price'         => $originalPrice,
                    'price'                  => $price,
                    'item_total'             => $itemTotal,
                    'ticket_availability_id' => $item['ticket_availability_id'],
                ]);
            }

            $globalSubtotal += $groupTotal;

            if ($groupItems->isNotEmpty()) {
                $cartGroupsData->push([
                    'group_id'    => 'session:' . $group['session_group_id'],
                    'location'    => $locationName,
                    'visit_date'  => $visitDate,
                    'items'       => $groupItems,
                    'group_total' => $groupTotal,
                ]);
            }
        }

        return [
            'cartGroups'     => $cartGroupsData,
            'globalSubtotal' => $globalSubtotal,
        ];
    }

    /**
     * Persist session cart into the database after authentication.
     * Called by LoginController and GuestLoginController after auth success.
     */
    public static function migrateSessionCartToDb(?int $userId = null, ?int $guestId = null): void
    {
        $sessionCart = session('session_cart', []);
        if (empty($sessionCart)) {
            return;
        }

        // Remove any existing DB cart for this user/guest to avoid duplicates
        if ($userId) {
            Cart::where('user_id', $userId)->delete();
        } elseif ($guestId) {
            Cart::where('guest_id', $guestId)->delete();
        }

        $cart = Cart::create([
            'user_id'    => $userId,
            'guest_id'   => $guestId,
            'expires_at' => now()->addHours(2),
        ]);

        session(['cart_id' => $cart->cart_id]);

        foreach ($sessionCart as $group) {
            $cartGroup = CartGroup::create(['cart_id' => $cart->cart_id]);
            foreach ($group['items'] as $item) {
                CartItem::create([
                    'cart_group_id'          => $cartGroup->cart_group_id,
                    'ticket_availability_id' => $item['ticket_availability_id'],
                    'quantity'               => $item['quantity'],
                ]);
            }
        }

        session()->forget('session_cart');
    }

    // =========================================================
    // PUBLIC CONTROLLER METHODS
    // =========================================================

    public function add(Request $request): JsonResponse | RedirectResponse
    {
        $validated = $request->validate([
            'ticket_availability_id' => ['required', 'integer', 'exists:ticket_availability,ticket_availability_id'],
            'quantity'               => ['required', 'integer', 'min:1'],
            'guest_id'               => ['nullable', 'integer', 'exists:guests,guest_id'],
        ]);

        $userId  = Auth::id();
        $guestId = $userId ? null : session('guest_id');

        // Validation: Disabilities vs Companion
        $disabilitiesQty = 0;
        $companionQty = 0;
        $newAvail = TicketAvailability::with('ticketType')->find($validated['ticket_availability_id']);
        if ($newAvail) {
            $typeName = strtolower($newAvail->ticketType->ticket_type_name);
            if ($typeName === 'disabilities') {
                $disabilitiesQty += $validated['quantity'];
            } elseif ($typeName === 'companion') {
                $companionQty += $validated['quantity'];
            }
        }

        // ── ANONYMOUS: store in session cart ─────────────────────────
        if (! $userId && ! $guestId) {
            $groups = $this->getSessionCart();
            foreach ($groups as $group) {
                foreach ($group['items'] as $item) {
                    $avail = TicketAvailability::with('ticketType')->find($item['ticket_availability_id']);
                    if ($avail) {
                        $typeName = strtolower($avail->ticketType->ticket_type_name);
                        if ($typeName === 'disabilities') {
                            $disabilitiesQty += $item['quantity'];
                        } elseif ($typeName === 'companion') {
                            $companionQty += $item['quantity'];
                        }
                    }
                }
            }

            if ($companionQty > $disabilitiesQty) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => "Failed to save cart: Companion quantity ({$companionQty}) must not exceed disabilities quantity ({$disabilitiesQty})."], 422);
                }
                return redirect()->back()->with('error', "Failed to save cart: Companion quantity ({$companionQty}) must not exceed disabilities quantity ({$disabilitiesQty}).");
            }

            if (empty($groups)) {
                $groups[] = [
                    'session_group_id' => (string) Str::uuid(),
                    'items'            => [],
                ];
            }

            $groups[0]['items'][] = [
                'ticket_availability_id' => $validated['ticket_availability_id'],
                'quantity'               => $validated['quantity'],
            ];

            $this->saveSessionCart($groups);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Item added to cart.'], 201);
            }

            return redirect()->route('ticket.cart')->with('success', 'Item added to cart.');
        }

        // ── AUTHENTICATED: store in DB cart ──────────────────────────
        $cart = $this->resolveCart($userId, $guestId);

        if ($cart) {
            $existingItems = $cart->cartGroups->flatMap->cartItems;
            foreach ($existingItems as $item) {
                $avail = TicketAvailability::with('ticketType')->find($item->ticket_availability_id);
                if ($avail) {
                    $typeName = strtolower($avail->ticketType->ticket_type_name);
                    if ($typeName === 'disabilities') {
                        $disabilitiesQty += $item->quantity;
                    } elseif ($typeName === 'companion') {
                        $companionQty += $item->quantity;
                    }
                }
            }
        }

        if ($companionQty > $disabilitiesQty) {
            if ($request->expectsJson()) {
                return response()->json(['message' => "Failed to save cart: Companion quantity ({$companionQty}) must not exceed disabilities quantity ({$disabilitiesQty})."], 422);
            }
            return redirect()->back()->with('error', "Failed to save cart: Companion quantity ({$companionQty}) must not exceed disabilities quantity ({$disabilitiesQty}).");
        }

        if (! $cart) {
            $cart = Cart::create([
                'user_id'    => $userId,
                'guest_id'   => $guestId,
                'expires_at' => now()->addHours(2),
            ]);
            session()->put('cart_id', $cart->cart_id);
        }

        if ($cart->expires_at && $cart->expires_at->isPast()) {
            $cart->update(['expires_at' => now()->addHours(2)]);
        }

        $cartGroup = $cart->cartGroups()->first();
        if (! $cartGroup) {
            $cartGroup = CartGroup::create(['cart_id' => $cart->cart_id]);
        }

        CartItem::create([
            'cart_group_id'          => $cartGroup->cart_group_id,
            'ticket_availability_id' => $validated['ticket_availability_id'],
            'quantity'               => $validated['quantity'],
        ]);

        $cart->load([
            'cartGroups.cartItems.ticketAvailability.ticketType',
            'cartGroups.cartItems.ticketAvailability.visitSchedule.location',
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Item added to cart.', 'data' => $cart], 201);
        }

        return redirect()->route('ticket.cart')->with('success', 'Item added to cart.');
    }

    public function index(Request $request): View | JsonResponse
    {
        $validated = $request->validate([
            'guest_id' => ['nullable', 'integer', 'exists:guests,guest_id'],
        ]);

        $userId  = Auth::id();
        $guestId = $userId ? null : ($validated['guest_id'] ?? session('guest_id'));

        // ── ANONYMOUS: display session cart ──────────────────────────
        if (! $userId && ! $guestId) {
            $display = $this->buildSessionCartDisplay();

            if ($request->expectsJson()) {
                return response()->json([
                    'data' => [
                        'cart_groups'     => $display['cartGroups'],
                        'global_subtotal' => $display['globalSubtotal'],
                    ],
                ]);
            }

            return view('ordinary.checkout.cart.cart', [
                'cartGroups'     => $display['cartGroups'],
                'globalSubtotal' => $display['globalSubtotal'],
                'title'          => 'Cart',
            ]);
        }

        // ── AUTHENTICATED: display DB cart ───────────────────────────
        $cart = $this->resolveCart($userId, $guestId);

        if ($cart) {
            $cart->load([
                'cartGroups.cartItems.ticketAvailability.ticketType',
                'cartGroups.cartItems.ticketAvailability.visitSchedule.location',
            ]);
        }

        $cartGroupsData = collect();
        $globalSubtotal = 0;

        if ($cart) {
            foreach ($cart->cartGroups as $group) {
                $groupTotal   = 0;
                $groupItems   = collect();
                $locationName = 'Unknown Location';
                $visitDate    = 'Unknown Date';

                foreach ($group->cartItems as $item) {
                    $ticketType = $item->ticketAvailability?->ticketType;
                    $schedule   = $item->ticketAvailability?->visitSchedule;
                    $location   = $schedule?->location;

                    if ($location && $locationName === 'Unknown Location') {
                        $locationName = $location->name;
                    }
                    if ($schedule && $visitDate === 'Unknown Date') {
                        $rawDate   = $schedule->visit_date;
                        $visitDate = $rawDate instanceof Carbon
                            ? $rawDate->format('l, F j, Y')
                            : Carbon::parse($rawDate)->format('l, F j, Y');
                    }

                    $originalPrice = (float) ($ticketType?->base_price ?? 0);
                    $price         = (float) ($ticketType?->getEffectivePrice(Auth::user()) ?? 0);
                    $itemTotal     = $price * $item->quantity;
                    $groupTotal   += $itemTotal;

                    $groupItems->push([
                        'id'                     => $item->cart_item_id,
                        'ticket_type'            => $ticketType?->name,
                        'quantity'               => $item->quantity,
                        'original_price'         => $originalPrice,
                        'price'                  => $price,
                        'item_total'             => $itemTotal,
                        'ticket_availability_id' => $item->ticket_availability_id,
                    ]);
                }

                $globalSubtotal += $groupTotal;

                if ($groupItems->isNotEmpty()) {
                    $cartGroupsData->push([
                        'group_id'    => $group->cart_group_id,
                        'location'    => $locationName,
                        'visit_date'  => $visitDate,
                        'items'       => $groupItems,
                        'group_total' => $groupTotal,
                    ]);
                }
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'data' => [
                    'cart_groups'     => $cartGroupsData,
                    'global_subtotal' => $globalSubtotal,
                ],
            ]);
        }

        return view('ordinary.checkout.cart.cart', [
            'cartGroups'     => $cartGroupsData,
            'globalSubtotal' => $globalSubtotal,
            'title'          => 'Cart',
        ]);
    }

    private function resolveCart(?int $userId, ?int $guestId): ?Cart
    {
        $query = Cart::query()->where('expires_at', '>', now());

        if ($userId) {
            return $query->where('user_id', $userId)->first();
        }

        if ($guestId) {
            return $query->where('guest_id', $guestId)->first();
        }

        if (session()->has('cart_id')) {
            return $query->whereKey(session('cart_id'))->first();
        }

        return null;
    }

    public function storeAdmission(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'location_id'            => ['required', 'integer'],
            'visit_schedule_id'      => ['required', 'integer'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.ticket_type_id' => ['required', 'integer'],
            'items.*.quantity'       => ['required', 'integer', 'min:1'],
        ]);

        $userId  = Auth::id();
        $guestId = $userId ? null : session('guest_id');

        // Validation check for Disabilities vs Companion quantity
        $disabilitiesQty = 0;
        $companionQty = 0;
        foreach ($validated['items'] as $item) {
            $ticketType = \App\Models\TicketType::find($item['ticket_type_id']);
            if ($ticketType) {
                $typeName = strtolower($ticketType->ticket_type_name);
                if ($typeName === 'disabilities') {
                    $disabilitiesQty += $item['quantity'];
                } elseif ($typeName === 'companion') {
                    $companionQty += $item['quantity'];
                }
            }
        }
        if ($companionQty > $disabilitiesQty) {
            return response()->json([
                'success' => false,
                'message' => "Failed to save cart: Companion quantity ({$companionQty}) must not exceed disabilities quantity ({$disabilitiesQty}).",
            ], 422);
        }

        // ── ANONYMOUS: store group in session cart ────────────────────
        if (! $userId && ! $guestId) {
            $items = [];

            foreach ($validated['items'] as $item) {
                $availability = TicketAvailability::where('ticket_type_id', $item['ticket_type_id'])
                    ->where('visit_schedule_id', $validated['visit_schedule_id'])
                    ->first();

                if (! $availability) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ticket availability not found for type ' . $item['ticket_type_id'],
                    ], 422);
                }

                $schedule = \App\Models\VisitSchedule::find($validated['visit_schedule_id']);
                $totalStock = $availability->capacity_limit ?? ($schedule->capacity_limit ?? 0);
                $sold = Ticket::where('ticket_availability_id', $availability->ticket_availability_id)
                    ->where('status', '!=', 'cancelled')
                    ->whereHas('order', function ($query) {
                        $query->whereRaw('LOWER(order_status) IN (?, ?)', ['paid', 'completed']);
                    })
                    ->count();
                $remaining = max(0, $totalStock - $sold);

                if ($item['quantity'] > $remaining) {
                    $ticketType = \App\Models\TicketType::find($item['ticket_type_id']);
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient ticket stock. Only {$remaining} left for {$ticketType->ticket_type_name}."
                    ], 422);
                }

                $items[] = [
                    'ticket_availability_id' => $availability->ticket_availability_id,
                    'quantity'               => $item['quantity'],
                ];
            }

            $sessionGroupId = (string) Str::uuid();
            $groups         = $this->getSessionCart();

            // Handle modify: remove old session group
            if (session()->has('modify_session_group_id')) {
                $modifyId = session('modify_session_group_id');
                $groups   = array_filter($groups, fn ($g) => $g['session_group_id'] !== $modifyId);
                session()->forget('modify_session_group_id');
            }

            $groups[] = [
                'session_group_id'  => $sessionGroupId,
                'location_id'       => $validated['location_id'],
                'visit_schedule_id' => $validated['visit_schedule_id'],
                'items'             => $items,
            ];

            $this->saveSessionCart($groups);

            return response()->json([
                'success'       => true,
                'cart_group_id' => 'session:' . $sessionGroupId,
            ], 200);
        }

        // ── AUTHENTICATED: store in DB cart ──────────────────────────
        try {
            DB::beginTransaction();

            $cart = $this->resolveCart($userId, $guestId);
            if (! $cart) {
                $cart = Cart::create([
                    'user_id'    => $userId,
                    'guest_id'   => $guestId,
                    'expires_at' => now()->addHours(2),
                ]);
                session()->put('cart_id', $cart->cart_id);
            }

            if ($cart->expires_at && $cart->expires_at->isPast()) {
                throw new \RuntimeException('Cart expired');
            }

            $cartGroup = CartGroup::create(['cart_id' => $cart->cart_id]);

            foreach ($validated['items'] as $item) {
                $availability = TicketAvailability::where('ticket_type_id', $item['ticket_type_id'])
                    ->where('visit_schedule_id', $validated['visit_schedule_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $availability) {
                    throw new \RuntimeException('Ticket availability not found for type ' . $item['ticket_type_id'] . ' on the selected schedule.');
                }
                
                // Early Stock Validation
                $schedule = \App\Models\VisitSchedule::find($validated['visit_schedule_id']);
                $totalStock = $availability->capacity_limit ?? ($schedule->capacity_limit ?? 0);
                $sold = Ticket::where('ticket_availability_id', $availability->ticket_availability_id)
                    ->where('status', '!=', 'cancelled')
                    ->whereHas('order', function ($query) {
                        $query->whereRaw('LOWER(order_status) IN (?, ?)', ['paid', 'completed']);
                    })
                    ->count();
                $remaining = max(0, $totalStock - $sold);

                if ($item['quantity'] > $remaining) {
                    $ticketType = \App\Models\TicketType::find($item['ticket_type_id']);
                    throw new \RuntimeException("Insufficient ticket stock. Only {$remaining} left for {$ticketType->ticket_type_name}.");
                }

                CartItem::create([
                    'cart_group_id'          => $cartGroup->cart_group_id,
                    'ticket_availability_id' => $availability->ticket_availability_id,
                    'quantity'               => $item['quantity'],
                ]);
            }

            if (session()->has('modify_cart_group_id')) {
                $oldGroupId = session('modify_cart_group_id');

                $oldGroup = CartGroup::where('cart_group_id', $oldGroupId)
                    ->where('cart_id', $cart->cart_id)
                    ->lockForUpdate()
                    ->first();

                if (! $oldGroup) {
                    throw new \RuntimeException('Invalid modify target');
                }

                $oldGroup->cartItems()->delete();
                $oldGroup->delete();

                session()->forget('modify_cart_group_id');
            }

            DB::commit();

            return response()->json([
                'success'       => true,
                'cart_group_id' => $cartGroup->cart_group_id,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to store admission tickets. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function removeGroup(Request $request, $id): JsonResponse
    {
        // ── SESSION CART GROUP ────────────────────────────────────────
        if (str_starts_with((string) $id, 'session:')) {
            $sessionGroupId = substr((string) $id, 8);
            $groups         = $this->getSessionCart();
            $groups         = array_filter($groups, fn ($g) => $g['session_group_id'] !== $sessionGroupId);
            $this->saveSessionCart(array_values($groups));

            return response()->json(['success' => true, 'message' => 'Group removed.']);
        }

        // ── DB CART GROUP ─────────────────────────────────────────────
        $cartGroup = CartGroup::where('cart_group_id', $id)->first();

        if (! $cartGroup) {
            return response()->json(['success' => false, 'message' => 'Cart group not found.'], 404);
        }

        if (! \Illuminate\Support\Facades\Gate::allows('modify', $cartGroup)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        try {
            DB::beginTransaction();
            $cartGroup->cartItems()->delete();
            $cartGroup->delete();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Group removed.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to remove group. ' . $e->getMessage()], 500);
        }
    }

    public function modifyGroup(Request $request, $id): RedirectResponse
    {
        // ── SESSION CART GROUP ────────────────────────────────────────
        if (str_starts_with((string) $id, 'session:')) {
            $sessionGroupId = substr((string) $id, 8);
            $groups         = $this->getSessionCart();
            $exists         = collect($groups)->firstWhere('session_group_id', $sessionGroupId);

            if (! $exists) {
                abort(404, 'Cart group not found.');
            }

            session(['modify_session_group_id' => $sessionGroupId]);

            return redirect()->route('ticket.admission');
        }

        // ── DB CART GROUP ─────────────────────────────────────────────
        $cartGroup = CartGroup::where('cart_group_id', $id)->first();

        if (! $cartGroup) {
            abort(404, 'Cart group not found.');
        }

        \Illuminate\Support\Facades\Gate::authorize('modify', $cartGroup);

        session(['modify_cart_group_id' => $id]);

        return redirect()->route('ticket.admission');
    }

    public function cancelModify(): RedirectResponse
    {
        session()->forget('modify_cart_group_id');
        session()->forget('modify_session_group_id');

        return redirect()->route('ticket.cart');
    }
}
