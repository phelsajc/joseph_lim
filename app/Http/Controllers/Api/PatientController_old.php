<?php
/**
 * File LaravelController.php
 *
 * @author Tuan Duong <bacduong@gmail.com>
 * @package Laravue
 * @version 1.0
 */

namespace App\Http\Controllers\Api;
use App\Model\Patients;
use App\Model\Profile;
use App\Model\Appointments;
use App\Model\Rx;
use App\Model\Rx_service;
use App\Model\Services;
use App\Model\Attachments;
use App\Model\Ancillary;
use App\Model\Labs;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Http\Resources\PatientsResource;
use App\Http\Resources\AppointmentResource;
use App\Laravue\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use DB;
use PDF;
use TGazel\LaraFpdf\Facades\LaraFpdf;
use App\Helpers\Helpers;
use App\Model\Medicine;
use App\Model\Generics;
use App\Model\OldPatients;
use App\Model\OldDiagnosis;
/**
 * Class LaravueController
 *
 * @package App\Http\Controllers
 */
class PatientController extends BaseController
{
    const ITEM_PER_PAGE = 15;
    /**
     * Entry point for Laravue Dashboard
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|ResourceCollection
     */
    public function index(Request $request)
    {
        /* $list = Patient::all();
        return response()->json($list);  */
        $searchParams = $request->all();
        $userQuery = Patients::query();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        //$role = Arr::get($searchParams, 'role', '');
        $keyword = Arr::get($searchParams, 'keyword', '');

        if (!empty($keyword)) {
            //$userQuery->where('LOWER(patientname)', 'LIKE', '%' . $keyword . '%');
            $userQuery->whereRaw('LOWER(patientname) LIKE ?', ['%'.strtolower($keyword).'%']);
            //$userQuery->orWhere('email', 'LIKE', '%' . $keyword . '%');
        }
        return PatientsResource::collection($userQuery->paginate($limit));
    }

    public function storePatient(Request $request) {
        //$input = Request::all();
        $data = new Patients();
        $lastinserted = Patients::latest()->value('id')+1;//DB::connection('mysql')->getPdo()->lastInsertId();
        $data->patientname = ucfirst($request->firstname).' '.ucfirst(mb_substr($request->middlename, 0, 1)).'. '.ucfirst($request->lastname);
        $data->firstname = $request->firstname;
        $data->middlename = $request->middlename;
        $data->lastname = $request->lastname;
        $data->patientid = date("Ymd").'-0'.$lastinserted;
        $data->contactno = $request->contactno;
        $data->birthdate= $request->birthdate; 
        $data->sex = $request->sex;
        $data->civil_status = $request->civil_status;
        //$data->oscaid = $request->firstname;
        $data->address = $request->address;
        $data->created_at = date("Y-m-d H:i:s");
        $data->referredby = $request->referredby;
        //$data->religionid = $request->firstname;
        $data->remarks = $request->remarks;
        $data->occupation = $request->occupation;
        $data->isold_patient = $request->isold_patient;
        $data->profile = $request->profile;

        $fam = '';
        if($request->fam){
            foreach ($request->fam as $key => $value) {
                $fam.=$value.',';
            }
            $data->fam = substr($fam, 0, -1);
        }
        $data->fam_others = $request->fam_others; 
        
        $pmh = '';
        if($request->pmh){
            foreach ($request->pmh as $key => $value) {
                $pmh.=$value.',';
            }
            $data->pmh = substr($pmh, 0, -1);
        }
        $data->pmh_others = $request->pmh_others; 
        
        $soc = '';
        if($request->soc){
            foreach ($request->soc as $key => $value) {
                $soc.=$value.',';
            }
            $data->soc = substr($soc, 0, -1);
        }
        $data->soc_others = $request->soc_others; 

        $data->save();
        //$data  = Patients::create(request()->all());
        return response()->json($data);
    }
    
