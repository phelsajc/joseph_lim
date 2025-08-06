<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="query.keyword" :placeholder="$t('table.keyword')" style="width: 200px;" class="filter-item" @keyup.enter.native="handleFilter" />
      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
        {{ $t('table.search') }}
      </el-button>
      <!-- <el-button class="filter-item" style="margin-left: 10px;" type="success" icon="el-icon-plus" @click="handleCreate">
        {{ $t('table.add') }}
      </el-button> -->
    </div>
    <el-table v-loading="loading" :data="patients" border fit highlight-current-row style="width: 100%" @row-click="selectRow">
      <el-table-column align="center" label="ID" width="80">
        <template slot-scope="scope">
          <span>{{ scope.row.id }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Medicine">
        <template slot-scope="scope">
          <span>{{ scope.row.medicine }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Description">
        <template slot-scope="scope">
          <span>{{ scope.row.desc }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Unit">
        <template slot-scope="scope">
          <span>{{ scope.row.unit }}</span>
        </template>
      </el-table-column>
    </el-table>
    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getPatients" />
  </div>
</template>
<script>
import Medicine from '@/api/medicine';
import Pagination from '@/components/Pagination';
export default {
  name: 'Patients',
  components: { Pagination },
  data(){
    return {
      loading: true,
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        role: '',
      },
      patients: null,
    };
  },
  created() {
    this.getPatients();
  },
  methods: {
    async getPatients(){
      this.loading = true;
      const { data, meta } = await Medicine.list({
        params: this.query,
      });
      this.patients = data;
      this.total = meta.total;
      this.loading = false;
    },
    handleFilter() {
      this.query.page = 1;
      this.getPatients();
    },
    selectRow(row) {
      this.$router.push({ path: '/masterfile/profile/' + row.id + '/' + row.patientid });
    },
    imgSrc(profile, id, type) {
      try {
        if (type === 0){
          return profile;
        } else {
          return `public/photos/${id}.jpg`;
        }
      } catch (e) {
        return `public/photos/${id}.jpg`;
      }
      // return file;
    },
    imageLoadError(id) {
      this.imgSrc(id, 'png');
    },
  },
};
</script>
