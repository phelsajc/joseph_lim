<template>
  <div class="app-container loading-container" v-loading="pageloading" element-loading-text="Loading...">
    <div class="action-toolbar">
      <div class="action-buttons">
        <el-dropdown @command="handleCommand" trigger="click">
          <el-button type="primary" size="large" class="action-btn">
            <i class="el-icon-menu"></i>
            Actions
            <i class="el-icon-arrow-down el-icon--right" />
          </el-button>
          <el-dropdown-menu slot="dropdown" class="action-menu">
            <el-dropdown-item command="update_diagnosis" class="action-item">
              <i class="el-icon-edit"></i>
              Update Diagnosis
            </el-dropdown-item>
            <el-dropdown-item command="print_rx" class="action-item">
              <i class="el-icon-printer"></i>
              Print Rx
            </el-dropdown-item>
            <el-dropdown-item command="print_labs" class="action-item">
              <i class="el-icon-document"></i>
              Print Diagnostics
            </el-dropdown-item>
            <el-dropdown-item command="print_referral" class="action-item">
              <i class="el-icon-s-promotion"></i>
              Print Referral
            </el-dropdown-item>
            <el-dropdown-item command="print_medcert" class="action-item">
              <i class="el-icon-document-copy"></i>
              Print Med Cert
            </el-dropdown-item>
            <el-dropdown-item v-role="['secretary', 'admin', 'doctor']" command="done_consult" class="action-item success">
              <i class="el-icon-check"></i>
              Done Consultation
            </el-dropdown-item>
            <el-dropdown-item command="cancel_apt" class="action-item danger">
              <i class="el-icon-close"></i>
              Cancel Appointment
            </el-dropdown-item>
            <el-dropdown-item v-role="['doctor', 'admin']" command="view_chart" class="action-item">
              <i class="el-icon-view"></i>
              View Chart
            </el-dropdown-item>
          </el-dropdown-menu>
        </el-dropdown>
        
        <el-popconfirm v-model="popconfirmUpddateDiagnosis" title="Are you done with this appointment?"
          @confirm="onSubmit">
          <template #reference>
            <el-button v-if="!isMobile" ref="updateDiagnosisBtn" style="display: none" type="success" size="large" class="action-btn">
              <i class="el-icon-check"></i>
              Update Diagnosis
            </el-button>
          </template>
        </el-popconfirm>
      </div>
    </div>

    <el-dialog :title="'Historical Records'" class="compact-table" width="100%" :visible.sync="historyDiaglog"
      :close-on-click-modal="false" :close-on-press-escape="false">
      <el-table :data="old_records" border :default-sort="{ prop: 'date', order: 'descending' }"
        @row-click="handleRowClick">
        <el-table-column prop="desc" label="Description" />
        <el-table-column prop="date" sortable label="Date" />
      </el-table>
    </el-dialog>

    <el-dialog :title="'HistorVitalsical Records'" class="compact-table" width="100%" :visible.sync="vitalsDiaglog"
      :close-on-click-modal="false" :close-on-press-escape="false">
      <el-table :data="vitals_records" border :default-sort="{ prop: 'date', order: 'descending' }">
        <el-table-column prop="date" sortable label="Date" />
        <el-table-column prop="bp" label="BP" />
        <el-table-column prop="weight" label="Weight" />
      </el-table>
    </el-dialog>

    <el-dialog title="Select Diagnostics" width="80%" :visible.sync="viewDiagnosticsTbl" :close-on-click-modal="false"
      :close-on-press-escape="false">
      <el-row :gutter="20">
        <el-col :span="12">
          <strong>
            <p>BLOOD CHEMISTRY</p>
          </strong>
          <el-row>
            <el-col :span="24">
              <el-checkbox-group v-model="diagnosticsRenderedModel">
                <el-checkbox v-for="item in getAllDiagnosticsOfferedChem" @change="addNewProcedure(item)"
                  :key="item.lab_test" :label="item.lab_test">{{ item.lab_test.toUpperCase() }}</el-checkbox>
              </el-checkbox-group>
            </el-col>
          </el-row>
        </el-col>
        <el-col :span="12">
          <strong>
            <p>HEMATOLOGY</p>
          </strong>
          <el-row>
            <el-col :span="24">
              <el-checkbox-group v-model="diagnosticsRenderedModel">
                <el-checkbox v-for="item in getAllDiagnosticsOfferedHema" @change="addNewProcedure(item)"
                  :key="item.lab_test" :label="item.lab_test">{{ item.lab_test.toUpperCase() }}</el-checkbox>
              </el-checkbox-group>
            </el-col>
          </el-row>
        </el-col>
        <el-col :span="12">
          <strong>
            <p>CLINICAL MICROSCOPY</p>
          </strong>
          <el-row>
            <el-col :span="24">
              <el-checkbox-group v-model="diagnosticsRenderedModel">
                <el-checkbox v-for="item in getAllDiagnosticsOfferedMicroscopy" @change="addNewProcedure(item)"
                  :key="item.lab_test" :label="item.lab_test">{{ item.lab_test.toUpperCase() }}</el-checkbox>
              </el-checkbox-group>
            </el-col>
          </el-row>
        </el-col>
      </el-row>
      <el-divider />
      <el-row :gutter="20">
        <el-col :span="12">
          <strong>
            <p>X-RAY</p>
          </strong>
          <el-row>
            <el-col :span="24">
              <el-checkbox-group v-model="diagnosticsRenderedModel">
                <el-checkbox v-for="item in getAllDiagnosticsOfferedXray" :key="item.lab_test_id" :label="item.lab_test"
                  @change="addNewProcedure(item)">
                  <el-input v-if="diagnosticsRenderedModel.includes(item.lab_test) && item.lab_test_id == 591"
                    v-model="findProcedure(item.lab_test_id).remarks" clearable placeholder="Remarks"
                    style="width: 400px" />
                  {{ item.lab_test.toUpperCase() }}
                </el-checkbox>
              </el-checkbox-group>
            </el-col>
          </el-row>
        </el-col>
        <el-col :span="12">
          <strong>
            <p>ULTRASOUND</p>
          </strong>
          <el-row>
            <el-col :span="24">
              <el-checkbox-group v-model="diagnosticsRenderedModel">
                <el-checkbox v-for="item in getAllDiagnosticsOfferedUtz" @change="addNewProcedure(item)"
                  :key="item.lab_test" :label="item.lab_test">{{ item.lab_test.toUpperCase() }}</el-checkbox>
              </el-checkbox-group>
            </el-col>
          </el-row>
        </el-col>
      </el-row>
      <el-divider />
      <el-row :gutter="20">
        <el-col :span="12">
          <strong>
            <p>IMMUNOLOGY</p>
          </strong>
          <el-row>
            <el-col :span="24">
              <el-checkbox-group v-model="diagnosticsRenderedModel">
                <el-checkbox v-for="item in getAllDiagnosticsOfferedImmonulogy" @change="addNewProcedure(item)"
                  :key="item.lab_test" :label="item.lab_test">{{ item.lab_test.toUpperCase() }}</el-checkbox>
              </el-checkbox-group>
            </el-col>
          </el-row>
        </el-col>

        <el-col :span="12">
          <strong>
            <p>MICROBIOLOGY</p>
          </strong>
          <el-row>
            <el-col :span="24">
              <el-checkbox-group v-model="diagnosticsRenderedModel">
                <el-checkbox v-for="item in getAllDiagnosticsOfferedMirco" @change="addNewProcedure(item)"
                  :key="item.lab_test" :label="item.lab_test">
                  {{ item.lab_test.toUpperCase() }}
                  <el-input
                    v-if="diagnosticsRenderedModel.includes(item.lab_test) && (item.lab_test_id == 560 || item.lab_test_id == 561)"
                    v-model="findProcedure(item.lab_test_id).remarks" clearable placeholder="Remarks"
                    style="width: 400px" />
                </el-checkbox>
              </el-checkbox-group>
              <!-- <el-input v-model="lab_micro_remarks" clearable placeholder="Remarks" /> -->
            </el-col>
          </el-row>
        </el-col>
      </el-row>
      <el-divider />

      <el-row :gutter="20">
        <el-col :span="12">
          <strong>
            <p>CT SCAN</p>
          </strong>
          <el-row>
            <el-col :span="24">
              <el-checkbox-group v-model="diagnosticsRenderedModel">
                <el-checkbox v-for="item in getAllDiagnosticsOfferedCt" @change="addNewProcedure(item)"
                  :key="item.lab_test" :label="item.lab_test">{{ item.lab_test.toUpperCase() }}</el-checkbox>
              </el-checkbox-group>
            </el-col>
          </el-row>
        </el-col>

        <el-col :span="12">
          <strong>
            <p>MRI</p>
          </strong>
          <el-row>
            <el-col :span="24">
              <el-checkbox-group v-model="diagnosticsRenderedModel">
                <el-checkbox v-for="item in getAllDiagnosticsOfferedMri" @change="addNewProcedure(item)"
                  :key="item.lab_test" :label="item.lab_test">{{ item.lab_test.toUpperCase() }}</el-checkbox>
              </el-checkbox-group>
            </el-col>
          </el-row>
        </el-col>
      </el-row>
      <el-divider />

      <el-row :gutter="20">
        <el-col :span="12">
          <strong>
            <p>Others</p>
          </strong>
          <el-row>
            <el-col :span="24">
              <el-checkbox-group v-model="diagnosticsRenderedModel">
                <el-checkbox v-for="item in getAllDiagnosticsOfferedOth" @change="addNewProcedure(item)"
                  :key="item.lab_test" :label="item.lab_test">{{ item.lab_test.toUpperCase() }}</el-checkbox>
              </el-checkbox-group>
              <el-input v-model="form.lab_others" clearable placeholder="Others" @change="addLabOthers" />
            </el-col>
          </el-row>
        </el-col>


        <el-col :span="12">
          <strong>
            <p>CRYSTAL ANALYSIS</p>
          </strong>
          <el-row>
            <el-col :span="24">
              <el-checkbox-group v-model="diagnosticsRenderedModel">
                <el-checkbox v-for="item in getAllDiagnosticsOfferedCrystal" @change="addNewProcedure(item)"
                  :key="item.lab_test" :label="item.lab_test">{{ item.lab_test.toUpperCase() }}
                  <el-input v-if="diagnosticsRenderedModel.includes(item.lab_test) && item.lab_test_id == 568"
                    v-model="findProcedure(item.lab_test_id).remarks" clearable placeholder="Remarks"
                    style="width: 400px" /></el-checkbox>
              </el-checkbox-group>
            </el-col>
          </el-row>
        </el-col>
      </el-row>

      <el-divider />
      <el-button type="success" @click="addProcedure">Add</el-button>
    </el-dialog>

    <el-dialog title="Details" :visible.sync="oldRecordsdialogVisible" width="30%">
      <p><strong>HPI:</strong> {{ selectedOldRecords.hpi }}</p>
      <p><strong>pmHx:</strong> {{ selectedOldRecords.pmhx }}</p>
      <p><strong>Description:</strong> {{ selectedOldRecords.desc }}</p>
      <p><strong>Date:</strong> {{ selectedOldRecords.date }}</p>
      <p><strong>CC:</strong> {{ selectedOldRecords.cc }}</p>
      <p><strong>Recommendations:</strong> {{ selectedOldRecords.recom }}</p>
    </el-dialog>

    <el-dialog :title="'Select Services'" class="compact-table" width="100%" :visible.sync="viewServicesTbl"
      :close-on-click-modal="false" :close-on-press-escape="false">
      <el-checkbox v-for="e in getAllServicesOffered" :key="e.description" v-model="servicesRenderedModel"
        :label="e.description" :value="e.description" @change="addNewServices(e)" />
      <el-divider />
      <el-button v-role="['doctor', 'admin']" type="success" @click="addServices()">
        Add
      </el-button>
    </el-dialog>

    <el-dialog :title="'Cancel Appointment'" :visible.sync="dialogFormVisible" :close-on-click-modal="false"
      :close-on-press-escape="false">
      <div class="form-container">
        <el-form ref="appForm" :model="form_cancel" :rules="rules" label-position="left" label-width="150px"
          style="max-width: 500px">
          <el-form-item :label="'Reason'" prop="cancel_reason">
            <el-input v-model="form_cancel.cancel_reason" type="textarea" maxlength="100" />
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">
            {{ $t("table.cancel") }}
          </el-button>
          <el-button type="primary" :loading="isProcessing" @click="confirmCancel()">
            {{ $t("table.confirm") }}
          </el-button>
        </div>
      </div>
    </el-dialog>
    <br />

    <el-card class="profile-card modern-profile-card" shadow="hover">
      <div slot="header" class="profile-header">
        <div class="profile-title">
          <i class="el-icon-user"></i>
          <span>Patient Profile</span>
        </div>
        <div class="profile-date">
          <i class="el-icon-date"></i>
          <span>{{ currentDt() }}</span>
        </div>
      </div>
      <div class="profile-content">
        <div class="profile-photo-section">
          <div class="profile-photo-container">
            <el-image 
              :src="profile.photo" 
              alt="Profile Photo" 
              fit="cover" 
              class="profile-photo"
              :preview-src-list="[profile.photo]"
            >
              <div slot="error" class="image-slot">
                <i class="el-icon-user-solid"></i>
              </div>
            </el-image>
            <div class="profile-status">
              <el-tag type="info" size="small">
                Patient
              </el-tag>
            </div>
          </div>
        </div>
        <div class="profile-details">
          <div class="profile-section">
            <h4 class="section-title">Basic Information</h4>
            <div class="profile-grid">
              <div class="profile-item">
                <div class="profile-label">
                  <i class="el-icon-user"></i>
                  <span>Name</span>
                </div>
                <div class="profile-value">{{ profile.patientname }}</div>
              </div>
              <div class="profile-item">
                <div class="profile-label">
                  <i class="el-icon-time"></i>
                  <span>Age</span>
                </div>
                <div class="profile-value">{{ profile.age }} years old</div>
              </div>
              <div class="profile-item">
                <div class="profile-label">
                  <i class="el-icon-calendar"></i>
                  <span>Birth Date</span>
                </div>
                <div class="profile-value">{{ dateFormat(profile.birthdate) }}</div>
              </div>
              <div class="profile-item">
                <div class="profile-label">
                  <i class="el-icon-s-custom"></i>
                  <span>Civil Status</span>
                </div>
                <div class="profile-value">{{ profile.civil_status }}</div>
              </div>
              <div class="profile-item">
                <div class="profile-label">
                  <i class="el-icon-user"></i>
                  <span>Gender</span>
                </div>
                <div class="profile-value">
                  <el-tag :type="profile.sex == '2' ? 'success' : 'primary'" size="small">
                    {{ profile.sex == "2" ? "Female" : "Male" }}
                  </el-tag>
                </div>
              </div>
            </div>
          </div>
          <div class="profile-section">
            <h4 class="section-title">Contact & Medical</h4>
            <div class="profile-grid">
              <div class="profile-item">
                <div class="profile-label">
                  <i class="el-icon-phone"></i>
                  <span>Contact</span>
                </div>
                <div class="profile-value">{{ profile.contactno }}</div>
              </div>
              <div class="profile-item">
                <div class="profile-label">
                  <i class="el-icon-location"></i>
                  <span>Address</span>
                </div>
                <div class="profile-value">{{ profile.address }}</div>
              </div>
              <div class="profile-item">
                <div class="profile-label">
                  <i class="el-icon-medicine"></i>
                  <span>Blood Type</span>
                </div>
                <div class="profile-value">
                  <el-tag v-if="profile.blood_type" type="danger" size="small">
                    {{ profile.blood_type }}
                  </el-tag>
                  <span v-else class="text-muted">Not specified</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </el-card>
    <br />

    <!-- Mobile Tab Navigation -->
    <div v-if="isMobile" class="mobile-tab-navigation">
      <el-select v-model="tab" placeholder="Select Section" style="width: 100%; margin-bottom: 20px;" size="large">
        <el-option 
          v-for="tabOption in availableTabs" 
          :key="tabOption.name" 
          :label="tabOption.label" 
          :value="tabOption.name"
          :disabled="!tabOption.available"
        >
          <span style="float: left">{{ tabOption.label }}</span>
          <span v-if="tabOption.hasContent" style="float: right; color: #67c23a; font-size: 12px;">
            <i class="el-icon-check"></i>
          </span>
        </el-option>
      </el-select>
    </div>

    <!-- Desktop Tab Navigation -->
    <el-tabs v-model="tab" type="card" class="modern-tabs" v-if="!isMobile">
      <el-tab-pane name="history" v-if="checkRole(['admin', 'doctor'])">
        <template #label>
          <span class="tab-label">
            <i class="el-icon-document"></i>
            Histories
          </span>
        </template>
        <el-card style="max-width: 100%">
          <el-form label-position="top">
            <el-row :gutter="20">
              <el-col :span="6">
                <el-form-item label="Previous Admission">
                  <el-input v-model="profile.prev_admission" type="textarea" rows="10" />
                </el-form-item>
              </el-col>
              <el-col :span="6">
                <el-form-item label="Previous Surgeries">
                  <el-input v-model="profile.prev_surgeries" type="textarea" rows="10" />
                </el-form-item>
              </el-col>
              <el-col :span="6">
                <el-form-item label="Allergies">
                  <el-input v-model="profile.allergies" type="textarea" rows="10" />
                </el-form-item>
              </el-col>
              <el-col :span="6">
                <el-form-item label="Asthma/Allergic Rhinitis/Atopic Dermatitis">
                  <el-input v-model="profile.asthma" type="textarea" rows="10" />
                </el-form-item>
              </el-col>
            </el-row>
          </el-form>

          <el-form label-position="top">
            <el-row :gutter="20">
              <el-col :span="6">
                <el-form-item label="Hypertension">
                  <el-input v-model="profile.hypertension" type="textarea" rows="10" />
                </el-form-item>
              </el-col>
              <el-col :span="6">
                <el-form-item label="TB">
                  <el-input v-model="profile.tb" type="textarea" rows="10" />
                </el-form-item>
              </el-col>
              <el-col :span="6">
                <el-form-item label="Seizure">
                  <el-input v-model="profile.seizure" type="textarea" rows="10" />
                </el-form-item>
              </el-col>
              <el-col :span="6">
                <el-form-item label="Diabetes">
                  <el-input v-model="profile.diabetes" type="textarea" rows="10" />
                </el-form-item>
              </el-col>
            </el-row>
          </el-form>

          <el-form label-position="top">
            <el-row :gutter="20">
              <el-col :span="12">
                <el-form-item label="COPD">
                  <el-input v-model="profile.copd" type="textarea" rows="10" />
                </el-form-item>
              </el-col>
              <el-col :span="12">
                <el-form-item label="Others">
                  <el-input v-model="profile.pmh_others" type="textarea" rows="10" />
                </el-form-item>
              </el-col>
            </el-row>
          </el-form>
        </el-card>
      </el-tab-pane>
      <el-tab-pane label="Family History" name="family" v-if="checkRole(['admin', 'doctor'])">
        <div class="block">
          <el-form :inline="true" label-position="top" class="demo-form-inline">
            <el-form-item label="History">
              <el-checkbox-group v-model="fam" size="large">
                <el-checkbox-button label="Hypertension">
                  Hypertension
                </el-checkbox-button>
                <el-checkbox-button label="Diabetes Mellitus">
                  Diabetes Mellitus
                </el-checkbox-button>
                <el-checkbox-button label="Stroke"> Stroke </el-checkbox-button>
                <el-checkbox-button label="CAD"> CAD </el-checkbox-button>
              </el-checkbox-group>
            </el-form-item>
            <el-form-item label="Others">
              <el-input v-model="profile.fam_others" :autosize="{ minRows: 2, maxRows: 4 }" style="width: 540px"
                :rows="2" type="textarea" placeholder="Please input" />
            </el-form-item>
          </el-form>
          <el-form label-position="top" style="margin-top: 20px;">
            <el-row :gutter="20">
              <el-col :span="12">
                <el-form-item label="Mother Details">
                  <el-input v-model="profile.mother_details" type="textarea" rows="6" placeholder="Enter mother's medical history, age, health conditions, etc." />
                </el-form-item>
              </el-col>
              <el-col :span="12">
                <el-form-item label="Father Details">
                  <el-input v-model="profile.father_details" type="textarea" rows="6" placeholder="Enter father's medical history, age, health conditions, etc." />
                </el-form-item>
              </el-col>
            </el-row>
          </el-form>
        </div>
      </el-tab-pane>
      <el-tab-pane label="Social / Environment History" name="soc" v-if="checkRole(['admin', 'doctor'])">
        <div class="block">
          <el-form :inline="true" label-position="top" class="demo-form-inline">
            <el-form-item label="History">
              <el-checkbox-group v-model="soc" size="large">
                <el-checkbox-button label="Smoking"> Smoking </el-checkbox-button>
                <el-checkbox-button label="Alcoholic Beverage Drinking">
                  Alcoholic Beverage Drinking
                </el-checkbox-button>
              </el-checkbox-group>
            </el-form-item>
            <el-form-item label="Smoking Details" v-if="soc.includes('Smoking')">
              <el-input v-model="profile.smoking_details" :autosize="{ minRows: 2, maxRows: 4 }" style="width: 540px"
                :rows="2" type="textarea" placeholder="Please provide details about smoking habits" />
            </el-form-item>
            <el-form-item label="Alcoholic Beverage Drinking Details" v-if="soc.includes('Alcoholic Beverage Drinking')">
              <el-input v-model="profile.alcohol_details" :autosize="{ minRows: 2, maxRows: 4 }" style="width: 540px"
                :rows="2" type="textarea" placeholder="Please provide details about alcoholic beverage drinking habits" />
            </el-form-item>
            <el-form-item label="Others">
              <el-input v-model="profile.soc_others" :autosize="{ minRows: 2, maxRows: 4 }" style="width: 540px"
                :rows="2" type="textarea" placeholder="Please input" />
            </el-form-item>
            <el-form-item label="Vaccinations">
              <el-input v-model="profile.vaccination_sup" :autosize="{ minRows: 2, maxRows: 4 }" style="width: 540px"
                :rows="2" type="textarea" placeholder="Please input" />
            </el-form-item>
          </el-form>
        </div>
      </el-tab-pane>
      <el-tab-pane label="Diagnosis" name="first">
        <el-form ref="form" label-width="120px" class="demo-form-inline">
          <el-form-item label="Secretary's Remarks">
            <el-input v-model="form.nurse_remarks" type="textarea" />
          </el-form-item>
          <el-form-item label="CC" v-if="checkRole(['admin', 'doctor'])">
            <el-input v-model="form.chiefcomplaints" type="textarea" rows="2" />
          </el-form-item>
          <el-form-item label="History" v-if="checkRole(['admin', 'doctor'])">
            <el-input v-model="form.history" type="textarea" rows="5" />
          </el-form-item>
        </el-form>
        <el-form ref="form" :model="form" label-width="120px" class="demo-form-inline"
          v-if="checkRole(['admin', 'doctor'])">
          <el-form-item label="P.E.">
            <div class="pe-template-section">
              <el-row :gutter="20" style="margin-bottom: 15px;">
                <el-col :span="24">
                  <div class="template-buttons">
                    <el-button v-for="template in peTemplates" :key="template.id" size="small"
                      :type="template.type === 'default' ? 'primary' : 'success'" plain
                      @click="insertPETemplate(template.content)" class="template-btn">
                      {{ template.name }}
                      <el-button v-if="template.type === 'custom'" size="mini" type="danger" icon="el-icon-delete"
                        circle @click.stop="deleteTemplate(template.id)" class="delete-template-btn"></el-button>
                    </el-button>
                  </div>
                  <el-button size="small" type="success" @click="showCustomTemplateDialog = true"
                    style="margin-left: 10px;">
                    Custom Template
                  </el-button>
                </el-col>
              </el-row>
              <el-input v-model="form.pe" type="textarea" ref="peInput" rows="10" @input="autoResize" />
            </div>
          </el-form-item>

          <!-- Custom Template Dialog -->
          <el-dialog title="Custom Physical Examination Template" :visible.sync="showCustomTemplateDialog" width="60%"
            :close-on-click-modal="false">
            <el-form :model="customTemplateForm" label-width="120px">
              <el-form-item label="Template Name">
                <el-input v-model="customTemplateForm.name" placeholder="Enter template name" />
              </el-form-item>
              <el-form-item label="Template Content">
                <el-input v-model="customTemplateForm.content" type="textarea" :rows="8"
                  placeholder="Enter your custom P.E. template content..." />
              </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
              <el-button @click="showCustomTemplateDialog = false">Cancel</el-button>
              <el-button type="primary" @click="saveCustomTemplate">Save Template</el-button>
            </div>
          </el-dialog>

          <el-form-item label="Diagnosis">
            <el-input v-model="form.diagnosis" type="textarea" />
          </el-form-item>
          <el-form-item label="Plans">
            <el-input v-model="form.remarks" type="textarea" ref="plansInput" rows="10" @input="autoResize" />
          </el-form-item>
          <el-form-item label="Follow Up Date">
            <!-- <el-date-picker
              v-model="form.followup"
              type="date"
              :clearable="false"
              placeholder="Pick a day"
            /> -->
            <date-picker v-model="form.followup" valueType="format"></date-picker>
          </el-form-item>
          <!-- <el-form-item label="Email">
            <el-input v-model="form.email" autosize clearable />
          </el-form-item> -->
        </el-form>
      </el-tab-pane>
      
      <el-tab-pane name="obgyn" v-if="checkRole(['admin', 'doctor'])">
        <template #label>
          <span class="tab-label">
            <i class="el-icon-female"></i>
            OB-GYN
          </span>
        </template>
        <el-card style="max-width: 100%">
          <el-form label-position="top">
            <el-row :gutter="20">
              <el-col :span="6">
                <el-form-item label="Pregnancy">
                  <el-input v-model="form.pregnancy" type="textarea" rows="4" placeholder="Enter pregnancy history" />
                </el-form-item>
              </el-col>
              <el-col :span="6">
                <el-form-item label="LMP (Last Menstrual Period)">
                  <el-date-picker
                    v-model="form.lmp"
                    type="date"
                    placeholder="Select LMP date"
                    format="yyyy-MM-dd"
                    value-format="yyyy-MM-dd"
                    style="width: 100%"
                  />
                </el-form-item>
              </el-col>
              <el-col :span="6">
                <el-form-item label="Contraceptive Use">
                  <el-input v-model="form.contraceptive_use" type="textarea" rows="4" placeholder="Enter contraceptive use history" />
                </el-form-item>
              </el-col>
              <el-col :span="6">
                <el-form-item label="Menopause">
                  <el-input v-model="form.menopause" type="textarea" rows="4" placeholder="Enter menopause information" />
                </el-form-item>
              </el-col>
            </el-row>
          </el-form>
        </el-card>
      </el-tab-pane>
      <el-tab-pane name="second">
        <template #label>
          <span class="tab-label">
            <i class="el-icon-data-line"></i>
            Vitals
          </span>
        </template>
        <el-card class="modern-card" shadow="hover">
          <div slot="header" class="card-header">
            <i class="el-icon-data-line"></i>
            <span>Vital Signs</span>
          </div>
          <div class="vitals-container">
            <div class="vitals-grid">
              <div class="vital-group">
                <h4 class="group-title">Blood Pressure</h4>
                <div class="vital-inputs">
                  <el-form-item label="Systolic" class="vital-item">
                    <el-input 
                      v-model="form.vit_sys" 
                      placeholder="mmHg"
                      size="large"
                      class="vital-input"
                    >
                      <template slot="prepend">SYS</template>
                    </el-input>
                  </el-form-item>
                  <el-form-item label="Diastolic" class="vital-item">
                    <el-input 
                      v-model="form.vit_dia" 
                      placeholder="mmHg"
                      size="large"
                      class="vital-input"
                    >
                      <template slot="prepend">DIA</template>
                    </el-input>
                  </el-form-item>
                </div>
              </div>
              
              <div class="vital-group">
                <h4 class="group-title">Body Measurements</h4>
                <div class="vital-inputs">
                  <el-form-item label="Weight" class="vital-item">
                    <el-input 
                      v-model="form.weight" 
                      placeholder="kg"
                      size="large"
                      class="vital-input"
                    >
                      <template slot="append">kg</template>
                    </el-input>
                  </el-form-item>
                  <el-form-item label="Height" class="vital-item">
                    <el-input 
                      v-model="form.height" 
                      placeholder="cm"
                      size="large"
                      class="vital-input"
                    >
                      <template slot="append">cm</template>
                    </el-input>
                  </el-form-item>
                  <el-form-item label="BMI" class="vital-item">
                    <el-input 
                      v-model="form.bmi" 
                      placeholder="Auto-calculated"
                      size="large"
                      class="vital-input"
                      readonly
                    >
                      <template slot="prepend">BMI</template>
                    </el-input>
                  </el-form-item>
                </div>
              </div>
              
              <div class="vital-group">
                <h4 class="group-title">Vital Signs</h4>
                <div class="vital-inputs">
                  <el-form-item label="Temperature" class="vital-item">
                    <el-input 
                      v-model="form.vit_temp" 
                      placeholder="°C"
                      size="large"
                      class="vital-input"
                    >
                      <template slot="append">°C</template>
                    </el-input>
                  </el-form-item>
                  <el-form-item label="Heart Rate" class="vital-item">
                    <el-input 
                      v-model="form.vit_cr" 
                      placeholder="bpm"
                      size="large"
                      class="vital-input"
                    >
                      <template slot="append">bpm</template>
                    </el-input>
                  </el-form-item>
                  <el-form-item label="Respiratory Rate" class="vital-item">
                    <el-input 
                      v-model="form.vit_rr" 
                      placeholder="rpm"
                      size="large"
                      class="vital-input"
                    >
                      <template slot="append">rpm</template>
                    </el-input>
                  </el-form-item>
                </div>
              </div>
            </div>
            
            <div class="vitals-actions">
              <el-button 
                v-role="['secretary', 'admin']" 
                type="primary" 
                size="large"
                icon="el-icon-check"
                @click="upDateBP()"
                class="update-btn"
              >
                Update Vitals
              </el-button>
            </div>
          </div>
        </el-card>
      </el-tab-pane>
      <el-tab-pane name="fourth" v-if="checkRole(['admin', 'doctor'])">
        <template #label>
          <span class="tab-label">
            <i class="el-icon-medicine"></i>
            Medicines
          </span>
        </template>
        <el-card style="max-width: 100%">
          <el-row :gutter="24">
            <el-form :inline="true" label-position="top" class="demo-form-inline">
              <el-checkbox v-model="medsArr.custom_meds" label="Not carried" size="large" />
              <br />
              <el-form-item label="Quantity">
                <el-input v-model="medsArr.qty" autosize clearable />
              </el-form-item>
              <el-form-item v-if="!medsArr.custom_meds" label="Search Medicine">
                <el-autocomplete v-model="medsArr.meds" :fetch-suggestions="querySearch" popper-class="my-autocomplete"
                  placeholder="Please input" style="width: 400%" @select="handleSelect">
                  <template #default="{ item }">
                    <div class="value">{{ item.medicine }}</div>
                  </template>
                </el-autocomplete>
              </el-form-item>
              <el-form-item v-if="medsArr.custom_meds" label="Generic Name">
                <el-input v-model="medsArr.custom_generic" autosize clearable />
              </el-form-item>
              <el-form-item v-if="medsArr.custom_meds" label="Brand Name">
                <el-input v-model="medsArr.custom_brand" autosize clearable />
              </el-form-item>
              <!-- <el-form-item v-if="medsArr.custom_meds" label="Dosage">
                <el-input v-model="medsArr.custom_dosage" autosize clearable />
              </el-form-item> -->
            </el-form>
          </el-row>
          <el-row :gutter="20">
            <el-form :inline="true" label-position="top" class="demo-form-inline">
              <el-form-item label="Before Breakfast">
                <el-input v-model="medsArr.bf_b" autosize clearable />
              </el-form-item>
              <el-form-item label="After Breakfast">
                <el-input v-model="medsArr.bf_a" autosize clearable />
              </el-form-item>
              <el-form-item label="Before Lunch">
                <el-input v-model="medsArr.l_b" autosize clearable />
              </el-form-item>
              <el-form-item label="After Lunch">
                <el-input v-model="medsArr.l_a" autosize clearable />
              </el-form-item>
              <el-form-item label="Before Dinner">
                <el-input v-model="medsArr.s_b" autosize clearable />
              </el-form-item>
              <el-form-item label="After Dinner">
                <el-input v-model="medsArr.s_a" autosize clearable />
              </el-form-item>
              <el-form-item label="Bedtime">
                <el-input v-model="medsArr.bt" autosize clearable />
              </el-form-item>
            </el-form>
          </el-row>
          <el-row :gutter="20">
            <el-form :inline="true" label-position="top" class="demo-form-inline">
              <el-col :xs="4" :sm="4" :md="4" :lg="4" :xl="4">
                <el-form-item label="Remarks">
                  <el-input v-model="medsArr.remarks" type="textarea" style="width: 650px" />
                </el-form-item>
              </el-col>
            </el-form>
          </el-row>
          <el-row :gutter="20">
            <el-table :data="rx_list" style="width: 100%" class="compact-table" size="small">
              <el-table-column prop="medicine" label="Medicine" width="300" />
              <el-table-column prop="qty" label="Qty" width="50" align="center" />
              <el-table-column label="Breakfast" width="120">
                <el-table-column prop="bb" label="B" width="60" align="center" />
                <el-table-column prop="ab" label="A" width="60" align="center" />
              </el-table-column>
              <el-table-column label="Lunch" width="120">
                <el-table-column prop="bl" label="B" width="60" align="center" />
                <el-table-column prop="al" label="A" width="60" align="center" />
              </el-table-column>
              <el-table-column label="Dinner" width="120">
                <el-table-column prop="bs" label="B" width="60" align="center" />
                <el-table-column prop="as" label="A" width="60" align="center" />
              </el-table-column>
              <el-table-column prop="bt" label="Bedtime" width="70" align="center" />
              <el-table-column prop="remarks" label="Remarks" width="150" show-overflow-tooltip />
              <el-table-column align="center" label="Actions" width="180">
                <template slot-scope="scope">
                  <el-button v-role="['doctor', 'admin']" type="primary" size="mini" icon="el-icon-edit"
                    @click="editMed(scope.row)"></el-button>
                  <el-button v-if="isEditMode && editingMedId === scope.row.id" v-role="['doctor', 'admin']"
                    type="warning" size="mini" icon="el-icon-close" @click="cancelEdit()"></el-button>
                  <el-button v-role="['doctor', 'admin']" type="danger" size="mini" icon="el-icon-delete"
                    @click="deleteMed(scope.row.id)"></el-button>
                </template>
              </el-table-column>
            </el-table>
          </el-row>
          <el-divider />
          <el-row :gutter="20">
            <el-button v-role="['doctor', 'admin']" type="success" @click="addMeds()">
              {{ isEditMode ? 'Update' : 'Add' }}
            </el-button>
            <el-button v-role="['doctor', 'admin']" type="info" @click="importMedicine()">
              Import
            </el-button>
          </el-row>
        </el-card>
      </el-tab-pane>
      <el-tab-pane name="fifth" v-if="checkRole(['admin', 'doctor'])">
        <template #label>
          <span class="tab-label">
            <i class="el-icon-data-analysis"></i>
            Diagnostics
          </span>
        </template>
        <el-card style="max-width: 100%">
          <el-radio-group v-model="form.fasting_mode">
            <el-radio label="1">Fasting 8-10 hours </el-radio>
            <el-radio label="2">Fasting 10-12 hours </el-radio>
            <el-radio label="3">Non-fasting</el-radio>
          </el-radio-group>
          <el-checkbox v-model="form.sendXrayToEmail" label="Send X-ray images" size="large" />
          <el-row>
            <el-form :inline="true" label-position="top" class="demo-form-inline">
              <el-form-item label="Remarks">
                <el-input v-model="form.lab_remarks" type="textarea" style="width: 650px" />
              </el-form-item>
            </el-form>
          </el-row>
          <br />
          <br />
          <br />
          <el-button type="primary" @click="viewDiagnosticsTbl = true">View Diagnostics</el-button>
          <el-row :gutter="20">
            <el-table :data="diagnostic_list" style="width: 100%">
              <el-table-column prop="diagnostic" label="Procedure" />
              <el-table-column prop="remarks" label="Remarks" />
              <el-table-column align="center" label="Actions" width="350">
                <template slot-scope="scope">
                  <el-button v-role="['doctor', 'admin']" type="danger" size="small" icon="el-icon-delete"
                    @click="removeProcedure(scope.row.id)">
                    Delete
                  </el-button>
                </template>
              </el-table-column>
            </el-table>
          </el-row>
          <el-divider />
        </el-card>
      </el-tab-pane>
      <el-tab-pane name="medcert" v-if="checkRole(['admin', 'doctor'])">
        <template #label>
          <span class="tab-label">
            <i class="el-icon-document-copy"></i>
            Med Cert
          </span>
        </template>
        <el-card style="max-width: 100%">
          <el-form label-position="top">
            <el-row :gutter="20">
              <el-col :span="6">
                <el-form-item label="Undersigned Date">
                  <!-- <el-date-picker
                      v-model="form.medcert_undersigned"
                      type="date"
                      :clearable="false"
                      placeholder="Pick a day"
                    />   -->
                  <date-picker v-model="form.medcert_undersigned" valueType="format"></date-picker>
                </el-form-item>
              </el-col>
              <el-col :span="9">
                <el-form-item label="Diagnosis">
                  <el-input v-model="form.medcert_diagnosis" type="textarea" rows="10" />
                </el-form-item>
              </el-col>
              <el-col :span="9">
                <el-form-item label="Remarks">
                  <el-input v-model="form.medcert_remarks" type="textarea" rows="10" />
                </el-form-item>
              </el-col>
            </el-row>
          </el-form>
        </el-card>
      </el-tab-pane>
      <el-tab-pane name="referral" v-if="checkRole(['admin', 'doctor'])">
        <template #label>
          <span class="tab-label">
            <i class="el-icon-s-promotion"></i>
            Referral
          </span>
        </template>
        <el-card style="max-width: 100%">
          <el-form label-position="top">
            <el-row :gutter="20">
              <el-col :span="4">
                <el-form-item label="Doctor"><el-input v-model="form.referral_doctor" />
                </el-form-item>
              </el-col>
              <el-col :span="4">
                <el-form-item label="Address 1"><el-input v-model="form.referral_addr1" />
                </el-form-item>
              </el-col>
              <el-col :span="4">
                <el-form-item label="Address 2"><el-input v-model="form.referral_addr2" />
                </el-form-item>
              </el-col>
              <el-col :span="4">
                <el-form-item label="Undersigned Date">
                  <date-picker v-model="form.referral_undersigned" valueType="format"></date-picker>
                </el-form-item>
              </el-col>
            </el-row>
            <el-row :gutter="20">
              <el-col :span="12">
                <el-form-item label="Diagnosis">
                  <el-input v-model="form.referral_diagnosis" type="textarea" rows="10" />
                </el-form-item>
              </el-col>
              <el-col :span="12">
                <el-form-item label="Remarks">
                  <el-input v-model="form.referral_remarks" type="textarea" rows="10" />
                </el-form-item>
              </el-col>
            </el-row>
          </el-form>
        </el-card>
      </el-tab-pane>
      <el-tab-pane name="attachments">
        <template #label>
          <span class="tab-label">
            <i class="el-icon-paperclip"></i>
            Attachments
          </span>
        </template>
        <div class="mb-4">
          <el-upload ref="uploadRef" action="#" :auto-upload="false" multiple :on-change="handleChange" :disabled="isUploading">
            <template #trigger>
              <el-button ref="uploadRef" size="small" type="info" action="#" :auto-upload="false" multiple
                :on-change="handleChange" :disabled="isUploading">Select attachments</el-button>
            </template>
            <el-button size="small" type="primary" @click="submitUpload" :loading="isUploading" :disabled="isUploading">
              {{ isUploading ? 'Uploading...' : 'Submit' }}
            </el-button>
          </el-upload>
          
          <!-- Upload Progress -->
          <div v-if="isUploading" class="mt-3">
            <el-progress :percentage="uploadProgress" :status="uploadProgress === 100 ? 'success' : ''"></el-progress>
            <p class="text-sm text-gray-600 mt-2">{{ uploadStatus }}</p>
          </div>
        </div>
        <el-card style="max-width: 100%">
          <el-dialog :visible.sync="dialogVisible" width="50%">
            <el-image :src="selectedImage.file" :alt="selectedImage.alt" fit="contain" class="popup-image" />
            <span slot="footer" class="dialog-footer">
              <el-button @click="dialogVisible = false">Close</el-button>
            </span>
          </el-dialog>

          <el-table :data="attachments" style="width: 100%">
            <el-table-column label="Image">
              <template slot-scope="scope">
                <el-button type="primary" icon="el-icon-files"
                  @click="viewFile(scope.row.newfile, scope.row.extension)" />
                {{ scope.row.fname }}
              </template>
            </el-table-column>
            <el-table-column label="Name" prop="description" />
            <el-table-column label="Date" prop="created_dt" />
            <el-table-column label="Action" width="150">
              <template slot-scope="scope">
                <el-button type="danger" icon="el-icon-delete" @click="deleteAtt(scope.row.id)" />
              </template>
            </el-table-column>
          </el-table>

          <el-dialog :visible.sync="viewFileModel" :fullscreen="false" :close-on-click-modal="false">
            <template #default>
              <div class="iframe-wrapper">
                <iframe v-if="isPdf" :src="sourceFile" :style="transformStyle" frameborder="0"
                  class="iframe-full"></iframe>
                <el-image v-if="!isPdf" style="width: 100px; height: 100px" :src="sourceFile" :zoom-rate="1.2"
                  :max-scale="7" :min-scale="0.2" :preview-src-list="[sourceFile]" show-progress :initial-index="4"
                  fit="cover" />
              </div>
            </template>
          </el-dialog>
        </el-card>
      </el-tab-pane>
    </el-tabs>

    <!-- Mobile Content Sections -->
    <div v-if="isMobile" class="mobile-content">
      <!-- Histories Section -->
      <div v-if="tab === 'history'" class="mobile-section">
        <h3>Histories</h3>
        <el-card style="max-width: 100%">
          <el-form label-position="top">
            <el-row :gutter="20">
              <el-col :span="24">
                <el-form-item label="Previous Admission">
                  <el-input v-model="profile.prev_admission" type="textarea" rows="5" />
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="Previous Surgeries">
                  <el-input v-model="profile.prev_surgeries" type="textarea" rows="5" />
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="Allergies">
                  <el-input v-model="profile.allergies" type="textarea" rows="5" />
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="Asthma/Allergic Rhinitis/Atopic Dermatitis">
                  <el-input v-model="profile.asthma" type="textarea" rows="5" />
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="Hypertension">
                  <el-input v-model="profile.hypertension" type="textarea" rows="5" />
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="TB">
                  <el-input v-model="profile.tb" type="textarea" rows="5" />
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="Seizure">
                  <el-input v-model="profile.seizure" type="textarea" rows="5" />
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="Diabetes">
                  <el-input v-model="profile.diabetes" type="textarea" rows="5" />
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="COPD">
                  <el-input v-model="profile.copd" type="textarea" rows="5" />
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="Others">
                  <el-input v-model="profile.pmh_others" type="textarea" rows="5" />
                </el-form-item>
              </el-col>
            </el-row>
          </el-form>
        </el-card>
      </div>

      <!-- Family History Section -->
      <div v-if="tab === 'family'" class="mobile-section">
        <h3>Family History</h3>
        <div class="block">
          <el-form label-position="top" class="demo-form-inline">
            <el-form-item label="History">
              <el-checkbox-group v-model="fam" size="large">
                <el-checkbox-button label="Hypertension">Hypertension</el-checkbox-button>
                <el-checkbox-button label="Diabetes Mellitus">Diabetes Mellitus</el-checkbox-button>
                <el-checkbox-button label="Stroke">Stroke</el-checkbox-button>
                <el-checkbox-button label="CAD">CAD</el-checkbox-button>
              </el-checkbox-group>
            </el-form-item>
            <el-form-item label="Others">
              <el-input v-model="profile.fam_others" :autosize="{ minRows: 2, maxRows: 4 }" 
                type="textarea" placeholder="Please input" />
            </el-form-item>
          </el-form>
          <el-form label-position="top" style="margin-top: 20px;">
            <el-row :gutter="20">
              <el-col :span="24">
                <el-form-item label="Mother Details">
                  <el-input v-model="form.mother_details" type="textarea" rows="6" placeholder="Enter mother's medical history, age, health conditions, etc." />
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="Father Details">
                  <el-input v-model="form.father_details" type="textarea" rows="6" placeholder="Enter father's medical history, age, health conditions, etc." />
                </el-form-item>
              </el-col>
            </el-row>
          </el-form>
        </div>
      </div>

      <!-- Social/Environment History Section -->
      <!-- <div v-if="tab === 'soc'" class="mobile-section">
        <h3>Social / Environment History</h3>
        <div class="block">
          <el-form label-position="top" class="demo-form-inline">
            <el-form-item label="History">
              <el-checkbox-group v-model="soc" size="large">
                <el-checkbox-button label="Smoking">Smoking</el-checkbox-button>
                <el-checkbox-button label="Alcoholic Beverage Drinking">Alcoholic Beverage Drinking</el-checkbox-button>
              </el-checkbox-group>
            </el-form-item>
            <el-form-item label="Smoking Details" v-if="soc.includes('Smoking')">
              <el-input v-model="profile.smoking_details" :autosize="{ minRows: 2, maxRows: 4 }" 
                type="textarea" placeholder="Please provide details about smoking habits" />
            </el-form-item>
            <el-form-item label="Alcoholic Beverage Drinking Details" v-if="soc.includes('Alcoholic Beverage Drinking')">
              <el-input v-model="profile.alcohol_details" :autosize="{ minRows: 2, maxRows: 4 }" 
                type="textarea" placeholder="Please provide details about alcoholic beverage drinking habits" />
            </el-form-item>
            <el-form-item label="Others">
              <el-input v-model="profile.soc_others" :autosize="{ minRows: 2, maxRows: 4 }" 
                type="textarea" placeholder="Please input" />
            </el-form-item>
            <el-form-item label="Vaccinations">
              <el-input v-model="profile.vaccination_sup" :autosize="{ minRows: 2, maxRows: 4 }" 
                type="textarea" placeholder="Please input" />
            </el-form-item>
          </el-form>
        </div>
      </div> -->

      <!-- Diagnosis Section -->
      <div v-if="tab === 'first'" class="mobile-section">
        <h3>Diagnosis</h3>
        <el-form ref="form" label-width="120px" class="demo-form-inline">
          <el-form-item label="Secretary's Remarks">
            <el-input v-model="form.nurse_remarks" type="textarea" />
          </el-form-item>
          <el-form-item label="CC" v-if="checkRole(['admin', 'doctor'])">
            <el-input v-model="form.chiefcomplaints" type="textarea" rows="2" />
          </el-form-item>
          <el-form-item label="History" v-if="checkRole(['admin', 'doctor'])">
            <el-input v-model="form.history" type="textarea" rows="5" />
          </el-form-item>
          <el-form-item label="P.E." v-if="checkRole(['admin', 'doctor'])">
            <div class="pe-template-section">
              <el-row :gutter="20" style="margin-bottom: 15px;">
                <el-col :span="24">
                  <div class="template-buttons">
                    <el-button v-for="template in peTemplates" :key="template.id" size="small"
                      :type="template.type === 'default' ? 'primary' : 'success'" plain
                      @click="insertPETemplate(template.content)" class="template-btn">
                      {{ template.name }}
                      <el-button v-if="template.type === 'custom'" size="mini" type="danger" icon="el-icon-delete"
                        circle @click.stop="deleteTemplate(template.id)" class="delete-template-btn"></el-button>
                    </el-button>
                  </div>
                  <el-button size="small" type="success" @click="showCustomTemplateDialog = true"
                    style="margin-left: 10px;">
                    Custom Template
                  </el-button>
                </el-col>
              </el-row>
              <el-input v-model="form.pe" type="textarea" ref="peInput" rows="10" @input="autoResize" />
            </div>
          </el-form-item>
          <el-form-item label="Diagnosis" v-if="checkRole(['admin', 'doctor'])">
            <el-input v-model="form.diagnosis" type="textarea" />
          </el-form-item>
          <el-form-item label="Plans" v-if="checkRole(['admin', 'doctor'])">
            <el-input v-model="form.remarks" type="textarea" ref="plansInput" rows="10" @input="autoResize" />
          </el-form-item>
          <el-form-item label="Follow Up Date" v-if="checkRole(['admin', 'doctor'])">
            <date-picker v-model="form.followup" valueType="format"></date-picker>
          </el-form-item>
        </el-form>
      </div>

      <!-- Vitals Section -->
      <div v-if="tab === 'second'" class="mobile-section">
        <h3>Vitals</h3>
        <el-card style="max-width: 100%">
          <el-form label-position="top" class="demo-form-inline">
            <el-row :gutter="20">
              <el-col :span="12">
                <el-form-item label="Systolic">
                  <el-input v-model="form.vit_sys" autosize clearable />
                </el-form-item>
              </el-col>
              <el-col :span="12">
                <el-form-item label="Diastolic">
                  <el-input v-model="form.vit_dia" clearable />
                </el-form-item>
              </el-col>
              <el-col :span="12">
                <el-form-item label="Weight">
                  <el-input v-model="form.weight" clearable placeholder="kilograms" />
                </el-form-item>
              </el-col>
              <el-col :span="12">
                <el-form-item label="Height">
                  <el-input v-model="form.height" clearable placeholder="centimeters" />
                </el-form-item>
              </el-col>
              <el-col :span="12">
                <el-form-item label="BMI">
                  <el-input v-model="form.bmi" clearable />
                </el-form-item>
              </el-col>
              <el-col :span="12">
                <el-form-item label="Temperature">
                  <el-input v-model="form.vit_temp" clearable />
                </el-form-item>
              </el-col>
              <el-col :span="12">
                <el-form-item label="Cardiac Rate">
                  <el-input v-model="form.vit_cr" clearable />
                </el-form-item>
              </el-col>
              <el-col :span="12">
                <el-form-item label="Respiratory Rate">
                  <el-input v-model="form.vit_rr" clearable />
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item v-role="['secretary', 'admin']" label="">
                  <el-button type="primary" @click="upDateBP()">Update</el-button>
                </el-form-item>
              </el-col>
            </el-row>
          </el-form>
        </el-card>
      </div>

      <!-- Medicines Section -->
      <div v-if="tab === 'fourth'" class="mobile-section">
        <h3>Medicines</h3>
        <el-card style="max-width: 100%">
          <el-row :gutter="24">
            <el-form label-position="top" class="demo-form-inline">
              <el-checkbox v-model="medsArr.custom_meds" label="Not carried" size="large" />
              <br />
              <el-form-item label="Quantity">
                <el-input v-model="medsArr.qty" autosize clearable />
              </el-form-item>
              <el-form-item v-if="!medsArr.custom_meds" label="Search Medicine">
                <el-autocomplete v-model="medsArr.meds" :fetch-suggestions="querySearch" popper-class="my-autocomplete"
                  placeholder="Please input" style="width: 100%" @select="handleSelect">
                  <template #default="{ item }">
                    <div class="value">{{ item.medicine }}</div>
                  </template>
                </el-autocomplete>
              </el-form-item>
              <el-form-item v-if="medsArr.custom_meds" label="Generic Name">
                <el-input v-model="medsArr.custom_generic" autosize clearable />
              </el-form-item>
              <el-form-item v-if="medsArr.custom_meds" label="Brand Name">
                <el-input v-model="medsArr.custom_brand" autosize clearable />
              </el-form-item>
            </el-form>
          </el-row>
          <el-row :gutter="20">
            <el-form label-position="top" class="demo-form-inline">
              <el-col :span="12">
                <el-form-item label="Before Breakfast">
                  <el-input v-model="medsArr.bf_b" autosize clearable />
                </el-form-item>
              </el-col>
              <el-col :span="12">
                <el-form-item label="After Breakfast">
                  <el-input v-model="medsArr.bf_a" autosize clearable />
                </el-form-item>
              </el-col>
              <el-col :span="12">
                <el-form-item label="Before Lunch">
                  <el-input v-model="medsArr.l_b" autosize clearable />
                </el-form-item>
              </el-col>
              <el-col :span="12">
                <el-form-item label="After Lunch">
                  <el-input v-model="medsArr.l_a" autosize clearable />
                </el-form-item>
              </el-col>
              <el-col :span="12">
                <el-form-item label="Before Dinner">
                  <el-input v-model="medsArr.s_b" autosize clearable />
                </el-form-item>
              </el-col>
              <el-col :span="12">
                <el-form-item label="After Dinner">
                  <el-input v-model="medsArr.s_a" autosize clearable />
                </el-form-item>
              </el-col>
              <el-col :span="12">
                <el-form-item label="Bedtime">
                  <el-input v-model="medsArr.bt" autosize clearable />
                </el-form-item>
              </el-col>
            </el-form>
          </el-row>
          <el-row :gutter="20">
            <el-form label-position="top" class="demo-form-inline">
              <el-col :span="24">
                <el-form-item label="Remarks">
                  <el-input v-model="medsArr.remarks" type="textarea" />
                </el-form-item>
              </el-col>
            </el-form>
          </el-row>
          <el-row :gutter="20">
            <el-table :data="rx_list" style="width: 100%" class="compact-table" size="small">
              <el-table-column prop="medicine" label="Medicine" width="300" />
              <el-table-column prop="qty" label="Qty" width="40" align="center" />
              <el-table-column label="Breakfast" width="100">
                <el-table-column prop="bb" label="B" width="50" align="center" />
                <el-table-column prop="ab" label="A" width="50" align="center" />
              </el-table-column>
              <el-table-column label="Lunch" width="100">
                <el-table-column prop="bl" label="B" width="50" align="center" />
                <el-table-column prop="al" label="A" width="50" align="center" />
              </el-table-column>
              <el-table-column label="Dinner" width="100">
                <el-table-column prop="bs" label="B" width="50" align="center" />
                <el-table-column prop="as" label="A" width="50" align="center" />
              </el-table-column>
              <el-table-column prop="bt" label="Bedtime" width="60" align="center" />
              <el-table-column prop="remarks" label="Remarks" width="120" show-overflow-tooltip />
              <el-table-column align="center" label="Actions" width="120">
                <template slot-scope="scope">
                  <el-button v-role="['doctor', 'admin']" type="primary" size="mini" icon="el-icon-edit"
                    @click="editMed(scope.row)"></el-button>
                  <el-button v-if="isEditMode && editingMedId === scope.row.id" v-role="['doctor', 'admin']"
                    type="warning" size="mini" icon="el-icon-close" @click="cancelEdit()"></el-button>
                  <el-button v-role="['doctor', 'admin']" type="danger" size="mini" icon="el-icon-delete"
                    @click="deleteMed(scope.row.id)"></el-button>
                </template>
              </el-table-column>
            </el-table>
          </el-row>
          <el-divider />
          <el-row :gutter="20">
            <el-button v-role="['doctor', 'admin']" type="success" @click="addMeds()">
              {{ isEditMode ? 'Update' : 'Add' }}
            </el-button>
            <el-button v-role="['doctor', 'admin']" type="info" @click="importMedicine()">
              Import
            </el-button>
          </el-row>
        </el-card>
      </div>

      <!-- Diagnostics Section -->
      <div v-if="tab === 'fifth'" class="mobile-section">
        <h3>Diagnostics</h3>
        <el-card style="max-width: 100%">
          <el-radio-group v-model="form.fasting_mode">
            <el-radio label="1">Fasting 8-10 hours </el-radio>
            <el-radio label="2">Fasting 10-12 hours </el-radio>
            <el-radio label="3">Non-fasting</el-radio>
          </el-radio-group>
          <el-checkbox v-model="form.sendXrayToEmail" label="Send X-ray images" size="large" />
          <el-row>
            <el-form label-position="top" class="demo-form-inline">
              <el-form-item label="Remarks">
                <el-input v-model="form.lab_remarks" type="textarea" />
              </el-form-item>
            </el-form>
          </el-row>
          <br />
          <el-button type="primary" @click="viewDiagnosticsTbl = true">View Diagnostics</el-button>
          <el-row :gutter="20">
            <el-table :data="diagnostic_list" style="width: 100%">
              <el-table-column prop="diagnostic" label="Procedure" />
              <el-table-column prop="remarks" label="Remarks" />
              <el-table-column align="center" label="Actions" width="200">
                <template slot-scope="scope">
                  <el-button v-role="['doctor', 'admin']" type="danger" size="small" icon="el-icon-delete"
                    @click="removeProcedure(scope.row.id)">
                    Delete
                  </el-button>
                </template>
              </el-table-column>
            </el-table>
          </el-row>
          <el-divider />
        </el-card>
      </div>

      <!-- Medical Certificate Section -->
      <div v-if="tab === 'medcert'" class="mobile-section">
        <h3>Medical Certificate</h3>
        <el-card style="max-width: 100%">
          <el-form label-position="top">
            <el-row :gutter="20">
              <el-col :span="24">
                <el-form-item label="Undersigned Date">
                  <date-picker v-model="form.medcert_undersigned" valueType="format"></date-picker>
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="Diagnosis">
                  <el-input v-model="form.medcert_diagnosis" type="textarea" rows="8" />
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="Remarks">
                  <el-input v-model="form.medcert_remarks" type="textarea" rows="8" />
                </el-form-item>
              </el-col>
            </el-row>
          </el-form>
        </el-card>
      </div>

      <!-- Referral Section -->
      <div v-if="tab === 'referral'" class="mobile-section">
        <h3>Referral</h3>
        <el-card style="max-width: 100%">
          <el-form label-position="top">
            <el-row :gutter="20">
              <el-col :span="24">
                <el-form-item label="Doctor">
                  <el-input v-model="form.referral_doctor" />
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="Address 1">
                  <el-input v-model="form.referral_addr1" />
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="Address 2">
                  <el-input v-model="form.referral_addr2" />
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="Undersigned Date">
                  <date-picker v-model="form.referral_undersigned" valueType="format"></date-picker>
                </el-form-item>
              </el-col>
            </el-row>
            <el-row :gutter="20">
              <el-col :span="24">
                <el-form-item label="Diagnosis">
                  <el-input v-model="form.referral_diagnosis" type="textarea" rows="8" />
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="Remarks">
                  <el-input v-model="form.referral_remarks" type="textarea" rows="8" />
                </el-form-item>
              </el-col>
            </el-row>
          </el-form>
        </el-card>
      </div>

      <!-- Obstetric and Gynecologic History Section -->
      <div v-if="tab === 'obgyn'" class="mobile-section">
        <h3>Obstetric and Gynecologic History</h3>
        <el-card style="max-width: 100%">
          <el-form label-position="top">
            <el-row :gutter="20">
              <el-col :span="24">
                <el-form-item label="Pregnancy">
                  <el-input v-model="form.pregnancy" type="textarea" rows="4" placeholder="Enter pregnancy history" />
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="LMP (Last Menstrual Period)">
                  <el-date-picker
                    v-model="form.lmp"
                    type="date"
                    placeholder="Select LMP date"
                    format="yyyy-MM-dd"
                    value-format="yyyy-MM-dd"
                    style="width: 100%"
                  />
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="Contraceptive Use">
                  <el-input v-model="form.contraceptive_use" type="textarea" rows="4" placeholder="Enter contraceptive use history" />
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="Menopause">
                  <el-input v-model="form.menopause" type="textarea" rows="4" placeholder="Enter menopause information" />
                </el-form-item>
              </el-col>
            </el-row>
          </el-form>
        </el-card>
      </div>

      <!-- Attachments Section -->
      <div v-if="tab === 'attachments'" class="mobile-section">
        <h3>Attachments</h3>
        <div class="mb-4">
          <el-upload ref="uploadRef" action="#" :auto-upload="false" multiple :on-change="handleChange" :disabled="isUploading">
            <template #trigger>
              <el-button ref="uploadRef" size="small" type="info" action="#" :auto-upload="false" multiple
                :on-change="handleChange" :disabled="isUploading">Select attachments</el-button>
            </template>
            <el-button size="small" type="primary" @click="submitUpload" :loading="isUploading" :disabled="isUploading">
              {{ isUploading ? 'Uploading...' : 'Submit' }}
            </el-button>
          </el-upload>
          
          <!-- Upload Progress -->
          <div v-if="isUploading" class="mt-3">
            <el-progress :percentage="uploadProgress" :status="uploadProgress === 100 ? 'success' : ''"></el-progress>
            <p class="text-sm text-gray-600 mt-2">{{ uploadStatus }}</p>
          </div>
        </div>
        <el-card style="max-width: 100%">
          <el-dialog :visible.sync="dialogVisible" width="50%">
            <el-image :src="selectedImage.file" :alt="selectedImage.alt" fit="contain" class="popup-image" />
            <span slot="footer" class="dialog-footer">
              <el-button @click="dialogVisible = false">Close</el-button>
            </span>
          </el-dialog>

          <el-table :data="attachments" style="width: 100%">
            <el-table-column label="Image">
              <template slot-scope="scope">
                <el-button type="primary" icon="el-icon-files"
                  @click="viewFile(scope.row.newfile, scope.row.extension)" />
                {{ scope.row.fname }}
              </template>
            </el-table-column>
            <el-table-column label="Name" prop="description" />
            <el-table-column label="Date" prop="created_dt" />
            <el-table-column label="Action" width="100">
              <template slot-scope="scope">
                <el-button type="danger" icon="el-icon-delete" @click="deleteAtt(scope.row.id)" />
              </template>
            </el-table-column>
          </el-table>

          <el-dialog :visible.sync="viewFileModel" :fullscreen="false" :close-on-click-modal="false">
            <template #default>
              <div class="iframe-wrapper">
                <iframe v-if="isPdf" :src="sourceFile" :style="transformStyle" frameborder="0"
                  class="iframe-full"></iframe>
                <el-image v-if="!isPdf" style="width: 100px; height: 100px" :src="sourceFile" :zoom-rate="1.2"
                  :max-scale="7" :min-scale="0.2" :preview-src-list="[sourceFile]" show-progress :initial-index="4"
                  fit="cover" />
              </div>
            </template>
          </el-dialog>
        </el-card>
      </div>
    </div>
  </div>
