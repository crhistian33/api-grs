<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'unit' => new UnitResource($this->unitShift->unit),
            'shift' => new ShiftResource($this->unitShift->shift),
            'start_date' => $this->start_date,
            'state' => $this->state,
            'unit_shift_id' => $this->unitShift->id,
            'workers_count' => count(WorkerResource::collection($this->workers)),
            'workers' => WorkerResource::collection($this->workers),
            'user_id' => $this->user->id,
        ];
    }
}
