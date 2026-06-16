<template>
  <div class="app-container">
    <user-activity
      v-if="user && user.name"
      :user="user"
      :profile="usrprofile"
      :image="img"
      @return-img="getImg"
    />
  </div>
</template>

<script>
import UserActivity from './components/UserActivity';
import Patients from '@/api/patients';

export default {
  name: 'SelfProfile',
  components: { UserActivity },
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
