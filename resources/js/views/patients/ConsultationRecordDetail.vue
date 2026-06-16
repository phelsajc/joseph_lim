<template>
  <div v-loading="loading" class="app-container consultation-record-detail">
    <header class="consultation-record-detail__header">
      <div class="consultation-record-detail__header-left">
        <el-button icon="el-icon-arrow-left" @click="goBack">
          Back to profile
        </el-button>
        <h2 class="consultation-record-detail__title">
          {{ pageTitle }}
        </h2>
      </div>
      <div class="consultation-record-detail__header-actions">
        <PatientAppointmentActions :patient-id="$route.params.pid" />
        <el-button
          v-if="record"
          type="primary"
          @click="openFullRecord"
        >
          Open full record
        </el-button>
      </div>
    </header>

    <ConsultationRecordCard
      v-if="record"
      :record="record"
      :attachments="attachments"
      readonly
    />

    <el-empty
      v-else-if="!loading"
      description="Consultation record not found."
    />
  </div>
</template>

<script>
import Patients from '@/api/patients';
import ConsultationRecordCard from '@/views/components/ConsultationRecordCard.vue';
import PatientAppointmentActions from '@/views/components/PatientAppointmentActions.vue';

export default {
  name: 'ConsultationRecordDetail',
  components: { ConsultationRecordCard, PatientAppointmentActions },
  data() {
    return {
      loading: false,
      record: null,
      attachments: [],
    };
  },
  computed: {
    pageTitle() {
      if (this.record && this.record.date_display) {
        return 'Visit Summary — ' + this.record.date_display;
      }
      return 'Visit Summary';
    },
  },
  created() {
    this.loadRecord();
  },
  methods: {
    async loadRecord() {
      const patientId = this.$route.params.pid;
      const appointmentId = this.$route.params.appointmentId;
      if (!patientId || !appointmentId) {
        return;
      }

      this.loading = true;
      try {
        const [historyResponse, attachmentsResponse] = await Promise.all([
          Patients.getPatientConsultationHistory(patientId, {
            appointment_id: appointmentId,
          }),
          Patients.getAttachments(patientId),
        ]);
        const rows = (historyResponse && historyResponse.data) || [];
        this.record = rows.length ? rows[0] : null;
        this.attachments = (attachmentsResponse && attachmentsResponse.data) || [];
      } catch (err) {
        console.error('Error loading consultation record:', err);
        this.$message.error('Could not load consultation record.');
      } finally {
        this.loading = false;
      }
    },
    goBack() {
      const { id, pid } = this.$route.params;
      this.$router.push({ path: `/masterfile/profile/${id}/${pid}` });
    },
    openFullRecord() {
      if (!this.record) {
        return;
      }
      this.$router.push({ path: '/appointments/form/' + this.record.id });
    },
  },
};
</script>

<style scoped>
.consultation-record-detail__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
  margin-bottom: 20px;
}

.consultation-record-detail__header-actions {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.consultation-record-detail__header-left {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}

.consultation-record-detail__title {
  margin: 0;
  font-size: 20px;
  font-weight: 700;
  color: #303133;
}

@media (max-width: 767px) {
  .consultation-record-detail.app-container {
    padding: 12px;
  }

  .consultation-record-detail__header {
    flex-direction: column;
    align-items: stretch;
    gap: 12px;
  }

  .consultation-record-detail__header-left {
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
  }

  .consultation-record-detail__header-actions {
    flex-direction: column;
    align-items: stretch;
    width: 100%;
  }

  .consultation-record-detail__header-actions ::v-deep .patient-appointment-actions {
    display: flex;
    width: 100%;
  }

  .consultation-record-detail__header-actions ::v-deep .el-button,
  .consultation-record-detail__header-actions ::v-deep .patient-appointment-actions .el-button {
    width: 100%;
    margin-left: 0 !important;
  }

  .consultation-record-detail__title {
    font-size: 17px;
    word-break: break-word;
  }
}
</style>
