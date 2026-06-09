<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\TicketAvailability;
use App\Models\TicketType;
use App\Models\User;
use App\Models\VisitSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketAnalyticsController extends Controller
{
    /**
     * Display main ticket analytics dashboard
     */
    public function index(Request $request)
    {
        // Get date range from request (default: last 30 days)
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate   = $request->get('end_date', now()->format('Y-m-d'));
        $dateFrom  = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
        $dateTo    = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();

        // ==========================================
        // 1. OVERVIEW ANALYTICS
        // ==========================================

        // Total Revenue Today
        $totalRevenueToday = Payment::whereHas('order', function ($query) {
            $query->whereIn('order_status', ['paid', 'completed']);
        })->whereDate('created_at', today())->sum('amount');

        // Total Revenue This Month
        $totalRevenueMonth = Payment::whereHas('order', function ($query) {
            $query->whereIn('order_status', ['paid', 'completed']);
        })->whereMonth('created_at', now()->month)
          ->whereYear('created_at', now()->year)
          ->sum('amount');

        // Tickets Sold Today
        $ticketsSoldToday = Ticket::where('status', '!=', 'cancelled')
            ->whereDate('created_at', today())
            ->whereHas('order', function ($query) {
                $query->whereRaw('LOWER(order_status) IN (?, ?)', ['paid', 'completed']);
            })
            ->count();

        // Total Visitors (Unique users + guests from completed orders)
        $totalVisitors = DB::table('orders')
            ->join('tickets', 'orders.order_id', '=', 'tickets.order_id')
            ->where('tickets.status', '!=', 'cancelled')
            ->whereBetween('tickets.created_at', [$dateFrom, $dateTo])
            ->select(DB::raw('COUNT(DISTINCT CASE
                WHEN orders.user_id IS NOT NULL THEN orders.user_id
                WHEN orders.guest_id IS NOT NULL THEN orders.guest_id
                END) as total'))
            ->value('total') ?? 0;

        // Pending Payments
        $pendingPayments = Payment::whereHas('order', function ($query) {
            $query->where('order_status', 'pending_payment');
        })->whereBetween('created_at', [$dateFrom, $dateTo])->count();

        // Conversion Rate (tickets sold / total orders)
        $totalOrders     = Order::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $successfulOrders = Order::whereIn('order_status', ['paid', 'completed'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->count();
        $conversionRate = $totalOrders > 0 ? round(($successfulOrders / $totalOrders) * 100, 2) : 0;

        // Active Visit Sessions
        $activeVisitSessions = VisitSchedule::where('visit_date', '>=', today())
            ->get()
            ->count();

        // Sold Out Sessions
        $soldOutSessions = VisitSchedule::where('visit_date', '>=', today())
            ->get()
            ->count();

        // ==========================================
        // 2. REVENUE ANALYTICS
        // ==========================================

        // Revenue Trend (daily for last 30 days)
        $revenueTrend = collect(range(29, 0))->map(function ($day) use ($dateFrom) {
            $date    = $dateFrom->clone()->addDays($day);
            $revenue = Payment::whereHas('order', function ($query) {
                $query->whereIn('order_status', ['paid', 'completed']);
            })->whereDate('created_at', $date)->sum('amount');
            return [
                'date'   => $date->format('M d'),
                'amount' => $revenue,
            ];
        })->values();

        // Monthly Revenue Comparison (last 12 months)
        $monthlyRevenue = collect(range(11, 0))->map(function ($month) {
            $date    = now()->subMonths($month);
            $revenue = Payment::whereHas('order', function ($query) {
                $query->whereIn('order_status', ['paid', 'completed']);
            })->whereMonth('created_at', $date->month)
              ->whereYear('created_at', $date->year)
              ->sum('amount');
            return [
                'month'  => $date->format('M Y'),
                'amount' => $revenue,
            ];
        })->values();

        // Payment Status Breakdown
        $paymentStatusBreakdown = DB::table('payments')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('payment_status')
            ->select('payment_status', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total_amount'))
            ->get()
            ->keyBy('payment_status');

        // ==========================================
        // 3. TICKET SALES ANALYTICS
        // ==========================================

        // Best Selling Tickets (by ticket type)
        $bestSellingTickets = Ticket::whereHas('order', function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        })
            ->where('status', '!=', 'cancelled')
            ->with('ticketAvailability.ticketType')
            ->select('ticket_availability_id', DB::raw('COUNT(*) as total_sold'))
            ->groupBy('ticket_availability_id')
            ->orderBy('total_sold', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($ticket) {
                $typeRevenue = Payment::whereHas('order', function ($query) use ($ticket) {
                    $query->whereIn('order_status', ['paid', 'completed'])
                        ->whereHas('tickets', function ($subQ) use ($ticket) {
                            $subQ->where('ticket_availability_id', $ticket->ticket_availability_id);
                        });
                })->sum('amount');

                return [
                    'type_name'  => $ticket->ticketAvailability->ticketType->name ?? 'Unknown',
                    'total_sold' => $ticket->total_sold,
                    'revenue'    => $typeRevenue,
                ];
            });

        // Ticket Sales Trend
        $ticketSalesTrend = collect(range(6, 0))->map(function ($day) {
            $date  = now()->subDays($day);
            $sales = Ticket::whereDate('created_at', $date)
                ->where('status', '!=', 'cancelled')
                ->whereHas('order', function ($query) {
                    $query->whereRaw('LOWER(order_status) IN (?, ?)', ['paid', 'completed']);
                })
                ->count();
            return [
                'date'  => $date->format('D'),
                'sales' => $sales,
            ];
        })->values();

        // Ticket Type Distribution
        $ticketTypeDistribution = DB::table('tickets')
            ->join('ticket_availability', 'tickets.ticket_availability_id', '=', 'ticket_availability.ticket_availability_id')
            ->join('ticket_types', 'ticket_availability.ticket_type_id', '=', 'ticket_types.ticket_type_id')
            ->join('orders', 'tickets.order_id', '=', 'orders.order_id')
            ->whereBetween('tickets.created_at', [$dateFrom, $dateTo])
            ->where('tickets.status', '!=', 'cancelled')
            ->whereRaw('LOWER(orders.order_status) IN (?, ?)', ['paid', 'completed'])
            ->groupBy('ticket_types.ticket_type_id', 'ticket_types.ticket_type_name')
            ->select('ticket_types.ticket_type_name as name', DB::raw('COUNT(tickets.ticket_id) as count'))
            ->get()
            ->map(function ($type) {
                return [
                    'name'       => $type->name,
                    'count'      => $type->count,
                    'percentage' => 0,
                ];
            })
            ->filter(fn($item) => $item['count'] > 0);

        // Calculate percentages
        $totalTickets = $ticketTypeDistribution->sum('count');
        if ($totalTickets > 0) {
            $ticketTypeDistribution = $ticketTypeDistribution->map(function ($item) use ($totalTickets) {
                $item['percentage'] = round(($item['count'] / $totalTickets) * 100, 2);
                return $item;
            });
        }

        // ==========================================
        // 4. CAPACITY & VISITOR ANALYTICS
        // ==========================================

        // Capacity Overview (all scheduled sessions)
        $capacityOverview = VisitSchedule::where('visit_date', '>=', now()->subDays(30))
            ->with(['ticketAvailabilities', 'location'])
            ->orderBy('visit_date')
            ->get()
            ->map(function ($schedule) {
                $totalTickets = 0;
                foreach ($schedule->ticketAvailabilities as $availability) {
                    $ticketCount  = Ticket::where('ticket_availability_id', $availability->ticket_availability_id)
                        ->where('status', '!=', 'cancelled')
                        ->whereHas('order', function ($query) {
                            $query->whereRaw('LOWER(order_status) IN (?, ?)', ['paid', 'completed']);
                        })
                        ->count();
                    $totalTickets += $ticketCount;
                }
                $capacity = 500;
                return [
                    'date'           => $schedule->visit_date->format('Y-m-d'),
                    'location'       => optional($schedule->location)->name ?? 'N/A',
                    'capacity'       => $capacity,
                    'sold'           => $totalTickets,
                    'remaining'      => max(0, $capacity - $totalTickets),
                    'occupancy_rate' => round(($totalTickets / $capacity) * 100, 2),
                    'is_sold_out'    => $totalTickets >= $capacity,
                ];
            });

        // ==========================================
        // 5. VISITOR ANALYTICS
        // ==========================================

        // Repeat Visitors (count of visitors with more than 1 order)
        $driverName = DB::connection()->getDriverName();
        $concatUser = $driverName === 'sqlite' ? "'user_' || orders.user_id" : "CONCAT('user_', orders.user_id)";
        $concatGuest = $driverName === 'sqlite' ? "'guest_' || orders.guest_id" : "CONCAT('guest_', orders.guest_id)";

        $repeatVisitors = DB::table('orders')
            ->join('tickets', 'orders.order_id', '=', 'tickets.order_id')
            ->whereBetween('tickets.created_at', [$dateFrom, $dateTo])
            ->select(DB::raw("CASE
                WHEN orders.user_id IS NOT NULL THEN {$concatUser}
                WHEN orders.guest_id IS NOT NULL THEN {$concatGuest}
                END as visitor_key"),
                DB::raw('COUNT(DISTINCT orders.order_id) as order_count'))
            ->groupBy('visitor_key')
            ->havingRaw('COUNT(DISTINCT orders.order_id) > 1')
            ->count();

        // Guest vs Registered
        $visitorTypes = DB::table('orders')
            ->join('tickets', 'orders.order_id', '=', 'tickets.order_id')
            ->whereBetween('tickets.created_at', [$dateFrom, $dateTo])
            ->where('tickets.status', '!=', 'cancelled')
            ->select(
                DB::raw("CASE WHEN orders.user_id IS NOT NULL THEN 'registered' ELSE 'guest' END as type"),
                DB::raw('COUNT(DISTINCT CASE
                    WHEN orders.user_id IS NOT NULL THEN orders.user_id
                    WHEN orders.guest_id IS NOT NULL THEN orders.guest_id
                    END) as count')
            )
            ->groupBy('type')
            ->pluck('count', 'type');

        // ==========================================
        // 6. QR TICKET VALIDATION ANALYTICS
        // ==========================================

        // Ticket Status Breakdown
        $ticketStatusBreakdown = Ticket::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // Validation Success Rate
        $usedTickets = Ticket::where('status', 'used')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->count();
        $totalTicketCount = Ticket::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', '!=', 'cancelled')
            ->whereHas('order', function ($query) {
                $query->whereRaw('LOWER(order_status) IN (?, ?)', ['paid', 'completed']);
            })
            ->count();
        $validationSuccessRate = $totalTicketCount > 0 ? round(($usedTickets / $totalTicketCount) * 100, 2) : 0;

        // ==========================================
        // 7. LATEST TRANSACTIONS
        // ==========================================

        // Latest Transactions
        $latestTransactions = Order::with(['user.profile', 'guest', 'payment', 'tickets.ticketAvailability.ticketType'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                $ticketCount = $order->tickets->where('status', '!=', 'cancelled')->count();
                $usedCount   = $order->tickets->where('status', 'used')->count();

                // Determine exact role according to DB reality
                $role = 'Guest';
                if ($order->user_id) {
                    $userRole = $order->user?->role_admin;
                    $role = $userRole ? ucfirst($userRole) : ($order->user?->is_admin ? 'Admin' : 'User');
                }

                return [
                    'order_id'       => $order->order_id,
                    'user_name'      => $order->user?->name ?? ($order->guest?->name ?? 'Guest'),
                    'user_email'     => $order->user?->email ?? ($order->guest?->email ?? 'N/A'),
                    'user_role'      => $role,
                    'ticket_count'   => $ticketCount,
                    'used_count'     => $usedCount,
                    'scan_progress'  => $ticketCount > 0 ? "{$usedCount}/{$ticketCount}" : '0/0',
                    'total_amount'   => $order->total_amount,
                    'payment_status' => $order->payment?->payment_status ?? 'pending',
                    'order_status'   => $order->order_status,
                    'created_at'     => $order->created_at,
                ];
            });

        // Latest Payments
        $latestPayments = Payment::with('order.user.profile', 'order.guest')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($payment) {
                $role = 'Guest';
                if ($payment->order?->user_id) {
                    $userRole = $payment->order->user->role_admin;
                    $role = $userRole ? ucfirst($userRole) : ($payment->order->user->is_admin ? 'Admin' : 'User');
                }

                return [
                    'id'         => $payment->payment_id,
                    'order_id'   => $payment->order_id,
                    'user_name'  => $payment->order?->user?->name ?? ($payment->order?->guest?->name ?? 'Guest'),
                    'user_role'  => $role,
                    'amount'     => $payment->amount,
                    'status'     => $payment->payment_status,
                    'method'     => $payment->payment_method ?? 'N/A',
                    'created_at' => $payment->created_at,
                ];
            });

        // Return view with all analytics
        return view('admin.ticket-analytics.index', [
            'title'                  => 'Ticket Sales Analytics',
            'subtitle'               => 'Comprehensive museum ticketing analytics and insights',
            'activeNav'              => 'ticket-analytics',
            'breadcrumbs'            => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Ticket Analytics', 'isCurrent' => true],
            ],

            // Overview
            'totalRevenueToday'      => $totalRevenueToday,
            'totalRevenueMonth'      => $totalRevenueMonth,
            'ticketsSoldToday'       => $ticketsSoldToday,
            'totalVisitors'          => $totalVisitors,
            'pendingPayments'        => $pendingPayments,
            'conversionRate'         => $conversionRate,
            'activeVisitSessions'    => $activeVisitSessions,
            'soldOutSessions'        => $soldOutSessions,

            // Revenue
            'revenueTrend'           => $revenueTrend,
            'monthlyRevenue'         => $monthlyRevenue,
            'paymentStatusBreakdown' => $paymentStatusBreakdown,

            // Tickets
            'bestSellingTickets'     => $bestSellingTickets,
            'ticketSalesTrend'       => $ticketSalesTrend,
            'ticketTypeDistribution' => $ticketTypeDistribution,

            // Capacity
            'capacityOverview'       => $capacityOverview,

            // Visitors
            'repeatVisitors'         => $repeatVisitors,
            'visitorTypes'           => $visitorTypes,

            // Validation
            'ticketStatusBreakdown'  => $ticketStatusBreakdown,
            'validationSuccessRate'  => $validationSuccessRate,

            // Transactions
            'latestTransactions'     => $latestTransactions,
            'latestPayments'         => $latestPayments,

            // Date range
            'startDate'              => $startDate,
            'endDate'                => $endDate,
        ]);
    }

    /**
     * Get analytics data as JSON (for AJAX/API requests)
     */
    public function getAnalyticsData(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate   = $request->get('end_date', now()->format('Y-m-d'));
        $dateFrom  = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
        $dateTo    = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();

        // Get revenue trend
        $revenueTrend = collect(range(29, 0))->map(function ($day) use ($dateFrom) {
            $date    = $dateFrom->clone()->addDays($day);
            $revenue = Payment::where('payment_status', 'Paid')
                ->whereDate('created_at', $date)
                ->sum('amount');
            return [
                'date'   => $date->format('M d'),
                'amount' => (float) $revenue,
            ];
        });

        return response()->json([
            'revenue_trend' => $revenueTrend,
        ]);
    }
}
