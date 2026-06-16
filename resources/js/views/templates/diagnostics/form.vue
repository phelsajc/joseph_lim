<template>
  <div class="app-container dx-tpl-form-page">
    <el-card class="page-header-card" shadow="never">
      <div class="page-header">
        <div>
          <h2 class="page-title">{{ isEdit ? 'Edit template' : 'New template' }}</h2>
          <p class="page-subtitle">
            Link a diagnosis name to a diagnostic list. You can reorder items using the arrows.
          </p>
        </div>
        <div class="header-actions">
          <el-button @click="goBack">Cancel</el-button>
          <el-button type="primary" :loading="saving" @click="submit">
            Save
          </el-button>
        </div>
      </div>
    </el-card>

    <el-form ref="formRef" :model="form" :rules="rules" label-position="top">
      <el-card shadow="never" class="mb-3">
        <div slot="header" class="card-header-title">
          <span>Diagnosis</span>
        </div>
        <el-form-item label="Diagnosis name" prop="diagnosis_name">
          <el-autocomplete
            v-model="form.diagnosis_name"
            class="w-100"
            :fetch-suggestions="fetchDiagnosisSuggestions"
            placeholder="e.g. Acute Gastroenteritis"
            clearable
          />
        </el-form-item>
      </el-card>

      <el-card shadow="never">
        <div slot="header" class="card-header-title card-header-row">
          <span>Diagnostic items</span>
          <div class="card-header-actions">
            <el-button type="primary" size="small" icon="el-icon-plus" plain @click="addRow">
              Add item
            </el-button>
          </div>
        </div>

        <el-table :data="form.items" border class="dx-items-table" empty-text="Add at least one diagnostic item">
          <el-table-column type="index" label="#" width="50" align="center" />

          <el-table-column label="Diagnostic name" min-width="260">
            <template slot-scope="scope">
              <el-autocomplete
                v-model="scope.row.diagnostic_name"
                class="w-100"
                :fetch-suggestions="(q, cb) => fetchDiagnosticSuggestions(q, cb)"
                placeholder="Search active diagnostics (e.g. CBC)"
                clearable
              />
            </template>
          </el-table-column>

          <el-table-column label="Category" width="160">
            <template slot-scope="scope">
              <el-input v-model="scope.row.category" placeholder="Optional" />
            </template>
          </el-table-column>

          <el-table-column label="Description / Notes" min-width="240">
            <template slot-scope="scope">
              <el-input v-model="scope.row.notes" type="textarea" :rows="2" placeholder="Optional notes" />
            </template>
          </el-table-column>

          <el-table-column label="Priority" width="110" align="center">
            <template slot-scope="scope">
              <el-input-number v-model="scope.row.priority" :min="0" :max="9999" controls-position="right" />
            </template>
          </el-table-column>

          <el-table-column label="Active" width="90" align="center">
            <template slot-scope="scope">
              <el-checkbox v-model="scope.row.active" />
            </template>
          </el-table-column>

          <el-table-column label="Order" width="110" align="center">
            <template slot-scope="scope">
              <el-button
                icon="el-icon-arrow-up"
                size="mini"
                plain
                :disabled="scope.$index === 0"
                @click="moveUp(scope.$index)"
              />
              <el-button
                icon="el-icon-arrow-down"
                size="mini"
                plain
                :disabled="scope.$index === form.items.length - 1"
                @click="moveDown(scope.$index)"
              />
            </template>
          </el-table-column>

          <el-table-column label="" width="90" align="center" fixed="right">
            <template slot-scope="scope">
              <el-button
                type="danger"
                icon="el-icon-delete"
                size="mini"
                plain
                :disabled="form.items.length <= 1"
                @click="removeRow(scope.$index)"
              />
            </template>
          </el-table-column>
        </el-table>
      </el-card>
    </el-form>
  </div>
</template>

<script>
import Diagnostics from '@/api/diagnostics';
import {
  getDiagnosticTemplate,
  createDiagnosticTemplate,
  updateDiagnosticTemplate,
  diagnosisTemplateNameSuggestions,
} from '@/api/diagnosticTemplate';

function emptyItem() {
  return {
    diagnostic_name: '',
    category: '',
    notes: '',
    priority: null,
    active: true,
  };
}

