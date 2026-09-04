<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
class MedicineResource extends JsonResource
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
            'id' => $this->id,
            'medicine' => Str::title($this->medicine_name),
            'generic' => Str::title($this->generic_name),
            'brand_name' => $this->medicine_name,
            'generic_name' => $this->generic_name,
            'isincluded' => $this->isincluded,
            'default_qty' => $this->default_qty,
            'unit' => $this->unit,
            'default_remarks' => $this->default_remarks,
        ];
    }
}
