<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BookingItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'       => $this->id,
            'service'  => new ServiceResource($this->whenLoaded('service')),
            'price'    => $this->price,
            'quantity' => $this->quantity,
            'subtotal' => $this->subtotal,
        ];
    }
}
