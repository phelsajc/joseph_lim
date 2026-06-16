<template>
  <el-dialog
    title="Vitals trend"
    :visible.sync="dialogVisible"
    :width="dialogWidth"
    top="5vh"
    append-to-body
    :close-on-click-modal="false"
    @open="loadVitals"
  >
    <PatientVitalsHistoryTable
      :records="vitalsRecords"
      :vitals-by-day="vitalsByDay"
      :loading="loading"
    />
    <span slot="footer" class="dialog-footer">
      <el-button @click="dialogVisible = false">Close</el-button>
    </span>
  </el-dialog>
</template>

<script>
import Patients from '@/api/patients';
import PatientVitalsHistoryTable from '@/views/components/PatientVitalsHistoryTable.vue';

export default {
  name: 'PatientVitalsHistoryDialog',
  components: { PatientVitalsHistoryTable },
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
      loading: false,
      vitalsRecords: [],
      vitalsByDay: {},
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
    dialogWidth() {
      if (typeof window !== 'undefined' && window.innerWidth < 768) {
        return '95%';
      }
      return '900px';
    },
  },
  methods: {
    async loadVitals() {
      if (!this.patientId) {
        return;
      }

      this.loading = true;
      try {
        const response = await Patients.getPatientVitalsHistory(this.patientId);
        this.vitalsRecords = (response && response.vitals_data) || [];
        this.vitalsByDay = (response && response.vitals_by_day) || {};
      } catch (err) {
        console.error('Error loading vitals history:', err);
        this.$message.error('Could not load vitals history.');
        this.vitalsRecords = [];
        this.vitalsByDay = {};
      } finally {
        this.loading = false;
      }
    },
    reload() {
      return this.loadVitals();
    },
  },
};
</script>
