<?php
/**
 * File LaravelController.php
 *
 * @author Tuan Duong <bacduong@gmail.com>
 * @package Laravue
 * @version 1.0
 */

namespace App\Http\Controllers\Api;
use Mpdf\Mpdf;
use App\Model\Patients;
use App\Model\Profile;
use App\Model\Appointments;
use App\Model\AppointmentVitals;
use App\Model\Rx;
use App\Model\PrescriptionGroup;
use App\Model\DiagnosticGroup;
use App\Model\Rxb;
use App\Model\Rx_service;
use App\Model\Services;
use App\Model\Attachments;
use App\Model\Ancillary;
use App\Services\ImageOrientationService;
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
use App\Services\RichTextSanitizer;
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
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

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
        Patients::where(['id' => $id])->update([
            'isdeleted' => 1
        ]);
        return response()->json(true);
    }

    public function storePatient(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        // Normalize birthdate for consistent duplicate checks
        $birthdate = $request->birthdate;
        if (!empty($birthdate)) {
            $parsedDate = preg_replace('/\(.*\)/', '', $birthdate);
            $timestamp = strtotime($parsedDate);
            if ($timestamp !== false) {
                $birthdate = date('Y-m-d', $timestamp);
            }
        }

        // Block duplicates unless explicitly overridden
        $forceDuplicate = filter_var($request->input('force_duplicate', false), FILTER_VALIDATE_BOOLEAN);
        if (!$forceDuplicate) {
            $existing = Patients::where('isdeleted', 0)
                ->whereRaw('LOWER(firstname) = ?', [strtolower((string) $request->firstname)])
                ->whereRaw('LOWER(lastname) = ?', [strtolower((string) $request->lastname)])
                ->where('birthdate', $birthdate)
                ->first();

            if ($existing) {
                return response()->json([
                    'error' => 'Duplicate patient detected.',
                    'duplicate' => true,
                    'existing' => [
                        'id' => $existing->id,
                        'patientid' => $existing->patientid,
                        'patientname' => $existing->patientname,
                        'birthdate' => $existing->birthdate,
                    ],
                ], 409);
            }
        }

        $data = new Patients();
        $lastId = Patients::latest()->value('id');
        $lastinserted = ($lastId ? $lastId : 0) + 1;//DB::connection('mysql')->getPdo()->lastInsertId();
        $data->patientname = ucfirst($request->firstname) . ' ' . ($request->middlename ? ucfirst(mb_substr($request->middlename, 0, 1)) . '. ' : '') . ucfirst($request->lastname);
        $data->firstname = $request->firstname;
        $data->middlename = $request->middlename;
        $data->lastname = $request->lastname;
        $data->patientid = date("Ymd") . '-0' . $lastinserted;
        $data->contactno = $request->contactno;
        $data->email = $request->email;
        $data->birthdate = $birthdate;
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
        $data->save();

        if ($request->hasFile('profile')) {
            $data->profile_name = $this->uploadPatientProfileToS3($data->id, $request->file('profile'));
            $data->save();
        }

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



        if ($request->hasFile('profile_pic')) {
            $data->profile_name = $this->uploadPatientProfileToS3($data->id, $request->file('profile_pic'));
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
        $data->o2_stat = $request->o2_stat;
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
            /* $fileName = $patient->profile_name;
            $fileUrl = url('/storage/app/public/pp/' . $fileName); */
            $fileUrl =
                Storage::disk('s3')->temporaryUrl(
                    $patient->id."/".$patient->profile_name,
                    Carbon::now()->addMinutes(180)
                );  
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
        $vitalsPayload = [
            'vit_sys' => $request->vit_sys,
            'vit_dia' => $request->vit_dia,
            'vit_temp' => $request->vit_temp,
            'vit_cr' => $request->vit_cr,
            'vit_rr' => $request->vit_rr,
            'o2_stat' => $request->o2_stat,
            'weight' => $request->weight,
            'height' => $request->height,
            'bmi' => $request->bmi,
        ];
        $vitalsChanged = $this->vitalsFieldsChanged($data, $vitalsPayload);
        $data->chiefcomplaints = $request->chiefcomplaints;
        $data->updated_by = Auth::user()->id;
        $data->updated_dt = date("Y-m-d H:i:s");
        $data->vit_sys = $request->vit_sys;
        $data->nurse_remarks = $request->nurse_remarks;
        $data->vit_dia = $request->vit_dia;
        $data->vit_temp = $request->vit_temp;
        $data->vit_cr = $request->vit_cr;
        $data->vit_rr = $request->vit_rr;
        $data->o2_stat = $request->o2_stat;
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
        $data->form_content = $request->form_content;

        /* $data->clearance_undersigned = $request->clearance_undersigned!='Invalid date'?$clearance_undersigned:null;//$undersigned;
        $data->clearance_diagnosis = $request->clearance_diagnosis;
        $data->clearance_remarks = $request->clearance_remarks*/


        /* $data->fit_undersigned = $request->fit_undersigned!='Invalid date'?$fit_undersigned:null;//$undersigned;
        $data->fit_diagnosis = $request->fit_diagnosis;
        $data->fit_remarks = $request->fit_remarks;
        $data->fit_remarks = $request->fit_remarks;
        $data->fit_treatment = $request->fit_treatment; */
        $data->save();

        if ($vitalsChanged && $this->payloadHasVitals($vitalsPayload)) {
            $this->recordAppointmentVitals($data, $vitalsPayload, Auth::user()->id);
        }

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
            //->where('isdone', 1)
            ->where('id', '!=', $id) // Exclude current appointment
            ->where('appointment_dt', '<', $data->appointment_dt) // Get appointments before current date
            ->orderBy('appointment_dt', 'desc')
            ->limit(1) // Get only the most recent previous record
            ->get();
        $px_profile = Helpers::patientDetail($data->patientid);
        //$px_profile->profile_name = url('/storage/app/public/pp/' . $px_profile->profile_name);
        $data->medcert_opt1 = $data->medcert_opt1 == 1 ? true : false;
        $data->medcert_opt2 = $data->medcert_opt2 == 1 ? true : false;
        $data->medcert_opt3 = $data->medcert_opt3 == 1 ? true : false;
        $data->medcert_opt4 = $data->medcert_opt4 == 1 ? true : false;
        /* $get_OldPatients = OldPatients::where(["Patient_id" => $px_profile->patientid])->first();
        $get_OldDiagnosis = $get_OldPatients ? OldDiagnosis::where(["PatientID" => $get_OldPatients->PatientID])->get() : []; */
        $this->ensureAppointmentVitalsBackfill($data);
        $vitalsResponse = $this->buildVitalsResponse($data->patientid, (int) $id, $data->appointment_dt);
        $vitals_data = $vitalsResponse['vitals_data'];
        $vitals_today = $vitalsResponse['vitals_today'];
        $vitals_by_day = $vitalsResponse['vitals_by_day'];
        /* $fileUrl = '';
        if ($px_profile->profile_name != null) {
            $fileUrl =
                Storage::disk('s3')->temporaryUrl(
                    $px_profile->id."/".$px_profile->profile_name,
                    Carbon::now()->addMinutes(180)
                );  
        }
        $px_profile->profile_name = $fileUrl; */

        $fileName = $px_profile->profile_name;

        if ($fileName && $px_profile) {
            $localKey = 'pp/' . $fileName;
            $s3Path = $px_profile->id . '/' . $fileName;

            if (Storage::disk('public')->exists($localKey)) {
                // After `php artisan storage:link`, browser path is usually /storage/pp/...
                $px_profile->profile_name = url('storage/app/public/pp/' . $fileName);
            } elseif (Storage::disk('s3')->exists($s3Path)) {
                try {
                    $px_profile->profile_name = Storage::disk('s3')->temporaryUrl(
                        $s3Path,
                        Carbon::now()->addMinutes(180)
                    );
                } catch (\Throwable $e) {
                    $px_profile->profile_name = '';
                }
            }
        }
        

        return response()->json([
            'vitals_data' => $vitals_data,
            'vitals_today' => $vitals_today,
            'vitals_by_day' => $vitals_by_day,
            'px_profile' => $px_profile,
            'data' => $data,
            'data->patientid'=>$data->patientid,
            'prev_data' => $getPreviousRecords->count() > 0 ? $getPreviousRecords[0] : []
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
            ->select('patients.patientname', 'patients.profile_name', 'appointments.isactive', 'appointments.cancel_reason', 'appointments.state', 'appointments.sequence', 'appointments.isdone', 'patients.profile', 'patients.isold_patient', 'appointments.chiefcomplaints', 'appointments.discount', 'appointments.patientid', 'appointments.id', 'appointments.appointment_dt', 'appointments.followup')
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

    public function appointmentReport(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $from = $request->input('from', date('Y-m-d'));
        $to = $request->input('to', date('Y-m-d'));

        $rows = DB::table('appointments')
            ->whereBetween('appointment_dt', [$from, $to])
            ->selectRaw('state, COUNT(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state');

        $stateLabels = [0 => 'Current', 1 => 'Completed', 2 => 'Cancelled'];

        $details = DB::table('appointments')
            ->join('patients', 'patients.patientid', '=', 'appointments.patientid')
            ->whereBetween('appointments.appointment_dt', [$from, $to])
            ->select(
                'appointments.id',
                'patients.patientname',
                'appointments.appointment_dt',
                'appointments.state',
                'appointments.chiefcomplaints',
                'appointments.discount',
                'appointments.cancel_reason'
            )
            ->orderBy('appointments.appointment_dt', 'desc')
            ->orderBy('appointments.sequence', 'asc')
            ->get()
            ->map(function ($row) use ($stateLabels) {
                $fee = (float) Rx_service::where('appointment_id', $row->id)->sum('fee');
                return [
                    'id' => $row->id,
                    'patientname' => $row->patientname,
                    'apt_dt' => $row->appointment_dt ? date_format(date_create($row->appointment_dt), 'F d, Y') : '',
                    'state' => (int) $row->state,
                    'status' => $stateLabels[$row->state] ?? 'Unknown',
                    'complaints' => $row->chiefcomplaints,
                    'cancel_reason' => $row->cancel_reason,
                    'fee' => $fee - (float) $row->discount,
                ];
            });

        return response()->json([
            'current' => (int) ($rows[0] ?? 0),
            'completed' => (int) ($rows[1] ?? 0),
            'cancelled' => (int) ($rows[2] ?? 0),
            'total' => (int) $rows->sum(),
            'details' => $details,
        ]);
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

    public function printpdf(Request $request, $id)
    {
        $data = array();
        $data = array_merge($data, $this->buildPrescriptionPdfPayload((int) $id, $request->query('group_id', 'all')));
        $data['appointment_detail'] = Appointments::where(['id' => $id])->first();
        $data['profile'] = Profile::where(['id' => 1])->first();
        $data['patient_detail'] = Patients::where(['patientid' => $data['appointment_detail']->patientid])->first();
        $myPdf = new CustomPrescriptiontest($data);
        $myPdf->Output('I', time() . "-.pdf", true);
        exit;
    }

    public function printpdf2(Request $request, $id)
    {
        $data = array();
        $data = array_merge($data, $this->buildPrescriptionPdfPayload((int) $id, $request->query('group_id', 'all')));
        $data['appointment_detail'] = Appointments::where(['id' => $id])->first();
        $data['profile'] = Profile::where(['id' => 1])->first();
        $data['patient_detail'] = Patients::where(['patientid' => $data['appointment_detail']->patientid])->first();
        $myPdf = new CustomPrescriptiontestA5Portrait($data);
        $myPdf->Output('I', time() . "-.pdf", true);
        exit;
    }

    public function emailPrescription(Request $request, $id)
    {
        date_default_timezone_set('Asia/Manila');
        $data = [];
        $data = array_merge($data, $this->buildPrescriptionPdfPayload((int) $id, $request->query('group_id', 'all')));
        $data['appointment_detail'] = Appointments::where('id', $id)->first();
        $data['profile'] = Profile::where('id', 1)->first();
        $data['patient_detail'] = Patients::where('patientid', $data['appointment_detail']->patientid)->first();

        // Generate the PDF and store it in memory
        $pdf = new CustomPrescriptiontestA5Portrait($data);
        $pdfContent = $pdf->Output('', 'S'); // Output as string (S = string)
        $subject = date("F d, Y");
        // Send email with PDF attached
        Mail::to($data['appointment_detail']->email)->send(new PrescriptionPdfMail($pdfContent, $subject));

        return response()->json(['message' => 'PDF sent via email.']);
    }

    public function printmedcert($id)
    {
        // #region agent log
        $apt = Appointments::where(['id' => $id])->first();
        $remarks = (string) ($apt->medcert_remarks ?? '');
        $diag = (string) ($apt->medcert_diagnosis ?? '');
        $payload = json_encode([
            'sessionId' => '37c2da',
            'timestamp' => (int) round(microtime(true) * 1000),
            'location' => 'PatientController.php:printmedcert',
            'message' => 'db_at_print',
            'data' => [
                'appointmentId' => (int) $id,
                'medcertRemarksLen' => strlen($remarks),
                'medcertDiagnosisLen' => strlen($diag),
            ],
            'hypothesisId' => 'A',
            'runId' => 'post-fix',
        ]);
        if ($payload !== false) {
            @file_put_contents(base_path('debug-37c2da.log'), $payload."\n", FILE_APPEND);
        }
        // #endregion
        $data = array();
        $data['appointment_detail'] = $apt;
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
    
    public function printform($id)
    {
        $data = [];
        $data['appointment_detail'] = Appointments::findOrFail($id);
        $data['profile'] = Profile::findOrFail(1);
        $data['patient_detail'] = Patients::where('patientid', $data['appointment_detail']->patientid)->first();

        /*$pdf = new FormController($data);
        $pdf->generate();
        $pdf->Output('example.pdf', 'I'); // I = inline display
        exit; */
        return (new PdfService($data))->generatePdf();
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

    public function printrequest(Request $request, $id, $type)
    {
        $data = array();
        $data = array_merge(
            $data,
            $this->buildDiagnosticPdfPayload((int) $id, $request->query('group_id', 'all'))
        );
        $data['appointment_detail'] = Appointments::where(['id' => $id])->first();
        $data['profile'] = Profile::where(['id' => 1])->first();
        $data['patient_detail'] = Patients::where(['patientid' => $data['appointment_detail']->patientid])->first();
        $data['type'] = $type;
        $myPdf = new RequestprescriptionA5($data);
        $myPdf->Output('I', time() . "-.pdf", true);
        exit;
    }

    public function printfees($id)
    {
        $data = [];
        $data['query_services'] = Rx_service::where(['appointment_id' => $id])->orderBy('rendered_id', 'asc')->get();
        $data['appointment_detail'] = Appointments::where(['id' => $id])->first();
        $data['profile'] = Profile::where(['id' => 1])->first();
        $data['patient_detail'] = Patients::where(['patientid' => $data['appointment_detail']->patientid])->first();
        $myPdf = new ClinicFeesA5Portrait($data);
        $myPdf->Output('I', time() . '-fees.pdf', true);
        exit;
    }

    public function publicPdfLink(Request $request, $id, $doc)
    {
        // Generates a signed, no-login link for sharing (no expiration).

        $params = [
            'doc' => $doc,
            'id' => (int) $id,
        ];

        // For diagnostics we allow type (1/2) as query param or route param
        if ($doc === 'diagnostics') {
            $params['type'] = (int) $request->query('type', 1);
        }

        $url = URL::signedRoute('public.pdf', $params);

        return response()->json([
            'url' => $url,
            'expires_at' => null,
        ]);
    }

    public function printchart($id)
    {
        $patient = Patients::where(['patientid' => $id])->first();
        if (!$patient) {
            return response()->json(['message' => 'Patient not found.'], 404);
        }

        $data = array();
        $data['patient_detail'] = $patient;
        $data['profile'] = Profile::where(['id' => 1])->first();
        $data['getHistory'] = Appointments::where(['patientid' => $patient->patientid, 'is_cancel' => 0])->orderby('id', 'desc')->get();
        $myPdf = new ChartRecordPdf($data);
        $myPdf->Output('I', time() . "-.pdf", true);
        exit;
    }

    function getpastConsultationList($id)
    {
        $data = Appointments::where(['patientid' => $id, 'isdone' => 1, 'is_cancel' => 0])
            ->orderBy('appointment_dt', 'desc')
            ->get();
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

    public function getPatientConsultationHistory(Request $request, $id)
    {
        $excludeId = (int) $request->query('exclude_id', 0);
        $appointmentId = (int) $request->query('appointment_id', 0);
        $limit = min((int) $request->query('limit', 50), 100);

        $query = Appointments::where([
            'patientid' => $id,
            //'isdone' => 1,
            'is_cancel' => 0,
        ])->orderBy('appointment_dt', 'desc');

        if ($appointmentId > 0) {
            $query->where('id', $appointmentId);
        }

        if ($excludeId > 0) {
            $query->where('id', '!=', $excludeId);
        }

        $appointments = $query->limit($limit)->get();
        if ($appointments->isEmpty()) {
            return response()->json(['data' => []]);
        }

        $patient = Helpers::patientDetail($id);
        if (!$patient) {
            return response()->json(['data' => []]);
        }
        $profile = Profile::find(1);
        $defaultDoctor = $profile && $profile->name ? $profile->name : '—';
        $defaultClinic = '—';

        $appointmentIds = $appointments->pluck('id')->all();

        $rxRows = RX::whereIn('appointment_id', $appointmentIds)
            ->orderBy('prescription_group_id')
            ->orderBy('sort_order')
            ->orderBy('rx_id')
            ->get()
            ->groupBy('appointment_id');

        $ancillaryRows = Ancillary::whereIn('appointment_id', $appointmentIds)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('appointment_id');

        $records = [];
        foreach ($appointments as $apt) {
            $appointmentId = (int) $apt->id;
            $visitDate = $apt->appointment_dt;

            $prescriptions = [];
            foreach ($rxRows->get($appointmentId, collect()) as $rx) {
                $medicineLabel = trim(($rx->generic_name ?? '') . ' ' . ($rx->medicine ?? ''));
                $prescriptions[] = [
                    'medicine' => $medicineLabel !== '' ? $medicineLabel : ($rx->medicine ?? ''),
                    'qty' => $rx->qty,
                    'remarks' => $rx->remarks,
                ];
            }

            $diagnostics = [];
            foreach ($ancillaryRows->get($appointmentId, collect()) as $item) {
                $diagnostics[] = [
                    'diagnostic' => $item->ancillary,
                    'type' => (int) $item->type === 1 ? 'Lab' : 'Ancillary',
                ];
            }

            $sys = trim((string) ($apt->vit_sys ?? ''));
            $dia = trim((string) ($apt->vit_dia ?? ''));
            $bp = '';
            if ($sys || $dia) {
                $bp = ($sys ?: '—') . '/' . ($dia ?: '—') . ' mmHg';
            }

            $weightVal = trim((string) ($apt->weight ?? ''));
            $heightVal = trim((string) ($apt->height ?? ''));
            $bsa = $this->computeBodySurfaceArea($weightVal, $heightVal);

            $formPreview = RichTextSanitizer::toPlainText($apt->form_content ?? '', 200);

            $records[] = [
                'id' => $appointmentId,
                'appointment_dt' => $visitDate,
                'date_display' => date_format(date_create($visitDate), 'M d Y'),
                'patient_name' => $patient->patientname ?? '',
                'doctor_name' => trim((string) ($apt->doctor ?? '')) !== '' ? $apt->doctor : $defaultDoctor,
                'clinic_name' => $defaultClinic,
                'patient_age' => $this->computeAgeAtDate($patient->birthdate ?? null, $visitDate) . ' years',
                'clinical' => [
                    'history' => $apt->history,
                    'pe' => $apt->pe,
                    'diagnosis' => $apt->diagnosis,
                    'plan' => $apt->remarks,
                    'remarks' => $apt->nurse_remarks,
                ],
                'vitals' => [
                    'weight' => $weightVal !== '' ? $weightVal . ' kg' : null,
                    'bsa' => $bsa,
                    'bp' => $bp !== '' ? $bp : null,
                    'hr' => trim((string) ($apt->vit_cr ?? '')) !== ''
                        ? trim((string) $apt->vit_cr) . ' bpm'
                        : null,
                ],
                'prescriptions' => $prescriptions,
                'diagnostics' => $diagnostics,
                'forms' => [
                    'medcert' => [
                        'has_content' => $this->appointmentHasMedCert($apt),
                        'diagnosis' => $apt->medcert_diagnosis,
                        'remarks' => $apt->medcert_remarks,
                    ],
                    'referral' => [
                        'has_content' => $this->appointmentHasReferral($apt),
                        'doctor' => $apt->referral_doctor,
                        'diagnosis' => $apt->referral_diagnosis,
                        'remarks' => $apt->referral_remarks,
                    ],
                    'form' => [
                        'has_content' => trim((string) ($apt->form_content ?? '')) !== '',
                        'preview' => $formPreview,
                    ],
                ],
            ];
        }

        return response()->json(['data' => $records]);
    }

    public function getPatientVitalsHistory($id)
    {
        $patient = Helpers::patientDetail($id);
        if (!$patient) {
            return response()->json(['vitals_data' => [], 'vitals_by_day' => []]);
        }

        $vitalsResponse = $this->buildVitalsResponse($id, 0, '1970-01-01');

        return response()->json([
            'vitals_data' => $vitalsResponse['vitals_data'],
            'vitals_by_day' => $vitalsResponse['vitals_by_day'],
        ]);
    }

    public function recordPatientVitals(Request $request)
    {
        date_default_timezone_set('Asia/Manila');
        $patientId = $request->patientid;
        $patient = Helpers::patientDetail($patientId);
        if (!$patient) {
            return response()->json(['success' => false, 'message' => 'Patient not found.'], 404);
        }

        $vitalsPayload = [
            'vit_sys' => $request->vit_sys,
            'vit_dia' => $request->vit_dia,
            'vit_temp' => $request->vit_temp,
            'vit_rr' => $request->vit_rr,
            'o2_stat' => $request->o2_stat,
            'vit_cr' => $request->vit_cr,
            'weight' => $request->weight,
            'height' => $request->height,
            'bmi' => $request->bmi,
        ];

        if (!$this->payloadHasVitals($vitalsPayload)) {
            return response()->json(['success' => false, 'message' => 'At least one vital sign is required.'], 422);
        }

        $appointment = Appointments::where([
            'patientid' => $patientId,
            'appointment_dt' => date('Y-m-d'),
            'isdone' => 0,
            'is_cancel' => 0,
        ])->orderByDesc('id')->first();

        $linkedAppointmentId = null;
        if ($appointment) {
            foreach ($this->vitalsFieldKeys() as $key) {
                $appointment->{$key} = $vitalsPayload[$key];
            }
            $appointment->save();
            $linkedAppointmentId = $appointment->id;
        }

        $log = $this->recordPatientVitalsLog($patientId, $appointment, $vitalsPayload, Auth::user()->id);
        $effectiveDt = $appointment ? $appointment->appointment_dt : date('Y-m-d');
        $vitalsEntry = $this->formatVitalsReading($log, $effectiveDt, true);

        return response()->json([
            'success' => true,
            'vitals_entry' => $vitalsEntry,
            'linked_appointment_id' => $linkedAppointmentId,
        ]);
    }

    private function computeAgeAtDate($birthdate, $referenceDate)
    {
        if (!$birthdate || !$referenceDate) {
            return '—';
        }
        try {
            $birth = new \DateTime($birthdate);
            $ref = new \DateTime($referenceDate);
            return (string) $birth->diff($ref)->y;
        } catch (\Exception $e) {
            return '—';
        }
    }

    private function computeBodySurfaceArea($weightKg, $heightCm)
    {
        $weight = (float) $weightKg;
        $height = (float) $heightCm;
        if ($weight <= 0 || $height <= 0) {
            return null;
        }
        $bsa = sqrt(($height * $weight) / 3600);
        return number_format($bsa, 2) . ' m2';
    }

    private function appointmentHasMedCert($apt)
    {
        return !empty($apt->medcert_diagnosis)
            || !empty($apt->medcert_remarks)
            || !empty($apt->medcert_undersigned);
    }

    private function appointmentHasReferral($apt)
    {
        return !empty($apt->referral_doctor)
            || !empty($apt->referral_addr1)
            || !empty($apt->referral_addr2)
            || !empty($apt->referral_diagnosis)
            || !empty($apt->referral_remarks)
            || !empty($apt->referral_undersigned);
    }

    function deleteMed($id)
    {
        RX::where('rx_id', $id)->delete();
        return response()->json(true);
    }

    public function addMed(Request $request)
    {
        $appointmentId = (int) $request->id;
        $groupId = $this->resolvePrescriptionGroupIdForAppointment(
            $appointmentId,
            $request->input('prescription_group_id')
        );

        $medicineDetail = null;
        if (!$request->custom_meds && $request->med_id) {
            $medicineDetail = Helpers::medicineDetail($request->med_id);
        }

        $brand = trim((string) ($request->custom_brand ?: ($medicineDetail ? $medicineDetail->medicine_name : '')));
        $generic = trim((string) ($request->custom_generic ?: ($medicineDetail ? $medicineDetail->generic_name : '')));
        $dosage = trim((string) $request->custom_dosage);
        if ($dosage === '' && $medicineDetail && !empty($medicineDetail->unit)) {
            $dosage = trim((string) $medicineDetail->unit);
        }

        $rx = new RX();
        $rx->appointment_id = $appointmentId;
        $rx->prescription_group_id = $groupId;
        $rx->medicine_id = $request->custom_meds ? 0 : (int) $request->med_id;
        $rx->breakfastbefore = $request->bf_b ?? '';
        $rx->breakfastafter = $request->bf_a ?? '';
        $rx->lunchbefore = $request->l_b ?? '';
        $rx->lunchafter = $request->l_a ?? '';
        $rx->supperbefore = $request->s_b ?? '';
        $rx->supperafter = $request->s_a ?? '';
        $rx->bedtime = $request->bt ?? '';
        $rx->qty = $request->qty;
        $rx->remarks = $request->remarks;
        $rx->created_dt = date("Y-m-d H:i:s");
        //$rx->medicine = $brand;
        $rx->medicine = $this->composeRxGenericName($brand, $dosage);
        //$rx->generic_name = $this->composeRxGenericName($generic, $dosage);
        $rx->generic_name = $generic;
        $maxSort = RX::where('appointment_id', $appointmentId)
            ->where('prescription_group_id', $groupId)
            ->max('sort_order');
        $rx->sort_order = $maxSort !== null ? ((int) $maxSort) + 1 : 0;
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
        if (!$request->custom_meds && $request->med_id) {
            $medicineDetail = Helpers::medicineDetail($request->med_id);
        }

        $brand = trim((string) ($request->custom_brand ?: ($medicineDetail ? $medicineDetail->medicine_name : $request->meds)));
        $generic = trim((string) ($request->custom_generic ?: ($medicineDetail ? $medicineDetail->generic_name : '')));
        $dosage = trim((string) $request->custom_dosage);
        if ($dosage === '' && $medicineDetail && !empty($medicineDetail->unit)) {
            $dosage = trim((string) $medicineDetail->unit);
        }

        $rx->medicine_id = $request->custom_meds ? 0 : (int) $request->med_id;
        $rx->breakfastbefore = $request->bf_b ?? '';
        $rx->breakfastafter = $request->bf_a ?? '';
        $rx->lunchbefore = $request->l_b ?? '';
        $rx->lunchafter = $request->l_a ?? '';
        $rx->supperbefore = $request->s_b ?? '';
        $rx->supperafter = $request->s_a ?? '';
        $rx->bedtime = $request->bt ?? '';
        $rx->qty = $request->qty;
        $rx->remarks = $request->remarks;
        //$rx->medicine = $brand;
        $rx->medicine = $this->composeRxGenericName($brand, $dosage);
        $rx->generic_id = $request->custom_meds ? 0 : ($medicineDetail ? $medicineDetail->generic_id : 0);
        //$rx->generic_name = $this->composeRxGenericName($generic, $dosage);
        $rx->generic_name = $generic;
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
        $appointmentId = isset($request->rendered[0]['id']) ? (int) $request->rendered[0]['id'] : 0;
        $groupId = $this->resolveDiagnosticGroupIdForAppointment(
            $appointmentId,
            $request->input('diagnostic_group_id')
        );
        $maxSort = $appointmentId
            ? Ancillary::where('appointment_id', $appointmentId)
                ->where('diagnostic_group_id', $groupId)
                ->max('sort_order')
            : null;
        $baseSort = $maxSort !== null ? ((int) $maxSort) + 1 : 0;

        foreach ($request->rendered as $key => $value) {
            $rx = new Ancillary();
            $rx->appointment_id = $value['id'];
            $rx->diagnostic_group_id = $groupId;
            $rx->ancillary_id = $value['procedure_id'];
            $rx->ancillary = $value['procedure'];
            $rx->remarks = $value['remarks'];
            $rx->micro_remarks = $value['lab_micro_remarks'];
            $rx->xray_remarks = $value['xray_remarks'];
            $rx->type = $value['type'];
            $rx->sort_order = $baseSort + $key;
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
            //$rx->rendered_id = $value['rendered_id'];
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

    function reorderAppointmentDiagnostics(Request $request)
    {
        $appointmentId = (int) $request->input('appointment_id');
        $order = $request->input('order', []);
        $groupId = $request->input('diagnostic_group_id');

        if (!$appointmentId || !is_array($order)) {
            return response()->json(['error' => 'Invalid request'], 422);
        }

        foreach ($order as $index => $ancillaryId) {
            $query = Ancillary::where('id', (int) $ancillaryId)
                ->where('appointment_id', $appointmentId);
            if ($groupId) {
                $query->where('diagnostic_group_id', (int) $groupId);
            }
            $query->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    function getAppointmentDiagnostics($id)
    {
        $appointmentId = (int) $id;
        $this->ensureDiagnosticGroupsForAppointment($appointmentId);

        $groups = DiagnosticGroup::where('appointment_id', $appointmentId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if (!$groups->count()) {
            $groupId = $this->createDiagnosticGroupRecord($appointmentId, 'Diagnostics 1', 0);
            $groups = DiagnosticGroup::where('appointment_id', $appointmentId)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
        }

        $groupPayload = [];
        foreach ($groups as $group) {
            $groupPayload[] = [
                'id' => $group->id,
                'title' => $group->title,
                'sort_order' => (int) $group->sort_order,
                'lab_remarks' => $group->lab_remarks,
                'request_date' => $group->request_date,
                'findings' => $group->findings,
                'notes' => $group->notes,
                'recommendations' => $group->recommendations,
            ];
        }

        $data = Ancillary::where(['appointment_id' => $appointmentId])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        $array = array();
        foreach ($data as $key => $value) {
            $arr = array();
            $arr['type'] = $value->type == 1 ? 'Lab' : 'Ancillary';
            $arr['diagnostic'] = $value->ancillary;
            $arr['ancillary_id'] = $value->ancillary_id;
            $arr['remarks'] = $value->remarks;
            $arr['id'] = $value->id;
            $arr['diagnostic_group_id'] = $value->diagnostic_group_id;
            $array[] = $arr;
        }
        return response()->json([
            'groups' => $groupPayload,
            'diagnostics' => $array,
            'data' => $array,
        ]);
    }

    public function createDiagnosticGroup(Request $request)
    {
        $appointmentId = (int) $request->input('appointment_id');
        if (!$appointmentId) {
            return response()->json(['error' => 'Invalid appointment'], 422);
        }

        $this->ensureDiagnosticGroupsForAppointment($appointmentId);
        $count = DiagnosticGroup::where('appointment_id', $appointmentId)->count();
        $title = trim((string) $request->input('title', ''));
        if ($title === '') {
            $title = 'Diagnostics ' . ($count + 1);
        }

        $groupId = $this->createDiagnosticGroupRecord($appointmentId, $title);

        return response()->json([
            'id' => $groupId,
            'title' => $title,
            'sort_order' => DiagnosticGroup::where('id', $groupId)->value('sort_order'),
            'lab_remarks' => '',
            'request_date' => null,
            'findings' => '',
            'notes' => '',
            'recommendations' => '',
        ]);
    }

    public function updateDiagnosticGroup(Request $request, $id)
    {
        $group = DiagnosticGroup::find((int) $id);
        if (!$group) {
            return response()->json(['error' => 'Diagnostic group not found'], 404);
        }

        if ($request->has('title')) {
            $title = trim((string) $request->input('title', ''));
            if ($title === '') {
                return response()->json(['error' => 'Title is required'], 422);
            }
            $group->title = $title;
        }

        foreach (['lab_remarks', 'request_date', 'findings', 'notes', 'recommendations'] as $field) {
            if ($request->has($field)) {
                $value = $request->input($field);
                $group->{$field} = $field === 'request_date' && $value === '' ? null : $value;
            }
        }

        $group->save();

        return response()->json([
            'id' => $group->id,
            'title' => $group->title,
            'sort_order' => (int) $group->sort_order,
            'lab_remarks' => $group->lab_remarks,
            'request_date' => $group->request_date,
            'findings' => $group->findings,
            'notes' => $group->notes,
            'recommendations' => $group->recommendations,
        ]);
    }

    public function deleteDiagnosticGroup($id)
    {
        $group = DiagnosticGroup::find((int) $id);
        if (!$group) {
            return response()->json(['error' => 'Diagnostic group not found'], 404);
        }

        $appointmentId = (int) $group->appointment_id;
        $remaining = DiagnosticGroup::where('appointment_id', $appointmentId)
            ->where('id', '!=', $group->id)
            ->count();

        if ($remaining < 1) {
            return response()->json(['error' => 'At least one diagnostic group is required'], 422);
        }

        Ancillary::where('diagnostic_group_id', $group->id)->delete();
        $group->delete();

        return response()->json(['success' => true]);
    }

    function reorderAppointmentMeds(Request $request)
    {
        $appointmentId = (int) $request->input('appointment_id');
        $order = $request->input('order', []);
        $groupId = $request->input('prescription_group_id');

        if (!$appointmentId || !is_array($order)) {
            return response()->json(['error' => 'Invalid request'], 422);
        }

        foreach ($order as $index => $rxId) {
            $query = RX::where('rx_id', (int) $rxId)
                ->where('appointment_id', $appointmentId);
            if ($groupId) {
                $query->where('prescription_group_id', (int) $groupId);
            }
            $query->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    function getAppointmentMedicine($id)
    {
        $appointmentId = (int) $id;
        $this->ensurePrescriptionGroupsForAppointment($appointmentId);

        $groups = PrescriptionGroup::where('appointment_id', $appointmentId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($groups->isEmpty()) {
            $this->createPrescriptionGroupRecord($appointmentId, 'Prescription 1', 0);
            $groups = PrescriptionGroup::where('appointment_id', $appointmentId)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
        }

        $groupPayload = [];
        foreach ($groups as $group) {
            $groupPayload[] = [
                'id' => $group->id,
                'title' => $group->title,
                'sort_order' => (int) $group->sort_order,
            ];
        }

        $data = RX::where(['appointment_id' => $appointmentId])
            ->orderBy('prescription_group_id')
            ->orderBy('sort_order')
            ->orderBy('rx_id')
            ->get();

        $array = array();
        foreach ($data as $key => $value) {
            $split = $this->splitRxGenericAndDosage($value->generic_name, (int) $value->medicine_id);
            $arr = array();
            $arr['generic'] = $split['generic'];
            $arr['brand'] = $value->medicine;
            $arr['dosage'] = $split['dosage'];
            $arr['medicine'] = trim($split['generic'] . ' ' . $value->medicine);
            $arr['qty'] = $value->qty;
            $arr['bb'] = $value->breakfastbefore;
            $arr['ab'] = $value->breakfastafter;
            $arr['bl'] = $value->lunchbefore;
            $arr['al'] = $value->lunchafter;
            $arr['bs'] = $value->supperbefore;
            $arr['as'] = $value->supperafter;
            $arr['bt'] = $value->bedtime;
            $arr['bf_b'] = $value->breakfastbefore;
            $arr['bf_a'] = $value->breakfastafter;
            $arr['l_b'] = $value->lunchbefore;
            $arr['l_a'] = $value->lunchafter;
            $arr['s_b'] = $value->supperbefore;
            $arr['s_a'] = $value->supperafter;
            $arr['remarks'] = $value->remarks;
            $arr['medicineId'] = $value->medicine_id;
            $arr['prescription_group_id'] = $value->prescription_group_id;
            $arr['id'] = $value->rx_id;
            $array[] = $arr;
        }

        return response()->json([
            'groups' => $groupPayload,
            'medicines' => $array,
            'data' => $array,
        ]);
    }

    public function createPrescriptionGroup(Request $request)
    {
        $appointmentId = (int) $request->input('appointment_id');
        if (!$appointmentId) {
            return response()->json(['error' => 'Invalid appointment'], 422);
        }

        $this->ensurePrescriptionGroupsForAppointment($appointmentId);
        $count = PrescriptionGroup::where('appointment_id', $appointmentId)->count();
        $title = trim((string) $request->input('title', ''));
        if ($title === '') {
            $title = 'Prescription ' . ($count + 1);
        }

        $groupId = $this->createPrescriptionGroupRecord($appointmentId, $title);

        return response()->json([
            'id' => $groupId,
            'title' => $title,
            'sort_order' => PrescriptionGroup::where('id', $groupId)->value('sort_order'),
        ]);
    }

    public function updatePrescriptionGroup(Request $request, $id)
    {
        $group = PrescriptionGroup::find((int) $id);
        if (!$group) {
            return response()->json(['error' => 'Prescription group not found'], 404);
        }

        $title = trim((string) $request->input('title', ''));
        if ($title === '') {
            return response()->json(['error' => 'Title is required'], 422);
        }

        $group->title = $title;
        $group->save();

        return response()->json([
            'id' => $group->id,
            'title' => $group->title,
            'sort_order' => (int) $group->sort_order,
        ]);
    }

    public function deletePrescriptionGroup($id)
    {
        $group = PrescriptionGroup::find((int) $id);
        if (!$group) {
            return response()->json(['error' => 'Prescription group not found'], 404);
        }

        $appointmentId = (int) $group->appointment_id;
        $remaining = PrescriptionGroup::where('appointment_id', $appointmentId)
            ->where('id', '!=', $group->id)
            ->count();

        if ($remaining < 1) {
            return response()->json(['error' => 'At least one prescription group is required'], 422);
        }

        RX::where('prescription_group_id', $group->id)->delete();
        $group->delete();

        return response()->json(['success' => true]);
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
    function getAttachments1($id)
    {
        $getidno = explode("-0", $id);
        if (sizeof($getidno) > 1) {
            $data = Attachments::where(['patientid' => $getidno[1]])->get();
            $getPatientId = Patients::where('id', $getidno[1])->first();
        } else {
            $data = Attachments::where(['patientid' => $id])->get();
            $getPatientId = Patients::where('patientid', $id)->first();
        }
        $array = array();
        foreach ($data as $key => $value) {
            $arr = array();
            $path =
                Storage::disk('s3')->temporaryUrl(
                    $getPatientId->id."/".$value->filename,
                    Carbon::now()->addMinutes(180)
                );            
            $arr['newfile'] = $path;
            $arr['oldfile'] = $path;
            $arr['extension'] = 'pdf';
            $arr['id'] = $value->AttachmentID;
            $arr['patientid'] = $getPatientId->id;
            $arr['fname'] = $value->filename;
            $arr['description'] = $value->description;
            $arr['created_dt'] = date_format(date_create($value->created_dt), "F d, Y");
            $array[] = $arr;
        }
        return response()->json(['data' => $array]);
    }
    function getAttachments($id)
    {
        $getidno = explode("-0", $id);
        if (sizeof($getidno) > 1) {
            $data = Attachments::where(['patientid' => $getidno[1]])->get();
            $getPatientId = Patients::where('id', $getidno[1])->first();
        } else {
            $data = Attachments::where(['patientid' => $id])->get();
            $getPatientId = Patients::where('patientid', $id)->first();
        }
        $array = array();
        foreach ($data as $key => $value) {
            $arr = array();
            $fileName = $value->filename;
            $fileUrl = url('public/storage/uploads/' . $fileName);
            $fileExt = explode(".", $fileName);
            $path =
                Storage::disk('s3')->temporaryUrl(
                    $getPatientId->id."/".$value->filename,
                    Carbon::now()->addMinutes(180)
                );    
            $arr['newfile'] = $path;
            $arr['oldfile'] = $path;
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
            $getPatientId = Patients::where('patientid', $request->patientid)->first();

            // Validate file size (10MB limit)
            $maxFileSize = 10 * 1024 * 1024; // 10MB in bytes
            $resizeThreshold = 1 * 1024 * 1024; // 1MB in bytes

            $uploadedFiles = [];
            foreach ($request->file('files') as $file) {

                $att = new Attachments();
                $getidno = explode("-0", $request->patientid);

                // Use unique filename to prevent conflicts
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                
                $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $ext = $file->getClientOriginalExtension();

                $timestamp = time();
                $filename = $name . '_' . $timestamp . '.' . $ext;

                $tmpPathToDelete = null;
                $fileToUpload = $file;
                $finalFilename = $filename;

                // If image > 1MB, resize/compress before uploading
                $mime = (string) $file->getMimeType();
                $isImage = str_starts_with($mime, 'image/');
                if ($isImage && $file->getSize() > $resizeThreshold) {
                    try {
                        if (
                            function_exists('imagecreatefromstring') &&
                            function_exists('imagecreatetruecolor') &&
                            function_exists('imagecopyresampled') &&
                            function_exists('imagejpeg')
                        ) {
                            $raw = @file_get_contents($file->getRealPath());
                            $src = $raw !== false ? @imagecreatefromstring($raw) : false;

                            if ($src !== false) {
                                $src = ImageOrientationService::applyExifOrientationToGdImage($src, $file->getRealPath());
                                $srcW = imagesx($src);
                                $srcH = imagesy($src);

                                // Resize down to a reasonable max dimension
                                $maxDim = 1920;
                                $scale = 1.0;
                                if ($srcW > $maxDim || $srcH > $maxDim) {
                                    $scale = min($maxDim / max($srcW, 1), $maxDim / max($srcH, 1));
                                }
                                $dstW = max(1, (int) round($srcW * $scale));
                                $dstH = max(1, (int) round($srcH * $scale));

                                $dst = imagecreatetruecolor($dstW, $dstH);
                                // White background (in case source has transparency)
                                $white = imagecolorallocate($dst, 255, 255, 255);
                                imagefilledrectangle($dst, 0, 0, $dstW, $dstH, $white);

                                imagecopyresampled($dst, $src, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);

                                // Save as JPEG (best chance to get under 1MB reliably)
                                $tmpBase = tempnam(sys_get_temp_dir(), 'att_');
                                if ($tmpBase !== false) {
                                    $tmpPath = $tmpBase . '.jpg';
                                    @rename($tmpBase, $tmpPath);

                                    $quality = 85;
                                    $minQuality = 45;
                                    do {
                                        imagejpeg($dst, $tmpPath, $quality);
                                        clearstatcache(true, $tmpPath);
                                        $size = @filesize($tmpPath);
                                        $quality -= 10;
                                    } while ($size !== false && $size > $resizeThreshold && $quality >= $minQuality);

                                    if (is_file($tmpPath) && filesize($tmpPath) !== false) {
                                        $tmpPathToDelete = $tmpPath;
                                        $fileToUpload = new \Illuminate\Http\File($tmpPath);
                                        $finalFilename = $name . '_' . $timestamp . '.jpg';
                                    }
                                }

                                imagedestroy($dst);
                                imagedestroy($src);
                            }
                        }
                    } catch (\Throwable $t) {
                        // If resizing fails for any reason, fall back to original file upload
                    }
                }

                Storage::disk('s3')->putFileAs($getPatientId->id, $fileToUpload, $finalFilename);

                $att->patientid = sizeof($getidno) > 1 ? $getidno[1] : $request->patientid;
                $att->filename = $finalFilename;
                $att->created_dt = date("Y-m-d H:i:s");
                $att->isold_record = false;
                $att->save();

                if ($tmpPathToDelete && is_file($tmpPathToDelete)) {
                    @unlink($tmpPathToDelete);
                }

                $uploadedFiles[] = [
                    'filename' => $finalFilename,
                    'original_name' => $originalName,
                    'size' => $file->getSize(),
                    'mime' => $mime,
                    'resized' => ($finalFilename !== $filename),
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
    public function rotatePatientProfile($id)
    {
        $patient = Patients::find($id);
        if (!$patient || !$patient->profile_name) {
            return response()->json(['message' => 'Patient or profile image not found'], 404);
        }

        $s3Key = $patient->id . '/' . $patient->profile_name;
        $disk = Storage::disk('s3');
        if (!$disk->exists($s3Key)) {
            return response()->json(['message' => 'Profile file not found on storage'], 404);
        }

        $tmpIn = tempnam(sys_get_temp_dir(), 'prof_in_');
        $tmpOut = tempnam(sys_get_temp_dir(), 'prof_out_') . '.jpg';
        if ($tmpIn === false) {
            return response()->json(['message' => 'Server error'], 500);
        }

        try {
            file_put_contents($tmpIn, $disk->get($s3Key));
            if (!ImageOrientationService::bakeAndRotate90ClockwiseToJpegFile($tmpIn, $tmpOut)) {
                return response()->json(['message' => 'Could not rotate image'], 422);
            }
            $disk->put($s3Key, file_get_contents($tmpOut));

            $fileUrl = $disk->temporaryUrl(
                $s3Key,
                \Illuminate\Support\Carbon::now()->addMinutes(180)
            );

            return response()->json([
                'success' => true,
                'profile' => $fileUrl,
            ]);
        } finally {
            @unlink($tmpIn);
            @unlink($tmpOut);
        }
    }

    public function rotatePatientAttachment($id)
    {
        $attachment = Attachments::find($id);
        if (!$attachment) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $patient = Patients::where('patientid', $attachment->patientid)->first();
        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }

        $ext = strtolower(pathinfo($attachment->filename, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'], true)) {
            return response()->json(['message' => 'Not an image'], 422);
        }

        $s3Key = $patient->id . '/' . $attachment->filename;
        $disk = Storage::disk('s3');
        if (!$disk->exists($s3Key)) {
            return response()->json(['message' => 'File not found on storage'], 404);
        }

        $tmpIn = tempnam(sys_get_temp_dir(), 'att_in_');
        $tmpOut = tempnam(sys_get_temp_dir(), 'att_out_') . '.jpg';
        if ($tmpIn === false) {
            return response()->json(['message' => 'Server error'], 500);
        }

        try {
            file_put_contents($tmpIn, $disk->get($s3Key));
            if (!ImageOrientationService::bakeAndRotate90ClockwiseToJpegFile($tmpIn, $tmpOut)) {
                return response()->json(['message' => 'Could not rotate image'], 422);
            }
            $newName = pathinfo($attachment->filename, PATHINFO_FILENAME) . '.jpg';
            $newKey = $patient->id . '/' . $newName;
            $disk->put($newKey, file_get_contents($tmpOut));
            if ($newKey !== $s3Key) {
                $disk->delete($s3Key);
                $attachment->filename = $newName;
                $attachment->save();
            }

            $fileUrl = $disk->temporaryUrl(
                $newKey,
                \Illuminate\Support\Carbon::now()->addMinutes(180)
            );

            return response()->json([
                'success' => true,
                'newfile' => $fileUrl,
                'filename' => $attachment->filename,
            ]);
        } finally {
            @unlink($tmpIn);
            @unlink($tmpOut);
        }
    }

    public function deleteAttachment($id)
    {
        $attachment = Attachments::find($id);

        if (!$attachment) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $filePath = $attachment->patientid."/".$attachment->filename;

        try {
            if ($filePath && Storage::disk('s3')->exists($filePath)) {
                Storage::disk('s3')->delete($filePath);
            }

            $attachment->delete();

            return response()->json($filePath);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
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

        function getService($id)
        {
            $field = Rx_service::where(['rendered_id' => $id])->first();
            return response()->json($field);
        }
        function updateService(Request $request)
        {
            $field = Rx_service::find($request->id);
            $field->fee = $request->amount;
            $field->save();
            return response()->json(true);



            foreach ($request->rendered as $key => $value) {
                $rx = new Rx_service();
                $rx->appointment_id = $value['id'];
                //$rx->rendered_id = $value['rendered_id'];
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
        date_default_timezone_set('Asia/Manila');
        $field = Appointments::find($request->id);
        $vitalsPayload = [
            'vit_sys' => $request->vit_sys,
            'vit_dia' => $request->vit_dia,
            'vit_temp' => $request->vit_temp,
            'vit_rr' => $request->vit_rr,
            'o2_stat' => $request->o2_stat,
            'vit_cr' => $request->vit_cr,
            'weight' => $request->weight,
            'height' => $request->height,
            'bmi' => $request->bmi,
        ];
        $field->vit_sys = $vitalsPayload['vit_sys'];
        $field->vit_dia = $vitalsPayload['vit_dia'];
        $field->vit_temp = $vitalsPayload['vit_temp'];
        $field->vit_rr = $vitalsPayload['vit_rr'];
        $field->o2_stat = $vitalsPayload['o2_stat'];
        $field->vit_cr = $vitalsPayload['vit_cr'];
        $field->weight = $vitalsPayload['weight'];
        $field->height = $vitalsPayload['height'];
        $field->bmi = $vitalsPayload['bmi'];
        $field->save();

        $vitalsEntry = null;
        if ($this->payloadHasVitals($vitalsPayload)) {
            $log = $this->recordAppointmentVitals($field, $vitalsPayload, Auth::user()->id);
            $vitalsEntry = $this->formatVitalsReading($log, $field->appointment_dt, true);
        }

        return response()->json([
            'success' => true,
            'vitals_entry' => $vitalsEntry,
        ]);
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

    public function api_saveAppointment(Request $request)
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

    /**
     * Past appointments for a patient that have at least one rx line (excludes current appointment).
     */
    public function getPatientPastPrescriptions($patientId, $currentAppointmentId)
    {
        $appointments = Appointments::where('patientid', $patientId)
            ->where('id', '!=', $currentAppointmentId)
            ->orderBy('appointment_dt', 'desc')
            ->orderBy('id', 'desc')
            ->limit(200)
            ->get();

        $out = [];
        foreach ($appointments as $apt) {
            $rxRows = Rx::where('appointment_id', $apt->id)->orderBy('sort_order')->orderBy('rx_id')->get();
            if ($rxRows->isEmpty()) {
                continue;
            }
            $meds = [];
            foreach ($rxRows as $value) {
                $meds[] = [
                    'medicine_id' => (int) $value->medicine_id,
                    'generic_id' => (int) $value->generic_id,
                    'generic_name' => $value->generic_name,
                    'medicine' => $value->medicine,
                    'qty' => $value->qty,
                    'remarks' => $value->remarks,
                    'bf_b' => $value->breakfastbefore,
                    'bf_a' => $value->breakfastafter,
                    'l_b' => $value->lunchbefore,
                    'l_a' => $value->lunchafter,
                    's_b' => $value->supperbefore,
                    's_a' => $value->supperafter,
                    'bt' => $value->bedtime,
                ];
            }
            $out[] = [
                'appointment_id' => $apt->id,
                'appointment_dt' => $apt->appointment_dt,
                'diagnosis' => $apt->diagnosis ?? '',
                'medications' => $meds,
            ];
        }

        return response()->json(['data' => $out]);
    }

    function ImportLastPrescription($id, $appId)
    {
        $appointment = Appointments::join('rx', 'rx.appointment_id', '=', 'appointments.id')
            ->where(['appointments.patientid' => $id])
            ->orderBy('appointments.id', 'desc')->first();
        $prescriptions = Rx::where(['appointment_id' => $appointment->id])->orderBy('sort_order')->orderBy('rx_id')->get();
        $array = array();
        $groupId = $this->resolvePrescriptionGroupIdForAppointment((int) $appId);

        foreach ($prescriptions as $key => $value) {
            $rx = new RX();
            $medicineDetail = Helpers::medicineDetail($value->med_id);
            $rx->appointment_id = $appId;
            $rx->prescription_group_id = $groupId;
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
            $rx->sort_order = $value->sort_order ?? $key;
            $rx->save();
        }
        return response()->json(['prescriptions' => $prescriptions, 'appointments' => $appointment]);
    }

    private function uploadPatientProfileToS3($patientId, $fileToUpload)
    {
        $resizeThreshold = 1 * 1024 * 1024; // 1MB
        $finalFilename = basename($fileToUpload->getClientOriginalName());
        $tmpPathToDelete = null;
        $file = $fileToUpload;

        $mime = (string) $fileToUpload->getMimeType();
        $isImage = str_starts_with($mime, 'image/');
        if ($isImage && $fileToUpload->getSize() > $resizeThreshold) {
            try {
                if (
                    function_exists('imagecreatefromstring') &&
                    function_exists('imagecreatetruecolor') &&
                    function_exists('imagecopyresampled') &&
                    function_exists('imagejpeg')
                ) {
                    $raw = @file_get_contents($fileToUpload->getRealPath());
                    $src = $raw !== false ? @imagecreatefromstring($raw) : false;

                    if ($src !== false) {
                        $src = ImageOrientationService::applyExifOrientationToGdImage($src, $fileToUpload->getRealPath());
                        $srcW = imagesx($src);
                        $srcH = imagesy($src);

                        $maxDim = 1920;
                        $scale = 1.0;
                        if ($srcW > $maxDim || $srcH > $maxDim) {
                            $scale = min($maxDim / max($srcW, 1), $maxDim / max($srcH, 1));
                        }
                        $dstW = max(1, (int) round($srcW * $scale));
                        $dstH = max(1, (int) round($srcH * $scale));

                        $dst = imagecreatetruecolor($dstW, $dstH);
                        $white = imagecolorallocate($dst, 255, 255, 255);
                        imagefilledrectangle($dst, 0, 0, $dstW, $dstH, $white);
                        imagecopyresampled($dst, $src, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);

                        $tmpBase = tempnam(sys_get_temp_dir(), 'profile_');
                        if ($tmpBase !== false) {
                            $tmpPath = $tmpBase . '.jpg';
                            @rename($tmpBase, $tmpPath);

                            $quality = 85;
                            $minQuality = 45;
                            do {
                                imagejpeg($dst, $tmpPath, $quality);
                                clearstatcache(true, $tmpPath);
                                $size = @filesize($tmpPath);
                                $quality -= 10;
                            } while ($size !== false && $size > $resizeThreshold && $quality >= $minQuality);

                            if (is_file($tmpPath) && filesize($tmpPath) !== false) {
                                $tmpPathToDelete = $tmpPath;
                                $file = new \Illuminate\Http\File($tmpPath);
                                $finalFilename = pathinfo($finalFilename, PATHINFO_FILENAME) . '.jpg';
                            }
                        }

                        imagedestroy($dst);
                        imagedestroy($src);
                    }
                }
            } catch (\Throwable $t) {
                // Fall back to original file upload
            }
        }

        Storage::disk('s3')->putFileAs($patientId, $file, $finalFilename);

        if ($tmpPathToDelete && is_file($tmpPathToDelete)) {
            @unlink($tmpPathToDelete);
        }

        return $finalFilename;
    }

    private function composeRxGenericName($generic, $dosage)
    {
        $generic = trim((string) $generic);
        $dosage = trim((string) $dosage);
        if ($generic === '') {
            return $dosage;
        }
        if ($dosage === '') {
            return $generic;
        }
        if (preg_match('/\s' . preg_quote($dosage, '/') . '$/iu', $generic)) {
            return $generic;
        }
        return trim($generic . ' ' . $dosage);
    }

    /**
     * Split stored generic_name into generic label and dosage for the UI.
     *
     * @return array{generic: string, dosage: string}
     */
    private function splitRxGenericAndDosage($genericName, $medicineId = 0)
    {
        $stored = trim((string) $genericName);
        $dosage = '';
        $generic = $stored;

        if ((int) $medicineId > 0) {
            $detail = Helpers::medicineDetail($medicineId);
            if ($detail && !empty($detail->unit)) {
                $unit = trim((string) $detail->unit);
                if ($unit !== '') {
                    if ($stored !== '' && preg_match('/\s' . preg_quote($unit, '/') . '$/iu', $stored)) {
                        $generic = trim(preg_replace('/\s' . preg_quote($unit, '/') . '$/iu', '', $stored));
                        $dosage = $unit;
                    } else {
                        $dosage = $unit;
                    }
                }
            }
        }

        if ($dosage === '' && $stored !== '') {
            if (preg_match('/^(.+?)\s+(\d+\s*(?:mg|mcg|g|ml|iu|units?|%)\b.*)$/iu', $stored, $matches)) {
                $generic = trim($matches[1]);
                $dosage = trim($matches[2]);
            }
        }

        return [
            'generic' => $generic,
            'dosage' => $dosage,
        ];
    }

    private function ensurePrescriptionGroupsForAppointment(int $appointmentId): void
    {
        if (!\Schema::hasTable('prescription_groups')) {
            return;
        }

        $hasGroups = PrescriptionGroup::where('appointment_id', $appointmentId)->exists();
        if (!$hasGroups) {
            $groupId = $this->createPrescriptionGroupRecord($appointmentId, 'Prescription 1', 0);
            if (\Schema::hasColumn('rx', 'prescription_group_id')) {
                Rx::where('appointment_id', $appointmentId)
                    ->whereNull('prescription_group_id')
                    ->update(['prescription_group_id' => $groupId]);
            }
            return;
        }

        if (\Schema::hasColumn('rx', 'prescription_group_id')) {
            $defaultGroup = PrescriptionGroup::where('appointment_id', $appointmentId)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->first();
            if ($defaultGroup) {
                Rx::where('appointment_id', $appointmentId)
                    ->whereNull('prescription_group_id')
                    ->update(['prescription_group_id' => $defaultGroup->id]);
            }
        }
    }

    private function createPrescriptionGroupRecord(int $appointmentId, string $title, ?int $sortOrder = null): int
    {
        if ($sortOrder === null) {
            $max = PrescriptionGroup::where('appointment_id', $appointmentId)->max('sort_order');
            $sortOrder = $max !== null ? ((int) $max) + 1 : 0;
        }

        $group = new PrescriptionGroup();
        $group->appointment_id = $appointmentId;
        $group->title = $title;
        $group->sort_order = $sortOrder;
        $group->created_dt = date('Y-m-d H:i:s');
        $group->save();

        return (int) $group->id;
    }

    private function resolvePrescriptionGroupIdForAppointment(int $appointmentId, $requestedGroupId = null): int
    {
        $this->ensurePrescriptionGroupsForAppointment($appointmentId);

        if ($requestedGroupId) {
            $group = PrescriptionGroup::where('id', (int) $requestedGroupId)
                ->where('appointment_id', $appointmentId)
                ->first();
            if ($group) {
                return (int) $group->id;
            }
        }

        $first = PrescriptionGroup::where('appointment_id', $appointmentId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        return $first ? (int) $first->id : $this->createPrescriptionGroupRecord($appointmentId, 'Prescription 1', 0);
    }

    public function buildPrescriptionPdfPayload(int $appointmentId, $groupFilter = 'all'): array
    {
        $this->ensurePrescriptionGroupsForAppointment($appointmentId);

        if ($groupFilter && $groupFilter !== 'all') {
            $groupId = (int) $groupFilter;
            $rxRows = Rx::where('appointment_id', $appointmentId)
                ->where('prescription_group_id', $groupId)
                ->orderBy('sort_order')
                ->orderBy('rx_id')
                ->get();

            return [
                'query_prescription' => $rxRows,
                'prescription_sections' => null,
            ];
        }

        $groups = PrescriptionGroup::where('appointment_id', $appointmentId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($groups->count() <= 1) {
            $rxRows = Rx::where('appointment_id', $appointmentId)
                ->orderBy('sort_order')
                ->orderBy('rx_id')
                ->get();

            return [
                'query_prescription' => $rxRows,
                'prescription_sections' => null,
            ];
        }

        $sections = [];
        foreach ($groups as $group) {
            $items = Rx::where('appointment_id', $appointmentId)
                ->where('prescription_group_id', $group->id)
                ->orderBy('sort_order')
                ->orderBy('rx_id')
                ->get();
            if ($items->isNotEmpty()) {
                $sections[] = [
                    'title' => $group->title,
                    'items' => $items,
                ];
            }
        }

        return [
            'query_prescription' => collect(),
            'prescription_sections' => $sections,
        ];
    }

    private function ensureDiagnosticGroupsForAppointment(int $appointmentId): void
    {
        if (!\Schema::hasTable('diagnostic_groups')) {
            return;
        }

        $hasGroups = DiagnosticGroup::where('appointment_id', $appointmentId)->exists();
        if (!$hasGroups) {
            $labRemarks = null;
            if (\Schema::hasTable('appointments')) {
                $labRemarks = Appointments::where('id', $appointmentId)->value('lab_remarks');
            }
            $groupId = $this->createDiagnosticGroupRecord($appointmentId, 'Diagnostics 1', 0, $labRemarks);
            if (\Schema::hasColumn('ancillary', 'diagnostic_group_id')) {
                Ancillary::where('appointment_id', $appointmentId)
                    ->whereNull('diagnostic_group_id')
                    ->update(['diagnostic_group_id' => $groupId]);
            }
            return;
        }

        if (\Schema::hasColumn('ancillary', 'diagnostic_group_id')) {
            $defaultGroup = DiagnosticGroup::where('appointment_id', $appointmentId)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->first();
            if ($defaultGroup) {
                Ancillary::where('appointment_id', $appointmentId)
                    ->whereNull('diagnostic_group_id')
                    ->update(['diagnostic_group_id' => $defaultGroup->id]);
            }
        }
    }

    private function createDiagnosticGroupRecord(
        int $appointmentId,
        string $title,
        ?int $sortOrder = null,
        ?string $labRemarks = null
    ): int {
        if ($sortOrder === null) {
            $max = DiagnosticGroup::where('appointment_id', $appointmentId)->max('sort_order');
            $sortOrder = $max !== null ? ((int) $max) + 1 : 0;
        }

        $group = new DiagnosticGroup();
        $group->appointment_id = $appointmentId;
        $group->title = $title;
        $group->lab_remarks = $labRemarks;
        $group->sort_order = $sortOrder;
        $group->created_dt = date('Y-m-d H:i:s');
        $group->save();

        return (int) $group->id;
    }

    private function resolveDiagnosticGroupIdForAppointment(int $appointmentId, $requestedGroupId = null): int
    {
        $this->ensureDiagnosticGroupsForAppointment($appointmentId);

        if ($requestedGroupId) {
            $group = DiagnosticGroup::where('id', (int) $requestedGroupId)
                ->where('appointment_id', $appointmentId)
                ->first();
            if ($group) {
                return (int) $group->id;
            }
        }

        $first = DiagnosticGroup::where('appointment_id', $appointmentId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        return $first
            ? (int) $first->id
            : $this->createDiagnosticGroupRecord($appointmentId, 'Diagnostics 1', 0);
    }

    public function buildDiagnosticPdfPayload(int $appointmentId, $groupFilter = 'all'): array
    {
        $this->ensureDiagnosticGroupsForAppointment($appointmentId);

        if ($groupFilter && $groupFilter !== 'all') {
            $groupId = (int) $groupFilter;
            $group = DiagnosticGroup::where('id', $groupId)
                ->where('appointment_id', $appointmentId)
                ->first();
            $items = Ancillary::where('appointment_id', $appointmentId)
                ->where('diagnostic_group_id', $groupId)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            return [
                'query_prescription' => $items,
                'diagnostic_sections' => null,
                'diagnostic_group_detail' => $group,
            ];
        }

        $groups = DiagnosticGroup::where('appointment_id', $appointmentId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($groups->count() <= 1) {
            $group = $groups->first();
            $itemsQuery = Ancillary::where('appointment_id', $appointmentId)
                ->orderBy('sort_order')
                ->orderBy('id');
            if ($group) {
                $itemsQuery->where('diagnostic_group_id', $group->id);
            }
            $items = $itemsQuery->get();

            return [
                'query_prescription' => $items,
                'diagnostic_sections' => null,
                'diagnostic_group_detail' => $group,
            ];
        }

        $sections = [];
        foreach ($groups as $group) {
            $items = Ancillary::where('appointment_id', $appointmentId)
                ->where('diagnostic_group_id', $group->id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
            $hasMeta = trim((string) $group->lab_remarks) !== ''
                || !empty($group->request_date)
                || trim((string) $group->findings) !== ''
                || trim((string) $group->notes) !== ''
                || trim((string) $group->recommendations) !== '';
            if ($items->isNotEmpty() || $hasMeta) {
                $sections[] = [
                    'title' => $group->title,
                    'items' => $items,
                    'lab_remarks' => $group->lab_remarks,
                    'request_date' => $group->request_date,
                    'findings' => $group->findings,
                    'notes' => $group->notes,
                    'recommendations' => $group->recommendations,
                ];
            }
        }

        return [
            'query_prescription' => collect(),
            'diagnostic_sections' => $sections,
            'diagnostic_group_detail' => null,
        ];
    }

    private function vitalsFieldKeys()
    {
        return ['vit_sys', 'vit_dia', 'weight', 'height', 'bmi', 'vit_temp', 'vit_cr', 'vit_rr', 'o2_stat'];
    }

    private function normalizeVitalValue($value)
    {
        if ($value === null) {
            return '';
        }

        return trim((string) $value);
    }

    private function payloadHasVitals(array $payload)
    {
        foreach ($this->vitalsFieldKeys() as $key) {
            if ($this->normalizeVitalValue($payload[$key] ?? '') !== '') {
                return true;
            }
        }

        return false;
    }

    private function rowHasVitals($row)
    {
        foreach ($this->vitalsFieldKeys() as $key) {
            if ($this->normalizeVitalValue($row->{$key} ?? '') !== '') {
                return true;
            }
        }

        return false;
    }

    private function vitalsFieldsChanged($appointment, array $payload)
    {
        foreach ($this->vitalsFieldKeys() as $key) {
            $current = $this->normalizeVitalValue($appointment->{$key} ?? '');
            $incoming = $this->normalizeVitalValue($payload[$key] ?? '');
            if ($current !== $incoming) {
                return true;
            }
        }

        return false;
    }

    private function buildVitalsBp($sys, $dia)
    {
        $sys = $this->normalizeVitalValue($sys);
        $dia = $this->normalizeVitalValue($dia);
        if ($sys === '' && $dia === '') {
            return '';
        }

        return ($sys !== '' ? $sys : '—') . '/' . ($dia !== '' ? $dia : '—');
    }

    private function formatVitalsTimeDisplay($recordedAt)
    {
        if (!$recordedAt) {
            return '';
        }

        return date_format(date_create($recordedAt), 'g:i A');
    }

    private function formatVitalsReading($row, $appointmentDt, $isLatest = false)
    {
        return [
            'id' => $row->id,
            'appointment_id' => $row->appointment_id,
            'recorded_at' => $row->recorded_at,
            'time_display' => $this->formatVitalsTimeDisplay($row->recorded_at),
            'date' => date_format(date_create($appointmentDt), 'M d, Y'),
            'date_sort' => $appointmentDt,
            'day_key' => date_format(date_create($appointmentDt), 'Y-m-d'),
            'bp' => $this->buildVitalsBp($row->vit_sys, $row->vit_dia),
            'vit_sys' => $row->vit_sys,
            'vit_dia' => $row->vit_dia,
            'weight' => $row->weight,
            'height' => $row->height,
            'bmi' => $row->bmi,
            'vit_temp' => $row->vit_temp,
            'vit_cr' => $row->vit_cr,
            'vit_rr' => $row->vit_rr,
            'o2_stat' => $row->o2_stat,
            'is_latest' => $isLatest,
        ];
    }

    private function recordPatientVitalsLog($patientId, $appointment, array $payload, $userId = null)
    {
        date_default_timezone_set('Asia/Manila');

        $log = new AppointmentVitals();
        $log->appointment_id = $appointment ? $appointment->id : null;
        $log->patientid = $patientId;
        $log->recorded_at = date('Y-m-d H:i:s');
        $log->recorded_by = $userId;
        foreach ($this->vitalsFieldKeys() as $key) {
            $log->{$key} = $payload[$key] ?? null;
        }
        $log->save();

        return $log;
    }

    private function recordAppointmentVitals($appointment, array $payload, $userId = null)
    {
        return $this->recordPatientVitalsLog($appointment->patientid, $appointment, $payload, $userId);
    }

    private function ensureAppointmentVitalsBackfill($appointment)
    {
        if (!$this->rowHasVitals($appointment)) {
            return;
        }

        $existingCount = AppointmentVitals::where('appointment_id', $appointment->id)->count();
        if ($existingCount > 0) {
            return;
        }

        $payload = [];
        foreach ($this->vitalsFieldKeys() as $key) {
            $payload[$key] = $appointment->{$key};
        }

        date_default_timezone_set('Asia/Manila');
        $log = new AppointmentVitals();
        $log->appointment_id = $appointment->id;
        $log->patientid = $appointment->patientid;
        $log->recorded_at = $appointment->updated_dt ?: ($appointment->created_dt ?: ($appointment->appointment_dt . ' 00:00:00'));
        $log->recorded_by = $appointment->updated_by ?: $appointment->created_by;
        foreach ($this->vitalsFieldKeys() as $key) {
            $log->{$key} = $payload[$key];
        }
        $log->save();
    }

    private function resolveVitalsEffectiveDate($row)
    {
        if (!empty($row->appointment_dt)) {
            return $row->appointment_dt;
        }

        return date('Y-m-d', strtotime($row->recorded_at));
    }

    private function buildVitalsResponse($patientId, $currentAppointmentId, $currentAppointmentDt)
    {
        $rows = DB::table('appointment_vitals as av')
            ->leftJoin('appointments as a', 'av.appointment_id', '=', 'a.id')
            ->where('av.patientid', $patientId)
            ->where(function ($query) {
                $query->whereNull('av.appointment_id')
                    ->orWhere('a.is_cancel', 0);
            })
            ->orderBy('av.recorded_at', 'desc')
            ->select(
                'av.*',
                'a.appointment_dt'
            )
            ->get();

        $vitalsToday = [];
        $byDay = [];
        $todayKey = date('Y-m-d');

        foreach ($rows as $row) {
            $effectiveDt = $this->resolveVitalsEffectiveDate($row);
            $dayKey = date_format(date_create($effectiveDt), 'Y-m-d');
            if (!isset($byDay[$dayKey])) {
                $byDay[$dayKey] = [];
            }
            $byDay[$dayKey][] = $row;

            $isCurrentAppointment = $currentAppointmentId > 0
                && (int) $row->appointment_id === (int) $currentAppointmentId;
            $isStandaloneToday = !$row->appointment_id
                && date('Y-m-d', strtotime($row->recorded_at)) === $todayKey;
            if ($isCurrentAppointment || $isStandaloneToday) {
                $vitalsToday[] = $row;
            }
        }

        $vitalsByDay = [];
        foreach ($byDay as $dayKey => $dayRows) {
            $formatted = [];
            foreach ($dayRows as $index => $dayRow) {
                $formatted[] = $this->formatVitalsReading(
                    $dayRow,
                    $this->resolveVitalsEffectiveDate($dayRow),
                    $index === 0
                );
            }
            $vitalsByDay[$dayKey] = $formatted;
        }

        $formattedToday = [];
        foreach ($vitalsToday as $index => $row) {
            $formattedToday[] = $this->formatVitalsReading(
                $row,
                $this->resolveVitalsEffectiveDate($row),
                $index === 0
            );
        }

        $currentDayKey = date_format(date_create($currentAppointmentDt), 'Y-m-d');
        $vitalsData = [];
        foreach ($byDay as $dayKey => $dayRows) {
            $latest = $dayRows[0];
            $effectiveDt = $this->resolveVitalsEffectiveDate($latest);
            $reading = $this->formatVitalsReading($latest, $effectiveDt, true);
            $reading['reading_count'] = count($dayRows);
            $reading['day_key'] = $dayKey;
            $reading['is_current'] = $dayKey === $currentDayKey;
            $reading['id'] = $latest->appointment_id
                ? (int) $latest->appointment_id
                : (int) $latest->id;
            $vitalsData[] = $reading;
        }

        usort($vitalsData, function ($a, $b) {
            return strcmp($b['date_sort'], $a['date_sort']);
        });

        return [
            'vitals_today' => $formattedToday,
            'vitals_data' => $vitalsData,
            'vitals_by_day' => $vitalsByDay,
        ];
    }
}
