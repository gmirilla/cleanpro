<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StaffResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'phone'               => $this->phone,
            'position'            => $this->position,
            'availability_status' => $this->availability_status,
            'rating'              => $this->rating,
            'completed_jobs'      => $this->completed_jobs,
            'working_days'        => $this->working_days,
            'shift_start'         => $this->shift_start,
            'shift_end'           => $this->shift_end,
        ];
    }
}
