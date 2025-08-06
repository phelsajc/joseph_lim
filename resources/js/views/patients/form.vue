<template>
  <div class="app-container">
    <div class="mb-4">
      <el-popconfirm
        confirm-button-text="Yes"
        cancel-button-text="No"
        icon-color="#626AEF"
        title="Do you want to proceed?"
        @confirm="onSubmit"
        @cancel="cancelEvent"
      >
        <template #reference>
          <el-button type="primary">
            Save
          </el-button>
        </template>
      </el-popconfirm>
    </div>
    <br>
    <el-tabs
      v-model="tab"
      type="card"
      class="demo-tabs"
    >
      <el-tab-pane label="Perosnal Information" name="first">
        <el-form :inline="true" label-position="right" class="demo-form-inline">
          <el-form-item label="Last Name">
            <el-input v-model="form.lastname" autosize clearable />
          </el-form-item>
          <el-form-item label="First Name">
            <el-input v-model="form.firstname" clearable />
          </el-form-item>
          <el-form-item label="Middle Name">
            <el-input v-model="form.middlename" clearable />
          </el-form-item>
          <el-form-item>
            <el-upload action="#" ref="uploadRef" :limit="1" list-type="picture-card" :on-change="handleSuccess" :auto-upload="false">
              <el-icon><Plus /></el-icon>
            </el-upload>
          </el-form-item>
        </el-form>
        <el-form label-width="120px" class="demo-form-inline">
          <el-form-item label="Home Address">
            <el-input v-model="form.address" type="textarea" />
          </el-form-item>
        </el-form>
        <el-form :inline="true" label-position="right" class="demo-form-inline">
          <el-form-item label="Contact No">
            <el-input v-model="form.contactno" autosize clearable />
          </el-form-item>
          <el-form-item label="Email">
            <el-input v-model="form.email" autosize clearable />
          </el-form-item>
          <el-form-item label="Occupation">
            <el-input v-model="form.occupation" clearable />
          </el-form-item>
          <el-form-item label="Birth day">
            <el-date-picker v-model="form.birthdate" type="date" placeholder="Pick a day" />
          </el-form-item>
          <el-form-item label="Gender">
            <el-select
              v-model="form.sex"
              placeholder="Select"
              size="large"
              style="width: 240px"
            >
              <el-option
                label="Female"
                value="2"
              />
              <el-option
                label="Male"
                value="1"
              />
            </el-select>
          </el-form-item>
          <el-form-item label="Status">
            <el-select
              v-model="form.civil_status"
              placeholder="Select"
              size="large"
              style="width: 240px"
            >
              <el-option
                label="Single"
                value="Single"
              />
              <el-option
                label="Married"
                value="Married"
              />
              <el-option
                label="Widowed"
                value="Widowed"
              />
              <el-option
                label="Legally Separated"
                value="Legally Separated"
              />
            </el-select>
          </el-form-item>
        </el-form>
        <el-form label-width="120px">
          <el-row :gutter="20">
            <el-col :span="6">
              <el-form-item label="Referred by">
                <el-input v-model="form.referredby" autosize clearable ></el-input>
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="Additional Info">
                <el-input type="textarea" v-model="form.remarks" autosize clearable ></el-input>
              </el-form-item>
            </el-col>
          </el-row>
        </el-form>
      </el-tab-pane>
      <!-- <el-tab-pane label="Past Medical History" name="pmh">
        <el-card style="max-width: 100%">
            <el-form :inline="true" label-position="top" class="demo-form-inline">
                <el-form-item label="History">
                  <el-checkbox-group v-model="form.pmh" size="large">
                    <el-checkbox-button label="Hypertension">
                      Hypertension
                    </el-checkbox-button>
                    <el-checkbox-button label="Previous MI">
                       Previous MI
                    </el-checkbox-button>
                    <el-checkbox-button label="Bronchial Asthma ">
                      Bronchial Asthma
                    </el-checkbox-button>
                    <el-checkbox-button label="Allergies">
                       Allergies
                    </el-checkbox-button>
                    <el-checkbox-button label="Prior Surgery ">
                       Prior Surgery
                    </el-checkbox-button>
                    <el-checkbox-button label="Prior Angina">
                       Prior Angina
                    </el-checkbox-button>
                    <el-checkbox-button label="PAD">
                       PAD
                    </el-checkbox-button>
                    <el-checkbox-button label="PTB">
                        PTB
                    </el-checkbox-button>
                    <el-checkbox-button label="Previous Myocardial Infraction">
                       Previous Myocardial Infraction
                    </el-checkbox-button>
                    <el-checkbox-button label="DyslipidemiaPAD">
                        Dyslipidemia
                    </el-checkbox-button>
                    <el-checkbox-button label="COPD">
                        COPD
                    </el-checkbox-button>
                  </el-checkbox-group>
                </el-form-item>
                <el-form-item label="Others">
                  <el-input v-model="form.pmh_others" :autosize="{ minRows: 2, maxRows: 4 }" style="width: 540px" :rows="2" type="textarea" placeholder="Please input" />
                </el-form-item>
            </el-form>
        </el-card>
      </el-tab-pane>
      <el-tab-pane label="Family History" name="famhis">
        <el-card style="max-width: 100%">
            <el-form :inline="true" label-position="top" class="demo-form-inline">
                <el-form-item label="History">
                  <el-checkbox-group v-model="form.fam" size="large">
                    <el-checkbox-button label="Hypertension">
                      Hypertension
                    </el-checkbox-button>
                    <el-checkbox-button label="Diabetes Mellitus">
                      Diabetes Mellitus
                    </el-checkbox-button>
                    <el-checkbox-button label="Stroke">
                      Stroke
                    </el-checkbox-button>
                    <el-checkbox-button label="CAD">
                       CAD
                    </el-checkbox-button>
                  </el-checkbox-group>
                </el-form-item>
                <el-form-item label="Others">
                  <el-input v-model="form.fam_others" :autosize="{ minRows: 2, maxRows: 4 }" style="width: 540px" :rows="2" type="textarea" placeholder="Please input" />
                </el-form-item>
            </el-form>
        </el-card>
      </el-tab-pane>
      <el-tab-pane label="Social Environment" name="socenv">
        <el-card style="max-width: 100%">
            <el-form :inline="true" label-position="top" class="demo-form-inline">
                <el-form-item label="History">
                  <el-checkbox-group v-model="form.soc" size="large">
                    <el-checkbox-button label="Smoking">
                      Smoking
                    </el-checkbox-button>
                    <el-checkbox-button label="Alcoholic Beverage Drinking">
                      Alcoholic Beverage Drinking
                    </el-checkbox-button>
                  </el-checkbox-group>
                </el-form-item>
                <el-form-item label="Others">
                  <el-input v-model="form.soc_others" :autosize="{ minRows: 2, maxRows: 4 }" style="width: 540px" :rows="2" type="textarea" placeholder="Please input" />
                </el-form-item>
            </el-form>
        </el-card>
      </el-tab-pane> -->
    </el-tabs>
  </div>
