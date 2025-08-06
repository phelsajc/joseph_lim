<template>
  <el-card v-if="user.name">
    <div class="user-profile">
      <div class="user-avatar box-center">
        <pan-thumb :image="form.img" :height="'200px'" :width="'200px'" :hoverable="false" />
      </div>
      <div class="box-center">
        <div class="user-name text-center">
          {{ profile.patientname }}
        </div>
      </div>
      <div class="box-social">
        <el-table :data="social" :show-header="false">
          <el-table-column prop="name" label="Name" />
          <el-table-column label="Count" align="left" width="200">
            <template slot-scope="scope">
              {{ scope.row.count }}
            </template>
          </el-table-column>
        </el-table>
      </div>
      <div class="user-follow">
        <el-upload
          ref="upload"
          class="upload-demo"
          :file-list="fileList"
          :on-change="handleChange"
          :limit="1"
          :auto-upload="false"
        >
          <el-button slot="trigger" size="small" type="primary">Select File</el-button>
          <div slot="tip" class="el-upload__tip">Only one file can be uploaded at a time</div>
        </el-upload>
      </div>
    </div>
  </el-card>
</template>

<script>

import PanThumb from '@/components/PanThumb';
import moment from 'moment-timezone';
export default {
  components: { PanThumb },
  props: {
    user: {
      type: Object,
      default: () => {
        return {
          name: '',
          email: '',
          avatar: '',
          roles: [],
        };
      },
    },
    profile: {
      type: Object,
      default: () => {
        return {
          patientname: '',
          birthdate: '',
          address: '',
        };
      },
    },
  },
  data() {
    return {
      form: {
        img: '',
      },
      a: {},
      fname: '',
      social: [
        {
          'name': 'Brithdate',
          'count': 'ff',
        },
        {
          'name': 'Age',
          'count': '',
        },
        {
          'name': 'Contact Number',
          'count': '',
        },
      ],
    };
  },
  watch: {
    // Watch for changes in the inputNumber prop
    profile: {
      handler(newValue) {
        // Recompute the modified number whenever the inputNumber prop changes
        this.fname = newValue.patientname;
        this.social[0]['count'] = moment(newValue.birthdate).format('MMMM DD, YYYY');
        this.social[1]['count'] = this.getAge(newValue.birthdate);
        this.social[2]['count'] = newValue.contactno;
        this.form.img = newValue.isold_patient === 1 ? '/public/photos/' + newValue.patientid + '.jpg' : newValue.profile;
      },
      immediate: true, // Call handler immediately with the current value
    },
  },
  created(){
  },
  mounted() {
    this.a = this.profile;
  },
  methods: {
    getRole() {
      const roles = this.user.roles.map(value => this.$options.filters.uppercaseFirst(value));
      return roles.join(' | ');
    },
    getAge(dateString) {
      var today = new Date();
      var birthDate = new Date(dateString);
      var age = today.getFullYear() - birthDate.getFullYear();
      var m = today.getMonth() - birthDate.getMonth();
      if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
      }
      return age;
    },
    handleChange(file, fileList) {
      this.fileList = fileList.slice(-1); // Ensure only one file is selected
      this.$emit('return-img', this.fileList);
      this.$store.dispatch('globalvar/changeval', {
        key: 'img_val',
        value: this.fileList,
      });
    },
    handleSuccess() {
      this.getBase64(this.$refs.uploadRef.uploadFiles[0].raw).then(base64 => {
        this.form.img = base64;
        this.$emit('return-img', base64);
        this.$store.dispatch('globalvar/changeval', {
          key: 'img_val',
          value: base64,
        });
      });
    },
    getBase64(file) {
      return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = () => resolve(reader.result);
        reader.onerror = error => reject(error);
      });
    },
  },
};
</script>

<style lang="scss" scoped>
.user-profile {
  .user-name {
    font-weight: bold;
  }
  .box-center {
    padding-top: 10px;
  }
  .user-role {
    padding-top: 10px;
    font-weight: 400;
    font-size: 14px;
  }
  .box-social {
    padding-top: 30px;
    .el-table {
      border-top: 1px solid #dfe6ec;
    }
  }
  .user-follow {
    padding-top: 20px;
  }
}
</style>
