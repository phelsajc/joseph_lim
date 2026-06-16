<template>
  <el-dialog
    title="Record vitals"
    :visible.sync="dialogVisible"
    width="92%"
    custom-class="ua-appointment-dialog"
    append-to-body
    :close-on-click-modal="false"
    :close-on-press-escape="false"
    @open="loadPreviousVitals"
    @closed="resetForm"
  >
    <div v-loading="loadingPrevious" class="ua-dialog-body">
      <p v-if="previousMeta" class="vitals-prefill-banner">
        Prefilled from last reading on <strong>{{ previousMeta.date }}</strong>
        at <strong>{{ previousMeta.time_display }}</strong>.
        Fields you change will be highlighted.
      </p>
      <el-form label-position="top" class="ua-form ua-form--dialog">
        <el-row :gutter="20">
          <el-col :xs="12" :sm="6">
            <el-form-item label="Systolic">
              <el-input
                v-model="form.vit_sys"
                clearable
                :class="{ 'vitals-field--changed': isFieldChanged('vit_sys') }"
              />
            </el-form-item>
          </el-col>
          <el-col :xs="12" :sm="6">
            <el-form-item label="Diastolic">
              <el-input
                v-model="form.vit_dia"
                clearable
                :class="{ 'vitals-field--changed': isFieldChanged('vit_dia') }"
              />
            </el-form-item>
          </el-col>
          <el-col :xs="12" :sm="8">
            <el-form-item label="Weight (kg)">
              <el-input
                v-model="form.weight"
                clearable
                :class="{ 'vitals-field--changed': isFieldChanged('weight') }"
                @input="calculateBMI"
              />
            </el-form-item>
          </el-col>
          <el-col :xs="12" :sm="8">
            <el-form-item label="Height (cm)">
              <el-input
                v-model="form.height"
                clearable
                :class="{ 'vitals-field--changed': isFieldChanged('height') }"
                @input="calculateBMI"
              />
            </el-form-item>
          </el-col>
          <el-col :xs="12" :sm="8">
            <el-form-item label="BMI">
              <el-input
                v-model="form.bmi"
                clearable
                :class="{ 'vitals-field--changed': isFieldChanged('bmi') }"
              />
            </el-form-item>
          </el-col>
          <el-col :xs="12" :sm="8">
            <el-form-item label="Temperature (°C)">
              <el-input
                v-model="form.vit_temp"
                clearable
                :class="{ 'vitals-field--changed': isFieldChanged('vit_temp') }"
              />
            </el-form-item>
          </el-col>
          <el-col :xs="12" :sm="8">
            <el-form-item label="Heart rate (bpm)">
              <el-input
                v-model="form.vit_cr"
                clearable
                :class="{ 'vitals-field--changed': isFieldChanged('vit_cr') }"
              />
            </el-form-item>
          </el-col>
          <el-col :xs="12" :sm="8">
            <el-form-item label="Respiratory rate (rpm)">
              <el-input
                v-model="form.vit_rr"
                clearable
                :class="{ 'vitals-field--changed': isFieldChanged('vit_rr') }"
              />
            </el-form-item>
          </el-col>
          <el-col :xs="12" :sm="8">
            <el-form-item label="O2 sat (%)">
              <el-input
                v-model="form.o2_stat"
                clearable
                :class="{ 'vitals-field--changed': isFieldChanged('o2_stat') }"
              />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
    </div>
    <span slot="footer" class="dialog-footer ua-dialog-footer">
      <el-button @click="dialogVisible = false">
        Cancel
      </el-button>
      <el-button type="primary" :loading="saving" @click="saveVitals">
        Save vitals
      </el-button>
    </span>
  </el-dialog>
</template>

<script>
import Patients from '@/api/patients';

const VITAL_FIELD_KEYS = [
  'vit_sys', 'vit_dia', 'weight', 'height', 'bmi',
  'vit_temp', 'vit_cr', 'vit_rr', 'o2_stat',
];