    public function updatePatient(Request $request) {
        //$input = Request::all();
        $data = Patients::find($request->id);
        $data->patientname = ucfirst($request->firstname).' '.ucfirst(mb_substr($request->middlename, 0, 1)).'. '.ucfirst($request->lastname);
        $data->firstname = $request->firstname;
        $data->middlename = $request->middlename;
        $data->lastname = $request->lastname;
        $data->contactno = $request->contactno;
        $data->birthdate= $request->birthdate; 
        $data->sex = $request->sex;
        $data->civil_status = $request->civil_status;
        //$data->oscaid = $request->firstname;
        $data->address = $request->address;
        $data->referredby = $request->referredby;
        //$data->religionid = $request->firstname;
        $data->remarks = $request->remarks;
        $data->occupation = $request->occupation;
        $data->isold_patient = $request->isold_patient;
        if($request->profile!=""||$request->profile!=null){
            $data->profile = $request->profile;
        }
        

        $fam = '';
        if($request->fam){
            foreach ($request->fam as $key => $value) {
                $fam.=$value.',';
            }
            $data->fam = substr($fam, 0, -1);
        }
        $data->fam_others = $request->fam_others; 
        
        $pmh = '';
        if($request->pmh){
            foreach ($request->pmh as $key => $value) {
                $pmh.=$value.',';
            }
            $data->pmh = substr($pmh, 0, -1);
        }
        $data->pmh_others = $request->pmh_others; 
        
        $soc = '';
        if($request->soc){
            foreach ($request->soc as $key => $value) {
                $soc.=$value.',';
            }
            $data->soc = substr($soc, 0, -1);
        }
        $data->soc_others = $request->soc_others; 

        $data->save();
        //$data  = Patients::create(request()->all());
        return response()->json(true);
    }

    public function saveAppointment(Request $request) {
        $data = new Appointments();
        $data->patientid = $request->pid;
        $data->chiefcomplaints = $request->complaints;
        $data->appointment_dt = date_format(date_create($request->apt_dt),'Y-m-d');
        $data->created_by = Auth::user()->id;
        $data->created_dt = date("Y-m-d H:i:s");
        $data->nurse_remarks = $request->remarks;
        $data->save();
        return $data;
    }

    public function getPatient($id)
    {
        return Patients::where(['id' => $id])->first();
    }

    public function updateAppointment(Request $request)
    {
        $ff_dt = '';
        if($request->followup!='Invalid date'){
            $ff_dt = date_format(date_create($request->followup),'Y-m-d');
        }
        $undersigned = '';
        if($request->undersigned!='Invalid date'){
            $undersigned = date_format(date_create($request->undersigned),'Y-m-d');
        }
        $data = Appointments::find($request->id);
        $data->cc = $request->cc;
        $data->obmens = $request->obmens;
        $data->ob_g = $request->ob_g;
        $data->ob_p = $request->ob_p;
        $data->ob_tpal = $request->ob_tpal;
        $data->ob_remarks = $request->ob_remarks;
        $data->mens_m = $request->mens_m;
        $data->mens_i = $request->mens_i;
        $data->mens_d = $request->mens_d;
        $data->mens_s = $request->mens_s;
        $data->mens_a = $request->mens_a;
        $data->mens_menu = $request->mens_menu;
        $data->hpi = $request->hpi;
        $data->sig_labs = $request->sig_labs;
        $data->pmhx = $request->pmhx;
        $data->recommendations = $request->recommendations;
        $data->findings = $request->findings;
        $data->updated_by = Auth::user()->id;
        $data->updated_dt = date("Y-m-d H:i:s");
        $data->vit_sys = $request->vit_sys;
        $data->vit_dia = $request->vit_dia;
        $data->vit_temp = $request->vit_temp;
        $data->vit_cr = $request->vit_cr;
        $data->vit_rr = $request->vit_rr;
        $data->pe_head = $request->pe_head;
        $data->pe_ear = $request->pe_ear;
        $data->pe_eyes = $request->pe_eyes;
        $data->pe_nose = $request->pe_nose;
        $data->pe_throat = $request->pe_throat;
        $data->pe_breast = $request->pe_breast;
        $data->pe_chest = $request->pe_chest;
        $data->pe_heart = $request->pe_heart;
        $data->pe_abdomen = $request->pe_abdomen;
        $data->pe_genito = $request->pe_genito;
        $data->pe_extremities = $request->pe_extremities;
        $data->pe_review = $request->pe_review;
        $data->pe_ext = $request->pe_ext;
        $data->pe_cer = $request->pe_cer;
        $data->pe_uterus = $request->pe_uterus;
        $data->pe_adnexa = $request->pe_adnexa;
        $data->pe_dish = $request->pe_dish;
        $data->pe_pq1 = $request->pe_pq1;
        $data->pe_pq2 = $request->pe_pq2;
        $data->pe_pq3 = $request->pe_pq3;
        $data->pe_pq4 = $request->pe_pq4;
        $data->pe_pq5 = $request->pe_pq5;
        $data->pe_pq6 = $request->pe_pq6;
        $data->pe_pq7 = $request->pe_pq7;
        $data->pe_pq8 = $request->pe_pq8;
        $data->pe_pq9 = $request->pe_pq9;
        $data->remarks = $request->remarks;
        $data->medcert_undersigned = $undersigned;
        $data->medcert_diagnosis = $request->medcert_diagnosis;
        $data->medcert_remarks = $request->medcert_remarks;
        $data->followup = $request->followup!='Invalid date'?$ff_dt:null;
        $data->save();

        //assume that update is final
        /* $checkFfDt = Appointments::where(['patientid'=>$request->patientid,'followup'=>$ff_dt])->first();
        if($request->followup_dt && !$checkFfDt) { */
        if($request->followup!='Invalid date'){
            $data = new Appointments();
            $data->patientid = $request->patientid;
            $data->chiefcomplaints = 'Follow up checkup';
            $data->appointment_dt = $request->followup!='Invalid date'?$ff_dt:null;
            $data->created_by = Auth::user()->id;
            $data->created_dt = date("Y-m-d H:i:s");
            $data->nurse_remarks = $request->remarks;
            $data->save();
        }
        //}
        return response()->json($request->medsArr);
    }