</template>
<script>
import Patients from '@/api/patients';
export default {
  data() {
    return {
      url: '',
      tab: 'first',
      form: {
        pmh: [],
        pmh_others: '',
        fam: [],
        fam_others: '',
        soc: [],
        soc_others: '',
        firstname: '',
        middlename: '',
        lastname: '',
        contactno: null,
        birthdate: '',
        id: this.$route.params.id,
        sex: '',
        civil_status: '',
        oscaid: '',
        referredby: '',
        remarks: '',
        address: '',
        occupation: '',
        profile: '',
        isold_patient: 0,
        email:'',
      },
    };
  },
  created() {
  },
  methods: {
    handleSuccess() {
      this.getBase64(this.$refs.uploadRef.uploadFiles[0].raw).then(base64 => {
        this.url = base64;
        this.form.profile = base64;
      });
    },
    afterSubmit() {
      /* console.log(123)
      console.log(this.$refs.uploadRef.uploadFiles[0]) */
      this.getBase64(this.$refs.uploadRef.uploadFiles[0].raw).then(base64 => {
        this.url = base64;
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
    onSubmit() {
      Patients.add(this.form).then((response) => {
        this.$message({
          message: 'Patient information has been created successfully.',
          type: 'success',
          duration: 5 * 1000,
        });
        this.$router.push({ path: '/masterfile/profile/' + response.id + '/' + response.patientid });
      })
        .catch((err) => {
          console.error('Error adding suggestions:', err);
        });
    },
  },
};
</script>
<style type="style/scss">
@media (max-width: 768px) {
  .el-form-item__label[style] {
    text-align: unset !important;
    width: 100% !important;
  }
  .el-form-item__content[style] {
    margin-left: unset !important;
  }
}
.container {
  max-width: 100%; /* Ensures container's width adjusts responsively */
}

.el-form-item {
  margin-right: 20px; /* Adjust margin as needed */
}

.responsive-textarea .el-input__inner {
  width: 100%; /* Ensures textarea takes full width of form item */
}
.demo-fit {
  display: flex;
  text-align: center;
  justify-content: space-between;
}
.demo-fit .block {
  flex: 1;
  display: flex;
  flex-direction: column;
  flex-grow: 0;
}

.demo-fit .title {
  margin-bottom: 10px;
  font-size: 14px;
  color: var(--el-text-color-secondary);
}
</style>
