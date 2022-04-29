<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class RequestApprovalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this['id'],
            'model' => $this['approvable_type'],
            'unique_id' => $this['approvable_id'],
            'request_type' => $this['request_type'],
            'status' => $this['status'],
            'data' => $this['data'],
            'requested by' => $this['requestedBy']
        ];
    }
}
