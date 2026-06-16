<template>
  <div class="app-container form-tpl-page">
    <el-card class="page-header-card" shadow="never">
      <div class="page-header">
        <div>
          <h2 class="page-title">Form Templates</h2>
          <p class="page-subtitle">
            Build reusable rich-text forms and clinical notes. Load them into an appointment’s Form tab and edit before saving.
          </p>
        </div>
        <el-button type="primary" icon="el-icon-plus" @click="goCreate">
          New template
        </el-button>
      </div>
    </el-card>

    <el-card class="filter-card" shadow="never">
      <el-row :gutter="16" type="flex" align="middle">
        <el-col :xs="24" :sm="12" :md="8">
          <el-input
            v-model="query.keyword"
            placeholder="Search name or category"
            clearable
            prefix-icon="el-icon-search"
            @keyup.enter.native="handleFilter"
          />
        </el-col>
        <el-col :xs="24" :sm="12" :md="8">
          <el-select
            v-model="query.category"
            placeholder="Filter by category"
            clearable
            filterable
            style="width: 100%"
            @change="handleFilter"
          >
            <el-option
              v-for="c in categoryOptions"
              :key="c"
              :label="c"
              :value="c"
            />
          </el-select>
        </el-col>
        <el-col :xs="24" :sm="24" :md="8">
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
        class="form-tpl-table"
        empty-text="No templates yet"
      >
        <el-table-column prop="name" label="Template name" min-width="200" show-overflow-tooltip />
        <el-table-column prop="description" label="Description" min-width="180" show-overflow-tooltip>
          <template slot-scope="scope">
            <span>{{ scope.row.description || '—' }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="category" label="Category / type" width="160" show-overflow-tooltip>
          <template slot-scope="scope">
            <span>{{ scope.row.category || '—' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="Created by" width="180" show-overflow-tooltip>
          <template slot-scope="scope">
            {{ (scope.row.creator && scope.row.creator.name) ? scope.row.creator.name : '—' }}
          </template>
        </el-table-column>
        <el-table-column label="Updated date" width="200" align="center">
          <template slot-scope="scope">
            {{ formatDate(scope.row.updated_at) }}
          </template>
        </el-table-column>
        <el-table-column label="Actions" width="340" align="center" fixed="right">
          <template slot-scope="scope">
            <el-button size="mini" plain @click="goPreview(scope.row.id)">
              Preview
            </el-button>
            <el-button size="mini" plain @click="goView(scope.row.id)">
              View
            </el-button>
            <el-button type="primary" size="mini" plain @click="goEdit(scope.row.id)">
              Edit
            </el-button>
            <el-button size="mini" plain @click="duplicate(scope.row)">
              Duplicate
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
import {
  listFormTemplates,
  deleteFormTemplate,
  duplicateFormTemplate,
  getFormTemplateCategories,
} from '@/api/formTemplate';

export default {
  name: 'FormTemplatesIndex',
  components: { Pagination },
  data() {
    return {
      loading: false,
      rows: [],
      total: 0,
      categoryOptions: [],
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        category: '',
      },
    };
  },
  created() {
    this.loadCategories();
    this.fetchList();
  },
  methods: {
    formatDate(value) {
      if (!value) {
        return '—';
      }
      return moment(value).format('MMM D, YYYY h:mm A');
    },
    async loadCategories() {
      try {
        const res = await getFormTemplateCategories();
        this.categoryOptions = res.data || [];
      } catch (e) {
        this.categoryOptions = [];
      }
    },
    async fetchList() {
      this.loading = true;
      try {
        const res = await listFormTemplates(this.query);
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
      this.$router.push({ name: 'FormTemplateCreate' });
    },
    goEdit(id) {
      this.$router.push({ name: 'FormTemplateEdit', params: { id }});
    },
    goView(id) {
      this.$router.push({ name: 'FormTemplateView', params: { id }});
    },
    goPreview(id) {
      this.$router.push({ name: 'FormTemplatePreview', params: { id }});
    },
    async duplicate(row) {
      try {
        await duplicateFormTemplate(row.id);
        this.$message.success('Template duplicated');
        this.fetchList();
        this.loadCategories();
      } catch (e) {
        this.$message.error('Could not duplicate template');
      }
    },
    async remove(row) {
      try {
        await deleteFormTemplate(row.id);
        this.$message.success('Template deleted');
        this.fetchList();
        this.loadCategories();
      } catch (e) {
        this.$message.error('Could not delete template');
      }
    },
  },
};
</script>

<style scoped>
.form-tpl-page {
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
  max-width: 720px;
  line-height: 1.5;
}

.filter-card {
  margin-bottom: 16px;
}
</style>
