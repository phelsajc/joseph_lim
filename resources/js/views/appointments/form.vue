<template>
  <div class="app-container loading-container" v-loading="pageloading" element-loading-text="Loading...">
    <div class="mb-4">
      <el-dropdown @command="handleCommand">
        <el-button type="primary">
          Actions<i class="el-icon-arrow-down el-icon--right" />
        </el-button>
        <el-dropdown-menu slot="dropdown">
          <el-dropdown-item command="update_diagnosis">Update Diagnosis</el-dropdown-item>
          <el-dropdown-item command="print_rx">Print Rx</el-dropdown-item>
          <!-- <el-dropdown-item command="email_rx">Email Rx</el-dropdown-item> -->
          <el-dropdown-item command="print_labs">Print Diagnostics</el-dropdown-item>
          <el-dropdown-item command="print_referral">Print Referral</el-dropdown-item>
          <el-dropdown-item command="print_medcert">Print Med Cert</el-dropdown-item>
          <el-dropdown-item v-role="['secretary', 'admin', 'doctor']" command="done_consult">Done Consultation</el-dropdown-item>
          <el-dropdown-item command="cancel_apt">Cancel Appointment</el-dropdown-item>
          <el-dropdown-item v-role="['doctor', 'admin']" command="view_chart">View Chart</el-dropdown-item>
        </el-dropdown-menu>
      </el-dropdown>
      <el-popconfirm v-model="popconfirmUpddateDiagnosis" title="Are you done with this appointment?"
        @confirm="onSubmit">
        <template #reference>
          <el-button v-if="!isMobile" ref="updateDiagnosisBtn" style="display: none" type="primary">
            Update Diagnosis
          </el-button>
        </template>
      </el-popconfirm>
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
                  <el-input v-if="diagnosticsRenderedModel.includes(item.lab_test) && (item.lab_test_id == 560 || item.lab_test_id == 561)"
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

    <el-card class="profile-card">
      <div slot="header" class="clearfix">
        <span>Profile Information</span>
      </div>
      <el-row class="profile-content">
        <el-col :span="6" class="profile-photo">
          <el-image style="width: 50%; height: auto" :src="profile.photo" alt="Profile Photo" fit="cover" />
        </el-col>
        <el-col :span="6">
          <div class="profile-item">
            <span class="profile-label">Name:</span>
            <span class="profile-value">{{ profile.patientname }}</span>
          </div>
          <div class="profile-item">
            <span class="profile-label">Age:</span>
            <span class="profile-value">{{ profile.age }}</span>
          </div>
          <div class="profile-item">
            <span class="profile-label">Civil Status:</span>
            <span class="profile-value">{{ profile.civil_status }}</span>
          </div>
          <div class="profile-item">
            <span class="profile-label">Address:</span>
            <span class="profile-value">{{ profile.address }}</span>
          </div>
          <div class="profile-item">
            <span class="profile-label">Date:</span>
            <span class="profile-value">{{ currentDt() }}</span>
          </div>
        </el-col>
        <el-col :span="6">
          <div class="profile-item">
            <span class="profile-label">Birth Date:</span>
            <span class="profile-value">{{ dateFormat(profile.birthdate) }}</span>
          </div>
          <div class="profile-item">
            <span class="profile-label">Contact No:</span>
            <span class="profile-value">{{ profile.contactno }}</span>
          </div>
          <div class="profile-item">
            <span class="profile-label">Gender:</span>
            <span class="profile-value">{{
              profile.sex == "2" ? "Female" : "Male"
            }}</span>
          </div>
          <div class="profile-item">
            <span class="profile-label">Blood Type:</span>
            <span class="profile-value">{{ profile.blood_type }}</span>
          </div>
        </el-col>
      </el-row>
    </el-card>
    <br />

    <el-tabs v-model="tab" type="card" class="demo-tabs">
      <el-tab-pane label="Histories" name="history" v-if="checkRole(['admin', 'doctor'])">
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
            <el-input v-model="form.pe" type="textarea" ref="peInput" rows="10"  @input="autoResize"/>
          </el-form-item>
          <el-form-item label="Diagnosis">
            <el-input v-model="form.diagnosis" type="textarea" />
          </el-form-item>
          <el-form-item label="Plans">
            <el-input v-model="form.remarks" type="textarea" ref="plansInput" rows="10"  @input="autoResize"/>
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
      <el-tab-pane label="Vitals" name="second">
        <el-card style="max-width: 100%">
          <el-form :inline="true" label-position="top" class="demo-form-inline">
            <el-form-item label="Systolic">
              <el-input v-model="form.vit_sys" autosize clearable />
            </el-form-item>
            <el-form-item label="Diastolic">
              <el-input v-model="form.vit_dia" clearable />
            </el-form-item>
            <el-form-item label="Weight">
              <el-input v-model="form.weight" clearable placeholder="kilograms" />
            </el-form-item>
            <el-form-item label="Height">
              <el-input v-model="form.height" clearable placeholder="centimeters" />
            </el-form-item>
            <el-form-item label="BMI">
              <el-input v-model="form.bmi" clearable />
            </el-form-item>
            <el-form-item label="Temperature">
              <el-input v-model="form.vit_temp" clearable />
            </el-form-item>
            <el-form-item label="Cardiac Rate">
              <el-input v-model="form.vit_cr" clearable />
            </el-form-item>
            <el-form-item label="Respiratory Rate">
              <el-input v-model="form.vit_rr" clearable />
            </el-form-item>
            <el-form-item v-role="['secretary', 'admin']" label="">
              <br />
              <el-button type="primary" @click="upDateBP()">Update</el-button>
            </el-form-item>
          </el-form>
        </el-card>
      </el-tab-pane>
      <el-tab-pane label="Medicines" name="fourth" v-if="checkRole(['admin', 'doctor'])">
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
            <el-table :data="rx_list" style="width: 100%" class="compact-table">
              <el-table-column prop="medicine" label="Medicine" width="310" />
              <el-table-column prop="qty" label="Qty" width="70" />
              <el-table-column label="Breakfast">
                <el-table-column prop="bb" label="Before" width="80" />
                <el-table-column prop="ab" label="After" width="80" />
              </el-table-column>
              <el-table-column label="Lunch">
                <el-table-column prop="bl" label="Before" width="80" />
                <el-table-column prop="al" label="After" width="80" />
              </el-table-column>
              <el-table-column label="Dinner">
                <el-table-column prop="bs" label="Before" width="80" />
                <el-table-column prop="as" label="After" width="80" />
              </el-table-column>
              <el-table-column prop="bt" label="Bedtime" width="80" />
              <el-table-column prop="remarks" label="Remarks" width="300" />
              <el-table-column align="center" label="Actions" width="300">
                <template slot-scope="scope">
                  <el-button v-role="['doctor', 'admin']" type="primary" size="mini"
                    @click="editMed(scope.row)">Edit</el-button>
                  <el-button v-if="isEditMode && editingMedId === scope.row.id" v-role="['doctor', 'admin']" type="warning" size="mini"
                    @click="cancelEdit()">Cancel</el-button>
                  <el-button v-role="['doctor', 'admin']" type="danger" size="mini"
                    @click="deleteMed(scope.row.id)">Delete</el-button>
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
      <el-tab-pane label="Diagnostics" name="fifth" v-if="checkRole(['admin', 'doctor'])">
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
      <el-tab-pane label="Medical Certificate" name="medcert" v-if="checkRole(['admin', 'doctor'])">
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
      <el-tab-pane label="Referral" name="referral" v-if="checkRole(['admin', 'doctor'])">
        <el-card style="max-width: 100%">
          <el-form label-position="top">
            <el-row :gutter="20">
              <el-col :span="4">
                <el-form-item label="Doctor"
                  ><el-input v-model="form.referral_doctor" />
                </el-form-item>
              </el-col>
              <el-col :span="4">
                <el-form-item label="Address 1"
                  ><el-input v-model="form.referral_addr1" />
                </el-form-item>
              </el-col>
              <el-col :span="4">
                <el-form-item label="Address 2"
                  ><el-input v-model="form.referral_addr2" />
                </el-form-item>
              </el-col>
              <el-col :span="4">
                <el-form-item label="Undersigned Date">
                  <date-picker
                    v-model="form.referral_undersigned"
                    valueType="format"
                  ></date-picker>
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
      <el-tab-pane label="Attachments" name="attachments">
        <div class="mb-4">
          <el-upload ref="uploadRef" action="#" :auto-upload="false" multiple :on-change="handleChange">
            <template #trigger>
              <el-button ref="uploadRef" size="small" type="info" action="#" :auto-upload="false" multiple
                :on-change="handleChange">Select attachments</el-button>
            </template>
            <el-button size="small" type="primary" @click="submitUpload">Submit</el-button>
          </el-upload>
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
                <!-- <el-image :src="imgSrc(scope.row.newfile, scope.row.oldfile, scope.row.type)" fit="cover" class="image"
                  :preview-src-list="[
                    imgSrc(scope.row.newfile, scope.row.oldfile, scope.row.type),
                  ]" /> -->
                <!-- <el-image v-if="checkExtn(scope.row.fname)!='pdf'" :src="imgSrc(scope.row.newfile, scope.row.oldfile, scope.row.type)" fit="cover" class="image"
                  :preview-src-list="[
                    imgSrc(scope.row.newfile, scope.row.oldfile, scope.row.type),
                  ]" />
                <iframe v-else :src="scope.row.newfile" width="100%" height="100%" frameborder="0"
                  allowfullscreen></iframe> -->
                <el-button type="primary" icon="el-icon-files"
                  @click="viewFile(scope.row.newfile, scope.row.extension)" />
                {{ scope.row.fname }}
              </template>
            </el-table-column>
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
          <!-- <el-dialog
            :visible.sync="viewFileModel"
            width="80%"
            :fullscreen="false"
            :close-on-click-modal="false"
          >
            <div class="iframe-wrapper">
              <div class="iframe-transform-container" :style="iframeStyle">
                <iframe
                  :src="sourceFile"
                  frameborder="0"
                  class="iframe-full"
                ></iframe>
              </div>

              <div class="controls">
                <button @click="zoomIn">Zoom In</button>
                <button @click="zoomOut">Zoom Out</button>
                <button @click="rotate">Rotate</button>
              </div>
            </div>
          </el-dialog> -->
        </el-card>
      </el-tab-pane>
    </el-tabs>
  </div>
