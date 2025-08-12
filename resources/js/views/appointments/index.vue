<template>
  <div class="app-container">
    
  <div class="filter-container">
      <el-input v-model="query.keyword" :placeholder="$t('table.keyword')" style="width: 200px;" class="filter-item" @keyup.enter.native="handleFilter" />
      <!-- <el-date-picker v-model="query.date" @clear="handleClear" type="date" style="width: 200px;" class="filter-item" placeholder="Pick a day" /> -->
      <date-picker v-model="query.date" valueType="format"></date-picker>
      <el-button class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
        {{ $t('table.search') }}
      </el-button>
      <el-button class="filter-item" style="margin-left: 10px;" type="success" icon="el-icon-plus" @click="handleCreate">
        {{ $t('table.add') }}
      </el-button>
      
      <el-select
      v-model="status"
      placeholder="Select"
      size="large"
      style="width: 240px"
      @change="changeSelect"
    >
      <el-option
        :key="1"
        label="Current"
        :value="0"
      />
      <el-option
        :key="2"
        label="Completed"
        :value="1"
      />
      <el-option
        :key="3"
        label="Cancelled"
        :value="2"
      />
    </el-select>
      <!-- <el-button v-role="['doctor']" type="danger" size="small" icon="el-icon-delete">
        Delete
      </el-button> -->
    </div>
      <el-table ref="maintbl" v-loading="loading" :data="patients" border fit highlight-current-row style="width: 100%" :current-row-key="currentRowKey">
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
              @error="imageLoadError(scope.row.patientid)"
              :zoom-rate="1.2"
              :max-scale="7"
              :min-scale="0.2"
              :initial-index="4"
              fit="cover"
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
      <el-table-column align="center" label="Cancelled Reason" v-if="status==2">
        <template slot-scope="scope">
          <div class="handle"><span>{{ scope.row.cancel_reason }}</span></div>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Date">
        <template slot-scope="scope">
          <div class="handle"><span>{{ scope.row.apt_dt }}</span></div>
        </template>
      </el-table-column>
      
    <el-table-column align="center" label="Actions">      
      <template #default="scope">
        <el-button type="primary" size="small" @click="selectRow(scope.$index, scope.row)">
          View
        </el-button>
        <el-button type="danger" size="small" @click="cancelAppointment(scope.$index, scope.row)">
          Cancel
        </el-button>
        <!-- <el-button type="success" size="small" @click="setActive(scope.$index, scope.row)">
          Set Active
        </el-button> -->
      </template>
    </el-table-column>
      </el-table>
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
    
    <el-dialog :title="'Add Appointment'" :visible.sync="dialogFormVisible" :close-on-click-modal="false" :close-on-press-escape="false">
      <div class="form-container">
        <el-form ref="appForm" :model="form" :rules="rules" label-position="left" label-width="150px" style="max-width: 500px;">
          <el-form-item :label="'Patient'" prop="patient">
            <el-autocomplete
              v-model="form.patient"
              :fetch-suggestions="querySearch"
              popper-class="my-autocomplete"
              placeholder="Please input"
              @select="handleSelect"
              style="width: 150%;"
            >
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
         <!--  <el-form-item :label="'Complaints'" prop="complaints">
            <el-input v-model="form.complaints" style="width: 150%;"/>
          </el-form-item> -->
          <el-form-item :label="'Date'" prop="apt_dt">
            <el-date-picker v-model="form.apt_dt" type="date" :picker-options="pickerOptions" placeholder="Pick a day" style="width: 40%;"/>
            <!-- <date-picker v-model="form.apt_dt" valueType="format" placeholder="Year-Month-Date"></date-picker> -->
          </el-form-item>
          <el-form-item :label="'Systolic'" prop="vit_sys">
            <el-input v-model="form.vit_sys" style="width: 23%;"/>
          </el-form-item>
          <el-form-item :label="'Diastolic'" prop="vit_dia">
            <el-input v-model="form.vit_dia" style="width: 23%;"/>
          </el-form-item>
          <el-form-item :label="'Weight'" prop="weight">
            <el-input v-model="form.weight" style="width: 23%;"/>
          </el-form-item>
          <el-form-item :label="'Height'" prop="height">
            <el-input v-model="form.height" style="width: 23%;"/>
          </el-form-item>
          <el-form-item :label="'Temperature'" prop="vit_temp">
            <el-input v-model="form.vit_temp" style="width: 23%;"/>
          </el-form-item>
          <el-form-item :label="'CR'" prop="vit_cr">
            <el-input v-model="form.vit_cr" style="width: 23%;"/>
          </el-form-item>
          <el-form-item :label="'RR'" prop="vit_rr">
            <el-input v-model="form.vit_rr" style="width: 23%;"/>
          </el-form-item>
          <el-form-item :label="'Remarks'" prop="remarks">
            <el-input v-model="form.nurse_remarks" :autosize="{ minRows: 2, maxRows: 4 }" style="width: 540px" :rows="2" type="textarea" placeholder="Please input" />
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">
            {{ $t('table.cancel') }}
          </el-button>
          <el-button type="primary" :loading="isProcessing" @click="addAppointment()">
            {{ $t('table.confirm') }}
          </el-button>
        </div>
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
  components: { Pagination, draggable,ElTableDraggable, DatePicker},
  directives: { role },
  name: 'Patients',
  data(){
    return {
      anyActive: false,
      currentRowKey: null,
      cancel_rules: {
        cancel_reason: [
          { required: true, message: 'Please provide reason', trigger: 'blur' }
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
        vit_temp: null,
        vit_cr: null,
        vit_rr: null,
      },
      rules: {
        complaints: [
          { required: true, message: 'Please input your complaints', trigger: 'blur' }
        ],
        patient: [
          { required: true, message: 'Please select patient', trigger: 'blur' }
        ],
        apt_dt: [
          { required: true, message: 'Please select patient appointment date', trigger: 'blur' }
        ],
      },
      isProcessing: false,
      pdfUrl: '',
      dialogFormVisible: false,
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
        state: 0, //meaning not done
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
        data: []
      },
    };
  },
  created() {
    this.getPatients();
  },
  mounted() {
    Echo.channel('patients')
      .listen('NewAppointments', (e) => {
        console.log('Echo here')
        this.getPatients();
      });
  },
  methods: {
    rowClassName(row, rowIndex) {
      console.log(row);
      if (row.row.isEdit) {
        console.log("1111");
        return "mmmnn";
      }
      return "success-row";
    },
    onRowDragEnd(newOrder) {
      // Handle the new order of rows
      console.log('New row order:', newOrder);
    },
      onMove(evt, originEvt, { dragged, related }) {
        console.log(evt)
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
      let raw_data = data
      let any_active = false
      let data2 = []
      let crk = this.currentRowKey
      let refs_maintbl = this.$refs.maintbl
      //data.forEach(element => {
        //  console.log(this.rawPatients)
        this.rawPatients.forEach(function (element, i) {
        if(element.isactive==1){
          let row = raw_data[i];
          crk = row.id;
          refs_maintbl.setCurrentRow(row);
          any_active = true
          //console.log(i,element.isactive)
        }
        data2.push(element);
        console.log(element)
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
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['appForm'].clearValidate();
      });
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
    handleSelect(ev){
      //this.value = ev.patientname;
      this.form.patient = ev.patientname;
      this.form.pid = ev.pid;
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
    addAppointment(){
      this.$refs['appForm'].validate((valid) => {
        if (valid) {
          this.isProcessing = true;
          this.form.apt_dt = moment(this.form.apt_dt).tz('Asia/Manila').format('YYYY-MM-DD');
          Patients.addAppointment(this.form).then((response) => {
            this.form.complaints = '';
            this.form.pid = null;
            this.form.apt_dt = null;
            this.form.patient = null;
            this.form.remakrs = null;
            this.getPatients();
            this.dialogFormVisible = false;
            this.$message({
              message: 'Appointment has been created successfully.',
              type: 'success',
              duration: 5 * 1000,
            });
          })
            .catch((err) => {
              console.error('Error adding suggestions:', err);
            })
            .finally(() => {
              // This will always run, regardless of the request outcome
              this.isProcessing = false;
            });
          //}, 5000);
        }else{
          console.log('error submit!!');
          return false;
        }
      });
    },
    selectRow(index,row) {
      this.$router.push({ path: '/appointments/form/' + row.id });
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
      if(event==0){
        this.query.state = 0;
      }else if(event==1){
        this.query.state = 1;
      }else{
        this.query.state = 2;
      }
    this.getPatients();
    },
    cancelAppointment(index,row) {
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
          //}, 5000);
        }else{
          console.log('error submit!!');
          return false;
        }
      });
    },
    setActive(index,selected_row) {
      if(!this.anyActive){
        const row = this.patients[index];
        this.currentRowKey = row.id;
        this.$refs.maintbl.setCurrentRow(row);
      
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
      }else{
        this.getPatients();
        this.$message({
          message: 'There are still active, cancelled or done it first.',
          type: 'warning',
          duration: 5 * 1000,
        });
      }
    },
    handleClear() {
      this.query.date = moment().format('YYYY-MM-DD')
    }
  },
};
</script>
