<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Model\Patients;
use App\Model\Rx_service;

class AppointmentResource extends JsonResource
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
            $fileUrl = url('storage/app/public/pp/' . $fileName);// url('public/profiles/' . $fileName);
            //$fileUrl = url('storage/pp/' . $fileName);
        //}
        return [
            'id' => $this->id,
            'patientid' => $this->patientid,
            'patientname' => $patient->patientname,
            'complaints' => $this->chiefcomplaints,
            'profile' => $fileUrl,//$this->profile,
            'sequence' => $this->sequence,
            'type' => $this->isold_patient,
            'apt_dt' => date_format(date_create($this->appointment_dt), 'F d, Y'),
            'flwup_dt' => $this->followup?date_format(date_create($this->followup), 'F d, Y'):'',            
            'fee' => $fee-$this->discount,
            'discount' => $this->discount,
            'cancel_reason' => $this->cancel_reason,
            'isactive' => $this->isactive, 
        ];
    }
}
