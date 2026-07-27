<template>
  <div class="patient-form-page app-container">
    <div class="patient-form-header">
      <div class="patient-form-header__text">
        <h1 class="patient-form-title">
          {{ pageTitle }}
        </h1>
        <p class="patient-form-subtitle">
          {{ pageSubtitle }}
        </p>
      </div>
      <div class="patient-form-header__actions">
        <el-popconfirm
          confirm-button-text="Yes"
          cancel-button-text="No"
          icon-color="#409EFF"
          title="Save this patient record?"
          @confirm="onSubmit"
          @cancel="cancelEvent"
        >
          <template #reference>
            <el-button
              type="primary"
              icon="el-icon-check"
              :loading="saving"
              :disabled="saving"
            >
              Save patient
            </el-button>
          </template>
        </el-popconfirm>
      </div>
    </div>

    <el-card
      class="patient-form-card"
      shadow="hover"
      :body-style="{ padding: '0' }"
    >
      <div class="patient-form-body">
        <el-form
          ref="patientForm"
          :model="form"
          :rules="rules"
          label-position="top"
          class="patient-form"
        >
          <el-row :gutter="24">
            <el-col :xs="24" :sm="12" :md="8">
              <el-form-item label="Last name" prop="lastname">
                <el-input
                  v-model="form.lastname"
                  clearable
                  placeholder="Family name"
                  autocomplete="family-name"
                />
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="8">
              <el-form-item label="First name" prop="firstname">
                <el-input
                  v-model="form.firstname"
                  clearable
                  placeholder="Given name"
                  autocomplete="given-name"
                />
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="8">
              <el-form-item label="Middle name">
                <el-input
                  v-model="form.middlename"
                  clearable
                  placeholder="Optional"
                  autocomplete="additional-name"
                />
              </el-form-item>
            </el-col>

            <el-col :span="24">
              <el-form-item label="Photo">
                <div class="patient-form-upload">
                  <el-upload
                    ref="uploadRef"
                    action="#"
                    :limit="1"
                    list-type="picture-card"
                    :on-change="handleSuccess"
                    :on-remove="handlePhotoRemove"
                    :auto-upload="false"
                  >
                    <i class="el-icon-plus patient-form-upload__icon" />
                  </el-upload>
                </div>
              </el-form-item>
            </el-col>

            <el-col :span="24">
              <el-form-item label="Home address" prop="address">
                <el-input
                  v-model="form.address"
                  type="textarea"
                  :rows="3"
                  placeholder="Street, city, postal code"
                />
              </el-form-item>
            </el-col>

            <el-col :xs="24" :sm="12">
              <el-form-item label="Contact number" prop="contactno">
                <el-input
                  v-model="form.contactno"
                  clearable
                  placeholder="Mobile or landline"
                  inputmode="tel"
                  autocomplete="tel"
                />
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12">
              <el-form-item label="Email" prop="email">
                <el-input
                  v-model="form.email"
                  clearable
                  placeholder="name@example.com"
                  autocomplete="email"
                />
              </el-form-item>
            </el-col>

            <el-col :xs="24" :sm="12" :md="8">
              <el-form-item label="Occupation" prop="occupation">
                <el-input v-model="form.occupation" clearable autocomplete="organization-title" />
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="8">
              <el-form-item label="Birth date" prop="birthdate">
                <el-date-picker
                  v-model="form.birthdate"
                  type="date"
                  placeholder="Pick a date"
                  class="patient-form-date"
                />
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="8">
              <el-form-item label="Gender" prop="sex">
                <el-select
                  v-model="form.sex"
                  placeholder="Select"
                  class="patient-form-select"
                >
                  <el-option label="Female" value="2" />
                  <el-option label="Male" value="1" />
                </el-select>
              </el-form-item>
            </el-col>

            <el-col :xs="24" :sm="12" :md="8">
              <el-form-item label="Civil status" prop="civil_status">
                <el-select
                  v-model="form.civil_status"
                  placeholder="Select"
                  class="patient-form-select"
                >
                  <el-option label="Single" value="Single" />
                  <el-option label="Married" value="Married" />
                  <el-option label="Widowed" value="Widowed" />
                  <el-option label="Legally Separated" value="Legally Separated" />
                </el-select>
              </el-form-item>
            </el-col>

            <el-col :xs="24" :md="8">
              <el-form-item label="Referred by">
                <el-input
                  v-model="form.referredby"
                  clearable
                  placeholder="Referring physician or source"
                />
              </el-form-item>
            </el-col>
            <el-col :xs="24" :md="16">
              <el-form-item label="Additional information">
                <el-input
                  v-model="form.remarks"
                  type="textarea"
                  :autosize="{ minRows: 2, maxRows: 6 }"
                  placeholder="Notes, allergies context, or other remarks"
                />
              </el-form-item>
            </el-col>
          </el-row>
        </el-form>
      </div>
    </el-card>
  </div>
