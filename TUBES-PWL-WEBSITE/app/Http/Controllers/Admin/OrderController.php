<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\VisitSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display orders management page
     */
    public function index()
    {
        // Sync visit-date expirations at runtime so the UI is always accurate
        $this->expirePassedOrders();
        // Get stats
        $totalOrders = Order::count();
        $pendingOrders = Order::where('order_status', 'pending_payment')->count();
        $completedOrders = Order::where('order_status', 'completed')->count();

        // Get recent orders
        $recentOrders = Order::with(['user.profile', 'guest', 'tickets', 'payment'])
            ->orderBy('order_date', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                $customerName = 'Guest';
                if ($order->user_id && $order->user?->profile) {
                    $customerName = trim(($order->user->profile->first_name ?? '') . ' ' . ($order->user->profile->last_name ?? ''));
                } elseif ($order->guest_id && $order->guest) {
                    $customerName = trim(($order->guest->first_name ?? '') . ' ' . ($order->guest->last_name ?? ''));
                }

                return [
                    'order_id'      => $order->order_id,
                    'order_code'    => $order->order_code,
                    'customer_name' => $customerName,
                    'ticket_count'  => $order->tickets->count(),
                    'used_count'    => $order->tickets->where('status', 'used')->count(),
                    'total'         => $order->total_amount,
                    'status'        => $order->order_status,
                    'date'          => $order->order_date?->format('Y-m-d H:i'),
                ];
            });

        return view('admin.orders.index', [
            'title'           => 'Orders',
            'subtitle'        => 'Manage all orders',
            'activeNav'       => 'orders',
            'breadcrumbs'     => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Orders', 'isCurrent' => true],
            ],
            'totalOrders'     => $totalOrders,
            'pendingOrders'   => $pendingOrders,
            'completedOrders' => $completedOrders,
            'recentOrders'    => $recentOrders,
        ]);
    }

    /**
     * Search for ticket by QR code, Ticket ID, or Order ID.
     *
     * Parameter priority:
     *  A. order_id (explicit) — sent by "View Order" button; resolves directly to that order,
     *     skipping the QR/ticket_id search entirely. Eliminates ticket_id vs order_id collision.
     *
     *  B. search (string) — sent by QR scanner input:
     *     1. Exact QR code match
     *     2. Exact ticket_id match
     *     3. order_id fallback (only if 1 and 2 both fail)
     */
    public function searchTicket(Request $request): JsonResponse
    {
        // ── PATH A: explicit order_id from the "View Order" table button ──────────
        if ($request->filled('order_id')) {
            $orderId = (int) $request->input('order_id');
            $order   = Order::with(['tickets.ticketAvailability.ticketType'])->find($orderId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => "Order #{$orderId} not found.",
                ], 404);
            }

            // Prefer first valid (unused, non-cancelled) ticket; fall back to first non-cancelled
            $ticket = $order->tickets
                ->whereNotIn('status', ['cancelled', 'expired'])
                ->sortBy('ticket_id')
                ->firstWhere('status', 'valid')
                ?? $order->tickets
                    ->whereNotIn('status', ['cancelled'])
                    ->sortBy('ticket_id')
                    ->first();

            if (!$ticket) {
                return response()->json([
                    'success' => false,
                    'message' => "Order #{$orderId} has no scannable tickets.",
                ], 404);
            }

            $ticket->load(['order', 'ticketAvailability.ticketType']);

            return $this->buildTicketResponse($ticket, $order);
        }

        // ── PATH B: QR scanner / manual ticket search ─────────────────────────────
        $search = trim($request->input('search', ''));

        if (empty($search)) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a ticket ID or scan QR code',
            ], 400);
        }

        // 1. Exact QR code
        $ticket = Ticket::where('qr_code', $search)
            ->with(['order', 'ticketAvailability.ticketType'])
            ->first();

        // 2. Exact ticket_id (numeric only)
        if (!$ticket && is_numeric($search)) {
            $ticket = Ticket::where('ticket_id', (int) $search)
                ->with(['order', 'ticketAvailability.ticketType'])
                ->first();
        }

        // 3. order_id fallback — only when QR and ticket_id both miss
        if (!$ticket && is_numeric($search)) {
            $order = Order::with(['tickets.ticketAvailability.ticketType'])
                ->find((int) $search);

            if ($order) {
                $ticket = $order->tickets
                    ->whereNotIn('status', ['cancelled', 'expired'])
                    ->sortBy('ticket_id')
                    ->firstWhere('status', 'valid')
                    ?? $order->tickets
                        ->whereNotIn('status', ['cancelled'])
                        ->sortBy('ticket_id')
                        ->first();

                if ($ticket) {
                    $ticket->load(['order', 'ticketAvailability.ticketType']);
                }
            }
        }

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found',
            ], 404);
        }

        return $this->buildTicketResponse($ticket, $ticket->order);
    }

    /**
     * Build the standard JSON response for a ticket + its parent order.
     * Used by both PATH A (order_id) and PATH B (QR / ticket_id / order_id fallback).
     */
    private function buildTicketResponse(Ticket $ticket, ?Order $order): JsonResponse
    {
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found for this ticket',
            ], 404);
        }

        // Customer info
        $customerName  = 'Guest';
        $customerEmail = 'N/A';

        if ($order->user_id) {
            $order->load('user.profile');
            if ($order->user?->profile) {
                $customerName = trim(
                    ($order->user->profile->first_name ?? '') . ' ' .
                    ($order->user->profile->last_name  ?? '')
                );
            }
            $customerEmail = $order->user?->email ?? 'N/A';
        } elseif ($order->guest_id) {
            $order->load('guest');
            if ($order->guest) {
                $customerName = trim(
                    ($order->guest->first_name ?? '') . ' ' .
                    ($order->guest->last_name  ?? '')
                );
            }
            $customerEmail = $order->guest?->email ?? 'N/A';
        }

        // All tickets for this order
        $allTickets = $order->tickets->load('ticketAvailability.ticketType')->map(fn ($t) => [
            'ticket_id' => $t->ticket_id,
            'qr_code'   => $t->qr_code,
            'status'    => $t->status,
            'used_at'   => $t->used_at?->format('Y-m-d H:i:s'),
            'type'      => $t->ticketAvailability?->ticketType?->ticket_type_name ?? 'Standard',
            'is_used'   => $t->status === 'used',
        ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'ticket' => [
                    'ticket_id' => $ticket->ticket_id,
                    'qr_code'   => $ticket->qr_code,
                    'status'    => $ticket->status,
                    'used_at'   => $ticket->used_at?->format('Y-m-d H:i:s'),
                    'type'      => $ticket->ticketAvailability?->ticketType?->ticket_type_name ?? 'Standard',
                    'is_used'   => $ticket->status === 'used',
                ],
                'order' => [
                    'order_id'       => $order->order_id,
                    'order_code'     => $order->order_code,
                    'customer_name'  => $customerName ?: 'Guest',
                    'customer_email' => $customerEmail,
                    'order_date'     => $order->order_date?->format('Y-m-d H:i:s'),
                    'total_amount'   => number_format($order->total_amount, 2),
                    'status'         => $order->order_status,
                ],
                'all_tickets' => $allTickets,
            ],
        ]);
    }

    /**
     * Validate (mark as used) a ticket
     */
    public function validateTicket(Request $request): JsonResponse
    {
        $ticketId = $request->input('ticket_id');

        if (!$ticketId) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket ID is required',
            ], 400);
        }

        $ticket = Ticket::find($ticketId);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found',
            ], 404);
        }

        // Check if already used
        if ($ticket->status === 'used') {
            return response()->json([
                'success' => false,
                'message' => 'This ticket has already been used',
                'already_used' => true,
            ], 409);
        }

        // Check if expired (visit date passed)
        if ($ticket->status === 'expired') {
            return response()->json([
                'success' => false,
                'message' => 'This ticket has expired — the visit date has already passed.',
            ], 409);
        }

        // Check if cancelled
        if ($ticket->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'This ticket has been cancelled',
            ], 409);
        }

        // Mark ticket as used
        $ticket->update([
            'status'  => 'used',
            'used_at' => now(),
        ]);

        // Reload all tickets for this order to determine scan progress
        $order = $ticket->order;
        if ($order) {
            $order->load('tickets');
            $allTickets    = $order->tickets;
            $activeTickets = $allTickets->whereNotIn('status', ['cancelled', 'expired']);
            $usedCount     = $allTickets->where('status', 'used')->count();
            $activeCount   = $activeTickets->count();

            // Only mark completed when ALL active (non-cancelled/expired) tickets are used
            if ($activeCount > 0 && $usedCount >= $activeCount) {
                $order->update(['order_status' => 'completed']);
            }
            // Otherwise keep current order_status (paid/etc) — partial is a UI concept
        }

        return response()->json([
            'success' => true,
            'message' => 'Ticket successfully validated and marked as used',
            'data'    => [
                'ticket_id'   => $ticket->ticket_id,
                'status'      => $ticket->status,
                'used_at'     => $ticket->used_at?->format('Y-m-d H:i:s'),
                'used_count'  => isset($order) ? $order->tickets->where('status', 'used')->count() : null,
                'total_count' => isset($order) ? $order->tickets->count() : null,
            ],
        ]);
    }

    /**
     * Show the form for creating a new order
     */
    public function create()
    {
        return view('admin.orders.form', [
            'title' => 'Create Order',
            'subtitle' => 'Add a new order',
            'activeNav' => 'orders',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Orders', 'href' => route('admin.orders.index')],
                ['label' => 'Create', 'isCurrent' => true],
            ],
            'order' => null,
            'isEdit' => false,
            'users' => \App\Models\User::orderBy('email')->get(),
            'guests' => \App\Models\Guest::orderBy('email')->get(),
            'order_types' => ['ticket' => 'Ticket', 'membership' => 'Membership'],
        ]);
    }

    /**
     * Store a newly created order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_code' => 'required|string|unique:orders,order_code',
            'user_id' => 'nullable|exists:users,user_id',
            'guest_id' => 'nullable|exists:guests,guest_id',
            'order_date' => 'required|date',
            'expired_at' => 'nullable|date|after_or_equal:order_date',
            'total_amount' => 'required|numeric|min:0',
            'order_type' => 'required|in:ticket,membership',
        ]);

        // Ensure either user_id or guest_id is provided
        if (!$validated['user_id'] && !$validated['guest_id']) {
            return back()->withInput()->withErrors(['user_id' => 'Either a user or guest must be selected']);
        }

        try {
            $order = Order::create($validated);
            
            return redirect()->route('admin.orders.show', $order->order_id)
                ->with('success', 'Order created successfully');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error creating order: ' . $e->getMessage());
        }
    }

    /**
     * Show the specified order
     */
    public function show(Order $order)
    {
        $order->load(['user.profile', 'guest', 'tickets.ticketAvailability.ticketType', 'payments', 'membership']);
        
        return view('admin.orders.show', [
            'title' => 'Order Details',
            'subtitle' => 'View order information',
            'activeNav' => 'orders',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Orders', 'href' => route('admin.orders.index')],
                ['label' => $order->order_code, 'isCurrent' => true],
            ],
            'order' => $order,
        ]);
    }

    /**
     * Show the form for editing the specified order
     */
    public function edit(Order $order)
    {
        $order->load(['user', 'guest']);
        
        return view('admin.orders.form', [
            'title' => 'Edit Order',
            'subtitle' => 'Modify order details',
            'activeNav' => 'orders',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Orders', 'href' => route('admin.orders.index')],
                ['label' => 'Edit', 'isCurrent' => true],
            ],
            'order' => $order,
            'isEdit' => true,
            'users' => \App\Models\User::orderBy('email')->get(),
            'guests' => \App\Models\Guest::orderBy('email')->get(),
            'order_types' => ['ticket' => 'Ticket', 'membership' => 'Membership'],
        ]);
    }

    /**
     * Update the specified order
     */
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'order_code' => 'required|string|unique:orders,order_code,' . $order->order_id . ',order_id',
            'user_id' => 'nullable|exists:users,user_id',
            'guest_id' => 'nullable|exists:guests,guest_id',
            'order_date' => 'required|date',
            'expired_at' => 'nullable|date|after_or_equal:order_date',
            'total_amount' => 'required|numeric|min:0',
            'order_type' => 'required|in:ticket,membership',
        ]);

        // Ensure either user_id or guest_id is provided
        if (!$validated['user_id'] && !$validated['guest_id']) {
            return back()->withInput()->withErrors(['user_id' => 'Either a user or guest must be selected']);
        }

        try {
            $order->update($validated);
            
            return redirect()->route('admin.orders.show', $order->order_id)
                ->with('success', 'Order updated successfully');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error updating order: ' . $e->getMessage());
        }
    }

    /**
     * Cancel the specified order securely
     */
    public function cancel(Order $order)
    {
        if ($order->order_status === 'completed') {
            return back()->with('error', 'Cannot cancel a completed order as tickets have already been scanned/used.');
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($order) {
                // If it is paid, set payment status to Refunded
                if ($order->order_status === 'paid') {
                    $order->update([
                        'order_status' => 'cancelled',
                    ]);

                    if ($order->payment) {
                        $order->payment->update([
                            'payment_status' => 'Refunded',
                        ]);
                    }
                } else {
                    // For pending_payment or expired, just cancel
                    $order->update([
                        'order_status' => 'cancelled',
                    ]);
                    
                    if ($order->payment && $order->payment->payment_status === 'Pending') {
                        $order->payment->update([
                            'payment_status' => 'Failed',
                        ]);
                    }
                }

                // Mark all tickets as cancelled
                $order->tickets()->update([
                    'status' => 'cancelled',
                ]);
            });

            return redirect()->route('admin.orders.show', $order->order_id)
                ->with('success', 'Order cancelled successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error cancelling order: ' . $e->getMessage());
        }
    }

    /**
     * Delete the specified order (delegates to cancel to preserve database rows)
     */
    public function destroy(Order $order)
    {
        if ($order->order_status === 'completed') {
            return redirect()->route('admin.orders.index')
                ->with('error', 'Cannot delete/cancel a completed order.');
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($order) {
                if ($order->order_status === 'paid') {
                    $order->update(['order_status' => 'cancelled']);
                    if ($order->payment) {
                        $order->payment->update(['payment_status' => 'Refunded']);
                    }
                } else {
                    $order->update(['order_status' => 'cancelled']);
                    if ($order->payment && $order->payment->payment_status === 'Pending') {
                        $order->payment->update(['payment_status' => 'Failed']);
                    }
                }

                $order->tickets()->update(['status' => 'cancelled']);
            });

            return redirect()->route('admin.orders.index')
                ->with('success', 'Order cancelled successfully (preserves history).');
        } catch (\Exception $e) {
            return redirect()->route('admin.orders.index')
                ->with('error', 'Error deleting order: ' . $e->getMessage());
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Runtime visit-date expiry sync
    // Called on every index load so the UI reflects reality immediately.
    // The nightly scheduler is the primary driver; this is a safety net.
    // ──────────────────────────────────────────────────────────────────────────
    private function expirePassedOrders(): void
    {
        // +1 DAY STRATEGY: expire orders only when today >= (visit_date + 1 day).
        // DB query: visit_date < today_date_string is mathematically identical.
        //   visit_date = '2026-05-19', today = '2026-05-20' → '2026-05-19' < '2026-05-20' = TRUE  → expired
        //   visit_date = '2026-05-20', today = '2026-05-20' → '2026-05-20' < '2026-05-20' = FALSE → valid
        $todayDate = now()->toDateString();

        // IDs of visit_schedules whose date has FULLY passed
        $pastScheduleIds = VisitSchedule::where('visit_date', '<', $todayDate)
            ->pluck('visit_schedule_id');

        if ($pastScheduleIds->isEmpty()) {
            return;
        }

        // Orders that are still paid/pending and whose tickets are all past-dated
        // and none were actually used (those should stay completed)
        $ordersToExpire = Order::whereIn('order_status', ['paid', 'pending_payment'])
            ->whereHas('tickets', function ($q) use ($pastScheduleIds) {
                $q->where('status', '!=', 'cancelled')
                    ->whereHas('ticketAvailability', function ($sq) use ($pastScheduleIds) {
                        $sq->whereIn('visit_schedule_id', $pastScheduleIds);
                    });
            })
            ->whereDoesntHave('tickets', function ($q) {
                $q->where('status', 'used');
            })
            ->with(['tickets', 'payment'])
            ->get();

        foreach ($ordersToExpire as $order) {
            DB::transaction(function () use ($order) {
                $order->update(['order_status' => 'expired']);

                $order->tickets()
                    ->whereNotIn('status', ['cancelled', 'used'])
                    ->update(['status' => 'expired']);

                if ($order->payment && strtolower($order->payment->payment_status) === 'pending') {
                    $order->payment->update(['payment_status' => 'Failed']);
                }
            });
        }
    }
}
