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
      <el-table-column align="center" label="ID" width="80">
        <template slot-scope="scope">
          <span>{{ scope.row.id }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Brand">
        <template slot-scope="scope">
          <span>{{ scope.row.medicine }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Generic">
        <template slot-scope="scope">
          <span>{{ scope.row.generic }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Actions">
        <template #default="scope">
          <el-button type="primary" @click="editMedicine(scope.$index, scope.row)">
            Edit
          </el-button>
          <el-popconfirm confirm-button-text="Yes" cancel-button-text="No" icon-color="#626AEF"
            title="Do you want to delete this medicine?" @confirm="deleteMedicine(scope.$index, scope.row)">
            <template #reference>
              <el-button type="danger"> Delete </el-button>
            </template>
          </el-popconfirm>
        </template>
      </el-table-column>
    </el-table>
    <pagination v-show="total > 0" :total="total" :page.sync="query.page" :limit.sync="query.limit"
      @pagination="getMedicines" />

    <el-dialog :title="'Add Item'" :visible.sync="dialogFormVisible" :close-on-click-modal="false"
      :close-on-press-escape="false">
      <div class="form-container">
        <el-form ref="appForm" :model="form" :rules="rules" label-position="left" label-width="150px"
          style="max-width: 500px">
          <el-form-item :label="'Brand'" prop="medicine_name">
            <el-input v-model="form.medicine_name" />
          </el-form-item>
          <el-form-item :label="'Generic'" prop="generic_name">
            <el-input v-model="form.generic_name" />
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">
            {{ $t("table.cancel") }}
          </el-button>
          <el-button type="primary" :loading="isProcessing" @click="addMedicine()">
            {{ $t("table.confirm") }}
          </el-button>
        </div>
      </div>
    </el-dialog>
  </div>
</template>
<script>
import Medicines from '@/api/medicine';
import Pagination from '@/components/Pagination';
export default {
  name: 'Medicines',
  components: { Pagination },
  data() {
    return {
      isEditForm: false,
      isProcessing: false,
      dialogFormVisible: false,
      loading: true,
      value: '',
      total: 0,
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        role: '',
      },
      filter: {
        keyword: '',
      },
      form: {
        medicine_name: null,
        generic_name: null,
        id: null,
      },
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
      this.form.id = row.id;
      this.form.medicine_name = row.medicine;
      this.form.generic_name = row.generic;
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['appForm'].clearValidate();
      });
    },
    handleCreate() {
      this.isEditForm = false;
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['appForm'].clearValidate();
      });
      this.form.id = null;
      this.form.medicine_name = '';
      this.form.generic_name = '';
    },
    addMedicine() {
      this.$refs['appForm'].validate((valid) => {
        if (valid) {
          this.isProcessing = true;
          const url = this.isEditForm ? Medicines.update(this.form) : Medicines.add(this.form);
          url
            .then((response) => {
              this.form.id = null;
              this.form.generic_name = '';
              this.form.medicine_name = 0;
              this.getMedicines();
              this.dialogFormVisible = false;
              this.$message({
                message: 'Appointment has been created successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
            })
            .catch((err) => {
              console.error('Error adding suggestions:', err);
            })
            .finally(() => {
              this.isProcessing = false;
            });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    deleteMedicine(index, row) {
      Medicines.delete(row.id).then((response) => {
        this.$message({
          message: 'Item has been deleted successfully.',
          type: 'success',
          duration: 5 * 1000,
        });
        this.getMedicines();
      })
        .catch((err) => {
          console.error('Error adding suggestions:', err);
        });
    },
  },
};
</script>