</template>
<script>
import Patients from '@/api/patients';
export default {
  data() {
    return {
      url: '',
      profileFile: null,
      saving: false,
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
        email: '',
      },
    };
  },
  computed: {
    isNewPatient() {
      const id = this.$route.params.id;
      return !id || id === '0';
    },
    pageTitle() {
      return this.isNewPatient ? 'New patient' : 'Edit patient';
    },
    pageSubtitle() {
      return this.isNewPatient
        ? "Enter the patient's details below. Required fields are marked when you try to save."
        : 'Update the patient record below. Required fields are marked when you try to save.';
    },
    rules() {
      return {
        lastname: [{ required: true, message: 'Last name is required', trigger: 'blur' }],
        firstname: [{ required: true, message: 'First name is required', trigger: 'blur' }],
        address: [{ required: true, message: 'Home address is required', trigger: 'blur' }],
        birthdate: [{ required: true, message: 'Birth date is required', trigger: 'change' }],
        civil_status: [{ required: true, message: 'Status is required', trigger: 'change' }],
      };
    },
  },
  created() {
  },
  methods: {
    cancelEvent() {
      // Popconfirm dismissed; no action required
    },
    handleSuccess(file, fileList) {
      const list = (fileList || []).slice(-1);
      const item = list[0];
      if (!item || !item.raw) {
        return;
      }
      this.profileFile = item.raw;
    },
    handlePhotoRemove() {
      this.profileFile = null;
    },
    buildFormData(forceDuplicate) {
      const formData = new FormData();
      const payload = {
        ...this.form,
        birthdate: this.normalizeDate(this.form.birthdate),
        force_duplicate: forceDuplicate ? '1' : '0',
      };

      Object.keys(payload).forEach((key) => {
        if (key === 'profile') {
          return;
        }
        const val = payload[key];
        if (val === null || val === undefined) {
          return;
        }
        if (Array.isArray(val)) {
          if (val.length) {
            formData.append(key, val.join(','));
          }
          return;
        }
        formData.append(key, val);
      });

      const file = this.profileFile
        || (this.$refs.uploadRef && this.$refs.uploadRef.uploadFiles
          && this.$refs.uploadRef.uploadFiles[0]
          && this.$refs.uploadRef.uploadFiles[0].raw);
      if (file) {
        formData.append('profile', file);
      }

      return formData;
    },
    normalizeName(val) {
      return (val || '')
        .toString()
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '');
    },
    tokenizeName(val) {
      return (val || '')
        .toString()
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, ' ')
        .split(' ')
        .map((s) => s.trim())
        .filter(Boolean);
    },
    normalizeDate(val) {
      if (!val) return '';
      if (val instanceof Date && !Number.isNaN(val.getTime())) {
        const y = val.getFullYear();
        const m = String(val.getMonth() + 1).padStart(2, '0');
        const d = String(val.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
      }
      const str = val.toString();
      const m = str.match(/^(\d{4}-\d{2}-\d{2})/);
      if (m) return m[1];
      const parsed = new Date(str);
      if (!Number.isNaN(parsed.getTime())) {
        const y = parsed.getFullYear();
        const mo = String(parsed.getMonth() + 1).padStart(2, '0');
        const d = String(parsed.getDate()).padStart(2, '0');
        return `${y}-${mo}-${d}`;
      }
      return '';
    },
    async findDuplicatePatients() {
      const first = (this.form.firstname || '').trim();
      const last = (this.form.lastname || '').trim();
      const birth = this.normalizeDate(this.form.birthdate);
      if (!first || !last || !birth) return [];

      const middle = (this.form.middlename || '').trim();
      const middleInitial = middle ? middle[0] : '';

      const keywords = [
        `${first} ${last}`.trim(),
        `${last} ${first}`.trim(),
        middleInitial ? `${first} ${middleInitial} ${last}`.trim() : null,
        last,
      ].filter(Boolean);

      const byId = new Map();
      for (const keyword of keywords) {
        const { data } = await Patients.list({ params: { keyword, limit: 100 } });
        for (const p of data || []) {
          if (p && p.id != null) byId.set(p.id, p);
        }
      }

      const firstTok = this.normalizeName(first);
      const lastTok = this.normalizeName(last);

      return Array.from(byId.values()).filter((p) => {
        const dob = this.normalizeDate(p.birthdate);
        if (dob !== birth) return false;
        const tokens = this.tokenizeName(p.patientname);
        return tokens.includes(firstTok) && tokens.includes(lastTok);
      });
    },
    async savePatient(forceDuplicate = false) {
      const response = await Patients.add(this.buildFormData(forceDuplicate));
      this.$message({
        message: 'Patient information has been created successfully.',
        type: 'success',
        duration: 5 * 1000,
      });
      this.$router.push({ path: '/masterfile/profile/' + response.id + '/' + response.patientid });
    },
    async onSubmit() {
      if (this.saving) return;

      this.$refs.patientForm.validate(async (valid) => {
        if (!valid) {
          return;
        }

        this.saving = true;
        try {
          let forceDuplicate = false;
          const dupes = await this.findDuplicatePatients();
          if (dupes.length > 0) {
            const preview = dupes
              .slice(0, 5)
              .map((d) => `• ${d.patientname} (${d.patientid}) — ${this.normalizeDate(d.birthdate)}`)
              .join('<br/>');
            const extra = dupes.length > 5 ? `<br/><br/>And ${dupes.length - 5} more match(es).` : '';
            try {
              await this.$confirm(
                `A patient with the same name and birth date already exists.<br/><br/>${preview}${extra}<br/><br/>Do you want to save anyway?`,
                'Possible duplicate patient',
                {
                  confirmButtonText: 'Save anyway',
                  cancelButtonText: 'Cancel',
                  type: 'warning',
                  dangerouslyUseHTMLString: true,
                }
              );
              forceDuplicate = true;
            } catch (e) {
              this.$message.warning('Duplicate detected. Save cancelled.');
              return;
            }
          }

          await this.savePatient(forceDuplicate);
        } catch (err) {
          if (err && err.response && err.response.status === 409 && err.response.data && err.response.data.existing) {
            const existing = err.response.data.existing;
            try {
              await this.$confirm(
                `A duplicate patient already exists: <b>${existing.patientname}</b> (${existing.patientid}).<br/><br/>Open the existing record instead?`,
                'Duplicate patient blocked',
                {
                  confirmButtonText: 'Open record',
                  cancelButtonText: 'Close',
                  type: 'warning',
                  dangerouslyUseHTMLString: true,
                }
              );
              //this.$router.push({ path: '/masterfile/profile/' + existing.id + '/' + existing.patientid });
            } catch (e) {
              // user closed dialog
            }
            return;
          }
          console.error('Error adding patient:', err);
        } finally {
          this.saving = false;
        }
      });
    },
  },
};
</script>
<style lang="scss" scoped>
.patient-form-page {
  max-width: 1200px;
  margin: 0 auto;
  padding-bottom: 2rem;
}

