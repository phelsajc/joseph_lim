<template>
  <div class="app-container">

    <el-card style="max-width: 100%">
      <el-row :gutter="24">


        <!-- <el-form :inline="true" label-position="top" class="demo-form-inline">
          <el-form-item :label="'Patient'" prop="patient">
            <el-autocomplete v-model="form.patient" style="width: 400%" :fetch-suggestions="querySearch" popper-class="my-autocomplete"
              placeholder="Please input" @select="handleSelect">
              <template #suffix>
                <el-icon class="el-input__icon">
                  <edit />
                </el-icon>
              </template>
<template #default="{ item }">
                <div class="value">{{ item.patientname }}</div>
              </template>
</el-autocomplete>
</el-form-item>

<el-checkbox v-model="medsArr.custom_meds" label="Not carried" size="large" />
<br />
<el-form-item label="Quantity">
  <el-input v-model="medsArr.qty" autosize clearable />
</el-form-item>
<el-form-item v-if="!medsArr.custom_meds" label="Search Medicine">
  <el-autocomplete v-model="medsArr.meds" :fetch-suggestions="querySearchMedicine" popper-class="my-autocomplete"
    placeholder="Please input" style="width: 400%" @select="handleSelectMedicine">
    <template #default="{ item }">
                <div class="value">{{ item.medicine }}</div>
              </template>
  </el-autocomplete>
</el-form-item>
<el-form-item v-if="medsArr.custom_meds" label="Generic Name">
  <el-input v-model="medsArr.custom_generic" autosize clearable />
</el-form-item>
<el-form-item v-if="medsArr.custom_meds" label="Brand Name">
  <el-input v-model="medsArr.custom_brand" autosize clearable />
</el-form-item>
<el-form-item v-if="medsArr.custom_meds" label="Dosage">
  <el-input v-model="medsArr.custom_dosage" autosize clearable />
</el-form-item>
<el-form-item label="Quantity">
  <el-input v-model="medsArr.qty" autosize clearable />
