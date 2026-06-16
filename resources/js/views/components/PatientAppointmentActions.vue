<template>
  <div class="patient-appointment-actions">
    <el-dropdown trigger="click" @command="handleAppointmentOption">
      <el-button type="success" icon="el-icon-plus" :loading="isProcessing">
        Set appointment
        <i class="el-icon-arrow-down el-icon--right" />
      </el-button>
      <el-dropdown-menu slot="dropdown">
        <el-dropdown-item command="today">
          Today — add appointment now
        </el-dropdown-item>
        <el-dropdown-item command="future">
          Future date — schedule appointment
        </el-dropdown-item>
      </el-dropdown-menu>
    </el-dropdown>

    <el-dialog
      title="Schedule future appointment"
      width="92%"
      custom-class="ua-appointment-dialog"
      :visible.sync="appointmentDialogVisible"
      :close-on-click-modal="false"
      :close-on-press-escape="false"
      append-to-body
    >
      <div class="ua-dialog-body">
        <el-form
          ref="appForm"
          :model="form_appointment"
          :rules="appointmentRules"
          label-position="top"
          class="ua-form ua-form--dialog"
        >
          <el-row :gutter="20">
            <el-col :xs="24" :sm="12">
              <el-form-item label="Date" prop="apt_dt">
                <el-date-picker
                  v-model="form_appointment.apt_dt"
                  type="date"
                  :picker-options="futureDatePickerOptions"
                  placeholder="Pick a future date"
                  class="ua-date"
                />
              </el-form-item>
            </el-col>
            <el-col :xs="12" :sm="6">
              <el-form-item label="Systolic" prop="vit_sys">
                <el-input v-model="form_appointment.vit_sys" />
              </el-form-item>
            </el-col>
            <el-col :xs="12" :sm="6">
              <el-form-item label="Diastolic" prop="vit_dia">
                <el-input v-model="form_appointment.vit_dia" />
              </el-form-item>
            </el-col>
            <el-col :xs="12" :sm="8">
              <el-form-item label="HR">
                <el-input v-model="form_appointment.hr" clearable placeholder="Heart rate" />
              </el-form-item>
            </el-col>
            <el-col :xs="12" :sm="8">
              <el-form-item label="RR">
                <el-input
                  v-model="form_appointment.rr"
                  clearable
                  placeholder="Respiratory rate"
                />
              </el-form-item>
            </el-col>
            <el-col :xs="12" :sm="8">
              <el-form-item label="Weight" prop="weight">
                <el-input v-model="form_appointment.weight" />
              </el-form-item>
            </el-col>
            <el-col :xs="12" :sm="8">
              <el-form-item label="Height" prop="height">
                <el-input v-model="form_appointment.height" />
              </el-form-item>
            </el-col>
            <el-col :xs="12" :sm="8">
              <el-form-item label="Cardiac rate">
                <el-input v-model="form_appointment.vit_cr" clearable />
              </el-form-item>
            </el-col>
            <el-col :xs="12" :sm="8">
              <el-form-item label="Respiratory rate (vitals)">
                <el-input v-model="form_appointment.vit_rr" clearable />
              </el-form-item>
            </el-col>
            <el-col :xs="12" :sm="8">
              <el-form-item label="O2 stat">
                <el-input v-model="form_appointment.o2_stat" clearable />
              </el-form-item>
            </el-col>
            <el-col :xs="12" :sm="8">
              <el-form-item label="Temperature" prop="vit_temp">
                <el-input v-model="form_appointment.vit_temp" />
              </el-form-item>
            </el-col>
            <el-col :span="24">
              <el-form-item label="Remarks" prop="complaints">
                <el-input
                  v-model="form_appointment.nurse_remarks"
                  :autosize="{ minRows: 3, maxRows: 8 }"
                  type="textarea"
                  placeholder="Nurse remarks or notes"
                />
              </el-form-item>
            </el-col>
          </el-row>
        </el-form>
      </div>
      <span slot="footer" class="dialog-footer ua-dialog-footer">
        <el-button @click="appointmentDialogVisible = false">
          {{ $t('table.cancel') }}
        </el-button>
        <el-button type="primary" :loading="isProcessing" @click="addAppointment">
          {{ $t('table.confirm') }}
        </el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
import Patients from '@/api/patients';
import moment from 'moment-timezone';

export default {
  name: 'PatientAppointmentActions',
  props: {
    patientId: {
      type: [String, Number],
      required: true,
    },
  },
  data() {
    return {
      isProcessing: false,
      appointmentDialogVisible: false,
      form_appointment: this.buildEmptyForm(),
      appointmentRules: {
        apt_dt: [
          { required: true, message: 'Please pick a future date', trigger: 'change' },
        ],
      },
      futureDatePickerOptions: {
        disabledDate(date) {
          const today = new Date();
          today.setHours(0, 0, 0, 0);
          return date.getTime() <= today.getTime();
        },
      },
    };
  },
  methods: {
    buildEmptyForm() {
      return {
        complaints: '',
        cancel_reason: '',
        pid: this.patientId,
        apt_dt: null,
        patient: '',
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
        o2_stat: null,
        nurse_remarks: null,
      };
    },
    handleAppointmentOption(command) {
      if (command === 'today') {
        this.addAppointmentToday();
      } else if (command === 'future') {
        this.openFutureDialog();
      }
    },
    openFutureDialog() {
      this.form_appointment.apt_dt = null;
      this.appointmentDialogVisible = true;
      this.$nextTick(() => {
        if (this.$refs.appForm) {
          this.$refs.appForm.clearValidate();
        }
      });
    },
    resetAppointmentForm() {
      this.form_appointment = this.buildEmptyForm();
    },
    addAppointmentToday() {
      if (this.isProcessing) {
        return;
      }
      this.isProcessing = true;
      const payload = {
        pid: this.patientId,
        apt_dt: moment().tz('Asia/Manila').format('YYYY-MM-DD'),
      };
      Patients.addAppointment(payload)
        .then((appointment) => {
          this.$message({
            message: 'Appointment has been created successfully.',
            type: 'success',
            duration: 5 * 1000,
          });
          if (appointment && appointment.id) {
            this.$router.push({ path: '/appointments/form/' + appointment.id });
          } else {
            this.$emit('created');
          }
        })
        .catch((err) => {
          console.error('Error adding appointment:', err);
          this.$message.error('Failed to save appointment. Please try again.');
        })
        .finally(() => {
          this.isProcessing = false;
        });
    },
    addAppointment() {
      this.$refs.appForm.validate((valid) => {
        if (!valid) {
          return false;
        }
        this.isProcessing = true;
        this.form_appointment.apt_dt = moment(this.form_appointment.apt_dt)
          .tz('Asia/Manila')
          .format('YYYY-MM-DD');
        this.form_appointment.pid = this.patientId;
        Patients.addAppointment(this.form_appointment)
          .then(() => {
            this.resetAppointmentForm();
            this.appointmentDialogVisible = false;
            this.$message({
              message: 'Appointment has been created successfully.',
              type: 'success',
              duration: 5 * 1000,
            });
            this.$emit('created');
          })
          .catch((err) => {
            console.error('Error adding appointment:', err);
            this.$message.error('Failed to save appointment. Please try again.');
          })
          .finally(() => {
            this.isProcessing = false;
          });
      });
    },
  },
};
</script>

<style scoped>
.patient-appointment-actions {
  display: inline-flex;
}
</style>
