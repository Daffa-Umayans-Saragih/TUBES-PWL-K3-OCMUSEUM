<?php

namespace App\Http\Controllers;

use App\Mail\MembershipActivationMail;
use App\Models\Membership;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\MembershipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Throwable;

class MembershipController extends Controller
{
    // ── Page: /member/add-member ──────────────────────────────────────────────
    public function addMember()
    {
        $authUser = Auth::user();
        $profile  = $authUser?->profile;

        $user = (object) [
            'first_name' => $profile?->first_name ?? '',
            'last_name'  => $profile?->last_name  ?? '',
            'email'      => $authUser?->email      ?? '',
        ];

        return view('ordinary.member.add-member.add-member', [
            'user'  => $user,
            'title' => 'Membership Information',
        ]);
    }

    public function information()
    {
        return $this->addMember();
    }

    // ── Page: /members/membership ─────────────────────────────────────────────
    public function index()
    {
        $memberships = array_values($this->buildMembershipList());

        $viewName = 'ordinary.member.membership.membership';

        if (! View::exists($viewName)) {
            return redirect('/member/add-member');
        }

        try {
            return view($viewName, [
                'memberships' => $memberships,
                'title'       => 'Membership',
            ]);
        } catch (Throwable $e) {
            return redirect('/member/add-member');
        }
    }

    public function show($id)
    {
        return view('ordinary.membership.show.show', [
            'membership' => null,
            'title'      => 'Membership Details',
        ]);
    }

