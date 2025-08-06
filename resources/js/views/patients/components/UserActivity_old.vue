<template>
  <el-card v-if="user.name">
    <div class="mb-4">
      <el-popconfirm
        confirm-button-text="Yes"
        cancel-button-text="No"
        icon-color="#626AEF"
        title="Do you want to proceed?"
        @confirm="onSubmit"
      >
        <template #reference>
          <el-button type="primary">
            Update
          </el-button>
        </template>
      </el-popconfirm>
      <el-button type="warning" @click="printChart()">
        Print Chart
      </el-button>
    </div>
    <el-tabs v-model="activeActivity" @tab-click="handleClick">
      <el-tab-pane v-loading="updating" label="Patiennt Profile" name="first">
        <!-- <el-form-item label="Name">
          <el-input v-model="user.name" :disabled="user.role === 'admin'" />
        </el-form-item>
        <el-form-item label="Email">
          <el-input v-model="user.email" :disabled="user.role === 'admin'" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" :disabled="user.role === 'admin'" @click="onSubmit">
            Update
          </el-button>
        </el-form-item> -->
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
          <!-- <el-form-item>
            <el-upload
              ref="uploadRef"
              action="#"
              :auto-upload="false"
              :limit="1"
              :on-change="afterSubmit"
              :on-success="handleSuccess">
              <el-button size="small" type="primary">Click to upload</el-button>
            </el-upload>
          </el-form-item>
          <el-form-item>
            <div class="demo-fit">
              <div class="block">
                <el-avatar shape="square" :size="100" fit="cover" :src="url" />
              </div>
            </div>
          </el-form-item> -->
          <!-- <el-form-item>
            <div class="demo-fit">
              <div class="block">
                <el-avatar shape="square" :size="100" fit="cover" :src="url" />
              </div>
            </div>
          </el-form-item>  -->
          <!-- <el-form-item>
            <el-upload action="#" ref="uploadRef" :limit="1" list-type="picture-card" :on-change="handleSuccess" :auto-upload="false">
              <el-icon><Plus /></el-icon>
            </el-upload>
          </el-form-item>  -->
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
          <!-- <el-form-item label="Referred by">
              <el-input v-model="form.referredby" autosize clearable ></el-input>
            </el-form-item>
          <el-form-item label="Referred by">
              <el-input type="textarea" v-model="form.remarks" autosize clearable ></el-input>
          </el-form-item>
            <el-form-item label="Additional Info">
            <el-input type="textarea" v-model="form.remarks" autosize clearable ></el-input>
          </el-form-item> -->
        </el-form>
      </el-tab-pane>
      <el-tab-pane label="Past Medical History" name="third">
        <div class="user-activity">
            <el-form :inline="true" label-position="top" class="demo-form-inline">
                <el-form-item label="History">
                  <el-checkbox-group v-model="form.pmh" size="large">
                    <el-checkbox-button label="Hypertension">
                      Hypertension
                    </el-checkbox-button>
                    <el-checkbox-button label="Previous MI">
                       Previous MI
                    </el-checkbox-button>
                    <el-checkbox-button :checked="past_meds.some( ai => form.pmh.includes(ai))" label="Bronchial Asthma">
                      Bronchial Asthma
                    </el-checkbox-button>
                    <el-checkbox-button label="Allergies">
                       Allergies
                    </el-checkbox-button>
                    <el-checkbox-button label="Prior Surgery">
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
        </div>
      </el-tab-pane>
      <el-tab-pane label="Family History" name="second">
        <div class="block">
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
        </div>
      </el-tab-pane>
      <el-tab-pane label="Social / Environment History" name="soc">
        <div class="block">
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
        </div>
      </el-tab-pane>
      <el-tab-pane label="Past Consultations" name="socenv">
        <el-card style="max-width: 100%">
          <el-table v-loading="loading" :data="patients_history" border fit highlight-current-row style="width: 100%" @row-click="selectRow">
            <el-table-column align="center" label="Date of Visit" width="110">
              <template slot-scope="scope">
                <span>{{ scope.row.date }}</span>
              </template>
            </el-table-column>
            <el-table-column align="center" label="Chief Complaints">
              <template slot-scope="scope">
                <span>{{ scope.row.cf }}</span>
              </template>
            </el-table-column>
          </el-table>
        </el-card>
      </el-tab-pane>
      <el-tab-pane label="Attachments" name="attachments">
        <el-upload
              ref="uploadRef"
              action="#"
              :auto-upload="false"
              :limit="1"
              :on-change="handleSuccess"
              :on-success="handleSuccess">
            <el-button size="small"  style="width:100%" type="info">Upload attachments</el-button>
        </el-upload>
        <el-card style="max-width: 100%">
          <el-row :gutter="20">
            <el-col :span="6" v-for="(image, index) in attachments" :key="index">
              <el-card shadow="always" @click="openDialog(image)">
                <el-image
                  :src="imgSrc(image.newfile,image.oldfile,image.type)"
                  :alt="image.alt"
                  fit="cover"
                  class="image"
                  :preview-src-list="[imgSrc(image.newfile,image.oldfile,image.type)]"
                />
                <div style="padding: 14px;">
                  <el-button type="danger" icon="el-icon-delete" @click="deleteAtt(image.id)">
                   
                  </el-button>
                </div>
              </el-card>
            </el-col>
          </el-row>
          <el-dialog :visible.sync="dialogVisible" width="50%">
            <el-image
              :src="selectedImage.file"
              :alt="selectedImage.alt"
              fit="contain"
              class="popup-image"
            />
            <span slot="footer" class="dialog-footer">
              <el-button @click="dialogVisible = false">Close</el-button>
            </span>
          </el-dialog>
        </el-card>
      </el-tab-pane>
      <el-tab-pane label="old Records" name="oldrecords">
        <el-table :data="old_records">
          <el-table-column prop="hpi" label="HPI" />
          <!-- <el-table-column prop="pmhx" label="pmHx"  /> -->
          <el-table-column prop="desc" label="Description" />
          <el-table-column prop="date" label="Date" />
          <!-- <el-table-column prop="cc" label="CC" /> -->
          <el-table-column prop="recom" label="Recommendation" />
        </el-table>
      </el-tab-pane>
    </el-tabs>
  </el-card>
