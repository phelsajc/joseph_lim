<template>
  <div class="app-container appointment-report-page">
    <div class="filter-container report-filters">
      <div class="filter-row">
        <date-picker
          v-model="range"
          range
          value-type="format"
          format="YYYY-MM-DD"
          class="filter-item filter-range"
          placeholder="Select date range"
        />
        <el-button class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
          {{ $t('table.search') }}
        </el-button>
      </div>
    </div>

    <div v-loading="loading" class="report-grid">
      <article class="report-card report-card--current">
        <div class="report-card-icon"><i class="el-icon-time" /></div>
        <div class="report-card-body">
          <span class="report-card-label">Current</span>
          <span class="report-card-count">{{ report.current }}</span>
        </div>
      </article>
      <article class="report-card report-card--completed">
        <div class="report-card-icon"><i class="el-icon-circle-check" /></div>
        <div class="report-card-body">
          <span class="report-card-label">Completed</span>
          <span class="report-card-count">{{ report.completed }}</span>
        </div>
      </article>
      <article class="report-card report-card--cancelled">
        <div class="report-card-icon"><i class="el-icon-circle-close" /></div>
        <div class="report-card-body">
          <span class="report-card-label">Cancelled</span>
          <span class="report-card-count">{{ report.cancelled }}</span>
        </div>
      </article>
      <article class="report-card report-card--total">
        <div class="report-card-icon"><i class="el-icon-data-line" /></div>
        <div class="report-card-body">
          <span class="report-card-label">Total</span>
          <span class="report-card-count">{{ report.total }}</span>
        </div>
      </article>
    </div>

    <el-table
      v-loading="loading"
      :data="report.details"
      border
      stripe
      class="report-table"
      empty-text="No appointments found"
    >
      <el-table-column type="index" label="#" width="55" align="center" />
      <el-table-column prop="patientname" label="Patient Name" min-width="180" />
      <el-table-column prop="apt_dt" label="Date" width="160" />
      <el-table-column label="Status" width="130" align="center">
        <template slot-scope="scope">
          <el-tag :type="statusTagType(scope.row.state)" size="medium">
            {{ scope.row.status }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column prop="complaints" label="Complaints" min-width="180" show-overflow-tooltip />
      <el-table-column label="Cancelled Reason" min-width="160" show-overflow-tooltip>
        <template slot-scope="scope">
          <span>{{ scope.row.cancel_reason || '—' }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Final Fee" width="120" align="right">
        <template slot-scope="scope">
          <span>{{ formatCurrency(scope.row.fee) }}</span>
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>

<script>
import Patients from '@/api/patients';
import moment from 'moment-timezone';
import DatePicker from 'vue2-datepicker';
import 'vue2-datepicker/index.css';

export default {
  name: 'AppointmentReport',
  components: { DatePicker },
  data() {
    return {
      loading: true,
      range: [
        moment().startOf('month').format('YYYY-MM-DD'),
        moment().endOf('month').format('YYYY-MM-DD'),
      ],
      report: {
        current: 0,
        completed: 0,
        cancelled: 0,
        total: 0,
        details: [],
      },
    };
  },
  created() {
    this.getReport();
  },
  methods: {
    async getReport() {
      this.loading = true;
      try {
        const [from, to] = this.range || [];
        const params = {
          from: from || moment().format('YYYY-MM-DD'),
          to: to || moment().format('YYYY-MM-DD'),
        };
        const data = await Patients.apt_report({ params });
        this.report = {
          current: data.current || 0,
          completed: data.completed || 0,
          cancelled: data.cancelled || 0,
          total: data.total || 0,
          details: data.details || [],
        };
      } catch (err) {
        console.error('Error fetching appointment report:', err);
      } finally {
        this.loading = false;
      }
    },
    handleFilter() {
      this.getReport();
    },
    statusTagType(state) {
      if (state === 1) {
        return 'success';
      }
      if (state === 2) {
        return 'danger';
      }
      return 'primary';
    },
    formatCurrency(value) {
      const amount = parseFloat(value) || 0;
      return '₱' + amount.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    },
  },
};
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
.appointment-report-page {
  .report-filters {
    padding-bottom: 16px;
  }

  .filter-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
  }

  .filter-range {
    min-width: 260px;
  }
}

.report-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 16px;
  min-height: 120px;
}

.report-table {
  margin-top: 24px;
  width: 100%;
}

.report-card {
  display: flex;
  align-items: center;
  gap: 16px;
  background: #fff;
  border: 1px solid #e4e7ed;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  transition: box-shadow 0.2s ease, transform 0.2s ease;

  &:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
  }
}

.report-card-icon {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 26px;
  color: #fff;
  flex-shrink: 0;
}

.report-card--current .report-card-icon {
  background: linear-gradient(135deg, #409eff 0%, #66b1ff 100%);
}

.report-card--completed .report-card-icon {
  background: linear-gradient(135deg, #67c23a 0%, #85ce61 100%);
}

.report-card--cancelled .report-card-icon {
  background: linear-gradient(135deg, #f56c6c 0%, #f78989 100%);
}

.report-card--total .report-card-icon {
  background: linear-gradient(135deg, #909399 0%, #b1b3b8 100%);
}

.report-card-body {
  display: flex;
  flex-direction: column;
}

.report-card-label {
  font-size: 13px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  color: #909399;
  margin-bottom: 4px;
}

.report-card-count {
  font-size: 30px;
  font-weight: 700;
  color: #303133;
  line-height: 1;
}

@media (max-width: 768px) {
  .report-grid {
    grid-template-columns: 1fr 1fr;
  }

  .filter-range {
    width: 100%;
  }
}
</style>
