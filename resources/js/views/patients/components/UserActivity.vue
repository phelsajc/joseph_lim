<template>
  <el-card v-if="user.name">
    <LoadingOverlay :visible="pageloading" text="Loading..." />
    <div class="mb-4">
      <el-popconfirm
        confirm-button-text="Yes"
        cancel-button-text="No"
        icon-color="#626AEF"
        title="Do you want to proceed?"
        @confirm="onSubmit"
      >
        <template #reference>
          <el-button type="primary"> Update </el-button>
        </template>
      </el-popconfirm>

      <el-popconfirm
        confirm-button-text="Yes"
        cancel-button-text="No"
        icon-color="#626AEF"
        title="Do you want to delete this patient?"
        @confirm="deletePatient"
      >
        <template #reference>
          <el-button type="danger"> Delete </el-button>
        </template>
      </el-popconfirm>
      <el-button type="warning" v-role="['doctor', 'admin']" @click="printChart()"> Print Chart </el-button>
      <el-button
        class="filter-item"
        style="margin-left: 10px"
        type="success"
        icon="el-icon-plus"
        @click="handleCreate"
      >
        Set Appointment
      </el-button>
    </div>
    <el-tabs v-model="activeActivity" @tab-click="handleClick">
      <el-tab-pane v-loading="updating" label="Patient Profile" name="first">
        <el-form :inline="true" label-position="right" class="demo-form-inline">
          <el-form-item label="Last Name">
            <el-input v-model="form.lastname" autosize clearable />
          </el-form-item>
          <el-form-item label="First Name">
            <el-input v-model="form.firstname" clearable />
          </el-form-item>
          <el-form-item label="Middle Name">
            <el-input v-model="form.middlename" clearable />
          </el-form-item>
        </el-form>
        <el-form label-width="120px" class="demo-form-inline">
          <el-form-item label="Home Address">
            <el-input v-model="form.address" type="textarea" />
          </el-form-item>
        </el-form>
        <el-form :inline="true" label-position="right" class="demo-form-inline">
          <el-form-item label="Contact No">
            <el-input v-model="form.contactno" autosize clearable />
          </el-form-item>
          <el-form-item label="Email">
            <el-input v-model="form.email" autosize clearable />
          </el-form-item>
          <!-- <el-form-item label="Occupation">
            <el-input v-model="form.occupation" clearable />
          </el-form-item> -->
          <el-form-item label="Birthday">
            <el-date-picker
              v-model="form.birthdate"
              type="date"
              placeholder="Pick a day"
            />
          </el-form-item>
          <el-form-item label="Gender">
            <el-select
              v-model="form.sex"
              placeholder="Select"
              size="large"
              style="width: 140px"
            >
              <el-option label="Female" value="2" />
              <el-option label="Male" value="1" />
            </el-select>
          </el-form-item>
          <el-form-item label="Blood Type">
            <el-select
              v-model="form.blood_type"
              placeholder="Select"
              size="large"
              style="width: 140px"
            >
              <el-option label="A+" value="A+" />
              <el-option label="A-" value="A-" />
              <el-option label="B+" value="B+" />
              <el-option label="B-" value="B-" />
              <el-option label="AB-" value="AB-" />
              <el-option label="AB+" value="AB+" />
              <el-option label="O+" value="O+" />
              <el-option label="O-" value="O-" />
            </el-select>
          </el-form-item>
          <!-- <el-form-item label="Status">
            <el-select
              v-model="form.civil_status"
              placeholder="Select"
              size="large"
              style="width: 240px"
            >
              <el-option label="Single" value="Single" />
              <el-option label="Married" value="Married" />
              <el-option label="Widowed" value="Widowed" />
              <el-option label="Legally Separated" value="Legally Separated" />
            </el-select>
          </el-form-item> -->
        </el-form>
        <el-form :inline="true" label-position="right" class="demo-form-inline">
          <el-form-item label="Caregiver's Name">
            <el-input v-model="form.caregiver_name" autosize clearable />
          </el-form-item>
          <el-form-item label="Age">
            <el-input v-model="form.caregiver_age" clearable />
          </el-form-item>
          <el-form-item label="Relationship">
            <el-input v-model="form.caregiver_rel" autosize clearable />
          </el-form-item>
          <el-form-item label="Contact Number">
            <el-input v-model="form.caregiver_contact" autosize clearable />
          </el-form-item>
          <el-form-item label="Occupation">
            <el-input v-model="form.caregiver_occupation" autosize clearable />
          </el-form-item>
        </el-form>
        <el-form label-width="150px" class="demo-form-inline">
          <el-form-item label="Sibling's Age/Sex">
            <el-input v-model="form.siblings_details" type="textarea" />
          </el-form-item>
        </el-form>
      </el-tab-pane>
      <el-tab-pane label="Past Medical History" name="third" v-if="checkRole(['admin','doctor'])">
        <div class="user-activity">
          <el-form label-position="right" class="demo-form-inline" :inline="true">
            <el-form-item label="Previous Admission">
              <el-input v-model="form.prev_admission" autosize clearable />
            </el-form-item>
            <el-form-item label="Previous Surgeries">
              <el-input v-model="form.prev_surgeries" clearable />
            </el-form-item>
            <el-form-item label="Allergies">
              <el-input v-model="form.allergies" autosize clearable />
            </el-form-item>
          </el-form>
          <el-divider />
          <el-form label-position="right" class="demo-form-inline" :inline="true">
            <el-form-item label="Asthma/Allergic Rhinitis/Atopic Dermatitis">
              <el-input v-model="form.asthma" autosize clearable />
            </el-form-item>
            <!-- <el-form-item label="Newborn & Hearing Screening">
              <el-input v-model="form.newborn_hearing" autosize clearable />
            </el-form-item> -->
            <el-form-item label="TB">
              <el-input v-model="form.tb" autosize clearable />
            </el-form-item>
            <el-form-item label="Seizure">
              <el-input v-model="form.seizure" autosize clearable />
            </el-form-item>
          </el-form>
          <el-divider />
          <el-form label-position="right" class="demo-form-inline" :inline="true">
            <el-form-item label="Hypertension">
              <el-input v-model="form.hypertension" autosize clearable />
            </el-form-item>
            <el-form-item label="Diabetes">
              <el-input v-model="form.diabetes" clearable />
            </el-form-item>
            <el-form-item label="COPD">
              <el-input v-model="form.copd" autosize clearable />
            </el-form-item>
          </el-form>
          <el-divider />
          <el-form label-width="150px" class="demo-form-inline">
            <el-form-item label="Others">
              <el-input v-model="form.pmh_others" type="textarea" />
            </el-form-item>
          </el-form>
        </div>
      </el-tab-pane>
      <el-tab-pane label="Family History" name="second" v-if="checkRole(['admin','doctor'])">
        <div class="block">
          <el-form :inline="true" label-position="top" class="demo-form-inline">
            <el-form-item label="History">
              <el-checkbox-group v-model="form.fam" size="large">
                <el-checkbox-button label="Hypertension">
                  Hypertension
                </el-checkbox-button>
                <el-checkbox-button label="Diabetes Mellitus">
                  Diabetes Mellitus
                </el-checkbox-button>
                <el-checkbox-button label="Stroke"> Stroke </el-checkbox-button>
                <el-checkbox-button label="CAD"> CAD </el-checkbox-button>
              </el-checkbox-group>
            </el-form-item>
            <el-form-item label="Others">
              <el-input
                v-model="form.fam_others"
                :autosize="{ minRows: 2, maxRows: 4 }"
                style="width: 540px"
                :rows="2"
                type="textarea"
                placeholder="Please input"
              />
            </el-form-item>
          </el-form>
        </div>
      </el-tab-pane>
      <el-tab-pane label="Social / Environment History" name="soc" v-if="checkRole(['admin','doctor'])">
        <div class="block">
          <el-form :inline="true" label-position="top" class="demo-form-inline">
            <el-form-item label="History">
              <el-checkbox-group v-model="form.soc" size="large">
                <el-checkbox-button label="Smoking"> Smoking </el-checkbox-button>
                <el-checkbox-button label="Alcoholic Beverage Drinking">
                  Alcoholic Beverage Drinking
                </el-checkbox-button>
              </el-checkbox-group>
            </el-form-item>
            <el-form-item label="Others">
              <el-input
                v-model="form.soc_others"
                :autosize="{ minRows: 2, maxRows: 4 }"
                style="width: 540px"
                :rows="2"
                type="textarea"
                placeholder="Please input"
              />
            </el-form-item>
            <el-form-item label="Vaccinations">
              <el-input
                v-model="form.vaccination_sup"
                :autosize="{ minRows: 2, maxRows: 4 }"
                style="width: 540px"
                :rows="2"
                type="textarea"
                placeholder="Please input"
              />
            </el-form-item>
          </el-form>
        </div>
      </el-tab-pane>
      <el-tab-pane label="Past Consultations" name="socenv" v-if="checkRole(['admin','doctor'])">
        <el-card style="max-width: 100%">
          <el-table
            v-loading="loading"
            :data="patients_history"
            border
            fit
            highlight-current-row
            style="width: 100%"
            @row-click="selectRow"
          >
            <el-table-column align="center" label="Date of Visit" width="110">
              <template slot-scope="scope">
                <span>{{ scope.row.date }}</span>
              </template>
            </el-table-column>
            <el-table-column align="center" label="Chief Complaints">
              <template slot-scope="scope">
                <span>{{ scope.row.cf }}</span>
              </template>
            </el-table-column>
          </el-table>
        </el-card>
      </el-tab-pane>
      <!-- <el-tab-pane label="Attachments" name="attachments">
        <div class="mb-4">
          <el-upload ref="uploadRef" action="#" :auto-upload="false" multiple :on-change="handleChange">
            <template #trigger>
              <el-button ref="uploadRef" size="small" type="info" action="#" :auto-upload="false" multiple
                :on-change="handleChange">Select attachments</el-button>
            </template>
            <el-button size="small" type="primary" @click="submitUpload">Submit</el-button>
          </el-upload>
        </div>
        <el-card style="max-width: 100%">
          <el-dialog :visible.sync="dialogVisible" width="50%">
            <el-image :src="selectedImage.file" :alt="selectedImage.alt" fit="contain" class="popup-image" />
            <span slot="footer" class="dialog-footer">
              <el-button @click="dialogVisible = false">Close</el-button>
            </span>
          </el-dialog>

          <el-table :data="attachments" style="width: 100%">
            <el-table-column label="Image">
              <template slot-scope="scope">
                <el-button type="primary" icon="el-icon-files" @click="viewFile(scope.row.newfile)" /> {{ scope.row.fname }}
              </template>
            </el-table-column>
            <el-table-column label="Action" width="150">
              <template slot-scope="scope">
                <el-button type="danger" icon="el-icon-delete" @click="deleteAtt(scope.row.id)" />
              </template>
            </el-table-column>
          </el-table>
          <el-dialog :visible.sync="viewFileModel"  width="80%" :fullscreen="false" :close-on-click-modal="false">
            <template #default>
              <div class="iframe-wrapper">
                <iframe :src="sourceFile" frameborder="0" class="iframe-full"></iframe>
              </div>
            </template>
          </el-dialog>
        </el-card>
      </el-tab-pane> -->

      
      <el-tab-pane label="Attachments" name="attachments">
        <div class="mb-4">
          <el-upload ref="uploadRef" action="#" :auto-upload="false" multiple :on-change="handleChange">
            <template #trigger>
              <el-button ref="uploadRef" size="small" type="info" action="#" :auto-upload="false" multiple
                :on-change="handleChange">Select attachments</el-button>
            </template>
            <el-button size="small" type="primary" :loading="isUploading" @click="submitUpload">Submit</el-button>
          </el-upload>
          
          <!-- Upload Progress -->
          <div v-if="isUploading" class="mt-3">
            <el-progress :percentage="uploadProgress" :status="uploadProgress === 100 ? 'success' : ''"></el-progress>
            <p class="text-sm text-gray-600 mt-2">{{ uploadStatus }}</p>
          </div>
        </div>
        <el-card style="max-width: 100%">
          <el-dialog :visible.sync="dialogVisible" width="50%">
            <el-image :src="selectedImage.file" :alt="selectedImage.alt" fit="contain" class="popup-image" />
            <span slot="footer" class="dialog-footer">
              <el-button @click="dialogVisible = false">Close</el-button>
            </span>
          </el-dialog>

          <el-table :data="attachments" style="width: 100%">
            <el-table-column label="Image">
              <template slot-scope="scope">
                <el-button type="primary" icon="el-icon-files"
                  @click="viewFile(scope.row.newfile, scope.row.extension)" />
                {{ scope.row.fname }}
              </template>
            </el-table-column>
            <el-table-column label="Name" prop="description" />
            <el-table-column label="Date" prop="created_dt" />
            <el-table-column label="Action" width="150">
              <template slot-scope="scope">
                <el-button type="danger" icon="el-icon-delete" @click="deleteAtt(scope.row.id)" />
              </template>
            </el-table-column>
          </el-table>

          <el-dialog :visible.sync="viewFileModel" :fullscreen="false" :close-on-click-modal="false">
            <template #default>
              <div class="iframe-wrapper">
                <iframe v-if="isPdf" :src="sourceFile" :style="transformStyle" frameborder="0"
                  class="iframe-full"></iframe>
                <el-image v-if="!isPdf" style="width: 100px; height: 100px" :src="sourceFile" :zoom-rate="1.2"
                  :max-scale="7" :min-scale="0.2" :preview-src-list="[sourceFile]" show-progress :initial-index="4"
                  fit="cover" />
              </div>
            </template>
          </el-dialog>
        </el-card>
      </el-tab-pane>
    </el-tabs>

    <el-dialog
      :title="'Add Appointment'"
      width="70%"
      :visible.sync="dialogFormVisible"
      :close-on-click-modal="false"
      :close-on-press-escape="false"
    >
      <div class="form-container">
        <el-form
          ref="appForm"
          :model="form_appointment"
          :rules="rules"
          label-position="left"
          label-width="150px"
          style="max-width: 500px"
        >
        </el-form>
        <el-form label-position="right" class="demo-form-inline" :inline="true">
          <el-form-item :label="'Date'" prop="apt_dt">
            <el-date-picker
              v-model="form_appointment.apt_dt"
              type="date"
              :picker-options="pickerOptions"
              placeholder="Pick a day"
            />
          </el-form-item>          
          <el-form-item :label="'Systolic'" prop="vit_sys">
            <el-input v-model="form_appointment.vit_sys"/>
          </el-form-item>
          <el-form-item :label="'Diastolic'" prop="vit_dia">
            <el-input v-model="form_appointment.vit_dia"/>
          </el-form-item>
          <el-form-item label="HR">
            <el-input v-model="form_appointment.hr" clearable placeholder="Heart Rate" />
          </el-form-item>
          <el-form-item label="RR">
            <el-input
              v-model="form_appointment.rr"
              clearable
              placeholder="Respiratory Rate"
            />
          </el-form-item>
          <el-form-item :label="'Weight'" prop="weight">
            <el-input v-model="form_appointment.weight" />
          </el-form-item>
          <el-form-item :label="'Height'" prop="height">
            <el-input v-model="form_appointment.height" />
          </el-form-item><el-form-item label="Cardiac Rate">
              <el-input v-model="form_appointment.vit_cr" clearable />
            </el-form-item>
            <el-form-item label="Respiratory Rate">
              <el-input v-model="form_appointment.vit_rr" clearable />
            </el-form-item>
            <el-form-item label="O2 Stat">
              <el-input v-model="form_appointment.o2_stat" clearable />
            </el-form-item>
          <el-form-item :label="'Temperature'" prop="vit_temp">
            <el-input v-model="form_appointment.vit_temp" />
          </el-form-item>
        </el-form>

        <el-form
          label-position="right"
          class="demo-form-inline"
          :inline="true"
          style="width: 100%"
        >
          <el-form-item :label="'Remarks'" prop="complaints" style="width: 100%">
            <el-input
              v-model="form_appointment.nurse_remarks"
              :autosize="{ minRows: 3, maxRows: 5 }"
              style="width: 800px"
              :rows="2"
              type="textarea"
              placeholder="Please input"
            />
          </el-form-item>
        </el-form>

        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">
            {{ $t("table.cancel") }}
          </el-button>
          <el-button type="primary" :loading="isProcessing" @click="addAppointment()">
            {{ $t("table.confirm") }}
          </el-button>
        </div>
      </div>
    </el-dialog>
  </el-card>