</template>
<script>
import role from "@/directive/role/index.js";
import Patients from "@/api/patients";
import Medicine from "@/api/medicine";
import Procedure from "@/api/procedure";
import Services from "@/api/services";
import Diagnostics from "@/api/diagnostics";
import { getPeTemplates, createPeTemplate, updatePeTemplate, deletePeTemplate } from "@/api/peTemplates";
import moment from "moment-timezone";
import debounce from "lodash/debounce";
import checkRole from "@/utils/role"; // Role checking
import DatePicker from "vue2-datepicker";
import heic2any from "heic2any";
export default {
  components: { DatePicker },
  directives: { role },
  data() {
    return {
      isPdf: false,
      rotation: 0,
      popconfirmUpddateDiagnosis: false,
      viewFileModel: false,
      sourceFile: null,
      pageloading: true,
      appointment_dt: "",
      vitalsDiaglog: false,
      vitals_records: [],
      selectedOldRecords: {},
      oldRecordsdialogVisible: false,
      historyDiaglog: false,
      viewServicesTbl: false,
      viewDiagnosticsTbl: false,
      dialogVisible: false,
      isMobile: false,
      dialogFormVisible: false,
      isProcessing: false,
      profile: {},
      // Upload progress tracking
      isUploading: false,
      uploadProgress: 0,
      uploadStatus: '',
      fam: [],
      soc: [],
      loading: true,
      tab: "first",
      rx_list: [],
      diagnostic_list: [],
      services_list: [],
      form: {
      smoking_details: '',
      alcohol_details: '',
        fasting_mode: "",
        sendXrayToEmail: false,
        email: "",
        prev_admission: "",
        prev_surgeries: "",
        allergies: "",
        asthma: "",
        hypertension: "",
        tb: "",
        seizure: "",
        diabetes: "",
        copd: "",
        pmh_others: "",
        clearance_remarks: "",
        history: "",
        pmr: "",
        pe: "",
        diagnosis: "",
        nurse_remarks: "",
        plan: "",
        height: null,
        bmi: null,
        discount: 0,
        medcert_diagnosis: "",
        medcert_remarks: "",
        medcert_undersigned: null,
        diagnostics_remarks: "",
        lab_remarks: "",
        ancillary_remarks: "",
        followup: null,
        medcert_opt1: false,
        medcert_opt2: false,
        medcert_opt3: false,
        medcert_opt4: false,
        medcert_opt1_text1: "",
        medcert_opt4_text1: "",
        medcert_opt4_text2: "",
        medcert_opt4_text3: "",
        remarks: "",
        medsArr: [
          {
            qty: "",
            bf_b: "",
            bf_a: "",
            l_a: "",
            l_b: "",
            s_b: "",
            s_a: "",
            bt: "",
            meds: "",
            id: "",
            remarks: "",
          },
        ],
        procedures: [
          {
            procedure: "",
            id: 0,
            remarks: "",
            type: 0,
          },
        ],
        services: [
          {
            service: "",
            id: 0,
            fee: 0,
            discount: 0,
          },
        ],
        id: this.$route.params.id,
        cc: "",
        obmens: "",
        ob_g: "",
        ob_p: "",
        ob_tpal: "",
        ob_remarks: "",
        mens_m: "",
        mens_i: "",
        mens_d: "",
        mens_a: "",
        mens_s: "",
        mens_menu: "",
        hpi: "",
        sig_labs: "",
        pmhx: "",
        recommendations: "",
        findings: "",
        vit_sys: "",
        vit_dia: "",
        weight: null,
        vit_temp: "",
        vit_cr: "",
        vit_rr: "",
        pe_head: "",
        pe_ear: "",
        pe_eyes: "",
        pe_nose: "",
        pe_throat: "",
        pe_breast: "",
        pe_chest: "",
        pe_heart: "",
        pe_abdomen: "",
        pe_genito: "",
        pe_extremities: "",
        pe_review: "",
        pe_pq1: "",
        pe_pq2: "",
        pe_pq3: "",
        pe_pq4: "",
        pe_pq5: "",
        pe_pq6: "",
        pe_pq7: "",
        pe_pq8: "",
        pe_pq9: "",
        pe_ext: "",
        pe_cer: "",
        pe_uterus: "",
        pe_adnexa: "",
        pe_dish: "",
        withs2: false,
        pregnancy: "",
        lmp: "",
        contraceptive_use: "",
        menopause: "",
        mother_details: "",
        father_details: "",
      },
      medsArr: {
        custom_meds: false,
        qty: "",
        bf_b: "",
        bf_a: "",
        l_a: "",
        l_b: "",
        s_b: "",
        s_a: "",
        bt: "",
        meds: "",
        med_id: 0,
        id: this.$route.params.id,
        remarks: "",
        custom_generic: "",
        custom_brand: "",
        custom_dosage: "",
      },
      isEditMode: false,
      editingMedId: null,
      procedure: {
        procedure: "",
        procedure_id: 0,
        id: this.$route.params.id,
        remarks: "",
        type: 0,
      },
      service: {
        service: "",
        // service_selected: [],
        id: this.$route.params.id,
        fee: 0,
        service_id: 0,
        // discount: 0,
      },
      form_att: {
        patientid: "",
        file: "",
      },
      form_cancel: {
        id: this.$route.params.id,
        cancel_reason: "",
      },
      selectedImage: {},
      attachments: [],
      patientid_id: 0,
      rules: {
        cancel_reason: [
          { required: true, message: "Please provide reason", trigger: "blur" },
        ],
      },
      getAllServicesOffered: [],
      servicesRendered: {
        id: this.$route.params.id,
        discount: 0,
        rendered: [],
      },
      servicesRenderedModel: [],
      old_records: [],
      getAllDiagnosticsOfferedLab: [],
      getAllDiagnosticsOfferedImg: [],
      diagnosticsRendered: {
        id: this.$route.params.id,
        rendered: [],
      },
      diagnosticsRenderedModel: [],
      lab_others: null,
      anc_others: null,
      uniqueArray: [],
      scale: 1,
      getAllDiagnosticsOfferedLab: [],
      getAllDiagnosticsOfferedImg: [],
      getAllDiagnosticsOfferedHema: [],
      getAllDiagnosticsOfferedChem: [],
      getAllDiagnosticsOfferedBleed: [],
      getAllDiagnosticsOfferedCardiac: [],
      getAllDiagnosticsOfferedXray: [],
      getAllDiagnosticsOfferCardiac: [],
      getAllDiagnosticsOfferedCardiacTest: [],
      getAllDiagnosticsOfferedCt: [],
      getAllDiagnosticsOfferedMri: [],
      getAllDiagnosticsOfferedVascular: [],
      getAllDiagnosticsOfferedUtz: [],
      getAllDiagnosticsOfferedOth: [],
      getAllDiagnosticsOfferedOthers: [],
      getAllDiagnosticsOfferedImmonulogy: [],
      getAllDiagnosticsOfferedMirco: [],
      getAllDiagnosticsOfferedCrystal: [],
      getAllDiagnosticsOfferedMicroscopy: [],
      lab_micro_remarks: "",
      xray_remarks: "",

      // Physical Examination Templates
      showCustomTemplateDialog: false,
      peTemplates: [],
      customTemplateForm: {
        name: "",
        content: ""
      },
    };
  },
  watch: {
    form: {
      handler: debounce(function () {
        this.onSubmit();
      }, 5000), // Adjust debounce time
      deep: true,
    },
    "form.weight": "calculateBMI",
    "form.height": "calculateBMI",
  },
  mounted() {
    this.checkIfMobile();
    window.addEventListener("resize", this.checkIfMobile);
  },
  beforeDestroy() {
    window.removeEventListener("resize", this.checkIfMobile);
  },
  created() {
    this.getAllDiagnostics();
    this.appointments();
    this.getmeds();
    this.getdiagnostics();
    this.getservices();
    this.loadTemplatesFromDatabase();
  },
  computed: {
    transformStyle() {
      return {
        transform: `rotate(${this.rotation}deg)`,
        transformOrigin: "center center",
        display: "inline-block",
      };
    },
    availableTabs() {
      const tabs = [
        {
          name: 'history',
          label: 'Histories',
          available: this.checkRole(['admin', 'doctor']),
          hasContent: this.hasHistoryContent()
        },
        {
          name: 'family',
          label: 'Family History',
          available: this.checkRole(['admin', 'doctor']),
          hasContent: this.hasFamilyContent()
        },
        {
          name: 'soc',
          label: 'Social/Environment History',
          available: this.checkRole(['admin', 'doctor']),
          hasContent: this.hasSocialContent()
        },
        {
          name: 'first',
          label: 'Diagnosis',
          available: true,
          hasContent: this.hasDiagnosisContent()
        },
        {
          name: 'second',
          label: 'Vitals',
          available: true,
          hasContent: this.hasVitalsContent()
        },
        {
          name: 'fourth',
          label: 'Medicines',
          available: this.checkRole(['admin', 'doctor']),
          hasContent: this.rx_list && this.rx_list.length > 0
        },
        {
          name: 'fifth',
          label: 'Diagnostics',
          available: this.checkRole(['admin', 'doctor']),
          hasContent: this.diagnostic_list && this.diagnostic_list.length > 0
        },
        {
          name: 'medcert',
          label: 'Medical Certificate',
          available: this.checkRole(['admin', 'doctor']),
          hasContent: this.hasMedCertContent()
        },
        {
          name: 'referral',
          label: 'Referral',
          available: this.checkRole(['admin', 'doctor']),
          hasContent: this.hasReferralContent()
        },
        {
          name: 'obgyn',
          label: 'Obstetric and Gynecologic History',
          available: this.checkRole(['admin', 'doctor']),
          hasContent: this.hasObgynContent()
        },
        {
          name: 'attachments',
          label: 'Attachments',
          available: true,
          hasContent: this.attachments && this.attachments.length > 0
        }
      ];
      return tabs.filter(tab => tab.available);
    }
  },
  methods: {
    checkRole,

    // Content checking methods for tab indicators
    hasHistoryContent() {
      return !!(this.profile.prev_admission || this.profile.prev_surgeries || 
                this.profile.allergies || this.profile.asthma || 
                this.profile.hypertension || this.profile.tb || 
                this.profile.seizure || this.profile.diabetes || 
                this.profile.copd || this.profile.pmh_others);
    },
    hasFamilyContent() {
      return !!(this.fam && this.fam.length > 0) || !!this.profile.fam_others || 
             !!this.form.mother_details || !!this.form.father_details;
    },
    hasSocialContent() {
      return !!(this.soc && this.soc.length > 0) || !!this.profile.soc_others || !!this.profile.vaccination_sup;
    },
    hasDiagnosisContent() {
      return !!(this.form.nurse_remarks || this.form.chiefcomplaints || 
                this.form.history || this.form.pe || 
                this.form.diagnosis || this.form.remarks);
    },
    hasVitalsContent() {
      return !!(this.form.vit_sys || this.form.vit_dia || 
                this.form.weight || this.form.height || 
                this.form.vit_temp || this.form.vit_cr || this.form.vit_rr);
    },
    hasMedCertContent() {
      return !!(this.form.medcert_diagnosis || this.form.medcert_remarks || this.form.medcert_undersigned);
    },
    hasReferralContent() {
      return !!(this.form.referral_doctor || this.form.referral_addr1 || 
                this.form.referral_addr2 || this.form.referral_diagnosis || 
                this.form.referral_remarks || this.form.referral_undersigned);
    },
    hasObgynContent() {
      return !!(this.form.pregnancy || this.form.lmp || 
                this.form.contraceptive_use || this.form.menopause);
    },

    // Physical Examination Template Methods
    insertPETemplate(content) {
      if (this.form.pe) {
        // If there's existing content, add a newline before the template
        this.form.pe += '\n\n' + content;
      } else {
        // If no existing content, just set the template
        this.form.pe = content;
      }
      // Trigger auto-resize
      this.$nextTick(() => {
        this.autoResize();
      });
    },

    async saveCustomTemplate() {
      if (!this.customTemplateForm.name || !this.customTemplateForm.content) {
        this.$message.error('Please fill in both template name and content');
        return;
      }

      // Check if template name already exists
      const existingTemplate = this.peTemplates.find(t => t.name === this.customTemplateForm.name);
      if (existingTemplate) {
        this.$message.error('A template with this name already exists');
        return;
      }

      try {
        const response = await createPeTemplate({
          name: this.customTemplateForm.name,
          content: this.customTemplateForm.content
        });

        if (response.success) {
          // Refresh templates from database
          await this.loadTemplatesFromDatabase();

          // Clear the form
          this.customTemplateForm.name = '';
          this.customTemplateForm.content = '';

          // Close the dialog
          this.showCustomTemplateDialog = false;

          this.$message.success('Custom template saved successfully!');
        } else {
          this.$message.error(response.message || 'Failed to save template');
        }
      } catch (error) {
        console.error('Error saving template:', error);
        this.$message.error('Failed to save template. Please try again.');
      }
    },

    async loadTemplatesFromDatabase() {
      try {
        const response = await getPeTemplates();
        if (response.success) {
          this.peTemplates = response.data;
        } else {
          console.error('Failed to load templates:', response.message);
        }
      } catch (error) {
        console.error('Failed to load templates from database:', error);
        // Fallback to empty array
        this.peTemplates = [];
      }
    },

    async deleteTemplate(templateId) {
      this.$confirm('Are you sure you want to delete this template?', 'Confirm Delete', {
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(async () => {
        try {
          const response = await deletePeTemplate(templateId);

          if (response.success) {
            // Refresh templates from database
            await this.loadTemplatesFromDatabase();
            this.$message.success('Template deleted successfully');
          } else {
            this.$message.error(response.message || 'Failed to delete template');
          }
        } catch (error) {
          console.error('Error deleting template:', error);
          this.$message.error('Failed to delete template. Please try again.');
        }
      }).catch(() => {
        // User cancelled deletion
      });
    },
    handleCommand(command) {
      if (command === "update_diagnosis") {
        this.popconfirmUpddateDiagnosis = true;
        this.$nextTick(() => {
          this.$refs.updateDiagnosisBtn.$el.click();
        });
      }
      if (command === "print_rx") {
        this.printpdf2();
      }
      if (command === "email_rx") {
        this.emailPrescription();
      }
      if (command === "print_labs") {
        this.printrequest(1);
      }
      if (command === "print_ancillary") {
        this.printrequest(2);
      }
      if (command === "print_medcert") {
        this.printmedcert();
      }
      if (command === "print_riskstrat") {
        this.printriskstrat();
      }
      if (command === "print_fit") {
        this.printfittowork();
      }
      if (command === "print_clearance") {
        this.printclearance();
      }
      if (command === "print_referral") {
        this.printreferral();
      }
      if (command === "cancel_apt") {
        this.cancelAppointment();
      }
      if (command === "view_chart") {
        this.printChart();
      }
      if (command === "done_consult") {
        this.doneConsult();
      } else {
        console.log(command);
      }
    },
    autoResize() {
      this.$nextTick(() => {
        const textarea = this.$refs.peInput.$el.querySelector('textarea')
        const textarea2 = this.$refs.plansInput.$el.querySelector('textarea')
        if (textarea) {
          textarea.style.height = 'auto'               // reset height
          textarea.style.height = textarea.scrollHeight + 'px' // set new height
        }
        if (textarea2) {
          textarea2.style.height = 'auto'               // reset height
          textarea2.style.height = textarea2.scrollHeight + 'px' // set new height
        }
      })
    },
    onSubmit() {
      this.form.followup = moment
        .tz(this.form.followup, "Asia/Manila")
        .format("YYYY-MM-DD"); // moment(this.form.followup).tz('Asia/Manila').format('YYYY-MM-DD');
      this.form.medcert_undersigned = moment
        .tz(this.form.medcert_undersigned, "Asia/Manila")
        .format("YYYY-MM-DD"); // moment(this.form.medcert_undersigned).tz('Asia/Manila').format('YYYY-MM-DD');
      this.form.prev_admission = this.profile.prev_admission;
      // this.form.email = this.profile.email;
      this.form.prev_surgeries = this.profile.prev_surgeries;
      this.form.allergies = this.profile.allergies;
      this.form.asthma = this.profile.asthma;
      this.form.hypertension = this.profile.hypertension;
      this.form.tb = this.profile.tb;
      this.form.seizure = this.profile.seizure;
      this.form.diabetes = this.profile.diabetes;
      this.form.copd = this.profile.copd;
      this.form.pmh_others = this.profile.pmh_others;

      let famVal = "";
      this.fam.forEach((element, index) => {
        if (index === this.fam.length - 1) {
          famVal += element;
        } else {
          famVal += element + ",";
        }
      });
      this.form.fam = famVal;
      this.form.fam_others = this.profile.fam_others;

      let socVal = "";
      this.soc.forEach((element, index) => {
        if (index === this.fam.length - 1) {
          socVal += element;
        } else {
          socVal += element + ",";
        }
      });
      this.form.soc = socVal;
      this.form.soc_others = this.profile.soc_others;
      this.form.vaccination_sup = this.profile.vaccination_sup;

      Patients.updateDiagnose(this.form)
        .then((response) => {
          this.$message({
            message: "Diagnosis has been created successfully.",
            type: "success",
            duration: 5 * 1000,
          });
        })
        .catch((err) => {
          console.error("Error adding suggestions:", err);
        });
    },
    checkIfMobile() {
      this.isMobile = window.innerWidth <= 768;
      console.log(this.isMobile);
    },
    onCancel() {
      this.$message({
        message: "cancel!",
        type: "warning",
      });
    },
    currentDt() {
      return moment(this.appointment_dt).format("MMMM DD, YYYY");
    },
    appointments() {
      Patients.getAppointment(this.form.id)
        .then((response) => {
          this.autoResize();
          this.pageloading = false;
          this.appointment_dt = response.data.appointment_dt;
          this.vitals_records = response.vitals_data;
          this.old_records = response.get_OldDiagnosis;
          this.form.email = response.data.email
            ? response.data.email
            : response.px_profile.email;
          this.profile = response.px_profile;
          this.profile.age = this.getAge(response.px_profile.birthdate);
          this.profile.status = response.px_profile.civil_status;
          this.profile.address = response.px_profile.address;
          /* this.profile.photo =
            response.px_profile.isold_patient === 1
              ? "/public/photos/" + response.px_profile.patientid + ".jpg"
              : response.px_profile.profile; */
          this.profile.photo = response.px_profile.profile_name;
          this.form = response.data;
          this.form_att.patientid = response.px_profile.patientid;
          this.patientid_id = response.px_profile.patientid;
          this.get_attachments(response.px_profile.patientid);

          if (response.px_profile.fam) {
            const fam = response.px_profile.fam.split(",");
            fam.forEach((element) => {
              this.fam.push(element);
            });
          }

          if (response.px_profile.soc) {
            const soc = response.px_profile.soc.split(",");
            soc.forEach((element) => {
              this.soc.push(element);
            });
          }

          if (this.form.cc === null) {
            this.form.cc = response.prev_data.cc;
          }

          if (this.form.history === null) {
            this.form.history = response.prev_data.history;
          }

          if (this.form.pe === null) {
            this.form.pe = response.prev_data.pe;
          }

          if (this.form.diagnosis === null) {
            this.form.diagnosis = response.prev_data.diagnosis;
          }

          if (this.form.remarks === null) {
            this.form.remarks = response.prev_data.remarks;
          }
          this.form.pregnancy = response.px_profile.pregnancy;
          this.form.lmp = response.px_profile.lmp;
          this.form.contraceptive_use = response.px_profile.contraceptive_use;
          this.form.menopause = response.px_profile.menopause;
          this.form.mother_details = response.px_profile.mother_details;
          this.form.father_details = response.px_profile.father_details;
          
        })
        .catch((error) => {
          console.log(error);
        });
    },
    async querySearch(queryString, cb) {
      if (queryString === "") {
        // If query string is empty, reset suggestions
        cb([]);
        return;
      }
      try {
        this.loading = true;
        // Make an asynchronous request to your Laravel backend API using Axios
        const response = await Medicine.findmedicine(queryString);
        // Extract the array of suggestions from the response data
        const suggestions = response.suggestions;
        // Call back function
        cb(suggestions);
      } catch (error) {
        console.error("Error fetching suggestions:", error);
        cb([]);
      } finally {
        this.loading = false;
      }
    },
    handleSelect(ev) {
      this.medsArr.meds = ev.medicine;
      this.medsArr.med_id = ev.id;
    },
    removeItem(id) { },
    async queryProcedure(queryString, cb) {
      if (queryString === "") {
        cb([]);
        return;
      }
      try {
        this.loading = true;
        const response = await Procedure.findprocedure(queryString);
        const suggestions = response.suggestions;
        cb(suggestions);
      } catch (error) {
        console.error("Error fetching suggestions:", error);
        cb([]);
      } finally {
        this.loading = false;
      }
    },
    handleSelectProcedure(ev) {
      this.procedure.procedure = ev.procedure;
      this.procedure.type = ev.type;
      this.procedure.procedure_id = ev.id;
    },
    removeProcedure(index) {
      this.$confirm("Are you sure you want to delete this item?", "Warning", {
        confirmButtonText: "OK",
        cancelButtonText: "Cancel",
        type: "warning",
      })
        .then(() => {
          Procedure.remove_diagnostic(index)
            .then((response) => {
              this.getdiagnostics();
            })
            .catch((err) => {
              console.error("Error adding suggestions:", err);
            });
        })
        .catch(() => {
          this.$message({
            type: "info",
            message: "Delete canceled",
          });
        });
    },
    async queryService(queryString, cb) {
      if (queryString === "") {
        // If query string is empty, reset suggestions
        cb([]);
        return;
      }
      try {
        this.loading = true;
        // Make an asynchronous request to your Laravel backend API using Axios
        const response = await Services.findservices(queryString);
        // Extract the array of suggestions from the response data
        const suggestions = response.suggestions;
        // Call back function
        cb(suggestions);
      } catch (error) {
        console.error("Error fetching suggestions:", error);
        cb([]);
      } finally {
        this.loading = false;
      }
    },
    handleSelectService(ev) {
      this.service.service = ev.service;
      this.service.fee = ev.fee;
      this.service.service_id = ev.id;
    },
    removeService(row) {
      this.$confirm("Are you sure you want to delete this item?", "Warning", {
        confirmButtonText: "OK",
        cancelButtonText: "Cancel",
        type: "warning",
      })
        .then(() => {
          Services.remove_service(row)
            .then((response) => {
              this.getservices();
            })
            .catch((err) => {
              console.error("Error adding suggestions:", err);
            });
        })
        .catch(() => {
          this.$message({
            type: "info",
            message: "Delete canceled",
          });
        });
    },
    getAllServices() {
      Services.getAllServices()
        .then((response) => {
          this.getAllServicesOffered = response;
        })
        .catch((err) => {
          console.error("Error adding suggestions:", err);
        });
    },
    getAllDiagnostics() {
      Diagnostics.getAllDiagnostics()
        .then((response) => {
          /* this.getAllDiagnosticsOfferedLab = response.filter(
            (e) => e.lab_category_id === 1
          );
          this.getAllDiagnosticsOfferedImg = response.filter(
            (e) => e.lab_category_id === 2
          ); */
          this.getAllDiagnosticsOfferedHema = response.filter(
            (e) => e.lab_category_id === 3
          );
          this.getAllDiagnosticsOfferedChem = response.filter(
            (e) => e.lab_category_id === 4
          );
          this.getAllDiagnosticsOfferedBleed = response.filter(
            (e) => e.lab_category_id === 6
          );
          this.getAllDiagnosticsOfferedCardiac = response.filter(
            (e) => e.lab_category_id === 5
          );
          this.getAllDiagnosticsOfferedCardiacTest = response.filter(
            (e) => e.lab_category_id === 10
          );
          this.getAllDiagnosticsOfferedXray = response.filter(
            (e) => e.lab_category_id === 9
          );
          this.getAllDiagnosticsOfferCardiac = response.filter(
            (e) => e.lab_category_id === 10
          );
          this.getAllDiagnosticsOfferedCt = response.filter(
            (e) => e.lab_category_id === 12
          );
          this.getAllDiagnosticsOfferedMri = response.filter(
            (e) => e.lab_category_id === 11
          );
          this.getAllDiagnosticsOfferedUtz = response.filter(
            (e) => e.lab_category_id === 13
          );
          this.getAllDiagnosticsOfferedVascular = response.filter(
            (e) => e.lab_category_id === 14
          );
          this.getAllDiagnosticsOfferedOth = response.filter(
            (e) => e.lab_category_id === 15
          );
          /* this.getAllDiagnosticsOfferedOthers = response.filter(
            (e) => e.lab_category_id === 16
          ); */
          this.getAllDiagnosticsOfferedImmonulogy = response.filter(
            (e) => e.lab_category_id === 17
          );
          this.getAllDiagnosticsOfferedMirco = response.filter(
            (e) => e.lab_category_id === 18
          );
          this.getAllDiagnosticsOfferedCrystal = response.filter(
            (e) => e.lab_category_id === 19
          );
          this.getAllDiagnosticsOfferedMicroscopy = response.filter(
            (e) => e.lab_category_id === 20
          );
        })
        .catch((err) => {
          console.error("Error adding suggestions:", err);
        });
    },
    addServices() {
      if (this.servicesRendered.rendered.length > 0) {
        Services.add_service(this.servicesRendered)
          .then((response) => {
            this.getservices();
            this.service.service = "";
            this.service.fee = 0;
            this.service.discount = 0;
            this.servicesRendered.rendered = [];
            this.servicesRenderedModel = [];
          })
          .catch((err) => {
            console.error("Error adding suggestions:", err);
          });
      } else {
        alert("Service is required.");
      }
    },
    addNewServices(e) {
      if (this.servicesRenderedModel.length > 0) {
        this.servicesRendered.rendered.push({
          service: e.description,
          id: this.$route.params.id,
          fee: e.fee,
          service_id: e.service_id,
        });
      } else {
        alert("Procedure is required.");
      }
    },
    editMed(row) {
      this.isEditMode = true;
      this.editingMedId = row.id;

      // Populate the form with the medicine data
      this.medsArr.qty = row.qty || "";
      this.medsArr.bf_b = row.bb || "";
      this.medsArr.bf_a = row.ab || "";
      this.medsArr.l_b = row.bl || "";
      this.medsArr.l_a = row.al || "";
      this.medsArr.s_b = row.bs || "";
      this.medsArr.s_a = row.as || "";
      this.medsArr.bt = row.bt || "";
      this.medsArr.remarks = row.remarks || "";
      this.medsArr.med_id = row.medicineId;
      
      // Handle medicine selection
      if (row.medicineId != 0) {
        this.medsArr.meds = row.medicine;
        //this.medsArr.med_id = row.med_id || row.id;
        this.medsArr.custom_meds = false;
      } else {
        // Custom medicine - check if it's a custom medicine entry
        this.medsArr.custom_meds = true;
        this.medsArr.custom_generic = row.generic;
        this.medsArr.custom_brand = row.brand;
        //this.medsArr.custom_dosage = row.dosage || "";
      }
    },
    deleteMed(row) {
      this.$confirm("Are you sure you want to delete this item?", "Warning", {
        confirmButtonText: "OK",
        cancelButtonText: "Cancel",
        type: "warning",
      })
        .then(() => {
          Medicine.remove_rx(row)
            .then((response) => {
              this.getmeds();
            })
            .catch((err) => {
              console.error("Error adding suggestions:", err);
            });
        })
        .catch(() => {
          this.$message({
            type: "info",
            message: "Delete canceled",
          });
        });
    },
    cancelEdit() {
      this.isEditMode = false;
      this.editingMedId = null;
      this.clearMedsForm();
    },
    clearMedsForm() {
      this.medsArr.qty = "";
      this.medsArr.bf_b = "";
      this.medsArr.bf_a = "";
      this.medsArr.l_a = "";
      this.medsArr.l_b = "";
      this.medsArr.s_b = "";
      this.medsArr.s_a = "";
      this.medsArr.bt = "";
      this.medsArr.meds = "";
      this.medsArr.remarks = "";
      this.medsArr.custom_generic = "";
      this.medsArr.custom_brand = "";
      this.medsArr.custom_dosage = "";
      this.medsArr.custom_meds = false;
      this.medsArr.med_id = 0;
      this.isEditMode = false;
      this.editingMedId = null;
    },
    newMedicine() {
      this.clearMedsForm();
    },
    addMeds() {
      if (
        (this.medsArr.meds !== "" && this.medsArr.qty !== "") ||
        (this.medsArr.custom_generic !== "" && this.medsArr.custom_brand !== ""
          
        )
      ) {
        if (this.isEditMode) {
          // Update existing medicine
          Medicine.update_rx(this.editingMedId, this.medsArr)
            .then((response) => {
              this.getmeds();
              this.isEditMode = false;
              this.editingMedId = null;
              this.clearMedsForm();
              this.$message({
                type: "success",
                message: "Medicine updated successfully.",
              });
            })
            .catch((err) => {
              console.error("Error updating medicine:", err);
              this.$message.error("Failed to update medicine.");
            });
        } else {
          // Add new medicine
          Medicine.add_rx(this.medsArr)
            .then((response) => {
              this.getmeds();
              this.clearMedsForm();
              this.$message({
                type: "success",
                message: "Medicine added successfully.",
              });
            })
            .catch((err) => {
              console.error("Error adding medicine:", err);
              this.$message.error("Failed to add medicine.");
            });
        }
      } else {
        alert("Medicine details are required.");
      }
    },
    addProcedure() {
      if (this.diagnosticsRendered.rendered.length > 0) {
        Procedure.add_diagnostic(this.diagnosticsRendered)
          .then((response) => {
            this.getdiagnostics();
            this.procedure.procedure = "";
            this.procedure.remarks = "";
            this.procedure.type = 0;
            this.diagnosticsRendered.rendered = [];
            this.diagnosticsRenderedModel = [];
            this.form.lab_others = null;
            this.form.anc_others = null;
            this.viewDiagnosticsTbl = false;
          })
          .catch((err) => {
            console.error("Error adding suggestions:", err);
          });
      } else {
        alert("Diagnostic are required.");
      }
    },
    addNewProcedure2(e) {
      console.log(e)
      if (this.diagnosticsRenderedModel.length > 0) {
        this.diagnosticsRendered.rendered.push({
          id: this.$route.params.id,
          procedure_id: e.lab_test_id,
          procedure: e.lab_test,
          ccccremarks: this.xray_remarks,
          type: e.lab_category_id,
          ssssslab_micro_remarks: this.lab_micro_remarks,
          ccccccxray_remarks: e.lab_test_id == 591 ? this.xray_remarks : '',
        });
      }
    },
    addNewProcedure(e) {
      if (this.diagnosticsRenderedModel.includes(e.lab_test)) {
        // Add only if not already added
        if (!this.diagnosticsRendered.rendered.find(p => p.procedure_id === e.lab_test_id)) {
          this.diagnosticsRendered.rendered.push({
            id: this.$route.params.id,
            procedure_id: e.lab_test_id,
            procedure: e.lab_test,
            remarks: "",
            type: e.lab_category_id,
            lab_micro_remarks: this.lab_micro_remarks,
            xray_remarks: ""
          });
        }
      } else {
        // Remove if unchecked
        this.diagnosticsRendered.rendered = this.diagnosticsRendered.rendered.filter(
          p => p.procedure_id !== e.lab_test_id
        );
      }
    },
    findProcedure(id) {
      return this.diagnosticsRendered.rendered.find(p => p.procedure_id === id) || {};
    },
    addLabOthers(v) {
      if (v !== null) {
        this.diagnosticsRendered.rendered.push({
          id: this.$route.params.id,
          procedure_id: 0,
          procedure: v,
          remarks: "",
          type: 1,
          lab_micro_remarks: this.lab_micro_remarks,
          xray_remarks: ""
        });
      }
    },
    addAncOthers(v) {
      if (v !== null) {
        this.diagnosticsRendered.rendered.push({
          id: this.$route.params.id,
          procedure_id: 0,
          procedure: v,
          remarks: "",
          type: 2,
        });
      }
    },
    getmeds() {
      this.rx_list = [];
      Medicine.getAppointmentMeds(this.$route.params.id)
        .then((response) => {
          this.rx_list = response.data;
        })
        .catch((err) => {
          console.error("Error adding suggestions:", err);
        });
    },
    getdiagnostics() {
      this.diagnostic_list = [];
      Procedure.getAppointmentDiagnostics(this.$route.params.id)
        .then((response) => {
          this.diagnostic_list = response.data;
        })
        .catch((err) => {
          console.error("Error adding suggestions:", err);
        });
    },
    getservices() {
      this.services_list = [];
      Services.getAppointmentService(this.$route.params.id)
        .then((response) => {
          this.services_list = response.data;
        })
        .catch((err) => {
          console.error("Error adding suggestions:", err);
        });
    },
    printpdf() {
      window.open("/api/printpdf/" + this.form.id);
    },
    printpdf2() {
      window.open("/api/printpdf2/" + this.form.id);
    },
    /* emailpdf() {
      window.open("/api/email-prescription/" + this.form.id);
    },
    emailPrescription() {
      Patients.emailPrescription(this.form.id)
        .then((response) => {
          this.$message({
            message: "Prescription has been sent to his/her email.",
            type: "success",
            duration: 5 * 1000,
          });
        })
        .catch((err) => {
          console.error("Error adding suggestions:", err);
        });
    }, */
    printrequest(type) {
      window.open("/api/printrequest/" + this.form.id + "/" + type);
    },
    printmedcert(type) {
      window.open("/api/printmedcert/" + this.form.id);
    },
    printriskstrat(type) {
      window.open("/api/printriskstrat/" + this.form.id);
    },
    printfittowork(type) {
      window.open("/api/printfittowork/" + this.form.id);
    },
    printclearance(type) {
      window.open("/api/printclearance/" + this.form.id);
    },
    printreferral(type) {
      window.open("/api/printreferral/" + this.form.id);
    },
    doneConsult() {
      this.$confirm("Are you done with this consultation?", "Warning", {
        confirmButtonText: "OK",
        cancelButtonText: "Cancel",
        type: "warning",
      })
        .then(() => {
          Patients.doneConsult(this.$route.params.id)
            .then((response) => {
              this.$message({
                type: "success",
                message: "Done consultation",
              });
            })
            .catch((err) => {
              console.error("Error adding suggestions:", err);
            });
        })
        .catch(() => {
          this.$message({
            type: "info",
            message: "Canceled action.",
          });
        });
    },
    upDateBP() {
      this.$confirm("Are you done with this update?", "Warning", {
        confirmButtonText: "OK",
        cancelButtonText: "Cancel",
        type: "warning",
      })
        .then(() => {
          const bp = {
            vit_sys: this.form.vit_sys,
            vit_dia: this.form.vit_dia,
            vit_rr: this.form.vit_rr,
            vit_cr: this.form.vit_cr,
            vit_temp: this.form.vit_temp,
            weight: this.form.weight,
            height: this.form.height,
            id: this.$route.params.id,
          };
          Patients.update_bp(bp)
            .then((response) => {
              this.$message({
                type: "success",
                message: "Done updating",
              });
            })
            .catch((err) => {
              console.error("Error adding suggestions:", err);
            });
        })
        .catch(() => {
          this.$message({
            type: "info",
            message: "Canceled action.",
          });
        });
    },
    get_attachments(id) {
      this.attachments = [];
      Patients.getAttachments(id)
        .then((response) => {
          this.attachments = response.data;
        })
        .catch((err) => {
          console.error("Error adding suggestions:", err);
        });
    },
    handleChange(file, fileList) {
      this.form_att.files = fileList.map((fileItem) => fileItem.raw);
    },
    
    // Image compression utility
    compressImage(file, quality = 0.8, maxWidth = 1920, maxHeight = 1080) {
      return new Promise((resolve) => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();
        
        img.onload = () => {
          // Calculate new dimensions
          let { width, height } = img;
          
          if (width > maxWidth || height > maxHeight) {
            const ratio = Math.min(maxWidth / width, maxHeight / height);
            width *= ratio;
            height *= ratio;
          }
          
          // Set canvas dimensions
          canvas.width = width;
          canvas.height = height;
          
          // Draw and compress
          ctx.drawImage(img, 0, 0, width, height);
          
          canvas.toBlob(
            (blob) => {
              const compressedFile = new File([blob], file.name, {
                type: 'image/jpeg',
                lastModified: Date.now()
              });
              resolve(compressedFile);
            },
            'image/jpeg',
            quality
          );
        };
        
        img.src = URL.createObjectURL(file);
      });
    },
    async submitUpload() {
      // Initialize upload progress
      this.uploadProgress = 0;
      this.isUploading = true;
      this.uploadStatus = 'Preparing files...';
      
      const formData = new FormData();
      formData.append("patientid", this.form_att.patientid);

      // Check network connection
      const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
      const isSlowConnection = connection && (connection.effectiveType === 'slow-2g' || connection.effectiveType === '2g');
      
      if (isSlowConnection) {
        this.$message.warning("Slow network detected. Upload may take longer...");
      }

      const totalFiles = this.form_att.files.length;
      let processedFiles = 0;

      for (let i = 0; i < this.form_att.files.length; i++) {
        const file = this.form_att.files[i];
        const extension = file.name.split(".").pop().toLowerCase();
        
        // Update progress
        this.uploadStatus = `Processing file ${i + 1} of ${totalFiles}: ${file.name}`;
        this.uploadProgress = (processedFiles / totalFiles) * 30; // 30% for processing

        // Check file size (10MB limit for mobile data)
        if (file.size > 10 * 1024 * 1024) {
          this.$message.error(`File "${file.name}" is too large. Maximum size is 10MB.`);
          this.isUploading = false;
          return;
        }

        let processedFile = file;

        // Handle HEIC/HEIF conversion
        if (extension === "heic" || extension === "heif") {
          try {
            this.uploadStatus = `Converting HEIC file: ${file.name}`;
            const bmpBlob = await heic2any({
              blob: file,
              toType: "image/bmp",
              quality: 0.9,
            });

            processedFile = new File(
              [bmpBlob],
              file.name.replace(/\.(heic|heif)$/i, ".jpg"),
              { type: "image/bmp" }
            );
          } catch (error) {
            console.error("HEIC conversion failed:", error);
            this.$message.error("HEIC conversion failed.");
            this.isUploading = false;
            return;
          }
        }
        
        // Compress images (JPEG, PNG, BMP, WebP)
        const imageTypes = ['jpg', 'jpeg', 'png', 'bmp', 'webp'];
        if (imageTypes.includes(extension.toLowerCase())) {
          try {
            this.uploadStatus = `Compressing image: ${file.name}`;
            processedFile = await this.compressImage(processedFile, 0.8, 1920, 1080);
            console.log(`Compressed ${file.name}: ${file.size} -> ${processedFile.size} bytes`);
          } catch (error) {
            console.error("Image compression failed:", error);
            // Continue with original file if compression fails
          }
        }

        formData.append(`files[${i}]`, processedFile);
        processedFiles++;
        this.uploadProgress = (processedFiles / totalFiles) * 30; // 30% for processing
      }

      // Retry mechanism for mobile data
      const maxRetries = 3;
      let retryCount = 0;
      
      while (retryCount < maxRetries) {
        try {
          this.uploadStatus = 'Uploading files...';
          this.uploadProgress = 30; // Start upload at 30%
          
          const response = await Patients.addAttachments(formData);
          
          // Upload successful
          this.uploadProgress = 100;
          this.uploadStatus = 'Upload completed!';
          
          this.form_att.file = "";
          this.get_attachments(this.form_att.patientid);
          this.$message.success("File uploaded successfully!");
          this.$refs.uploadRef.clearFiles();
          
          // Reset upload state
          this.isUploading = false;
          this.uploadProgress = 0;
          this.uploadStatus = '';
          
          return; // Success, exit the retry loop
        } catch (err) {
          retryCount++;
          console.error(`Upload attempt ${retryCount} failed:`, err);
          
          // Check if it's a network-related error
          const isNetworkError = !err.response || err.code === 'ECONNABORTED' || err.code === 'NETWORK_ERROR';
          
          if (isNetworkError && retryCount < maxRetries) {
            this.uploadStatus = `Upload failed (attempt ${retryCount}/${maxRetries}). Retrying...`;
            this.$message.warning(`Upload failed (attempt ${retryCount}/${maxRetries}). Retrying...`);
            // Wait before retry (exponential backoff)
            await new Promise(resolve => setTimeout(resolve, Math.pow(2, retryCount) * 1000));
            continue;
          }
          
          // Final error handling
          let errorMessage = "Upload failed.";
          
          if (isNetworkError) {
            errorMessage = "Network error. Please check your connection and try again.";
          } else if (err.response && err.response.data) {
            if (err.response.data.message) {
              errorMessage += " " + err.response.data.message;
            } else if (err.response.data.error) {
              errorMessage += " " + err.response.data.error;
            } else if (typeof err.response.data === 'string') {
              errorMessage += " " + err.response.data;
            }
          } else if (err.message) {
            errorMessage += " " + err.message;
          } else if (typeof err === 'string') {
            errorMessage += " " + err;
          }
          
          this.$message.error(errorMessage);
          break;
        }
      }
      
      // Reset upload state on failure
      this.isUploading = false;
      this.uploadProgress = 0;
      this.uploadStatus = '';
    },
    deleteAtt(id) {
      this.$confirm("Are you done with this file?", "Warning", {
        confirmButtonText: "OK",
        cancelButtonText: "Cancel",
        type: "warning",
      })
        .then(() => {
          Patients.deleteAttachments(id)
            .then((response) => {
              this.$message({
                type: "success",
                message: "Deleted File",
              });
              this.get_attachments(this.form_att.patientid);
            })
            .catch((err) => {
              console.error("Error adding suggestions:", err);
            });
        })
        .catch(() => {
          this.$message({
            type: "info",
            message: "Canceled action.",
          });
        });
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
    cancelAppointment() {
      this.dialogFormVisible = true;
    },
    confirmCancel() {
      this.$refs["appForm"].validate((valid) => {
        if (valid) {
          this.isProcessing = true;
          Patients.cancel_appointment(this.form)
            .then((response) => {
              this.dialogFormVisible = false;
              this.$message({
                message: "Appointment Cancelled",
                type: "success",
                duration: 5 * 1000,
              });
              this.$router.push({ path: "/appointments/appointments" });
            })
            .catch((err) => {
              console.error("Error adding suggestions:", err);
            })
            .finally(() => {
              // This will always run, regardless of the request outcome
              this.isProcessing = false;
            });
          // }, 5000);
        } else {
          console.log("error submit!!");
          return false;
        }
      });
    },
    printChart() {
      window.open("/api/printchart/" + this.patientid_id);
    },
    handleRowClick(row, column, event) {
      this.selectedOldRecords = row;
      this.oldRecordsdialogVisible = true;
    },
    calculateBMI() {
      const { weight, height } = this.form;
      if (weight && height) {
        const heightInMeters = height / 100;
        this.form.bmi = (weight / heightInMeters ** 2).toFixed(2);
      } else {
        this.form.bmi = null; // Reset BMI if inputs are not valid
      }
    },
    checkExtn(a) {
      return a.split(".");
    },
    dateFormat(dt) {
      return moment(dt).format("MMMM D, YYYY");
    },
    viewFile(s, e) {
      this.isPdf = e == "pdf" ? true : false;
      this.viewFileModel = true;
      this.sourceFile = s;
    },
    importMedicine() {
      this.$confirm("Are you sure you want to import last prescriptions?", "Warning", {
        confirmButtonText: "OK",
        cancelButtonText: "Cancel",
        type: "warning",
      })
        .then(() => {
          Patients.ImportMedicine(this.form_att.patientid, this.$route.params.id)
            .then((response) => {
              this.getmeds();
            })
            .catch((err) => {
              console.error("Error adding suggestions:", err);
            });
        })
        .catch(() => {
          this.$message({
            type: "info",
            message: "Delete canceled",
          });
        });
    },
    rotate() {
      this.rotation = this.rotation + 90;
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
  max-width: 100%;
  /* Ensures container's width adjusts responsively */
}

.el-form-item {
  margin-right: 20px;
  /* Adjust margin as needed */
}

.responsive-textarea .el-input__inner {
  width: 100%;
  /* Ensures textarea takes full width of form item */
}

.compact-table .el-table__cell {
  padding: 5px 10px;
  /* Adjust padding for compactness */
  font-size: 12px;
  /* Adjust font size for compactness */
}

.el-dialog__body {
  line-height: 29px;
}

.ql-container {
  min-height: 200px;
}
</style>
<style scoped>
.iframe-wrapper {
  text-align: center;
  padding: 10px;
}

.iframe-transform-container {
  display: inline-block;
  overflow: hidden;
}

.iframe-full {
  width: 800px;
  height: 600px;
  border: none;
}

.controls {
  margin-top: 10px;
}

button {
  margin: 0 5px;
}

.demo-image__error .image-slot {
  font-size: 30px;
}

.demo-image__error .image-slot .el-icon {
  font-size: 30px;
}

.demo-image__error .el-image {
  width: 100%;
  height: 200px;
}

/* Physical Examination Template Styles */
.pe-template-section {
  margin-bottom: 20px;
}

.template-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 15px;
}

.template-btn {
  position: relative;
  margin-right: 8px;
  margin-bottom: 8px;
  font-size: 12px;
  padding: 8px 15px;
  min-width: 120px;
}

.delete-template-btn {
  position: absolute;
  top: -5px;
  right: -5px;
  width: 18px;
  height: 18px;
  font-size: 10px;
  padding: 0;
}

.pe-template-section .el-button[type="success"] {
  margin-left: 10px;
}

/* Template button hover effects */
.template-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  transition: all 0.2s ease;
}

/* Custom template button styling */
.template-btn[type="success"] {
  border-color: #67c23a;
  color: #67c23a;
}

.template-btn[type="success"]:hover {
  background-color: #67c23a;
  color: white;
}
</style>
<style>
.input-with-label label {
  margin-left: 8px;
  /* space between input and label */
}

.el-textarea__inner {
  resize: none !important;
}

/* Mobile Tab Navigation Styles */
.mobile-tab-navigation {
  margin-bottom: 20px;
}

.mobile-content {
  margin-top: 20px;
}

.mobile-section h3 {
  color: #409EFF;
  margin-bottom: 15px;
  padding-bottom: 10px;
  border-bottom: 2px solid #409EFF;
}

.mobile-section .el-form-item {
  margin-bottom: 15px;
}

.mobile-section .el-input,
.mobile-section .el-textarea {
  width: 100%;
}

/* Modern Profile Card Styles */
.modern-profile-card {
  border-radius: 12px;
  border: none;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
}

.modern-profile-card:hover {
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
  transform: translateY(-2px);
}

.profile-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0;
  border-bottom: 2px solid #f0f2f5;
  margin-bottom: 20px;
}

.profile-title {
  display: flex;
  align-items: center;
  font-size: 18px;
  font-weight: 600;
  color: #2c3e50;
}

.profile-title i {
  margin-right: 8px;
  color: #409EFF;
  font-size: 20px;
}

.profile-date {
  display: flex;
  align-items: center;
  color: #909399;
  font-size: 14px;
}

.profile-date i {
  margin-right: 5px;
}

.profile-content {
  display: flex;
  gap: 30px;
  align-items: flex-start;
}

.profile-photo-section {
  flex-shrink: 0;
}

.profile-photo-container {
  position: relative;
  width: 120px;
  height: 120px;
  border-radius: 50%;
  overflow: hidden;
  border: 4px solid #f0f2f5;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.profile-photo {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.profile-status {
  position: absolute;
  bottom: -5px;
  right: -5px;
}

.profile-details {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 25px;
}

.profile-section {
  background: #fafbfc;
  padding: 20px;
  border-radius: 8px;
  border-left: 4px solid #409EFF;
}

.section-title {
  margin: 0 0 15px 0;
  font-size: 16px;
  font-weight: 600;
  color: #2c3e50;
  display: flex;
  align-items: center;
}

.section-title::before {
  content: '';
  width: 4px;
  height: 16px;
  background: #409EFF;
  margin-right: 8px;
  border-radius: 2px;
}

.profile-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 15px;
}

