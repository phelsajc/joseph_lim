<template>
  <div class="app-container">
    <el-form v-if="user" :model="user">
      <el-row :gutter="10">
        <el-col :span="6">
          <user-card :user="user" :profile="usrprofile" @return-img="getImg" />
          <!-- <user-bio /> -->
        </el-col>
        <el-col :span="18">
          <user-activity :user="user" :profile="usrprofile" :image="img" />
        </el-col>
      </el-row>
    </el-form>
  </div>
</template>

<script>
// import UserBio from './components/UserBio';
import UserCard from './components/UserCard';
import UserActivity from './components/UserActivity';
import Patients from '@/api/patients';

export default {
  name: 'SelfProfile',
  components: { /* UserBio, */ UserCard, UserActivity },
  data() {
    return {
      user: {},
      usrprofile: {},
      img: '',
    };
  },
  watch: {
    '$route': ['getUser', 'getpatient'],
  },
  created() {
    this.getUser();
    this.getpatient();
  },
  methods: {
    async getUser() {
      const data = await this.$store.dispatch('user/getInfo');
      this.user = data;
    },
    async getpatient() {
      await Patients.getpatient(this.$route.params.id).then((response) => {
        this.usrprofile = response;
      })
        .catch((err) => {
          console.error('Error adding suggestions:', err);
        });
    },
    getImg: function(id){
      this.img = id;
    },
  },
};
</script>