export default {
  name: 'DiagnosticTemplateForm',
  data() {
    return {
      saving: false,
      activeDiagnosticsCache: [],
      form: {
        diagnosis_name: '',
        items: [emptyItem()],
      },
      rules: {
        diagnosis_name: [{ required: true, message: 'Diagnosis name is required', trigger: 'blur' }],
      },
    };
  },
  computed: {
    isEdit() {
      return Boolean(this.$route.params.id);
    },
  },
  created() {
    this.loadActiveDiagnostics();
    if (this.isEdit) {
      this.load();
    }
  },
  methods: {
    goBack() {
      this.$router.push({ name: 'DiagnosticTemplates' });
    },
    addRow() {
      this.form.items.push(emptyItem());
    },
    removeRow(index) {
      if (this.form.items.length <= 1) return;
      this.form.items.splice(index, 1);
    },
    moveUp(index) {
      if (index <= 0) return;
      const tmp = this.form.items[index - 1];
      this.$set(this.form.items, index - 1, this.form.items[index]);
      this.$set(this.form.items, index, tmp);
    },
    moveDown(index) {
      if (index >= this.form.items.length - 1) return;
      const tmp = this.form.items[index + 1];
      this.$set(this.form.items, index + 1, this.form.items[index]);
      this.$set(this.form.items, index, tmp);
    },
    async fetchDiagnosisSuggestions(queryString, cb) {
      try {
        const res = await diagnosisTemplateNameSuggestions({ q: queryString || '' });
        const list = (res.data || []).map((v) => ({ value: v }));
        cb(list);
      } catch (e) {
        cb([]);
      }
    },
    async load() {
      try {
        const res = await getDiagnosticTemplate(this.$route.params.id);
        const data = res.data;
        this.form.diagnosis_name = data.diagnosis_name || '';
        this.form.items = (data.items && data.items.length)
          ? data.items.map((r) => ({
            diagnostic_name: r.diagnostic_name || '',
            category: r.category || '',
            notes: r.notes || '',
            priority: r.priority != null ? r.priority : null,
            active: r.active !== false,
          }))
          : [emptyItem()];
      } catch (e) {
        this.$message.error('Could not load template');
        this.goBack();
      }
    },
    async loadActiveDiagnostics() {
      try {
        // Backend already filters with isactive = 1
        const res = await Diagnostics.getAllDiagnostics();
        const list = Array.isArray(res) ? res : (res && res.data) ? res.data : [];
        this.activeDiagnosticsCache = (list || [])
          .map((x) => (x && x.lab_test ? String(x.lab_test) : ''))
          .filter((x) => x.trim() !== '');
      } catch (e) {
        this.activeDiagnosticsCache = [];
      }
    },
    fetchDiagnosticSuggestions(queryString, cb) {
      const q = (queryString || '').trim().toLowerCase();
      const list = this.activeDiagnosticsCache || [];
      const filtered = !q ? list : list.filter((n) => n.toLowerCase().includes(q));
      cb(filtered.slice(0, 30).map((v) => ({ value: v })));
    },
    submit() {
      this.$refs.formRef.validate(async (valid) => {
        if (!valid) return;

        const payload = {
          diagnosis_name: (this.form.diagnosis_name || '').trim(),
          items: (this.form.items || []).map((r) => ({
            diagnostic_name: (r.diagnostic_name || '').trim(),
            category: (r.category || '').trim() || null,
            notes: (r.notes || '').trim() || null,
            priority: (r.priority === '' || r.priority == null) ? null : Number(r.priority),
            active: !!r.active,
          })),
        };

        const hasAny = payload.items.some((x) => x.diagnostic_name);
        if (!hasAny) {
          this.$message.error('At least one diagnostic item is required.');
          return;
        }
        const missingName = payload.items.some((x) => !x.diagnostic_name);
        if (missingName) {
          this.$message.error('Each row must have a diagnostic name (remove empty rows).');
          return;
        }

        this.saving = true;
        try {
          if (this.isEdit) {
            await updateDiagnosticTemplate(this.$route.params.id, payload);
            this.$message.success('Template updated');
          } else {
            await createDiagnosticTemplate(payload);
            this.$message.success('Template created');
          }
          this.goBack();
        } catch (e) {
          this.$message.error('Could not save template. Check required fields and try again.');
        } finally {
          this.saving = false;
        }
      });
    },
  },
};
</script>

<style scoped>
.dx-tpl-form-page {
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
  gap: 12px;
  align-items: flex-start;
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

.card-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-header-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.mb-3 {
  margin-bottom: 16px;
}

.w-100 {
  width: 100%;
}

@media (max-width: 992px) {
  .page-header {
    flex-direction: column;
  }
}
</style>

