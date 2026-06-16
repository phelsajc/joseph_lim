<template>
  <div v-loading="loading" class="app-container form-tpl-form-page">
    <el-card class="page-header-card" shadow="never">
      <div class="page-header">
        <div>
          <h2 class="page-title">{{ isEdit ? 'Edit form template' : 'New form template' }}</h2>
          <p class="page-subtitle">
            Build reusable medical documents (certificates, referrals, admitting letters, PT notes, etc.).
            Rich HTML is preserved when loaded into an appointment. Preview and print use <strong>A5 portrait</strong> (same as prescription).
            Structured Rx rows use
            <router-link class="inl" :to="{ name: 'PrescriptionDiagnosisTemplates' }">Rx diagnosis templates</router-link>.
          </p>
        </div>
        <div class="header-actions">
          <el-button v-if="isEdit" @click="goPreview">Page preview</el-button>
          <el-button @click="openPrintPreview">Print</el-button>
          <el-button @click="goBack">Cancel</el-button>
          <el-button type="primary" :loading="saving" @click="submit">Save</el-button>
        </div>
      </div>
    </el-card>

    <el-form ref="formRef" :model="form" :rules="rules" label-position="top">
      <el-card shadow="never" class="mb-3">
        <el-row :gutter="20">
          <el-col :xs="24" :md="10">
            <el-form-item label="Template name" prop="name">
              <el-input
                v-model="form.name"
                placeholder="e.g. Medical Certificate — General"
                maxlength="500"
                show-word-limit
              />
            </el-form-item>
          </el-col>
          <el-col :xs="24" :md="8">
            <el-form-item label="Category / type" prop="category">
              <el-select
                v-model="form.category"
                filterable
                allow-create
                default-first-option
                placeholder="Select or type category"
                style="width: 100%"
              >
                <el-option
                  v-for="c in categoryOptions"
                  :key="c"
                  :label="c"
                  :value="c"
                />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :xs="24" :md="24">
            <el-form-item label="Description (optional)">
              <el-input
                v-model="form.description"
                type="textarea"
                :rows="2"
                maxlength="1000"
                show-word-limit
                placeholder="Short note for staff — when to use this template"
              />
            </el-form-item>
          </el-col>
        </el-row>
      </el-card>

      <el-card shadow="never">
        <div slot="header" class="card-header-title card-header-split">
          <span>Template builder</span>
          <span class="header-hint">Edit · Live preview · Insert placeholders from the sidebar</span>
        </div>
        <el-row :gutter="16" class="builder-layout">
          <el-col :xs="24" :md="7" :lg="6" class="builder-sidebar-col">
            <PlaceholderSidebar
              @insert="insertPlaceholder"
              @insert-snippet="insertSnippet"
            />
          </el-col>
          <el-col :xs="24" :md="17" :lg="18">
            <el-form-item prop="content_html" class="editor-form-item">
              <el-tabs v-model="editorTab">
                <el-tab-pane label="Edit" name="edit">
                  <QuillEditor
                    ref="quillEditor"
                    v-model="form.content_html"
                    :height="editorHeight"
                    preset="full"
                    :font-sizes="formEditorFontSizes"
                    placeholder="Compose your form template…"
                  />
                </el-tab-pane>
                <el-tab-pane label="Live preview (A5 portrait)" name="preview">
                  <div class="preview-toolbar">
                    <el-button size="small" plain @click="openPrintPreview">Print</el-button>
                  </div>
                  <FormTemplateA4Preview :content-html="form.content_html">
                    <template slot="empty">
                      Compose content in <strong>Edit</strong> — preview appears here.
                    </template>
                  </FormTemplateA4Preview>
                </el-tab-pane>
              </el-tabs>
            </el-form-item>
          </el-col>
        </el-row>
      </el-card>
    </el-form>
  </div>
</template>

<script>
import QuillEditor from '@/components/QuillEditor';
import { getFormTemplate, createFormTemplate, updateFormTemplate, getFormTemplateCategories } from '@/api/formTemplate';
import PlaceholderSidebar from './components/PlaceholderSidebar';
import FormTemplateA4Preview from './components/FormTemplateA4Preview';
import { openFormTemplatePrintPreview } from './components/openFormTemplatePrint';
import { FORM_TEMPLATE_CATEGORIES } from './constants';
import {
  clinicHeaderSnippetHtml,
  horizontalRuleSnippetHtml,
  signatureBlockSnippetHtml,
} from './components/formTemplateSnippets';

const FORM_EDITOR_FONT_SIZES = [
  '8px', '9px', '10px', '11px', '12px', '14px', '16px', '18px', '20px', '24px',
  '28px', '32px', '36px', '40px', '48px', '60px', '72px',
];

