<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\TicketAvailability;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function create()
    {
        $ticketAvailabilities = TicketAvailability::with(['ticketType', 'visitSchedule.location'])->get();

        $groupedTickets = $ticketAvailabilities->groupBy('visitSchedule.location_id')->map(function ($locationTickets) {
            return [
                'location'   => optional(optional($locationTickets->first()->visitSchedule)->location),
                'categories' => $locationTickets->groupBy(function ($item) {
                    return optional($item->ticketType)->name ?? 'General';
                })->map(function ($categoryTickets) {
                    return $categoryTickets->values();
                }),
            ];
        });

        return view('ordinary.order.create.create', compact('groupedTickets'));
    }

    public function store(StoreOrderRequest $request)
    {
        try {
            $validated = $request->validated();

            $quantity = (int) $validated['quantity'];
            $userId   = Auth::id();
            $guestId  = $userId ? null : session('guest_id');

            if (! $userId && ! $guestId) {
                return back()->with('error', 'Guest or authenticated user context is required.')->withInput();
            }

            $ticketAvailability = TicketAvailability::with('ticketType')
                ->findOrFail($validated['ticket_availability_id']);

            if (! $ticketAvailability) {
                return back()->with('error', 'Ticket availability is required.')->withInput();
            }

            $order = DB::transaction(function () use ($ticketAvailability, $quantity, $userId, $guestId) {
                $totalAmount = ((float) ($ticketAvailability->ticketType?->base_price ?? 0)) * $quantity;

                $order = Order::create([
                    'order_code'   => (string) Str::uuid(),
                    'user_id'      => $userId,
                    'guest_id'     => $guestId,
                    'order_date'   => now(),
                    'expired_at'   => now()->addMinutes(30),
                    'total_amount' => $totalAmount,
                ]);

                for ($i = 0; $i < $quantity; $i++) {
                    Ticket::create([
                        'order_id'               => $order->order_id,
                        'ticket_availability_id' => $ticketAvailability->ticket_availability_id,
                        'qr_code'                => (string) Str::uuid(),
                        'status'                 => 'valid',
                        'used_at'                => null,
                    ]);
                }

                Payment::create([
                    'order_id'       => $order->order_id,
                    'payment_method' => 'manual',
                    'amount'         => $totalAmount,
                    'payment_status' => 'Pending',
                    'paid_at'        => null,
                ]);

                return $order;
            });

            return redirect()->route('order.show.detail', $order->order_id)
                ->with('success', 'Order created successfully. Please proceed to payment.');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'An error occurred while creating your order. Please try again.')
                ->withInput();
        }
    }

    public function index()
    {
        $userId  = Auth::id();
        $guestId = session('guest_id');

        if (! $userId && ! $guestId) {
            return redirect()->route('ticket.admission')->with('error', 'Authentication required to view orders.');
        }

        $query = Order::with('payment');

        if ($userId && $guestId) {
            $query->where(function ($q) use ($userId, $guestId) {
                $q->where('user_id', $userId)->orWhere('guest_id', $guestId);
            });
        } elseif ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('guest_id', $guestId);
        }

        $orders = $query->orderBy('order_date', 'desc')->get();

        return view('ordinary.order.show.show', [
            'orders' => $orders,
            'mode'   => 'list',
        ]);
    }

    public function show(Order $order)
    {
        \Illuminate\Support\Facades\Gate::authorize('view', $order);

        $order->load([
            'payment',
            'membership.user',
            'tickets.ticketAvailability.ticketType',
            'tickets.ticketAvailability.visitSchedule.location',
        ]);

        return view('ordinary.order.show.show', [
            'order' => $order,
            'mode'  => 'detail',
        ]);
    }
}
