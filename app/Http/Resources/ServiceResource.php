<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'category'         => $this->category,
            'description'      => $this->description,
            'base_price'       => $this->base_price,
            'duration_minutes' => $this->duration_minutes,
            'duration'         => $this->duration_for_humans,
            'status'           => $this->status,
        ];
    }
}
