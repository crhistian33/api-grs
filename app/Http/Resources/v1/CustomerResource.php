<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'ruc' => $this->ruc,
            'phone' => $this->phone,
            'company_id' => $this->company->id,
            'company' => new CompanyResource($this->company),
            'user_id' => $this->user->id,
        ];
    }
}