    public function getAppointmentDetails($id){
        $data = Appointments::find($id);
        $getPreviousRecords = DB::table('appointments')
            ->where('patientid', $data->patientid)
            ->orderBy('id', 'desc')
            ->limit(2)
            ->get();
        $px_profile = Helpers::patientDetail($data->patientid);
        $get_OldPatients = OldPatients::where(["Patient_id"=>$px_profile->patientid])->first();
        $get_OldDiagnosis = $get_OldPatients?OldDiagnosis::where(["PatientID"=>$get_OldPatients->PatientID])->get():[];
        $old_data = array();
        foreach ($get_OldDiagnosis as $key => $value) {
            $arr = array();
            $arr['hpi'] = $value->HPI;
            $arr['pmhx'] = $value->pmHx;
            $arr['desc'] = $value->description;
            $arr['date'] = date_format(date_create($value->DateOfVisit),'F d, Y');
            $arr['cc'] = $value->CC;
            $arr['recom'] = $value->Recom;
            $old_data[] = $arr;
        }
        return response()->json(['get_OldDiagnosis'=>$old_data,'get_OldPatients'=>$get_OldPatients,'px_profile' => $px_profile ,'data'=>$data,'getPreviousRecords'=>$getPreviousRecords,'prev_data'=>sizeof($getPreviousRecords)>1?$getPreviousRecords[1]:[]]);
    }    

    public function appointmentList(Request $request)
    {
        $searchParams = $request->all();
        //$userQuery = Appointments::query();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = Arr::get($searchParams, 'keyword', '');
        $date = Arr::get($searchParams, 'date', '');
        $formattedDt = $date?date_format(date_create($date),'Y-m-d'):date("Y-m-d");
        
       // $userQuery = Appointments::selectRaw(" * left join patients on appointments.patientid = patients.patientid")->paginate($limit);

        $userQuery = DB::table('appointments')
        ->join('patients', 'patients.patientid', '=', 'appointments.patientid')
        ->select('patients.patientname','patients.profile','patients.isold_patient','appointments.chiefcomplaints' ,'appointments.discount', 'appointments.patientid', 'appointments.id', 'appointments.appointment_dt')
        //->where('LOWER(patients.patientname)', 'LIKE', '%'.$keyword.'%')
        //->whereRaw('LOWER(patients.patientname) LIKE ?', ['%'.$keyword.'%'])
        
        //->whereRaw('LOWER(patients.patientname) LIKE ? AND appointment_dt >= CURDATE() AND isdone = false AND is_cancel = false', ['%'.strtolower($keyword).'%'])
        ->whereRaw('LOWER(patients.patientname) LIKE ? AND appointment_dt = ? AND isdone = false AND is_cancel = false', ['%'.strtolower($keyword).'%',$formattedDt])
        ->paginate($limit);

        /* if (!empty($keyword)) {
            $userQuery->where('id', 'LIKE', '%' . $keyword . '%');
        } */
        return AppointmentResource::collection($userQuery);
    }