.patient-form-header {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem 1.5rem;
  margin-bottom: 1.25rem;
}

.patient-form-header__text {
  flex: 1;
  min-width: 0;
}

.patient-form-title {
  margin: 0 0 0.35rem;
  font-size: 1.5rem;
  font-weight: 600;
  line-height: 1.3;
  color: #303133;
}

.patient-form-subtitle {
  margin: 0;
  font-size: 0.875rem;
  line-height: 1.5;
  color: #909399;
  max-width: 42rem;
}

.patient-form-header__actions {
  flex-shrink: 0;
}

.patient-form-card {
  border-radius: 8px;
  border: 1px solid #ebeef5;
}

.patient-form-body {
  padding: 1.25rem 1.25rem 1.5rem;
}

@media (min-width: 768px) {
  .patient-form-body {
    padding: 1.5rem 1.75rem 2rem;
  }
}

.patient-form {
  ::v-deep .el-form-item {
    margin-bottom: 1.1rem;
  }

  ::v-deep .el-form-item__label {
    padding-bottom: 4px;
    line-height: 1.4;
    color: #606266;
    font-weight: 500;
  }

  ::v-deep .el-input__inner,
  ::v-deep .el-textarea__inner {
    border-radius: 6px;
  }

  ::v-deep .el-select,
  ::v-deep .patient-form-select {
    display: block;
    width: 100%;
  }

  ::v-deep .patient-form-date {
    width: 100%;
  }

  ::v-deep .patient-form-date.el-input {
    width: 100%;
  }
}

.patient-form-upload {
  ::v-deep .el-upload--picture-card {
    border-radius: 8px;
    border-style: dashed;
    background: #fafafa;
  }
}

.patient-form-upload__icon {
  font-size: 28px;
  color: #8c939d;
}

@media (max-width: 767px) {
  .patient-form-header__actions {
    width: 100%;
  }

  .patient-form-header__actions .el-button {
    width: 100%;
  }
}
</style>
