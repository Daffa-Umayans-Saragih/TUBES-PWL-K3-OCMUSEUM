<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->payment_id,
            'payment_method' => $this->payment_method,
            'amount' => $this->amount,
            'payment_status' => $this->payment_status,
            'paid_at' => $this->paid_at,
        ];
    }
}
