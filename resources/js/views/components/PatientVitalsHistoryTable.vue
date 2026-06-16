<template>
  <div v-loading="loading" class="patient-vitals-history-table">
    <el-table
      v-if="records.length"
      :data="records"
      border
      stripe
      size="small"
      class="compact-table vitals-history-table"
      :default-sort="{ prop: 'date_sort', order: 'descending' }"
      max-height="420"
    >
      <el-table-column type="expand">
        <template slot-scope="props">
          <div v-if="props.row.reading_count > 1" class="vitals-history-expand">
            <p class="vitals-history-expand__title">All readings for {{ props.row.date }}</p>
            <el-table
              :data="vitalsByDay[props.row.day_key] || []"
              border
              size="mini"
              class="vitals-history-expand__table"
            >
              <el-table-column prop="time_display" label="Time" width="90" />
              <el-table-column prop="bp" label="BP (mmHg)" width="110" />
              <el-table-column prop="weight" label="Weight (kg)" width="100" />
              <el-table-column prop="height" label="Height (cm)" width="100" />
              <el-table-column prop="bmi" label="BMI" width="70" />
              <el-table-column prop="vit_temp" label="Temp (°C)" width="90" />
              <el-table-column prop="vit_cr" label="HR (bpm)" width="90" />
              <el-table-column prop="vit_rr" label="RR (rpm)" width="90" />
              <el-table-column prop="o2_stat" label="O2 Sat (%)" width="95" />
              <el-table-column label="" width="80">
                <template slot-scope="scope">
                  <el-tag v-if="scope.row.is_latest" size="mini" type="success">Latest</el-tag>
                </template>
              </el-table-column>
            </el-table>
          </div>
          <p v-else class="vitals-history-expand__empty">Only one reading recorded for this day.</p>
        </template>
      </el-table-column>
      <el-table-column prop="date" label="Date" sortable width="120" />
      <el-table-column label="Readings" width="95">
        <template slot-scope="scope">
          <span>{{ scope.row.reading_count || 1 }}</span>
        </template>
      </el-table-column>
      <el-table-column prop="bp" label="BP (mmHg)" width="110" />
      <el-table-column prop="weight" label="Weight (kg)" width="100" />
      <el-table-column prop="height" label="Height (cm)" width="100" />
      <el-table-column prop="bmi" label="BMI" width="70" />
      <el-table-column prop="vit_temp" label="Temp (°C)" width="90" />
      <el-table-column prop="vit_cr" label="HR (bpm)" width="90" />
      <el-table-column prop="vit_rr" label="RR (rpm)" width="90" />
      <el-table-column prop="o2_stat" label="O2 Sat (%)" width="95" />
    </el-table>
    <p v-else-if="!loading" class="vitals-history-empty">
      No past vitals recorded for this patient yet.
    </p>
  </div>
</template>

<script>
export default {
  name: 'PatientVitalsHistoryTable',
  props: {
    records: {
      type: Array,
      default: () => [],
    },
    vitalsByDay: {
      type: Object,
      default: () => ({}),
    },
    loading: {
      type: Boolean,
      default: false,
    },
  },
};
</script>

<style scoped>
.patient-vitals-history-table {
  min-height: 80px;
}

.vitals-history-expand {
  padding: 8px 12px 12px;
}

.vitals-history-expand__title {
  margin: 0 0 8px;
  font-size: 13px;
  font-weight: 600;
  color: #606266;
}

.vitals-history-expand__empty {
  margin: 0;
  padding: 8px 12px;
  font-size: 12px;
  color: #909399;
}

.vitals-history-empty {
  margin: 24px 0;
  text-align: center;
  font-size: 14px;
  color: #909399;
}
</style>
