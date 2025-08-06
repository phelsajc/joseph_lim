<template>
  <div class="app-container">
    <el-form ref="form" :model="form" label-width="100px">
      <el-form-item label="Name">
        <el-input v-model="form.name" />
      </el-form-item>
      <el-form-item label="Specialization 1">
        <el-input v-model="form.spcl1" />
      </el-form-item>
      <el-form-item label="Specialization 2">
        <el-input v-model="form.spcl2" />
      </el-form-item>
      <el-form-item label="PRC No">
        <el-col :span="11">
        <el-input v-model="form.prc" />
        </el-col>
        <el-col :span="2" class="line">
          -
        </el-col>
        <el-col :span="11">
          <el-date-picker v-model="form.prc_validity" type="date" placeholder="Pick a date" style="width: 100%;" />
        </el-col>
      </el-form-item>
      <el-form-item label="PTR No">
        <el-col :span="11">
        <el-input v-model="form.ptr" />
        </el-col>
        <el-col :span="2" class="line">
          -
        </el-col>
        <el-col :span="11">
          <el-date-picker v-model="form.ptr_validity" type="date" placeholder="Pick a date" style="width: 100%;" />
        </el-col>
      </el-form-item>
      <el-form-item label="S2 No">
        <el-col :span="11">
        <el-input v-model="form.s2" />
        </el-col>
        <el-col :span="2" class="line">
          -
        </el-col>
        <el-col :span="11">
          <el-date-picker v-model="form.s2_validity" type="date" placeholder="Pick a date" style="width: 100%;" />
        </el-col>
      </el-form-item>
      <el-form-item label="Date Issued">
        <el-col :span="11">
          <el-date-picker v-model="form.date_issued" type="date" placeholder="Pick a date" style="width: 100%;" />
        </el-col>
      </el-form-item>
      <el-form-item>
            <el-upload action="#" ref="uploadRef" :limit="1" list-type="picture-card" :on-change="handleSuccess" :auto-upload="false">
              <el-icon><Plus /></el-icon>
            </el-upload>
          </el-form-item>
      <el-form-item>
      <div class="user-avatar box-center">
        <pan-thumb :image="form.pic" :height="'200px'" :width="'200px'" :hoverable="false" />
      </div>
        <div class="signature">
          <VueSignaturePad width="1700px" height="500px" ref="signaturePad" />
        </div>
      </el-form-item>
      <el-form-item>
        <div class="demo-image__lazy">
          <el-image :src="this.form.signatureData"  />
        </div>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="onSubmit">
          Create
        </el-button>
        <el-button type="warning" @click="clearSig()">
          Clear signature
        </el-button>
        <el-button type="danger" @click="removeSignature()">
          Remove signature
        </el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import Profile from '@/api/profile';
import PanThumb from '@/components/PanThumb';
import moment from "moment-timezone";
export default {
  components: { PanThumb },
  data() {
    return {
      form: {
        name: '',
        spcl1: '',
        spcl2: '',
        prc: '',
        prc_validity: '',
        ptr: '',
        ptr_validity: '',
        s2: '',
        s2_validity: '',
        signatureData: '',
        id: 1,
        pic: '',
        date_issued: '',
      },
    };
  },
  created() {
    this.getDetail();
  },
  methods: {
    onSubmit() {
      const signaturePad = this.$refs.signaturePad.signaturePad;
      if (!this.$refs.signaturePad.isEmpty()) {
        this.form.signatureData = signaturePad.toDataURL();
      }
      this.form.prc_validity = moment.tz(this.form.prc_validity, "Asia/Manila").format("YYYY-MM-DD");
      this.form.ptr_validity = moment.tz(this.form.ptr_validity, "Asia/Manila").format("YYYY-MM-DD");
      this.form.s2_validity = moment.tz(this.form.s2_validity, "Asia/Manila").format("YYYY-MM-DD");
      this.form.date_issued = moment.tz(this.form.date_issued, "Asia/Manila").format("YYYY-MM-DD");
      Profile.update(this.form).then((response) => {
        this.$message({
          message: 'Profile has been updated successfully.',
          type: 'success',
          duration: 5 * 1000,
        });
        this.getDetail();
      })
        .catch((err) => {
          console.error('Error:', err);
        });
    },
    onCancel() {
      this.$message({
        message: 'cancel!',
        type: 'warning',
      });
    },
    clearSig() {
      this.$refs.signaturePad.undoSignature();
    },
    removeSignature(){
      Profile.removeSignature(1).then((response) => {
        this.form.signatureData = '';
        this.$refs.signaturePad.undoSignature();
      })
        .catch((err) => {
          console.error('Error adding suggestions:', err);
        });
    },
    getDetail(){
      Profile.get().then((response) => {
        this.form.name = response.name;
        this.form.spcl1 = response.specialization1;
        this.form.spcl2 = response.specialization2;
        this.form.prc = response.prc;
        this.form.prc_validity = response.prc_validity;
        this.form.ptr = response.ptr;
        this.form.ptr_validity = response.ptr_validity;
        this.form.s2 = response.s2;
        this.form.s2_validity = response.s2_validity;
        this.form.date_issued = response.date_issued;
        this.form.signatureData = response.signature;
        this.form.pic = response.pic;
        
      })
        .catch((err) => {
          console.error('Error adding suggestions:', err);
        });
    },
    handleSuccess() {
      this.getBase64(this.$refs.uploadRef.uploadFiles[0].raw).then(base64 => {
        this.url = base64;
        this.form.pic = base64;
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

<style scoped>
.line{
  text-align: center;
}

.signature {
  border: double 3px transparent;
  border-radius: 5px;
  background-image: linear-gradient(white, white),
    radial-gradient(circle at top left, #4bc5e8, #9f6274);
  background-origin: border-box;
  background-clip: content-box, border-box;
}

.demo-image__lazy {
  height: 800px;
  overflow-y: auto;
}
.demo-image__lazy .el-image {
  display: block;
  min-height: 200px;
  margin-bottom: 10px;
}

</style>