export default {
  name: 'FormTemplateForm',
  components: { QuillEditor, PlaceholderSidebar, FormTemplateA4Preview },
  data() {
    return {
      saving: false,
      loading: false,
      editorHeight: 520,
      editorTab: 'edit',
      formEditorFontSizes: FORM_EDITOR_FONT_SIZES,
      categoryOptions: [...FORM_TEMPLATE_CATEGORIES],
      form: {
        name: '',
        category: '',
        description: '',
        content_html: '',
      },
      rules: {
        name: [{ required: true, message: 'Template name is required', trigger: 'blur' }],
      },
    };
  },
  computed: {
    isEdit() {
      return !!this.$route.params.id;
    },
  },
  created() {
    this.loadCategoryOptions();
    if (this.isEdit) {
      this.load();
    }
  },
  methods: {
    async loadCategoryOptions() {
      try {
        const res = await getFormTemplateCategories();
        const fromApi = res.data || [];
        this.categoryOptions = [...new Set([...FORM_TEMPLATE_CATEGORIES, ...fromApi])];
      } catch (e) {
        this.categoryOptions = [...FORM_TEMPLATE_CATEGORIES];
      }
    },
    goBack() {
      this.$router.push({ name: 'FormTemplates' });
    },
    goPreview() {
      if (!this.isEdit) {
        return;
      }
      this.$router.push({ name: 'FormTemplatePreview', params: { id: this.$route.params.id }});
    },
    openPrintPreview() {
      const ok = openFormTemplatePrintPreview(
        this.form.content_html,
        this.form.name || 'Form template'
      );
      if (!ok) {
        this.$message.warning('Allow pop-ups to use print preview.');
      }
    },
    insertPlaceholder(token) {
      const editor = this.$refs.quillEditor;
      if (editor && typeof editor.insertHtml === 'function') {
        editor.insertHtml(token);
      } else {
        this.form.content_html = `${this.form.content_html || ''}${token}`;
      }
    },
    insertSnippet(type) {
      let html = '';
      if (type === 'clinicHeader') {
        html = clinicHeaderSnippetHtml();
      } else if (type === 'signature') {
        html = signatureBlockSnippetHtml();
      } else if (type === 'hr') {
        html = horizontalRuleSnippetHtml();
      }
      const editor = this.$refs.quillEditor;
      if (editor && typeof editor.insertHtml === 'function') {
        editor.insertHtml(html);
      } else {
        this.form.content_html = `${this.form.content_html || ''}${html}`;
      }
    },
    async load() {
      this.loading = true;
      try {
        const res = await getFormTemplate(this.$route.params.id);
        const d = res.data || {};
        this.form.name = d.name || '';
        this.form.category = d.category || '';
        this.form.description = d.description || '';
        this.form.content_html = d.content_html || '';
      } catch (e) {
        this.$message.error('Could not load template');
        this.goBack();
      } finally {
        this.loading = false;
      }
    },
    submit() {
      this.$refs.formRef.validate(async(valid) => {
        if (!valid) {
          return;
        }
        this.saving = true;
        try {
          const payload = {
            name: this.form.name.trim(),
            category: (this.form.category || '').trim() || null,
            description: (this.form.description || '').trim() || null,
            content_html: this.form.content_html || '',
          };
          if (this.isEdit) {
            await updateFormTemplate(this.$route.params.id, payload);
            this.$message.success('Template updated');
          } else {
            await createFormTemplate(payload);
            this.$message.success('Template created');
          }
          this.goBack();
        } catch (e) {
          const msg = (e && e.response && e.response.data && e.response.data.message) || 'Save failed';
          this.$message.error(msg);
        } finally {
          this.saving = false;
        }
      });
    },
  },
};
</script>

<style scoped>
.form-tpl-form-page {
  max-width: 1280px;
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
  max-width: 860px;
  line-height: 1.55;
}

.page-subtitle .inl {
  color: #409eff;
  font-weight: 600;
}

.header-actions .el-button + .el-button {
  margin-left: 8px;
}

.mb-3 {
  margin-bottom: 16px;
}

.card-header-title {
  font-weight: 600;
  color: #303133;
}

.card-header-split {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
}

.header-hint {
  font-size: 12px;
  font-weight: 400;
  color: #909399;
}

.builder-sidebar-col {
  margin-bottom: 12px;
}

.editor-form-item {
  margin-bottom: 0;
}

.preview-toolbar {
  margin-bottom: 10px;
}

@media (min-width: 992px) {
  .builder-sidebar-col {
    margin-bottom: 0;
  }
}
</style>
