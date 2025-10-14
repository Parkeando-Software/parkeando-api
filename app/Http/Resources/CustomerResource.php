<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'city' => $this->city,
            'points' => $this->points,
            'reputation' => $this->reputation,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
