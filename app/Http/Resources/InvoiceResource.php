<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'invoice_number' => $this->invoice_number,
            'amount'         => $this->amount,
            'tax'            => $this->tax,
            'discount'       => $this->discount,
            'total'          => $this->total,
            'status'         => $this->status,
            'due_date'       => $this->due_date?->toDateString(),
            'paid_at'        => $this->paid_at?->toISOString(),
            'booking'        => new BookingResource($this->whenLoaded('booking')),
        ];
    }
}
