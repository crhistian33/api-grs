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
            'start_date' => $this->start_date,
            'state' => $this->state,
            'unit_shift_id' => $this->unitShift->id,
            'unitshift' => new UnitShiftResource($this->unitshift),
            'workers_count' => count(WorkerResource::collection($this->workers)),
            'workers' => WorkerResource::collection($this->workers),
            'Created_by' => $this->createdBy,
            'updated_by' => $this->updatedBy,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