.profile-item {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.profile-label {
  display: flex;
  align-items: center;
  font-size: 12px;
  font-weight: 600;
  color: #909399;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.profile-label i {
  margin-right: 6px;
  font-size: 14px;
}

.profile-value {
  font-size: 14px;
  font-weight: 500;
  color: #2c3e50;
  word-break: break-word;
}

.text-muted {
  color: #c0c4cc;
  font-style: italic;
}

/* Modern Tab Styles */
.modern-tabs {
  margin-top: 20px;
}

.modern-tabs .el-tabs__header {
  margin-bottom: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 8px;
  padding: 5px;
}

.modern-tabs .el-tabs__nav-wrap {
  background: transparent;
}

.modern-tabs .el-tabs__item {
  color: rgba(255, 255, 255, 0.8);
  border: none;
  background: transparent;
  border-radius: 6px;
  margin: 0 2px;
  transition: all 0.3s ease;
  font-weight: 500;
}

.modern-tabs .el-tabs__item:hover {
  color: white;
  background: rgba(255, 255, 255, 0.1);
}

.modern-tabs .el-tabs__item.is-active {
  color: white;
  background: rgba(255, 255, 255, 0.2);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.tab-label {
  display: flex;
  align-items: center;
  gap: 6px;
}

.tab-label i {
  font-size: 16px;
}

/* Modern Card Styles */
.modern-card {
  border-radius: 12px;
  border: none;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
}

.modern-card:hover {
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
}

.card-header {
  display: flex;
  align-items: center;
  font-size: 16px;
  font-weight: 600;
  color: #2c3e50;
  padding: 0;
  border-bottom: 2px solid #f0f2f5;
  margin-bottom: 20px;
}

.card-header i {
  margin-right: 8px;
  color: #409EFF;
  font-size: 18px;
}

/* Vitals Container Styles */
.vitals-container {
  padding: 20px 0;
}

.vitals-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 30px;
  margin-bottom: 30px;
}

.vital-group {
  background: #fafbfc;
  padding: 20px;
  border-radius: 8px;
  border-left: 4px solid #67c23a;
}

.group-title {
  margin: 0 0 15px 0;
  font-size: 14px;
  font-weight: 600;
  color: #2c3e50;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.vital-inputs {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.vital-item {
  margin-bottom: 0;
}

.vital-input {
  width: 100%;
}

.vitals-actions {
  display: flex;
  justify-content: center;
  padding-top: 20px;
  border-top: 1px solid #f0f2f5;
}

.update-btn {
  padding: 12px 30px;
  font-size: 16px;
  font-weight: 600;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(64, 158, 255, 0.3);
  transition: all 0.3s ease;
}

.update-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(64, 158, 255, 0.4);
}