    public function findPatient($kw)
    {
        $q = DB::connection('mysql')->select("select * from patients where LOWER(patientname) like '%".strtolower($kw)."%' order by id desc limit 100");
        
        $data = array();
        foreach ($q as $key => $value) {
            $arr =  array();
            $arr['patientname'] = $value->patientname;
            $arr['pid'] = $value->patientid;
            $arr['id'] = $value->id;
            $data[] = $arr;
        }
        //$suggestions = Patients::where('patientname', 'like', "%{$kw}%")->limit(10)->get();
        /* $output = array("data" => $data);
        return response()->json($output); */
        return response()->json(['suggestions' => $data]);
    }

    public function printpdf($id)
    {
        $data = array();
        $data['query_prescription'] = Rx::where(['appointment_id'=>$id])->get();
        $data['appointment_detail'] = Appointments::where(['id'=>$id])->first();
        $data['profile'] = Profile::where(['id'=>1])->first();
        $data['patient_detail'] = Patients::where(['patientid'=>$data['appointment_detail']->patientid])->first();
        $myPdf = new CustomPrescriptiontest($data);
        $myPdf->Output('I', time()."-.pdf", true);
        exit;
    }

    public function printmedcert($id)
    {
        $data = array();
        $data['appointment_detail'] = Appointments::where(['id'=>$id])->first();
        $data['patient_detail'] = Patients::where(['patientid'=>$data['appointment_detail']->patientid])->first();
        $myPdf = new MedCert($data);
        $myPdf->Output('I', time()."-.pdf", true);
        exit;
    }

    public function printrequest($id,$type)
    {
        $data = array();
        $data['query_prescription'] = Ancillary::where(['appointment_id'=>$id,'type'=>$type])->get();
        $data['appointment_detail'] = Appointments::where(['id'=>$id])->first();
        $data['patient_detail'] = Patients::where(['patientid'=>$data['appointment_detail']->patientid])->first();
        $myPdf = new Requestprescription($data);
        $myPdf->Output('I', time()."-.pdf", true);
        exit;
    }

    public function printchart($id)
    {
        $data = array();
        $data['patient_detail'] = Patients::where(['patientid'=>$id])->first();
        /* $data['query_prescriptions'] = Rx::where(['appointment_id'=>$id])->get();
        $data['query_diagnostics'] = Ancillary::where(['appointment_id'=>$id])->get(); */
        $data['profile'] = Profile::where(['id'=>1])->first();
        $data['getHistory'] = Appointments::where(['patientid'=>$data['patient_detail']->patientid])->get();
        $myPdf = new ChartRecordPdf($data);
        $myPdf->Output('I', time()."-.pdf", true);
        exit;
    }

    function getpastConsultationList($id) {
        $data = Appointments::where(['patientid'=>$id])->get();

        $px_profile = Helpers::patientDetail($id);
        $get_OldPatients = OldPatients::where(["Patient_id"=>$px_profile->patientid])->first();
        $get_OldDiagnosis = $get_OldPatients?OldDiagnosis::where(["PatientID"=>$get_OldPatients->PatientID])->get():[];
        $old_data = array();
        foreach ($get_OldDiagnosis as $key => $value) {
            $arr = array();
            $arr['hpi'] = $value->HPI;
            $arr['pmhx'] = $value->pmHx;
            $arr['desc'] = $value->description;
            $arr['date'] = date_format(date_create($value->DateOfVisit),'F d, Y');
            $arr['cc'] = $value->CC;
            $arr['recom'] = $value->Recom;
            $old_data[] = $arr;
        }

        $array = array();
        foreach ($data as $key => $value) {
            $arr = array();
            $arr['date'] = date_format(date_create($value->appointment_dt), 'F d, Y');
            $arr['cf'] = $value->chiefcomplaints;
            $arr['id'] = $value->id;
            $array[] = $arr;
        }
        return response()->json(['data' => $array,'get_OldDiagnosis'=>$old_data]);
    }

    function deleteMed($id) {
        RX::where('rx_id',$id)->delete();
        return response()->json(true);
    }

