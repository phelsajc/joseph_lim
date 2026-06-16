<template>
  <div class="app-container form-tpl-view-page">
    <el-card class="page-header-card" shadow="never">
      <div class="page-header">
        <div>
          <h2 class="page-title">View form template</h2>
          <p class="page-subtitle">{{ template.name || '—' }}</p>
        </div>
        <div class="header-actions">
          <el-button @click="goBack">Back</el-button>
          <el-button plain @click="goPreview">Page preview</el-button>
          <el-button type="primary" @click="goEdit">Edit</el-button>
        </div>
      </div>
    </el-card>

    <el-card shadow="never" class="mb-3">
      <div slot="header" class="card-header-title">
        <span>Details</span>
      </div>
      <el-row :gutter="16">
        <el-col :xs="24" :sm="8">
          <div class="kv">
            <div class="k">Category / type</div>
            <div class="v">{{ template.category || '—' }}</div>
          </div>
        </el-col>
        <el-col :xs="24" :sm="8">
          <div class="kv">
            <div class="k">Created by</div>
            <div class="v">{{ (template.creator && template.creator.name) ? template.creator.name : '—' }}</div>
          </div>
        </el-col>
        <el-col :xs="24" :sm="8">
          <div class="kv">
            <div class="k">Updated</div>
            <div class="v">{{ formatDate(template.updated_at) }}</div>
          </div>
        </el-col>
        <el-col v-if="template.description" :xs="24">
          <div class="kv kv--block">
            <div class="k">Description</div>
            <div class="v v--normal">{{ template.description }}</div>
          </div>
        </el-col>
      </el-row>
    </el-card>

    <el-card v-loading="loading" shadow="never">
      <div slot="header" class="card-header-title card-header-split">
        <span>Content preview</span>
        <el-button size="small" plain @click="printPreview">Print</el-button>
      </div>
      <FormTemplateA4Preview :content-html="template.content_html" />
    </el-card>
  </div>
</template>

<script>
import moment from 'moment-timezone';
import { getFormTemplate } from '@/api/formTemplate';
import FormTemplateA4Preview from './components/FormTemplateA4Preview';
import { openFormTemplatePrintPreview } from './components/openFormTemplatePrint';

export default {
  name: 'FormTemplateView',
  components: { FormTemplateA4Preview },
  data() {
    return {
      loading: false,
      template: {
        name: '',
        category: '',
        description: '',
        content_html: '',
        creator: null,
        updated_at: null,
      },
    };
  },
  created() {
    this.load();
  },
  methods: {
    formatDate(value) {
      if (!value) {
        return '—';
      }
      return moment(value).format('MMM D, YYYY h:mm A');
    },
    goBack() {
      this.$router.push({ name: 'FormTemplates' });
    },
    goEdit() {
      this.$router.push({ name: 'FormTemplateEdit', params: { id: this.$route.params.id }});
    },
    goPreview() {
      this.$router.push({ name: 'FormTemplatePreview', params: { id: this.$route.params.id }});
    },
    printPreview() {
      const ok = openFormTemplatePrintPreview(
        this.template.content_html,
        this.template.name || 'Form template'
      );
      if (!ok) {
        this.$message.warning('Allow pop-ups to print.');
      }
    },
    async load() {
      this.loading = true;
      try {
        const res = await getFormTemplate(this.$route.params.id);
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
.form-tpl-view-page {
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
  font-size: 15px;
  color: #606266;
  font-weight: 500;
}

.header-actions .el-button + .el-button {
  margin-left: 8px;
}

.card-header-title {
  font-weight: 600;
  color: #303133;
}

.card-header-split {
  display: flex;
  justify-content: space-between;
  align-items: center;
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

.kv--block {
  margin-top: 12px;
}

.v--normal {
  font-weight: 400;
  line-height: 1.5;
}
</style>
