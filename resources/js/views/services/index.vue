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
      <el-table-column align="center" label="Services">
        <template slot-scope="scope">
          <span>{{ scope.row.services }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Fee">
        <template slot-scope="scope">
          <span>{{ scope.row.fee }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Actions">
        <template #default="scope">
          <el-button type="primary" @click="editService(scope.$index, scope.row)">
            Edit
          </el-button>
          <el-popconfirm confirm-button-text="Yes" cancel-button-text="No" icon-color="#626AEF"
            title="Do you want to delete this service?" @confirm="deleteService(scope.$index, scope.row)">
            <template #reference>
              <el-button type="danger"> Delete </el-button>
            </template>
          </el-popconfirm>
        </template>
      </el-table-column>
    </el-table>
    <pagination v-show="total > 0" :total="total" :page.sync="query.page" :limit.sync="query.limit"
      @pagination="getServices" />

    <el-dialog :title="'Add Item'" :visible.sync="dialogFormVisible" :close-on-click-modal="false"
      :close-on-press-escape="false">
      <div class="form-container">
        <el-form ref="appForm" :model="form" :rules="rules" label-position="left" label-width="150px"
          style="max-width: 500px">
          <el-form-item :label="'Service'" prop="service">
            <el-input v-model="form.service" />
          </el-form-item>

          <el-form-item :label="'Fee'" prop="fee">
            <el-input v-model.number="form.fee"/>
          </el-form-item>

        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">
            {{ $t("table.cancel") }}
          </el-button>
          <el-button type="primary" :loading="isProcessing" @click="addService()">
            {{ $t("table.confirm") }}
          </el-button>
        </div>
      </div>
    </el-dialog>
  </div>
</template>
<script>
import Services from '@/api/services';
import Pagination from '@/components/Pagination';
import waves from '@/directive/waves'; // Waves directive

export default {
  name: 'Services',
  components: { Pagination },
  directives: { waves },
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
        type: 1,
      },
      filter: {
        keyword: '',
      },
      form: {
        service: null,
        fee: null,
        id: null,
      },
      item_list: null,
      rules: {
        service: [{ required: true, message: 'Please provide service', trigger: 'blur' }],
        fee: [
          { required: true, message: 'Please provide fee', },
          { type: 'number' , message: 'Fee must be numeric'},
        ],
      },
    };
  },
  created() {
    this.getServices();
  },
  methods: {
    async getServices() {
      this.loading = true;
      const { data, meta } = await Services.list({
        params: this.query,
      });
      this.item_list = data;
      this.total = meta.total;
      this.loading = false;
    },
    handleFilter() {
      this.query.page = 1;
      this.getServices();
    },
    editService(index, row) {
      this.isEditForm = true;
      this.form.id = row.id;
      this.form.service = row.services;
      this.form.fee = row.fee;
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
      this.form.service = '';
      this.form.fee = '';
    },
    addService() {
      this.$refs['appForm'].validate((valid) => {
        if (valid) {
          this.isProcessing = true;
          const url = this.isEditForm ? Services.update(this.form) : Services.add(this.form);
          url
            .then((response) => {
              this.form.id = null;
              this.form.description = '';
              this.form.fee = 0;
              this.getServices();
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
    deleteService(index, row) {
      Services.delete(row.id).then((response) => {
        this.$message({
          message: 'Item has been deleted successfully.',
          type: 'success',
          duration: 5 * 1000,
        });
        this.getServices();
      })
        .catch((err) => {
          console.error('Error adding suggestions:', err);
        });
    },
  },
};
</script>
