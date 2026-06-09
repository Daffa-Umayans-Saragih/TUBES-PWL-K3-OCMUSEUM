<?php
namespace App\Http\Controllers;

use App\Mail\OrderSuccessMail;
use App\Models\Cart;
use App\Models\Guest;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\TicketAvailability;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    private const PAYMENT_TIMEOUT_MINUTES = 20;

    public function checkout(Request $request): RedirectResponse
    {
        // dd('checkout reached');

        $userId  = Auth::id();
        $guestId = session('guest_id');

        if (! $userId && ! $guestId) {
            abort(403, 'User or guest identity not found. Please add items to cart first.');
        }

        $context = $this->resolveCartContext($request);
        $cart    = $context['cart'];

        if (! $cart || $cart->cartGroups->isEmpty()) {
            return redirect()->route('ticket.cart')->with('error', 'Cart is empty');
        }

        // Validate that all groups have items
        foreach ($cart->cartGroups as $group) {
            if ($group->cartItems->isEmpty()) {
                return redirect()->route('ticket.cart')->with('error', 'One or more cart groups are empty.');
            }
        }

        // --- IDEMPOTENCY: Reuse existing pending order ---
        $existingOrder = Order::query()
            ->where(function ($query) use ($userId, $guestId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('guest_id', $guestId);
                }

            })
            ->where('expired_at', '>', now())
            ->whereHas('payment', fn($q) => $q->where('payment_status', 'Pending'))
            ->latest('order_date')
            ->first();

        if ($existingOrder) {
            return redirect()->route('checkout.payments', $existingOrder->order_id);
        }

        try {
            $order = DB::transaction(function () use ($userId, $guestId, $cart) {
                $cart->lockForUpdate();
                $cartItems = $cart->cartGroups->flatMap->cartItems;

                $orderTotalAmount = 0.0;
                $disabilitiesQty = 0;
                $companionQty = 0;

                foreach ($cartItems as $item) {
                    $availability = TicketAvailability::with(['ticketType', 'visitSchedule'])->lockForUpdate()->find($item->ticket_availability_id);
                    if ($availability) {
                        $totalStock = $availability->capacity_limit ?? $availability->visitSchedule->capacity_limit;
                        $sold = Ticket::where('ticket_availability_id', $availability->ticket_availability_id)
                            ->where('status', '!=', 'cancelled')
                            ->whereHas('order', function ($query) {
                                $query->whereRaw('LOWER(order_status) IN (?, ?)', ['paid', 'completed']);
                            })
                            ->count();
                        $remaining = max(0, $totalStock - $sold);
                        
                        if ($item->quantity > $remaining) {
                            $ticketName = $availability->ticketType->ticket_type_name;
                            throw new \Exception("Insufficient ticket stock. Only {$remaining} ticket(s) remaining for {$ticketName}.");
                        }

                        $orderTotalAmount += ((float) ($availability->ticketType->getEffectivePrice(Auth::user()) ?? 0)) * (int) $item->quantity;
                        $typeName = strtolower($availability->ticketType->ticket_type_name);
                        if ($typeName === 'disabilities') {
                            $disabilitiesQty += (int) $item->quantity;
                        } elseif ($typeName === 'companion') {
                            $companionQty += (int) $item->quantity;
                        }
                    }
                }

                if ($companionQty > $disabilitiesQty) {
                    throw new \Exception("Companion quantity ({$companionQty}) must not exceed disabilities quantity ({$disabilitiesQty}).");
                }

                // Defensive normalization for XOR constraint
                if ($userId && $guestId) {
                    // If both are set, prefer user session
                    $guestId = null;
                } elseif (! $userId && ! $guestId) {
                    throw new \Exception('User or guest identity required for order.');
                }

                $order = Order::create([
                    'order_code'   => (string) Str::uuid(),
                    'user_id'      => $userId,
                    'guest_id'     => $guestId,
                    'order_date'   => now(),
                    'expired_at'   => now()->addMinutes(self::PAYMENT_TIMEOUT_MINUTES),
                    'total_amount' => $orderTotalAmount,
                    'order_status' => 'pending_payment',
                ]);

                Payment::create([
                    'order_id'       => $order->order_id,
                    'payment_method' => 'Credit Card',
                    'amount'         => $orderTotalAmount,
                    'payment_status' => 'Pending',
                ]);

                // STRICT: DO NOT create tickets here
                // STRICT: DO NOT delete cart here

                return $order;
            });
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('checkout.payments', $order->order_id);
    }

    public function paymentPage(Order $order): View | RedirectResponse
    {
        // Ownership validation
        \Illuminate\Support\Facades\Gate::authorize('view', $order);

        // Enforce timeout using backend time and persist cancellation when needed.
        $this->expireOrderIfTimedOut($order);

        $order->refresh();
        $order->load([
            'payment',
            'tickets.ticketAvailability.visitSchedule.location',
            'tickets.ticketAvailability.ticketType',
            'orderDetails.ticket.ticketAvailability.ticketType',
            'user',
            'guest',
        ]);

        if ($order->order_status === 'expired') {
            return view('ordinary.checkout.payments.payments', [
                'order'        => $order,
                'title'        => 'Payment Expired',
                'isExpired'    => true,
                'errorMessage' => 'PAYMENT EXPIRED',
            ]);
        }

        if ($order->order_status === 'cancelled') {
            return view('ordinary.checkout.payments.payments', [
                'order'        => $order,
                'title'        => 'Order Cancelled',
                'isExpired'    => true,
                'errorMessage' => 'ORDER CANCELLED',
            ]);
        }

        if ($order->order_status === 'completed') {
            return view('ordinary.checkout.payments.payments', [
                'order'        => $order,
                'title'        => 'Ticket Redeemed',
                'isExpired'    => true,
                'errorMessage' => 'TICKET ALREADY REDEEMED',
            ]);
        }

        if ($order->order_status === 'paid' || ($order->payment && $order->payment->payment_status === 'Paid')) {
            return view('ordinary.checkout.payments.payments', [
                'order'          => $order,
                'title'          => 'Payment Completed',
                'isExpired'      => false,
                'successMessage' => 'PAYMENT ALREADY COMPLETED',
            ]);
        }

        $isExpired  = $this->isOrderExpiredState($order);
        if ($isExpired) {
            $order->update(['order_status' => 'expired']);
            return view('ordinary.checkout.payments.payments', [
                'order'        => $order,
                'title'        => 'Payment Expired',
                'isExpired'    => true,
                'errorMessage' => 'PAYMENT EXPIRED',
            ]);
        }

        $deadlineAt = null;
        if ($order->payment && $order->payment->payment_status === 'Pending') {
            $deadlineAt = $this->paymentDeadlineAt($order);
        }

        return view('ordinary.checkout.payments.payments', [
            'order'             => $order,
            'title'             => 'Payment Confirmation',
            'paymentDeadlineAt' => $deadlineAt,
            'isExpired'         => false,
        ]);
    }

    public function pay(Request $request, Order $order)
    {
        // Ownership validation
        \Illuminate\Support\Facades\Gate::authorize('view', $order);

        $this->expireOrderIfTimedOut($order);
        $order->refresh();
        $order->load('payment');

        if ($this->isOrderExpiredState($order)) {
            return redirect()->route('home')->with('error', 'Cart expired');
        }

        if ($order->payment && $order->payment->payment_status === 'Paid') {
            return redirect()->route('ticket.checkout.success', $order->order_id)
                ->with('info', 'This order has already been paid.');
        }

        $billing = null;
        if (! Auth::check()) {
            $request->validate([
                'first_name'  => 'required',
                'last_name'   => 'required',
                'address'     => 'required',
                'city'        => 'required',
                'state'       => 'required',
                'postal_code' => 'required',
                'country'     => 'required',
            ]);

            $billing = [
                'first_name'  => $request->first_name,
                'last_name'   => $request->last_name,
                'address'     => $request->address,
                'city'        => $request->city,
                'state'       => $request->state,
                'postal_code' => $request->postal_code,
                'country'     => $request->country,
            ];
        }

        try {
            $userId  = $order->user_id;
            $guestId = $order->guest_id;
            $isMembershipOrder = $order->payment?->payment_method === 'Membership';

            DB::transaction(function () use ($order, $userId, $guestId, $isMembershipOrder) {
                $payment = Payment::where('order_id', $order->order_id)
                    ->lockForUpdate()
                    ->first();

                if (! $payment || $payment->payment_status !== 'Pending') {
                    return;
                }

                $payment->update([
                    'payment_status' => 'Paid',
                    'paid_at'        => now(),
                ]);

                $order->update([
                    'order_status' => 'paid',
                ]);

                if ($isMembershipOrder) {
                    return;
                }

                // Idempotency: DO NOT create again if tickets already exist
                if ($order->tickets()->exists()) {
                    return;
                }

                // Load Cart
                // Cart must act as temporary snapshot.
                $cartQuery = Cart::with('cartGroups.cartItems');
                if ($userId) {
                    $cart = $cartQuery->where('user_id', $userId)->first();
                } elseif ($guestId) {
                    $cart = $cartQuery->where('guest_id', $guestId)->first();
                } else {
                    $cart = $cartQuery->whereKey(session('cart_id'))->first();
                }

                if (! $cart) {
                    throw new \Exception('Cart not found. Cannot generate tickets.');
                }

                $cartItems = $cart->cartGroups->flatMap->cartItems;

                // Generate Tickets with Final Hard Validation
                foreach ($cartItems as $item) {
                    $availability = TicketAvailability::with(['ticketType', 'visitSchedule'])->lockForUpdate()->find($item->ticket_availability_id);
                    if ($availability) {
                        $totalStock = $availability->capacity_limit ?? $availability->visitSchedule->capacity_limit;
                        $sold = Ticket::where('ticket_availability_id', $availability->ticket_availability_id)
                            ->where('status', '!=', 'cancelled')
                            ->whereHas('order', function ($query) {
                                $query->whereRaw('LOWER(order_status) IN (?, ?)', ['paid', 'completed']);
                            })
                            ->count();
                        $remaining = max(0, $totalStock - $sold);
                        
                        if ($item->quantity > $remaining) {
                            $ticketName = $availability->ticketType->ticket_type_name;
                            throw new \Exception("Purchase failed: Only {$remaining} ticket(s) remaining for {$ticketName}.");
                        }
                    }

                    // Create one order_detail row per cart line item
                    // (quantity = how many tickets of this type were purchased)
                    $createdTicketIds = collect();
                    for ($i = 0; $i < $item->quantity; $i++) {
                        $ticket = Ticket::create([
                            'order_id'               => $order->order_id,
                            'ticket_availability_id' => $item->ticket_availability_id,
                            'qr_code'                => (string) Str::uuid(),
                            'status'                 => 'valid',
                        ]);
                        $createdTicketIds->push($ticket->ticket_id);
                    }

                    $originalPrice = (float) ($availability->ticketType->base_price ?? 0);
                    $unitPrice = (float) ($availability->ticketType->getEffectivePrice($order->user) ?? 0);
                    $discountAmount = max(0, $originalPrice - $unitPrice);

                    // Insert order_details row for this cart line item
                    // Stores the first ticket_id of the group as the representative reference.
                    OrderDetail::create([
                        'order_id'        => $order->order_id,
                        'ticket_id'       => $createdTicketIds->first(),
                        'quantity'        => $item->quantity,
                        'original_price'  => $originalPrice,
                        'unit_price'      => $unitPrice,
                        'discount_amount' => $discountAmount,
                    ]);
                }

                // Delete cart AFTER ticket creation
                $cart->cartGroups()->delete();
                $cart->delete();
            });
        } catch (\Exception $e) {
            Log::error('Payment settlement failed', ['order_id' => $order->order_id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Payment failed to process: ' . $e->getMessage());
        }

        if ($order->payment?->payment_method === 'Membership') {
            $order->load(['guest']);

            return redirect()->route('ticket.checkout.success', $order->order_id)
                ->with('success', 'Membership payment successful.');
        }

        // STEP 1: Load Order Relations
        $order->load([
            'tickets.ticketAvailability.ticketType',
            'tickets.ticketAvailability.visitSchedule',
            'user',
            'guest',
        ]);

        // STEP 2: Send Email
        $recipientEmail = $order->user?->email ?? $order->guest?->email;

        if ($recipientEmail) {
            try {
                \Illuminate\Support\Facades\Mail::to($recipientEmail)->send(new OrderSuccessMail($order, $billing));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Order success email failed', [
                    'order_id' => $order->order_id, 
                    'to'       => $recipientEmail,
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        return redirect()->route('ticket.checkout.success', $order->order_id)
            ->with('success', 'Payment successful! Your tickets have been generated.');
    }

    public function success(Order $order): View
    {
        // Ownership validation
        \Illuminate\Support\Facades\Gate::authorize('view', $order);

        $order->load([
            'payment',
            'tickets.ticketAvailability.visitSchedule.location',
            'tickets.ticketAvailability.ticketType',
            'user',
            'guest',
        ]);

        return view('ordinary.checkout.payments.success', [
            'order' => $order,
            'title' => 'Booking Confirmed',
        ]);
    }

    private function resolveCartContext(Request $request): array
    {
        $userId  = Auth::id();
        $guestId = $userId ? null : ($request->integer('guest_id') ?: session('guest_id'));

        $cartQuery = Cart::query()->with([
            'cartGroups.cartItems.ticketAvailability.ticketType',
            'cartGroups.cartItems.ticketAvailability.visitSchedule.location',
        ]);

        if ($userId) {
            $cart = $cartQuery->where('user_id', $userId)->where('expires_at', '>', now())->first();
        } elseif ($guestId) {
            $cart = $cartQuery->where('guest_id', $guestId)->where('expires_at', '>', now())->first();
        } elseif (session()->has('cart_id')) {
            $cart = $cartQuery->whereKey(session('cart_id'))->where('expires_at', '>', now())->first();
        } else {
            $cart = null;
        }

        return [
            'userId'  => $userId,
            'guestId' => $guestId,
            'cart'    => $cart,
        ];
    }

    private function resolveCustomerDefaults(?int $userId, ?int $guestId): array
    {
        $name  = '';
        $email = '';
        $user  = Auth::user();

        if ($userId && $user) {
            $email   = (string) $user->email;
            $profile = $user->profile;
            if ($profile) {
                $name = trim($profile->first_name . ' ' . $profile->last_name);
            }
        }

        if ($guestId) {
            $guest = Guest::find($guestId);

            if ($guest) {
                $name  = trim($guest->first_name . ' ' . $guest->last_name);
                $email = $guest->email;
            }
        }

        return [
            'name'  => $name,
            'email' => $email,
        ];
    }

    private function flattenCartItems(?Cart $cart): Collection
    {
        if (! $cart) {
            return collect();
        }

        return $cart->cartGroups
            ->flatMap(fn($group) => $group->cartItems)
            ->values();
    }

    private function resolveCheckoutErrorMessage(\Throwable $e): string
    {
        $knownMessages = [
            'Cart is empty.',
            'Guest session not found.',
            'Invalid ticket availability found in cart.',
            'Visit schedule not found for one or more cart items.',
            'Overbooking detected for selected visit schedule.',
        ];

        if (in_array($e->getMessage(), $knownMessages, true)) {
            return $e->getMessage();
        }

        return 'We could not complete checkout right now. Please try again.';
    }

    private function paymentStartAt(Order $order): Carbon
    {
        if ($order->payment?->created_at) {
            return $order->payment->created_at->copy();
        }

        if ($order->order_date) {
            return $order->order_date->copy();
        }

        return $order->created_at ? $order->created_at->copy() : now();
    }

    private function paymentDeadlineAt(Order $order): Carbon
    {
        return $this->paymentStartAt($order)->addMinutes(self::PAYMENT_TIMEOUT_MINUTES);
    }

    private function isOrderExpiredState(Order $order): bool
    {
        if ($order->order_status === 'expired') {
            return true;
        }

        $paymentStatus = $order->payment?->payment_status;

        if ($paymentStatus === 'Failed') {
            return true;
        }

        if ($paymentStatus === 'Pending' && now()->greaterThanOrEqualTo($this->paymentDeadlineAt($order))) {
            return true;
        }

        return false;
    }

    private function expireOrderIfTimedOut(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $lockedOrder = Order::with('payment')
                ->lockForUpdate()
                ->find($order->order_id);

            if (! $lockedOrder || ! $lockedOrder->payment) {
                return;
            }

            if ($lockedOrder->payment->payment_status !== 'Pending') {
                return;
            }

            if (now()->lt($this->paymentDeadlineAt($lockedOrder))) {
                return;
            }

            $lockedOrder->payment->update([
                'payment_status' => 'Failed',
            ]);

            $lockedOrder->update([
                'expired_at' => now(),
                'order_status' => 'expired',
            ]);
        });
    }
}
