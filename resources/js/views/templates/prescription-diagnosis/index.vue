<template>
  <div class="app-container rx-dx-tpl-page">
    <el-card class="page-header-card" shadow="never">
      <div class="page-header">
        <div>
          <h2 class="page-title">Prescription Templates by Diagnosis</h2>
          <p class="page-subtitle">
            Define reusable medication lists for common diagnoses. Use them when entering prescriptions from an appointment.
          </p>
        </div>
        <el-button type="primary" icon="el-icon-plus" @click="goCreate">
          New template
        </el-button>
      </div>
    </el-card>

    <el-card class="filter-card" shadow="never">
      <el-row :gutter="16" type="flex" align="middle">
        <el-col :xs="24" :sm="12" :md="10">
          <el-input
            v-model="query.keyword"
            placeholder="Search by diagnosis name"
            clearable
            prefix-icon="el-icon-search"
            @keyup.enter.native="handleFilter"
          />
        </el-col>
        <el-col :xs="24" :sm="12" :md="14">
          <el-button type="primary" icon="el-icon-search" @click="handleFilter">
            Search
          </el-button>
        </el-col>
      </el-row>
    </el-card>

    <el-card shadow="never">
      <el-table
        v-loading="loading"
        :data="rows"
        border
        stripe
        style="width: 100%"
        class="rx-dx-tpl-table"
        empty-text="No templates yet"
      >
        <el-table-column prop="diagnosis_name" label="Diagnosis name" min-width="220" show-overflow-tooltip />
        <el-table-column label="Medications" width="140" align="center">
          <template slot-scope="scope">
            <el-tag type="info" size="small">{{ scope.row.items_count }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Last updated" width="200" align="center">
          <template slot-scope="scope">
            {{ formatDate(scope.row.updated_at) }}
          </template>
        </el-table-column>
        <el-table-column label="Actions" width="200" align="center" fixed="right">
          <template slot-scope="scope">
            <el-button type="primary" size="mini" plain @click="goEdit(scope.row.id)">
              Edit
            </el-button>
            <el-popconfirm
              title="Delete this template?"
              confirm-button-text="Delete"
              cancel-button-text="Cancel"
              @confirm="remove(scope.row)"
            >
              <el-button slot="reference" type="danger" size="mini" plain>
                Delete
              </el-button>
            </el-popconfirm>
          </template>
        </el-table-column>
      </el-table>

      <pagination
        v-show="total > 0"
        :total="total"
        :page.sync="query.page"
        :limit.sync="query.limit"
        @pagination="fetchList"
      />
    </el-card>
  </div>
</template>

<script>
import moment from 'moment-timezone';
import Pagination from '@/components/Pagination';
import { listPrescriptionDiagnosisTemplates, deletePrescriptionDiagnosisTemplate } from '@/api/prescriptionDiagnosisTemplate';

export default {
  name: 'PrescriptionDiagnosisTemplatesIndex',
  components: { Pagination },
  data() {
    return {
      loading: false,
      rows: [],
      total: 0,
      query: {
        page: 1,
        limit: 15,
        keyword: '',
      },
    };
  },
  created() {
    this.fetchList();
  },
  methods: {
    formatDate(value) {
      if (!value) {
        return '—';
      }
      return moment(value).format('MMM D, YYYY h:mm A');
    },
    async fetchList() {
      this.loading = true;
      try {
        const res = await listPrescriptionDiagnosisTemplates(this.query);
        this.rows = res.data || [];
        this.total = (res.meta && res.meta.total) || 0;
      } catch (e) {
        this.$message.error('Failed to load templates');
      } finally {
        this.loading = false;
      }
    },
    handleFilter() {
      this.query.page = 1;
      this.fetchList();
    },
    goCreate() {
      this.$router.push({ name: 'PrescriptionDiagnosisTemplateCreate' });
    },
    goEdit(id) {
      this.$router.push({ name: 'PrescriptionDiagnosisTemplateEdit', params: { id } });
    },
    async remove(row) {
      try {
        await deletePrescriptionDiagnosisTemplate(row.id);
        this.$message.success('Template deleted');
        this.fetchList();
      } catch (e) {
        this.$message.error('Could not delete template');
      }
    },
  },
};
</script>

<style scoped>
.rx-dx-tpl-page {
  max-width: 1200px;
  margin: 0 auto;
}

.page-header-card {
  margin-bottom: 16px;
}

.page-header {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
}

.page-title {
  margin: 0 0 6px;
  font-size: 20px;
  font-weight: 600;
  color: #303133;
}

.page-subtitle {
  margin: 0;
  font-size: 13px;
  color: #606266;
  max-width: 640px;
  line-height: 1.5;
}

.filter-card {
  margin-bottom: 16px;
}

.rx-dx-tpl-table {
  width: 100%;
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
  }
}
</style>
