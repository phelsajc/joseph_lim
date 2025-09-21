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
use App\Model\Rxb;
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
use App\Helpers\helpers;
use App\Model\Medicine;
use App\Model\Generics;
use App\Model\OldPatients;
use App\Model\OldDiagnosis;
use App\Model\AdditionalCheckList;
use App\Model\Adolecense;
use App\Model\Vaccinations;
use App\Model\GrowthDev;
use Illuminate\Support\Facades\Storage;
use App\Events\NewAppointments;
use App\Mail\PrescriptionPdfMail;
use Illuminate\Support\Facades\Mail;

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
        date_default_timezone_set('Asia/Manila');
        /* $list = Patient::all();
        return response()->json($list);  */
        $searchParams = $request->all();
        $userQuery = Patients::query();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        //$role = Arr::get($searchParams, 'role', '');
        $keyword = Arr::get($searchParams, 'keyword', '');
        /* $userQuery->orWhere('active', 1);
        if (!empty($keyword)) {
            $userQuery->whereRaw('LOWER(patientname) LIKE ? AND active = 1', ['%'.strtolower($keyword).'%']);
        } */

        $userQuery = DB::table('patients')
            ->select('*')
            ->whereRaw('patientname LIKE ? AND isdeleted = 0', ['%' . $keyword . '%'])
            ->paginate($limit);
        return PatientsResource::collection($userQuery);
        //return PatientsResource::collection($userQuery->paginate($limit));
    }
    
    function deletePatient($id)
    {
        Patients::where(['patientid' => $id])->update([
            'isdeleted' => 1
        ]);
        return response()->json(true);
    }

    public function storePatient(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        //$input = Request::all();
        $data = new Patients();
        $lastinserted = Patients::latest()->value('id') + 1;//DB::connection('mysql')->getPdo()->lastInsertId();
        $data->patientname = ucfirst($request->firstname) . ' ' . ($request->middlename ? ucfirst(mb_substr($request->middlename, 0, 1)) . '. ' : '') . ucfirst($request->lastname);
        $data->firstname = $request->firstname;
        $data->middlename = $request->middlename;
        $data->lastname = $request->lastname;
        $data->patientid = date("Ymd") . '-0' . $lastinserted;
        $data->contactno = $request->contactno;
        $data->email = $request->email;
        $data->birthdate = $request->birthdate;
        $data->sex = $request->sex;
        $data->civil_status = $request->civil_status;
        $data->address = $request->address;
        $data->created_at = date("Y-m-d H:i:s");
        $data->referredby = $request->referredby;
        $data->remarks = $request->remarks;
        $data->occupation = $request->occupation;
        $data->isold_patient = $request->isold_patient;
        $data->profile = $request->profile;
        $data->blood_type = $request->blood_type;
        //$data->city = $request->city;

        /* $fam = '';
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
        
        $soc = '';
        if($request->soc){
            foreach ($request->soc as $key => $value) {
                $soc.=$value.',';
            }
            $data->soc = substr($soc, 0, -1);
        }
        $data->soc_others = $request->soc_others; */

        $data->save();
        //$data  = Patients::create(request()->all());
        return response()->json($data);
    }

    public function updatePatient(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $data = Patients::find($request->id);
        $data->patientname = ucfirst($request->firstname) . ' ' . ($request->middlename ? ucfirst(mb_substr($request->middlename, 0, 1)) . '. ' : '') . ucfirst($request->lastname);
        $data->firstname = $request->firstname;
        $data->middlename = $request->middlename;
        $data->lastname = $request->lastname;
        $data->contactno = $request->contactno;
        $data->email = $request->email;
        $birthdate = $request->birthdate;
        $parsedDate = preg_replace('/\(.*\)/', '', $birthdate);
        $timestamp = strtotime($parsedDate);
        $data->birthdate = date('Y-m-d', $timestamp);
        $data->sex = $request->sex;
        $data->civil_status = $request->civil_status;
        $data->blood_type = $request->blood_type;
        //$data->city = $request->city;
        //$data->oscaid = $request->firstname;
        $data->address = $request->address;
        $data->referredby = $request->referredby;
        //$data->religionid = $request->firstname;
        $data->remarks = $request->remarks;
        $data->occupation = $request->occupation;
        $data->isold_patient = $request->isold_patient;
        $data->prev_admission = $request->prev_admission;
        $data->prev_surgeries = $request->prev_surgeries;
        $data->allergies = $request->allergies;
        $data->asthma = $request->asthma;
        $data->newborn_hearing = $request->newborn_hearing;
        $data->tb = $request->tb;
        $data->seizure = $request->seizure;
        $data->hypertension = $request->hypertension;
        $data->diabetes = $request->diabetes;
        $data->copd = $request->copd;
        $data->mo_comorb = $request->mo_comorb;
        $data->fa_comorb = $request->fa_comorb;
        $data->blood_type = $request->blood_type;
        $data->number_members = $request->number_members;
        $data->water_source = $request->water_source;
        $data->breastfeed_dur = $request->breastfeed_dur;
        $data->milk_dur = $request->milk_dur;
        $data->complementary_feeding = $request->complementary_feeding;
        $data->ob_score = $request->ob_score;
        $data->cog_aog = $request->cog_aog;
        $data->maternal_illness = $request->maternal_illness;
        $data->prenatal_checkup = $request->prenatal_checkup;
        $data->vaccination_sup = $request->vaccination_sup;
        $data->maternal_age_dur_preg = $request->maternal_age_dur_preg;
        $data->maternal_b_type = $request->maternal_b_type;
        $data->term_pre_post = $request->term_pre_post;
        $data->nsd_cs = $request->nsd_cs;
        $data->birth_weight = $request->birth_weight;
        $data->cry = $request->cry;
        $data->palce_delivery = $request->palce_delivery;
        $data->complications = $request->complications;
        $data->caregiver_name = $request->caregiver_name;
        $data->caregiver_age = $request->caregiver_age;
        $data->caregiver_rel = $request->caregiver_rel;
        $data->caregiver_contact = $request->caregiver_contact;
        $data->caregiver_occupation = $request->caregiver_occupation;
        $data->siblings_details = $request->siblings_details;
        $data->pmh_others = $request->pmh_others;


        if ($data->profile_name != null) {
            $oldFilePath = public_path('profiles/' . $data->profile_name);
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }
        }

        $fname = '';
        if ($request->hasFile('profile_pic')) {
            $file = $request->file('profile_pic');
            $fname = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('pp', $fname, 'public');
            $data->profile_name = $fname;
        }

        $fam = '';
        $getFam = explode(",", $request->fam);
        if ($request->fam) {
            foreach ($getFam as $key => $value) {
                $fam .= $value . ',';
            }
            $data->fam = substr($fam, 0, -1);
        }
        $data->fam_others = $request->fam_others;

        /* $pmh = '';
        if($request->pmh){
            foreach ($request->pmh as $key => $value) {
                $pmh.=$value.',';
            }
            $data->pmh = substr($pmh, 0, -1);
        }
        $data->pmh_others = $request->pmh_others; */

        $soc = '';
        $getSoc = explode(",", $request->soc);
        if ($request->soc) {
            foreach ($getSoc as $key => $value) {
                $soc .= $value . ',';
            }
            $data->soc = substr($soc, 0, -1);
        }
        $data->soc_others = $request->soc_others;

        $data->save();
        return response()->json($request);
    }

    public function saveAppointment(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $apt_dt = date_format(date_create($request->apt_dt), 'Y-m-d');
        $check_sequence = Appointments::where(['appointment_dt' => $apt_dt, 'state' => 0])->orderBy('sequence', 'desc')->first();
        if ($check_sequence) {
            $sequence = $check_sequence->sequence + 1;
        } else {
            $sequence = 1;
        }
        $data = new Appointments();
        $data->patientid = $request->pid;
        //$data->chiefcomplaints = $request->complaints;
        $data->appointment_dt = $apt_dt;
        $data->created_by = Auth::user()->id;
        $data->created_dt = date("Y-m-d H:i:s");
        $data->nurse_remarks = $request->nurse_remarks;
        $data->sequence = $sequence;
        $data->weight = $request->weight;
        $data->height = $request->height;
        $data->vit_temp = $request->vit_temp;
        $data->vit_sys = $request->vit_sys;
        $data->vit_dia = $request->vit_dia;
        $data->vit_cr = $request->vit_cr;
        $data->vit_rr = $request->vit_rr;
        $data->save();

        event(new NewAppointments($data));
        return $data;
    }

    public function getPatient($id)
    {
        //return Patients::where(['id' => $id])->first();
        $patient = Patients::where(['id' => $id])->first();

        //$oldFilePath = url('storage/pp/' . $patient->profile_name);
        $fileUrl = '';

        if ($patient->profile_name != null) {
            $fileName = $patient->profile_name;
            $fileUrl = url('/storage/app/public/pp/' . $fileName);
            //$fileUrl = url('/storage/pp/' . $fileName);
        }

        $patient->profile = $fileUrl;
        return $patient;
    }

    public function updateAppointment(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $ff_dt = '';
        if ($request->followup != 'Invalid date') {
            $ff_dt = date_format(date_create($request->followup), 'Y-m-d');
        }
        $undersigned = '';
        if ($request->medcert_undersigned != 'Invalid date') {
            $undersigned = date_format(date_create($request->medcert_undersigned), 'Y-m-d');
        }
        $ref_undersigned = '';
        if ($request->referral_undersigned != 'Invalid date') {
            $ref_undersigned = date_format(date_create($request->referral_undersigned), 'Y-m-d');
        }
        $risk_undersigned = '';
        if ($request->risk_undersigned != 'Invalid date') {
            $risk_undersigned = date_format(date_create($request->risk_undersigned), 'Y-m-d');
        }
        $clearance_undersigned = '';
        if ($request->clearance_undersigned != 'Invalid date') {
            $clearance_undersigned = date_format(date_create($request->clearance_undersigned), 'Y-m-d');
        }
        $fit_undersigned = '';
        if ($request->fit_undersigned != 'Invalid date') {
            $fit_undersigned = date_format(date_create($request->fit_undersigned), 'Y-m-d');
        }
        $data = Appointments::find($request->id);
        $data->chiefcomplaints = $request->chiefcomplaints;
        $data->updated_by = Auth::user()->id;
        $data->updated_dt = date("Y-m-d H:i:s");
        $data->vit_sys = $request->vit_sys;        
        $data->nurse_remarks = $request->nurse_remarks;
        $data->vit_dia = $request->vit_dia;
        $data->vit_temp = $request->vit_temp;
        $data->vit_cr = $request->vit_cr;
        $data->vit_rr = $request->vit_rr;
        $data->weight = $request->weight;
        $data->height = $request->height;
        $data->bmi = $request->bmi;
        $data->remarks = $request->remarks;
        $data->withs2 = $request->withs2;
        $data->medcert_undersigned = $request->medcert_undersigned != 'Invalid date' ? $undersigned : null;//$undersigned;
        $data->medcert_diagnosis = $request->medcert_diagnosis;
        $data->medcert_remarks = $request->medcert_remarks;
        //$data->medcert_remarks = $request->medcert_remarks;

        $data->email = $request->email;

        /* $data->pregnancy = $request->pregnancy;
        $data->lmp = $request->lmp;
        $data->contraceptive_use = $request->contraceptive_use;
        $data->menopause = $request->menopause;
        $data->mother_details = $request->mother_details;
        $data->father_details = $request->father_details; */
        
        
        $data->referral_doctor = $request->referral_doctor;
        $data->referral_addr1 = $request->referral_addr1;
        $data->referral_addr2 = $request->referral_addr2;
        $data->referral_undersigned = $request->referral_undersigned != 'Invalid date' ? $ref_undersigned : null;//$undersigned;
        $data->referral_diagnosis = $request->referral_diagnosis;
        $data->referral_remarks = $request->referral_remarks;

        if ($request->medcert_opt1) {
            $data->medcert_opt1 = 1;
        } else {
            $data->medcert_opt1 = 0;
        }

        if ($request->medcert_opt2) {
            $data->medcert_opt2 = 1;
        } else {
            $data->medcert_opt2 = 0;
        }

        if ($request->medcert_opt3) {
            $data->medcert_opt3 = 1;
        } else {
            $data->medcert_opt3 = 0;
        }

        if ($request->medcert_opt4) {
            $data->medcert_opt4 = 1;
        } else {
            $data->medcert_opt4 = 0;
        }

        $data->medcert_opt4_text1 = $request->medcert_opt4_text1;
        $data->medcert_opt4_text2 = $request->medcert_opt4_text2;
        $data->medcert_opt4_text3 = $request->medcert_opt4_text3;
        $data->medcert_opt1_text1 = $request->medcert_opt1_text1;

        /* $data->risk_remarks = $request->risk_undersigned!='Invalid date'?$risk_undersigned:null;//$undersigned;
        $data->risk_diagnosis = $request->risk_diagnosis;
        $data->risk_remarks = $request->risk_remarks; */

        //$data->diagnostics_remarks = $request->diagnostics_remarks;
        $data->lab_remarks = $request->lab_remarks;
        $data->ancillary_remarks = $request->ancillary_remarks;
        $data->discount = $request->discount;
        $data->followup = $request->followup != 'Invalid date' ? $ff_dt : null;
        $data->history = $request->history;
        //$data->pastmedicalrecord = $request->pastmedicalrecord;
        $data->pe = $request->pe;
        $data->diagnosis = $request->diagnosis;
        $data->fasting_mode = $request->fasting_mode;
        $data->send_xray_email = $request->sendXrayToEmail ?? 0;
        //$data->plan = $request->plan;

        /* $data->clearance_undersigned = $request->clearance_undersigned!='Invalid date'?$clearance_undersigned:null;//$undersigned;
        $data->clearance_diagnosis = $request->clearance_diagnosis;
        $data->clearance_remarks = $request->clearance_remarks*/
        

        /* $data->fit_undersigned = $request->fit_undersigned!='Invalid date'?$fit_undersigned:null;//$undersigned;
        $data->fit_diagnosis = $request->fit_diagnosis;
        $data->fit_remarks = $request->fit_remarks;
        $data->fit_remarks = $request->fit_remarks;
        $data->fit_treatment = $request->fit_treatment; */
        $data->save();

        //assume that update is final
        /* $checkFfDt = Appointments::where(['patientid'=>$request->patientid,'followup'=>$ff_dt])->first();
        if($request->followup_dt && !$checkFfDt) { */
        $apt_dt = date_format(date_create($request->apt_dt), 'Y-m-d');
        $check_if_has_appointment = Appointments::where(['appointment_dt' => $ff_dt, 'patientid' => $request->patientid])->first();
        if ($request->followup != 'Invalid date') {
            if ($check_if_has_appointment == null) {
                $apt_dt = date_format(date_create($request->apt_dt), 'Y-m-d');
                $check_sequence = Appointments::where(['appointment_dt' => $apt_dt, 'state' => 0])->orderBy('sequence', 'desc')->first();
                if ($check_sequence) {
                    $sequence = $check_sequence->sequence + 1;
                } else {
                    $sequence = 1;
                }
                $data = new Appointments();
                $data->patientid = $request->patientid;
                $data->chiefcomplaints = 'Follow up checkup';
                $data->appointment_dt = $request->followup != 'Invalid date' ? $ff_dt : null;
                $data->created_by = Auth::user()->id;
                $data->created_dt = date("Y-m-d H:i:s");
                $data->nurse_remarks = $request->remarks;
                $data->sequence = $sequence;
                $data->save();
            }
        }
        //}

        $dataPatient = Patients::where('patientid', $request->patientid)->first();
        $dataPatient->prev_admission = $request->prev_admission;
        $dataPatient->prev_surgeries = $request->prev_surgeries;
        $dataPatient->allergies = $request->allergies;
        $dataPatient->asthma = $request->asthma;
        $dataPatient->newborn_hearing = $request->newborn_hearing;
        $dataPatient->tb = $request->tb;
        $dataPatient->seizure = $request->seizure;
        $dataPatient->hypertension = $request->hypertension;
        $dataPatient->smoking_details = $request->smoking_details;
        $dataPatient->alcohol_details = $request->alcohol_details;
        $dataPatient->diabetes = $request->diabetes;
        $dataPatient->copd = $request->copd;
        $dataPatient->pmh_others = $request->pmh_others;

        $dataPatient->pregnancy = $request->pregnancy;
        $dataPatient->lmp = $request->lmp;
        $dataPatient->contraceptive_use = $request->contraceptive_use;
        $dataPatient->menopause = $request->menopause;
        $dataPatient->mother_details = $request->mother_details;
        $dataPatient->father_details = $request->father_details;
        $fam = '';
        $getFam = explode(",", $request->fam);
        if ($request->fam) {
            foreach ($getFam as $key => $value) {
                $fam .= $value . ',';
            }
            $dataPatient->fam = substr($fam, 0, -1);
        }
        $dataPatient->fam_others = $request->fam_others;
        

        
        $soc = '';
        $getSoc = explode(",", $request->soc);
        if ($request->soc) {
            foreach ($getSoc as $key => $value) {
                $soc .= $value . ',';
            }
            $dataPatient->soc = substr($soc, 0, -1);
        }
        $dataPatient->soc_others = $request->soc_others;
        $dataPatient->vaccination_sup = $request->vaccination_sup;
        
        $dataPatient->save();
        
        return response()->json($data->medcert_opt3 == 1 ? 7 : 8);
    }

    public function getAppointmentDetails($id)
    {
        $data = Appointments::find($id);
        $getPreviousRecords = DB::table('appointments')
            ->where('patientid', $data->patientid)
            ->where('is_cancel', 0)
            ->orderBy('appointment_dt', 'desc')
            ->limit(2)
            ->get();
        $px_profile = Helpers::patientDetail($data->patientid);
        $px_profile->profile_name = url('/storage/app/public/pp/' . $px_profile->profile_name);
        $data->medcert_opt1 = $data->medcert_opt1 == 1 ? true : false;
        $data->medcert_opt2 = $data->medcert_opt2 == 1 ? true : false;
        $data->medcert_opt3 = $data->medcert_opt3 == 1 ? true : false;
        $data->medcert_opt4 = $data->medcert_opt4 == 1 ? true : false;
        /* $get_OldPatients = OldPatients::where(["Patient_id" => $px_profile->patientid])->first();
        $get_OldDiagnosis = $get_OldPatients ? OldDiagnosis::where(["PatientID" => $get_OldPatients->PatientID])->get() : []; */
        $getVitals = Appointments::where(["patientid" => $data->patientid])->orderBy('appointment_dt', 'desc')->get();
        //$old_data = array();
        $vitals_data = array();
        /* foreach ($get_OldDiagnosis as $key => $value) {
            $arr = array();
            $arr['hpi'] = $value->HPI;
            $arr['pmhx'] = $value->pmHx;
            $arr['desc'] = $value->description;
            $arr['date'] = date_format(date_create($value->DateOfVisit), 'F d, Y');
            $arr['cc'] = $value->CC;
            $arr['recom'] = $value->Recom;
            $old_data[] = $arr;
        } */

        foreach ($getVitals as $key => $value) {
            $arr = array();
            $arr['bp'] = $value->vit_sys . '/' . $value->vit_dia;
            $arr['weight'] = $value->weight;
            $arr['date'] = date_format(date_create($value->appointment_dt), ' F d,Y');
            $vitals_data[] = $arr;
        }
        return response()->json([
            //'vitals_data'=>$vitals_data,
            //'get_OldDiagnosis'=>$old_data,
            //'get_OldPatients'=> $get_OldPatients,
            'px_profile' => $px_profile,
            'data' => $data,
            //'getPreviousRecords'=>$getPreviousRecords,
            'prev_data' => sizeof($getPreviousRecords) > 1 ? $getPreviousRecords[1] : []
        ]);
    }

    public function appointmentList(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $searchParams = $request->all();
        //$userQuery = Appointments::query();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = Arr::get($searchParams, 'keyword', '');
        $date = Arr::get($searchParams, 'date', '');
        $formattedDt = $date ? date_format(date_create($date), 'Y-m-d') : date("Y-m-d");
        $isdone = Arr::get($searchParams, 'isdone');
        $state = Arr::get($searchParams, 'state');
        // $userQuery = Appointments::selectRaw(" * left join patients on appointments.patientid = patients.patientid")->paginate($limit);

        $userQuery = DB::table('appointments')
            ->join('patients', 'patients.patientid', '=', 'appointments.patientid')
            ->select('patients.patientname', 'patients.profile_name', 'appointments.isactive', 'appointments.cancel_reason', 'appointments.state', 'appointments.sequence', 'appointments.isdone', 'patients.profile', 'patients.isold_patient', 'appointments.chiefcomplaints', 'appointments.discount', 'appointments.patientid', 'appointments.id', 'appointments.appointment_dt')
            //->where('LOWER(patients.patientname)', 'LIKE', '%'.$keyword.'%')
            //->whereRaw('LOWER(patients.patientname) LIKE ?', ['%'.$keyword.'%'])

            //->whereRaw('LOWER(patients.patientname) LIKE ? AND appointment_dt >= CURDATE() AND isdone = false AND is_cancel = false', ['%'.strtolower($keyword).'%'])
            //->whereRaw('LOWER(patients.patientname) LIKE ? AND appointments.appointment_dt = ? AND appointments.isdone = ? AND appointments.is_cancel = false', ['%'.strtolower($keyword).'%',$formattedDt,$isdone])
            //->whereRaw('LOWER(patients.patientname) LIKE ? AND appointments.appointment_dt = ? AND appointments.state = ? AND appointments.is_cancel = false', ['%'.strtolower($keyword).'%',$formattedDt,$state])
            ->whereRaw('patients.patientname LIKE ? AND appointments.appointment_dt = ? AND appointments.state = ?', ['%' . $keyword . '%', $formattedDt, $state])
            ->orderBy('appointments.sequence', 'asc')
            ->paginate($limit);

        /* if (!empty($keyword)) {
            $userQuery->where('id', 'LIKE', '%' . $keyword . '%');
        } */
        return AppointmentResource::collection($userQuery);
    }

    public function findPatient($kw)
    {
        $q = DB::connection('mysql')->select("select * from patients where patientname like '%" . $kw . "%' and isdeleted = 0 order by id desc limit 100");

        $data = array();
        foreach ($q as $key => $value) {
            $arr = array();
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
        $data['query_prescription'] = Rx::where(['appointment_id' => $id])->get();
        $data['appointment_detail'] = Appointments::where(['id' => $id])->first();
        $data['profile'] = Profile::where(['id' => 1])->first();
        $data['patient_detail'] = Patients::where(['patientid' => $data['appointment_detail']->patientid])->first();
        $myPdf = new CustomPrescriptiontest($data);
        $myPdf->Output('I', time() . "-.pdf", true);
        exit;
    }

    public function printpdf2($id)
    {
        $data = array();
        $data['query_prescription'] = Rx::where(['appointment_id' => $id])->orderby('rx_id','asc')->get();
        $data['appointment_detail'] = Appointments::where(['id' => $id])->first();
        $data['profile'] = Profile::where(['id' => 1])->first();
        $data['patient_detail'] = Patients::where(['patientid' => $data['appointment_detail']->patientid])->first();
        $myPdf = new CustomPrescriptiontestA5Portrait($data);
        $myPdf->Output('I', time() . "-.pdf", true);
        exit;
    }
    
    public function emailPrescription($id)
    {
        date_default_timezone_set('Asia/Manila');
        $data = [];
        $data['query_prescription'] = Rx::where('appointment_id', $id)->get();
        $data['appointment_detail'] = Appointments::where('id', $id)->first();
        $data['profile'] = Profile::where('id', 1)->first();
        $data['patient_detail'] = Patients::where('patientid', $data['appointment_detail']->patientid)->first();
    
        // Generate the PDF and store it in memory
        $pdf = new CustomPrescriptiontestA5Portrait($data);
        $pdfContent = $pdf->Output('', 'S'); // Output as string (S = string)
        $subject = date("F d, Y");
        // Send email with PDF attached
        Mail::to($data['appointment_detail']->email)->send(new PrescriptionPdfMail($pdfContent,$subject));
    
        return response()->json(['message' => 'PDF sent via email.']);
    }

    public function printmedcert($id)
    {
        $data = array();
        $data['appointment_detail'] = Appointments::where(['id' => $id])->first();
        $data['profile'] = Profile::where(['id' => 1])->first();
        $data['patient_detail'] = Patients::where(['patientid' => $data['appointment_detail']->patientid])->first();
        $myPdf = new MedCertA5($data);
        $myPdf->Output('I', time() . "-.pdf", true);
        exit;
    }

    public function printreferral($id)
    {
        $data = array();
        $data['appointment_detail'] = Appointments::where(['id' => $id])->first();
        $data['profile'] = Profile::where(['id' => 1])->first();
        $data['patient_detail'] = Patients::where(['patientid' => $data['appointment_detail']->patientid])->first();
        $myPdf = new Referral($data);
        $myPdf->Output('I', time() . "-.pdf", true);
        exit;
    }

    public function printriskstrat($id)
    {
        $data = array();
        $data['appointment_detail'] = Appointments::where(['id' => $id])->first();
        $data['profile'] = Profile::where(['id' => 1])->first();
        $data['patient_detail'] = Patients::where(['patientid' => $data['appointment_detail']->patientid])->first();
        $myPdf = new RiskStrat($data);
        $myPdf->Output('I', time() . "-.pdf", true);
        exit;
    }

    public function printclearance($id)
    {
        $data = array();
        $data['appointment_detail'] = Appointments::where(['id' => $id])->first();
        $data['profile'] = Profile::where(['id' => 1])->first();
        $data['patient_detail'] = Patients::where(['patientid' => $data['appointment_detail']->patientid])->first();
        $myPdf = new Clearance($data);
        $myPdf->Output('I', time() . "-.pdf", true);
        exit;
    }

    public function printfittowork($id)
    {
        $data = array();
        $data['appointment_detail'] = Appointments::where(['id' => $id])->first();
        $data['profile'] = Profile::where(['id' => 1])->first();
        $data['patient_detail'] = Patients::where(['patientid' => $data['appointment_detail']->patientid])->first();
        $myPdf = new FitToWork($data);
        $myPdf->Output('I', time() . "-.pdf", true);
        exit;
    }

    public function printrequest($id, $type)
    {
        $data = array();
        $data['query_prescription'] = Ancillary::where(['appointment_id' => $id])->get();
        $data['appointment_detail'] = Appointments::where(['id' => $id])->first();
        $data['profile'] = Profile::where(['id' => 1])->first();
        $data['patient_detail'] = Patients::where(['patientid' => $data['appointment_detail']->patientid])->first();
        $data['type'] = $type;
        $myPdf = new RequestprescriptionA5($data);
        $myPdf->Output('I', time() . "-.pdf", true);
        exit;
    }

    public function printchart($id)
    {
        $data = array();
        $data['patient_detail'] = Patients::where(['patientid' => $id])->first();
        /* $data['query_prescriptions'] = Rx::where(['appointment_id'=>$id])->get();
        $data['query_diagnostics'] = Ancillary::where(['appointment_id'=>$id])->get(); */
        $data['profile'] = Profile::where(['id' => 1])->first();
        $data['getHistory'] = Appointments::where(['patientid' => $data['patient_detail']->patientid,'isdone' => 1])->orderby('id','desc')->get();
        $myPdf = new ChartRecordPdf($data);
        $myPdf->Output('I', time() . "-.pdf", true);
        exit;
    }

    function getpastConsultationList($id)
    {
        $data = Appointments::where(['patientid' => $id])->get();

        $px_profile = Helpers::patientDetail($id);
        /* $get_OldPatients = OldPatients::where(["Patient_id" => $px_profile->patientid])->first();
        $get_OldDiagnosis = $get_OldPatients ? OldDiagnosis::where(["PatientID" => $get_OldPatients->PatientID])->get() : [];
        $old_data = array();
        foreach ($get_OldDiagnosis as $key => $value) {
            $arr = array();
            $arr['hpi'] = $value->HPI;
            $arr['pmhx'] = $value->pmHx;
            $arr['desc'] = $value->description;
            $arr['date'] = date_format(date_create($value->DateOfVisit), 'F d, Y');
            $arr['cc'] = $value->CC;
            $arr['recom'] = $value->Recom;
            $old_data[] = $arr;
        } */

        $array = array();
        foreach ($data as $key => $value) {
            $arr = array();
            $arr['date'] = date_format(date_create($value->appointment_dt), 'F d, Y');
            $arr['cf'] = $value->chiefcomplaints;
            $arr['id'] = $value->id;
            $array[] = $arr;
        }
        return response()->json(['data' => $array]);
    }

    function deleteMed($id)
    {
        RX::where('rx_id', $id)->delete();
        return response()->json(true);
    }

    public function addMed(Request $request)
    {
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
        $rx->medicine = $request->custom_meds ? $request->custom_brand : $request->meds;
        $rx->generic_id = $request->custom_meds ? 0 : $medicineDetail->generic_id;
        $rx->generic_name = $request->custom_meds ? $request->custom_generic: $medicineDetail->generic_name;
        $rx->save();
        return response()->json($medicineDetail);
    }

    public function updateMed(Request $request, $id)
    {
        $rx = RX::find($id);
        if (!$rx) {
            return response()->json(['error' => 'Medicine not found'], 404);
        }

        $medicineDetail = null;
        if (!$request->custom_meds) {
            $medicineDetail = Helpers::medicineDetail($request->med_id);
        }

        $rx->medicine_id = $request->custom_meds ? 0 : $request->med_id;
        $rx->breakfastbefore = $request->bf_b;
        $rx->breakfastafter = $request->bf_a;
        $rx->lunchbefore = $request->l_b;
        $rx->lunchafter = $request->l_a;
        $rx->supperbefore = $request->s_b;
        $rx->supperafter = $request->s_a;
        $rx->bedtime = $request->bt;
        $rx->qty = $request->qty;
        $rx->remarks = $request->remarks;
        $rx->medicine = $request->custom_meds ? $request->custom_brand : $request->meds;
        $rx->generic_id = $request->custom_meds ? 0 : ($medicineDetail ? $medicineDetail->generic_id : 0);
        $rx->generic_name = $request->custom_meds ? $request->custom_generic . ' ' . $request->custom_dosage : ($medicineDetail ? $medicineDetail->generic_name . ' ' . $medicineDetail->unit : '');
        $rx->save();
        
        return response()->json(['success' => true, 'message' => 'Medicine updated successfully']);
    }

    function deleteDiagnostic($id)
    {
        Ancillary::where('id', $id)->delete();
        return response()->json(true);
    }

    public function addDiagnostic(Request $request)
    {
        foreach ($request->rendered as $key => $value) {
            $rx = new Ancillary();
            $rx->appointment_id = $value['id'];
            $rx->ancillary_id = $value['procedure_id'];
            $rx->ancillary = $value['procedure'];
            $rx->remarks = $value['remarks'];
            $rx->micro_remarks = $value['lab_micro_remarks'];
            $rx->xray_remarks = $value['xray_remarks'];
            $rx->type = $value['type'];
            $rx->save();
        }
        return response()->json(true);
    }

    function deleteService($id)
    {
        Rx_service::where('rendered_id', $id)->delete();
        return response()->json(true);
    }

    public function addService(Request $request)
    {
        foreach ($request->rendered as $key => $value) {
            $rx = new Rx_service();
            $rx->appointment_id = $value['id'];
            $rx->service_id = $value['service_id'];
            $rx->fee = $value['fee'];
            $rx->created_dt = date("Y-m-d H:i:s");
            $rx->service = $value['service'];
            //$rx->discount = $request->discount;
            $rx->total = $request->fee - $request->discount;
            $rx->save();
        }
        $field = Appointments::find($request->id);
        $field->discount = $request->discount;
        $field->save();
        return response()->json(true);
    }

    function getAppointmentDiagnostics($id)
    {
        $data = Ancillary::where(['appointment_id' => $id])->get();
        $array = array();
        foreach ($data as $key => $value) {
            $arr = array();
            $arr['type'] = $value->type == 1 ? 'Lab' : 'Ancillary';
            $arr['diagnostic'] = $value->ancillary;
            $arr['ancillary_id'] = $value->ancillary_id;
            $arr['remarks'] = $value->remarks;
            $arr['id'] = $value->id;
            $array[] = $arr;
        }
        return response()->json(['data' => $array]);
    }

    function getAppointmentMedicine($id)
    {
        $data = RX::where(['appointment_id' => $id])->orderby('rx_id','desc')->get();
        $array = array();
        foreach ($data as $key => $value) {
            $arr = array();
            $arr['medicine'] = $value->generic_name.' '. $value->medicine ;
            $arr['generic'] = $value->generic_name;
            $arr['brand'] = $value->medicine;
            $arr['dosage'] = $value->medicine;
            $arr['qty'] = $value->qty;
            $arr['bb'] = $value->breakfastbefore;
            $arr['ab'] = $value->breakfastafter;
            $arr['bl'] = $value->lunchbefore;
            $arr['al'] = $value->lunchafter;
            $arr['bs'] = $value->supperbefore;
            $arr['as'] = $value->supperafter;
            $arr['bt'] = $value->bedtime;
            $arr['remarks'] = $value->remarks;
            $arr['medicineId'] = $value->medicine_id;
            $arr['id'] = $value->rx_id;
            $array[] = $arr;
        }
        return response()->json(['data' => $array]);
    }

    function getAppointmentService($id)
    {
        $data = Rx_service::where(['appointment_id' => $id])->get();
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

    function doneConsult($id)
    {
        $field = Appointments::find($id);
        $field->isdone = true;
        $field->state = 1;
        $field->isactive = 0;
        $field->save();
        return response()->json(true);
    }

    function getAttachments($id)
    {
        $getidno = explode("-0", $id);
        if (sizeof($getidno) > 1) {
            $data = Attachments::where(['patientid' => $getidno[1]])->get();
        } else {
            $data = Attachments::where(['patientid' => $id])->get();
        }
        // select * from patients_attachments pa where '03182'  = pa.patientid
        // ('name', 'like', '%' . Input::get('name') . '%')->get();
        $array = array();
        foreach ($data as $key => $value) {
            $arr = array();
            $fileName = $value->filename;
            $fileUrl = url('public/storage/uploads/' . $fileName);
            //$fileUrl = url('/storage/uploads/' . $fileName);
            $fileExt = explode(".", $fileName);
            $arr['newfile'] = $fileUrl;//$value->isold_record==0?$value->file:null;
            $arr['oldfile'] = $fileUrl;//$value->isold_record==1?$value->file:null;
            $arr['extension'] = $fileExt[1];
            $arr['id'] = $value->AttachmentID;
            $arr['fname'] = $fileName;
            $arr['description'] = $value->description;
            $arr['created_dt'] = date_format(date_create($value->created_dt), "F d, Y");
            $array[] = $arr;
        }
        return response()->json(['data' => $array]);
    }


    public function addpatientAttachments(Request $request)
    {
        try {
            date_default_timezone_set('Asia/Manila');
            
            if (!$request->hasFile('files')) {
                return response()->json(['error' => 'No files uploaded'], 400);
            }
            
            // Validate file size (10MB limit)
            $maxFileSize = 10 * 1024 * 1024; // 10MB in bytes
            
            $uploadedFiles = [];
            foreach ($request->file('files') as $file) {
                // Check file size
                if ($file->getSize() > $maxFileSize) {
                    return response()->json(['error' => 'File too large. Maximum size is 10MB.'], 413);
                }
                
                // Check if file is valid
                if (!$file->isValid()) {
                    return response()->json(['error' => 'Invalid file: ' . $file->getErrorMessage()], 400);
                }
                
                $att = new Attachments();
                $getidno = explode("-0", $request->patientid);
                
                // Use unique filename to prevent conflicts
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '_' . uniqid() . '.' . $extension;
                
                // Store file with unique name
                $file->storeAs('uploads', $filename, 'public');
                
                $att->patientid = sizeof($getidno) > 1 ? $getidno[1] : $request->patientid;
                $att->filename = $filename;
                $att->created_dt = date("Y-m-d H:i:s");
                $att->isold_record = false;
                $att->save();
                
                $uploadedFiles[] = [
                    'filename' => $filename,
                    'original_name' => $originalName,
                    'size' => $file->getSize()
                ];
            }

            return response()->json([
                'success' => true,
                'files' => $uploadedFiles,
                'message' => 'Files uploaded successfully'
            ]);
        } catch (\Illuminate\Http\Exceptions\PostTooLargeException $e) {
            return response()->json(['error' => 'File too large. Please reduce file size and try again.'], 413);
        } catch (\Exception $e) {
            \Log::error('Upload error: ' . $e->getMessage(), [
                'patient_id' => $request->patientid,
                'files_count' => $request->hasFile('files') ? count($request->file('files')) : 0
            ]);
            return response()->json(['error' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }

    function deleteAttachment($id)
    {
        Attachments::where('AttachmentID', $id)->delete();
        return response()->json(true);
    }

    public function dashboard()
    {
        date_default_timezone_set('Asia/Manila');
        
        // Use count() instead of get()->count() for better performance
        $count_px = Patients::where('isdeleted', 0)->count();
        $count_meds = Medicine::count();
        $count_diagnostics = Services::count();
        
        // Count new patients added today
        $new_patients_today = Patients::where('isdeleted', 0)
            ->whereDate('created_at', date('Y-m-d'))
            ->count();
        
        // Optimize today's appointments query with patient names in one query
        $todays_appt = DB::table('appointments')
            ->join('patients', 'appointments.patientid', '=', 'patients.patientid')
            ->where('appointments.appointment_dt', date("Y-m-d"))
            ->where('appointments.is_cancel', 0)
            ->select(
                'appointments.patientid',
                'appointments.chiefcomplaints',
                //'appointments.appointment_time',
                'appointments.isdone',
                'patients.patientname'
            )
            ->orderBy('appointments.appointment_dt', 'asc')
            ->get();
        
        // Optimize graph census query
        $graph_census = DB::table('appointments')
            ->selectRaw('count(appointment_dt) as cnt, DATE_FORMAT(appointment_dt, "%Y-%m") as apt_dt')
            ->where('is_cancel', 0)
            ->groupBy(DB::raw('DATE_FORMAT(appointment_dt, "%Y-%m")'))
            ->orderBy('apt_dt', 'asc')
            ->get();
        
        // Optimize calendar query with patient names in one query (no more N+1 queries!)
        $calendar = DB::table('appointments')
            ->join('patients', 'appointments.patientid', '=', 'patients.patientid')
            ->where('appointments.is_cancel', 0)
            ->select(
                'appointments.appointment_dt',
                'appointments.patientid',
                'patients.patientname'
            )
            ->groupBy('appointments.appointment_dt', 'appointments.patientid', 'patients.patientname')
            ->get();
        
        // Process graph data
        $data_graph = [];
        $data_graph_month = [];
        foreach ($graph_census as $value) {
            $data_graph[] = $value->cnt;
            $data_graph_month[] = date_format(date_create($value->apt_dt), 'F Y');
        }
        
        // Process calendar data (no more N+1 queries!)
        $data_calendar = [];
        foreach ($calendar as $value) {
            $data_calendar[] = [
                'title' => $value->patientname ?: 'Unknown Patient',
                'start' => date_format(date_create($value->appointment_dt), 'Y-m-d')
            ];
        }
        
        // Process today's patients (no more N+1 queries!)
        $data_todays_pxs = [];
        foreach ($todays_appt as $value) {
            $data_todays_pxs[] = [
                'patient' => $value->patientname ?: 'Unknown Patient',
                'complaints' => $value->chiefcomplaints,
               //'appointment_time' => $value->appointment_time,
                'isdone' => $value->isdone
            ];
        }
        
        // Optimize revenue query
        $graph_revenue = DB::table('appointments')
            ->leftJoin('servicesrendered', 'appointments.id', '=', 'servicesrendered.appointment_id')
            ->where('appointments.is_cancel', 0)
            ->selectRaw('
                COALESCE(SUM(servicesrendered.fee), 0) as amt,
                COALESCE(SUM(appointments.discount), 0) as discount,
                DATE_FORMAT(appointments.appointment_dt, "%Y-%m") as apt_dt
            ')
            ->groupBy(DB::raw('DATE_FORMAT(appointments.appointment_dt, "%Y-%m")'))
            ->orderBy('apt_dt', 'asc')
            ->get();
        
        // Process revenue data
        $revenue_arr = [];
        $revenue_month_arr = [];
        foreach ($graph_revenue as $value) {
            $revenue_arr[] = max(0, ($value->amt ?: 0) - ($value->discount ?: 0));
            $revenue_month_arr[] = date_format(date_create($value->apt_dt), 'F Y');
        }
        
        // Calculate completed and pending counts based on isdone status
        $completed_today = $todays_appt->where('isdone', 1)->count();
        $pending_today = $todays_appt->where('isdone', 0)->count();
        
        return response()->json([
            'graph_amt' => [["name" => 'Total', 'data' => $revenue_arr]],
            'revenue_mon' => $revenue_month_arr,
            'todaysAppt' => $data_todays_pxs,
            'calendar' => $data_calendar,
            'graph_census' => [["name" => 'No. of Patients', 'data' => $data_graph]],
            'data_graph_month' => $data_graph_month,
            'appt' => $todays_appt->count(),
            'patients' => $count_px,
            'meds' => $count_meds,
            'dx' => $count_diagnostics,
            'completed_today' => $completed_today,
            'pending_today' => $pending_today,
            'new_patients_today' => $new_patients_today
        ]);
    }

    function cancelAppointment(Request $request)
    {
        $field = Appointments::find($request->id);
        $field->is_cancel = true;
        $field->cancel_reason = $request->cancel_reason;
        $field->state = 2;
        $field->isactive = 0;
        $field->save();
        return response()->json(true);
    }

    function updateBP(Request $request)
    {
        $field = Appointments::find($request->id);
        $field->vit_sys = $request->vit_sys;
        $field->vit_dia = $request->vit_dia;
        $field->vit_temp = $request->vit_temp;
        $field->vit_rr = $request->vit_rr;
        $field->vit_cr = $request->vit_cr;
        $field->weight = $request->weight;
        $field->height = $request->height;
        $field->bmi = $request->bmi;
        $field->save();
        return response()->json(true);
    }

    function reorderAppointment(Request $request)
    {
        foreach ($request->data as $key => $value) {
            $data = Appointments::find($value['id']);
            $data->sequence = $key + 1;
            $data->save();
        }
        return response()->json(['data' => $request->data]);
    }

    function setActive($id)
    {
        $field = Appointments::find($id);
        $field->isactive = 1;
        $field->save();
        return response()->json(true);
    }

    function updateCivil()
    {
        $patients = Patients::all();
        foreach ($patients as $key => $value) {
            $old = DB::connection('old_emr')->select("select * from patients where Patient_id = " . $value->patientid);
            $data = Patients::find($value->id);
            if ($old) {
                if ($old[0]->status == 1) {
                    $data->civil_status = 'Single';
                } else if ($old[0]->status == 2) {
                    $data->civil_status = 'Married';
                } else if ($old[0]->status == 3) {
                    $data->civil_status = 'Widowed';
                } else if ($old[0]->status == 4) {
                    $data->civil_status = 'Legally Separated';
                }
                $data->save();
            }
        }
        return true;
    }

    public function api_saveAppointment(Requet $request)
    {
        if (env('SECRET_PASS') == "2024p@!") {
            date_default_timezone_set('Asia/Manila');
            $apt_dt = date_format(date_create($request->apt_dt), 'Y-m-d');
            $check_sequence = Appointments::where(['appointment_dt' => $apt_dt, 'state' => 0])->orderBy('sequence', 'desc')->first();
            if ($check_sequence) {
                $sequence = $check_sequence->sequence + 1;
            } else {
                $sequence = 1;
            }
            $bday = date_format(date_create($request->bday), 'Y-m-d');
            $bp = explode("/", $request->bp);
            $check_patient = Patients::where(["firstname" => $request->fname, "lastname" => $request->lname, "birthdate" => $bday])->first();
            if ($check_patient) {
                $data = new Appointments();
                $data->patientid = $check_patient->patientid;
                $data->chiefcomplaints = $request->complaints;
                $data->appointment_dt = $apt_dt;
                $data->created_by = 1;
                $data->created_dt = date("Y-m-d H:i:s");
                $data->nurse_remarks = $request->remarks;
                $data->sequence = $sequence;
                $data->weight = $request->weigth;
                $data->height = $request->height;
                $data->vit_sys = $request->bp ? $bp[0] : '';
                $data->vit_dia = $request->bp ? $bp[1] : '';
                $data->mab_cos_id = $request->mab_cos_id;
                $data->save();
                return response()->json($check_patient);
            } else {
                $data = new Patients();
                $lastinserted = Patients::latest()->value('id') + 1;
                $data->patientname = ucfirst($request->fname) . ' ' . ucfirst(mb_substr($request->mname, 0, 1)) . '. ' . ucfirst($request->lname) . ' ' . ucfirst($request->suffix);
                $data->firstname = $request->fname;
                $data->middlename = $request->mname;
                $data->lastname = $request->lname;
                $data->patientid = date("Ymd") . '-0' . $lastinserted;
                $data->contactno = $request->contactno;
                $data->birthdate = $bday;
                $data->sex = $request->sex;
                $data->civil_status = $request->civil_status;
                $data->address = $request->address;
                $data->created_at = date("Y-m-d H:i:s");
                $data->save();


                $save_data = new Appointments();
                $save_data->patientid = $data->patientid;
                $save_data->chiefcomplaints = $request->complaints;
                $save_data->appointment_dt = $apt_dt;
                $save_data->created_by = 1;
                $save_data->created_dt = date("Y-m-d H:i:s");
                $save_data->nurse_remarks = $request->remarks;
                $save_data->sequence = $sequence;
                $save_data->weight = $request->weigth;
                $save_data->height = $request->height;
                $save_data->vit_sys = $request->bp ? $bp[0] : '';
                $save_data->vit_dia = $request->bp ? $bp[1] : '';
                $save_data->mab_cos_id = $request->mab_cos_id;
                //$data->bmi = $bmi;
                $save_data->save();
                return response()->json($request);
            }
        } else {
            return response()->json(false);
        }
    }

    public function api_updateAppointment(Request $request)
    {
        if (env('SECRET_PASS') == "2024p@!") {
            date_default_timezone_set('Asia/Manila');
            $check_data = Appointments::where(["mab_cos_id" => $request->id])->first();
            Appointments::where(['mab_cos_id' => $request->id])->update([
                'history' => $request->history,
                'diagnosis' => $request->diagnosis,
                'pe' => $request->pe,
                'remarks' => $request->remarks,
                'updated_by' => 1,
                'updated_dt' => date("Y-m-d H:i:s"),
                'followup' => date_format(date_create($request->followup), 'Y-m-d'),
            ]);
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }

    public function api_addMed(Request $request)
    {
        $aid = Appointments::where(['mab_cos_id' => $request->tid])->first();
        foreach ($request->data as $key => $value) {
            $rx = new RX();
            $rx->appointment_id = $aid->id;
            $rx->medicine_id = 0;
            $rx->breakfastbefore = '';
            $rx->breakfastafter = '';
            $rx->lunchbefore = '';
            $rx->lunchafter = '';
            $rx->supperbefore = '';
            $rx->supperafter = '';
            $rx->bedtime = '';
            $rx->qty = $value['quantity'];
            $rx->remarks = $value['instruction'];
            $rx->created_dt = date("Y-m-d H:i:s");
            $rx->medicine = $value['medecine_desc'];
            $rx->generic_id = 0;
            $rx->generic_name = $value['generic_name'];
            $rx->save();
        }
        return response()->json(true);
    }

    public function api_addDiagnostic(Request $request)
    {
        $aid = Appointments::where(['mab_cos_id' => $request->tid])->first();
        foreach ($request->data as $key => $value) {
            $rx = new Ancillary();
            $rx->appointment_id = $aid->id;
            $rx->ancillary_id = 0;
            $rx->ancillary = $value['diagnostic'];
            $rx->remarks = $value['instructions'];
            $rx->type = 0;
            $rx->save();
        }
        return response()->json(true);
    }

    function generateImage()
    {
        $sql = DB::connection('mysql')->select("select * from patients where profile is not null and profile_name is not null");

        foreach ($sql as $key => $value) {

            if ($value->profile) {

                $base64String = preg_replace('/^data:image\/\w+;base64,/', '', $value->profile);

                // Decode the base64 string into binary data
                $imageData = base64_decode($base64String);

                if ($imageData === false) {
                    return response()->json(['error' => 'Base64 decoding failed'], 400);
                }

                // Generate a unique file name
                $fileName = $value->profile_name;//uniqid() . '.png';

                // Define the path to save the image (e.g., public/uploads)
                // $filePath = url('/storage/pp/' . $fileName);///* url('/public/profiles/' . $fileName); */public_path('profiles/' . $fileName);

                // Create the directory if it doesn't exist
                /* if (!file_exists(public_path('profiles'))) {
                    mkdir(public_path('profiles'), 0777, true);
                } */

                // Save the image data to the file
                //file_put_contents($filePath, $imageData);
                Storage::disk('public')->put('pp/' . $fileName, $imageData);
                //$imageData->storeAs('uploads', $fileName, 'public');

            }
        }
        return true;
    }

    function generateAtt()
    {
        $sql = DB::connection('mysql')->select("select * from patients_attachments where file like '%data:image%'");

        foreach ($sql as $key => $value) {


            if ($value->file) {
                $base64String = preg_replace('/^data:image\/\w+;base64,/', '', $value->file);
                // Decode the base64 string into binary data
                $imageData = base64_decode($base64String);

                if ($imageData === false) {
                    return response()->json(['error' => 'Base64 decoding failed'], 400);
                }

                // Generate a unique file name
                $fileName = $value->filename;//uniqid() . '.png';

                // Define the path to save the image (e.g., public/uploads)
                //$filePath = /* url('/public/profiles/' . $fileName); */public_path('uploads/' . $fileName);

                // Create the directory if it doesn't exist
                /* if (!file_exists(public_path('uploads'))) {
                    mkdir(public_path('uploads'), 0777, true);
                } */

                // Save the image data to the file
                //file_put_contents($filePath, $imageData);
                Storage::disk('public')->put('uploads/' . $fileName, $imageData);
            }
        }
        return true;
    }

    public function addProblem(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        if ($request->id != 0) {
            $data = AdditionalCheckList::find($request->id);
        } else {
            $data = new AdditionalCheckList();
        }
        $data->patientid = $request->pid;
        $data->description = $request->description;
        $data->value = $request->value;
        $data->ischeck = $request->isactive;
        $data->created_dt = date("Y-m-d H:i:s");
        $data->save();
        return response()->json($request->id);
    }

    public function getPatientAdditionalCheckList($pid)
    {
        $array = array();
        $fetch = AdditionalCheckList::where(['patientid' => $pid])->get();
        foreach ($fetch as $key => $value) {
            $arr = array();
            $arr['description'] = $value->description;
            $arr['value'] = $value->value;
            $arr['id'] = $value->id;
            $arr['ischeck'] = $value->ischeck;
            $array[] = $arr;
        }
        return response()->json(['data' => $array]);
    }

    function deletePatientProblem($id)
    {
        AdditionalCheckList::where('id', $id)->delete();
        return response()->json(true);
    }

    public function addAdolecense(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        if ($request->id != 0) {
            $data = Adolecense::find($request->id);
        } else {
            $data = new Adolecense();
        }
        $data->patientid = $request->pid;
        $data->description = $request->description;
        $data->value = $request->value;
        $data->created_dt = date("Y-m-d H:i:s");
        $data->save();
        return response()->json($request->id);
    }

    public function getPatientAdolecense($pid)
    {
        $array = array();
        $fetch = Adolecense::where(['patientid' => $pid])->get();
        foreach ($fetch as $key => $value) {
            $arr = array();
            $arr['description'] = $value->description;
            $arr['value'] = $value->value;
            $arr['id'] = $value->id;
            $array[] = $arr;
        }
        return response()->json(['data' => $array]);
    }

    function deleteAdolecense($id)
    {
        Adolecense::where('id', $id)->delete();
        return response()->json(true);
    }

    public function addVaccination(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        if ($request->id != 0) {
            $data = Vaccinations::find($request->id);
        } else {
            $data = new Vaccinations();
        }
        $data->patient_id = $request->pid;
        $data->vax = $request->vax;
        $data->first_dose = $request->first_dose;
        $data->second_dose = $request->second_dose;
        $data->third_dose = $request->third_dose;
        $data->booster = $request->booster;
        $data->created_dt = date("Y-m-d H:i:s");
        $data->save();
        return response()->json($request->id);
    }

    public function getPatientVaccinations($pid)
    {
        $array = array();
        $fetch = Vaccinations::where(['patient_id' => $pid])->get();
        foreach ($fetch as $key => $value) {
            $arr = array();
            $arr['vax'] = $value->vax;
            $arr['first_dose'] = $value->first_dose;
            $arr['second_dose'] = $value->second_dose;
            $arr['third_dose'] = $value->third_dose;
            $arr['booster'] = $value->booster;
            $arr['id'] = $value->id;
            $array[] = $arr;
        }
        return response()->json(['data' => $array]);
    }

    function deleteVaccination($id)
    {
        Vaccinations::where('id', $id)->delete();
        return response()->json(true);
    }

    public function addGrowthDev(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        if ($request->id != 0) {
            $data = GrowthDev::find($request->id);
        } else {
            $data = new GrowthDev();
        }
        $data->patient_id = $request->pid;
        $data->gross_motor = $request->gross_motor;
        $data->gross_motor_age = $request->gross_motor_age;
        $data->fine_motor = $request->fine_motor;
        $data->fine_motor_age = $request->fine_motor_age;
        $data->language = $request->language;
        $data->language_age = $request->language_age;
        $data->social = $request->social;
        $data->social_age = $request->social_age;
        $data->created_dt = date("Y-m-d H:i:s");
        $data->save();
        return response()->json($request->id);
    }

    public function getPatientGrowthDevs($pid)
    {
        $array = array();
        $fetch = GrowthDev::where(['patient_id' => $pid])->get();
        foreach ($fetch as $key => $value) {
            $arr = array();
            $arr['gross_motor'] = $value->gross_motor;
            $arr['gross_motor_age'] = $value->gross_motor_age;
            $arr['fine_motor'] = $value->fine_motor;
            $arr['fine_motor_age'] = $value->fine_motor_age;
            $arr['language'] = $value->language;
            $arr['language_age'] = $value->language_age;
            $arr['social'] = $value->social;
            $arr['social_age'] = $value->social_age;
            $arr['id'] = $value->id;
            $array[] = $arr;
        }
        return response()->json(['data' => $array]);
    }

    function deleteGrowthDev($id)
    {
        GrowthDev::where('id', $id)->delete();
        return response()->json(true);
    }
    public function addMed_blank(Request $request)
    {
        $rx = new RXB();
        $medicineDetail = Helpers::medicineDetail($request->med_id);
        $rx->patientid = $request->patientid;
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
        $rx->medicine = $request->custom_meds ? $request->custom_brand : $request->meds;
        $rx->generic_id = $request->custom_meds ? 0 : $medicineDetail->generic_id;
        $rx->generic_name = $request->custom_meds ? $request->custom_generic . ' ' . $request->custom_dosage : $medicineDetail->generic_name . ' ' . $medicineDetail->unit;
        $rx->save();
        return response()->json($medicineDetail);
    }
    function getAppointmentMedicineBlank($id)
    {
        $data = RXB::where(['patientid' => $id, 'status' => 'Ordered'])->get();
        $array = array();
        foreach ($data as $key => $value) {
            $arr = array();
            $arr['medicine'] = $value->generic_name . ' (' . $value->medicine . ')';
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
    public function printpdf3($id)
    {
        $data = array();
        $data['query_prescription'] = Rxb::where(['patientid' => $id, 'status' => 'Ordered'])->get();
        $data['profile'] = Profile::where(['id' => 1])->first();
        $data['patient_detail'] = Patients::where(['id' => $id])->first();
        $myPdf = new CustomPrescriptiontestA5PortraitBlank($data);
        $myPdf->Output('I', time() . "-.pdf", true);



        Rxb::where(['patientid' => $id, 'status' => 'Ordered'])->update([
            'status' => 'Dispensed',
        ]);

        exit;
    }

    function ImportLastPrescription($id,$appId) {
        $appointment = Appointments::join('rx', 'rx.appointment_id', '=', 'appointments.id')
        ->where(['appointments.patientid'=>$id])
        ->orderBy('appointments.id', 'desc')->first();
        $prescriptions = Rx::where(['appointment_id'=>$appointment->id])->get();
        $array = array();
        foreach ($prescriptions as $key => $value) {
            $rx = new RX();
            $medicineDetail = Helpers::medicineDetail($value->med_id);
            $rx->appointment_id = $appId;
            $rx->medicine_id = $value->medicine_id;
            $rx->qty = $value->qty;
            $rx->remarks = $value->remarks;
            $rx->created_dt = date("Y-m-d H:i:s");
            $rx->medicine = $value->medicine;
            $rx->generic_id = $value->generic_id;
            $rx->generic_name = $value->generic_name;
            $rx->breakfastbefore = $value->breakfastbefore;
            $rx->breakfastafter = $value->breakfastafter;
            $rx->lunchbefore = $value->lunchbefore;
            $rx->lunchafter = $value->lunchafter;
            $rx->supperbefore = $value->supperbefore;
            $rx->supperafter = $value->supperafter;
            $rx->bedtime = $value->bedtime;
            $rx->save();
        }
        return response()->json(['prescriptions' => $prescriptions,'appointments' => $appointment]);
    }
}