</el-form-item>
</el-form> -->


        <!-- <el-form :inline="true" label-position="top" class="demo-form-inline">
          <el-form-item :label="'Patient'" prop="patient">
            <el-autocomplete v-model="form.patient" style="width: 400%" :fetch-suggestions="querySearch" popper-class="my-autocomplete"
              placeholder="Please input" @select="handleSelect">
              <template #suffix>
                <el-icon class="el-input__icon">
                  <edit />
                </el-icon>
              </template>
              <template #default="{ item }">
                <div class="value">{{ item.patientname }}</div>
              </template>
            </el-autocomplete>
          </el-form-item>

          <el-checkbox v-model="medsArr.custom_meds" label="Not carried" size="large" />
          <br />
            <el-checkbox v-model="medsArr.custom_meds" label="Not carried" size="large" style="margin-right: 20px" />
            <el-form-item label="Quantity" style="margin-right: 20px">
              <el-input v-model="medsArr.qty" autosize clearable />
            </el-form-item>

            
          <el-form-item v-if="!medsArr.custom_meds" label="Search Medicine">
            <el-autocomplete v-model="medsArr.meds" :fetch-suggestions="querySearchMedicine" popper-class="my-autocomplete"
              placeholder="Please input" style="width: 300%" @select="handleSelectMedicine">
              <template #default="{ item }">
                <div class="value">{{ item.medicine }}</div>
              </template>
            </el-autocomplete>
          </el-form-item>

            <el-form-item v-if="medsArr.custom_meds" label="Generic Name" style="margin-right: 20px">
              <el-input v-model="medsArr.custom_generic" autosize clearable />
            </el-form-item>
            <el-form-item v-if="medsArr.custom_meds" label="Brand Name" style="margin-right: 20px">
              <el-input v-model="medsArr.custom_brand" autosize clearable />
            </el-form-item>
            <el-form-item v-if="medsArr.custom_meds" label="Dosage" style="margin-right: 20px">
              <el-input v-model="medsArr.custom_dosage" autosize clearable />
            </el-form-item>
            
            <el-form-item label="Remarks" style="width: 650px">
              <el-input v-model="medsArr.remarks" />
            </el-form-item>
          </el-form> -->

        <el-form :inline="true" label-position="top" class="demo-form-inline">
          <el-form-item :label="'Patient'" prop="patient">
            <el-autocomplete v-model="form.patient" style="width: 400%" :fetch-suggestions="querySearch"
              popper-class="my-autocomplete" placeholder="Please input" @select="handleSelect">
              <template #suffix>
                <el-icon class="el-input__icon">
                  <edit />
                </el-icon>
              </template>
              <template #default="{ item }">
                <div class="value">{{ item.patientname }}</div>
              </template>
            </el-autocomplete>
          </el-form-item>
        </el-form>

        <el-form :inline="true" label-position="top" class="demo-form-inline">
          <el-checkbox v-model="medsArr.custom_meds" label="Not carried" size="large" style="margin-right: 20px" />
          <el-form-item label="Quantity" style="margin-right: 20px; width: 7%;">
            <el-input v-model="medsArr.qty" autosize clearable />
          </el-form-item>
          <el-form-item v-if="!medsArr.custom_meds" label="Search Medicine" style="margin-right: 20px; width: 400px">
            <el-autocomplete v-model="medsArr.meds" :fetch-suggestions="querySearchMedicine"
              popper-class="my-autocomplete" placeholder="Please input" style="width: 100%"
              @select="handleSelectMedicine">
              <template #default="{ item }">
                <div class="value">{{ item.medicine }}</div>
              </template>
            </el-autocomplete>
          </el-form-item>
          <el-form-item v-if="medsArr.custom_meds" label="Generic Name" style="margin-right: 20px">
            <el-input v-model="medsArr.custom_generic" autosize clearable />
          </el-form-item>
          <el-form-item v-if="medsArr.custom_meds" label="Brand Name" style="margin-right: 20px">
            <el-input v-model="medsArr.custom_brand" autosize clearable />
          </el-form-item>
          <el-form-item v-if="medsArr.custom_meds" label="Dosage" style="margin-right: 20px">
            <el-input v-model="medsArr.custom_dosage" autosize clearable />
          </el-form-item>
          <el-form-item label="Remarks" style="width: 650px">
            <el-input v-model="medsArr.remarks" />
          </el-form-item>
        </el-form>

      </el-row>

      <el-row :gutter="24">
        <el-table :data="rx_list" style="width: 100%" class="compact-table">
          <el-table-column prop="medicine" label="Medicine" width="700" />
          <el-table-column prop="qty" label="Qty" width="60" />
          <el-table-column prop="remarks" label="Remarks" width="700" />
          <el-table-column align="center" label="Actions" width="150">
            <template slot-scope="scope">
              <el-button v-role="['doctor', 'admin']" type="danger" @click="deleteMed(scope.row.id)">Delete</el-button>
            </template>
          </el-table-column>
        </el-table>
      </el-row>
      <el-divider />
      <el-row :gutter="20">
        <el-button v-role="['doctor', 'admin']" type="success" @click="addMeds()">
          Add
        </el-button>
        <el-button v-role="['doctor', 'admin']" type="primary" @click="printrx()">
          Print
        </el-button>
      </el-row>
    </el-card>
  </div>