/* Action Toolbar Styles */
.action-toolbar {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
  border-radius: 12px;
  margin-bottom: 20px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.action-buttons {
  display: flex;
  gap: 15px;
  align-items: center;
}

.action-btn {
  border-radius: 8px;
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  transition: all 0.3s ease;
  border: none;
}

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.action-menu {
  border-radius: 8px;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
  border: none;
  padding: 8px 0;
}

.action-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  font-weight: 500;
  transition: all 0.2s ease;
}

.action-item:hover {
  background: #f5f7fa;
  color: #409EFF;
}

.action-item.success:hover {
  background: #f0f9ff;
  color: #67c23a;
}

.action-item.danger:hover {
  background: #fef0f0;
  color: #f56c6c;
}

.action-item i {
  font-size: 16px;
  width: 20px;
  text-align: center;
}

/* Enhanced Form Styling */
.app-container {
  background: #f8f9fa;
  min-height: 100vh;
  padding: 20px;
}

.loading-container {
  border-radius: 12px;
}

/* Card Enhancements */
.el-card {
  border-radius: 12px;
  border: none;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
}

.el-card:hover {
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
}

/* Form Input Enhancements */
.el-input__inner {
  border-radius: 8px;
  border: 2px solid #e4e7ed;
  transition: all 0.3s ease;
}

