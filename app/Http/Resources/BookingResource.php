<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                => $this->id,
            'booking_reference' => $this->booking_reference,
            'status'            => $this->status,
            'service_date'      => $this->service_date?->toISOString(),
            'pickup_date'       => $this->pickup_date?->toISOString(),
            'delivery_date'     => $this->delivery_date?->toISOString(),
            'total_amount'      => $this->total_amount,
            'notes'             => $this->notes,
            'confirmed_at'      => $this->confirmed_at?->toISOString(),
            'completed_at'      => $this->completed_at?->toISOString(),
            'customer'          => new CustomerResource($this->whenLoaded('customer')),
            'items'             => BookingItemResource::collection($this->whenLoaded('items')),
            'assigned_staff'    => new StaffResource($this->whenLoaded('assignedStaff')),
            'invoice'           => new InvoiceResource($this->whenLoaded('invoice')),
            'address'           => $this->whenLoaded('address', fn() => [
                'id'      => $this->address->id,
                'label'   => $this->address->label,
                'address' => $this->address->full_address,
            ]),
            'created_at'        => $this->created_at->toISOString(),
        ];
    }
}