</template>
<script>
import role from "@/directive/role/index.js";
import Patients from "@/api/patients";
import Medicine from "@/api/medicine";
import Procedure from "@/api/procedure";
import Services from "@/api/services";
import Diagnostics from "@/api/diagnostics";
import moment from "moment-timezone";
import debounce from "lodash/debounce";
/* import Quill from 'quill';
import 'quill/dist/quill.snow.css'; */
export default {
  directives: { role },
  data() {
    return {
      rx_list: [],
      medsArr: {
        patientid: null,
        custom_meds: false,
        qty: "",
        bf_b: "",
        bf_a: "",
        l_a: "",
        l_b: "",
        s_b: "",
        s_a: "",
        bt: "",
        meds: "",
        med_id: 0,
        id: this.$route.params.id,
        remarks: "",
        custom_generic: "",
        custom_brand: "",
        custom_dosage: "",
      },
      form: {
        complaints: '',
        cancel_reason: '',
        pid: null,
        apt_dt: null,
        patient: null,
        remakrs: null,
        vit_sys: null,
        vit_dia: null,
        weight: null,
        height: null,
        vit_temp: null,
        vit_cr: null,
        vit_rr: null,
      },
    };
  },
  watch: {
    "form.weight": "calculateBMI",
    "form.height": "calculateBMI",
  },
  mounted() {
    this.checkIfMobile();
    window.addEventListener("resize", this.checkIfMobile);
  },
  beforeDestroy() {
    window.removeEventListener("resize", this.checkIfMobile);
  },
  created() {
    this.getmeds();
  },
  methods: {
    checkIfMobile() {
      this.isMobile = window.innerWidth <= 768; // You can adjust the breakpoint as needed
      console.log(this.isMobile);
    },
    currentDt() {
      return moment(this.appointment_dt).format("MMMM DD, YYYY");
    },
    async querySearch(queryString, cb) {
      if (queryString === '') {
        // If query string is empty, reset suggestions
        cb([]);
        return;
      }
      try {
        this.loading = true;
        // Make an asynchronous request to your Laravel backend API using Axios
        const response = await Patients.findpatients(queryString);
        // Extract the array of suggestions from the response data
        const suggestions = response.suggestions;
        // Call back function
        cb(suggestions);
      } catch (error) {
        console.error('Error fetching suggestions:', error);
        cb([]);
      } finally {
        this.loading = false;
      }
    },
    handleSelect(ev) {
      // this.value = ev.patientname;
      this.form.patient = ev.patientname;
      this.medsArr.patientid = ev.id;
    },
    async querySearchMedicine(queryString, cb) {
      if (queryString === "") {
        // If query string is empty, reset suggestions
        cb([]);
        return;
      }
      try {
        this.loading = true;
        // Make an asynchronous request to your Laravel backend API using Axios
        const response = await Medicine.findmedicine(queryString);
        // Extract the array of suggestions from the response data
        const suggestions = response.suggestions;
        // Call back function
        cb(suggestions);
      } catch (error) {
        console.error("Error fetching suggestions:", error);
        cb([]);
      } finally {
        this.loading = false;
      }
    },
    handleSelectMedicine(ev) {
      this.medsArr.meds = ev.medicine;
      this.medsArr.med_id = ev.id;
    },
    addMeds() {
      if (
        (this.medsArr.meds !== "" && this.medsArr.qty !== "") ||
        (this.medsArr.custom_generic !== "" &&
          this.medsArr.custom_brand !== "" &&
          this.medsArr.custom_dosage !== "")
      ) {
        Medicine.add_rx_blank(this.medsArr)
          .then((response) => {
            this.getmeds();
            this.medsArr.qty = "";
            this.medsArr.bf_b = "";
            this.medsArr.bf_a = "";
            this.medsArr.l_a = "";
            this.medsArr.l_b = "";
            this.medsArr.s_b = "";
            this.medsArr.s_a = "";
            this.medsArr.bt = "";
            this.medsArr.meds = "";
            this.medsArr.remarks = "";
            this.medsArr.custom_generic = "";
            this.medsArr.custom_brand = "";
            this.medsArr.custom_dosage = "";
          })
          .catch((err) => {
            console.error("Error adding suggestions:", err);
          });
      } else {
        alert("Medicine details are required.");
      }
    },
    getmeds() {
      this.rx_list = [];
      Medicine.getAppointmentMedsBlank(this.medsArr.patientid)
        .then((response) => {
          this.rx_list = response.data;
        })
        .catch((err) => {
          console.error("Error adding suggestions:", err);
        });
    },
    printrx() {
      window.open("/api/printpdf3/" + this.medsArr.patientid);
    },
  },
};
</script>
<style type="style/scss">
@media (max-width: 768px) {
  .el-form-item__label[style] {
    text-align: unset !important;
    width: 100% !important;
  }

  .el-form-item__content[style] {
    margin-left: unset !important;
  }
}

.container {
  max-width: 100%;
  /* Ensures container's width adjusts responsively */
}

.el-form-item {
  margin-right: 20px;
  /* Adjust margin as needed */
}

.responsive-textarea .el-input__inner {
  width: 100%;
  /* Ensures textarea takes full width of form item */
}

.compact-table .el-table__cell {
  padding: 5px 10px;
  /* Adjust padding for compactness */
  font-size: 12px;
  /* Adjust font size for compactness */
}

.el-dialog__body {
  line-height: 29px;
}

.ql-container {
  min-height: 200px;
}
</style>

<style scoped>
.iframe-wrapper {
  height: 80vh;
  /* You can also use 100% if you set el-dialog body height */
  width: 100%;
}

.iframe-full {
  width: 100%;
  height: 100%;
  border: none;
}
</style>