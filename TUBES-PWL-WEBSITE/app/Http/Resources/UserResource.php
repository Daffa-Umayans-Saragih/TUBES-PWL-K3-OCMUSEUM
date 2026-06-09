<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $activeMembership = $this->whenLoaded('memberships')
            ? $this->memberships->sortByDesc('activated_at')->firstWhere('membership_status', 'active')
            : null;

        return [
            'id'                 => $this->user_id,
            'email'              => $this->email,
            'is_admin'           => $this->is_admin,
            'premium_started_at' => $activeMembership?->activated_at ?? $this->premium_started_at,
            'premium_ended_at'   => $activeMembership?->expires_at ?? $this->premium_ended_at,
            'profile'            => new UserProfileResource($this->whenLoaded('profile')),
        ];
    }
}
