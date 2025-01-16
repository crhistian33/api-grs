<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'center_id' => $this->center->id,
            'center' => new CenterResource($this->center),
            'customer_id' => $this->customer->id,
            'customer' => new CustomerResource($this->customer),
            'shifts' => ShiftResource::collection($this->shifts),
            'user_id' => $this->user->id,
        ];
    }
}
