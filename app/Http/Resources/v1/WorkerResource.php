<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkerResource extends JsonResource
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
            'name' => $this->name,
            'dni' => $this->dni,
            'birth_date' => $this->birth_date,
            'type_worker_id' => $this->typeWorker->id,
            'typeworker' => $this->typeworker,
            'user_id' => $this->user->id,
        ];
    }
}
