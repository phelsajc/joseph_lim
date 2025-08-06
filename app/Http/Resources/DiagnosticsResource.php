<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DiagnosticsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->lab_test_id,
            'diagnostic' => strtoupper($this->lab_test),
            'type' => strtoupper($this->lab_test==1?'Lab':'Ancillary'),
        ];
    }
}
