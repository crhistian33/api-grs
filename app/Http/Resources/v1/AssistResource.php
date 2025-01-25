<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "start_date" => $this->start_date,
            "unit_shift_id" => $this->unit_shift_id,
            "unitshift" => new UnitShiftResource($this->unitshift),
            'total_attended' => $this->workerAssignments->where('pivot.is_assist', true)->count(),
            'total_absent' => $this->workerAssignments->where('pivot.is_assist', false)->count(),
        ];
    }
}
