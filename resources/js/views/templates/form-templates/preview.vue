<template>
  <div class="app-container form-tpl-preview-page">
    <el-card class="page-header-card" shadow="never">
      <div class="page-header">
        <div>
          <h2 class="page-title">Print preview</h2>
          <p class="page-subtitle">{{ template.name || '—' }}</p>
        </div>
        <div class="header-actions">
          <el-button @click="goBack">Back</el-button>
          <el-button type="primary" @click="printPage">Print</el-button>
        </div>
      </div>
    </el-card>

    <el-card v-loading="loading" shadow="never">
      <FormTemplateA4Preview :content-html="template.content_html" />
    </el-card>
  </div>
</template>

<script>
import { getFormTemplate } from '@/api/formTemplate';
import FormTemplateA4Preview from './components/FormTemplateA4Preview';
import { openFormTemplatePrintPreview } from './components/openFormTemplatePrint';

export default {
  name: 'FormTemplatePreview',
  components: { FormTemplateA4Preview },
  data() {
    return {
      loading: false,
      template: {
        name: '',
        content_html: '',
      },
    };
  },
  created() {
    this.load();
  },
  methods: {
    goBack() {
      this.$router.push({ name: 'FormTemplateView', params: { id: this.$route.params.id }});
    },
    printPage() {
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
        this.$router.push({ name: 'FormTemplates' });
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
.form-tpl-preview-page {
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
}

.page-subtitle {
  margin: 0;
  font-size: 15px;
  color: #606266;
}

.header-actions .el-button + .el-button {
  margin-left: 8px;
}
</style>