    public function addMed(Request $request) {
        $rx = new RX();
        $medicineDetail = Helpers::medicineDetail($request->med_id);
        $rx->appointment_id = $request->id;
        $rx->medicine_id = $request->med_id;
        $rx->breakfastbefore = $request->bf_b;
        $rx->breakfastafter = $request->bf_a;
        $rx->lunchbefore = $request->l_b;
        $rx->lunchafter = $request->l_a;
        $rx->supperbefore = $request->s_b;
        $rx->supperafter = $request->s_a;
        $rx->bedtime = $request->bt;
        $rx->qty = $request->qty;
        $rx->remarks = $request->remarks;
        $rx->created_dt = date("Y-m-d H:i:s");
        $rx->medicine = $request->meds;
        $rx->generic_id = $medicineDetail->generic_id;
        $rx->generic_name = $medicineDetail->generic_name;
        $rx->save();
        return response()->json($medicineDetail);
    }

    function deleteDiagnostic($id) {
        Ancillary::where('id',$id)->delete();
        return response()->json(true);
    }

    /* public function addDiagnostic(Request $request) {
        $rx = new Ancillary();
        $rx->appointment_id = $request->id;
        $rx->ancillary_id = $request->procedure_id;
        $rx->ancillary = $request->procedure;
        $rx->remarks = $request->remarks;
        $rx->type = $request->type;
        $rx->save();
        return response()->json(true);
    } */

    public function addDiagnostic(Request $request) {
        foreach ($request->rendered as $key => $value) {
            $rx = new Ancillary();
            $rx->appointment_id = $value['id'];
            $rx->ancillary_id = $value['procedure_id'];
            $rx->ancillary = $value['procedure'];
            $rx->remarks = $value['remarks'];
            $rx->type = $value['type'];
            $rx->save();
        }
        return response()->json(true);
    }

    function deleteService($id) {
        Rx_service::where('rendered_id',$id)->delete();
        return response()->json(true);
    }

    public function addService(Request $request) {
        foreach ($request->rendered as $key => $value) {
            $rx = new Rx_service();
            $rx->appointment_id = $value['id'];
            $rx->service_id = $value['service_id'];
            $rx->fee = $value['fee'];
            $rx->created_dt = date("Y-m-d H:i:s");
            $rx->service = $value['service'];
            //$rx->discount = $request->discount;
            //$rx->total = $request->fee - $request->discount;
            $rx->save();
        }
        $field = Appointments::find($request->id);
        $field->discount = $request->discount;
        $field->save();
        return response()->json(true);
    }

    function getAppointmentDiagnostics($id) {
        $data = Ancillary::where(['appointment_id'=>$id])->get();
        $array = array();
        foreach ($data as $key => $value) {
            $arr = array();
            $arr['type'] = $value->type==1?'Lab':'Ancillary';
            $arr['diagnostic'] = $value->ancillary;
            $arr['ancillary_id'] = $value->ancillary_id;
            $arr['remarks'] = $value->remarks;
            $arr['id'] = $value->id;
            $array[] = $arr;
        }
        return response()->json(['data' => $array]);
    }

    function getAppointmentMedicine($id) {
        $data = RX::where(['appointment_id'=>$id])->get();
        $array = array();
        foreach ($data as $key => $value) {
            $arr = array();
            $arr['medicine'] = $value->medicine.' ('.$value->generic_name.')';
            $arr['qty'] = $value->qty;
            $arr['bb'] = $value->breakfastbefore;
            $arr['ab'] = $value->breakfastafter;
            $arr['bl'] = $value->lunchbefore;
            $arr['al'] = $value->lunchafter;
            $arr['bs'] = $value->supperbefore;
            $arr['as'] = $value->supperafter;
            $arr['bt'] = $value->bedtime;
            $arr['remarks'] = $value->remarks;
            $arr['id'] = $value->rx_id;
            $array[] = $arr;
        }
        return response()->json(['data' => $array]);
    }

    function getAppointmentService($id) {
        $data = Rx_service::where(['appointment_id'=>$id])->get();
        $array = array();
        foreach ($data as $key => $value) {
            $arr = array();
            $arr['service'] = $value->service;
            $arr['total'] = $value->total;
            $arr['others'] = $value->others;
            $arr['discount'] = $value->discount;
            $arr['fee'] = $value->fee;
            $arr['service_id'] = $value->service_id;
            $arr['id'] = $value->rendered_id;
            $array[] = $arr;
        }
        return response()->json(['data' => $array]);
    }

    function doneConsult($id) {
        $field = Appointments::find($id);
        $field->isdone = true;
        $field->save();
        return response()->json(true);
    }

