<?php
namespace App\Services;

use App\Mail\GiftMembershipMail;
use App\Mail\MembershipActivationMail;
use App\Models\Guest;
use App\Models\Membership;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MembershipService
{
    public function createPendingMembership(Order $order, array $meta = []): Membership
    {
        return DB::transaction(function () use ($order, $meta) {
            $order->loadMissing(['user', 'guest']);

            $membership = Membership::firstOrNew(['order_id' => $order->order_id]);

            if ($membership->exists && $membership->membership_status === 'active') {
                return $membership;
            }

            $isGift         = (bool) ($meta['is_gift'] ?? false);
            $recipientEmail = strtolower(trim((string) ($meta['recipient_email'] ?? '')));

            if ($recipientEmail === '') {
                $recipientEmail = strtolower(trim((string) ($order->user?->email ?? '')));
            }

            if ($recipientEmail === '' && $order->guest_id) {
                $recipientEmail = strtolower(trim((string) (Guest::find($order->guest_id)?->email ?? '')));
            }

            Log::info('Membership recipient', [
                'is_gift'         => $isGift,
                'recipient_email' => $recipientEmail,
                'order_user_id'   => $order->user_id,
                'order_guest_id'  => $order->guest_id,
                'user_email'      => $order->user?->email,
                'guest_email'     => $order->guest?->email,
            ]);

            $membership->fill([
                'order_id'          => $order->order_id,
                'user_id'           => $order->user_id,
                'recipient_email'   => $recipientEmail !== '' ? $recipientEmail : null,
                'membership_status' => $isGift ? 'gift_pending_claim' : 'verification_pending',
                'is_gift'           => $isGift,
                'auto_renewal'      => (bool) ($meta['auto_renewal'] ?? false),
                'activation_token'  => Str::random(64),
                'token_expires_at'  => now()->addDays(7),
                'activated_at'      => null,
                'expires_at'        => null,
            ]);

            $membership->save();

            Log::info('Membership created', [
                'order_id'                => $order->order_id,
                'membership_id'           => $membership->membership_id,
                'membership_status'       => $membership->membership_status,
                'is_gift'                 => $membership->is_gift,
                'recipient_email'         => $membership->recipient_email,
                'activation_token_exists' => filled($membership->activation_token),
            ]);

            if (! empty($membership->recipient_email)) {
                $mailable = $isGift
                    ? new GiftMembershipMail($membership)
                    : new MembershipActivationMail($membership);

                Log::info('Membership email sending', [
                    'membership_id'    => $membership->membership_id,
                    'recipient_email'  => $membership->recipient_email,
                    'subject'          => $isGift ? "You've Received a MET Membership Gift" : 'Activate Your MET Membership',
                    'activation_token' => $membership->activation_token,
                    'activation_url'   => $isGift
                        ? route('member.gift.claim', $membership->activation_token)
                        : route('member.activate', $membership->activation_token),
                ]);

                try {
                    Mail::to($membership->recipient_email)->send($mailable);

                    Log::info('Membership email sent successfully', [
                        'membership_id'   => $membership->membership_id,
                        'recipient_email' => $membership->recipient_email,
                    ]);
                } catch (\Throwable $exception) {
                    Log::error('Membership email failed', [
                        'membership_id'   => $membership->membership_id,
                        'recipient_email' => $membership->recipient_email,
                        'error'           => $exception->getMessage(),
                    ]);

                    throw $exception;
                }
            } else {
                Log::warning('Membership email skipped because recipient_email is empty', [
                    'membership_id' => $membership->membership_id,
                    'order_id'      => $order->order_id,
                ]);
            }

            return $membership;
        });
    }

    public function activateMembership(Membership $membership, User $user): Membership
    {
        return DB::transaction(function () use ($membership, $user) {
            $membership->refresh();

            if ($membership->membership_status === 'expired' || $membership->membership_status === 'cancelled') {
                abort(403, 'Membership is no longer valid.');
            }

            $membership->update([
                'user_id'           => $user->user_id,
                'membership_status' => 'active',
                'activated_at'      => now(),
                'expires_at'        => now()->addMonth(),
                'activation_token'  => null,
                'token_expires_at'  => null,
            ]);

            Log::info('Membership activated', [
                'membership_id' => $membership->membership_id,
                'user_id'       => $user->user_id,
                'activated_at'  => $membership->activated_at,
                'expires_at'    => $membership->expires_at,
            ]);

            $this->syncPremiumState($user, true, $membership->expires_at);

            return $membership->fresh(['user', 'order']);
        });
    }

    public function claimGiftMembership(Membership $giftMembership, User $user): array
    {
        return DB::transaction(function () use ($giftMembership, $user) {
            $giftMembership->refresh();

            $activeMembership = Membership::query()
                ->where('user_id', $user->user_id)
                ->where('membership_status', 'active')
                ->whereNotNull('expires_at')
                ->where('expires_at', '>', now())
                ->orderByDesc('expires_at')
                ->orderByDesc('activated_at')
                ->lockForUpdate()
                ->first();

            if ($activeMembership) {
                $newExpiresAt = $activeMembership->expires_at->copy()->addMonth();

                $activeMembership->update([
                    'expires_at' => $newExpiresAt,
                ]);

                $giftMembership->update([
                    'user_id'           => $user->user_id,
                    'membership_status' => 'claimed',
                    'activated_at'      => now(),
                    'expires_at'        => null,
                    'activation_token'  => null,
                    'token_expires_at'  => null,
                ]);

                $this->syncPremiumState(
                    $user,
                    true,
                    $newExpiresAt,
                    $activeMembership->activated_at ?? $user->premium_started_at
                );

                Log::info('Gift membership extended existing active membership', [
                    'gift_membership_id'   => $giftMembership->membership_id,
                    'active_membership_id' => $activeMembership->membership_id,
                    'user_id'              => $user->user_id,
                    'previous_expires_at'  => $activeMembership->getOriginal('expires_at'),
                    'new_expires_at'       => $newExpiresAt,
                ]);

                return [
                    'membership' => $activeMembership->fresh(['user', 'order']),
                    'message'    => 'Your MET Membership has been extended by 1 month.',
                    'action'     => 'extended',
                ];
            }

            $activatedMembership = $this->activateMembership($giftMembership, $user);

            return [
                'membership' => $activatedMembership,
                'message'    => 'Your MET Membership is now active.',
                'action'     => 'activated',
            ];
        });
    }

    public function expireMembershipsForUser(User $user): void
    {
        $expiredMemberships = Membership::query()
            ->where('user_id', $user->user_id)
            ->where('membership_status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expiredMemberships as $membership) {
            $membership->update([
                'membership_status' => 'expired',
            ]);

            Log::info('Membership expired during login check', [
                'membership_id' => $membership->membership_id,
                'user_id'       => $user->user_id,
                'expires_at'    => $membership->expires_at,
            ]);
        }

        $hasActiveMembership = Membership::query()
            ->where('user_id', $user->user_id)
            ->where('membership_status', 'active')
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->exists();

        $this->syncPremiumState($user, $hasActiveMembership);
    }

    public function syncPremiumState(User $user, bool $isPremium,  ? \Carbon\CarbonInterface $expiresAt = null,  ? \Carbon\CarbonInterface $startedAt = null) : void
    {
        $payload = [
            'premium_started_at' => $isPremium ? ($startedAt ?? now()) : $user->premium_started_at,
            'premium_ended_at'   => $isPremium ? ($expiresAt ?? now()->addMonth()) : now(),
        ];

        if (Schema::hasColumn('users', 'is_premium')) {
            $payload['is_premium'] = $isPremium;
        }

        DB::table('users')
            ->where('user_id', $user->user_id)
            ->update($payload);

        Log::info('User premium state synced', [
            'user_id'            => $user->user_id,
            'is_premium'         => $isPremium,
            'premium_started_at' => $payload['premium_started_at'] ?? null,
            'premium_ended_at'   => $payload['premium_ended_at'] ?? null,
        ]);
    }
}
