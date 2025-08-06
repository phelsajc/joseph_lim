<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input
        v-model="query.keyword" :placeholder="$t('table.keyword')" style="width: 200px;" class="filter-item"
        @keyup.enter.native="handleFilter"
      />
      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
        {{ $t('table.search') }}
      </el-button>
      <router-link :to="'/masterfile/patient_form/0'">
        <el-button class="filter-item" type="success" icon="el-icon-add">
          {{ $t('table.add') }}
        </el-button>
      </router-link>
    </div>
    <el-table
      v-loading="loading" :data="patients" border fit highlight-current-row style="width: 100%"
      :default-sort="{ prop: 'patientname', order: 'descending' }" @row-click="selectRow"
    >
      <el-table-column type="index" width="50" />
      <el-table-column align="center" label="Photo" width="80">
        <template slot-scope="scope">
          <span>
            <el-image
              style="width: 50px; height: 50px" :src="scope.row.profile" :zoom-rate="1.2" :max-scale="7"
              :min-scale="0.2" :preview-src-list="[scope.row.profile]" :initial-index="4" fit="cover"
            />
          </span>
        </template>
      </el-table-column>

      <el-table-column prop="patientname" align="center" sortable label="Name">
        <template slot-scope="scope">
          <span>{{ scope.row.patientname }}</span>
        </template>
      </el-table-column>
      <!-- <el-table-column prop="city" align="center" sortable label="Location">
        <template slot-scope="scope">
          <span>{{ scope.row.city }}</span>
        </template>
      </el-table-column> -->
      <el-table-column align="center" label="Patient ID">
        <template slot-scope="scope">
          <span>{{ scope.row.patientid }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Birth Date">
        <template slot-scope="scope">
          <span>{{ scope.row.birthdate }}</span>
        </template>
      </el-table-column>
    </el-table>
    <pagination
      v-show="total > 0" :total="total" :page.sync="query.page" :limit.sync="query.limit"
      @pagination="getPatients"
    />
  </div>
</template>
<script>
import Patients from '@/api/patients';
import Pagination from '@/components/Pagination';
export default {
  name: 'Patients',
  components: { Pagination },
  data() {
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
    async getPatients() {
      this.loading = true;
      const { data, meta } = await Patients.list({
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
        if (type === 0) {
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
    generate() {
      Patients.generate_image().then((response) => {

      })
        .catch((err) => {
          console.error('Error adding suggestions:', err);
        });
    },
    generateatt() {
      Patients.generate_att().then((response) => {

      })
        .catch((err) => {
          console.error('Error adding suggestions:', err);
        });
    },
  },
};
</script>
