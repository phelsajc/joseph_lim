<template>
  <div>
  <p>Todays appointments</p>
  <br>
    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
      <el-table-column type="index" width="50" />
      <el-table-column align="center" label="Name">
        <template slot-scope="scope">
          <span>{{ scope.row.patient }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Complaints">
        <template slot-scope="scope">
          <span>{{ scope.row.complaints }}</span>
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>

<script>
export default {
  props: {
    patients: {
      type: Object,
      default: () => {
        return {
          patient: '',
          complaints: '',
        };
      },
    },
  },
  data() {
    return {
      list: [],
      loading: true,
    };
  },
  watch: {
    patients: {
      handler(newValue) {
        this.list = newValue;
      },
      immediate: true,
    },
  },
  created() {
    this.getData();
  },
  methods: {
    getData(){
      this.loading = false;
      return this.list;
    }
  },
};
</script>
