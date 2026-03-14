<?php namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource {
    public function toArray($request): array {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'email'          => $this->email,
            'phone'          => $this->phone,
            'notes'          => $this->notes,
            'bookings_count' => $this->when(isset($this->bookings_count), $this->bookings_count),
            'addresses'      => $this->whenLoaded('addresses'),
            'created_at'     => $this->created_at->toISOString(),
        ];
    }
}
