<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /**
     * Display payment management dashboard
     */
    public function index(Request $request)
    {
        // Get filter status (All, Pending, Paid, Used)
        $filterStatus = $request->get('status', 'All');
        $perPage = $request->get('per_page', 15);
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $search = $request->get('search');

        // Build query based on filter status
        $query = Payment::with(['order.user.profile', 'order.guest', 'order.tickets.ticketAvailability.ticketType']);

        // Apply status filter case-insensitively
        if ($filterStatus !== 'All' && $filterStatus !== '') {
            $query->whereRaw('LOWER(payment_status) = ?', [strtolower($filterStatus)]);
        }

        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('order.user.profile', function($qu) use ($search) {
                    $qu->where('first_name', 'like', "%{$search}%")
                       ->orWhere('last_name', 'like', "%{$search}%");
                })
                ->orWhereHas('order.user', function($qu) use ($search) {
                    $qu->where('email', 'like', "%{$search}%");
                })
                ->orWhereHas('order.guest', function($qu) use ($search) {
                    $qu->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('order', function($qu) use ($search) {
                    $qu->where('order_code', 'like', "%{$search}%");
                })
                ->orWhere('payment_method', 'like', "%{$search}%")
                ->orWhere('payment_status', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results preserving query string
        $payments = $query->paginate($perPage)->withQueryString();

        // Transform payments data
        $paymentsList = $payments->map(function ($payment) {
            $order = $payment->order;
            $customer = $order->user ?? $order->guest;
            $ticketCount = $order->tickets->where('status', '!=', 'cancelled')->count();
            
            // Get primary ticket type
            $ticketType = $order->tickets
                ->where('status', '!=', 'cancelled')
                ->first()
                ?->ticketAvailability
                ?->ticketType
                ?->name ?? 'N/A';

            return [
                'payment_id' => $payment->payment_id,
                'order_id' => $order->order_id,
                'customer_name' => $customer?->name ?? 'Guest',
                'customer_email' => $customer?->email ?? 'N/A',
                'ticket_type' => $ticketType,
                'ticket_count' => $ticketCount,
                'amount' => $payment->amount,
                'payment_status' => $payment->payment_status,
                'payment_method' => $payment->payment_method ?? 'N/A',
                'ticket_usage_status' => $this->getTicketUsageStatus($order),
                'created_at' => $payment->created_at,
                'updated_at' => $payment->updated_at,
            ];
        });

        // Analytics data
        $analytics = $this->getPaymentAnalytics($filterStatus, $search);

        // Status options for filter
        $statusOptions = ['All', 'Pending', 'Paid', 'Failed', 'Refunded'];

        return view('admin.payment.index', [
            'payments' => $payments,
            'paymentsList' => $paymentsList,
            'filterStatus' => $filterStatus,
            'statusOptions' => $statusOptions,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'perPage' => $perPage,
            
            // Analytics
            'totalPayments' => $analytics['absolute_total'],
            'totalRevenue' => $analytics['revenue'],
            'averageAmount' => $analytics['average'],
            'pendingAmount' => $analytics['pending'],
            'completedCount' => $analytics['completed'],
            'pendingCount' => $analytics['pending_count'],
            'failedCount' => $analytics['failed'],
            'refundedCount' => $analytics['refunded'],
            
            // Meta
            'pageTitle' => 'Payment Management',
            'pageSubtitle' => 'Manage and track payment transactions',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Payment Management', 'isCurrent' => true],
            ],
        ]);
    }

    /**
     * Get ticket usage status
     */
    private function getTicketUsageStatus($order)
    {
        $totalTickets = $order->tickets->where('status', '!=', 'cancelled')->count();
        if ($totalTickets === 0) {
            return 'cancelled';
        }

        $usedTickets = $order->tickets->where('status', 'used')->count();
        $cancelledTickets = $order->tickets->where('status', 'cancelled')->count();

        if ($usedTickets === $totalTickets) {
            return 'used';
        } elseif ($usedTickets > 0) {
            return 'partial';
        } elseif ($cancelledTickets > 0) {
            return 'partially_cancelled';
        }

        return 'pending';
    }

    /**
     * Get payment analytics
     */
    private function getPaymentAnalytics($filterStatus = null, $search = null)
    {
        $query = Payment::query();

        if ($filterStatus && $filterStatus !== 'All') {
            $query->whereRaw('LOWER(payment_status) = ?', [strtolower($filterStatus)]);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('order.user.profile', function($qu) use ($search) {
                    $qu->where('first_name', 'like', "%{$search}%")
                       ->orWhere('last_name', 'like', "%{$search}%");
                })
                ->orWhereHas('order.user', function($qu) use ($search) {
                    $qu->where('email', 'like', "%{$search}%");
                })
                ->orWhereHas('order.guest', function($qu) use ($search) {
                    $qu->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('order', function($qu) use ($search) {
                    $qu->where('order_code', 'like', "%{$search}%");
                })
                ->orWhere('payment_method', 'like', "%{$search}%")
                ->orWhere('payment_status', 'like', "%{$search}%");
            });
        }

        $total = $query->count();
        
        $revenueQuery = clone $query;
        $revenue = $revenueQuery->whereRaw('LOWER(payment_status) = ?', ['paid'])
            ->whereHas('order', function($q) {
                $q->whereRaw('LOWER(order_status) IN (?, ?)', ['paid', 'completed']);
            })
            ->sum('amount');
        
        $average = $total > 0 ? $revenue / $total : 0;
        
        $pendingQuery = clone $query;
        $pending = $pendingQuery->whereRaw('LOWER(payment_status) = ?', ['pending'])->sum('amount');

        $absoluteTotal = Payment::count();
        $absolutePendingCount = Payment::whereRaw('LOWER(payment_status) = ?', ['pending'])->count();
        $absoluteCompletedCount = Payment::whereRaw('LOWER(payment_status) = ?', ['paid'])->count();
        $absoluteFailedCount = Payment::whereRaw('LOWER(payment_status) = ?', ['failed'])->count();
        $absoluteRefundedCount = Payment::whereRaw('LOWER(payment_status) = ?', ['refunded'])->count();

        return [
            'total' => $total,
            'revenue' => $revenue,
            'average' => $average,
            'pending' => $pending,
            'completed' => $absoluteCompletedCount,
            'pending_count' => $absolutePendingCount,
            'failed' => $absoluteFailedCount,
            'refunded' => $absoluteRefundedCount,
            'absolute_total' => $absoluteTotal,
        ];
    }

    /**
     * Get payments as JSON (for AJAX requests)
     */
    public function getData(Request $request)
    {
        $filterStatus = $request->get('status', 'All');
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');

        $query = Payment::with(['order.user.profile', 'order.guest']);

        if ($filterStatus !== 'All' && $filterStatus !== '') {
            $query->whereRaw('LOWER(payment_status) = ?', [strtolower($filterStatus)]);
        }

        $payments = $query->orderBy($sortBy, $sortOrder)
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'payments' => $payments,
            'total' => $payments->count(),
        ]);
    }

    /**
     * Process refund for a paid payment transaction
     */
    public function refund(Request $request, Payment $payment)
    {
        try {
            return DB::transaction(function () use ($payment) {
                // 1. Lock payment for update
                $payment = Payment::lockForUpdate()->find($payment->payment_id);
                $order = $payment->order;

                if (!$order) {
                    return back()->with('error', 'Order not found for this payment.');
                }

                $order->load('tickets.ticketAvailability.ticketType');

                // 2. Validate payment status (must be Paid)
                if (strtolower($payment->payment_status) !== 'paid') {
                    return back()->with('error', 'Only paid transactions can be refunded.');
                }

                // 3. Validate ticket usage: ALL tickets must be unused (i.e. status != used)
                $tickets = $order->tickets;
                $usedCount = $tickets->where('status', 'used')->count();

                if ($usedCount > 0) {
                    return back()->with('error', 'Refund unavailable: some tickets already used.');
                }

                // 4. Update Order status -> cancelled
                $order->update([
                    'order_status' => 'cancelled'
                ]);

                // 5. Update Payment status -> refunded
                $payment->update([
                    'payment_status' => 'Refunded'
                ]);

                // 6. Update Ticket status -> cancelled
                foreach ($tickets as $ticket) {
                    $ticket->update([
                        'status' => 'cancelled'
                    ]);
                }

                // 7. Determine recipient email and name
                $customer = $order->user ?? $order->guest;
                
                // If registered user, get their email and profile details
                if ($order->user) {
                    $recipientEmail = $order->user->email;
                    $recipientName = $order->user->profile 
                        ? trim($order->user->profile->first_name . ' ' . $order->user->profile->last_name) 
                        : ($order->user->name ?? 'Valued Customer');
                } else {
                    // Guest order
                    $recipientEmail = $order->guest ? $order->guest->email : null;
                    $recipientName = $order->guest 
                        ? trim($order->guest->first_name . ' ' . $order->guest->last_name) 
                        : 'Guest Customer';
                }

                // 8. Generate ticket summary for the email
                $ticketCount = $tickets->count();
                $ticketType = $tickets->first()
                    ?->ticketAvailability
                    ?->ticketType
                    ?->name ?? 'Standard Admission';
                $ticketSummary = "{$ticketCount} x {$ticketType}";

                // 9. Send email confirmation if email is set
                if ($recipientEmail) {
                    \Illuminate\Support\Facades\Mail::to($recipientEmail)->send(
                        new \App\Mail\RefundConfirmationMail(
                            $order,
                            $recipientName,
                            $payment->amount,
                            now()->format('M d, Y H:i:s'),
                            $ticketSummary
                        )
                    );
                }

                return back()->with('success', 'Refund processed successfully and confirmation email sent.');
            });
        } catch (\Exception $e) {
            return back()->with('error', 'Refund failed to process: ' . $e->getMessage());
        }
    }
}