</template>

<script>
import Patients from '@/api/patients';
export default {
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
    image: {
      type: String,
      default: '',
    },
  },
  data() {
    return {
      old_records: [],
      dialogVisible: false,
      selectedImage: {},
      attachments: [],
      patients_history: [],
      pid: '',
      past_meds: ['Bronchial Asthma', 'Hypertension', 'Previous MI', 'Allergies', 'Prior Surgery', 'Prior Angina', 'PAD', 'PTB', 'Previous Myocardial infraction', 'Dyslipidemia', 'COPD'],
      form: {
        patientid: '',
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
      },
      patientid_id: 0,
      form_att: {
        patientid: this.$route.params.pid,
        //patientid: this.$route.params.id,
        file: '',
      },
      activeActivity: 'first',
      updating: false,
    };
  },
  watch: {
    // Watch for changes in the inputNumber prop
    profile: {
      handler(newValue) {
        this.patientid_id = newValue.id;
        this.form.patientid = newValue.patientid;
        this.form.firstname = newValue.firstname;
        this.form.middlename = newValue.middlename;
        this.form.lastname = newValue.lastname;
        this.form.address = newValue.address;
        this.form.birthdate = newValue.birthdate;
        this.form.contactno = newValue.contactno;
        this.form.occupation = newValue.occupation;
        this.form.sex = newValue.sex;
        this.form.civil_status = newValue.civil_status;
        this.form.referredby = newValue.referredby;
        /* this.form.firstname = newValue.firstname
        this.form.firstname = newValue.firstname
        this.form.firstname = newValue.firstname */
        const pmh = newValue.pmh.split(',');
        pmh.forEach(element => {
          this.form.pmh.push(element);
        });
        this.form.pmh_others = newValue.pmh_others;
        const fam = newValue.fam.split(',');
        fam.forEach(element => {
          this.form.fam.push(element);
        });
        this.form.fam_others = newValue.fam_others;
        const soc = newValue.soc.split(',');
        soc.forEach(element => {
          this.form.soc.push(element);
        });
        this.form.soc_others = newValue.soc_others;
      },
      immediate: true, // Call handler immediately with the current value
    },
    image: {
      handler(v) {
        this.form.profile = v;
      },
      immediate: true,
    },
    patientidpx: {
      handler(v) {
      },
      immediate: true,
    },
  },
  created(){
    this.past_consult();
    this.get_attachments();
  },
  methods: {
    openDialog(image) {
      this.selectedImage = image;
      this.dialogVisible = true;
    },
    handleClick(tab, event) {
    },
    onSubmit() {
      Patients.update(this.form).then((response) => {
        this.$message({
          message: 'Patient profile has been updated successfully.',
          type: 'success',
          duration: 5 * 1000,
        });
      })
        .catch((err) => {
          console.error('Error adding suggestions:', err);
        });
    },
    past_consult() {
      Patients.getpatientpastconsult(this.$route.params.pid).then((response) => {
        this.patients_history = response.data;
        this.old_records = response.get_OldDiagnosis;
      })
        .catch((err) => {
          console.error('Error adding suggestions:', err);
        });
    },
    get_attachments() {
        this.attachments = [];
      Patients.getAttachments(this.$route.params.pid).then((response) => {
        this.attachments = response.data;
      })
        .catch((err) => {
          console.error('Error adding suggestions:', err);
        });
    },
    selectRow(row) {
      this.$router.push({ path: '/appointments/form/' + row.id });
    },
    handleSuccess() {
      this.getBase64(this.$refs.uploadRef.uploadFiles[0].raw).then(base64 => {
        this.form_att.file = base64;

        Patients.addAttachments(this.form_att).then((response) => {
          this.form_att.file = '';
          this.get_attachments();
          this.$refs.uploadRef.clearFiles();
        })
          .catch((err) => {
            console.error('Error adding suggestions:', err);
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
    imgSrc(newfile, oldfile, isold) {
      try {
        if (isold === 0){
          return newfile;
        } else {
          return `public/${oldfile}`;
        }
      } catch (e) {
        return `public/${oldfile}`;
      }
    },
    deleteAtt(id){
      this.$confirm('Are you done with this file?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        Patients.deleteAttachments(id).then((response) => {
          this.$message({
            type: 'success',
            message: 'Deleted File',
          });
          this.get_attachments();
        })
          .catch((err) => {
            console.error('Error adding suggestions:', err);
          });
      }).catch(() => {
        this.$message({
          type: 'info',
          message: 'Canceled action.',
        });
      });
    },
    printChart(){
      window.open('/api/printchart/' + this.$route.params.pid);
    },
  },
};
</script>

<style lang="scss" scoped>
.user-activity {
  .user-block {
    .username, .description {
      display: block;
      margin-left: 50px;
      padding: 2px 0;
    }
    img {
      width: 40px;
      height: 40px;
      float: left;
    }
    :after {
      clear: both;
    }
    .img-circle {
      border-radius: 50%;
      border: 2px solid #d2d6de;
      padding: 2px;
    }
    span {
      font-weight: 500;
      font-size: 12px;
    }
  }
  .post {
    font-size: 14px;
    border-bottom: 1px solid #d2d6de;
    margin-bottom: 15px;
    padding-bottom: 15px;
    color: #666;
    .image {
      width: 100%;
    }
    .user-images {
      padding-top: 20px;
    }
  }
  .list-inline {
    padding-left: 0;
    margin-left: -5px;
    list-style: none;
    li {
      display: inline-block;
      padding-right: 5px;
      padding-left: 5px;
      font-size: 13px;
    }
    .link-black {
      &:hover, &:focus {
        color: #999;
      }
    }
  }
  .el-carousel__item h3 {
    color: #475669;
    font-size: 14px;
    opacity: 0.75;
    line-height: 200px;
    margin: 0;
  }

  .el-carousel__item:nth-child(2n) {
    background-color: #99a9bf;
  }

  .el-carousel__item:nth-child(2n+1) {
    background-color: #d3dce6;
  }
}
</style>
