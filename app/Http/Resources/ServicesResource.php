<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Model\Patients;
use App\Model\Rx_service;

class ServicesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $fee=0;
        $discount_fee=0;
        $getFee = Rx_service::where(['appointment_id'=>$this->id])->get();
        foreach ($getFee as $key => $value) {
             $fee+=$value['fee'];
             //$discount_fee+=$value['discount'];
        }
        $patient = Patients::where('patientid',$this->patientid)->first();
        $fileUrl = '';
        //if($this->profile){
            $fileName = $this->profile_name;
            $fileUrl = url('storage/pp/' . $fileName);// url('public/profiles/' . $fileName);
            //$fileUrl = url('storage/pp/' . $fileName);
        //}
        return [
            'id' => $this->service_id,
            'services' => $this->description,
            'fee' => $this->fee,
        ];
    }
}