    function getAttachments($id) {
        $getidno = explode("-0", $id);
        if(sizeof($getidno)>1){
            $data = Attachments::where(['patientid'=>$getidno[1]])->get();
        }else{
            $data = Attachments::where(['patientid'=>$id])->get();
        }
        // select * from patients_attachments pa where '03182'  = pa.patientid
        // ('name', 'like', '%' . Input::get('name') . '%')->get();
        $array = array();
        foreach ($data as $key => $value) {
            $arr = array();
            $arr['newfile'] = $value->isold_record==0?$value->file:null;
            $arr['oldfile'] = $value->isold_record==1?$value->file:null;
            $arr['type'] = $value->isold_record;
            $arr['id'] = $value->AttachmentID;
            $array[] = $arr;
        }
        return response()->json(['data' => $array]);
    }
    
    public function addpatientAttachments(Request $request) {
        $att = new Attachments();
        $getidno = explode("-0", $request->patientid);
        
        $att->patientid = sizeof($getidno)>1?$getidno[1]:$request->patientid;
        $att->file = $request->file;
        $att->isold_record = false;
        $att->save();
        return response()->json(true);
    }

    function deleteAttachment($id) {
        Attachments::where('AttachmentID',$id)->delete();
        return response()->json(true);
    }

    public function dashboard()
    {
        date_default_timezone_set('Asia/Manila');
        $count_px = Patients::all();
        $count_meds = Medicine::all();
        $count_diagnostics = Services::all();
        $todays_appt = DB::table('appointments')
            ->where('appointment_dt', date("Y-m-d"))
            ->orderBy('id', 'desc')
            ->get();
        $curr_month = date("Y-m");
        $graph_census = DB::connection('mysql')->select("select count(appointment_dt) as cnt, DATE_FORMAT(appointment_dt , '%Y-%m') apt_dt  from appointments group by DATE_FORMAT(appointment_dt , '%Y-%m')");
        $calendar = DB::connection('mysql')->select("select * from appointments where DATE_FORMAT(appointment_dt , '%Y-%m') = '$curr_month'");
        $data_graph = array();
        $data_graph_month = array();
        foreach ($graph_census as $key => $value) {
            $data_graph[] = $value->cnt;
            $data_graph_month[] = date_format(date_create($value->apt_dt), 'F Y');
        }
        $data_calendar = array();
        foreach ($calendar as $key => $value) {
            $arr = array();
            $arr['title'] = $value->patientid?Helpers::patientDetail($value->patientid)->patientname:'';
            $arr['start'] = date_format(date_create($value->appointment_dt),'Y-m-d');
            $data_calendar[] = $arr;
        }
        $data_todays_pxs = array();
        foreach ($todays_appt as $key => $value) {
            $arr = array();
            $arr['patient'] = Helpers::patientDetail($value->patientid)->patientname;
            $arr['complaints'] = $value->chiefcomplaints;
            $data_todays_pxs[] = $arr;
        }
        $graph_revenue = DB::connection('mysql')->select("select sum(s.total) as amt, DATE_FORMAT(appointment_dt , '%Y-%m') as apt_dt from appointments a 
        left join servicesrendered s on a.id = s.appointment_id 
        group by DATE_FORMAT(a.appointment_dt , '%Y-%m')");
        $revenue_month_arr = array();
        $revenue_arr = array();
        foreach ($graph_revenue as $key => $value) {
            $revenue_arr[] = $value->amt?$value->amt:0;
            $revenue_month_arr[] = date_format(date_create($value->apt_dt), 'F Y');
        }
        return response()->json(['graph_amt' =>array(["name" => 'Total', 'data' => $revenue_arr]), 'revenue_mon'=>$revenue_month_arr ,'todaysAppt' => $data_todays_pxs,'calendar'=>$data_calendar,'graph_census' =>array(["name" => 'No. of Patients', 'data' => $data_graph]), 'data_graph_month' => $data_graph_month, 'appt' => $todays_appt->count(),'patients' => $count_px->count(),'meds' => $count_meds->count(),'dx' => $count_diagnostics->count()]);
    }
    
    function cancelAppointment(Request $request) {
        $field = Appointments::find($request->id);
        $field->is_cancel = true;
        $field->cancel_reason = $request->cancel_reason;
        $field->save();
        return response()->json(true);
    }

    function updateBP(Request $request) {
        $field = Appointments::find($request->id);
        $field->vit_sys = $request->vit_sys;
        $field->vit_dia = $request->vit_dia;
        $field->save();
        return response()->json(true);
    }
}