</template>
<script>
import role from "@/directive/role/index.js";
import Patients from "@/api/patients";
import Medicine from "@/api/medicine";
import Procedure from "@/api/procedure";
import Services from "@/api/services";
import Diagnostics from "@/api/diagnostics";
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
      fam: [],
      soc: [],
      loading: true,
      tab: "first",
      rx_list: [],
      diagnostic_list: [],
      services_list: [],
      form: {
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
  },
  computed: {
    transformStyle() {
      return {
        transform: `rotate(${this.rotation}deg)`,
        transformOrigin: "center center",
        display: "inline-block",
      };
    },
  },
  methods: {
    checkRole,
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
      
      // Handle medicine selection
      if (row.medicineId!=0) {
        this.medsArr.meds = row.medicine;
        this.medsArr.med_id = row.med_id || row.id;
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
        (this.medsArr.custom_generic !== "" && this.medsArr.custom_dosage !== "")
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
    async submitUpload() {
      const formData = new FormData();
      formData.append("patientid", this.form_att.patientid);

      for (let i = 0; i < this.form_att.files.length; i++) {
        const file = this.form_att.files[i];
        const extension = file.name.split(".").pop().toLowerCase();

        if (extension === "heic" || extension === "heif") {
          try {
            const bmpBlob = await heic2any({
              blob: file,
              toType: "image/bmp",
              quality: 0.9,
            });

            const convertedFile = new File(
              [bmpBlob],
              file.name.replace(/\.(heic|heif)$/i, ".jpg"),
              { type: "image/bmp" }
            );

            formData.append(`files[${i}]`, convertedFile);
          } catch (error) {
            console.error("HEIC conversion failed:", error);
            this.$message.error("HEIC conversion failed.");
            return;
          }
        } else {
          formData.append(`files[${i}]`, file);
        }
      }

      try {
        const response = await Patients.addAttachments(formData);
        this.form_att.file = "";
        this.get_attachments(this.form_att.patientid);
        this.$message.success("File uploaded successfully!");
        this.$refs.uploadRef.clearFiles();
      } catch (err) {
        console.error("Error uploading files:", err);
        this.$message.error("Upload failed. "+err);
      }
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
</style>
<style>
.input-with-label label {
  margin-left: 8px;
  /* space between input and label */
}
.el-textarea__inner {
  resize: none !important;
}

</style>