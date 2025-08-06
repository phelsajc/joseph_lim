<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PatientsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $fileUrl = '';
        if($this->profile_name){
            $fileName = $this->profile_name;
            $fileUrl =  url('/storage/app/public/pp/' . $fileName);//url('public/profiles/' . $fileName);
        }
        return [
            'id' => $this->id,
            'patientname' => strtoupper($this->patientname),
            'patientid' => $this->patientid,
            'profile' => $fileUrl,//$this->profile,
            //'city' => $this->city,
            'type' => $this->isold_patient,
            'birthdate' => $this->birthdate,//?date_format(date_create($this->birthdate), 'F d, Y'):'',
        ];
    }
}
