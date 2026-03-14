<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                    => $this->id,
            'amount'                => $this->amount,
            'currency'              => $this->currency,
            'payment_method'        => $this->payment_method,
            'payment_status'        => $this->payment_status,
            'transaction_reference' => $this->transaction_reference,
            'paid_at'               => $this->paid_at?->toISOString(),
        ];
    }
}
