<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpirePassedOrders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'orders:expire-passed
                            {--dry-run : Preview without making changes}';

    /**
     * The console command description.
     */
    protected $description = 'Auto-expire paid/pending orders whose visit date has passed without being used.';

    /**
     * Execute the console command.
     *
     * Business Rule:
     *   - Order status is `paid` or `pending_payment`
     *   - ALL tickets in the order belong to a visit_schedule whose visit_date < today
     *   - No ticket in the order has status = `used` (those remain `completed`)
     *   - Cancelled/refunded orders are untouched
     *
     * Transition:
     *   order_status  → expired
     *   ticket status → expired
     *   payment_status (if Pending) → Failed
     */
    public function handle(): int
    {
        // +1 DAY STRATEGY:
        // A ticket for visit_date '2026-05-20' is valid for the full day.
        // Expiry boundary = visit_date + 1 day (i.e. '2026-05-21').
        // We expire the order only when today >= (visit_date + 1 day).
        //
        // DB query equivalent: visit_date < today_date_string
        //   '2026-05-19' < '2026-05-20' → TRUE  → expired ✓
        //   '2026-05-20' < '2026-05-20' → FALSE → still valid ✓
        //   '2026-05-21' < '2026-05-20' → FALSE → future ✓
        $todayDate = now()->toDateString(); // e.g. '2026-05-20'
        $isDryRun  = $this->option('dry-run');

        $this->info($isDryRun
            ? '[DRY RUN] Scanning for orders to expire (no changes will be saved)...'
            : 'Scanning for orders to expire...'
        );

        // Expire orders whose visit_date is strictly before today
        // (i.e. visit_date + 1 day has already arrived)
        $ordersToExpire = Order::whereIn('order_status', ['paid', 'pending_payment'])
            ->whereHas('tickets', function ($q) use ($todayDate) {
                $q->where('status', '!=', 'cancelled')
                    ->whereHas('ticketAvailability.visitSchedule', function ($sq) use ($todayDate) {
                        $sq->where('visit_date', '<', $todayDate);
                    });
            })
            // Guard: none of the tickets in the order were already used
            ->whereDoesntHave('tickets', function ($q) {
                $q->where('status', 'used');
            })
            ->with(['tickets.ticketAvailability.visitSchedule', 'payment'])
            ->get();

        if ($ordersToExpire->isEmpty()) {
            $this->info('No orders to expire. All clear!');
            return Command::SUCCESS;
        }

        $this->info("Found {$ordersToExpire->count()} order(s) to expire.");

        $expiredCount = 0;

        foreach ($ordersToExpire as $order) {
            // Double-check in PHP: expiry boundary = visit_date + 1 day
            // The order expires only when today >= (visit_date + 1 day)
            $relevantTickets = $order->tickets->where('status', '!=', 'cancelled');
            $allPast = $relevantTickets->every(function ($ticket) use ($todayDate) {
                $visitDate = $ticket->ticketAvailability?->visitSchedule?->visit_date;
                if (!$visitDate) return false;
                // Boundary: one day after visit. Today must be >= that boundary.
                $expiryBoundary = $visitDate->copy()->addDay()->toDateString(); // e.g. '2026-05-21'
                return $todayDate >= $expiryBoundary;
            });

            if (!$allPast) {
                // Some tickets are for today or a future date – skip this order
                continue;
            }

            if ($isDryRun) {
                $this->line("  [DRY RUN] Would expire Order #{$order->order_id} (status: {$order->order_status})");
                $expiredCount++;
                continue;
            }

            DB::transaction(function () use ($order) {
                // 1. Expire the order
                $order->update(['order_status' => 'expired']);

                // 2. Expire all non-cancelled, non-used tickets
                $order->tickets()
                    ->whereNotIn('status', ['cancelled', 'used'])
                    ->update(['status' => 'expired']);

                // 3. Mark a pending payment as Failed (unpaid = no revenue = Failed)
                if ($order->payment && strtolower($order->payment->payment_status) === 'pending') {
                    $order->payment->update(['payment_status' => 'Failed']);
                }
                // Note: if the payment was 'Paid', it remains 'Paid' — the order is just expired,
                // not refunded. Admin can trigger a refund manually if required.
            });

            $expiredCount++;
            $this->line("  Expired Order #{$order->order_id} (was: {$order->order_status})");
        }

        $verb = $isDryRun ? 'would be expired' : 'expired';
        $this->info("Done. {$expiredCount} order(s) {$verb}.");

        return Command::SUCCESS;
    }
}
