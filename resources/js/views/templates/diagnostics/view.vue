<template>
  <div class="app-container dx-tpl-view-page">
    <el-card class="page-header-card" shadow="never">
      <div class="page-header">
        <div>
          <h2 class="page-title">View template</h2>
          <p class="page-subtitle">
            {{ template.diagnosis_name || '—' }}
          </p>
        </div>
        <div class="header-actions">
          <el-button @click="goBack">Back</el-button>
          <el-button type="primary" @click="goEdit">Edit</el-button>
        </div>
      </div>
    </el-card>

    <el-card shadow="never" class="mb-3">
      <div slot="header" class="card-header-title">
        <span>Details</span>
      </div>
      <el-row :gutter="16">
        <el-col :xs="24" :sm="12">
          <div class="kv">
            <div class="k">Diagnosis name</div>
            <div class="v">{{ template.diagnosis_name || '—' }}</div>
          </div>
        </el-col>
        <el-col :xs="24" :sm="12">
          <div class="kv">
            <div class="k">Created by</div>
            <div class="v">{{ (template.creator && template.creator.name) ? template.creator.name : '—' }}</div>
          </div>
        </el-col>
      </el-row>
    </el-card>

    <el-card shadow="never">
      <div slot="header" class="card-header-title">
        <span>Diagnostic items</span>
      </div>
      <el-table v-loading="loading" :data="template.items || []" border stripe empty-text="No items">
        <el-table-column type="index" label="#" width="60" align="center" />
        <el-table-column prop="diagnostic_name" label="Diagnostic name" min-width="240" show-overflow-tooltip />
        <el-table-column prop="category" label="Category" width="160" show-overflow-tooltip />
        <el-table-column prop="notes" label="Notes" min-width="240" show-overflow-tooltip />
        <el-table-column prop="priority" label="Priority" width="110" align="center" />
        <el-table-column label="Active" width="90" align="center">
          <template slot-scope="scope">
            <el-tag :type="scope.row.active ? 'success' : 'info'" size="mini">
              {{ scope.row.active ? 'Yes' : 'No' }}
            </el-tag>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>

<script>
import { getDiagnosticTemplate } from '@/api/diagnosticTemplate';

export default {
  name: 'DiagnosticTemplateView',
  data() {
    return {
      loading: false,
      template: {
        diagnosis_name: '',
        creator: null,
        items: [],
      },
    };
  },
  created() {
    this.load();
  },
  methods: {
    goBack() {
      this.$router.push({ name: 'DiagnosticTemplates' });
    },
    goEdit() {
      this.$router.push({ name: 'DiagnosticTemplateEdit', params: { id: this.$route.params.id } });
    },
    async load() {
      this.loading = true;
      try {
        const res = await getDiagnosticTemplate(this.$route.params.id);
        this.template = res.data || this.template;
      } catch (e) {
        this.$message.error('Could not load template');
        this.goBack();
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
.dx-tpl-view-page {
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
  max-width: 720px;
  line-height: 1.5;
}

.header-actions .el-button + .el-button {
  margin-left: 8px;
}

.card-header-title {
  font-weight: 600;
  color: #303133;
}

.mb-3 {
  margin-bottom: 16px;
}

.kv .k {
  font-size: 12px;
  color: #909399;
}

.kv .v {
  font-size: 14px;
  color: #303133;
  font-weight: 500;
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
  }
}
</style>

