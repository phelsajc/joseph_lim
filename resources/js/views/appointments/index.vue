<template>
  <div class="app-container appointments-page">

    <div class="filter-container appointments-filters">
      <div class="filter-row filter-row--primary">
        <el-input
          v-model="query.keyword"
          :placeholder="$t('table.keyword')"
          class="filter-item filter-keyword"
          clearable
          @keyup.enter.native="handleFilter"
        />
        <date-picker v-model="query.date" value-type="format" class="filter-item filter-date" />
        <el-button class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
          {{ $t('table.search') }}
        </el-button>
        <el-button class="filter-item" type="success" icon="el-icon-plus" @click="handleCreate">
          {{ $t('table.add') }}
        </el-button>
        <el-select
          v-model="status"
          placeholder="Select"
          class="filter-item filter-status"
          @change="changeSelect"
        >
          <el-option :key="1" label="Current" :value="0" />
          <el-option :key="2" label="Completed" :value="1" />
          <el-option :key="3" label="Cancelled" :value="2" />
        </el-select>
      </div>
    </div>

    <div v-loading="loading" class="appointments-grid">
      <div v-if="!loading && patients.length === 0" class="appointments-empty">
        <i class="el-icon-date appointments-empty-icon" />
        <p>No appointments found</p>
      </div>
      <article
        v-for="(row, index) in patients"
        :key="row.id"
        class="appointment-card"
        :class="{ 'appointment-card--active': row.isactive == 1 }"
      >
        <div class="appointment-card-header">
          <span class="appointment-card-seq">#{{ row.sequence }}</span>
          <el-image
            class="appointment-card-photo"
            :src="imgSrc(row.profile, row.patientid, row.type)"
            fit="cover"
            @error="imageLoadError(row.patientid)"
          />
          <div class="appointment-card-title">
            <h3 class="appointment-card-name">{{ row.patientname }}</h3>
            <p class="appointment-card-date">{{ row.apt_dt }}</p>
          </div>
        </div>
        <div class="appointment-card-body">
          <div v-if="row.complaints" class="appointment-card-field">
            <span class="field-label">Complaints</span>
            <span class="field-value">{{ row.complaints }}</span>
          </div>
          <div v-if="status == 2 && row.cancel_reason" class="appointment-card-field">
            <span class="field-label">Cancelled reason</span>
            <span class="field-value">{{ row.cancel_reason }}</span>
          </div>
          <div class="appointment-card-meta">
            <div class="meta-item">
              <span class="meta-label">Follow-up</span>
              <span class="meta-value">{{ row.flwup_dt || '—' }}</span>
            </div>
            <div class="meta-item">
              <span class="meta-label">Discount</span>
              <span class="meta-value">{{ row.discount || '0' }}</span>
            </div>
          </div>
          <div class="appointment-card-fee">
            <span class="field-label">Final fee</span>
            <el-popover trigger="click" placement="top" width="280">
              <div class="dues-popover">
                <p class="dues-popover-title">{{ row.patientname }}</p>
                <template v-if="row.services && row.services.length">
                  <div v-for="(item, idx) in row.services" :key="idx" class="dues-line">
                    <span>{{ item.service }}</span>
                    <span>{{ formatCurrency(item.fee) }}</span>
                  </div>
                  <div class="dues-line dues-subtotal">
                    <span>Subtotal</span>
                    <span>{{ formatCurrency(row.gross_fee) }}</span>
                  </div>
                  <div v-if="parseFloat(row.discount)" class="dues-line dues-discount">
                    <span>Discount</span>
                    <span>-{{ formatCurrency(row.discount) }}</span>
                  </div>
                </template>
                <p v-else class="dues-empty">No services recorded</p>
                <div class="dues-line dues-final">
                  <span>Final fee</span>
                  <span>{{ formatCurrency(row.fee) }}</span>
                </div>
              </div>
              <el-button slot="reference" type="text" class="fee-preview-btn">
                {{ formatCurrency(row.fee) }}
              </el-button>
            </el-popover>
          </div>
        </div>
        <div class="appointment-card-actions">
          <el-button type="primary" size="small" @click="selectRow(index, row)">
            View
          </el-button>
          <el-button type="warning" size="small" icon="el-icon-printer" @click="printPrescription(row)">
            Print Rx
          </el-button>
          <el-button type="success" size="small" icon="el-icon-money" @click="printFees(row)">
            Print Fees
          </el-button>
          <el-button type="danger" size="small" @click="cancelAppointment(index, row)">
            Cancel
          </el-button>
        </div>
      </article>
    </div>

      <el-table-column align="center" label="No" width="50">
        <template slot-scope="scope">
          <div class="handle"><span>{{ scope.row.sequence }}</span></div>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Name">
        <template slot-scope="scope">
          <div class="handle"><span>{{ scope.row.patientname }}</span></div>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Photo" width="80">
        <template slot-scope="scope">
          <div class="handle">
            <span>
              <el-image
                style="width: 50px; height: 50px"
                :src="imgSrc(scope.row.profile,scope.row.patientid,scope.row.type)"
                :zoom-rate="1.2"
                :max-scale="7"
                :min-scale="0.2"
                :initial-index="4"
                fit="cover"
                @error="imageLoadError(scope.row.patientid)"
              />
            </span>
          </div>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Complaints">
        <template slot-scope="scope">
          <div class="handle"><span>{{ scope.row.complaints }}</span></div>
        </template>
      </el-table-column>
      <el-table-column v-if="status==2" align="center" label="Cancelled Reason">
        <template slot-scope="scope">
          <div class="handle"><span>{{ scope.row.cancel_reason }}</span></div>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Date">
        <template slot-scope="scope">
          <div class="handle"><span>{{ scope.row.apt_dt }}</span></div>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Follow Up Date">
        <template slot-scope="scope">
          <div class="handle"><span>{{ scope.row.flwup_dt }}</span></div>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Discount" width="55">
        <template slot-scope="scope">
          <div class="handle"><span>{{ scope.row.discount }}</span></div>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Final Fee" width="100">
        <template slot-scope="scope">
          <el-popover trigger="click" placement="left" width="280">
            <div class="dues-popover">
              <p class="dues-popover-title">{{ scope.row.patientname }}</p>
              <template v-if="scope.row.services && scope.row.services.length">
                <div v-for="(item, idx) in scope.row.services" :key="idx" class="dues-line">
                  <span>{{ item.service }}</span>
                  <span>{{ formatCurrency(item.fee) }}</span>
                </div>
                <div class="dues-line dues-subtotal">
                  <span>Subtotal</span>
                  <span>{{ formatCurrency(scope.row.gross_fee) }}</span>
                </div>
                <div v-if="parseFloat(scope.row.discount)" class="dues-line dues-discount">
                  <span>Discount</span>
                  <span>-{{ formatCurrency(scope.row.discount) }}</span>
                </div>
              </template>
              <p v-else class="dues-empty">No services recorded</p>
              <div class="dues-line dues-final">
                <span>Final fee</span>
                <span>{{ formatCurrency(scope.row.fee) }}</span>
              </div>
            </div>
            <el-button slot="reference" type="text" class="fee-preview-btn">
              {{ formatCurrency(scope.row.fee) }}
            </el-button>
          </el-popover>
        </template>
      </el-table-column>

    <div v-if="patients.length > 0" class="dues-summary">
      <div class="dues-summary-header">
        <span>Total for the day</span>
        <span class="dues-summary-total">{{ formatCurrency(duesBreakdown.totalFinal) }}</span>
      </div>
    </div>

    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getPatients" />
    <!--  </el-table-draggable> -->

    <!-- <ElTableDraggable>
        <el-table row-key="id" :data="patients" @row-dragend="onRowDragEnd">
          <el-table-column
          key="id"
          label="Name"
          prop="patientname"
        ></el-table-column>
        </el-table>
    </ElTableDraggable> -->

    <!-- Add Appointment Modal -->
    <el-dialog
      title="Add New Appointment"
      :visible.sync="showAppointmentModal"
      width="640px"
      custom-class="appointment-modal"
      append-to-body
      :close-on-click-modal="false"
      :close-on-press-escape="false"
    >
      <div class="modal-content">
        <el-form
          ref="appForm"
          :model="form"
          :rules="rules"
          label-position="top"
          class="appointment-form"
        >
          <el-row :gutter="16">
            <el-col :span="24">
              <el-form-item label="Patient" prop="patient">
                <el-autocomplete
                  v-model="form.patient"
                  class="appointment-field-full"
                  :fetch-suggestions="querySearch"
                  popper-class="my-autocomplete"
                  placeholder="Search and select patient from list"
                  @select="handleSelect"
                >
                  <template #default="{ item }">
                    <div class="value">{{ item.patientname }}</div>
                  </template>
                </el-autocomplete>
              </el-form-item>
            </el-col>
            <el-col :span="24">
              <el-form-item label="Date" prop="apt_dt">
                <el-date-picker
                  v-model="form.apt_dt"
                  class="appointment-field-full"
                  type="date"
                  :picker-options="pickerOptions"
                  placeholder="Pick a day"
                />
              </el-form-item>
            </el-col>
            <el-col :xs="12" :sm="8">
              <el-form-item label="Systolic" prop="vit_sys">
                <el-input v-model="form.vit_sys" />
              </el-form-item>
            </el-col>
            <el-col :xs="12" :sm="8">
              <el-form-item label="Diastolic" prop="vit_dia">
                <el-input v-model="form.vit_dia" />
              </el-form-item>
            </el-col>
            <el-col :xs="12" :sm="8">
              <el-form-item label="Weight" prop="weight">
                <el-input v-model="form.weight" />
              </el-form-item>
            </el-col>
            <el-col :xs="12" :sm="8">
              <el-form-item label="Height" prop="height">
                <el-input v-model="form.height" />
              </el-form-item>
            </el-col>
            <el-col :xs="12" :sm="8">
              <el-form-item label="Temperature" prop="vit_temp">
                <el-input v-model="form.vit_temp" />
              </el-form-item>
            </el-col>
            <el-col :xs="12" :sm="8">
              <el-form-item label="CR" prop="vit_cr">
                <el-input v-model="form.vit_cr" />
              </el-form-item>
            </el-col>
            <el-col :xs="12" :sm="8">
              <el-form-item label="RR" prop="vit_rr">
                <el-input v-model="form.vit_rr" />
              </el-form-item>
            </el-col>
            <el-col :span="24">
              <el-form-item label="Remarks" prop="remarks">
                <el-input
                  v-model="form.nurse_remarks"
                  :autosize="{ minRows: 2, maxRows: 4 }"
                  :rows="2"
                  type="textarea"
                  placeholder="Please input"
                />
              </el-form-item>
            </el-col>
          </el-row>
        </el-form>
      </div>

      <div slot="footer" class="modal-footer">
        <el-button @click="closeAppointmentModal">Cancel</el-button>
        <el-button type="primary" :loading="savingAppointment" @click="saveAppointment">
          {{ savingAppointment ? 'Saving...' : 'Save Appointment' }}
        </el-button>
      </div>
    </el-dialog>

    <el-dialog :title="'Cancel Appointment'" :visible.sync="cancelForm" :close-on-click-modal="false" :close-on-press-escape="false">
      <div class="form-container">
        <el-form ref="cancelForm" :model="cancel_submitForm" :rules="cancel_rules" label-position="left" label-width="150px" style="max-width: 500px;">
          <el-form-item :label="'Reason'" prop="cancel_reason">
            <el-input v-model="cancel_submitForm.cancel_reason" />
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="cancelForm = false">
            {{ $t('table.cancel') }}
          </el-button>
          <el-button type="primary" :loading="isProcessing" @click="confirmCancel()">
            {{ $t('table.confirm') }}
          </el-button>
        </div>
      </div>
    </el-dialog>

  </div>
