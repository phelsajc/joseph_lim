<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="query.keyword" :placeholder="$t('table.keyword')" style="width: 200px" class="filter-item"
        @keyup.enter.native="handleFilter" />
      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
        {{ $t("table.search") }}
      </el-button>
      <el-button class="filter-item" style="margin-left: 10px" type="success" icon="el-icon-plus" @click="handleCreate">
        {{ $t("table.add") }}
      </el-button>
    </div>
    <el-table v-loading="loading" :data="item_list" border fit highlight-current-row style="width: 100%">
      <el-table-column align="center" label="ID" width="70" fixed>
        <template slot-scope="scope">
          <span>{{ scope.row.id }}</span>
        </template>
      </el-table-column>
      <el-table-column align="left" label="Brand Name" min-width="160" show-overflow-tooltip>
        <template slot-scope="scope">
          <span>{{ scope.row.brand_name || scope.row.medicine }}</span>
        </template>
      </el-table-column>
      <el-table-column align="left" label="Generic Name" min-width="160" show-overflow-tooltip>
        <template slot-scope="scope">
          <span>{{ scope.row.generic_name || scope.row.generic }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Included" width="90">
        <template slot-scope="scope">
          <el-tag :type="scope.row.isincluded ? 'success' : 'info'" size="small">
            {{ scope.row.isincluded ? 'Yes' : 'No' }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Created" width="110">
        <template slot-scope="scope">
          <span>{{ formatDate(scope.row.createddate) }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Qty" width="64" prop="default_qty" />
      <el-table-column align="left" label="Dosage" width="100" show-overflow-tooltip prop="unit" />
      <el-table-column align="left" label="Remarks" min-width="140" show-overflow-tooltip prop="default_remarks" />
      <el-table-column align="center" label="Actions" width="180" fixed="right">
        <template #default="scope">
          <el-button type="primary" size="mini" @click="editMedicine(scope.$index, scope.row)">
            Edit
          </el-button>
          <el-popconfirm confirm-button-text="Yes" cancel-button-text="No" icon-color="#626AEF"
            title="Do you want to delete this medicine?" @confirm="deleteMedicine(scope.$index, scope.row)">
            <template #reference>
              <el-button type="danger" size="mini"> Delete </el-button>
            </template>
          </el-popconfirm>
        </template>
      </el-table-column>
    </el-table>
    <pagination v-show="total > 0" :total="total" :page.sync="query.page" :limit.sync="query.limit"
      @pagination="getMedicines" />

    <el-dialog :title="isEditForm ? 'Edit Medicine' : 'Add Medicine'" :visible.sync="dialogFormVisible"
      :close-on-click-modal="false" :close-on-press-escape="false" width="720px">
      <div class="form-container">
        <el-form ref="appForm" :model="form" :rules="rules" label-position="left" label-width="150px">
          <el-form-item label="Brand Name" prop="brand_name">
            <el-input v-model="form.brand_name" />
          </el-form-item>
          <el-form-item label="Generic Name" prop="generic_name">
            <el-input v-model="form.generic_name" />
          </el-form-item>
          <el-form-item label="Included">
            <el-switch v-model="form.isincluded" :active-value="1" :inactive-value="0" />
          </el-form-item>
          <el-divider content-position="left">Default prescription (used in appointments)</el-divider>
          <el-row :gutter="12">
            <el-col :span="12">
              <el-form-item label="Quantity" label-width="90px">
                <el-input v-model="form.default_qty" placeholder="e.g. 30" />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="Dosage" label-width="90px">
                <el-input v-model="form.unit" placeholder="e.g. 20mg" />
              </el-form-item>
            </el-col>
          </el-row>
          <el-form-item label="Remarks">
            <el-input v-model="form.default_remarks" type="textarea" :rows="2" />
          </el-form-item>
        </el-form>
      </div>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogFormVisible = false">
          {{ $t("table.cancel") }}
        </el-button>
        <el-button type="primary" :loading="isProcessing" @click="addMedicine()">
          {{ $t("table.confirm") }}
        </el-button>
      </div>
    </el-dialog>
  </div>
</template>
<script>
import Medicines from '@/api/medicine';
import Pagination from '@/components/Pagination';
import waves from '@/directive/waves';

const emptyForm = () => ({
  id: null,
  brand_name: '',
  generic_name: '',
  isincluded: 1,
  default_qty: '',
  unit: '',
  default_remarks: '',
});

export default {
  name: 'Medicines',
  components: { Pagination },
  directives: { waves },
  data() {
    return {
      isEditForm: false,
      isProcessing: false,
      dialogFormVisible: false,
      loading: true,
      total: 0,
      query: {
        page: 1,
        limit: 15,
        keyword: '',
      },
      form: emptyForm(),
      item_list: null,
      rules: {
        generic_name: [{ required: true, message: 'Please provide generic name', trigger: 'blur' }],
      },
    };
  },
  created() {
    this.getMedicines();
  },
  methods: {
    formatDate(val) {
      if (!val) {
        return '—';
      }
      const d = new Date(val);
      if (Number.isNaN(d.getTime())) {
        return String(val).slice(0, 10);
      }
      return d.toLocaleDateString();
    },
    buildPayload() {
      return {
        id: this.form.id,
        medicine_name: this.form.brand_name,
        brand_name: this.form.brand_name,
        generic_name: this.form.generic_name,
        isincluded: this.form.isincluded,
        default_qty: this.form.default_qty || null,
        unit: this.form.unit || null,
        default_remarks: this.form.default_remarks || null,
      };
    },
    async getMedicines() {
      this.loading = true;
      const { data, meta } = await Medicines.list({
        params: this.query,
      });
      this.item_list = data;
      this.total = meta.total;
      this.loading = false;
    },
    handleFilter() {
      this.query.page = 1;
      this.getMedicines();
    },
    editMedicine(index, row) {
      this.isEditForm = true;
      this.form = {
        id: row.id,
        brand_name: row.brand_name || row.medicine || '',
        generic_name: row.generic_name || row.generic || '',
        isincluded: row.isincluded != null ? row.isincluded : 1,
        default_qty: row.default_qty || '',
        unit: row.unit || '',
        default_remarks: row.default_remarks || '',
      };
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['appForm'].clearValidate();
      });
    },
    handleCreate() {
      this.isEditForm = false;
      this.form = emptyForm();
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['appForm'].clearValidate();
      });
    },
    addMedicine() {
      this.$refs['appForm'].validate((valid) => {
        if (valid) {
          this.isProcessing = true;
          const payload = this.buildPayload();
          const url = this.isEditForm ? Medicines.update(payload) : Medicines.add(payload);
          url
            .then(() => {
              this.form = emptyForm();
              this.getMedicines();
              this.dialogFormVisible = false;
              this.$message({
                message: this.isEditForm
                  ? 'Medicine has been updated successfully.'
                  : 'Medicine has been created successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
            })
            .catch((err) => {
              console.error('Error saving medicine:', err);
            })
            .finally(() => {
              this.isProcessing = false;
            });
        }
      });
    },
    deleteMedicine(index, row) {
      Medicines.delete(row.id).then(() => {
        this.$message({
          message: 'Medicine has been deleted successfully.',
          type: 'success',
          duration: 5 * 1000,
        });
        this.getMedicines();
      })
        .catch((err) => {
          console.error('Error deleting medicine:', err);
        });
    },
  },
};
</script>