</template>

<script>
import role from "@/directive/role/index.js";
import LoadingOverlay from "../../components/loading.vue";
import Patients from "@/api/patients";
import Pagination from "@/components/Pagination";
import moment from "moment-timezone";
import checkRole from '@/utils/role'; // Role checking
import heic2any from "heic2any";
export default {
  components: { Pagination, LoadingOverlay },
  directives: { role },
  props: {
    user: {
      type: Object,
      default: () => {
        return {
          name: "",
          email: "",
          avatar: "",
          roles: [],
        };
      },
    },
    profile: {
      type: Object,
      default: () => {
        return {
          patientname: "",
          birthdate: "",
          address: "",
        };
      },
    },
    image: {
      type: String,
      default: "",
    },
  },
  data() {
    return {
      isPdf: false,
      viewFileModel: false,
      sourceFile: null,
      pageloading: true,
      isProcessing: false,
      isProcessingAdolecents: false,
      isProcessingVax: false,
      isUploading: false,
      // Upload progress tracking
      uploadProgress: 0,
      uploadStatus: '',
      popconfirmDeleteProblem: false,
      additionalIdForDelete: 0,
      selectedOldRecords: {},
      oldRecordsdialogVisible: false,
      old_records: [],
      dialogVisible: false,
      selectedImage: {},
      attachments: [],
      patients_history: [],
      pid: "",
      past_meds: [
        "Bronchial Asthma",
        "Hypertension",
        "Previous MI",
        "Allergies",
        "Prior Surgery",
        "Prior Angina",
        "PAD",
        "PTB",
        "Previous Myocardial infraction",
        "Dyslipidemia",
        "COPD",
      ],
      formProblem: {
        id: 0,
        description: "",
        value: "",
        isactive: true,
        pid: this.$route.params.id,
      },
      formAdolecents: {
        id: 0,
        description: "",
        value: "",
        pid: this.$route.params.id,
      },
      formVax: {
        id: 0,
        vax: "",
        first_dose: "",
        second_dose: "",
        third_dose: "",
        booster: "",
        pid: this.$route.params.id,
      },
      formGd: {
        id: 0,
        gross_motor: "",
        gross_motor_age: "",
        fine_motor: "",
        fine_motor_age: "",
        language: "",
        language_age: "",
        social: "",
        social_age: "",
        pid: this.$route.params.id,
      },
      form_appointment: {
        complaints: "",
        cancel_reason: "",
        pid: this.$route.params.pid,
        apt_dt: null,
        patient: "",
        remakrs: null,
        vit_sys: null,
        vit_dia: null,
        weight: null,
        height: null,
        bp: null,
        hr: null,
        rr: null,
        vit_temp: null,
        vit_cr: null,
        vit_rr: null,
      },
      form: {
        hypertension: "",
        prev_admission: "",
        prev_surgeries: "",
        allergies: "",
        asthma: "",
        newborn_hearing: "",
        tb: "",
        seizure: "",
        copd: "",
        diabetes: "",
        mo_comorb: "",
        fa_comorb: "",
        blood_type: "",
        number_members: 0,
        water_source: "",
        breastfeed_dur: "",
        milk_dur: "",
        complementary_feeding: "",
        ob_score: "",
        cog_aog: "",
        maternal_illness: "",
        prenatal_checkup: "",
        vaccination_sup: "",
        maternal_age_dur_preg: "",
        maternal_b_type: "",
        term_pre_post: "",
        nsd_cs: "",
        birth_weight: "",
        cry: "",
        palce_delivery: "",
        complications: "",
        caregiver_name: "",
        caregiver_age: 0,
        caregiver_rel: "",
        caregiver_contact: "",
        caregiver_occupation: "",
        siblings_details: "",
        patientid: "",
        pmh: [],
        pmh_others: "",
        fam: [],
        fam_others: "",
        soc: [],
        soc_others: "",
        firstname: "",
        middlename: "",
        lastname: "",
        contactno: "",
        email:"",
        birthdate: "",
        id: this.$route.params.id,
        sex: "",
        civil_status: "",
        oscaid: "",
        referredby: "",
        remarks: "",
        address: "",
        occupation: "",
        profile: "",
        isold_patient: 0,
      },
      patientid_id: 0,
      form_att: {
        patientid: this.$route.params.pid,
        file: [],
      },
      activeActivity: "first",
      updating: false,
      uniqueArray: [],
      dialogFormVisible: false,
      dialogFormAdolecentsVisible: false,
      dialogFormVaxVisible: false,
      dialogFormGdVisible: false,
      query: {
        page: 1,
        limit: 15,
        keyword: "",
        role: "",
        date: moment().format("YYYY-MM-DD"),
        isdone: false,
        state: 0, //meaning not done
      },
      rules: {
        description: [
          { required: true, message: "Please input your description", trigger: "blur" },
        ],
      },
      rulesAdolecense: {
        description: [
          { required: true, message: "Please input your description", trigger: "blur" },
        ],
      },
      rulesVax: {
        vax: [
          { required: true, message: "Please input your Vaccination", trigger: "blur" },
        ],
      },
      additionalList: [],
      adolecents: [],
      vaxs: [],
      growthdevs: [],
    };
  },
  watch: {
    // Watch for changes in the any props
    profile: {
      handler(newValue) {
        this.patientid_id = newValue.id;
        this.form.patientid = newValue.patientid;
        this.form.firstname = newValue.firstname
          ? newValue.firstname
          : this.form.firstname;
        this.form.middlename = newValue.middlename
          ? newValue.middlename
          : this.form.middlename;
        this.form.lastname = newValue.lastname ? newValue.lastname : this.form.lastname;
        this.form.address = newValue.address ? newValue.address : this.form.address;
        this.form.birthdate = newValue.birthdate
          ? newValue.birthdate
          : this.form.birthdate;
        this.form.contactno = newValue.contactno
          ? newValue.contactno
          : this.form.contactno;
        this.form.email = newValue.email
          ? newValue.email
          : this.form.email;
        this.form.occupation = newValue.occupation
          ? newValue.occupation
          : this.form.occupation;
        this.form.sex = newValue.sex ? newValue.sex : this.form.sex;
        this.form.civil_status = newValue.civil_status
          ? newValue.civil_status
          : this.form.civil_status;
        this.form.referredby = newValue.referredby
          ? newValue.referredby
          : this.form.referredby;
        this.form.blood_type = newValue.blood_type
          ? newValue.blood_type
          : this.form.blood_type;
        this.form.prev_admission = newValue.prev_admission ?? this.form.prev_admission;
        this.form.prev_surgeries = newValue.prev_surgeries
          ? newValue.prev_surgeries
          : this.form.prev_surgeries;
        this.form.allergies = newValue.allergies
          ? newValue.allergies
          : this.form.allergies;
        this.form.asthma = newValue.asthma ? newValue.asthma : this.form.asthma;
        this.form.newborn_hearing = newValue.newborn_hearing
          ? newValue.newborn_hearing
          : this.form.newborn_hearing;
        this.form.tb = newValue.tb ? newValue.tb : this.form.tb;
        this.form.seizure = newValue.seizure ? newValue.seizure : this.form.seizure;
        this.form.hypertension = newValue.hypertension ? newValue.hypertension : this.form.hypertension;
        this.form.copd = newValue.copd ? newValue.copd : this.form.copd;
        this.form.diabetes = newValue.diabetes ? newValue.diabetes : this.form.diabetes;
        this.form.mo_comorb = newValue.mo_comorb
          ? newValue.mo_comorb
          : this.form.mo_comorb;
        this.form.fa_comorb = newValue.fa_comorb
          ? newValue.fa_comorb
          : this.form.fa_comorb;
        this.form.number_members = newValue.number_members
          ? newValue.number_members
          : this.form.number_members;
        this.form.water_source = newValue.water_source
          ? newValue.water_source
          : this.form.water_source;
        this.form.breastfeed_dur = newValue.breastfeed_dur
          ? newValue.breastfeed_dur
          : this.form.breastfeed_dur;
        this.form.milk_dur = newValue.milk_dur ? newValue.milk_dur : this.form.milk_dur;
        this.form.complementary_feeding = newValue.complementary_feeding
          ? newValue.complementary_feeding
          : this.form.complementary_feeding;
        this.form.ob_score = newValue.ob_score ? newValue.ob_score : this.form.ob_score;
        this.form.cog_aog = newValue.cog_aog ? newValue.cog_aog : this.form.cog_aog;
        this.form.maternal_illness = newValue.maternal_illness
          ? newValue.maternal_illness
          : this.form.maternal_illness;
        this.form.prenatal_checkup = newValue.prenatal_checkup
          ? newValue.prenatal_checkup
          : this.form.prenatal_checkup;
        this.form.vaccination_sup = newValue.vaccination_sup
          ? newValue.vaccination_sup
          : this.form.vaccination_sup;
        this.form.maternal_age_dur_preg = newValue.maternal_age_dur_preg
          ? newValue.maternal_age_dur_preg
          : this.form.maternal_age_dur_preg;
        this.form.maternal_b_type = newValue.maternal_b_type
          ? newValue.maternal_b_type
          : this.form.maternal_b_type;
        this.form.term_pre_post = newValue.term_pre_post
          ? newValue.term_pre_post
          : this.form.term_pre_post;
        this.form.nsd_cs = newValue.nsd_cs ? newValue.nsd_cs : this.form.nsd_cs;
        this.form.birth_weight = newValue.birth_weight
          ? newValue.birth_weight
          : this.form.birth_weight;
        this.form.cry = newValue.cry ? newValue.cry : this.form.cry;
        this.form.palce_delivery = newValue.palce_delivery
          ? newValue.palce_delivery
          : this.form.palce_delivery;
        this.form.complications = newValue.complications
          ? newValue.complications
          : this.form.complications;
        this.form.caregiver_name = newValue.caregiver_name
          ? newValue.caregiver_name
          : this.form.caregiver_name;
        this.form.caregiver_age = newValue.caregiver_age
          ? newValue.caregiver_age
          : this.form.caregiver_age;
        this.form.caregiver_rel = newValue.caregiver_rel
          ? newValue.caregiver_rel
          : this.form.caregiver_rel;
        this.form.caregiver_contact = newValue.caregiver_contact
          ? newValue.caregiver_contact
          : this.form.caregiver_contact;
        this.form.caregiver_occupation = newValue.caregiver_occupation
          ? newValue.caregiver_occupation
          : this.form.caregiver_occupation;
        this.form.siblings_details = newValue.siblings_details
          ? newValue.siblings_details
          : this.form.siblings_details;

        /* const pmh = newValue.pmh.split(",");
        pmh.forEach((element) => {
          this.form.pmh.push(element);
        }); */
        this.form.pmh_others = newValue.pmh_others
          ? newValue.pmh_others
          : this.form.pmh_others;
        if(newValue.fam){
          const fam = newValue.fam.split(",");
          fam.forEach((element) => {
            this.form.fam.push(element);
          });
        }
        this.form.fam_others = newValue.fam_others;
        if(newValue.soc){
          const soc = newValue.soc.split(",");
          soc.forEach((element) => {
            this.form.soc.push(element);
          });
        }
        this.form.soc_others = newValue.soc_others;
      },
      immediate: true, // Call handler immediately with the current value
    },
    image: {
      handler(v) {
        this.form.profile = v;
      },
      immediate: true,
    },
    patientidpx: {
      handler(v) {},
      immediate: true,
    },
  },
  created() {
    this.past_consult();
    this.get_attachments();
    /* this.getProblemList();
    this.getAdolecentsList();
    this.getVaxsList();
    this.getGrowthDevList(); */
  },
  methods: {
    checkRole,
    handleCreate() {
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs["appForm"].clearValidate();
      });
    },
    handleCreateAdolecents() {
      this.dialogFormAdolecentsVisible = true;
      this.$nextTick(() => {
        this.$refs["appFormAdolecents"].clearValidate();
      });
    },
    handleCreateVax() {
      this.dialogFormVaxVisible = true;
      this.$nextTick(() => {
        this.$refs["appFormVax"].clearValidate();
      });
    },
    handleCreateGd() {
      this.dialogFormGdVisible = true;
      this.$nextTick(() => {
        this.$refs["appFormGrowthDev"].clearValidate();
      });
    },
    openDialog(image) {
      this.selectedImage = image;
      this.dialogVisible = true;
    },
    handleClick(tab, event) {},
    async onSubmit() {
      this.pageloading = true;
      const formData = new FormData();
      for (const key in this.form) {
        formData.append(key, this.form[key]);
      }

      if (this.form.profile.length > 0) {
        formData.append("profile_pic", this.form.profile[0].raw);
      }
      
      let socVal = '';
      this.form.soc.forEach(element => {
        socVal+=element;
      });
      this.form.soc = socVal

      let famVal = '';
      this.form.fam.forEach(element => {
        famVal+=element;
        console.log(element)
      });
      this.form.fam = famVal

      await Patients.update(formData)
        .then((response) => {
          this.$message({
            message: "Patient profile has been updated successfully.",
            type: "success",
            duration: 5 * 1000,
          });
          location.reload();
          this.pageloading = false;
        })
        .catch((err) => {
          console.error("Error adding suggestions:", err);
        });
    },
    async past_consult() {
      await Patients.getpatientpastconsult(this.$route.params.pid)
        .then((response) => {
          this.patients_history = response.data;
          this.old_records = response.get_OldDiagnosis;
          this.pageloading = false;
        })
        .catch((err) => {
          console.error("Error adding suggestions:", err);
        });
    },
    async get_attachments() {
      this.attachments = [];
      await Patients.getAttachments(this.$route.params.pid)
        .then((response) => {
          this.attachments = response.data;
          this.pageloading = false;
        })
        .catch((err) => {
          console.error("Error adding suggestions:", err);
        });
    },
    selectRow(row) {
      this.$router.push({ path: "/appointments/form/" + row.id });
    },
    editAdditional(index, row) {
      this.dialogFormVisible = true;
      this.formProblem.id = row.id;
      this.formProblem.description = row.description;
      this.formProblem.value = row.value;
      this.formProblem.isactive = row.ischeck == 1 ? true : false;
    },
    handleChange(file, fileList) {
      this.form_att.files = fileList.map((fileItem) => fileItem.raw);
    },
    // Image compression utility
    compressImage(file, quality = 0.8, maxWidth = 1920, maxHeight = 1080) {
      return new Promise((resolve) => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();
        
        img.onload = () => {
          // Calculate new dimensions
          let { width, height } = img;
          
          if (width > maxWidth || height > maxHeight) {
            const ratio = Math.min(maxWidth / width, maxHeight / height);
            width *= ratio;
            height *= ratio;
          }
          
          // Set canvas dimensions
          canvas.width = width;
          canvas.height = height;
          
          // Draw and compress
          ctx.drawImage(img, 0, 0, width, height);
          
          canvas.toBlob(
            (blob) => {
              const compressedFile = new File([blob], file.name, {
                type: 'image/jpeg',
                lastModified: Date.now()
              });
              resolve(compressedFile);
            },
            'image/jpeg',
            quality
          );
        };
        
        img.src = URL.createObjectURL(file);
      });
    },
    async submitUpload() {
      // Initialize upload progress
      this.uploadProgress = 0;
      this.isUploading = true;
      this.uploadStatus = 'Preparing files...';
      
      const formData = new FormData();
      formData.append("patientid", this.form_att.patientid);

      const totalFiles = this.form_att.files.length;
      let processedFiles = 0;

      for (let i = 0; i < this.form_att.files.length; i++) {
        const file = this.form_att.files[i];
        const extension = file.name.split(".").pop().toLowerCase();
        
        // Update progress
        this.uploadStatus = `Processing file ${i + 1} of ${totalFiles}: ${file.name}`;
        this.uploadProgress = (processedFiles / totalFiles) * 30; // 30% for processing
        
        let processedFile = file;

        // Handle HEIC/HEIF conversion
        if (extension === "heic" || extension === "heif") {
          try {
            this.uploadStatus = `Converting HEIC file: ${file.name}`;
            const bmpBlob = await heic2any({
              blob: file,
              toType: "image/bmp",
              quality: 0.9,
            });

            processedFile = new File(
              [bmpBlob],
              file.name.replace(/\.(heic|heif)$/i, ".jpg"),
              { type: "image/bmp" }
            );
          } catch (error) {
            console.error("HEIC conversion failed:", error);
            this.$message.error("HEIC conversion failed.");
            this.isUploading = false;
            this.uploadProgress = 0;
            this.uploadStatus = '';
            return;
          }
        }
        
        // Compress images (JPEG, PNG, BMP, WebP)
        const imageTypes = ['jpg', 'jpeg', 'png', 'bmp', 'webp'];
        if (imageTypes.includes(extension.toLowerCase())) {
          try {
            this.uploadStatus = `Compressing image: ${file.name}`;
            processedFile = await this.compressImage(processedFile, 0.8, 1920, 1080);
            console.log(`Compressed ${file.name}: ${file.size} -> ${processedFile.size} bytes`);
          } catch (error) {
            console.error("Image compression failed:", error);
            // Continue with original file if compression fails
          }
        }

        formData.append(`files[${i}]`, processedFile);
        processedFiles++;
        this.uploadProgress = (processedFiles / totalFiles) * 30; // 30% for processing
      }

      try {
        this.uploadStatus = 'Uploading files...';
        this.uploadProgress = 30; // Start upload at 30%
        
        const response = await Patients.addAttachments(formData);
        
        // Upload successful
        this.uploadProgress = 100;
        this.uploadStatus = 'Upload completed!';
        
        this.form_att.file = "";
        this.get_attachments(this.form_att.patientid);
        this.$message.success("File uploaded successfully!");
        this.$refs.uploadRef.clearFiles();
        
        // Reset upload state
        this.isUploading = false;
        this.uploadProgress = 0;
        this.uploadStatus = '';
        
      } catch (err) {
        console.error("Error uploading files:", err);
        this.$message.error("Upload failed.");
        
        // Reset upload state on failure
        this.isUploading = false;
        this.uploadProgress = 0;
        this.uploadStatus = '';
      }
    },
    getBase64(file) {
      return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = () => resolve(reader.result);
        reader.onerror = (error) => reject(error);
      });
    },
    imgSrc(newfile, oldfile, isold) {
      try {
        if (isold === 0) {
          return newfile;
        } else {
          return `public/${oldfile}`;
        }
      } catch (e) {
        return `public/${oldfile}`;
      }
    },
    async deleteAtt(id) {
      await this.$confirm("Are you done with this file?", "Warning", {
        confirmButtonText: "OK",
        cancelButtonText: "Cancel",
        type: "warning",
      })
        .then(() => {
          Patients.deleteAttachments(id)
            .then((response) => {
              this.$message({
                type: "success",
                message: "Deleted File",
              });
              this.get_attachments();
            })
            .catch((err) => {
              console.error("Error adding suggestions:", err);
            });
        })
        .catch(() => {
          this.$message({
            type: "info",
            message: "Canceled action.",
          });
        });
    },
    async deletePatient() {
      await Patients.delete(this.$route.params.pid)
        .then((response) => {
          this.$message({
            message: "Patient has been deleted successfully.",
            type: "success",
            duration: 5 * 1000,
          });
          this.$router.push({ path: "/masterfile/patients" });
        })
        .catch((err) => {
          console.error("Error adding suggestions:", err);
        });
    },
    printChart() {
      window.open("/api/printchart/" + this.$route.params.pid);
    },
    handleRowClick(row, column, event) {
      this.selectedOldRecords = row;
      this.oldRecordsdialogVisible = true;
    },
    async getProblemList() {
      this.additionalList = [];
      await Patients.getPatientAdditionalCheckList(this.$route.params.id)
        .then((response) => {
          this.additionalList = response.data;
        })
        .catch((err) => {
          console.error("Error adding suggestions:", err);
        });
    },
    async AddProblem() {
      await this.$refs["appForm"].validate((valid) => {
        if (valid) {
          this.isProcessing = true;
          this.form.apt_dt = moment(this.form.apt_dt)
            .tz("Asia/Manila")
            .format("YYYY-MM-DD");
          Patients.AddProblem(this.formProblem)
            .then((response) => {
              this.$message({
                message: "Created successfully.",
                type: "success",
                duration: 5 * 1000,
              });
              this.getProblemList();
            })
            .catch((err) => {
              console.error("Error adding suggestions:", err);
            })
            .finally(() => {
              // This will always run, regardless of the request outcome
              this.isProcessing = false;
              this.dialogFormVisible = false;
              this.formProblem.id = 0;
              this.formProblem.description = "";
              this.formProblem.value = "";
              this.formProblem.isactive = true;
            });
        } else {
          console.log("error submit!!");
          return false;
        }
      });
    },
    async confirmDeleteProblem(index, row) {
      await Patients.deleteProblem(row.id)
        .then((response) => {
          this.$message({
            type: "success",
            message: "Successfully Deleted",
          });
          this.getProblemList();
        })
        .catch((err) => {
          console.error("Error adding suggestions:", err);
        });
    },
    async getAdolecentsList() {
      this.adolecents = [];
      await Patients.getPatientAdolecense(this.$route.params.id)
        .then((response) => {
          this.adolecents = response.data;
        })
        .catch((err) => {
          console.error("Error adding suggestions:", err);
        });
    },
    async AddAdolecense() {
      await this.$refs["appFormAdolecents"].validate((valid) => {
        if (valid) {
          this.isProcessingAdolecents = true;
          Patients.AddAdolecense(this.formAdolecents)
            .then((response) => {
              this.$message({
                message: "Created successfully.",
                type: "success",
                duration: 5 * 1000,
              });
              this.getAdolecentsList();
            })
            .catch((err) => {
              console.error("Error adding suggestions:", err);
            })
            .finally(() => {
              this.isProcessingAdolecents = false;
              this.dialogFormAdolecentsVisible = false;
              this.formAdolecents.id = 0;
              this.formAdolecents.description = "";
              this.formAdolecents.value = "";
            });
        } else {
          console.log("error submit!!");
          return false;
        }
      });
    },
    async confirmDeleteAdolecents(index, row) {
      await Patients.deleteAdolecense(row.id)
        .then((response) => {
          this.$message({
            type: "success",
            message: "Successfully Deleted",
          });
          this.getAdolecentsList();
        })
        .catch((err) => {
          console.error("Error adding suggestions:", err);
        });
    },
    editAdolecents(index, row) {
      this.dialogFormAdolecentsVisible = true;
      this.formAdolecents.id = row.id;
      this.formAdolecents.description = row.description;
      this.formAdolecents.value = row.value;
    },
    async getVaxsList() {
      this.vaxs = [];
      await Patients.getPatientVaxs(this.$route.params.id)
        .then((response) => {
          this.vaxs = response.data;
        })
        .catch((err) => {
          console.error("Error adding suggestions:", err);
        });
    },
    async AddVax() {
      await this.$refs["appFormVax"].validate((valid) => {
        if (valid) {
          this.isProcessingVax = true;
          Patients.AddVax(this.formVax)
            .then((response) => {
              this.$message({
                message: "Vaccination has been created successfully.",
                type: "success",
                duration: 5 * 1000,
              });
              this.getVaxsList();
            })
            .catch((err) => {
              console.error("Error adding suggestions:", err);
            })
            .finally(() => {
              this.isProcessingVax = false;
              this.dialogFormVaxVisible = false;
              this.formAdolecents.id = 0;
              this.formAdolecents.vax = "";
              this.formAdolecents.first_dose = "";
              this.formAdolecents.second_dose = "";
              this.formAdolecents.third_dose = "";
              this.formAdolecents.booster = "";
            });
        } else {
          console.log("error submit!!");
          return false;
        }
      });
    },
    async confirmDeleteVax(index, row) {
      await Patients.deleteVax(row.id)
        .then((response) => {
          this.$message({
            type: "success",
            message: "Successfully Deleted",
          });
          this.getVaxsList();
        })
        .catch((err) => {
          console.error("Error adding suggestions:", err);
        });
    },
    editVax(index, row) {
      this.dialogFormVaxVisible = true;
      this.formVax.id = row.id;
      this.formVax.vax = row.vax;
      this.formVax.first_dose = row.first_dose;
      this.formVax.second_dose = row.second_dose;
      this.formVax.third_dose = row.third_dose;
      this.formVax.booster = row.booster;
    },
    async getGrowthDevList() {
      this.growthdevs = [];
      await Patients.getPatientGrowthDev(this.$route.params.id)
        .then((response) => {
          this.growthdevs = response.data;
        })
        .catch((err) => {
          console.error("Error adding suggestions:", err);
        });
    },
    async AddGrowthDev() {
      await this.$refs["appFormGrowthDev"].validate((valid) => {
        if (valid) {
          this.isProcessingGrowthDev = true;
          Patients.AddGrowthDev(this.formGd)
            .then((response) => {
              this.$message({
                message: "Created successfully.",
                type: "success",
                duration: 5 * 1000,
              });
              this.getGrowthDevList();
            })
            .catch((err) => {
              console.error("Error adding suggestions:", err);
            })
            .finally(() => {
              this.isProcessingGrowthDev = false;
              this.dialogFormGdVisible = false;
              this.formGd.id = 0;
              this.formGd.gross_motor = "";
              this.formGd.gross_motor_age = "";
              this.formGd.fine_motor = "";
              this.formGd.fine_motor_age = "";
              this.formGd.language = "";
              this.formGd.language_age = "";
              this.formGd.social = "";
              this.formGd.social_age = "";
            });
        } else {
          console.log("error submit!!");
          return false;
        }
      });
    },
    async confirmDeleteGrowthDev(index, row) {
      await Patients.deleteGrowthDev(row.id)
        .then((response) => {
          this.$message({
            type: "success",
            message: "Successfully Deleted",
          });
          this.getGrowthDevList();
        })
        .catch((err) => {
          console.error("Error adding suggestions:", err);
        });
    },
    editGrowthDev(index, row) {
      this.dialogFormGdVisible = true;
      this.formGd.id = row.id;
      this.formGd.gross_motor = row.gross_motor;
      this.formGd.gross_motor_age = row.gross_motor_age;
      this.formGd.fine_motor = row.fine_motor;
      this.formGd.fine_motor_age = row.fine_motor_age;
      this.formGd.language = row.language;
      this.formGd.language_age = row.language_age;
      this.formGd.social = row.social;
      this.formGd.social_age = row.social_age;
    },
    checkExtn(a) {
      let b = a.split(".");
      return b[1];
    },
    viewFile(s, e) {
      this.isPdf = e == "pdf" ? true : false;
      this.viewFileModel = true;
      this.sourceFile = s;
    },
    addAppointment() {
      this.$refs["appForm"].validate((valid) => {
        if (valid) {
          this.isProcessing = true;
          this.form_appointment.apt_dt = moment(this.form_appointment.apt_dt)
            .tz("Asia/Manila")
            .format("YYYY-MM-DD");
          Patients.addAppointment(this.form_appointment)
            .then((response) => {
              this.form_appointment.complaints = "";
              this.form_appointment.pid = null;
              this.form_appointment.apt_dt = null;
              this.form_appointment.patient = null;
              this.form_appointment.remakrs = null;
              this.dialogFormVisible = false;
              this.$message({
                message: "Appointment has been created successfully.",
                type: "success",
                duration: 5 * 1000,
              });
              this.pageloading = true;
              this.past_consult();
            })
            .catch((err) => {
              console.error("Error adding suggestions:", err);
            })
            .finally(() => {
              // This will always run, regardless of the request outcome
              this.isProcessing = false;
            });
          // }, 5000);
        } else {
          console.log("error submit!!");
          return false;
        }
      });
    },
  },
};
</script>

