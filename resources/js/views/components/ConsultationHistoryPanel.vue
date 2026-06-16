<template>
  <section
    class="consultation-history-panel"
    aria-labelledby="consultation-history-panel-title"
  >
    <header class="consultation-history-panel__header">
      <div class="consultation-history-panel__title-row">
        <h3 id="consultation-history-panel-title" class="consultation-history-panel__title">
          Past consultations
        </h3>
        <span v-if="records.length" class="consultation-history-panel__count">
          {{ records.length }} record{{ records.length === 1 ? '' : 's' }}
        </span>
      </div>
      <div class="consultation-history-panel__actions">
        <!-- <el-button
          v-role="['secretary', 'admin', 'doctor']"
          size="small"
          icon="el-icon-edit-outline"
          @click="recordVitalsDialogVisible = true"
        >
          Record vitals
        </el-button> -->
        <el-button
          size="small"
          icon="el-icon-data-line"
          @click="vitalsDialogVisible = true"
        >
          View vitals trend
        </el-button>
        <el-button
          size="small"
          :icon="expanded ? 'el-icon-arrow-up' : 'el-icon-arrow-down'"
          @click="toggleExpanded"
        >
          {{ expanded ? 'Hide' : 'Show' }}
        </el-button>
      </div>
    </header>

    <PatientRecordVitalsDialog
      :visible.sync="recordVitalsDialogVisible"
      :patient-id="patientId"
      @saved="onVitalsSaved"
    />

    <PatientVitalsHistoryDialog
      ref="vitalsHistoryDialog"
      :visible.sync="vitalsDialogVisible"
      :patient-id="patientId"
    />

    <div v-show="expanded" class="consultation-history-panel__body">
      <div v-loading="loading" class="consultation-history-panel__list">
        <ConsultationRecordCard
          v-for="record in records"
          :key="record.id"
          :record="record"
          :attachments="attachments"
          @select="openDetail"
        />
        <p
          v-if="!loading && !records.length"
          class="consultation-history-panel__empty"
        >
          No previous completed consultations found.
        </p>
      </div>
    </div>
  </section>
</template>

<script>
import role from '@/directive/role/index.js';
import ConsultationRecordCard from '@/views/components/ConsultationRecordCard.vue';
import PatientRecordVitalsDialog from '@/views/components/PatientRecordVitalsDialog.vue';
import PatientVitalsHistoryDialog from '@/views/components/PatientVitalsHistoryDialog.vue';

export default {
  name: 'ConsultationHistoryPanel',
  components: { ConsultationRecordCard, PatientRecordVitalsDialog, PatientVitalsHistoryDialog },
  directives: { role },
  props: {
    records: {
      type: Array,
      default: () => [],
    },
    loading: {
      type: Boolean,
      default: false,
    },
    defaultExpanded: {
      type: Boolean,
      default: true,
    },
    profileId: {
      type: [String, Number],
      required: true,
    },
    patientId: {
      type: [String, Number],
      required: true,
    },
    attachments: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      expanded: true,
      vitalsDialogVisible: false,
      recordVitalsDialogVisible: false,
    };
  },
  created() {
    const isMobile = typeof window !== 'undefined' && window.innerWidth < 768;
    this.expanded = isMobile ? false : this.defaultExpanded;
  },
  methods: {
    toggleExpanded() {
      this.expanded = !this.expanded;
    },
    openDetail(record) {
      if (!record || !record.id) {
        return;
      }
      this.$router.push({
        path: `/masterfile/profile/${this.profileId}/${this.patientId}/consultation/${record.id}`,
      });
    },
    onVitalsSaved() {
      this.refreshVitalsHistory();
    },
    refreshVitalsHistory() {
      if (this.$refs.vitalsHistoryDialog) {
        this.$refs.vitalsHistoryDialog.reload();
      }
    },
  },
};
</script>

<style scoped>
.consultation-history-panel {
  width: 100%;
  background: #fff;
  border: 1px solid #ebeef5;
  border-radius: 10px;
  margin-bottom: 20px;
  overflow: hidden;
}

.consultation-history-panel__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 16px 20px;
  border-bottom: 1px solid #ebeef5;
  background: linear-gradient(180deg, #fafbfc 0%, #fff 100%);
}

.consultation-history-panel__title-row {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  min-width: 0;
}

.consultation-history-panel__title {
  margin: 0;
  font-size: 18px;
  font-weight: 700;
  color: #303133;
}

.consultation-history-panel__count {
  font-size: 12px;
  font-weight: 600;
  color: #606266;
  background: #f0f2f5;
  padding: 2px 10px;
  border-radius: 999px;
}

.consultation-history-panel__actions {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

.consultation-history-panel__body {
  padding: 16px 20px 20px;
}

.consultation-history-panel__list {
  min-height: 60px;
}

.consultation-history-panel__empty {
  margin: 24px 0;
  text-align: center;
  color: #909399;
  font-size: 14px;
}

@media (max-width: 767px) {
  .consultation-history-panel__header {
    flex-direction: column;
    align-items: stretch;
    padding: 12px 14px;
  }

  .consultation-history-panel__actions {
    flex-direction: column;
    width: 100%;
  }

  .consultation-history-panel__actions ::v-deep .el-button {
    width: 100%;
    margin-left: 0 !important;
  }

  .consultation-history-panel__title {
    font-size: 16px;
  }

  .consultation-history-panel__body {
    padding: 12px 14px 16px;
  }
}
</style>