</template>
<script>
import role from '@/directive/role/index.js';
import Patients from '@/api/patients';
import Pagination from '@/components/Pagination';
import moment from 'moment-timezone';
import draggable from 'vuedraggable';
import { Table, TableColumn, TableRow } from 'element-ui';
import ElTableDraggable from 'element-ui-el-table-draggable';
import DatePicker from 'vue2-datepicker';
import 'vue2-datepicker/index.css';
export default {
  name: 'Patients',
  components: { Pagination, draggable, ElTableDraggable, DatePicker },
  directives: { role },
  data(){
    return {
      anyActive: false,
      currentRowKey: null,
      cancel_rules: {
        cancel_reason: [
          { required: true, message: 'Please provide reason', trigger: 'blur' },
        ],
      },
      cancelForm: false,
      cancel_submitForm: {
        id: 0,
        cancel_reason: '',
      },
      status: 0,
      tab: 'current',
      ss: this.patients,
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
        nurse_remarks: null,
      },
      rules: {
        complaints: [
          { required: true, message: 'Please input your complaints', trigger: 'blur' },
        ],
        patient: [
          {
            validator: (rule, value, callback) => {
              if (!value) {
                callback(new Error('Please select patient'));
              } else if (!this.form.pid) {
                callback(new Error('Please select a patient from the list'));
              } else {
                callback();
              }
            },
            trigger: ['blur', 'change'],
          },
        ],
        apt_dt: [
          { required: true, message: 'Please select patient appointment date', trigger: 'blur' },
        ],
      },
      isProcessing: false,
      pdfUrl: '',
      showAppointmentModal: false,
      savingAppointment: false,
      selectedPatientName: '',
      value: '',
      total: 0,
      loading: true,
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        role: '',
        date: moment().format('YYYY-MM-DD'),
        isdone: false,
        state: 0, // meaning not done
      },
      filter: {
        keyword: '',
      },
      pickerOptions: {
        disabledDate(date) {
          // Disable dates before today
          return date && date < new Date(new Date().toDateString());
        },
      },
      patients: [],
      rawPatients: [],
      reorder: {
        data: [],
      },
    };
  },
  computed: {
    totalFinalFee() {
      return this.patients.reduce((sum, patient) => {
        const fee = parseFloat(patient.fee) || 0;
        return sum + fee;
      }, 0);
    },
    duesBreakdown() {
      const map = {};
      let totalGross = 0;
      let totalDiscount = 0;
      this.patients.forEach((patient) => {
        totalDiscount += parseFloat(patient.discount) || 0;
        (patient.services || []).forEach((service) => {
          const name = service.service || 'Other';
          const fee = parseFloat(service.fee) || 0;
          map[name] = (map[name] || 0) + fee;
          totalGross += fee;
        });
      });
      const items = Object.keys(map)
        .sort()
        .map((name) => ({ name, amount: map[name] }));
      return {
        items,
        totalGross,
        totalDiscount,
        totalFinal: totalGross - totalDiscount,
      };
    },
  },
  watch: {
    'form.patient'(value) {
      if (!value || value !== this.selectedPatientName) {
        this.form.pid = null;
      }
    },
  },
  created() {
    this.getPatients();
  },
  mounted() {
    Echo.channel('patients')
      .listen('NewAppointments', (e) => {
        console.log('Echo here');
        this.getPatients();
      });
  },
  methods: {
    formatCurrency(value) {
      const amount = parseFloat(value) || 0;
      return '₱' + amount.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    },
    rowClassName(row, rowIndex) {
      console.log(row);
      if (row.row.isEdit) {
        console.log('1111');
        return 'mmmnn';
      }
      return 'success-row';
    },
    onRowDragEnd(newOrder) {
      // Handle the new order of rows
      console.log('New row order:', newOrder);
    },
    onMove(evt, originEvt, { dragged, related }) {
      console.log(evt);
      if (dragged.level !== 2) {
        return false;
      }

      if (dragged.parent === related.parent) {
        return true;
      }

      return false;
    },
    onDragChange(event) {
      this.reorder.data = event.list;
      this.reorderList(this.reorder);
    },
    async getPatients(){
      this.loading = true;
      this.patients = [];
      const { data, meta } = await Patients.apt_list({
        params: this.query,
      });
      this.patients = data;
      this.rawPatients = data;
      const raw_data = data;
      let any_active = false;
      const data2 = [];
      let crk = this.currentRowKey;
      // data.forEach(element => {
      //  console.log(this.rawPatients)
      this.rawPatients.forEach(function(element, i) {
        if (element.isactive == 1){
          const row = raw_data[i];
          crk = row.id;
          any_active = true;
          // console.log(i,element.isactive)
        }
        data2.push(element);
        console.log(element);
      });
      // console.log(data2)
      this.patients = [];
      this.patients = data2;
      this.total = meta.total;
      this.loading = false;
      this.anyActive = any_active;
    },
    handleFilter() {
      this.query.page = 1;
      this.query.date = moment(this.query.date).tz('Asia/Manila').format('YYYY-MM-DD');
      this.getPatients();
    },
    handleCreate() {
      this.showAppointmentModal = true;
      this.resetAppointmentForm();
    },
    resetAppointmentForm() {
      this.selectedPatientName = '';
      this.form = {
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
        nurse_remarks: null,
      };
      this.$nextTick(() => {
        if (this.$refs.appForm) {
          this.$refs.appForm.clearValidate();
        }
      });
    },
    closeAppointmentModal() {
      this.showAppointmentModal = false;
      this.resetAppointmentForm();
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
      this.selectedPatientName = ev.patientname;
      this.form.patient = ev.patientname;
      this.form.pid = ev.pid;
      this.$nextTick(() => {
        if (this.$refs.appForm) {
          this.$refs.appForm.validateField('patient');
        }
      });
    },
    submitForm() {
      this.$refs['form'].validate((valid) => {
        if (valid) {
          alert('Submit!');
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    saveAppointment() {
      this.$refs['appForm'].validate((valid) => {
        if (valid) {
          this.savingAppointment = true;
          this.form.apt_dt = moment(this.form.apt_dt).tz('Asia/Manila').format('YYYY-MM-DD');
          Patients.addAppointment(this.form).then((response) => {
            this.$message({
              message: 'Appointment has been created successfully.',
              type: 'success',
              duration: 5 * 1000,
            });
            this.showAppointmentModal = false;
            this.getPatients();
          })
            .catch((err) => {
              console.error('Error adding appointment:', err);
              this.$message.error('Failed to save appointment. Please try again.');
            })
            .finally(() => {
              this.savingAppointment = false;
            });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    selectRow(index, row) {
      this.$router.push({ path: '/appointments/form/' + row.id });
    },
    printPrescription(row) {
      window.open('/api/printpdf2/' + row.id);
    },
    printFees(row) {
      window.open('/api/printfees/' + row.id);
    },
    imgSrc(profile, id, type) {
      try {
        if (type === 0){
          return profile;
        } else {
          return `public/photos/${id}.jpg`;
        }
      } catch (e) {
        return `public/photos/${id}.jpg`;
      }
      // return file;
    },
    imageLoadError(id) {
      this.imgSrc(id, 'png');
    },
    reorderList(arr){
      Patients.reorder(arr).then((response) => {
        /* this.patients=[]
            this.patients=response.data
            console.log(response.data) */
        this.getPatients();
      })
        .catch((err) => {
          console.error('Error adding suggestions:', err);
        })
        .finally(() => {
          // This will always run, regardless of the request outcome
          this.isProcessing = false;
        });
    },
    changeSelect(event){
      /* if(event==0){
        this.query.isdone = 0;
      }else if(event==1){
        this.query.isdone = 1;
      }else{
        this.query.isdone = 1;
      } */
      if (event == 0){
        this.query.state = 0;
      } else if (event == 1){
        this.query.state = 1;
      } else {
        this.query.state = 2;
      }
      this.getPatients();
    },
    cancelAppointment(index, row) {
      this.cancelForm = true;
      this.cancel_submitForm.id = row.id;
      this.$nextTick(() => {
        this.$refs['cancelForm'].clearValidate();
      });
    },
    confirmCancel(){
      this.$refs['cancelForm'].validate((valid) => {
        if (valid) {
          this.isProcessing = true;
          Patients.cancel_appointment(this.cancel_submitForm).then((response) => {
            this.cancelForm = false;
            this.$message({
              message: 'Appointment Cancelled',
              type: 'success',
              duration: 5 * 1000,
            });
            this.getPatients();
          })
            .catch((err) => {
              console.error('Error adding suggestions:', err);
            })
            .finally(() => {
              // This will always run, regardless of the request outcome
              this.isProcessing = false;
            });
          // }, 5000);
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    setActive(index, selected_row) {
      if (!this.anyActive){
        const row = this.patients[index];
        this.currentRowKey = row.id;

        Patients.activerow(selected_row.id).then((response) => {
          this.$message({
            message: 'Appointment has been set active successfully.',
            type: 'success',
            duration: 5 * 1000,
          });
          this.getPatients();
        })
          .catch((err) => {
            console.error('Error adding suggestions:', err);
          })
          .finally(() => {
            this.isProcessing = false;
          });
      } else {
        this.getPatients();
        this.$message({
          message: 'There are still active, cancelled or done it first.',
          type: 'warning',
          duration: 5 * 1000,
        });
      }
    },
    handleClear() {
      this.query.date = moment().format('YYYY-MM-DD');
    },
  },
};
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
.appointments-page {
  .appointments-filters {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding-bottom: 16px;
  }

  .filter-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
  }

  .filter-keyword {
    flex: 1 1 180px;
    min-width: 140px;
    max-width: 280px;
  }

  .filter-date {
    flex: 0 1 auto;
  }

  .filter-status {
    min-width: 160px;
    max-width: 240px;
  }

}

.appointments-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 16px;
  min-height: 120px;
}

.appointments-empty {
  grid-column: 1 / -1;
  text-align: center;
  padding: 48px 20px;
  color: #909399;

  .appointments-empty-icon {
    font-size: 48px;
    margin-bottom: 12px;
    display: block;
    color: #c0c4cc;
  }

  p {
    margin: 0;
    font-size: 15px;
  }
}

.appointment-card {
  background: #fff;
  border: 1px solid #e4e7ed;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  transition: box-shadow 0.2s ease, transform 0.2s ease;
  display: flex;
  flex-direction: column;

  &:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
  }

  &--active {
    border-color: #409eff;
    box-shadow: 0 0 0 1px #409eff, 0 4px 12px rgba(64, 158, 255, 0.15);
  }
}

.appointment-card-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border-bottom: 1px solid #ebeef5;
}

.appointment-card-seq {
  font-size: 12px;
  font-weight: 600;
  color: #909399;
  flex-shrink: 0;
}

.appointment-card-photo {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  flex-shrink: 0;
  border: 2px solid #fff;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}

.appointment-card-title {
  flex: 1;
  min-width: 0;
}

.appointment-card-name {
  margin: 0 0 4px;
  font-size: 16px;
  font-weight: 600;
  color: #303133;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.appointment-card-date {
  margin: 0;
  font-size: 13px;
  color: #606266;
}

.appointment-card-body {
  padding: 14px 16px;
  flex: 1;
}

.appointment-card-field {
  margin-bottom: 10px;

  .field-label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #909399;
    margin-bottom: 2px;
  }

  .field-value {
    font-size: 13px;
    color: #606266;
    line-height: 1.4;
    word-break: break-word;
  }
}

.appointment-card-meta {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
  margin-bottom: 10px;
}

.meta-item {
  .meta-label {
    display: block;
    font-size: 11px;
    color: #909399;
    margin-bottom: 2px;
  }

  .meta-value {
    font-size: 13px;
    font-weight: 500;
    color: #303133;
  }
}

.appointment-card-fee {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 10px;
  border-top: 1px dashed #ebeef5;

  .field-label {
    font-size: 13px;
    font-weight: 600;
    color: #606266;
  }
}

.appointment-card-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  padding: 12px 16px;
  border-top: 1px solid #ebeef5;
  background: #fafbfc;

  .el-button {
    flex: 1 1 auto;
    min-width: 0;
    margin: 0;
  }
}

@media (max-width: 768px) {
  .appointments-page {
    .filter-keyword {
      max-width: none;
      flex: 1 1 100%;
    }

    .filter-status {
      max-width: none;
      width: 100%;
    }
  }

  .appointments-grid {
    grid-template-columns: 1fr;
  }

  .appointment-card-actions .el-button {
    flex: 1 1 calc(50% - 4px);
  }
}

.fee-preview-btn {
  color: #409eff;
  font-weight: 600;
  padding: 0;
}
.fee-preview-btn:hover {
  color: #66b1ff;
}
.dues-popover-title {
  font-weight: 600;
  margin: 0 0 8px;
  color: #303133;
}
.dues-line {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  padding: 4px 0;
  font-size: 13px;
}
.dues-subtotal {
  border-top: 1px dashed #dcdfe6;
  margin-top: 4px;
  padding-top: 8px;
  font-weight: 500;
}
.dues-discount {
  color: #e6a23c;
}
.dues-final {
  border-top: 1px solid #dcdfe6;
  margin-top: 4px;
  padding-top: 8px;
  font-weight: 700;
  color: #409eff;
}
.dues-empty {
  margin: 0 0 8px;
  color: #909399;
  font-size: 13px;
}
.dues-summary {
  margin-top: 20px;
  margin-bottom: 10px;
  padding: 16px 20px;
  background: #f5f7fa;
  border-radius: 6px;
  border: 1px solid #ebeef5;
}
.dues-summary-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 16px;
  font-weight: bold;
}
.dues-summary-total {
  color: #409eff;
  font-size: 18px;
}
.dues-summary-body {
  margin-top: 14px;
  padding-top: 14px;
  border-top: 1px solid #dcdfe6;
}
.dues-summary-label {
  margin: 0 0 8px;
  font-size: 13px;
  color: #606266;
}

// Appointment modal (dialog is appended to body)
::v-deep .appointment-modal {
  border-radius: 16px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  overflow: hidden;

  .el-dialog__header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px 24px;

    .el-dialog__title {
      font-size: 1.3rem;
      font-weight: 600;
      color: white;
    }

    .el-dialog__headerbtn .el-dialog__close {
      color: white;
      font-size: 20px;

      &:hover {
        color: rgba(255, 255, 255, 0.8);
      }
    }
  }

  .el-dialog__body {
    padding: 24px;
    background: #fafbfc;
    max-height: 70vh;
    overflow-x: hidden;
    overflow-y: auto;
  }

  .el-dialog__footer {
    padding: 16px 24px;
    background: white;
    border-top: 1px solid #e2e8f0;
  }
}

.appointment-form {
  width: 100%;

  ::v-deep .el-form-item {
    margin-bottom: 16px;
  }

  ::v-deep .el-form-item__label {
    font-weight: 600;
    color: #2c3e50;
    font-size: 14px;
    line-height: 1.4;
    padding-bottom: 4px;
  }

  ::v-deep .el-input,
  ::v-deep .el-textarea,
  ::v-deep .el-autocomplete {
    width: 100%;
  }

  ::v-deep .appointment-field-full {
    width: 100%;
  }

  ::v-deep .appointment-field-full .el-input {
    width: 100%;
  }

  ::v-deep .el-date-editor.el-input {
    width: 100%;
  }

  ::v-deep .el-input__inner,
  ::v-deep .el-textarea__inner {
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;

    &:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
  }

  ::v-deep .el-textarea__inner {
    resize: vertical;
    min-height: 80px;
  }
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  flex-wrap: wrap;

  .el-button {
    padding: 10px 24px;
    border-radius: 8px;
    font-weight: 600;
  }
}

@media (max-width: 768px) {
  ::v-deep .appointment-modal {
    width: 95% !important;
    margin: 20px auto !important;

    .el-dialog__body {
      padding: 16px;
      max-height: 75vh;
    }

    .el-dialog__footer {
      padding: 12px 16px;
    }
  }
}
</style>