export default {
  name: 'PatientRecordVitalsDialog',
  props: {
    visible: {
      type: Boolean,
      default: false,
    },
    patientId: {
      type: [String, Number],
      required: true,
    },
  },
  data() {
    return {
      saving: false,
      loadingPrevious: false,
      form: this.buildEmptyForm(),
      previousVitals: null,
      previousMeta: null,
    };
  },
  computed: {
    dialogVisible: {
      get() {
        return this.visible;
      },
      set(value) {
        this.$emit('update:visible', value);
      },
    },
  },
  methods: {
    buildEmptyForm() {
      const form = {};
      VITAL_FIELD_KEYS.forEach((key) => {
        form[key] = null;
      });
      return form;
    },
    buildFormFromReading(reading) {
      const form = {};
      VITAL_FIELD_KEYS.forEach((key) => {
        form[key] = reading[key] ?? null;
      });
      return form;
    },
    normalizeVitalValue(value) {
      if (value === null || value === undefined) {
        return '';
      }
      return String(value).trim();
    },
    isFieldChanged(key) {
      if (!this.previousVitals) {
        return false;
      }
      return this.normalizeVitalValue(this.form[key])
        !== this.normalizeVitalValue(this.previousVitals[key]);
    },
    getLatestReading(vitalsByDay) {
      const allReadings = [];
      Object.values(vitalsByDay || {}).forEach((dayReadings) => {
        allReadings.push(...dayReadings);
      });
      if (!allReadings.length) {
        return null;
      }
      allReadings.sort((a, b) => {
        return new Date(b.recorded_at) - new Date(a.recorded_at);
      });
      return allReadings[0];
    },
    async loadPreviousVitals() {
      if (!this.patientId) {
        return;
      }

      this.loadingPrevious = true;
      this.previousVitals = null;
      this.previousMeta = null;
      this.form = this.buildEmptyForm();

      try {
        const response = await Patients.getPatientVitalsHistory(this.patientId);
        const latest = this.getLatestReading(response && response.vitals_by_day);

        if (latest) {
          this.form = this.buildFormFromReading(latest);
          this.previousVitals = { ...this.form };
          this.previousMeta = {
            date: latest.date || '—',
            time_display: latest.time_display || '—',
          };
        }
      } catch (err) {
        console.error('Error loading previous vitals:', err);
        this.$message.error('Could not load previous vitals.');
      } finally {
        this.loadingPrevious = false;
      }
    },
    resetForm() {
      this.form = this.buildEmptyForm();
      this.previousVitals = null;
      this.previousMeta = null;
      this.loadingPrevious = false;
    },
    calculateBMI() {
      const weight = parseFloat(this.form.weight);
      const height = parseFloat(this.form.height);
      if (weight && height) {
        const heightInMeters = height / 100;
        this.form.bmi = (weight / heightInMeters ** 2).toFixed(2);
      } else {
        this.form.bmi = null;
      }
    },
    hasVitals() {
      return Object.values(this.form).some((value) => {
        return value !== null && String(value).trim() !== '';
      });
    },
    async saveVitals() {
      if (!this.hasVitals()) {
        this.$message.warning('Enter at least one vital sign.');
        return;
      }

      this.saving = true;
      try {
        const response = await Patients.recordPatientVitals({
          patientid: this.patientId,
          ...this.form,
        });

        const linked = response && response.linked_appointment_id;
        this.$message({
          message: linked
            ? "Vitals saved and linked to today's visit."
            : 'Vitals saved as standalone reading.',
          type: 'success',
          duration: 5 * 1000,
        });
        this.dialogVisible = false;
        this.$emit('saved', response);
      } catch (err) {
        console.error('Error saving vitals:', err);
        const message = (err && err.response && err.response.data && err.response.data.message)
          || 'Failed to save vitals. Please try again.';
        this.$message.error(message);
      } finally {
        this.saving = false;
      }
    },
  },
};
</script>

<style scoped>
.vitals-prefill-banner {
  margin: 0 0 16px;
  padding: 10px 14px;
  font-size: 13px;
  line-height: 1.5;
  color: #606266;
  background: #f0f7ff;
  border: 1px solid #d9ecff;
  border-radius: 6px;
}

.vitals-field--changed ::v-deep .el-input__inner {
  background-color: #fffbe6;
  border-left: 3px solid #e6a23c;
}
</style>