    // ── POST /member/add-member  (and /members/membership/purchase) ───────────
    public function purchase(Request $request)
    {
        $validated = $request->validate([
            'membership_id'      => ['nullable', 'integer'],
            'is_gift'            => ['nullable', 'boolean'],
            'auto_renewal'       => ['nullable', 'boolean'],
            'first_name'         => ['required', 'string', 'max:100'],
            'last_name'          => ['required', 'string', 'max:100'],
            'email'              => ['required', 'email', 'max:255'],
            'gift_first_name'    => ['nullable', 'string', 'max:100'],
            'gift_last_name'     => ['nullable', 'string', 'max:100'],
            'gift_email'         => ['nullable', 'email', 'max:255'],
            'street_address'     => ['nullable', 'string', 'max:255'],
            'apartment'          => ['nullable', 'string', 'max:255'],
            'city'               => ['nullable', 'string', 'max:100'],
            'country'            => ['nullable', 'string', 'max:100'],
            'postal_code'        => ['nullable', 'string', 'max:30'],
            'ship_to'            => ['nullable', 'in:recipient,donor'],
            'email_confirmation' => ['nullable', 'in:both,donor'],
        ]);

        $userId  = Auth::id();
        $guestId = $userId ? null : session('guest_id');

        if (! $userId && ! $guestId) {
            abort(403, 'User or guest identity not found.');
        }

        $catalog      = $this->catalog();
        $membershipId = (int) ($validated['membership_id'] ?? 1);
        $tier         = $catalog[$membershipId] ?? $catalog[1];

        $isGift      = (bool) ($validated['is_gift']      ?? false);
        $autoRenewal = (bool) ($validated['auto_renewal'] ?? false);

        // ── Smart Conditional UX Enforcement ──────────────────────────────────
        $shipTo      = $validated['ship_to'] ?? 'recipient';
        $emailConf   = $validated['email_confirmation'] ?? 'donor';

        if ($isGift && $shipTo === 'donor') {
            $emailConf = 'donor';
        }

        $donorEmail = isset($validated['email']) ? strtolower(trim($validated['email'])) : null;

        // Recipient e-mail: gift → gift_email; self → submitter email
        $rawRecipientEmail = $isGift
            ? ($validated['gift_email'] ?? $validated['email'])
            : $validated['email'];
        $recipientEmail = strtolower(trim($rawRecipientEmail));

        \Illuminate\Support\Facades\Log::info('Membership Checkout Payload', [
            'isGift' => $isGift,
            'shipTo' => $shipTo,
            'emailConf' => $emailConf,
            'donorEmail' => $donorEmail,
            'recipientEmail' => $recipientEmail,
        ]);

        // ── Resolve the user_id that will OWN the membership ─────────────────
        //
        // STRICT RULE: membership.user_id must be the RECIPIENT's user_id.
        // NEVER fall back to the purchaser's user_id for gift memberships.
        //
        // For a self-purchase ($isGift === false):
        //   The purchaser IS the recipient. user_id = authenticated purchaser.
        //
        // For a gift ($isGift === true):
        //   Look up the recipient by email.
        //   - Recipient HAS an account → user_id = recipient's user_id.
        //     Membership is immediately activatable via activation link.
        //   - Recipient does NOT have an account → user_id = null.
        //     Membership stays 'gift_pending_claim' until the recipient
        //     registers and activates via the emailed token.
        //     The activate() endpoint will stamp user_id at that point.
        if ($isGift) {
            $recipientUser   = User::where('email', $recipientEmail)->first();
            $membershipOwner = $recipientUser?->user_id; // null if no account yet
        } else {
            // Self-purchase: purchaser is the recipient.
            $membershipOwner = $userId;
        }

        // ── Resolve stacked membership dates ─────────────────────────────────
        //
        // CASE 1 — No prior active membership (or expired):
        //   activated_at = now(),  expires_at = now() + 1 year
        //
        // CASE 2 — Active membership exists (expires_at is in the future):
        //   activated_at = existing.expires_at
        //   expires_at   = existing.expires_at + 1 year
        //   (remaining time is NOT lost — new membership stacks on top)
        //
        // CASE 3 — Gift where recipient has no account yet:
        //   Dates are null at purchase time; they are computed and stamped
        //   when the recipient registers and calls activate().
        $isImmediatelyActive = false; // All memberships must be activated via email
        [$newActivatedAt, $newExpiresAt] = $isImmediatelyActive
            ? $this->resolveNewMembershipDates($membershipOwner, $recipientEmail)
            : [null, null];

        // ── DB transaction: Order + Payment + Membership ──────────────────────
        /** @var Membership $membership */
        $membership = DB::transaction(function () use (
            $userId, $guestId, $tier, $isGift, $autoRenewal, $recipientEmail,
            $membershipOwner, $isImmediatelyActive, $newActivatedAt, $newExpiresAt
        ) {
            // 1. Order — order belongs to the PURCHASER (user or guest)
            $order = Order::create([
                'order_code'   => (string) Str::uuid(),
                'user_id'      => $userId,
                'guest_id'     => $guestId,
                'order_date'   => now(),
                'expired_at'   => now()->addMinutes(20),
                'total_amount' => $tier['price'],
                'order_status' => 'paid',
                'order_type'   => 'membership',
            ]);

            // 2. Payment (instant — membership is paid at checkout)
            Payment::create([
                'order_id'       => $order->order_id,
                'payment_method' => 'Membership',
                'amount'         => $tier['price'],
                'payment_status' => 'Paid',
                'paid_at'        => now(),
            ]);

            // 3. Membership row
            //    user_id     = $membershipOwner (recipient's user_id, or null if no account yet)
            //    activated_at / expires_at use stacked dates (computed above outside transaction)
            return Membership::create([
                'order_id'          => $order->order_id,
                'user_id'           => $membershipOwner,   // RECIPIENT's user_id, never purchaser for gifts
                'recipient_email'   => $recipientEmail,
                'membership_status' => $isImmediatelyActive ? 'active' : 'gift_pending_claim',
                'is_gift'           => $isGift,
                'auto_renewal'      => $autoRenewal,
                'activation_token'  => (string) Str::uuid(),
                'token_expires_at'  => now()->addDays(7),
                'activated_at'      => $newActivatedAt,
                'expires_at'        => $newExpiresAt,
            ]);
        });
        // ── End transaction ───────────────────────────────────────────────────

        // 4. Sync users.premium_started_at / premium_ended_at.
        //
        //    This ONLY applies to immediately-active self-purchases where the
        //    recipient already has an account. For gifts, the sync happens inside
        //    activate() when the recipient claims the membership.
        //
        //    We use the SAME stacked dates ($newActivatedAt / $newExpiresAt) that
        //    were already written to the memberships row — no recalculation needed.
        if ($isImmediatelyActive && $membershipOwner !== null) {
            $recipientUser = User::find($membershipOwner);
            if ($recipientUser) {
                app(MembershipService::class)->syncPremiumState(
                    $recipientUser,
                    true,
                    $newExpiresAt,
                    $newActivatedAt
                );
            }
        }

        // 5. Send confirmation email AFTER the transaction commits.
        //    If mail fails, the membership is already saved — log and continue.
        try {
            $shipTo = $validated['ship_to'] ?? 'recipient';

            $activationRecipients = [];

            if ($isGift) {
                if ($shipTo === 'recipient') {
                    // Primary card to recipient
                    $activationRecipients[] = $recipientEmail;

                    // Check if donor wants a copy (email_confirmation = both)
                    if ($emailConf === 'both' && $donorEmail) {
                        $activationRecipients[] = $donorEmail;
                    }
                } else {
                    // shipTo === 'donor'
                    if ($donorEmail) {
                        $activationRecipients[] = $donorEmail;
                    }
                }
            } else {
                // Not a gift
                $activationRecipients[] = $recipientEmail;
            }

            // Remove empty values and strictly deduplicate emails
            $activationRecipients = array_unique(array_filter($activationRecipients));

            foreach ($activationRecipients as $email) {
                Mail::to($email)->send(new MembershipActivationMail($membership));
            }
        } catch (Throwable $e) {
            Log::error('MembershipActivationMail failed', [
                'membership_id' => $membership->membership_id,
                'to'            => $recipientEmail,
                'error'         => $e->getMessage(),
            ]);
        }

        // 6. Send Payment Confirmation (OrderSuccessMail) to Donor/Purchaser
        $invoiceRecipients = [$donorEmail ?: $recipientEmail];

        if ($isGift && $shipTo === 'recipient' && $emailConf === 'both') {
            $invoiceRecipients[] = $recipientEmail;
        }

        $invoiceRecipients = array_unique(array_filter($invoiceRecipients));

        foreach ($invoiceRecipients as $email) {
            try {
                // SAFE SCOPE RECOVERY
                $order = \App\Models\Order::find($membership->order_id);
                if ($order) {
                    // Pass order and dummy billing to OrderSuccessMail
                    $dummyBilling = [
                        'first_name' => $validated['first_name'] ?? 'Valued',
                        'last_name'  => $validated['last_name'] ?? 'Member'
                    ];
                    Mail::to($email)->send(new \App\Mail\OrderSuccessMail($order, $dummyBilling));
                }
            } catch (Throwable $e) {
                Log::error('OrderSuccessMail failed for membership', [
                    'order_id' => $membership->order_id,
                    'to'       => $email,
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        return redirect()->route('checkout.payments', $membership->order_id)
            ->with('success', 'Membership confirmed! A confirmation email has been sent.');
    }

    // ── GET /member/activate/{token} ──────────────────────────────────────────
    public function activate(string $token)
    {
        // Find the membership by token — include soft-deleted to avoid silent misses
        $membership = Membership::withTrashed()
            ->where('activation_token', $token)
            ->first();

        // Token not found at all
        if (! $membership) {
            return view('ordinary.member.activation.result', [
                'success' => false,
                'title'   => 'Invalid Activation Link',
                'message' => 'This activation link is invalid or has already been used. Please contact support if you believe this is an error.',
            ]);
        }

        // Token expired
        if ($membership->token_expires_at && now()->isAfter($membership->token_expires_at)) {
            return view('ordinary.member.activation.result', [
                'success'    => false,
                'title'      => 'Activation Link Expired',
                'message'    => 'This activation link has expired. Membership activation links are valid for 7 days. Please contact support to request a new link.',
                'membership' => $membership,
            ]);
        }

        // Already activated — show success without re-processing
        if ($membership->membership_status === 'active' && $membership->activated_at) {
            return view('ordinary.member.activation.result', [
                'success'    => true,
                'title'      => 'Membership Already Active',
                'message'    => 'Your membership is already active. Enjoy unlimited access to OC Museum!',
                'membership' => $membership,
            ]);
        }

        // ── STRICT: Recipient must be authenticated to activate ───────────────
        //
        // We must verify that the person clicking the link is actually the
        // intended recipient. Without this check, anyone who intercepts the
        // token URL could activate the membership under their own account —
        // or the membership would remain attached to the purchaser forever.
        //
        // Flow:
        //   1. Not logged in  → redirect to login, then return here.
        //   2. Logged in but wrong email → reject (security).
        //   3. Logged in with matching email → activate and stamp user_id.
        if (! Auth::check()) {
            // Store intended URL so the login page redirects back after auth.
            session()->put('url.intended', route('member.activate', $token));

            return redirect()->route('account.login')->with(
                'info',
                'Please log in (or register) with the email address this membership was sent to, then click the activation link again.'
            );
        }

        /** @var \App\Models\User $activatingUser */
        $activatingUser = Auth::user();

        // Email guard: the logged-in user's email must match the recipient email
        // stored on the membership. Case-insensitive comparison.
        if (strtolower(trim($activatingUser->email)) !== strtolower(trim($membership->recipient_email ?? ''))) {
            return view('ordinary.member.activation.result', [
                'success' => false,
                'title'   => 'Account Mismatch',
                'message' => 'This membership was sent to a different email address. Please log in with the account that matches the recipient email, then click the activation link again.',
            ]);
        }

        // Activate — update DB atomically, stamp the correct user_id, and apply
        // stacked membership dates.
        //
        // The recipient may already have another active membership (e.g. they
        // were gifted one before). We must NOT reset their expiry to now() + 1 year;
        // instead we stack on top of their furthest active expires_at.
        [$activatedAt, $expiresAt] = $this->resolveNewMembershipDates(
            $activatingUser->user_id,
            $activatingUser->email,
            $membership->membership_id   // exclude THIS membership from the lookup
        );

        DB::transaction(function () use ($membership, $activatingUser, $activatedAt, $expiresAt) {
            $membership->update([
                'user_id'           => $activatingUser->user_id,  // stamp correct recipient
                'membership_status' => 'active',
                'activated_at'      => $activatedAt,
                'expires_at'        => $expiresAt,
                'activation_token'  => null,   // consume token — can't be reused
                'token_expires_at'  => null,
            ]);
        });

        // Sync users.premium_started_at / premium_ended_at for the recipient.
        //
        // The stacked dates ($activatedAt / $expiresAt) were computed by
        // resolveNewMembershipDates() BEFORE the transaction, so they are
        // consistent with what was just written to memberships. We pass them
        // directly — no second lookup required.
        app(MembershipService::class)->syncPremiumState(
            $activatingUser,
            true,
            $expiresAt,
            $activatedAt
        );

        return view('ordinary.member.activation.result', [
            'success'    => true,
            'title'      => 'Membership Activated!',
            'message'    => 'Welcome to The Met! Your membership is now active and valid for one year.',
            'membership' => $membership->fresh(),
        ]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Compute the [activated_at, expires_at] dates for a NEW membership row,
     * applying the stacking rule:
     *
     *   CASE 1 — No active membership (or all expired):
     *     activated_at = now(),  expires_at = now() + 1 year
     *
     *   CASE 2 — Active membership exists (expires_at > now()):
     *     activated_at = existing.expires_at
     *     expires_at   = existing.expires_at + 1 year
     *     (remaining time is preserved — new duration stacks on top)
     *
     * @param  int|null  $userId          Recipient's user_id (null if account not yet registered)
     * @param  string    $recipientEmail  Recipient's email (used as fallback lookup key)
     * @param  int|null  $excludeId       membership_id to exclude (prevents self-referential lookup
     *                                   during activate() when the row being activated is itself
     *                                   already in the table but not yet active)
     * @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon}  [activated_at, expires_at]
     */
    private function resolveNewMembershipDates(
        ?int $userId,
        string $recipientEmail,
        ?int $excludeId = null
    ): array {
        // Find the furthest future expires_at among all active memberships
        // that belong to this recipient (matched by user_id OR recipient_email).
        $query = Membership::query()
            ->where('membership_status', 'active')
            ->where('expires_at', '>', now());

        if ($userId) {
            $query->where(function ($q) use ($userId, $recipientEmail) {
                $q->where('user_id', $userId)
                  ->orWhere('recipient_email', $recipientEmail);
            });
        } else {
            $query->where('recipient_email', $recipientEmail);
        }

        if ($excludeId !== null) {
            $query->where('membership_id', '!=', $excludeId);
        }

        $latestExpiry = $query->max('expires_at');

        if ($latestExpiry && now()->lessThan($latestExpiry)) {
            // CASE 2 — stack on top of the furthest active expiry
            $base = \Carbon\Carbon::parse($latestExpiry);
            return [$base->copy(), $base->copy()->addYear()];
        }

        // CASE 1 / CASE 3 — no active membership, or all expired: start fresh from now
        $base = now();
        return [$base->copy(), $base->copy()->addYear()];
    }

    private function catalog(): array
    {
        return [
            1 => ['name' => 'Individual', 'price' => 99.00],
            2 => ['name' => 'Family',     'price' => 199.00],
            3 => ['name' => 'Patron',     'price' => 500.00],
        ];
    }

    private function buildMembershipList(): array
    {
        return [
            1 => [
                'id'       => 1,
                'name'     => 'Individual',
                'price'    => 99,
                'duration' => '/year',
                'featured' => false,
                'features' => [
                    'Unlimited admission',
                    'Member events',
                    '10% gift shop discount',
                    'Member magazine',
                ],
            ],
            2 => [
                'id'       => 2,
                'name'     => 'Family',
                'price'    => 199,
                'duration' => '/year',
                'featured' => true,
                'features' => [
                    'Unlimited admission for 2 adults + 1 child',
                    'Member events',
                    '15% gift shop discount',
                    'Member magazine',
                    'Priority access to exhibitions',
                ],
            ],
            3 => [
                'id'       => 3,
                'name'     => 'Patron',
                'price'    => 500,
                'duration' => '/year',
                'featured' => false,
                'features' => [
                    'Unlimited admission + up to 4 guests',
                    'VIP events access',
                    '20% gift shop discount',
                    'Member magazine',
                    'Priority access to exhibitions',
                    'Exclusive Patron benefits',
                ],
            ],
        ];
    }
}