.el-input__inner:focus {
  border-color: #409EFF;
  box-shadow: 0 0 0 2px rgba(64, 158, 255, 0.1);
}

.el-textarea__inner {
  border-radius: 8px;
  border: 2px solid #e4e7ed;
  transition: all 0.3s ease;
}

.el-textarea__inner:focus {
  border-color: #409EFF;
  box-shadow: 0 0 0 2px rgba(64, 158, 255, 0.1);
}

/* Button Enhancements */
.el-button {
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.el-button--primary {
  background: linear-gradient(135deg, #409EFF 0%, #67c23a 100%);
  border: none;
  box-shadow: 0 2px 8px rgba(64, 158, 255, 0.3);
}

.el-button--primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(64, 158, 255, 0.4);
}

.el-button--success {
  background: linear-gradient(135deg, #67c23a 0%, #85ce61 100%);
  border: none;
  box-shadow: 0 2px 8px rgba(103, 194, 58, 0.3);
}

.el-button--success:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(103, 194, 58, 0.4);
}

/* Table Enhancements */
.el-table {
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.el-table th {
  background: #f8f9fa;
  color: #2c3e50;
  font-weight: 600;
  border-bottom: 2px solid #e4e7ed;
}

.el-table td {
  border-bottom: 1px solid #f0f2f5;
}

/* Mobile responsive improvements */
@media (max-width: 768px) {
  .app-container {
    padding: 10px;
  }
  
  .action-toolbar {
    padding: 15px;
  }
  
  .action-buttons {
    flex-direction: column;
    align-items: stretch;
  }
  
  .action-btn {
    width: 100%;
    margin-bottom: 10px;
  }
  
  .profile-content {
    flex-direction: column;
    gap: 20px;
  }
  
  .profile-photo-container {
    width: 100px;
    height: 100px;
    margin: 0 auto;
  }
  
  .profile-grid {
    grid-template-columns: 1fr;
  }
  
  .vitals-grid {
    grid-template-columns: 1fr;
    gap: 20px;
  }
  
  .mobile-section .el-col {
    margin-bottom: 10px;
  }
  
  .mobile-section .el-form-item__label {
    font-weight: 600;
    margin-bottom: 5px;
  }
  
  .template-buttons {
    flex-direction: column;
    align-items: stretch;
  }
  
  .template-btn {
    width: 100%;
    margin-bottom: 8px;
    margin-right: 0;
  }
  
  .modern-tabs .el-tabs__header {
    background: linear-gradient(135deg, #409EFF 0%, #67c23a 100%);
  }
}
</style>