<style lang="scss" scoped>
.user-activity {
  .user-block {
    .username,
    .description {
      display: block;
      margin-left: 50px;
      padding: 2px 0;
    }

    img {
      width: 40px;
      height: 40px;
      float: left;
    }

    :after {
      clear: both;
    }

    .img-circle {
      border-radius: 50%;
      border: 2px solid #d2d6de;
      padding: 2px;
    }

    span {
      font-weight: 500;
      font-size: 12px;
    }
  }

  .post {
    font-size: 14px;
    border-bottom: 1px solid #d2d6de;
    margin-bottom: 15px;
    padding-bottom: 15px;
    color: #666;

    .image {
      width: 100%;
    }

    .user-images {
      padding-top: 20px;
    }
  }

  .list-inline {
    padding-left: 0;
    margin-left: -5px;
    list-style: none;

    li {
      display: inline-block;
      padding-right: 5px;
      padding-left: 5px;
      font-size: 13px;
    }

    .link-black {
      &:hover,
      &:focus {
        color: #999;
      }
    }
  }

  .el-carousel__item h3 {
    color: #475669;
    font-size: 14px;
    opacity: 0.75;
    line-height: 200px;
    margin: 0;
  }

  .el-carousel__item:nth-child(2n) {
    background-color: #99a9bf;
  }

  .el-carousel__item:nth-child(2n + 1) {
    background-color: #d3dce6;
  }

  .compact-table .el-table__cell {
    padding: 5px 10px;
    /* Adjust padding for compactness */
    font-size: 12px;
    /* Adjust font size for compactness */
  }
}
</style>

<style scoped>
.iframe-wrapper {
  text-align: center;
  padding: 10px;
}

.iframe-transform-container {
  display: inline-block;
  overflow: hidden;
}

.iframe-full {
  width: 800px;
  height: 600px;
  border: none;
}


</style>