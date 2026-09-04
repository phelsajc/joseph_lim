<template>
  <div v-loading="pageloading" class="app-container loading-container" element-loading-text="Loading...">
    <div ref="toolbarWrap" class="action-toolbar-wrap">
      <div ref="toolbarSentinel" class="action-toolbar-sentinel" aria-hidden="true" />
      <div
        ref="actionToolbar"
        class="action-toolbar"
        :class="{ 'is-pinned': toolbarPinned }"
        :style="pinnedToolbarStyle"
      >
        <div class="action-buttons">
          <el-dropdown trigger="click" @command="handleCommand">
            <el-button type="primary" size="large" class="action-btn">
              <i class="el-icon-menu" />
              Actions
              <i class="el-icon-arrow-down el-icon--right" />
            </el-button>
            <el-dropdown-menu slot="dropdown" class="action-menu">
              <el-dropdown-item command="update_diagnosis" class="action-item">
                <i class="el-icon-edit" />
                Update Diagnosis
              </el-dropdown-item>
              <el-dropdown-item command="share_pdf" class="action-item">
                <i class="el-icon-share" />
                Share PDF
              </el-dropdown-item>
              <el-dropdown-item command="print_rx_current" class="action-item">
                <i class="el-icon-printer" />
                Print Rx (current group)
              </el-dropdown-item>
              <el-dropdown-item command="print_rx_all" class="action-item">
                <i class="el-icon-printer" />
                Print Rx (all groups)
              </el-dropdown-item>
              <el-dropdown-item command="print_dx_current" class="action-item">
                <i class="el-icon-document" />
                Print Diagnostics (current group)
              </el-dropdown-item>
              <el-dropdown-item command="print_dx_all" class="action-item">
                <i class="el-icon-document" />
                Print Diagnostics (all groups)
              </el-dropdown-item>
              <el-dropdown-item command="print_referral" class="action-item">
                <i class="el-icon-s-promotion" />
                Print Referral
              </el-dropdown-item>
              <el-dropdown-item command="print_form" class="action-item">
                <i class="el-icon-s-promotion" />
                Print Form
              </el-dropdown-item>
              <el-dropdown-item command="load_form_template" class="action-item">
                <i class="el-icon-document" />
                Load form template
              </el-dropdown-item>
              <el-dropdown-item command="print_medcert" class="action-item">
                <i class="el-icon-document-copy" />
                Print Med Cert
              </el-dropdown-item>
              <el-dropdown-item command="print_fees" class="action-item">
                <i class="el-icon-money" />
                Print Fees
              </el-dropdown-item>
              <el-dropdown-item v-role="['secretary', 'admin', 'doctor']" command="done_consult" class="action-item success">
                <i class="el-icon-check" />
                Done Consultation
              </el-dropdown-item>
              <el-dropdown-item command="cancel_apt" class="action-item danger">
                <i class="el-icon-close" />
                Cancel Appointment
              </el-dropdown-item>
              <el-dropdown-item v-role="['doctor', 'admin']" command="view_chart" class="action-item">
                <i class="el-icon-view" />
                View Chart
              </el-dropdown-item>
            </el-dropdown-menu>
          </el-dropdown>

          <el-button
            type="primary"
            plain
            size="large"
            class="action-btn"
            :disabled="!hasPatientProfileLink"
            @click="goToPatientProfile"
          >
            <i class="el-icon-user" />
            Patient profile
          </el-button>

          <el-button
            type="info"
            size="large"
            class="action-btn"
            :disabled="!patientid_id"
            @click="openCompareDrawer"
          >
            <i class="el-icon-copy-document" />
            Compare previous
          </el-button>

          <el-popconfirm
            v-model="popconfirmUpddateDiagnosis" title="Are you done with this appointment?"
            @confirm="onSubmit"
          >
            <template #reference>
              <el-button ref="updateDiagnosisBtn" type="success" size="large" class="action-btn">
                <i class="el-icon-check" />
                Update Diagnosis
              </el-button>
            </template>
          </el-popconfirm>

          <el-button
            v-role="['secretary', 'admin', 'doctor']"
            type="success"
            plain
            size="large"
            class="action-btn"
            @click="doneConsult"
          >
            <i class="el-icon-check" />
            Done Consultation
          </el-button>

          <el-button
            v-role="['doctor', 'admin']"
            type="primary"
            plain
            size="large"
            class="action-btn"
            @click="printChart"
          >
            <i class="el-icon-view" />
            View Chart
          </el-button>

          <el-button
            type="danger"
            plain
            size="large"
            class="action-btn"
            @click="cancelAppointment"
          >
            <i class="el-icon-close" />
            Cancel Appointment
          </el-button>

          <el-button
            type="primary"
            size="large"
            class="action-btn"
            @click="openSharePdfDialog"
          >
            <i class="el-icon-share" />
            Share PDF
          </el-button>
        </div>
      </div>
    </div>

    <el-drawer
      title="Compare with a previous visit"
      :visible.sync="compareDrawerVisible"
      direction="rtl"
      size="92%"
      :destroy-on-close="true"
      @close="closeCompareDrawer"
    >
      <div class="compare-drawer">
        <div class="compare-drawer__controls">
          <span class="compare-drawer__label">Previous visit:</span>
          <el-select
            v-model="compareSelectedId"
            v-loading="compareListLoading"
            placeholder="Select a previous completed visit"
            filterable
            clearable
            style="min-width: 360px"
            @change="loadCompareVisit"
          >
            <el-option
              v-for="visit in comparePastVisits"
              :key="visit.id"
              :value="visit.id"
              :label="visit.cf ? (visit.date + ' - ' + visit.cf) : visit.date"
            />
          </el-select>
          <span v-if="!comparePastVisits.length && !compareListLoading" class="compare-drawer__empty">
            No previous completed visits found for this patient.
          </span>
        </div>

        <el-row :gutter="16" class="compare-columns">
          <el-col
            v-for="col in compareColumns"
            :key="col.key"
            :xs="24"
            :sm="12"
          >
            <el-card
              v-loading="col.loading"
              shadow="never"
              class="compare-column"
            >
              <div slot="header" class="compare-column__header">
                <strong>{{ col.title }}</strong>
                <span v-if="col.subtitle" class="compare-column__subtitle">{{ col.subtitle }}</span>
              </div>

              <template v-if="col.key === 'previous' && !compareSelectedId">
                <div class="compare-empty">Select a previous visit to compare.</div>
              </template>
              <template v-else>
                <div class="compare-section">
                  <h4>Clinical Notes</h4>
                  <div class="compare-field">
                    <label>Chief Complaint</label>
                    <p>{{ col.snapshot.form.chiefcomplaints || col.snapshot.form.cc || "-" }}</p>
                  </div>
                  <div class="compare-field">
                    <label>History / HPI</label>
                    <p>{{ col.snapshot.form.history || "-" }}</p>
                  </div>
                  <div class="compare-field">
                    <label>Physical Exam</label>
                    <p>{{ col.snapshot.form.pe || "-" }}</p>
                  </div>
                  <div class="compare-field">
                    <label>Diagnosis</label>
                    <p>{{ col.snapshot.form.diagnosis || "-" }}</p>
                  </div>
                  <div class="compare-field">
                    <label>Plan</label>
                    <p>{{ col.snapshot.form.plan || "-" }}</p>
                  </div>
                  <div class="compare-field">
                    <label>Remarks</label>
                    <p>{{ col.snapshot.form.remarks || "-" }}</p>
                  </div>
                </div>

                <div class="compare-section">
                  <h4>Vitals</h4>
                  <div class="compare-vitals">
                    <span>BP: {{ col.snapshot.form.vit_sys || "-" }}/{{ col.snapshot.form.vit_dia || "-" }}</span>
                    <span>Weight: {{ col.snapshot.form.weight || "-" }}</span>
                    <span>Height: {{ col.snapshot.form.height || "-" }}</span>
                    <span>BMI: {{ col.snapshot.form.bmi || "-" }}</span>
                    <span>Temp: {{ col.snapshot.form.vit_temp || "-" }}</span>
                    <span>CR: {{ col.snapshot.form.vit_cr || "-" }}</span>
                    <span>RR: {{ col.snapshot.form.vit_rr || "-" }}</span>
                    <span>O2: {{ col.snapshot.form.o2_stat || "-" }}</span>
                  </div>
                </div>

                <div class="compare-section">
                  <h4>Prescriptions</h4>
                  <el-table
                    v-if="col.snapshot.rx_list && col.snapshot.rx_list.length"
                    :data="col.snapshot.rx_list"
                    size="mini"
                    class="compact-table"
                  >
                    <el-table-column prop="medicine" label="Medicine" min-width="180" />
                    <el-table-column prop="qty" label="Qty" width="60" align="center" />
                    <el-table-column prop="remarks" label="Remarks" min-width="140" show-overflow-tooltip />
                  </el-table>
                  <div v-else class="compare-empty">No prescriptions.</div>
                </div>

                <div class="compare-section">
                  <h4>Diagnostics</h4>
                  <el-table
                    v-if="col.snapshot.diagnostic_list && col.snapshot.diagnostic_list.length"
                    :data="col.snapshot.diagnostic_list"
                    size="mini"
                    class="compact-table"
                  >
                    <el-table-column prop="diagnostic" label="Procedure" min-width="180" />
                    <el-table-column prop="remarks" label="Remarks" min-width="140" show-overflow-tooltip />
                  </el-table>
                  <div v-else class="compare-empty">No diagnostics.</div>
                </div>

                <div class="compare-section">
                  <h4>Services</h4>
                  <el-table
                    v-if="col.snapshot.services_list && col.snapshot.services_list.length"
                    :data="col.snapshot.services_list"
                    size="mini"
                    class="compact-table"
                  >
                    <el-table-column prop="service" label="Service" min-width="180" />
                    <el-table-column prop="fee" label="Fee" width="100" align="right" />
                  </el-table>
                  <div v-else class="compare-empty">No services.</div>
                </div>
              </template>
            </el-card>
          </el-col>
        </el-row>
      </div>
    </el-drawer>

    <el-dialog
      :title="'Historical Records'" class="compact-table" width="100%" :visible.sync="historyDiaglog"
      :close-on-click-modal="false" :close-on-press-escape="false"
    >
      <el-table
        :data="old_records" border :default-sort="{ prop: 'date', order: 'descending' }"
        @row-click="handleRowClick"
      >
        <el-table-column prop="desc" label="Description" />
        <el-table-column prop="date" sortable label="Date" />
      </el-table>
    </el-dialog>

    <el-dialog
      :title="'HistorVitalsical Records'" class="compact-table" width="100%" :visible.sync="vitalsDiaglog"
      :close-on-click-modal="false" :close-on-press-escape="false"
    >
      <el-table :data="vitals_records" border :default-sort="{ prop: 'date', order: 'descending' }">
        <el-table-column prop="date" sortable label="Date" />
        <el-table-column prop="bp" label="BP" />
        <el-table-column prop="weight" label="Weight" />
      </el-table>
    </el-dialog>

    <el-dialog
      title="Share PDF link"
      :visible.sync="sharePdfDialogVisible"
      width="560px"
      top="10vh"
      :close-on-click-modal="false"
      append-to-body
    >
      <p class="rx-template-dialog__hint">
        Share via WhatsApp/Viber by sending a public link. For Messenger, the popup requires a Facebook App ID; otherwise use “Copy link” then paste in chat.
      </p>
      <el-form label-position="top">
        <el-form-item label="Document">
          <el-select v-model="sharePdfDoc" placeholder="Select document" style="width: 100%" @change="updateSharePdfLink">
            <el-option label="Prescription (Rx)" value="rx" />
            <el-option label="Diagnostics (Lab request)" value="diagnostics" />
            <el-option label="Referral" value="referral" />
            <el-option label="Form" value="form" />
            <el-option label="Medical Certificate" value="medcert" />
          </el-select>
        </el-form-item>
        <el-form-item label="Public link">
          <el-input v-model="sharePdfLink" readonly>
            <el-button slot="append" icon="el-icon-document-copy" @click="copySharePdfLink">
              Copy
            </el-button>
          </el-input>
        </el-form-item>
      </el-form>
      <div class="share-actions">
        <el-button type="info" plain icon="el-icon-share" @click="sharePdfToMessenger">
          Messenger
        </el-button>
        <el-button type="success" plain icon="el-icon-chat-dot-round" @click="sharePdfToWhatsApp">
          WhatsApp
        </el-button>
        <el-button type="primary" plain icon="el-icon-message" @click="sharePdfToViber">
          Viber
        </el-button>
      </div>
      <span slot="footer" class="dialog-footer">
        <el-button @click="sharePdfDialogVisible = false">Close</el-button>
      </span>
    </el-dialog>

    <el-dialog
      title="Load prescription template"
      :visible.sync="rxTemplateDialogVisible"
      width="520px"
      :close-on-click-modal="false"
    >
      <p class="rx-template-dialog__hint">
        Adds each medication using the same prescription flow as manual entry (custom / not carried), with dosing details in remarks.
      </p>
      <el-select
        v-model="rxTemplateSelectId"
        filterable
        placeholder="Select template by diagnosis"
        style="width: 100%"
        :loading="rxTemplateLoading"
      >
        <el-option
          v-for="t in rxTemplateList"
          :key="t.id"
          :label="`${t.diagnosis_name} (${t.items_count} meds)`"
          :value="t.id"
        />
      </el-select>
      <span slot="footer" class="dialog-footer">
        <el-button @click="rxTemplateDialogVisible = false">Cancel</el-button>
        <el-button
          type="primary"
          :disabled="!rxTemplateSelectId"
          :loading="rxTemplateApplyLoading"
          @click="applyRxTemplate"
        >
          Add to prescription
        </el-button>
      </span>
    </el-dialog>

    <el-dialog
      title="Load diagnostic template"
      :visible.sync="dxTemplateDialogVisible"
      width="560px"
      :close-on-click-modal="false"
    >
      <p class="rx-template-dialog__hint">
        Loads the template items into your current diagnostics selection. You can still add/remove items before saving.
      </p>
      <el-select
        v-model="dxTemplateSelectId"
        filterable
        remote
        :remote-method="fetchDxTemplatesForDialog"
        placeholder="Search diagnosis template"
        style="width: 100%"
        :loading="dxTemplateLoading"
        clearable
      >
        <el-option
          v-for="t in dxTemplateList"
          :key="t.id"
          :label="`${t.diagnosis_name} (${t.items_count} items)`"
          :value="t.id"
        />
      </el-select>
      <span slot="footer" class="dialog-footer">
        <el-button @click="dxTemplateDialogVisible = false">Cancel</el-button>
        <el-button
          type="primary"
          :disabled="!dxTemplateSelectId"
          :loading="dxTemplateApplyLoading"
          @click="applyDxTemplate"
        >
          Load into diagnostics
        </el-button>
      </span>
    </el-dialog>

    <el-dialog
      title="Load form template"
      :visible.sync="formTemplateDialogVisible"
      width="640px"
      top="8vh"
      :close-on-click-modal="false"
      custom-class="form-template-load-dialog"
    >
      <p v-pre class="rx-template-dialog__hint">
        Inserts rich HTML into the Form tab (formatting preserved). Placeholders like {{patient_name}}, {{diagnosis}}, {{gender}} fill from the chart. You can edit after loading.
      </p>
      <div v-if="formTemplateRecent.length" class="form-template-recent">
        <span class="form-template-recent__label">Recent:</span>
        <el-button
          v-for="r in formTemplateRecent"
          :key="'r-' + r.id"
          type="text"
          size="small"
          @click="quickSelectFormTemplate(r.id)"
        >
          {{ r.name }}
        </el-button>
      </div>
      <el-form label-position="top" size="small">
        <el-form-item label="Search">
          <el-input
            v-model="formTemplateSearchKeyword"
            clearable
            placeholder="Template name or category"
            prefix-icon="el-icon-search"
            @keyup.enter.native="fetchFormTemplatesForDialog"
            @clear="fetchFormTemplatesForDialog"
          />
        </el-form-item>
        <el-form-item label="Category">
          <el-select
            v-model="formTemplateFilterCategory"
            placeholder="All categories"
            clearable
            filterable
            style="width: 100%"
            @change="fetchFormTemplatesForDialog"
          >
            <el-option
              v-for="c in formTemplateCategoryOptions"
              :key="'cat-' + c"
              :label="c"
              :value="c"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="Template">
          <el-select
            v-model="formTemplateSelectId"
            filterable
            placeholder="Search or select a template"
            style="width: 100%"
            :loading="formTemplateLoading"
            @visible-change="onFormTemplateSelectOpen"
          >
            <el-option
              v-for="t in formTemplateList"
              :key="t.id"
              :label="formTemplateOptionLabel(t)"
              :value="t.id"
            />
          </el-select>
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="formTemplateDialogVisible = false">Cancel</el-button>
        <el-button
          type="primary"
          :disabled="!formTemplateSelectId"
          :loading="formTemplateApplyLoading"
          @click="applyFormTemplate"
        >
          Load into form
        </el-button>
      </span>
    </el-dialog>

    <el-dialog
      title="Load previous prescription"
      :visible.sync="rxPastDialogVisible"
      width="920px"
      top="5vh"
      custom-class="rx-past-dialog"
      :close-on-click-modal="false"
    >
      <div class="rx-past-dialog__toolbar">
        <el-input
          v-model="rxPastSearch"
          clearable
          placeholder="Search by date, diagnosis, or medication"
          prefix-icon="el-icon-search"
          class="rx-past-dialog__search"
        />
      </div>
      <div v-loading="rxPastLoading" class="rx-past-dialog__body-wrap">
        <el-table
          ref="rxPastTable"
          :data="rxPastFilteredRows"
          :row-key="rxPastRowKey"
          border
          size="small"
          max-height="420"
          class="rx-past-dialog__table"
          empty-text="No past prescriptions found"
        >
          <el-table-column type="expand">
            <template slot-scope="props">
              <el-table :data="props.row.medications" size="mini" border class="rx-past-dialog__inner-table">
                <el-table-column label="Medicine" min-width="220">
                  <template slot-scope="s">
                    {{ medicationLineLabel(s.row) }}
                  </template>
                </el-table-column>
                <el-table-column prop="qty" label="Qty" width="56" align="center" />
                <el-table-column label="Remarks" min-width="180" show-overflow-tooltip>
                  <template slot-scope="s">
                    {{ s.row.remarks || '—' }}
                  </template>
                </el-table-column>
              </el-table>
            </template>
          </el-table-column>
          <el-table-column label="Date" width="128">
            <template slot-scope="scope">
              {{ formatPastRxDate(scope.row.appointment_dt) }}
            </template>
          </el-table-column>
          <el-table-column label="Diagnosis" min-width="160" show-overflow-tooltip>
            <template slot-scope="scope">
              {{ scope.row.diagnosis || '—' }}
            </template>
          </el-table-column>
          <el-table-column label="Preview" min-width="220" show-overflow-tooltip>
            <template slot-scope="scope">
              {{ pastRxPreview(scope.row.medications) }}
            </template>
          </el-table-column>
          <el-table-column label="Action" width="200" align="center" fixed="right">
            <template slot-scope="scope">
              <el-button type="text" size="mini" @click="togglePastRxExpand(scope.row)">
                View details
              </el-button>
              <el-button
                type="primary"
                size="mini"
                :loading="rxPastUseLoading"
                @click="usePastPrescription(scope.row)"
              >
                Use
              </el-button>
            </template>
          </el-table-column>
        </el-table>
      </div>
      <span slot="footer" class="dialog-footer">
        <el-button @click="rxPastDialogVisible = false">Close</el-button>
      </span>
    </el-dialog>

    <el-dialog
      title="Favorite medicines"
      :visible.sync="rxFavoritesDialogVisible"
      width="720px"
      top="8vh"
      :close-on-click-modal="false"
    >
      <p class="rx-fav-dialog__hint">
        Select one or more medicines to add to this prescription. You can edit them in the table after adding.
      </p>
      <el-table
        ref="rxFavoritesTable"
        :data="rxFavoriteMedicines"
        max-height="420"
        border
        size="small"
        empty-text="No favorites yet. Use the star in medicine search to save favorites."
        @selection-change="handleRxFavoritesSelectionChange"
      >
        <el-table-column type="selection" width="48" align="center" />
        <el-table-column prop="drug_name" label="Medicine" min-width="220" show-overflow-tooltip />
        <el-table-column prop="default_qty" label="Qty" width="64" align="center" />
        <el-table-column label="Schedule / remarks" min-width="200" show-overflow-tooltip>
          <template slot-scope="scope">
            {{ rxFavoriteSchedulePreview(scope.row) }}
          </template>
        </el-table-column>
        <el-table-column label="Actions" width="110" align="center" fixed="right">
          <template slot-scope="scope">
            <el-button type="text" size="mini" @click="openRxFavoriteEdit(scope.row)">
              Edit
            </el-button>
            <el-button type="text" size="mini" style="color:#F56C6C" @click="removeRxFavorite(scope.row)">
              Remove
            </el-button>
          </template>
        </el-table-column>
      </el-table>
      <span slot="footer" class="dialog-footer">
        <el-button @click="rxFavoritesDialogVisible = false">Cancel</el-button>
        <el-button
          type="primary"
          :loading="rxFavoritesApplyLoading"
          :disabled="!rxFavoritesDialogSelection.length"
          @click="applyRxFavoritesSelection"
        >
          Add selected
        </el-button>
      </span>
    </el-dialog>

    <el-dialog
      title="Edit favorite medicine"
      :visible.sync="rxFavoriteEditDialogVisible"
      width="520px"
      :close-on-click-modal="false"
      append-to-body
    >
      <el-form label-position="top">
        <el-form-item label="Medicine">
          <el-input v-model="rxFavoriteEditForm.drug_name" disabled />
        </el-form-item>
        <el-form-item label="Default Qty">
          <el-input v-model="rxFavoriteEditForm.default_qty" placeholder="e.g. 10" />
        </el-form-item>
        <el-form-item label="Default remarks">
          <el-input v-model="rxFavoriteEditForm.default_remarks" type="textarea" :rows="3" placeholder="Optional notes / instructions" />
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="rxFavoriteEditDialogVisible = false">Cancel</el-button>
        <el-button type="primary" :loading="rxFavoriteEditSaving" @click="saveRxFavoriteEdit">
          Save
        </el-button>
      </span>
    </el-dialog>

    <el-dialog
      title="Custom Med Cert Remarks Template"
      :visible.sync="showMedcertRemarksCustomTemplateDialog"
      width="60%"
      :close-on-click-modal="false"
      append-to-body
    >
      <el-form :model="medcertRemarksCustomTemplateForm" label-width="120px">
        <el-form-item label="Template Name">
          <el-input v-model="medcertRemarksCustomTemplateForm.name" placeholder="Enter template name" />
        </el-form-item>
        <el-form-item label="Template Content">
          <el-input
            v-model="medcertRemarksCustomTemplateForm.content"
            type="textarea"
            :rows="8"
            placeholder="Enter your custom med cert remarks template content..."
          />
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="showMedcertRemarksCustomTemplateDialog = false">Cancel</el-button>
        <el-button type="primary" @click="saveMedcertRemarksCustomTemplate">Save Template</el-button>
      </span>
    </el-dialog>

    <el-dialog
      :title="isEditMode ? 'Edit order' : 'Add order'"
      :visible.sync="rxOrderDialogVisible"
      width="90%"
      top="4vh"
      class="rx-order-dialog"
      :close-on-click-modal="false"
      append-to-body
      @close="onRxOrderDialogClose"
    >
      <el-form :inline="true" label-position="top" class="demo-form-inline" style="width: 100%;">
        <el-row :gutter="24">
          <el-col :xs="24" :sm="6" :md="4" :lg="3">
            <el-form-item label="Quantity">
              <el-input v-model="medsArr.qty" autosize clearable />
            </el-form-item>
          </el-col>
          <el-col :xs="24" :sm="18" :md="8" :lg="7">
            <el-form-item label="Generic Name" class="search-medicine-item">
              <el-autocomplete
                v-model="medsArr.custom_generic"
                value-key="generic_name"
                :fetch-suggestions="querySearch"
                popper-class="my-autocomplete rx-fav-meds-dropdown"
                placeholder="Search by generic name"
                class="search-medicine-autocomplete"
                style="width: 100%"
                @select="handleSelect"
              >
                <template #default="{ item }">
                  <div v-if="item.isSectionHeader" class="rx-ac-section-header">{{ item.sectionLabel }}</div>
                  <div
                    v-else
                    class="rx-ac-suggestion-row"
                    :class="{ 'rx-ac-suggestion-row--fav': item.isFavoriteRow }"
                  >
                    <div class="rx-ac-suggestion-main">
                      <span class="rx-ac-name">{{ item.generic_name || item.medicine }}</span>
                      <span v-if="item.medicine || item.unit" class="rx-ac-meta">
                        {{ [item.medicine, item.unit].filter(Boolean).join(' · ') }}
                      </span>
                    </div>
                    <i
                      class="rx-ac-star"
                      :class="(item.favoriteId || item.isFavoriteRow) ? 'el-icon-star-on' : 'el-icon-star-off'"
                      @click.stop="toggleRxFavoriteStar(item)"
                    />
                  </div>
                </template>
              </el-autocomplete>
            </el-form-item>
          </el-col>
          <el-col :xs="24" :sm="18" :md="8" :lg="7">
            <el-form-item label="Brand Name" class="search-medicine-item">
              <el-input v-model="medsArr.custom_brand" autosize clearable placeholder="Brand name" style="width: 100%" />
            </el-form-item>
          </el-col>
          <el-col :xs="24" :sm="6" :md="4" :lg="4">
            <el-form-item label="Dosage">
              <el-input v-model="medsArr.custom_dosage" autosize clearable placeholder="e.g. 20mg" />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <el-row :gutter="20">
        <el-form :inline="true" label-position="top" class="demo-form-inline" style="width: 100%;">
          <el-row :gutter="16" style="width: 100%;" class="rx-meal-timing-inputs">
            <el-col :xs="24" :sm="12" :md="8" :lg="3">
              <el-form-item label="Before Breakfast">
                <el-input
                  v-model="medsArr.bf_b"
                  autosize
                  clearable
                  :class="{ 'rx-dose-input--filled': rxTimingDoseFilled(medsArr.bf_b) }"
                />
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="8" :lg="3">
              <el-form-item label="After Breakfast">
                <el-input
                  v-model="medsArr.bf_a"
                  autosize
                  clearable
                  :class="{ 'rx-dose-input--filled': rxTimingDoseFilled(medsArr.bf_a) }"
                />
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="8" :lg="3">
              <el-form-item label="Before Lunch">
                <el-input
                  v-model="medsArr.l_b"
                  autosize
                  clearable
                  :class="{ 'rx-dose-input--filled': rxTimingDoseFilled(medsArr.l_b) }"
                />
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="8" :lg="3">
              <el-form-item label="After Lunch">
                <el-input
                  v-model="medsArr.l_a"
                  autosize
                  clearable
                  :class="{ 'rx-dose-input--filled': rxTimingDoseFilled(medsArr.l_a) }"
                />
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="8" :lg="3">
              <el-form-item label="Before Dinner">
                <el-input
                  v-model="medsArr.s_b"
                  autosize
                  clearable
                  :class="{ 'rx-dose-input--filled': rxTimingDoseFilled(medsArr.s_b) }"
                />
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="8" :lg="3">
              <el-form-item label="After Dinner">
                <el-input
                  v-model="medsArr.s_a"
                  autosize
                  clearable
                  :class="{ 'rx-dose-input--filled': rxTimingDoseFilled(medsArr.s_a) }"
                />
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="8" :lg="3">
              <el-form-item label="Bedtime">
                <el-input
                  v-model="medsArr.bt"
                  autosize
                  clearable
                  :class="{ 'rx-dose-input--filled': rxTimingDoseFilled(medsArr.bt) }"
                />
              </el-form-item>
            </el-col>
          </el-row>
        </el-form>
      </el-row>
      <el-row :gutter="24">
        <el-form :inline="false" label-position="top" class="demo-form-inline meds-remarks-form" style="width: 100%;">
          <el-col :xs="24" :sm="24" :md="24" :lg="24">
            <el-form-item label="Remarks" class="w-100">
              <el-input v-model="medsArr.remarks" type="textarea" style="width: 100%" />
            </el-form-item>
          </el-col>
        </el-form>
      </el-row>
      <span slot="footer" class="dialog-footer">
        <el-button @click="closeRxOrderDialog">Cancel</el-button>
        <el-button v-role="['doctor', 'admin']" type="success" @click="addMeds()">
          {{ isEditMode ? 'Update' : 'Add' }}
        </el-button>
      </span>
    </el-dialog>

    <el-dialog
      title="Select Diagnostics"
      width="90%"
      top="4vh"
      custom-class="diagnostics-select-dialog"
      :visible.sync="viewDiagnosticsTbl"
      :close-on-click-modal="false"
      :close-on-press-escape="false"
      append-to-body
    >
      <el-input
        v-model="diagnosticsFilterQuery"
        clearable
        prefix-icon="el-icon-search"
        placeholder="Search diagnostics (e.g. CBC, X-ray)"
        class="diagnostics-select-dialog__search"
      />
      <div class="diagnostics-select-dialog__body">
        <div v-if="diagnosticsFilterQuery && !hasFilteredDiagnostics" class="compare-empty">
          No diagnostics match your search.
        </div>
        <el-row :gutter="20">
          <el-col v-if="filterDiagnosticsList(getAllDiagnosticsOfferedChem).length" :xs="24" :sm="24" :md="12" :lg="12">
            <strong>
              <p>BLOOD CHEMISTRY</p>
            </strong>
            <el-row>
              <el-col :span="24">
                <el-checkbox-group v-model="diagnosticsRenderedModel">
                  <el-checkbox
                    v-for="item in filterDiagnosticsList(getAllDiagnosticsOfferedChem)" :key="item.lab_test"
                    :label="item.lab_test" @change="addNewProcedure(item)"
                  >{{ item.lab_test.toUpperCase() }}</el-checkbox>
                </el-checkbox-group>
              </el-col>
            </el-row>
          </el-col>
          <el-col v-if="filterDiagnosticsList(getAllDiagnosticsOfferedHema).length" :xs="24" :sm="24" :md="12" :lg="12">
            <strong>
              <p>HEMATOLOGY</p>
            </strong>
            <el-row>
              <el-col :span="24">
                <el-checkbox-group v-model="diagnosticsRenderedModel">
                  <el-checkbox
                    v-for="item in filterDiagnosticsList(getAllDiagnosticsOfferedHema)" :key="item.lab_test"
                    :label="item.lab_test" @change="addNewProcedure(item)"
                  >{{ item.lab_test.toUpperCase() }}</el-checkbox>
                </el-checkbox-group>
              </el-col>
            </el-row>
          </el-col>
          <el-col v-if="filterDiagnosticsList(getAllDiagnosticsOfferedMicroscopy).length" :xs="24" :sm="24" :md="12" :lg="12">
            <strong>
              <p>CLINICAL MICROSCOPY</p>
            </strong>
            <el-row>
              <el-col :span="24">
                <el-checkbox-group v-model="diagnosticsRenderedModel">
                  <el-checkbox
                    v-for="item in filterDiagnosticsList(getAllDiagnosticsOfferedMicroscopy)" :key="item.lab_test"
                    :label="item.lab_test" @change="addNewProcedure(item)"
                  >{{ item.lab_test.toUpperCase() }}</el-checkbox>
                </el-checkbox-group>
              </el-col>
            </el-row>
          </el-col>
        </el-row>
        <el-divider />
        <el-row :gutter="20">
          <el-col v-if="filterDiagnosticsList(getAllDiagnosticsOfferedXray).length" :xs="24" :sm="24" :md="12" :lg="12">
            <strong>
              <p>X-RAY</p>
            </strong>
            <el-row>
              <el-col :span="24">
                <el-checkbox-group v-model="diagnosticsRenderedModel">
                  <el-checkbox
                    v-for="item in filterDiagnosticsList(getAllDiagnosticsOfferedXray)" :key="item.lab_test_id" :label="item.lab_test"
                    @change="addNewProcedure(item)"
                  >
                    <el-input
                      v-if="diagnosticsRenderedModel.includes(item.lab_test) && item.with_remarks == 1"
                      v-model="findProcedure(item.lab_test_id).remarks" clearable placeholder="Remarks"
                      class="diagnostics-remark-input"
                    />
                    {{ item.lab_test.toUpperCase() }}
                  </el-checkbox>
                </el-checkbox-group>
              </el-col>
            </el-row>
          </el-col>
          <el-col v-if="filterDiagnosticsList(getAllDiagnosticsOfferedUtz).length" :xs="24" :sm="24" :md="12" :lg="12">
            <strong>
              <p>ULTRASOUND</p>
            </strong>
            <el-row>
              <el-col :span="24">
                <el-checkbox-group v-model="diagnosticsRenderedModel">
                  <el-checkbox
                    v-for="item in filterDiagnosticsList(getAllDiagnosticsOfferedUtz)" :key="item.lab_test"
                    :label="item.lab_test" @change="addNewProcedure(item)"
                  >{{ item.lab_test.toUpperCase() }}
                    <el-input
                      v-if="diagnosticsRenderedModel.includes(item.lab_test) && item.with_remarks == 1"
                      v-model="findProcedure(item.lab_test_id).remarks" clearable placeholder="Remarks"
                      class="diagnostics-remark-input"
                    />
                  </el-checkbox>
                </el-checkbox-group>
              </el-col>
            </el-row>
          </el-col>
        </el-row>
        <el-divider />
        <el-row :gutter="20">
          <el-col v-if="filterDiagnosticsList(getAllDiagnosticsOfferedImmonulogy).length" :xs="24" :sm="24" :md="12" :lg="12">
            <strong>
              <p>IMMUNOLOGY</p>
            </strong>
            <el-row>
              <el-col :span="24">
                <el-checkbox-group v-model="diagnosticsRenderedModel">
                  <el-checkbox
                    v-for="item in filterDiagnosticsList(getAllDiagnosticsOfferedImmonulogy)" :key="item.lab_test"
                    :label="item.lab_test" @change="addNewProcedure(item)"
                  >{{ item.lab_test.toUpperCase() }}</el-checkbox>
                </el-checkbox-group>
              </el-col>
            </el-row>
          </el-col>

          <el-col v-if="filterDiagnosticsList(getAllDiagnosticsOfferedMirco).length" :xs="24" :sm="24" :md="12" :lg="12">
            <strong>
              <p>MICROBIOLOGY</p>
            </strong>
            <el-row>
              <el-col :span="24">
                <el-checkbox-group v-model="diagnosticsRenderedModel">
                  <el-checkbox
                    v-for="item in filterDiagnosticsList(getAllDiagnosticsOfferedMirco)" :key="item.lab_test"
                    :label="item.lab_test" @change="addNewProcedure(item)"
                  >
                    {{ item.lab_test.toUpperCase() }}
                    <el-input
                      v-if="diagnosticsRenderedModel.includes(item.lab_test) && item.with_remarks == 1"
                      v-model="findProcedure(item.lab_test_id).remarks" clearable placeholder="Remarks"
                      class="diagnostics-remark-input"
                    />
                  </el-checkbox>
                </el-checkbox-group>
              <!-- <el-input v-model="lab_micro_remarks" clearable placeholder="Remarks" /> -->
              </el-col>
            </el-row>
          </el-col>
        </el-row>
        <el-divider />

        <el-row :gutter="20">
          <el-col v-if="filterDiagnosticsList(getAllDiagnosticsOfferedCt).length" :xs="24" :sm="24" :md="12" :lg="12">
            <strong>
              <p>CT SCAN</p>
            </strong>
            <el-row>
              <el-col :span="24">
                <el-checkbox-group v-model="diagnosticsRenderedModel">
                  <el-checkbox
                    v-for="item in filterDiagnosticsList(getAllDiagnosticsOfferedCt)" :key="item.lab_test"
                    :label="item.lab_test" @change="addNewProcedure(item)"
                  >{{ item.lab_test.toUpperCase() }}
                    <el-input
                      v-if="diagnosticsRenderedModel.includes(item.lab_test) && item.with_remarks == 1"
                      v-model="findProcedure(item.lab_test_id).remarks" clearable placeholder="Remarks"
                      class="diagnostics-remark-input"
                    />
                  </el-checkbox>
                </el-checkbox-group>
              </el-col>
            </el-row>
          </el-col>

          <el-col v-if="filterDiagnosticsList(getAllDiagnosticsOfferedMri).length" :xs="24" :sm="24" :md="12" :lg="12">
            <strong>
              <p>MRI</p>
            </strong>
            <el-row>
              <el-col :span="24">
                <el-checkbox-group v-model="diagnosticsRenderedModel">
                  <el-checkbox
                    v-for="item in filterDiagnosticsList(getAllDiagnosticsOfferedMri)" :key="item.lab_test"
                    :label="item.lab_test" @change="addNewProcedure(item)"
                  >{{ item.lab_test.toUpperCase() }}
                    <el-input
                      v-if="diagnosticsRenderedModel.includes(item.lab_test) && item.with_remarks == 1"
                      v-model="findProcedure(item.lab_test_id).remarks" clearable placeholder="Remarks"
                      class="diagnostics-remark-input"
                    />
                  </el-checkbox>
                </el-checkbox-group>
              </el-col>
            </el-row>
          </el-col>
        </el-row>
        <el-divider />

        <el-row :gutter="20">
          <el-col v-if="filterDiagnosticsList(getAllDiagnosticsOfferedOth).length || !diagnosticsFilterQuery" :xs="24" :sm="24" :md="12" :lg="12">
            <strong>
              <p>Others</p>
            </strong>
            <el-row>
              <el-col :span="24">
                <el-checkbox-group v-model="diagnosticsRenderedModel">
                  <el-checkbox
                    v-for="item in filterDiagnosticsList(getAllDiagnosticsOfferedOth)" :key="item.lab_test"
                    :label="item.lab_test" @change="addNewProcedure(item)"
                  >{{ item.lab_test.toUpperCase() }}</el-checkbox>
                </el-checkbox-group>
                <el-input v-model="form.lab_others" clearable placeholder="Others (comma-separated for multiple)" @change="addLabOthers" />
              </el-col>
            </el-row>
          </el-col>

          <el-col v-if="filterDiagnosticsList(getAllDiagnosticsOfferedCrystal).length" :xs="24" :sm="24" :md="12" :lg="12">
            <strong>
              <p>CRYSTAL ANALYSIS</p>
            </strong>
            <el-row>
              <el-col :span="24">
                <el-checkbox-group v-model="diagnosticsRenderedModel">
                  <el-checkbox
                    v-for="item in filterDiagnosticsList(getAllDiagnosticsOfferedCrystal)" :key="item.lab_test"
                    :label="item.lab_test" @change="addNewProcedure(item)"
                  >{{ item.lab_test.toUpperCase() }}
                    <el-input
                      v-if="diagnosticsRenderedModel.includes(item.lab_test) && item.with_remarks == 1"
                      v-model="findProcedure(item.lab_test_id).remarks" clearable placeholder="Remarks"
                      class="diagnostics-remark-input"
                    /></el-checkbox>
                </el-checkbox-group>
                <!-- Synovial Fluid Extra Options -->
                <div v-if="isSynovialFluidSelected" class="diagnostics-synovial-extra">
                  <p class="diagnostics-synovial-extra__title">Additional Options:</p>
                  <el-checkbox-group v-model="synovialFluidExtraOptions">
                    <el-checkbox
                      v-for="option in synovialFluidOptions"
                      :key="option.label"
                      :label="option.label"
                      @change="handleSynovialFluidExtraOption(option)"
                    >
                      {{ option.label.toUpperCase() }}
                    </el-checkbox>
                  </el-checkbox-group>
                </div>
              </el-col>
            </el-row>
          </el-col>
        </el-row>
      </div>
      <span slot="footer" class="dialog-footer diagnostics-select-dialog__footer">
        <el-button type="success" @click="addProcedure">Add</el-button>
      </span>
    </el-dialog>

    <el-dialog title="Details" :visible.sync="oldRecordsdialogVisible" width="30%">
      <p><strong>HPI:</strong> {{ selectedOldRecords.hpi }}</p>
      <p><strong>pmHx:</strong> {{ selectedOldRecords.pmhx }}</p>
      <p><strong>Description:</strong> {{ selectedOldRecords.desc }}</p>
      <p><strong>Date:</strong> {{ selectedOldRecords.date }}</p>
      <p><strong>CC:</strong> {{ selectedOldRecords.cc }}</p>
      <p><strong>Recommendations:</strong> {{ selectedOldRecords.recom }}</p>
    </el-dialog>

    <el-dialog
      :title="'Select Services'" class="compact-table" width="100%" :visible.sync="viewServicesTbl"
      :close-on-click-modal="false" :close-on-press-escape="false"
    >
      <el-checkbox
        v-for="e in getAllServicesOffered" :key="e.description" v-model="servicesRenderedModel"
        :label="e.description" :value="e.description" @change="addNewServices(e)"
      />
      <el-divider />
      <el-button v-role="['doctor', 'admin']" type="success" @click="addServices()">
        Add
      </el-button>
    </el-dialog>

    <el-dialog
      :title="'Cancel Appointment'" :visible.sync="dialogFormVisible" :close-on-click-modal="false"
      :close-on-press-escape="false"
    >
      <div class="form-container">
        <el-form
          ref="appForm" :model="form_cancel" :rules="rules" label-position="left" label-width="150px"
          style="max-width: 500px"
        >
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
    <br>

    <el-card class="profile-card modern-profile-card" shadow="hover">
      <div slot="header" class="profile-header">
        <div class="profile-title">
          <i class="el-icon-user" />
          <span>Patient Profile</span>
        </div>
        <div class="profile-date">
          <i class="el-icon-date" />
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
                <i class="el-icon-user-solid" />
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
                  <i class="el-icon-user" />
                  <span>Name</span>
                </div>
                <div class="profile-value">{{ profile.patientname }}</div>
              </div>
              <div class="profile-item">
                <div class="profile-label">
                  <i class="el-icon-time" />
                  <span>Age</span>
                </div>
                <div class="profile-value">{{ profile.age }} years old</div>
              </div>
              <div class="profile-item">
                <div class="profile-label">
                  <i class="el-icon-calendar" />
                  <span>Birth Date</span>
                </div>
                <div class="profile-value">{{ dateFormat(profile.birthdate) }}</div>
              </div>
              <div class="profile-item">
                <div class="profile-label">
                  <i class="el-icon-s-custom" />
                  <span>Civil Status</span>
                </div>
                <div class="profile-value">{{ profile.civil_status }}</div>
              </div>
              <div class="profile-item">
                <div class="profile-label">
                  <i class="el-icon-user" />
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
                  <i class="el-icon-phone" />
                  <span>Contact</span>
                </div>
                <div class="profile-value">{{ profile.contactno }}</div>
              </div>
              <div class="profile-item">
                <div class="profile-label">
                  <i class="el-icon-location" />
                  <span>Address</span>
                </div>
                <div class="profile-value">{{ profile.address }}</div>
              </div>
              <div class="profile-item">
                <div class="profile-label">
                  <i class="el-icon-medicine" />
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
    <br>

    <!-- Sidebar-driven form sections (replaces tab navigation) -->
    <div class="appointment-form-page">
      <!-- Mobile: collapse sidebar into dropdown -->
      <div v-if="isMobile" class="mobile-section-nav mb-4">
        <el-select v-model="tab" placeholder="Select Section" style="width: 100%;" size="large">
          <el-option
            v-for="tabOption in availableTabs"
            :key="tabOption.name"
            :label="tabOption.label"
            :value="tabOption.name"
          />
        </el-select>
      </div>

      <div class="appointment-form-layout">
        <!-- Desktop/Tablet: left sidebar -->
        <aside v-if="!isMobile" class="appointment-form-sidebar">
          <el-menu
            :default-active="tab"
            class="appointment-sidebar-menu"
            @select="(name) => (tab = name)"
          >
            <el-menu-item
              v-for="tabOption in availableTabs"
              :key="tabOption.name"
              :index="tabOption.name"
            >
              <span>{{ tabOption.label }}</span>
              <i v-if="tabOption.hasContent" class="el-icon-check sidebar-check" />
            </el-menu-item>
          </el-menu>
        </aside>

        <!-- Main content -->
        <main class="appointment-form-container">
          <!-- Histories -->
          <div v-show="tab === 'history'">
            <el-card v-if="checkRole(['admin', 'doctor'])" class="section-card mb-6" shadow="never">
              <div slot="header" class="section-card__header">
                <div class="section-card__title">
                  <i class="el-icon-document" />
                  <span>Histories</span>
                </div>
              </div>
              <el-card style="max-width: 100%" shadow="never">
                <el-form label-position="top">
                  <el-row :gutter="20">
                    <el-col :xs="24" :sm="12" :md="12" :lg="6">
                      <el-form-item label="Previous Admission">
                        <el-input v-model="profile.prev_admission" type="textarea" rows="10" />
                      </el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="12" :md="12" :lg="6">
                      <el-form-item label="Previous Surgeries">
                        <el-input v-model="profile.prev_surgeries" type="textarea" rows="10" />
                      </el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="12" :md="12" :lg="6">
                      <el-form-item label="Allergies">
                        <el-input v-model="profile.allergies" type="textarea" rows="10" />
                      </el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="12" :md="12" :lg="6">
                      <el-form-item label="Asthma/Allergic Rhinitis/Atopic Dermatitis">
                        <el-input v-model="profile.asthma" type="textarea" rows="10" />
                      </el-form-item>
                    </el-col>
                  </el-row>
                </el-form>

                <el-form label-position="top">
                  <el-row :gutter="20">
                    <el-col :xs="24" :sm="12" :md="12" :lg="6">
                      <el-form-item label="Hypertension">
                        <el-input v-model="profile.hypertension" type="textarea" rows="10" />
                      </el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="12" :md="12" :lg="6">
                      <el-form-item label="TB">
                        <el-input v-model="profile.tb" type="textarea" rows="10" />
                      </el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="12" :md="12" :lg="6">
                      <el-form-item label="Seizure">
                        <el-input v-model="profile.seizure" type="textarea" rows="10" />
                      </el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="12" :md="12" :lg="6">
                      <el-form-item label="Diabetes">
                        <el-input v-model="profile.diabetes" type="textarea" rows="10" />
                      </el-form-item>
                    </el-col>
                  </el-row>
                </el-form>

                <el-form label-position="top">
                  <el-row :gutter="20">
                    <el-col :xs="24" :sm="24" :md="12" :lg="12">
                      <el-form-item label="COPD">
                        <el-input v-model="profile.copd" type="textarea" rows="10" />
                      </el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="24" :md="12" :lg="12">
                      <el-form-item label="Others">
                        <el-input v-model="profile.pmh_others" type="textarea" rows="10" />
                      </el-form-item>
                    </el-col>
                  </el-row>
                </el-form>
              </el-card>
            </el-card>
          </div>

          <!-- Family History -->
          <div v-show="tab === 'family'">
            <el-card v-if="checkRole(['admin', 'doctor'])" class="section-card mb-6" shadow="never">
              <div slot="header" class="section-card__header">
                <div class="section-card__title">
                  <i class="el-icon-collection" />
                  <span>Family History</span>
                </div>
              </div>
              <div class="block">
                <el-form :inline="true" label-position="top" class="demo-form-inline">
                  <el-form-item label="History">
                    <el-checkbox-group v-model="fam" size="large">
                      <el-checkbox-button label="Hypertension">Hypertension</el-checkbox-button>
                      <el-checkbox-button label="Diabetes Mellitus">Diabetes Mellitus</el-checkbox-button>
                      <el-checkbox-button label="Stroke">Stroke</el-checkbox-button>
                      <el-checkbox-button label="CAD">CAD</el-checkbox-button>
                    </el-checkbox-group>
                  </el-form-item>
                  <el-form-item label="Others" class="w-100">
                    <el-input
                      v-model="profile.fam_others" :autosize="{ minRows: 2, maxRows: 4 }"
                      :rows="2" type="textarea" placeholder="Please input"
                    />
                  </el-form-item>
                </el-form>
                <el-form label-position="top" style="margin-top: 20px;">
                  <el-row :gutter="20">
                    <el-col :xs="24" :sm="24" :md="12" :lg="12">
                      <el-form-item label="Mother Details">
                        <el-input
                          v-model="profile.mother_details" type="textarea" rows="6"
                          placeholder="Enter mother's medical history, age, health conditions, etc."
                        />
                      </el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="24" :md="12" :lg="12">
                      <el-form-item label="Father Details">
                        <el-input
                          v-model="profile.father_details" type="textarea" rows="6"
                          placeholder="Enter father's medical history, age, health conditions, etc."
                        />
                      </el-form-item>
                    </el-col>
                  </el-row>
                </el-form>
              </div>
            </el-card>
          </div>

          <!-- Social / Environment History -->
          <div v-show="tab === 'soc'">
            <el-card v-if="checkRole(['admin', 'doctor'])" class="section-card mb-6" shadow="never">
              <div slot="header" class="section-card__header">
                <div class="section-card__title">
                  <i class="el-icon-s-flag" />
                  <span>Social / Environment History</span>
                </div>
              </div>
              <div class="block">
                <el-form :inline="true" label-position="top" class="demo-form-inline">
                  <el-form-item label="History">
                    <el-checkbox-group v-model="soc" size="large">
                      <el-checkbox-button label="Smoking">Smoking</el-checkbox-button>
                      <el-checkbox-button label="Alcoholic Beverage Drinking">Alcoholic Beverage Drinking</el-checkbox-button>
                    </el-checkbox-group>
                  </el-form-item>
                  <el-form-item v-if="soc.includes('Smoking')" label="Smoking Details" class="w-100">
                    <el-input
                      v-model="profile.smoking_details" :autosize="{ minRows: 2, maxRows: 4 }"
                      :rows="2" type="textarea" placeholder="Please provide details about smoking habits"
                    />
                  </el-form-item>
                  <el-form-item v-if="soc.includes('Alcoholic Beverage Drinking')" label="Alcoholic Beverage Drinking Details" class="w-100">
                    <el-input
                      v-model="profile.alcohol_details" :autosize="{ minRows: 2, maxRows: 4 }"
                      :rows="2" type="textarea" placeholder="Please provide details about alcoholic beverage drinking habits"
                    />
                  </el-form-item>
                  <el-form-item label="Others" class="w-100">
                    <el-input
                      v-model="profile.soc_others" :autosize="{ minRows: 2, maxRows: 4 }"
                      :rows="2" type="textarea" placeholder="Please input"
                    />
                  </el-form-item>
                  <el-form-item label="Vaccinations" class="w-100">
                    <el-input
                      v-model="profile.vaccination_sup" :autosize="{ minRows: 2, maxRows: 4 }"
                      :rows="2" type="textarea" placeholder="Please input"
                    />
                  </el-form-item>
                </el-form>
              </div>
            </el-card>
          </div>

          <!-- Diagnosis -->
          <div v-show="tab === 'first'">
            <el-card class="section-card mb-6" shadow="never">
              <div slot="header" class="section-card__header">
                <div class="section-card__title">
                  <i class="el-icon-edit" />
                  <span>Diagnosis</span>
                </div>
              </div>
              <el-form ref="form" label-width="120px" class="demo-form-inline">
                <el-form-item label="Secretary's Remarks">
                  <el-input v-model="form.nurse_remarks" type="textarea" />
                </el-form-item>
                <el-form-item v-if="checkRole(['admin', 'doctor'])" label="CC">
                  <el-input v-model="form.chiefcomplaints" type="textarea" rows="2" />
                </el-form-item>
                <el-form-item v-if="checkRole(['admin', 'doctor'])" label="History">
                  <el-input v-model="form.history" type="textarea" rows="5" />
                </el-form-item>
              </el-form>
              <el-form
                v-if="checkRole(['admin', 'doctor'])" ref="form" :model="form" label-width="120px"
                class="demo-form-inline"
              >
                <el-form-item label="P.E.">
                  <div class="pe-template-section">
                    <el-row :gutter="20" style="margin-bottom: 15px;">
                      <el-col :span="24">
                        <div class="template-buttons">
                          <el-button
                            v-for="template in peTemplates" :key="template.id" size="small"
                            :type="template.type === 'default' ? 'primary' : 'success'" plain
                            class="template-btn" @click="insertPETemplate(template.content)"
                          >
                            {{ template.name }}
                            <el-button
                              v-if="template.type === 'custom'" size="mini" type="danger" icon="el-icon-delete"
                              circle class="delete-template-btn" @click.stop="deleteTemplate(template.id)"
                            />
                          </el-button>
                        </div>
                        <el-button
                          size="small" type="success" style="margin-left: 10px;"
                          @click="showCustomTemplateDialog = true"
                        >
                          Custom Template
                        </el-button>
                      </el-col>
                    </el-row>
                    <el-input ref="peInput" v-model="form.pe" type="textarea" rows="10" @input="autoResize" />
                    <div class="pe-latest-vitals">
                      <div class="pe-latest-vitals__header">
                        <i class="el-icon-data-line" />
                        <span class="pe-latest-vitals__title">Latest Vitals</span>
                        <span v-if="latestTodayVitals.time_display" class="pe-latest-vitals__time">{{ latestTodayVitals.time_display }}</span>
                        <span class="pe-latest-vitals__date">{{ currentDt() }}</span>
                      </div>
                      <div v-if="hasCurrentVisitVitals" class="pe-latest-vitals__values compare-vitals">
                        <span :class="{ 'pe-latest-vitals__abnormal': isPeVitalAbnormal('bp', latestTodayVitals) }">BP: {{ latestTodayVitals.vit_sys || "-" }}/{{ latestTodayVitals.vit_dia || "-" }} mmHg</span>
                        <span>Weight: {{ latestTodayVitals.weight || "-" }} kg</span>
                        <span>Height: {{ latestTodayVitals.height || "-" }} cm</span>
                        <span :class="{ 'pe-latest-vitals__abnormal': isPeVitalAbnormal('bmi', latestTodayVitals) }">BMI: {{ latestTodayVitals.bmi || "-" }}</span>
                        <span :class="{ 'pe-latest-vitals__abnormal': isPeVitalAbnormal('temp', latestTodayVitals) }">Temp: {{ latestTodayVitals.vit_temp || "-" }} °C</span>
                        <span :class="{ 'pe-latest-vitals__abnormal': isPeVitalAbnormal('hr', latestTodayVitals) }">HR: {{ latestTodayVitals.vit_cr || "-" }} bpm</span>
                        <span :class="{ 'pe-latest-vitals__abnormal': isPeVitalAbnormal('rr', latestTodayVitals) }">RR: {{ latestTodayVitals.vit_rr || "-" }} rpm</span>
                        <span :class="{ 'pe-latest-vitals__abnormal': isPeVitalAbnormal('o2', latestTodayVitals) }">O2 Sat: {{ latestTodayVitals.o2_stat || "-" }} %</span>
                      </div>
                      <p v-else class="pe-latest-vitals__empty">No vitals recorded for this visit yet.</p>
                      <button
                        v-if="hasMoreTodayVitals"
                        type="button"
                        class="pe-latest-vitals__toggle"
                        @click="showTodayVitalsMore = !showTodayVitalsMore"
                      >
                        {{ showTodayVitalsMore ? "See less" : `See more (${otherTodayVitals.length})` }}
                      </button>
                      <div v-if="showTodayVitalsMore && hasMoreTodayVitals" class="pe-latest-vitals__more">
                        <div
                          v-for="reading in otherTodayVitals"
                          :key="reading.id"
                          class="pe-latest-vitals__reading"
                        >
                          <div class="pe-latest-vitals__reading-time">{{ reading.time_display }}</div>
                          <div class="pe-latest-vitals__values compare-vitals">
                            <span :class="{ 'pe-latest-vitals__abnormal': isPeVitalAbnormal('bp', reading) }">BP: {{ reading.vit_sys || "-" }}/{{ reading.vit_dia || "-" }} mmHg</span>
                            <span>Weight: {{ reading.weight || "-" }} kg</span>
                            <span>Height: {{ reading.height || "-" }} cm</span>
                            <span :class="{ 'pe-latest-vitals__abnormal': isPeVitalAbnormal('bmi', reading) }">BMI: {{ reading.bmi || "-" }}</span>
                            <span :class="{ 'pe-latest-vitals__abnormal': isPeVitalAbnormal('temp', reading) }">Temp: {{ reading.vit_temp || "-" }} °C</span>
                            <span :class="{ 'pe-latest-vitals__abnormal': isPeVitalAbnormal('hr', reading) }">HR: {{ reading.vit_cr || "-" }} bpm</span>
                            <span :class="{ 'pe-latest-vitals__abnormal': isPeVitalAbnormal('rr', reading) }">RR: {{ reading.vit_rr || "-" }} rpm</span>
                            <span :class="{ 'pe-latest-vitals__abnormal': isPeVitalAbnormal('o2', reading) }">O2 Sat: {{ reading.o2_stat || "-" }} %</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </el-form-item>

                <!-- Custom Template Dialog -->
                <el-dialog
                  title="Custom Physical Examination Template" :visible.sync="showCustomTemplateDialog" width="60%"
                  :close-on-click-modal="false"
                >
                  <el-form :model="customTemplateForm" label-width="120px">
                    <el-form-item label="Template Name">
                      <el-input v-model="customTemplateForm.name" placeholder="Enter template name" />
                    </el-form-item>
                    <el-form-item label="Template Content">
                      <el-input
                        v-model="customTemplateForm.content" type="textarea" :rows="8"
                        placeholder="Enter your custom P.E. template content..."
                      />
                    </el-form-item>
                  </el-form>
                  <div slot="footer" class="dialog-footer">
                    <el-button @click="showCustomTemplateDialog = false">Cancel</el-button>
                    <el-button type="primary" @click="saveCustomTemplate">Save Template</el-button>
                  </div>
                </el-dialog>

                <el-form-item label="Diagnosis">
                  <div class="pe-template-section">
                    <el-row :gutter="20" style="margin-bottom: 15px;">
                      <el-col :span="24">
                        <div class="template-buttons">
                          <el-button
                            v-for="template in diagnosisTemplates" :key="template.id" size="small"
                            :type="template.type === 'default' ? 'primary' : 'success'" plain
                            class="template-btn" @click="insertDiagnosisTemplate(template.content)"
                          >
                            {{ template.name }}
                            <el-button
                              v-if="template.type === 'custom'" size="mini" type="danger" icon="el-icon-delete"
                              circle class="delete-template-btn" @click.stop="deleteDiagnosisTemplate(template.id)"
                            />
                          </el-button>
                        </div>
                        <el-button
                          size="small" type="success" style="margin-left: 10px;"
                          @click="showDiagnosisCustomTemplateDialog = true"
                        >
                          Custom Template
                        </el-button>
                      </el-col>
                    </el-row>
                    <el-input ref="diagnosisInput" v-model="form.diagnosis" type="textarea" rows="5" @input="autoResize" />
                  </div>
                </el-form-item>

                <el-dialog
                  title="Custom Diagnosis Template" :visible.sync="showDiagnosisCustomTemplateDialog" width="60%"
                  :close-on-click-modal="false"
                >
                  <el-form :model="diagnosisCustomTemplateForm" label-width="120px">
                    <el-form-item label="Template Name">
                      <el-input v-model="diagnosisCustomTemplateForm.name" placeholder="Enter template name" />
                    </el-form-item>
                    <el-form-item label="Template Content">
                      <el-input
                        v-model="diagnosisCustomTemplateForm.content" type="textarea" :rows="8"
                        placeholder="Enter your custom diagnosis template content..."
                      />
                    </el-form-item>
                  </el-form>
                  <div slot="footer" class="dialog-footer">
                    <el-button @click="showDiagnosisCustomTemplateDialog = false">Cancel</el-button>
                    <el-button type="primary" @click="saveDiagnosisCustomTemplate">Save Template</el-button>
                  </div>
                </el-dialog>

                <el-form-item label="Plans">
                  <div class="pe-template-section">
                    <el-row :gutter="20" style="margin-bottom: 15px;">
                      <el-col :span="24">
                        <div class="template-buttons">
                          <el-button
                            v-for="template in plansTemplates" :key="template.id" size="small"
                            :type="template.type === 'default' ? 'primary' : 'success'" plain
                            class="template-btn" @click="insertPlansTemplate(template.content)"
                          >
                            {{ template.name }}
                            <el-button
                              v-if="template.type === 'custom'" size="mini" type="danger" icon="el-icon-delete"
                              circle class="delete-template-btn" @click.stop="deletePlansTemplate(template.id)"
                            />
                          </el-button>
                        </div>
                        <el-button
                          size="small" type="success" style="margin-left: 10px;"
                          @click="showPlansCustomTemplateDialog = true"
                        >
                          Custom Template
                        </el-button>
                      </el-col>
                    </el-row>
                    <el-input ref="plansInput" v-model="form.remarks" type="textarea" rows="10" @input="autoResize" />
                  </div>
                </el-form-item>

                <el-dialog
                  title="Custom Plans Template" :visible.sync="showPlansCustomTemplateDialog" width="60%"
                  :close-on-click-modal="false"
                >
                  <el-form :model="plansCustomTemplateForm" label-width="120px">
                    <el-form-item label="Template Name">
                      <el-input v-model="plansCustomTemplateForm.name" placeholder="Enter template name" />
                    </el-form-item>
                    <el-form-item label="Template Content">
                      <el-input
                        v-model="plansCustomTemplateForm.content" type="textarea" :rows="8"
                        placeholder="Enter your custom plans template content..."
                      />
                    </el-form-item>
                  </el-form>
                  <div slot="footer" class="dialog-footer">
                    <el-button @click="showPlansCustomTemplateDialog = false">Cancel</el-button>
                    <el-button type="primary" @click="savePlansCustomTemplate">Save Template</el-button>
                  </div>
                </el-dialog>
                <el-form-item label="Follow Up Date">
                  <date-picker v-model="form.followup" value-type="format" />
                </el-form-item>
              </el-form>
            </el-card>
          </div>

          <!-- Vitals -->
          <div v-show="tab === 'second'">
            <el-card v-if="checkRole(['admin','secretary','doctor'])" class="section-card mb-6" shadow="never">
              <div slot="header" class="section-card__header">
                <div class="section-card__title">
                  <i class="el-icon-data-line" />
                  <span>Vitals</span>
                </div>
              </div>
              <el-card class="vitals-today-card mb-4" shadow="never">
                <div slot="header" class="section-card__header">
                  <div class="section-card__title">
                    <i class="el-icon-time" />
                    <span>Today's Readings</span>
                  </div>
                </div>
                <div class="pe-latest-vitals pe-latest-vitals--inline">
                  <div class="pe-latest-vitals__header">
                    <span class="pe-latest-vitals__title">Latest</span>
                    <span v-if="latestTodayVitals.time_display" class="pe-latest-vitals__time">{{ latestTodayVitals.time_display }}</span>
                    <span class="pe-latest-vitals__date">{{ currentDt() }}</span>
                  </div>
                  <div v-if="hasCurrentVisitVitals" class="pe-latest-vitals__values compare-vitals">
                    <span :class="{ 'pe-latest-vitals__abnormal': isPeVitalAbnormal('bp', latestTodayVitals) }">BP: {{ latestTodayVitals.vit_sys || "-" }}/{{ latestTodayVitals.vit_dia || "-" }} mmHg</span>
                    <span>Weight: {{ latestTodayVitals.weight || "-" }} kg</span>
                    <span>Height: {{ latestTodayVitals.height || "-" }} cm</span>
                    <span :class="{ 'pe-latest-vitals__abnormal': isPeVitalAbnormal('bmi', latestTodayVitals) }">BMI: {{ latestTodayVitals.bmi || "-" }}</span>
                    <span :class="{ 'pe-latest-vitals__abnormal': isPeVitalAbnormal('temp', latestTodayVitals) }">Temp: {{ latestTodayVitals.vit_temp || "-" }} °C</span>
                    <span :class="{ 'pe-latest-vitals__abnormal': isPeVitalAbnormal('hr', latestTodayVitals) }">HR: {{ latestTodayVitals.vit_cr || "-" }} bpm</span>
                    <span :class="{ 'pe-latest-vitals__abnormal': isPeVitalAbnormal('rr', latestTodayVitals) }">RR: {{ latestTodayVitals.vit_rr || "-" }} rpm</span>
                    <span :class="{ 'pe-latest-vitals__abnormal': isPeVitalAbnormal('o2', latestTodayVitals) }">O2 Sat: {{ latestTodayVitals.o2_stat || "-" }} %</span>
                  </div>
                  <p v-else class="pe-latest-vitals__empty">No vitals recorded for this visit yet.</p>
                  <button
                    v-if="hasMoreTodayVitals"
                    type="button"
                    class="pe-latest-vitals__toggle"
                    @click="showVitalsTabMore = !showVitalsTabMore"
                  >
                    {{ showVitalsTabMore ? "See less" : `See more (${otherTodayVitals.length})` }}
                  </button>
                  <div v-if="showVitalsTabMore && hasMoreTodayVitals" class="pe-latest-vitals__more">
                    <div
                      v-for="reading in otherTodayVitals"
                      :key="'tab-' + reading.id"
                      class="pe-latest-vitals__reading"
                    >
                      <div class="pe-latest-vitals__reading-time">{{ reading.time_display }}</div>
                      <div class="pe-latest-vitals__values compare-vitals">
                        <span :class="{ 'pe-latest-vitals__abnormal': isPeVitalAbnormal('bp', reading) }">BP: {{ reading.vit_sys || "-" }}/{{ reading.vit_dia || "-" }} mmHg</span>
                        <span>Weight: {{ reading.weight || "-" }} kg</span>
                        <span>Height: {{ reading.height || "-" }} cm</span>
                        <span :class="{ 'pe-latest-vitals__abnormal': isPeVitalAbnormal('bmi', reading) }">BMI: {{ reading.bmi || "-" }}</span>
                        <span :class="{ 'pe-latest-vitals__abnormal': isPeVitalAbnormal('temp', reading) }">Temp: {{ reading.vit_temp || "-" }} °C</span>
                        <span :class="{ 'pe-latest-vitals__abnormal': isPeVitalAbnormal('hr', reading) }">HR: {{ reading.vit_cr || "-" }} bpm</span>
                        <span :class="{ 'pe-latest-vitals__abnormal': isPeVitalAbnormal('rr', reading) }">RR: {{ reading.vit_rr || "-" }} rpm</span>
                        <span :class="{ 'pe-latest-vitals__abnormal': isPeVitalAbnormal('o2', reading) }">O2 Sat: {{ reading.o2_stat || "-" }} %</span>
                      </div>
                    </div>
                  </div>
                </div>
              </el-card>
              <el-card style="max-width: 100%" shadow="never">
                <el-form label-position="top" class="demo-form-inline">
                  <el-row :gutter="20">
                    <el-col :xs="24" :sm="12" :md="12" :lg="8">
                      <el-form-item label="Systolic">
                        <el-input v-model="form.vit_sys" autosize clearable />
                      </el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="12" :md="12" :lg="8">
                      <el-form-item label="Diastolic">
                        <el-input v-model="form.vit_dia" clearable />
                      </el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="12" :md="12" :lg="8">
                      <el-form-item label="Weight">
                        <el-input v-model="form.weight" clearable placeholder="kilograms" />
                      </el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="12" :md="12" :lg="8">
                      <el-form-item label="Height">
                        <el-input v-model="form.height" clearable placeholder="centimeters" />
                      </el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="12" :md="12" :lg="8">
                      <el-form-item label="BMI">
                        <el-input v-model="form.bmi" clearable />
                      </el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="12" :md="12" :lg="8">
                      <el-form-item label="Temperature">
                        <el-input v-model="form.vit_temp" clearable />
                      </el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="12" :md="12" :lg="8">
                      <el-form-item label="Cardiac Rate">
                        <el-input v-model="form.vit_cr" clearable />
                      </el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="12" :md="12" :lg="8">
                      <el-form-item label="Respiratory Rate">
                        <el-input v-model="form.vit_rr" clearable />
                      </el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="12" :md="12" :lg="8">
                      <el-form-item label="O2 Stat">
                        <el-input v-model="form.o2_stat" clearable />
                      </el-form-item>
                    </el-col>
                    <el-col :xs="24">
                      <el-form-item v-role="['secretary', 'admin']" label="">
                        <el-button type="primary" @click="upDateBP()">Update</el-button>
                      </el-form-item>
                    </el-col>
                  </el-row>
                </el-form>
              </el-card>

              <el-card class="vitals-history-card mt-4" shadow="never">
                <div slot="header" class="section-card__header">
                  <div class="section-card__title">
                    <i class="el-icon-time" />
                    <span>Vitals History</span>
                  </div>
                </div>
                <el-table
                  v-if="vitals_records.length"
                  :data="vitals_records"
                  border
                  stripe
                  size="small"
                  class="compact-table vitals-history-table"
                  :row-class-name="vitalsHistoryRowClass"
                  :default-sort="{ prop: 'date_sort', order: 'descending' }"
                  max-height="420"
                >
                  <el-table-column type="expand">
                    <template slot-scope="props">
                      <div v-if="props.row.reading_count > 1" class="vitals-history-expand">
                        <p class="vitals-history-expand__title">All readings for {{ props.row.date }}</p>
                        <el-table
                          :data="vitals_by_day[props.row.day_key] || []"
                          border
                          size="mini"
                          class="vitals-history-expand__table"
                        >
                          <el-table-column prop="time_display" label="Time" width="90" />
                          <el-table-column prop="bp" label="BP (mmHg)" width="110" />
                          <el-table-column prop="weight" label="Weight (kg)" width="100" />
                          <el-table-column prop="height" label="Height (cm)" width="100" />
                          <el-table-column prop="bmi" label="BMI" width="70" />
                          <el-table-column prop="vit_temp" label="Temp (°C)" width="90" />
                          <el-table-column prop="vit_cr" label="HR (bpm)" width="90" />
                          <el-table-column prop="vit_rr" label="RR (rpm)" width="90" />
                          <el-table-column prop="o2_stat" label="O2 Sat (%)" width="95" />
                          <el-table-column label="" width="80">
                            <template slot-scope="scope">
                              <el-tag v-if="scope.row.is_latest" size="mini" type="success">Latest</el-tag>
                            </template>
                          </el-table-column>
                        </el-table>
                      </div>
                      <p v-else class="vitals-history-expand__empty">Only one reading recorded for this day.</p>
                    </template>
                  </el-table-column>
                  <el-table-column prop="date" label="Date" sortable width="120" />
                  <el-table-column label="Readings" width="95">
                    <template slot-scope="scope">
                      <span>{{ scope.row.reading_count || 1 }}</span>
                    </template>
                  </el-table-column>
                  <el-table-column prop="bp" label="BP (mmHg)" width="110" />
                  <el-table-column prop="weight" label="Weight (kg)" width="100" />
                  <el-table-column prop="height" label="Height (cm)" width="100" />
                  <el-table-column prop="bmi" label="BMI" width="70" />
                  <el-table-column prop="vit_temp" label="Temp (°C)" width="90" />
                  <el-table-column prop="vit_cr" label="HR (bpm)" width="90" />
                  <el-table-column prop="vit_rr" label="RR (rpm)" width="90" />
                  <el-table-column prop="o2_stat" label="O2 Sat (%)" width="95" />
                </el-table>
                <p v-else class="vitals-history-empty">No past vitals recorded for this patient yet.</p>
              </el-card>
            </el-card>
          </div>

          <!-- OB-GYN -->
          <div v-show="tab === 'obgyn'">
            <el-card v-if="checkRole(['admin', 'doctor'])" class="section-card mb-6" shadow="never">
              <div slot="header" class="section-card__header">
                <div class="section-card__title">
                  <i class="el-icon-female" />
                  <span>OB-GYN</span>
                </div>
              </div>
              <el-card style="max-width: 100%" shadow="never">
                <el-form label-position="top">
                  <el-row :gutter="20">
                    <el-col :xs="24" :sm="12" :md="12" :lg="6">
                      <el-form-item label="Pregnancy">
                        <el-input v-model="form.pregnancy" type="textarea" rows="4" placeholder="Enter pregnancy history" />
                      </el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="12" :md="12" :lg="6">
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
                    <el-col :xs="24" :sm="12" :md="12" :lg="6">
                      <el-form-item label="Contraceptive Use">
                        <el-input v-model="form.contraceptive_use" type="textarea" rows="4" placeholder="Enter contraceptive use history" />
                      </el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="12" :md="12" :lg="6">
                      <el-form-item label="Menopause">
                        <el-input v-model="form.menopause" type="textarea" rows="4" placeholder="Enter menopause information" />
                      </el-form-item>
                    </el-col>
                  </el-row>
                </el-form>
              </el-card>
            </el-card>
          </div>

          <!-- Medicines -->
          <div v-show="tab === 'fourth'">
            <el-card v-if="checkRole(['admin', 'doctor'])" class="section-card mb-6" shadow="never">
              <div slot="header" class="section-card__header">
                <div class="section-card__title">
                  <i class="el-icon-medicine" />
                  <span>Medicines</span>
                </div>
              </div>
              <el-card style="max-width: 100%" shadow="never">
                <div class="rx-prescription-groups-bar">
                  <div class="rx-prescription-groups-tabs-wrap">
                    <el-tabs
                      v-model="activePrescriptionGroupId"
                      type="card"
                      class="rx-prescription-tabs"
                    >
                      <el-tab-pane
                        v-for="g in prescription_groups"
                        :key="g.id"
                        :name="String(g.id)"
                      >
                        <span slot="label" class="rx-prescription-tab-label">
                          <span>{{ g.title }}</span>
                          <i
                            v-role="['doctor', 'admin']"
                            class="el-icon-edit rx-prescription-tab-edit"
                            title="Rename prescription group"
                            @click.stop="renamePrescriptionGroup(g)"
                          />
                        </span>
                      </el-tab-pane>
                    </el-tabs>
                    <el-button
                      v-role="['doctor', 'admin']"
                      class="rx-prescription-add-btn"
                      type="primary"
                      plain
                      icon="el-icon-plus"
                      size="small"
                      :loading="rxGroupActionLoading"
                      @click="addPrescriptionGroup"
                    >
                      Add Prescription
                    </el-button>
                    <el-button
                      v-if="prescription_groups.length > 1"
                      v-role="['doctor', 'admin']"
                      type="danger"
                      plain
                      icon="el-icon-delete"
                      size="small"
                      :loading="rxGroupActionLoading"
                      @click="deleteActivePrescriptionGroup"
                    >
                      Delete group
                    </el-button>
                  </div>
                </div>
                <el-row :gutter="20" class="rx-med-toolbar" style="margin-bottom: 16px;">
                  <el-button v-role="['doctor', 'admin']" type="primary" @click="openRxOrderDialog">
                    Add order
                  </el-button>
                  <el-button v-role="['doctor', 'admin']" type="warning" plain @click="openRxTemplateDialog">
                    Load template
                  </el-button>
                  <el-button v-role="['doctor', 'admin']" type="info" plain @click="openRxPastPrescriptionDialog">
                    Load previous prescription
                  </el-button>
                  <el-button v-role="['doctor', 'admin']" plain @click="openRxFavoritesDialog">
                    <i class="el-icon-star-on" style="margin-right: 4px" />
                    Favorites
                  </el-button>
                  <el-button
                    v-role="['doctor', 'admin']"
                    plain
                    icon="el-icon-refresh"
                    :loading="rxListRefreshLoading"
                    @click="refreshRxList"
                  >
                    Refresh
                  </el-button>
                  <el-dropdown v-role="['doctor', 'admin']" trigger="click" @command="handlePrintRxCommand">
                    <el-button type="success" plain>
                      Print Rx
                      <i class="el-icon-arrow-down el-icon--right" />
                    </el-button>
                    <el-dropdown-menu slot="dropdown">
                      <el-dropdown-item command="current">Print current group</el-dropdown-item>
                      <el-dropdown-item command="all">Print all groups</el-dropdown-item>
                    </el-dropdown-menu>
                  </el-dropdown>
                  <el-button
                    v-role="['doctor', 'admin']"
                    type="danger"
                    plain
                    icon="el-icon-delete"
                    :disabled="!rxListSelection.length"
                    :loading="rxListDeleteLoading"
                    @click="deleteSelectedMeds"
                  >
                    Delete selected
                  </el-button>
                </el-row>
                <el-row :gutter="24">
                  <el-table
                    ref="rxMedTable"
                    :key="'rx-med-' + rxMedTableKey + '-' + activePrescriptionGroupId"
                    row-key="id"
                    :data="activeRxList"
                    style="width: 100%"
                    class="compact-table rx-med-table"
                    size="small"
                    @selection-change="handleRxListSelectionChange"
                  >
                    <el-table-column type="selection" width="48" align="center" />
                    <el-table-column label="" width="40" align="center" class-name="rx-drag-col">
                      <template slot-scope="scope">
                        <span
                          v-role="['doctor', 'admin']"
                          class="rx-drag-handle"
                          title="Drag to reorder"
                        >
                          <i class="el-icon-rank" />
                        </span>
                      </template>
                    </el-table-column>
                    <el-table-column prop="generic" label="Generic" min-width="160" show-overflow-tooltip />
                    <el-table-column prop="brand" label="Brand" min-width="160" show-overflow-tooltip />
                    <el-table-column prop="dosage" label="Dosage" width="90" align="center" show-overflow-tooltip />
                    <el-table-column prop="qty" label="Qty" width="56" align="center" />
                    <el-table-column label="Breakfast" width="120">
                      <el-table-column label="B" width="60" align="center">
                        <template slot-scope="scope">
                          <span :class="{ 'rx-timing-dose-filled': rxTimingDoseFilled(scope.row.bf_b) }">{{ scope.row.bf_b }}</span>
                        </template>
                      </el-table-column>
                      <el-table-column label="A" width="60" align="center">
                        <template slot-scope="scope">
                          <span :class="{ 'rx-timing-dose-filled': rxTimingDoseFilled(scope.row.bf_a) }">{{ scope.row.bf_a }}</span>
                        </template>
                      </el-table-column>
                    </el-table-column>
                    <el-table-column label="Lunch" width="120">
                      <el-table-column label="B" width="60" align="center">
                        <template slot-scope="scope">
                          <span :class="{ 'rx-timing-dose-filled': rxTimingDoseFilled(scope.row.l_b) }">{{ scope.row.l_b }}</span>
                        </template>
                      </el-table-column>
                      <el-table-column label="A" width="60" align="center">
                        <template slot-scope="scope">
                          <span :class="{ 'rx-timing-dose-filled': rxTimingDoseFilled(scope.row.l_a) }">{{ scope.row.l_a }}</span>
                        </template>
                      </el-table-column>
                    </el-table-column>
                    <el-table-column label="Dinner" width="120">
                      <el-table-column label="B" width="60" align="center">
                        <template slot-scope="scope">
                          <span :class="{ 'rx-timing-dose-filled': rxTimingDoseFilled(scope.row.s_b) }">{{ scope.row.s_b }}</span>
                        </template>
                      </el-table-column>
                      <el-table-column label="A" width="60" align="center">
                        <template slot-scope="scope">
                          <span :class="{ 'rx-timing-dose-filled': rxTimingDoseFilled(scope.row.s_a) }">{{ scope.row.s_a }}</span>
                        </template>
                      </el-table-column>
                    </el-table-column>
                    <el-table-column label="Bed" width="56" align="center">
                      <template slot-scope="scope">
                        <span :class="{ 'rx-timing-dose-filled': rxTimingDoseFilled(scope.row.bt) }">{{ scope.row.bt }}</span>
                      </template>
                    </el-table-column>
                    <el-table-column prop="remarks" label="Remarks" min-width="180" show-overflow-tooltip />
                    <el-table-column align="center" label="Actions" width="180">
                      <template slot-scope="scope">
                        <el-button
                          v-role="['doctor', 'admin']" type="primary" size="mini" icon="el-icon-edit"
                          @click="editMed(scope.row)"
                        />
                        <el-button
                          v-if="isEditMode && editingMedId === scope.row.id" v-role="['doctor', 'admin']"
                          type="warning" size="mini" icon="el-icon-close" @click="cancelEdit()"
                        />
                        <el-button
                          v-role="['doctor', 'admin']" type="danger" size="mini" icon="el-icon-delete"
                          @click="deleteMed(scope.row.id)"
                        />
                      </template>
                    </el-table-column>
                  </el-table>
                </el-row>
                <el-form label-position="top" style="margin-top: 16px;">
                  <el-form-item label="Follow Up Date">
                    <date-picker v-model="form.followup" value-type="format" />
                  </el-form-item>
                </el-form>
              </el-card>
            </el-card>
          </div>

          <!-- Diagnostics -->
          <div v-show="tab === 'fifth'">
            <el-card v-if="checkRole(['admin', 'doctor'])" class="section-card mb-6" shadow="never">
              <div slot="header" class="section-card__header">
                <div class="section-card__title">
                  <i class="el-icon-data-analysis" />
                  <span>Diagnostics</span>
                </div>
              </div>
              <el-card style="max-width: 100%" shadow="never">
                <div class="rx-prescription-groups-bar">
                  <div class="rx-prescription-groups-tabs-wrap">
                    <el-tabs
                      v-model="activeDiagnosticGroupId"
                      type="card"
                      class="rx-prescription-tabs"
                      @tab-click="onDiagnosticGroupTabClick"
                    >
                      <el-tab-pane
                        v-for="g in diagnostic_groups"
                        :key="g.id"
                        :name="String(g.id)"
                      >
                        <span slot="label" class="rx-prescription-tab-label">
                          <span>{{ g.title }}</span>
                          <i
                            v-role="['doctor', 'admin']"
                            class="el-icon-edit rx-prescription-tab-edit"
                            title="Rename diagnostic group"
                            @click.stop="renameDiagnosticGroup(g)"
                          />
                        </span>
                      </el-tab-pane>
                    </el-tabs>
                    <el-button
                      v-role="['doctor', 'admin']"
                      class="rx-prescription-add-btn"
                      type="primary"
                      plain
                      icon="el-icon-plus"
                      size="small"
                      :loading="dxGroupActionLoading"
                      @click="addDiagnosticGroup"
                    >
                      Add Diagnostic Group
                    </el-button>
                    <el-button
                      v-if="diagnostic_groups.length > 1"
                      v-role="['doctor', 'admin']"
                      type="danger"
                      plain
                      icon="el-icon-delete"
                      size="small"
                      :loading="dxGroupActionLoading"
                      @click="deleteActiveDiagnosticGroup"
                    >
                      Delete group
                    </el-button>
                  </div>
                </div>
                <el-radio-group v-model="form.fasting_mode">
                  <el-radio label="1">Fasting 8-10 hours </el-radio>
                  <el-radio label="2">Fasting 10-12 hours </el-radio>
                  <el-radio label="3">Non-fasting</el-radio>
                </el-radio-group>
                <el-checkbox v-model="form.sendXrayToEmail" label="Send X-ray images" size="large" />
                <el-row v-if="activeDiagnosticGroup">
                  <el-form :inline="true" label-position="top" class="demo-form-inline">
                    <el-form-item label="Request date">
                      <el-date-picker
                        v-model="activeDiagnosticGroup.request_date"
                        type="date"
                        value-format="yyyy-MM-dd"
                        format="MM/dd/yyyy"
                        placeholder="Select date"
                        @change="saveActiveDiagnosticGroupMeta"
                      />
                    </el-form-item>
                    <el-form-item label="Remarks" class="w-100">
                      <el-input
                        v-model="activeDiagnosticGroup.lab_remarks"
                        type="textarea"
                        style="width: 100%"
                        @blur="saveActiveDiagnosticGroupMeta"
                      />
                    </el-form-item>
                  </el-form>
                </el-row>
                <div class="mb-4" />
                <el-button type="primary" @click="viewDiagnosticsTbl = true">View Diagnostics</el-button>
                <el-button v-role="['doctor', 'admin']" type="warning" plain @click="openDxTemplateDialog">
                  Load template
                </el-button>
                <el-dropdown v-role="['doctor', 'admin']" trigger="click" @command="handlePrintDxCommand">
                  <el-button type="success" plain>
                    Print Diagnostics
                    <i class="el-icon-arrow-down el-icon--right" />
                  </el-button>
                  <el-dropdown-menu slot="dropdown">
                    <el-dropdown-item command="current">Print current group</el-dropdown-item>
                    <el-dropdown-item command="all">Print all groups</el-dropdown-item>
                  </el-dropdown-menu>
                </el-dropdown>
                <el-button
                  v-role="['doctor', 'admin']"
                  type="danger"
                  plain
                  icon="el-icon-delete"
                  :disabled="!dxListSelection.length"
                  :loading="dxListDeleteLoading"
                  @click="deleteSelectedDiagnostics"
                >
                  Delete selected
                </el-button>
                <el-row :gutter="20">
                  <el-table
                    ref="dxTable"
                    :key="'dx-table-' + activeDiagnosticGroupId"
                    row-key="id"
                    :data="activeDiagnosticList"
                    style="width: 100%"
                    class="compact-table dx-table"
                    size="small"
                    @selection-change="handleDxListSelectionChange"
                  >
                    <el-table-column type="selection" width="48" align="center" />
                    <el-table-column label="" width="40" align="center" class-name="rx-drag-col">
                      <template slot-scope="scope">
                        <span
                          v-role="['doctor', 'admin']"
                          class="rx-drag-handle"
                          title="Drag to reorder"
                        >
                          <i class="el-icon-rank" />
                        </span>
                      </template>
                    </el-table-column>
                    <el-table-column prop="diagnostic" label="Procedure" />
                    <el-table-column prop="remarks" label="Remarks" />
                    <el-table-column align="center" label="Actions" width="350">
                      <template slot-scope="scope">
                        <el-button
                          v-role="['doctor', 'admin']" type="danger" size="small" icon="el-icon-delete"
                          @click="removeProcedure(scope.row.id)"
                        >
                          Delete
                        </el-button>
                      </template>
                    </el-table-column>
                  </el-table>
                </el-row>
              </el-card>
            </el-card>
          </div>

          <!-- Services -->
          <div v-show="tab === 'sixth'">
            <el-card class="section-card mb-6 services-card" shadow="never">
              <div slot="header" class="section-header">
                <div class="section-card__title">
                  <i class="el-icon-service" />
                  <span>Services & Billing</span>
                </div>
                <div class="header-actions">
                  <el-button type="default" size="small" @click="printfees">
                    <i class="el-icon-printer" />
                    Print Fees
                  </el-button>
                  <el-button type="primary" size="small" @click="viewServicesTbl = true">
                    <i class="el-icon-plus" />
                    Add Services
                  </el-button>
                </div>
              </div>
              <div class="services-form-section">
                <el-form :inline="true" label-position="top" class="enhanced-form">
                  <el-row :gutter="20">
                    <el-col :xs="24" :sm="12" :md="12" :lg="6">
                      <el-form-item label="Discount" class="form-item-enhanced">
                        <el-input v-model="form.discount" placeholder="Discount amount" class="enhanced-input" />
                      </el-form-item>
                    </el-col>
                  </el-row>
                </el-form>
              </div>
              <div class="services-list-section">
                <el-table v-if="services_list.length > 0" :data="services_list" class="enhanced-table">
                  <el-table-column prop="service" label="Service" min-width="200">
                    <template slot-scope="scope">
                      <div class="service-cell">
                        <i class="el-icon-service" />
                        <span>{{ scope.row.service }}</span>
                      </div>
                    </template>
                  </el-table-column>
                  <el-table-column prop="fee" label="Fee" width="120" align="right">
                    <template slot-scope="scope">
                      <span class="fee-amount">₱{{ scope.row.fee }}</span>
                    </template>
                  </el-table-column>
                  <el-table-column align="center" label="Actions" width="200">
                    <template slot-scope="scope">
                      <el-button
                        v-role="['doctor', 'admin']" type="danger" size="mini" icon="el-icon-delete"
                        class="delete-btn" @click="removeService(scope.row.id)"
                      >
                        Delete
                      </el-button>
                    </template>
                  </el-table-column>
                </el-table>
                <div v-else class="empty-state">
                  <i class="el-icon-service" />
                  <p>No services added yet</p>
                  <p class="empty-subtitle">Click "Add Services" to add procedures</p>
                </div>
              </div>
            </el-card>
          </div>

          <!-- Med Cert -->
          <div v-show="tab === 'medcert'">
            <el-card v-if="checkRole(['admin', 'doctor'])" class="section-card mb-6" shadow="never">
              <div slot="header" class="section-card__header">
                <div class="section-card__title">
                  <i class="el-icon-document-copy" />
                  <span>Med Cert</span>
                </div>
                <el-button v-role="['doctor', 'admin']" type="default" size="small" @click="printmedcert">
                  <i class="el-icon-printer" />
                  Print Med Cert
                </el-button>
              </div>
              <el-card style="max-width: 100%" shadow="never">
                <el-form label-position="top">
                  <el-row :gutter="20">
                    <el-col :xs="24" :sm="12" :md="6" :lg="6">
                      <el-form-item label="Undersigned Date">
                        <date-picker v-model="form.medcert_undersigned" value-type="format" />
                      </el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="24" :md="24" :lg="24">
                      <el-form-item label="Diagnosis">
                        <!-- <p class="medcert-field-hint">Copied from the Diagnosis tab</p> -->
                        <el-input v-model="form.medcert_diagnosis" type="textarea" rows="10" readonly />
                      </el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="24" :md="24" :lg="24">
                      <el-form-item label="Remarks">
                        <div class="pe-template-section">
                          <el-row :gutter="20" style="margin-bottom: 15px;">
                            <el-col :span="24">
                              <div class="template-buttons">
                                <el-button
                                  v-for="template in medcertRemarksTemplates" :key="template.id" size="small"
                                  :type="template.type === 'default' ? 'primary' : 'success'" plain
                                  class="template-btn" @click="insertMedcertRemarksTemplate(template.content)"
                                >
                                  {{ template.name }}
                                  <el-button
                                    v-if="template.type === 'custom'" size="mini" type="danger" icon="el-icon-delete"
                                    circle class="delete-template-btn" @click.stop="deleteMedcertRemarksTemplate(template.id)"
                                  />
                                </el-button>
                              </div>
                              <el-button
                                size="small" type="success" style="margin-left: 10px;"
                                @click="showMedcertRemarksCustomTemplateDialog = true"
                              >
                                Custom Template
                              </el-button>
                            </el-col>
                          </el-row>
                          <el-input v-model="form.medcert_remarks" type="textarea" rows="10" />
                        </div>
                      </el-form-item>
                    </el-col>
                  </el-row>
                </el-form>
              </el-card>
            </el-card>
          </div>

          <!-- Referral -->
          <div v-show="tab === 'referral'">
            <el-card v-if="checkRole(['admin', 'doctor'])" class="section-card mb-6" shadow="never">
              <div slot="header" class="section-card__header">
                <div class="section-card__title">
                  <i class="el-icon-s-promotion" />
                  <span>Referral</span>
                </div>
                <el-button v-role="['doctor', 'admin']" type="default" size="small" @click="printreferral">
                  <i class="el-icon-printer" />
                  Print Referral
                </el-button>
              </div>
              <el-card style="max-width: 100%" shadow="never">
                <el-form label-position="top">
                  <el-row :gutter="20">
                    <el-col :xs="24" :sm="12" :md="6" :lg="6">
                      <el-form-item label="Doctor"><el-input v-model="form.referral_doctor" /></el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="12" :md="6" :lg="6">
                      <el-form-item label="Address 1"><el-input v-model="form.referral_addr1" /></el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="12" :md="6" :lg="6">
                      <el-form-item label="Address 2"><el-input v-model="form.referral_addr2" /></el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="12" :md="6" :lg="6">
                      <el-form-item label="Undersigned Date">
                        <date-picker v-model="form.referral_undersigned" value-type="format" />
                      </el-form-item>
                    </el-col>
                  </el-row>
                  <el-row :gutter="20">
                    <el-col :xs="24" :sm="24" :md="24" :lg="24">
                      <el-form-item label="History">
                        <el-input v-model="form.history" type="textarea" rows="5" />
                      </el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="24" :md="12" :lg="12">
                      <el-form-item label="Diagnosis">
                        <el-input v-model="form.referral_diagnosis" type="textarea" rows="10" />
                      </el-form-item>
                    </el-col>
                    <el-col :xs="24" :sm="24" :md="12" :lg="12">
                      <el-form-item label="Remarks">
                        <el-input v-model="form.referral_remarks" type="textarea" rows="10" />
                      </el-form-item>
                    </el-col>
                  </el-row>
                </el-form>
              </el-card>
            </el-card>
          </div>

          <!-- Attachments -->
          <div v-show="tab === 'attachments'">
            <el-card class="section-card mb-6" shadow="never">
              <div slot="header" class="section-card__header">
                <div class="section-card__title">
                  <i class="el-icon-paperclip" />
                  <span>Attachments</span>
                </div>
              </div>
              <div class="mb-4">
                <el-upload ref="uploadRef" action="#" :auto-upload="false" multiple :on-change="handleChange" :disabled="isUploading">
                  <template #trigger>
                    <el-button
                      ref="uploadRef" size="small" type="info" action="#" :auto-upload="false" multiple
                      :on-change="handleChange" :disabled="isUploading"
                    >Select attachments</el-button>
                  </template>
                  <el-button size="small" type="primary" :loading="isUploading" :disabled="isUploading" @click="submitUpload">
                    {{ isUploading ? 'Uploading...' : 'Submit' }}
                  </el-button>
                </el-upload>
                <div v-if="isUploading" class="mt-3">
                  <el-progress :percentage="uploadProgress" :status="uploadProgress === 100 ? 'success' : ''" />
                  <p class="text-sm text-gray-600 mt-2">{{ uploadStatus }}</p>
                </div>
              </div>
              <el-card style="max-width: 100%" shadow="never">
                <el-dialog :visible.sync="dialogVisible" width="50%">
                  <el-image :src="selectedImage.file" :alt="selectedImage.alt" fit="contain" class="popup-image" />
                  <span slot="footer" class="dialog-footer">
                    <el-button @click="dialogVisible = false">Close</el-button>
                  </span>
                </el-dialog>
                <div v-for="group in attachmentGroups" :key="group.key" style="margin-bottom: 18px;">
                  <div style="font-weight: 600; margin: 6px 0 10px; color: #303133;">
                    {{ group.label }}
                  </div>
                  <div class="att-gallery">
                    <div v-for="item in group.items" :key="item.id || item.newfile || item.fname" class="att-tile">
                      <div class="att-tile__media">
                        <el-image
                          v-if="item.isImage"
                          class="att-tile__image"
                          :src="item.src"
                          fit="cover"
                          :preview-src-list="group.previewList"
                          :initial-index="item.previewIndex"
                        />
                        <div v-else class="att-tile__file" @click="viewFile(item.raw.newfile, item.raw.extension)">
                          <i class="el-icon-document" style="font-size: 24px;" />
                          <div class="att-tile__fileext">{{ (item.raw.extension || '').toUpperCase() }}</div>
                        </div>
                        <el-button
                          class="att-tile__delete"
                          type="danger"
                          icon="el-icon-delete"
                          circle
                          size="mini"
                          @click.stop="deleteAtt(item.raw.id)"
                        />
                      </div>
                      <div class="att-tile__meta">
                        <div class="att-tile__name" :title="item.raw.description || item.raw.fname">
                          {{ item.raw.description || item.raw.fname }}
                        </div>
                        <div class="att-tile__date">
                          {{ item.raw.created_dt }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <el-dialog :visible.sync="viewFileModel" :fullscreen="false" :close-on-click-modal="false">
                  <template #default>
                    <div class="iframe-wrapper">
                      <iframe
                        v-if="isPdf" :src="sourceFile" :style="transformStyle" frameborder="0"
                        class="iframe-full"
                      />
                      <el-image
                        v-if="!isPdf" style="width: 100px; height: 100px" :src="sourceFile" :zoom-rate="1.2"
                        :max-scale="7" :min-scale="0.2" :preview-src-list="[sourceFile]" show-progress :initial-index="4"
                        fit="cover"
                      />
                    </div>
                  </template>
                </el-dialog>
              </el-card>
            </el-card>
          </div>

          <!-- Form -->
          <div v-show="tab === 'form'">
            <el-card class="section-card mb-6" shadow="never">
              <div slot="header" class="section-card__header">
                <div class="section-card__title">
                  <i class="el-icon-edit-outline" />
                  <span>Form</span>
                </div>
                <el-button type="default" size="small" @click="printform">
                  <i class="el-icon-printer" />
                  Print Form
                </el-button>
              </div>
              <el-card style="max-width: 100%" shadow="never">
                <div class="form-editor-container">
                  <div class="form-editor-title-row">
                    <h3 class="form-editor-title">Form Editor</h3>
                    <el-button type="primary" size="small" plain icon="el-icon-document" @click="openFormTemplateDialog">
                      Load template
                    </el-button>
                  </div>
                  <QuillEditor
                    v-model="form.form_content"
                    :height="420"
                    preset="full"
                    :font-sizes="formEditorFontSizes"
                    placeholder="Enter form content here..."
                  />
                </div>
              </el-card>
            </el-card>
          </div>

        </main>
      </div>

    </div>

    <!-- Mobile Tab Navigation -->
    <div v-if="false && isMobile" class="mobile-tab-navigation">
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
            <i class="el-icon-check" />
          </span>
        </el-option>
      </el-select>
    </div>

    <!-- Desktop Tab Navigation -->
    <el-tabs v-if="false && !isMobile" v-model="tab" type="card" class="modern-tabs">
      <el-tab-pane v-if="checkRole(['admin', 'doctor'])" name="history">
        <template #label>
          <span class="tab-label">
            <i class="el-icon-document" />
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
      <el-tab-pane v-if="checkRole(['admin', 'doctor'])" label="Family History" name="family">
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
              <el-input
                v-model="profile.fam_others" :autosize="{ minRows: 2, maxRows: 4 }" style="width: 540px"
                :rows="2" type="textarea" placeholder="Please input"
              />
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
      <el-tab-pane v-if="checkRole(['admin', 'doctor'])" label="Social / Environment History" name="soc">
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
            <el-form-item v-if="soc.includes('Smoking')" label="Smoking Details">
              <el-input
                v-model="profile.smoking_details" :autosize="{ minRows: 2, maxRows: 4 }" style="width: 540px"
                :rows="2" type="textarea" placeholder="Please provide details about smoking habits"
              />
            </el-form-item>
            <el-form-item v-if="soc.includes('Alcoholic Beverage Drinking')" label="Alcoholic Beverage Drinking Details">
              <el-input
                v-model="profile.alcohol_details" :autosize="{ minRows: 2, maxRows: 4 }" style="width: 540px"
                :rows="2" type="textarea" placeholder="Please provide details about alcoholic beverage drinking habits"
              />
            </el-form-item>
            <el-form-item label="Others">
              <el-input
                v-model="profile.soc_others" :autosize="{ minRows: 2, maxRows: 4 }" style="width: 540px"
                :rows="2" type="textarea" placeholder="Please input"
              />
            </el-form-item>
            <el-form-item label="Vaccinations">
              <el-input
                v-model="profile.vaccination_sup" :autosize="{ minRows: 2, maxRows: 4 }" style="width: 540px"
                :rows="2" type="textarea" placeholder="Please input"
              />
            </el-form-item>
          </el-form>
        </div>
      </el-tab-pane>
      <el-tab-pane label="Diagnosis" name="first">
        <el-form ref="form" label-width="120px" class="demo-form-inline">
          <el-form-item label="Secretary's Remarks">
            <el-input v-model="form.nurse_remarks" type="textarea" />
          </el-form-item>
          <el-form-item v-if="checkRole(['admin', 'doctor'])" label="CC">
            <el-input v-model="form.chiefcomplaints" type="textarea" rows="2" />
          </el-form-item>
          <el-form-item v-if="checkRole(['admin', 'doctor'])" label="History">
            <el-input v-model="form.history" type="textarea" rows="5" />
          </el-form-item>
        </el-form>
        <el-form
          v-if="checkRole(['admin', 'doctor'])" ref="form" :model="form" label-width="120px"
          class="demo-form-inline"
        >
          <el-form-item label="P.E.">
            <div class="pe-template-section">
              <el-row :gutter="20" style="margin-bottom: 15px;">
                <el-col :span="24">
                  <div class="template-buttons">
                    <el-button
                      v-for="template in peTemplates" :key="template.id" size="small"
                      :type="template.type === 'default' ? 'primary' : 'success'" plain
                      class="template-btn" @click="insertPETemplate(template.content)"
                    >
                      {{ template.name }}
                      <el-button
                        v-if="template.type === 'custom'" size="mini" type="danger" icon="el-icon-delete"
                        circle class="delete-template-btn" @click.stop="deleteTemplate(template.id)"
                      />
                    </el-button>
                  </div>
                  <el-button
                    size="small" type="success" style="margin-left: 10px;"
                    @click="showCustomTemplateDialog = true"
                  >
                    Custom Template
                  </el-button>
                </el-col>
              </el-row>
              <el-input ref="peInput" v-model="form.pe" type="textarea" rows="10" @input="autoResize" />
            </div>
          </el-form-item>

          <!-- Custom Template Dialog -->
          <el-dialog
            title="Custom Physical Examination Template" :visible.sync="showCustomTemplateDialog" width="60%"
            :close-on-click-modal="false"
          >
            <el-form :model="customTemplateForm" label-width="120px">
              <el-form-item label="Template Name">
                <el-input v-model="customTemplateForm.name" placeholder="Enter template name" />
              </el-form-item>
              <el-form-item label="Template Content">
                <el-input
                  v-model="customTemplateForm.content" type="textarea" :rows="8"
                  placeholder="Enter your custom P.E. template content..."
                />
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
            <el-input ref="plansInput" v-model="form.remarks" type="textarea" rows="10" @input="autoResize" />
          </el-form-item>
          <el-form-item label="Follow Up Date">
            <!-- <el-date-picker
              v-model="form.followup"
              type="date"
              :clearable="false"
              placeholder="Pick a day"
            /> -->
            <date-picker v-model="form.followup" value-type="format" />
          </el-form-item>
          <!-- <el-form-item label="Email">
            <el-input v-model="form.email" autosize clearable />
          </el-form-item> -->
        </el-form>
      </el-tab-pane>

      <el-tab-pane v-if="checkRole(['admin', 'doctor'])" name="obgyn">
        <template #label>
          <span class="tab-label">
            <i class="el-icon-female" />
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
      <!-- <el-tab-pane name="second">
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
      </el-tab-pane> -->

      <el-tab-pane v-if="checkRole(['admin','secretary','doctor'])" label="Vitals" name="second">
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
            <el-form-item label="O2 Stat">
              <el-input v-model="form.o2_stat" clearable />
            </el-form-item>
            <el-form-item v-role="['secretary', 'admin']" label="">
              <br>
              <el-button type="primary" @click="upDateBP()">Update</el-button>
            </el-form-item>
          </el-form>
        </el-card>
      </el-tab-pane>

      <el-tab-pane v-if="checkRole(['admin', 'doctor'])" name="fourth">
        <template #label>
          <span class="tab-label">
            <i class="el-icon-medicine" />
            Medicines
          </span>
        </template>
        <el-card style="max-width: 100%">
          <div class="rx-prescription-groups-bar">
            <div class="rx-prescription-groups-tabs-wrap">
              <el-tabs
                v-model="activePrescriptionGroupId"
                type="card"
                class="rx-prescription-tabs"
              >
                <el-tab-pane
                  v-for="g in prescription_groups"
                  :key="g.id"
                  :name="String(g.id)"
                >
                  <span slot="label" class="rx-prescription-tab-label">
                    <span>{{ g.title }}</span>
                    <i
                      v-role="['doctor', 'admin']"
                      class="el-icon-edit rx-prescription-tab-edit"
                      title="Rename prescription group"
                      @click.stop="renamePrescriptionGroup(g)"
                    />
                  </span>
                </el-tab-pane>
              </el-tabs>
              <el-button
                v-role="['doctor', 'admin']"
                class="rx-prescription-add-btn"
                type="primary"
                plain
                icon="el-icon-plus"
                size="small"
                :loading="rxGroupActionLoading"
                @click="addPrescriptionGroup"
              >
                Add Prescription
              </el-button>
              <el-button
                v-if="prescription_groups.length > 1"
                v-role="['doctor', 'admin']"
                type="danger"
                plain
                icon="el-icon-delete"
                size="small"
                :loading="rxGroupActionLoading"
                @click="deleteActivePrescriptionGroup"
              >
                Delete group
              </el-button>
            </div>
          </div>
          <el-row :gutter="20" class="rx-med-toolbar" style="margin-bottom: 16px;">
            <el-button v-role="['doctor', 'admin']" type="primary" @click="openRxOrderDialog">
              Add order
            </el-button>
            <el-button v-role="['doctor', 'admin']" type="warning" plain @click="openRxTemplateDialog">
              Load template
            </el-button>
            <el-button v-role="['doctor', 'admin']" type="info" plain @click="openRxPastPrescriptionDialog">
              Load previous prescription
            </el-button>
            <el-button v-role="['doctor', 'admin']" plain @click="openRxFavoritesDialog">
              <i class="el-icon-star-on" style="margin-right: 4px" />
              Favorites
            </el-button>
            <el-button
              v-role="['doctor', 'admin']"
              plain
              icon="el-icon-refresh"
              :loading="rxListRefreshLoading"
              @click="refreshRxList"
            >
              Refresh
            </el-button>
            <el-dropdown v-role="['doctor', 'admin']" trigger="click" @command="handlePrintRxCommand">
              <el-button type="success" plain>
                Print Rx
                <i class="el-icon-arrow-down el-icon--right" />
              </el-button>
              <el-dropdown-menu slot="dropdown">
                <el-dropdown-item command="current">Print current group</el-dropdown-item>
                <el-dropdown-item command="all">Print all groups</el-dropdown-item>
              </el-dropdown-menu>
            </el-dropdown>
            <el-button
              v-role="['doctor', 'admin']"
              type="danger"
              plain
              icon="el-icon-delete"
              :disabled="!rxListSelection.length"
              :loading="rxListDeleteLoading"
              @click="deleteSelectedMeds"
            >
              Delete selected
            </el-button>
          </el-row>
          <el-row :gutter="20">
            <el-table
              ref="rxMedTable"
              :key="'rx-med-tab-' + activePrescriptionGroupId"
              :data="activeRxList"
              style="width: 100%"
              class="compact-table rx-med-table"
              size="small"
              @selection-change="handleRxListSelectionChange"
            >
              <el-table-column type="selection" width="48" align="center" />
              <el-table-column label="" width="40" align="center" class-name="rx-drag-col">
                <template slot-scope="scope">
                  <span
                    v-role="['doctor', 'admin']"
                    class="rx-drag-handle"
                    title="Drag to reorder"
                  >
                    <i class="el-icon-rank" />
                  </span>
                </template>
              </el-table-column>
              <el-table-column prop="generic" label="Generic" min-width="160" show-overflow-tooltip />
              <el-table-column prop="brand" label="Brand" min-width="160" show-overflow-tooltip />
              <el-table-column prop="dosage" label="Dosage" width="90" align="center" show-overflow-tooltip />
              <el-table-column prop="qty" label="Qty" width="50" align="center" />
              <el-table-column label="Breakfast" width="120">
                <el-table-column label="B" width="60" align="center">
                  <template slot-scope="scope">
                    <span :class="{ 'rx-timing-dose-filled': rxTimingDoseFilled(scope.row.bf_b) }">{{ scope.row.bf_b }}</span>
                  </template>
                </el-table-column>
                <el-table-column label="A" width="60" align="center">
                  <template slot-scope="scope">
                    <span :class="{ 'rx-timing-dose-filled': rxTimingDoseFilled(scope.row.bf_a) }">{{ scope.row.bf_a }}</span>
                  </template>
                </el-table-column>
              </el-table-column>
              <el-table-column label="Lunch" width="120">
                <el-table-column label="B" width="60" align="center">
                  <template slot-scope="scope">
                    <span :class="{ 'rx-timing-dose-filled': rxTimingDoseFilled(scope.row.l_b) }">{{ scope.row.l_b }}</span>
                  </template>
                </el-table-column>
                <el-table-column label="A" width="60" align="center">
                  <template slot-scope="scope">
                    <span :class="{ 'rx-timing-dose-filled': rxTimingDoseFilled(scope.row.l_a) }">{{ scope.row.l_a }}</span>
                  </template>
                </el-table-column>
              </el-table-column>
              <el-table-column label="Dinner" width="120">
                <el-table-column label="B" width="60" align="center">
                  <template slot-scope="scope">
                    <span :class="{ 'rx-timing-dose-filled': rxTimingDoseFilled(scope.row.s_b) }">{{ scope.row.s_b }}</span>
                  </template>
                </el-table-column>
                <el-table-column label="A" width="60" align="center">
                  <template slot-scope="scope">
                    <span :class="{ 'rx-timing-dose-filled': rxTimingDoseFilled(scope.row.s_a) }">{{ scope.row.s_a }}</span>
                  </template>
                </el-table-column>
              </el-table-column>
              <el-table-column label="Bed" width="56" align="center">
                <template slot-scope="scope">
                  <span :class="{ 'rx-timing-dose-filled': rxTimingDoseFilled(scope.row.bt) }">{{ scope.row.bt }}</span>
                </template>
              </el-table-column>
              <el-table-column prop="remarks" label="Remarks" width="150" show-overflow-tooltip />
              <el-table-column align="center" label="Actions" width="180">
                <template slot-scope="scope">
                  <el-button
                    v-role="['doctor', 'admin']" type="primary" size="mini" icon="el-icon-edit"
                    @click="editMed(scope.row)"
                  />
                  <el-button
                    v-if="isEditMode && editingMedId === scope.row.id" v-role="['doctor', 'admin']"
                    type="warning" size="mini" icon="el-icon-close" @click="cancelEdit()"
                  />
                  <el-button
                    v-role="['doctor', 'admin']" type="danger" size="mini" icon="el-icon-delete"
                    @click="deleteMed(scope.row.id)"
                  />
                </template>
              </el-table-column>
            </el-table>
          </el-row>
          <el-form label-position="top" style="margin-top: 16px;">
            <el-form-item label="Follow Up Date">
              <date-picker v-model="form.followup" value-type="format" />
            </el-form-item>
          </el-form>
        </el-card>
      </el-tab-pane>
      <el-tab-pane v-if="checkRole(['admin', 'doctor'])" name="fifth">
        <template #label>
          <span class="tab-label">
            <i class="el-icon-data-analysis" />
            Diagnostics
          </span>
        </template>
        <el-card style="max-width: 100%">
          <div class="rx-prescription-groups-bar">
            <div class="rx-prescription-groups-tabs-wrap">
              <el-tabs
                v-model="activeDiagnosticGroupId"
                type="card"
                class="rx-prescription-tabs"
                @tab-click="onDiagnosticGroupTabClick"
              >
                <el-tab-pane
                  v-for="g in diagnostic_groups"
                  :key="g.id"
                  :name="String(g.id)"
                >
                  <span slot="label" class="rx-prescription-tab-label">
                    <span>{{ g.title }}</span>
                    <i
                      v-role="['doctor', 'admin']"
                      class="el-icon-edit rx-prescription-tab-edit"
                      title="Rename diagnostic group"
                      @click.stop="renameDiagnosticGroup(g)"
                    />
                  </span>
                </el-tab-pane>
              </el-tabs>
              <el-button
                v-role="['doctor', 'admin']"
                class="rx-prescription-add-btn"
                type="primary"
                plain
                icon="el-icon-plus"
                size="small"
                :loading="dxGroupActionLoading"
                @click="addDiagnosticGroup"
              >
                Add Diagnostic Group
              </el-button>
              <el-button
                v-if="diagnostic_groups.length > 1"
                v-role="['doctor', 'admin']"
                type="danger"
                plain
                icon="el-icon-delete"
                size="small"
                :loading="dxGroupActionLoading"
                @click="deleteActiveDiagnosticGroup"
              >
                Delete group
              </el-button>
            </div>
          </div>
          <el-radio-group v-model="form.fasting_mode">
            <el-radio label="1">Fasting 8-10 hours </el-radio>
            <el-radio label="2">Fasting 10-12 hours </el-radio>
            <el-radio label="3">Non-fasting</el-radio>
          </el-radio-group>
          <el-checkbox v-model="form.sendXrayToEmail" label="Send X-ray images" size="large" />
          <el-row v-if="activeDiagnosticGroup">
            <el-form :inline="true" label-position="top" class="demo-form-inline">
              <el-form-item label="Request date">
                <el-date-picker
                  v-model="activeDiagnosticGroup.request_date"
                  type="date"
                  value-format="yyyy-MM-dd"
                  format="MM/dd/yyyy"
                  placeholder="Select date"
                  @change="saveActiveDiagnosticGroupMeta"
                />
              </el-form-item>
              <el-form-item label="Remarks">
                <el-input
                  v-model="activeDiagnosticGroup.lab_remarks"
                  type="textarea"
                  style="width: 650px"
                  @blur="saveActiveDiagnosticGroupMeta"
                />
              </el-form-item>
              <el-form-item label="Findings">
                <el-input
                  v-model="activeDiagnosticGroup.findings"
                  type="textarea"
                  style="width: 650px"
                  @blur="saveActiveDiagnosticGroupMeta"
                />
              </el-form-item>
              <el-form-item label="Notes">
                <el-input
                  v-model="activeDiagnosticGroup.notes"
                  type="textarea"
                  style="width: 650px"
                  @blur="saveActiveDiagnosticGroupMeta"
                />
              </el-form-item>
              <el-form-item label="Recommendations">
                <el-input
                  v-model="activeDiagnosticGroup.recommendations"
                  type="textarea"
                  style="width: 650px"
                  @blur="saveActiveDiagnosticGroupMeta"
                />
              </el-form-item>
            </el-form>
          </el-row>
          <br>
          <br>
          <br>
          <el-button type="primary" @click="viewDiagnosticsTbl = true">View Diagnostics</el-button>
          <el-button v-role="['doctor', 'admin']" type="warning" plain @click="openDxTemplateDialog">
            Load template
          </el-button>
          <el-dropdown v-role="['doctor', 'admin']" trigger="click" @command="handlePrintDxCommand">
            <el-button type="success" plain>
              Print Diagnostics
              <i class="el-icon-arrow-down el-icon--right" />
            </el-button>
            <el-dropdown-menu slot="dropdown">
              <el-dropdown-item command="current">Print current group</el-dropdown-item>
              <el-dropdown-item command="all">Print all groups</el-dropdown-item>
            </el-dropdown-menu>
          </el-dropdown>
          <el-button
            v-role="['doctor', 'admin']"
            type="danger"
            plain
            icon="el-icon-delete"
            :disabled="!dxListSelection.length"
            :loading="dxListDeleteLoading"
            @click="deleteSelectedDiagnostics"
          >
            Delete selected
          </el-button>
          <el-row :gutter="20">
            <el-table
              ref="dxTable"
              :key="'dx-table-tab-' + activeDiagnosticGroupId"
              row-key="id"
              :data="activeDiagnosticList"
              style="width: 100%"
              class="compact-table dx-table"
              size="small"
              @selection-change="handleDxListSelectionChange"
            >
              <el-table-column type="selection" width="48" align="center" />
              <el-table-column label="" width="40" align="center" class-name="rx-drag-col">
                <template slot-scope="scope">
                  <span
                    v-role="['doctor', 'admin']"
                    class="rx-drag-handle"
                    title="Drag to reorder"
                  >
                    <i class="el-icon-rank" />
                  </span>
                </template>
              </el-table-column>
              <el-table-column prop="diagnostic" label="Procedure" />
              <el-table-column prop="remarks" label="Remarks" />
              <el-table-column align="center" label="Actions" width="350">
                <template slot-scope="scope">
                  <el-button
                    v-role="['doctor', 'admin']" type="danger" size="small" icon="el-icon-delete"
                    @click="removeProcedure(scope.row.id)"
                  >
                    Delete
                  </el-button>
                </template>
              </el-table-column>
            </el-table>
          </el-row>
          <el-divider />
        </el-card>
      </el-tab-pane>
      <el-tab-pane name="sixth"">
        <span slot="label" class="tab-label">
          <i class="el-icon-service" />
          Services
        </span>
        <div class="tab-content">
          <el-card class="services-card">
            <div slot="header" class="section-header">
              <i class="el-icon-service" />
              <span>Services & Billing</span>
              <div class="header-actions">
                <el-button type="default" size="small" @click="printfees">
                  <i class="el-icon-printer" />
                  Print Fees
                </el-button>
                <el-button type="primary" size="small" @click="viewServicesTbl = true">
                  <i class="el-icon-plus" />
                  Add Services
                </el-button>
              </div>
            </div>

            <div class="services-form-section">
              <el-form :inline="true" label-position="top" class="enhanced-form">
                <el-row :gutter="20">
                  <el-col :span="6">
                    <el-form-item label="Discount" class="form-item-enhanced">
                      <el-input v-model="form.discount" placeholder="Discount amount" class="enhanced-input" />
                    </el-form-item>
                  </el-col>
                </el-row>
              </el-form>
            </div>

            <div class="services-list-section">
              <el-table v-if="services_list.length > 0" :data="services_list" class="enhanced-table">
                <el-table-column prop="service" label="Service" min-width="200">
                  <template slot-scope="scope">
                    <div class="service-cell">
                      <i class="el-icon-service" />
                      <span>{{ scope.row.service }}</span>
                    </div>
                  </template>
                </el-table-column>
                <el-table-column prop="fee" label="Fee" width="120" align="right">
                  <template slot-scope="scope">
                    <span class="fee-amount">₱{{ scope.row.fee }}</span>
                  </template>
                </el-table-column>
                <el-table-column align="center" label="Actions" width="200">
                  <template slot-scope="scope">
                    <!-- <el-button v-role="['doctor', 'admin']" type="primary" size="mini" icon="el-icon-edit"
                      @click="editService(scope.row.id)" class="edit-btn">
                      Edit
                    </el-button> -->
                    <el-button
                      v-role="['doctor', 'admin']" type="danger" size="mini" icon="el-icon-delete"
                      class="delete-btn" @click="removeService(scope.row.id)"
                    >
                      Delete
                    </el-button>
                  </template>
                </el-table-column>
              </el-table>

              <div v-else class="empty-state">
                <i class="el-icon-service" />
                <p>No services added yet</p>
                <p class="empty-subtitle">Click "Add Services" to add procedures</p>
              </div>
            </div>
          </el-card>
        </div>
      </el-tab-pane>
      <el-tab-pane v-if="checkRole(['admin', 'doctor'])" name="medcert">
        <template #label>
          <span class="tab-label">
            <i class="el-icon-document-copy" />
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
                  <date-picker v-model="form.medcert_undersigned" value-type="format" />
                </el-form-item>
              </el-col>
              <el-col :span="9">
                <el-form-item label="Diagnosis">
                  <p class="medcert-field-hint">Copied from the Diagnosis tab</p>
                  <el-input v-model="form.medcert_diagnosis" type="textarea" rows="10" readonly />
                </el-form-item>
              </el-col>
              <el-col :span="9">
                <el-form-item label="Remarks">
                  <div class="pe-template-section">
                    <el-row :gutter="20" style="margin-bottom: 15px;">
                      <el-col :span="24">
                        <div class="template-buttons">
                          <el-button
                            v-for="template in medcertRemarksTemplates" :key="template.id" size="small"
                            :type="template.type === 'default' ? 'primary' : 'success'" plain
                            class="template-btn" @click="insertMedcertRemarksTemplate(template.content)"
                          >
                            {{ template.name }}
                            <el-button
                              v-if="template.type === 'custom'" size="mini" type="danger" icon="el-icon-delete"
                              circle class="delete-template-btn" @click.stop="deleteMedcertRemarksTemplate(template.id)"
                            />
                          </el-button>
                        </div>
                        <el-button
                          size="small" type="success" style="margin-left: 10px;"
                          @click="showMedcertRemarksCustomTemplateDialog = true"
                        >
                          Custom Template
                        </el-button>
                      </el-col>
                    </el-row>
                    <el-input v-model="form.medcert_remarks" type="textarea" rows="10" />
                  </div>
                </el-form-item>
              </el-col>
            </el-row>
          </el-form>
        </el-card>
      </el-tab-pane>
      <el-tab-pane v-if="checkRole(['admin', 'doctor'])" name="referral">
        <template #label>
          <span class="tab-label">
            <i class="el-icon-s-promotion" />
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
                  <date-picker v-model="form.referral_undersigned" value-type="format" />
                </el-form-item>
              </el-col>
            </el-row>
            <el-row :gutter="20">
              <el-col :span="24">
                <el-form-item label="History">
                  <el-input v-model="form.history" type="textarea" rows="5" />
                </el-form-item>
              </el-col>
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
            <i class="el-icon-paperclip" />
            Attachments
          </span>
        </template>
        <div class="mb-4">
          <el-upload ref="uploadRef" action="#" :auto-upload="false" multiple :on-change="handleChange" :disabled="isUploading">
            <template #trigger>
              <el-button
                ref="uploadRef" size="small" type="info" action="#" :auto-upload="false" multiple
                :on-change="handleChange" :disabled="isUploading"
              >Select attachments</el-button>
            </template>
            <el-button size="small" type="primary" :loading="isUploading" :disabled="isUploading" @click="submitUpload">
              {{ isUploading ? 'Uploading...' : 'Submit' }}
            </el-button>
          </el-upload>

          <!-- Upload Progress -->
          <div v-if="isUploading" class="mt-3">
            <el-progress :percentage="uploadProgress" :status="uploadProgress === 100 ? 'success' : ''" />
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

          <div v-for="group in attachmentGroups" :key="group.key" style="margin-bottom: 18px;">
            <div style="font-weight: 600; margin: 6px 0 10px; color: #303133;">
              {{ group.label }}
            </div>
            <div class="att-gallery">
              <div v-for="item in group.items" :key="item.id || item.newfile || item.fname" class="att-tile">
                <div class="att-tile__media">
                  <el-image
                    v-if="item.isImage"
                    class="att-tile__image"
                    :src="item.src"
                    fit="cover"
                    :preview-src-list="group.previewList"
                    :initial-index="item.previewIndex"
                  />
                  <div v-else class="att-tile__file" @click="viewFile(item.raw.newfile, item.raw.extension)">
                    <i class="el-icon-document" style="font-size: 24px;" />
                    <div class="att-tile__fileext">{{ (item.raw.extension || '').toUpperCase() }}</div>
                  </div>
                  <el-button
                    class="att-tile__delete"
                    type="danger"
                    icon="el-icon-delete"
                    circle
                    size="mini"
                    @click.stop="deleteAtt(item.raw.id)"
                  />
                </div>
                <div class="att-tile__meta">
                  <div class="att-tile__name" :title="item.raw.description || item.raw.fname">
                    {{ item.raw.description || item.raw.fname }}
                  </div>
                  <div class="att-tile__date">
                    {{ item.raw.created_dt }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <el-dialog :visible.sync="viewFileModel" :fullscreen="false" :close-on-click-modal="false">
            <template #default>
              <div class="iframe-wrapper">
                <iframe
                  v-if="isPdf" :src="sourceFile" :style="transformStyle" frameborder="0"
                  class="iframe-full"
                />
                <el-image
                  v-if="!isPdf" style="width: 100px; height: 100px" :src="sourceFile" :zoom-rate="1.2"
                  :max-scale="7" :min-scale="0.2" :preview-src-list="[sourceFile]" show-progress :initial-index="4"
                  fit="cover"
                />
              </div>
            </template>
          </el-dialog>
        </el-card>
      </el-tab-pane>
      <el-tab-pane name="form">
        <template #label>
          <span class="tab-label">
            <i class="el-icon-edit-outline" />
            Form
          </span>
        </template>
        <el-card style="max-width: 100%">
          <div class="form-editor-container">
            <div class="form-editor-title-row">
              <h3 class="form-editor-title">Form Editor</h3>
              <el-button type="primary" size="small" plain icon="el-icon-document" @click="openFormTemplateDialog">
                Load template
              </el-button>
            </div>
            <QuillEditor
              v-model="form.form_content"
              :height="420"
              preset="full"
              :font-sizes="formEditorFontSizes"
              placeholder="Enter form content here..."
            />
          </div>
        </el-card>
      </el-tab-pane>
    </el-tabs>

    <!-- Mobile Content Sections -->
    <div v-if="false && isMobile" class="mobile-content">
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
              <el-input
                v-model="profile.fam_others" :autosize="{ minRows: 2, maxRows: 4 }"
                type="textarea" placeholder="Please input"
              />
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
          <el-form-item v-if="checkRole(['admin', 'doctor'])" label="CC">
            <el-input v-model="form.chiefcomplaints" type="textarea" rows="2" />
          </el-form-item>
          <el-form-item v-if="checkRole(['admin', 'doctor'])" label="History">
            <el-input v-model="form.history" type="textarea" rows="5" />
          </el-form-item>
          <el-form-item v-if="checkRole(['admin', 'doctor'])" label="P.E.">
            <div class="pe-template-section">
              <el-row :gutter="20" style="margin-bottom: 15px;">
                <el-col :span="24">
                  <div class="template-buttons">
                    <el-button
                      v-for="template in peTemplates" :key="template.id" size="small"
                      :type="template.type === 'default' ? 'primary' : 'success'" plain
                      class="template-btn" @click="insertPETemplate(template.content)"
                    >
                      {{ template.name }}
                      <el-button
                        v-if="template.type === 'custom'" size="mini" type="danger" icon="el-icon-delete"
                        circle class="delete-template-btn" @click.stop="deleteTemplate(template.id)"
                      />
                    </el-button>
                  </div>
                  <el-button
                    size="small" type="success" style="margin-left: 10px;"
                    @click="showCustomTemplateDialog = true"
                  >
                    Custom Template
                  </el-button>
                </el-col>
              </el-row>
              <el-input ref="peInput" v-model="form.pe" type="textarea" rows="10" @input="autoResize" />
            </div>
          </el-form-item>
          <el-form-item v-if="checkRole(['admin', 'doctor'])" label="Diagnosis">
            <el-input v-model="form.diagnosis" type="textarea" />
          </el-form-item>
          <el-form-item v-if="checkRole(['admin', 'doctor'])" label="Plans">
            <el-input ref="plansInput" v-model="form.remarks" type="textarea" rows="10" @input="autoResize" />
          </el-form-item>
          <el-form-item v-if="checkRole(['admin', 'doctor'])" label="Follow Up Date">
            <date-picker v-model="form.followup" value-type="format" />
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
          <div class="rx-prescription-groups-bar">
            <div class="rx-prescription-groups-tabs-wrap">
              <el-tabs
                v-model="activePrescriptionGroupId"
                type="card"
                class="rx-prescription-tabs"
              >
                <el-tab-pane
                  v-for="g in prescription_groups"
                  :key="g.id"
                  :name="String(g.id)"
                >
                  <span slot="label" class="rx-prescription-tab-label">
                    <span>{{ g.title }}</span>
                    <i
                      v-role="['doctor', 'admin']"
                      class="el-icon-edit rx-prescription-tab-edit"
                      title="Rename prescription group"
                      @click.stop="renamePrescriptionGroup(g)"
                    />
                  </span>
                </el-tab-pane>
              </el-tabs>
              <el-button
                v-role="['doctor', 'admin']"
                class="rx-prescription-add-btn"
                type="primary"
                plain
                icon="el-icon-plus"
                size="small"
                :loading="rxGroupActionLoading"
                @click="addPrescriptionGroup"
              >
                Add Prescription
              </el-button>
              <el-button
                v-if="prescription_groups.length > 1"
                v-role="['doctor', 'admin']"
                type="danger"
                plain
                icon="el-icon-delete"
                size="small"
                :loading="rxGroupActionLoading"
                @click="deleteActivePrescriptionGroup"
              >
                Delete group
              </el-button>
            </div>
          </div>
          <el-row :gutter="20" class="rx-med-toolbar" style="margin-bottom: 16px;">
            <el-button v-role="['doctor', 'admin']" type="primary" @click="openRxOrderDialog">
              Add order
            </el-button>
            <el-button v-role="['doctor', 'admin']" type="warning" plain @click="openRxTemplateDialog">
              Load template
            </el-button>
            <el-button v-role="['doctor', 'admin']" type="info" plain @click="openRxPastPrescriptionDialog">
              Load previous prescription
            </el-button>
            <el-button v-role="['doctor', 'admin']" plain @click="openRxFavoritesDialog">
              <i class="el-icon-star-on" style="margin-right: 4px" />
              Favorites
            </el-button>
            <el-button
              v-role="['doctor', 'admin']"
              plain
              icon="el-icon-refresh"
              :loading="rxListRefreshLoading"
              @click="refreshRxList"
            >
              Refresh
            </el-button>
            <el-dropdown v-role="['doctor', 'admin']" trigger="click" @command="handlePrintRxCommand">
              <el-button type="success" plain>
                Print Rx
                <i class="el-icon-arrow-down el-icon--right" />
              </el-button>
              <el-dropdown-menu slot="dropdown">
                <el-dropdown-item command="current">Print current group</el-dropdown-item>
                <el-dropdown-item command="all">Print all groups</el-dropdown-item>
              </el-dropdown-menu>
            </el-dropdown>
            <el-button
              v-role="['doctor', 'admin']"
              type="danger"
              plain
              icon="el-icon-delete"
              :disabled="!rxListSelection.length"
              :loading="rxListDeleteLoading"
              @click="deleteSelectedMeds"
            >
              Delete selected
            </el-button>
          </el-row>
          <el-row :gutter="20">
            <el-table
              ref="rxMedTable"
              :key="'rx-med-mobile-' + activePrescriptionGroupId"
              :data="activeRxList"
              style="width: 100%"
              class="compact-table rx-med-table"
              size="small"
              @selection-change="handleRxListSelectionChange"
            >
              <el-table-column type="selection" width="48" align="center" />
              <el-table-column label="" width="40" align="center" class-name="rx-drag-col">
                <template slot-scope="scope">
                  <span
                    v-role="['doctor', 'admin']"
                    class="rx-drag-handle"
                    title="Drag to reorder"
                  >
                    <i class="el-icon-rank" />
                  </span>
                </template>
              </el-table-column>
              <el-table-column prop="generic" label="Generic" min-width="160" show-overflow-tooltip />
              <el-table-column prop="brand" label="Brand" min-width="160" show-overflow-tooltip />
              <el-table-column prop="dosage" label="Dosage" width="90" align="center" show-overflow-tooltip />
              <el-table-column prop="qty" label="Qty" width="56" align="center" />
              <el-table-column label="Breakfast" width="120">
                <el-table-column label="B" width="60" align="center">
                  <template slot-scope="scope">
                    <span :class="{ 'rx-timing-dose-filled': rxTimingDoseFilled(scope.row.bf_b) }">{{ scope.row.bf_b }}</span>
                  </template>
                </el-table-column>
                <el-table-column label="A" width="60" align="center">
                  <template slot-scope="scope">
                    <span :class="{ 'rx-timing-dose-filled': rxTimingDoseFilled(scope.row.bf_a) }">{{ scope.row.bf_a }}</span>
                  </template>
                </el-table-column>
              </el-table-column>
              <el-table-column label="Lunch" width="120">
                <el-table-column label="B" width="60" align="center">
                  <template slot-scope="scope">
                    <span :class="{ 'rx-timing-dose-filled': rxTimingDoseFilled(scope.row.l_b) }">{{ scope.row.l_b }}</span>
                  </template>
                </el-table-column>
                <el-table-column label="A" width="60" align="center">
                  <template slot-scope="scope">
                    <span :class="{ 'rx-timing-dose-filled': rxTimingDoseFilled(scope.row.l_a) }">{{ scope.row.l_a }}</span>
                  </template>
                </el-table-column>
              </el-table-column>
              <el-table-column label="Dinner" width="120">
                <el-table-column label="B" width="60" align="center">
                  <template slot-scope="scope">
                    <span :class="{ 'rx-timing-dose-filled': rxTimingDoseFilled(scope.row.s_b) }">{{ scope.row.s_b }}</span>
                  </template>
                </el-table-column>
                <el-table-column label="A" width="60" align="center">
                  <template slot-scope="scope">
                    <span :class="{ 'rx-timing-dose-filled': rxTimingDoseFilled(scope.row.s_a) }">{{ scope.row.s_a }}</span>
                  </template>
                </el-table-column>
              </el-table-column>
              <el-table-column label="Bed" width="56" align="center">
                <template slot-scope="scope">
                  <span :class="{ 'rx-timing-dose-filled': rxTimingDoseFilled(scope.row.bt) }">{{ scope.row.bt }}</span>
                </template>
              </el-table-column>
              <el-table-column prop="remarks" label="Remarks" min-width="180" show-overflow-tooltip />
              <el-table-column align="center" label="Actions" width="120">
                <template slot-scope="scope">
                  <el-button
                    v-role="['doctor', 'admin']" type="primary" size="mini" icon="el-icon-edit"
                    @click="editMed(scope.row)"
                  />
                  <el-button
                    v-if="isEditMode && editingMedId === scope.row.id" v-role="['doctor', 'admin']"
                    type="warning" size="mini" icon="el-icon-close" @click="cancelEdit()"
                  />
                  <el-button
                    v-role="['doctor', 'admin']" type="danger" size="mini" icon="el-icon-delete"
                    @click="deleteMed(scope.row.id)"
                  />
                </template>
              </el-table-column>
            </el-table>
          </el-row>
          <el-form label-position="top" style="margin-top: 16px;">
            <el-form-item label="Follow Up Date">
              <date-picker v-model="form.followup" value-type="format" />
            </el-form-item>
          </el-form>
        </el-card>
      </div>

      <!-- Diagnostics Section -->
      <div v-if="tab === 'fifth'" class="mobile-section">
        <h3>Diagnostics</h3>
        <el-card style="max-width: 100%">
          <div class="rx-prescription-groups-bar">
            <div class="rx-prescription-groups-tabs-wrap">
              <el-tabs
                v-model="activeDiagnosticGroupId"
                type="card"
                class="rx-prescription-tabs"
                @tab-click="onDiagnosticGroupTabClick"
              >
                <el-tab-pane
                  v-for="g in diagnostic_groups"
                  :key="g.id"
                  :name="String(g.id)"
                >
                  <span slot="label" class="rx-prescription-tab-label">
                    <span>{{ g.title }}</span>
                    <i
                      v-role="['doctor', 'admin']"
                      class="el-icon-edit rx-prescription-tab-edit"
                      title="Rename diagnostic group"
                      @click.stop="renameDiagnosticGroup(g)"
                    />
                  </span>
                </el-tab-pane>
              </el-tabs>
              <el-button
                v-role="['doctor', 'admin']"
                class="rx-prescription-add-btn"
                type="primary"
                plain
                icon="el-icon-plus"
                size="small"
                :loading="dxGroupActionLoading"
                @click="addDiagnosticGroup"
              >
                Add Diagnostic Group
              </el-button>
              <el-button
                v-if="diagnostic_groups.length > 1"
                v-role="['doctor', 'admin']"
                type="danger"
                plain
                icon="el-icon-delete"
                size="small"
                :loading="dxGroupActionLoading"
                @click="deleteActiveDiagnosticGroup"
              >
                Delete group
              </el-button>
            </div>
          </div>
          <el-radio-group v-model="form.fasting_mode">
            <el-radio label="1">Fasting 8-10 hours </el-radio>
            <el-radio label="2">Fasting 10-12 hours </el-radio>
            <el-radio label="3">Non-fasting</el-radio>
          </el-radio-group>
          <el-checkbox v-model="form.sendXrayToEmail" label="Send X-ray images" size="large" />
          <el-row v-if="activeDiagnosticGroup">
            <el-form label-position="top" class="demo-form-inline">
              <el-form-item label="Request date">
                <el-date-picker
                  v-model="activeDiagnosticGroup.request_date"
                  type="date"
                  value-format="yyyy-MM-dd"
                  format="MM/dd/yyyy"
                  placeholder="Select date"
                  @change="saveActiveDiagnosticGroupMeta"
                />
              </el-form-item>
              <el-form-item label="Remarks">
                <el-input
                  v-model="activeDiagnosticGroup.lab_remarks"
                  type="textarea"
                  @blur="saveActiveDiagnosticGroupMeta"
                />
              </el-form-item>
              <el-form-item label="Findings">
                <el-input
                  v-model="activeDiagnosticGroup.findings"
                  type="textarea"
                  @blur="saveActiveDiagnosticGroupMeta"
                />
              </el-form-item>
              <el-form-item label="Notes">
                <el-input
                  v-model="activeDiagnosticGroup.notes"
                  type="textarea"
                  @blur="saveActiveDiagnosticGroupMeta"
                />
              </el-form-item>
              <el-form-item label="Recommendations">
                <el-input
                  v-model="activeDiagnosticGroup.recommendations"
                  type="textarea"
                  @blur="saveActiveDiagnosticGroupMeta"
                />
              </el-form-item>
            </el-form>
          </el-row>
          <br>
          <el-button type="primary" @click="viewDiagnosticsTbl = true">View Diagnostics</el-button>
          <el-button v-role="['doctor', 'admin']" type="warning" plain @click="openDxTemplateDialog">
            Load template
          </el-button>
          <el-dropdown v-role="['doctor', 'admin']" trigger="click" @command="handlePrintDxCommand">
            <el-button type="success" plain>
              Print Diagnostics
              <i class="el-icon-arrow-down el-icon--right" />
            </el-button>
            <el-dropdown-menu slot="dropdown">
              <el-dropdown-item command="current">Print current group</el-dropdown-item>
              <el-dropdown-item command="all">Print all groups</el-dropdown-item>
            </el-dropdown-menu>
          </el-dropdown>
          <el-button
            v-role="['doctor', 'admin']"
            type="danger"
            plain
            icon="el-icon-delete"
            :disabled="!dxListSelection.length"
            :loading="dxListDeleteLoading"
            @click="deleteSelectedDiagnostics"
          >
            Delete selected
          </el-button>
          <el-row :gutter="20">
            <el-table
              ref="dxTable"
              :key="'dx-table-tab-' + activeDiagnosticGroupId"
              row-key="id"
              :data="activeDiagnosticList"
              style="width: 100%"
              class="compact-table dx-table"
              size="small"
              @selection-change="handleDxListSelectionChange"
            >
              <el-table-column type="selection" width="48" align="center" />
              <el-table-column label="" width="40" align="center" class-name="rx-drag-col">
                <template slot-scope="scope">
                  <span
                    v-role="['doctor', 'admin']"
                    class="rx-drag-handle"
                    title="Drag to reorder"
                  >
                    <i class="el-icon-rank" />
                  </span>
                </template>
              </el-table-column>
              <el-table-column prop="diagnostic" label="Procedure" />
              <el-table-column prop="remarks" label="Remarks" />
              <el-table-column align="center" label="Actions" width="200">
                <template slot-scope="scope">
                  <el-button
                    v-role="['doctor', 'admin']" type="danger" size="small" icon="el-icon-delete"
                    @click="removeProcedure(scope.row.id)"
                  >
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
                  <date-picker v-model="form.medcert_undersigned" value-type="format" />
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="Diagnosis">
                  <p class="medcert-field-hint">Copied from the Diagnosis tab</p>
                  <el-input v-model="form.medcert_diagnosis" type="textarea" rows="8" readonly />
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="Remarks">
                  <div class="pe-template-section">
                    <el-row :gutter="20" style="margin-bottom: 15px;">
                      <el-col :span="24">
                        <div class="template-buttons">
                          <el-button
                            v-for="template in medcertRemarksTemplates" :key="template.id" size="small"
                            :type="template.type === 'default' ? 'primary' : 'success'" plain
                            class="template-btn" @click="insertMedcertRemarksTemplate(template.content)"
                          >
                            {{ template.name }}
                            <el-button
                              v-if="template.type === 'custom'" size="mini" type="danger" icon="el-icon-delete"
                              circle class="delete-template-btn" @click.stop="deleteMedcertRemarksTemplate(template.id)"
                            />
                          </el-button>
                        </div>
                        <el-button
                          size="small" type="success" style="margin-left: 10px;"
                          @click="showMedcertRemarksCustomTemplateDialog = true"
                        >
                          Custom Template
                        </el-button>
                      </el-col>
                    </el-row>
                    <el-input v-model="form.medcert_remarks" type="textarea" rows="8" />
                  </div>
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
                  <date-picker v-model="form.referral_undersigned" value-type="format" />
                </el-form-item>
              </el-col>
            </el-row>
            <el-row :gutter="20">
              <el-col :span="24">
                <el-form-item label="History">
                  <el-input v-model="form.history" type="textarea" rows="5" />
                </el-form-item>
              </el-col>
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
              <el-button
                ref="uploadRef" size="small" type="info" action="#" :auto-upload="false" multiple
                :on-change="handleChange" :disabled="isUploading"
              >Select attachments</el-button>
            </template>
            <el-button size="small" type="primary" :loading="isUploading" :disabled="isUploading" @click="submitUpload">
              {{ isUploading ? 'Uploading...' : 'Submit' }}
            </el-button>
          </el-upload>

          <!-- Upload Progress -->
          <div v-if="isUploading" class="mt-3">
            <el-progress :percentage="uploadProgress" :status="uploadProgress === 100 ? 'success' : ''" />
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

          <div v-for="group in attachmentGroups" :key="group.key" style="margin-bottom: 18px;">
            <div style="font-weight: 600; margin: 6px 0 10px; color: #303133;">
              {{ group.label }}
            </div>
            <div class="att-gallery">
              <div v-for="item in group.items" :key="item.id || item.newfile || item.fname" class="att-tile">
                <div class="att-tile__media">
                  <el-image
                    v-if="item.isImage"
                    class="att-tile__image"
                    :src="item.src"
                    fit="cover"
                    :preview-src-list="group.previewList"
                    :initial-index="item.previewIndex"
                  />
                  <div v-else class="att-tile__file" @click="viewFile(item.raw.newfile, item.raw.extension)">
                    <i class="el-icon-document" style="font-size: 24px;" />
                    <div class="att-tile__fileext">{{ (item.raw.extension || '').toUpperCase() }}</div>
                  </div>
                  <el-button
                    class="att-tile__delete"
                    type="danger"
                    icon="el-icon-delete"
                    circle
                    size="mini"
                    @click.stop="deleteAtt(item.raw.id)"
                  />
                </div>
                <div class="att-tile__meta">
                  <div class="att-tile__name" :title="item.raw.description || item.raw.fname">
                    {{ item.raw.description || item.raw.fname }}
                  </div>
                  <div class="att-tile__date">
                    {{ item.raw.created_dt }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <el-dialog :visible.sync="viewFileModel" :fullscreen="false" :close-on-click-modal="false">
            <template #default>
              <div class="iframe-wrapper">
                <iframe
                  v-if="isPdf" :src="sourceFile" :style="transformStyle" frameborder="0"
                  class="iframe-full"
                />
                <el-image
                  v-if="!isPdf" style="width: 100px; height: 100px" :src="sourceFile" :zoom-rate="1.2"
                  :max-scale="7" :min-scale="0.2" :preview-src-list="[sourceFile]" show-progress :initial-index="4"
                  fit="cover"
                />
              </div>
            </template>
          </el-dialog>
        </el-card>
      </div>
    </div>
  </div>
</template>
<script>
import role from '@/directive/role/index.js';
import Patients from '@/api/patients';
import Medicine from '@/api/medicine';
import { listPrescriptionDiagnosisTemplates, getPrescriptionDiagnosisTemplate } from '@/api/prescriptionDiagnosisTemplate';
import { listDiagnosticTemplates, getDiagnosticTemplate } from '@/api/diagnosticTemplate';
import { listFormTemplates, getFormTemplate, getFormTemplateCategories } from '@/api/formTemplate';
import { replaceFormTemplatePlaceholders, buildFormTemplateContext } from '@/utils/formTemplatePlaceholders';
import {
  appointmentMealTimingStringsFromFrequency,
  isPositiveMealDoseString,
} from '@/utils/medicationTemplateTiming';
import { listFavoriteMedicines, createFavoriteMedicine, updateFavoriteMedicine, deleteFavoriteMedicine } from '@/api/favoriteMedicine';
import Procedure from '@/api/procedure';
import Services from '@/api/services';
import Diagnostics from '@/api/diagnostics';
import { getPeTemplates, createPeTemplate, deletePeTemplate } from '@/api/peTemplates';
import {
  getDiagnosisTemplates,
  createDiagnosisTemplate,
  deleteDiagnosisTemplate as deleteDiagnosisTemplateApi,
} from '@/api/diagnosisTemplates';
import {
  getPlansTemplates,
  createPlansTemplate,
  deletePlansTemplate as deletePlansTemplateApi,
} from '@/api/plansTemplates';
import {
  getMedcertRemarksTemplates,
  createMedcertRemarksTemplate,
  deleteMedcertRemarksTemplate as deleteMedcertRemarksTemplateApi,
} from '@/api/medcertRemarksTemplates';
import moment from 'moment-timezone';
import debounce from 'lodash/debounce';
import checkRole from '@/utils/role'; // Role checking
import DatePicker from 'vue2-datepicker';
import heic2any from 'heic2any';
import QuillEditor from '@/components/QuillEditor';
import Sortable from 'sortablejs';

const FORM_EDITOR_FONT_SIZES = [
  '8px', '9px', '10px', '11px', '12px', '14px', '16px', '18px', '20px', '24px',
  '28px', '32px', '36px', '40px', '48px', '60px', '72px',
];

export default {
  components: { DatePicker, QuillEditor },
  directives: { role },
  data() {
    return {
      servicesDialogFormVisible: false,
      isPdf: false,
      rotation: 0,
      popconfirmUpddateDiagnosis: false,
      _lastFormChangeAt: null,
      _lastSaveCompletedAt: null,
      _saveInFlight: false,
      _hydratingForm: true,
      viewFileModel: false,
      sourceFile: null,
      pageloading: true,
      appointment_dt: '',
      vitalsDiaglog: false,
      vitals_records: [],
      vitals_today: [],
      vitals_by_day: {},
      showTodayVitalsMore: false,
      showVitalsTabMore: false,
      sharePdfDialogVisible: false,
      sharePdfDoc: 'rx',
      sharePdfLink: '',
      selectedOldRecords: {},
      oldRecordsdialogVisible: false,
      historyDiaglog: false,
      viewServicesTbl: false,
      viewDiagnosticsTbl: false,
      diagnosticsFilterQuery: '',
      rxTemplateDialogVisible: false,
      rxTemplateLoading: false,
      rxTemplateApplyLoading: false,
      rxTemplateList: [],
      rxTemplateSelectId: null,
      dxTemplateDialogVisible: false,
      dxTemplateLoading: false,
      dxTemplateApplyLoading: false,
      dxTemplateList: [],
      dxTemplateSelectId: null,
      formTemplateDialogVisible: false,
      formTemplateLoading: false,
      formTemplateApplyLoading: false,
      formTemplateList: [],
      formTemplateCategoryOptions: [],
      formTemplateFilterCategory: '',
      formTemplateSearchKeyword: '',
      formTemplateSelectId: null,
      formTemplateRecent: [],
      formEditorFontSizes: FORM_EDITOR_FONT_SIZES,
      rxPastDialogVisible: false,
      rxPastLoading: false,
      rxPastUseLoading: false,
      rxPastRows: [],
      rxPastSearch: '',
      rxFavoriteMedicines: [],
      rxFavoritesDialogVisible: false,
      rxFavoritesApplyLoading: false,
      rxFavoritesDialogSelection: [],
      rxListSelection: [],
      rxListDeleteLoading: false,
      rxListRefreshLoading: false,
      rxListReorderLoading: false,
      rxMedSortables: [],
      dxListSelection: [],
      dxListDeleteLoading: false,
      dxListReorderLoading: false,
      dxTableSortables: [],
      rxFavoriteEditDialogVisible: false,
      rxOrderDialogVisible: false,
      rxFavoriteEditSaving: false,
      rxFavoriteEditForm: {
        id: null,
        drug_name: '',
        default_qty: '',
        default_remarks: '',
      },
      dialogVisible: false,
      isMobile: false,
      toolbarPinned: false,
      pinnedToolbarLeft: 0,
      pinnedToolbarWidth: 0,
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
      tab: 'first',
      rx_list: [],
      prescription_groups: [],
      activePrescriptionGroupId: null,
      rxGroupActionLoading: false,
      diagnostic_groups: [],
      activeDiagnosticGroupId: null,
      dxGroupActionLoading: false,
      diagnostic_list: [],
      services_list: [],
      compareDrawerVisible: false,
      comparePastVisits: [],
      compareListLoading: false,
      compareSelectedId: null,
      compareLoading: false,
      compareData: {
        form: {},
        rx_list: [],
        prescription_groups: [],
        diagnostic_list: [],
        diagnostic_groups: [],
        services_list: [],
      },
      form: {
        lab_others: null,
        smoking_details: '',
        alcohol_details: '',
        fasting_mode: '',
        sendXrayToEmail: false,
        email: '',
        prev_admission: '',
        prev_surgeries: '',
        allergies: '',
        asthma: '',
        hypertension: '',
        tb: '',
        seizure: '',
        diabetes: '',
        copd: '',
        pmh_others: '',
        clearance_remarks: '',
        history: '',
        pmr: '',
        pe: '',
        diagnosis: '',
        nurse_remarks: '',
        form_content: '',
        plan: '',
        height: null,
        bmi: null,
        discount: 0,
        medcert_diagnosis: '',
        medcert_remarks: '',
        medcert_undersigned: null,
        diagnostics_remarks: '',
        lab_remarks: '',
        ancillary_remarks: '',
        followup: null,
        medcert_opt1: false,
        medcert_opt2: false,
        medcert_opt3: false,
        medcert_opt4: false,
        medcert_opt1_text1: '',
        medcert_opt4_text1: '',
        medcert_opt4_text2: '',
        medcert_opt4_text3: '',
        remarks: '',
        medsArr: [
          {
            qty: '',
            bf_b: '',
            bf_a: '',
            l_a: '',
            l_b: '',
            s_b: '',
            s_a: '',
            bt: '',
            meds: '',
            id: '',
            remarks: '',
          },
        ],
        procedures: [
          {
            procedure: '',
            id: 0,
            remarks: '',
            type: 0,
          },
        ],
        services: [
          {
            service: '',
            id: 0,
            fee: 0,
            discount: 0,
          },
        ],
        id: this.$route.params.id,
        cc: '',
        obmens: '',
        ob_g: '',
        ob_p: '',
        ob_tpal: '',
        ob_remarks: '',
        mens_m: '',
        mens_i: '',
        mens_d: '',
        mens_a: '',
        mens_s: '',
        mens_menu: '',
        hpi: '',
        sig_labs: '',
        pmhx: '',
        recommendations: '',
        findings: '',
        vit_sys: '',
        vit_dia: '',
        weight: null,
        vit_temp: '',
        vit_cr: '',
        vit_rr: '',
        pe_head: '',
        pe_ear: '',
        pe_eyes: '',
        pe_nose: '',
        pe_throat: '',
        pe_breast: '',
        pe_chest: '',
        pe_heart: '',
        pe_abdomen: '',
        pe_genito: '',
        pe_extremities: '',
        pe_review: '',
        pe_pq1: '',
        pe_pq2: '',
        pe_pq3: '',
        pe_pq4: '',
        pe_pq5: '',
        pe_pq6: '',
        pe_pq7: '',
        pe_pq8: '',
        pe_pq9: '',
        pe_ext: '',
        pe_cer: '',
        pe_uterus: '',
        pe_adnexa: '',
        pe_dish: '',
        withs2: false,
        pregnancy: '',
        lmp: '',
        contraceptive_use: '',
        menopause: '',
        mother_details: '',
        father_details: '',
      },
      medsArr: {
        custom_meds: false,
        qty: '',
        bf_b: '',
        bf_a: '',
        l_a: '',
        l_b: '',
        s_b: '',
        s_a: '',
        bt: '',
        meds: '',
        med_id: 0,
        id: this.$route.params.id,
        remarks: '',
        custom_generic: '',
        custom_brand: '',
        custom_dosage: '',
      },
      isEditMode: false,
      editingMedId: null,
      procedure: {
        procedure: '',
        procedure_id: 0,
        id: this.$route.params.id,
        remarks: '',
        type: 0,
      },
      service: {
        service: '',
        // service_selected: [],
        id: this.$route.params.id,
        fee: 0,
        service_id: 0,
        // discount: 0,
      },
      form_edit_services: {
        id: null,
        service: null,
        amount: 0,
      },
      form_att: {
        patientid: '',
        file: '',
      },
      form_cancel: {
        id: this.$route.params.id,
        cancel_reason: '',
      },
      selectedImage: {},
      attachments: [],
      patientid_id: 0,
      rules: {
        cancel_reason: [
          { required: true, message: 'Please provide reason', trigger: 'blur' },
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
      lab_micro_remarks: '',
      xray_remarks: '',
      synovialFluidExtraOptions: [],
      synovialFluidOptions: [
        { label: 'cell count and differential count', procedureName: 'Cell Count and Differential Count' },
        { label: 'gram stain', procedureName: 'Gram Stain ' },
        { label: 'culture and sensitivity', procedureName: 'Culture and Sensitivity ' },
        { label: 'crystal analysis', procedureName: 'Crystal Analysis' },
      ],

      // Physical Examination Templates
      showCustomTemplateDialog: false,
      peTemplates: [],
      customTemplateForm: {
        name: '',
        content: '',
      },

      // Diagnosis Templates
      showDiagnosisCustomTemplateDialog: false,
      diagnosisTemplates: [],
      diagnosisCustomTemplateForm: {
        name: '',
        content: '',
      },

      // Plans Templates
      showPlansCustomTemplateDialog: false,
      plansTemplates: [],
      plansCustomTemplateForm: {
        name: '',
        content: '',
      },

      // Med Cert Remarks Templates
      showMedcertRemarksCustomTemplateDialog: false,
      medcertRemarksTemplates: [],
      medcertRemarksCustomTemplateForm: {
        name: '',
        content: '',
      },
    };
  },
  computed: {
    pinnedToolbarStyle() {
      if (!this.toolbarPinned) {
        return {};
      }
      return {
        left: `${this.pinnedToolbarLeft}px`,
        width: `${this.pinnedToolbarWidth}px`,
      };
    },
    hasPatientProfileLink() {
      return !!(this.profile && this.profile.id && this.patientid_id);
    },
    patientProfilePath() {
      if (!this.hasPatientProfileLink) {
        return '';
      }
      return `/masterfile/profile/${this.profile.id}/${this.patientid_id}`;
    },
    currentVisitSnapshot() {
      return {
        form: this.form,
        rx_list: this.rx_list,
        prescription_groups: this.prescription_groups,
        diagnostic_list: this.diagnostic_list,
        diagnostic_groups: this.diagnostic_groups,
        services_list: this.services_list,
      };
    },
    compareColumns() {
      return [
        {
          key: 'current',
          title: 'Current visit',
          subtitle: this.appointment_dt || '',
          snapshot: this.currentVisitSnapshot,
          loading: false,
        },
        {
          key: 'previous',
          title: 'Previous visit',
          subtitle: this.compareSelectedVisitLabel,
          snapshot: this.compareData,
          loading: this.compareLoading,
        },
      ];
    },
    compareSelectedVisitLabel() {
      const match = (this.comparePastVisits || []).find(
        (v) => String(v.id) === String(this.compareSelectedId)
      );
      if (!match) {
        return '';
      }
      return match.cf ? `${match.date} - ${match.cf}` : match.date;
    },
    activeRxList() {
      const gid = this.activePrescriptionGroupId
        ? Number(this.activePrescriptionGroupId)
        : null;
      if (!gid) {
        return this.rx_list || [];
      }
      return (this.rx_list || []).filter(
        (r) => Number(r.prescription_group_id) === gid
      );
    },
    activePrescriptionGroup() {
      const gid = this.activePrescriptionGroupId
        ? Number(this.activePrescriptionGroupId)
        : null;
      return (this.prescription_groups || []).find((g) => g.id === gid) || null;
    },
    activeDiagnosticList() {
      const gid = this.activeDiagnosticGroupId
        ? Number(this.activeDiagnosticGroupId)
        : null;
      if (!gid) {
        return this.diagnostic_list || [];
      }
      return (this.diagnostic_list || []).filter(
        (r) => Number(r.diagnostic_group_id) === gid
      );
    },
    activeDiagnosticGroup() {
      const gid = this.activeDiagnosticGroupId
        ? Number(this.activeDiagnosticGroupId)
        : null;
      return (this.diagnostic_groups || []).find((g) => g.id === gid) || null;
    },
    transformStyle() {
      return {
        transform: `rotate(${this.rotation}deg)`,
        transformOrigin: 'center center',
        display: 'inline-block',
      };
    },
    isSynovialFluidSelected() {
      // Check if "synovial fluid" is selected (case-insensitive)
      return this.diagnosticsRenderedModel.some(item =>
        item && item.toLowerCase().includes('synovial fluid')
      );
    },
    hasFilteredDiagnostics() {
      const lists = [
        this.getAllDiagnosticsOfferedChem,
        this.getAllDiagnosticsOfferedHema,
        this.getAllDiagnosticsOfferedMicroscopy,
        this.getAllDiagnosticsOfferedXray,
        this.getAllDiagnosticsOfferedUtz,
        this.getAllDiagnosticsOfferedImmonulogy,
        this.getAllDiagnosticsOfferedMirco,
        this.getAllDiagnosticsOfferedCt,
        this.getAllDiagnosticsOfferedMri,
        this.getAllDiagnosticsOfferedOth,
        this.getAllDiagnosticsOfferedCrystal,
      ];
      return lists.some((list) => this.filterDiagnosticsList(list).length > 0);
    },
    availableTabs() {
      const tabs = [
        {
          name: 'history',
          label: 'Histories',
          available: this.checkRole(['admin', 'doctor']),
          hasContent: this.hasHistoryContent(),
        },
        {
          name: 'family',
          label: 'Family History',
          available: false,
          hasContent: this.hasFamilyContent(),
        },
        {
          name: 'soc',
          label: 'Social/Environment History',
          available: false,
          hasContent: this.hasSocialContent(),
        },
        {
          name: 'first',
          label: 'Diagnosis',
          available: true,
          hasContent: this.hasDiagnosisContent(),
        },
        {
          name: 'second',
          label: 'Vitals',
          available: true,
          hasContent: this.hasVitalsContent(),
        },
        {
          name: 'fourth',
          label: 'Medicines',
          available: this.checkRole(['admin', 'doctor']),
          hasContent: this.rx_list && this.rx_list.length > 0,
        },
        {
          name: 'fifth',
          label: 'Diagnostics',
          available: this.checkRole(['admin', 'doctor']),
          hasContent: this.diagnostic_list && this.diagnostic_list.length > 0 ||
            (this.diagnostic_groups || []).some((g) =>
              (g.lab_remarks || g.findings || g.notes || g.recommendations)
            ),
        },
        {
          name: 'sixth',
          label: 'Services & Billing',
          available: true,
          hasContent: this.services_list && this.services_list.length > 0,
        },
        {
          name: 'medcert',
          label: 'Medical Certificate',
          available: this.checkRole(['admin', 'doctor']),
          hasContent: this.hasMedCertContent(),
        },
        {
          name: 'referral',
          label: 'Referral',
          available: this.checkRole(['admin', 'doctor']),
          hasContent: this.hasReferralContent(),
        },
        {
          name: 'obgyn',
          label: 'Obstetric and Gynecologic History',
          available: this.checkRole(['admin', 'doctor']),
          hasContent: this.hasObgynContent(),
        },
        {
          name: 'attachments',
          label: 'Attachments',
          available: true,
          hasContent: this.attachments && this.attachments.length > 0,
        },
        {
          name: 'form',
          label: 'Form',
          available: true,
          hasContent: !!this.form.form_content,
        },
      ];
      return tabs.filter(tab => tab.available);
    },
    attachmentGroups() {
      const input = Array.isArray(this.attachments) ? this.attachments : [];
      const imageExts = new Set(['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'heic']);

      const parsed = input.map((att) => {
        const rawDate = att && (att.created_dt || att.created_at || att.date);
        const m = rawDate ? moment(rawDate) : null;
        const isValid = !!(m && m.isValid());
        const key = isValid ? m.format('YYYY-MM') : 'unknown';
        const label = isValid ? m.format('MMMM YYYY') : 'Unknown date';
        const ext = (att && att.extension ? String(att.extension) : '').toLowerCase();
        const isImage = imageExts.has(ext);
        return { att, key, label, sortTs: isValid ? m.valueOf() : -Infinity, isImage };
      });

      parsed.sort((a, b) => {
        if (a.key === 'unknown' && b.key !== 'unknown') {
          return 1;
        }
        if (b.key === 'unknown' && a.key !== 'unknown') {
          return -1;
        }
        return b.sortTs - a.sortTs;
      });

      const groupsByKey = new Map();
      for (const row of parsed) {
        if (!groupsByKey.has(row.key)) {
          groupsByKey.set(row.key, { key: row.key, label: row.label, _rows: [] });
        }
        groupsByKey.get(row.key)._rows.push(row);
      }

      const groups = Array.from(groupsByKey.values());
      groups.sort((a, b) => {
        if (a.key === 'unknown' && b.key !== 'unknown') {
          return 1;
        }
        if (b.key === 'unknown' && a.key !== 'unknown') {
          return -1;
        }
        return a.key < b.key ? 1 : a.key > b.key ? -1 : 0;
      });

      return groups.map((g) => {
        const previewList = g._rows.filter(r => r.isImage).map(r => r.att.newfile);
        let imgIdx = 0;
        const items = g._rows.map((r) => {
          const previewIndex = r.isImage ? imgIdx++ : -1;
          return {
            raw: r.att,
            isImage: r.isImage,
            src: r.att.newfile,
            previewIndex,
          };
        });
        return { key: g.key, label: g.label, previewList, items };
      });
    },
    rxPastFilteredRows() {
      const q = (this.rxPastSearch || '').trim().toLowerCase();
      const rows = Array.isArray(this.rxPastRows) ? this.rxPastRows : [];
      if (!q) {
        return rows;
      }
      return rows.filter((row) => {
        const dateStr = this.formatPastRxDate(row.appointment_dt).toLowerCase();
        const dx = (row.diagnosis || '').toLowerCase();
        const meds = (row.medications || [])
          .map((m) => this.medicationLineLabel(m).toLowerCase())
          .join(' ');
        return dateStr.includes(q) || dx.includes(q) || meds.includes(q);
      });
    },
    latestTodayVitals() {
      if (this.vitals_today && this.vitals_today.length > 0) {
        return this.vitals_today[0];
      }
      return {
        vit_sys: this.form.vit_sys,
        vit_dia: this.form.vit_dia,
        weight: this.form.weight,
        height: this.form.height,
        bmi: this.form.bmi,
        vit_temp: this.form.vit_temp,
        vit_cr: this.form.vit_cr,
        vit_rr: this.form.vit_rr,
        o2_stat: this.form.o2_stat,
        time_display: '',
      };
    },
    otherTodayVitals() {
      return (this.vitals_today || []).slice(1);
    },
    hasMoreTodayVitals() {
      return this.otherTodayVitals.length > 0;
    },
    hasCurrentVisitVitals() {
      return this.vitalReadingHasValues(this.latestTodayVitals);
    },
  },
  watch: {
    form: [
      {
        handler() {
          if (this._hydratingForm) {
            return;
          }
          this._lastFormChangeAt = Date.now();
        },
        deep: true,
      },
      {
        handler() {
          if (this._hydratingForm) {
            return;
          }
          this._debouncedAutoSave();
        },
        deep: true,
      },
    ],
    'form.weight': 'calculateBMI',
    'form.height': 'calculateBMI',
    'form.diagnosis'(val) {
      if (this._hydratingForm) {
        return;
      }
      this.form.medcert_diagnosis = val || '';
    },
    tab(val) {
      if (val === 'fourth') {
        this.initRxMedTableSortable();
      } else {
        this.destroyRxMedSortables();
      }
      if (val === 'fifth') {
        this.initDxTableSortable();
      } else {
        this.destroyDxTableSortables();
      }
    },
    activePrescriptionGroupId() {
      this.clearRxMedTableSelection();
      this.$nextTick(() => {
        this.initRxMedTableSortable();
      });
    },
    activeDiagnosticGroupId() {
      this.clearDxListSelection();
      this.$nextTick(() => {
        this.initDxTableSortable();
      });
    },
    isSynovialFluidSelected(newVal) {
      // Clear extra options when synovial fluid is deselected
      if (!newVal) {
        // Remove all synovial fluid extra options from diagnosticsRenderedModel
        this.synovialFluidOptions.forEach(option => {
          this.diagnosticsRenderedModel = this.diagnosticsRenderedModel.filter(
            item => item !== option.procedureName
          );
          // Remove from diagnosticsRendered.rendered
          this.diagnosticsRendered.rendered = this.diagnosticsRendered.rendered.filter(
            p => p.procedure !== option.procedureName
          );
        });
        // Clear the synovialFluidExtraOptions array
        this.synovialFluidExtraOptions = [];
      }
    },
  },
  mounted() {
    this.checkIfMobile();
    window.addEventListener('resize', this.checkIfMobile);
    this.initActionToolbarPin();
  },
  beforeDestroy() {
    window.removeEventListener('resize', this.checkIfMobile);
    this.destroyActionToolbarPin();
    this.destroyRxMedSortables();
    this.destroyDxTableSortables();
  },
  created() {
    this._hydratingForm = true;
    this._debouncedAutoSave = debounce(() => {
      this.onSubmit('autosave');
    }, 1000);
    this.getAllDiagnostics();
    this.appointments();
    this.getmeds();
    this.getdiagnostics();
    this.getservices();
    this.getAllServices();
    this.loadTemplatesFromDatabase();
    this.loadRxFavoriteMedicines();
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
          content: this.customTemplateForm.content,
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
        const [peResponse, diagnosisResponse, plansResponse, medcertRemarksResponse] = await Promise.all([
          getPeTemplates(),
          getDiagnosisTemplates(),
          getPlansTemplates(),
          getMedcertRemarksTemplates(),
        ]);

        if (peResponse.success) {
          this.peTemplates = peResponse.data;
        } else {
          console.error('Failed to load P.E. templates:', peResponse.message);
          this.peTemplates = [];
        }

        if (diagnosisResponse.success) {
          this.diagnosisTemplates = diagnosisResponse.data;
        } else {
          console.error('Failed to load diagnosis templates:', diagnosisResponse.message);
          this.diagnosisTemplates = [];
        }

        if (plansResponse.success) {
          this.plansTemplates = plansResponse.data;
        } else {
          console.error('Failed to load plans templates:', plansResponse.message);
          this.plansTemplates = [];
        }

        if (medcertRemarksResponse.success) {
          this.medcertRemarksTemplates = medcertRemarksResponse.data;
        } else {
          console.error('Failed to load med cert remarks templates:', medcertRemarksResponse.message);
          this.medcertRemarksTemplates = [];
        }
      } catch (error) {
        console.error('Failed to load templates from database:', error);
        this.peTemplates = [];
        this.diagnosisTemplates = [];
        this.plansTemplates = [];
        this.medcertRemarksTemplates = [];
      }
    },

    insertDiagnosisTemplate(content) {
      if (this.form.diagnosis) {
        this.form.diagnosis += '\n\n' + content;
      } else {
        this.form.diagnosis = content;
      }
      this.$nextTick(() => {
        this.autoResize();
      });
    },

    async saveDiagnosisCustomTemplate() {
      if (!this.diagnosisCustomTemplateForm.name || !this.diagnosisCustomTemplateForm.content) {
        this.$message.error('Please fill in both template name and content');
        return;
      }

      const existingTemplate = this.diagnosisTemplates.find(t => t.name === this.diagnosisCustomTemplateForm.name);
      if (existingTemplate) {
        this.$message.error('A template with this name already exists');
        return;
      }

      try {
        const response = await createDiagnosisTemplate({
          name: this.diagnosisCustomTemplateForm.name,
          content: this.diagnosisCustomTemplateForm.content,
        });

        if (response.success) {
          await this.loadTemplatesFromDatabase();
          this.diagnosisCustomTemplateForm.name = '';
          this.diagnosisCustomTemplateForm.content = '';
          this.showDiagnosisCustomTemplateDialog = false;
          this.$message.success('Custom template saved successfully!');
        } else {
          this.$message.error(response.message || 'Failed to save template');
        }
      } catch (error) {
        console.error('Error saving diagnosis template:', error);
        this.$message.error('Failed to save template. Please try again.');
      }
    },

    insertPlansTemplate(content) {
      if (this.form.remarks) {
        this.form.remarks += '\n\n' + content;
      } else {
        this.form.remarks = content;
      }
      this.$nextTick(() => {
        this.autoResize();
      });
    },

    async savePlansCustomTemplate() {
      if (!this.plansCustomTemplateForm.name || !this.plansCustomTemplateForm.content) {
        this.$message.error('Please fill in both template name and content');
        return;
      }

      const existingTemplate = this.plansTemplates.find(t => t.name === this.plansCustomTemplateForm.name);
      if (existingTemplate) {
        this.$message.error('A template with this name already exists');
        return;
      }

      try {
        const response = await createPlansTemplate({
          name: this.plansCustomTemplateForm.name,
          content: this.plansCustomTemplateForm.content,
        });

        if (response.success) {
          await this.loadTemplatesFromDatabase();
          this.plansCustomTemplateForm.name = '';
          this.plansCustomTemplateForm.content = '';
          this.showPlansCustomTemplateDialog = false;
          this.$message.success('Custom template saved successfully!');
        } else {
          this.$message.error(response.message || 'Failed to save template');
        }
      } catch (error) {
        console.error('Error saving plans template:', error);
        this.$message.error('Failed to save template. Please try again.');
      }
    },

    async deleteDiagnosisTemplate(templateId) {
      this.$confirm('Are you sure you want to delete this template?', 'Confirm Delete', {
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(async() => {
        try {
          const response = await deleteDiagnosisTemplateApi(templateId);

          if (response.success) {
            await this.loadTemplatesFromDatabase();
            this.$message.success('Template deleted successfully');
          } else {
            this.$message.error(response.message || 'Failed to delete template');
          }
        } catch (error) {
          console.error('Error deleting diagnosis template:', error);
          this.$message.error('Failed to delete template. Please try again.');
        }
      }).catch(() => {});
    },

    async deletePlansTemplate(templateId) {
      this.$confirm('Are you sure you want to delete this template?', 'Confirm Delete', {
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(async() => {
        try {
          const response = await deletePlansTemplateApi(templateId);

          if (response.success) {
            await this.loadTemplatesFromDatabase();
            this.$message.success('Template deleted successfully');
          } else {
            this.$message.error(response.message || 'Failed to delete template');
          }
        } catch (error) {
          console.error('Error deleting plans template:', error);
          this.$message.error('Failed to delete template. Please try again.');
        }
      }).catch(() => {});
    },

    insertMedcertRemarksTemplate(content) {
      if (this.form.medcert_remarks) {
        this.form.medcert_remarks += '\n\n' + content;
      } else {
        this.form.medcert_remarks = content;
      }
    },

    async saveMedcertRemarksCustomTemplate() {
      if (!this.medcertRemarksCustomTemplateForm.name || !this.medcertRemarksCustomTemplateForm.content) {
        this.$message.error('Please fill in both template name and content');
        return;
      }

      const existingTemplate = this.medcertRemarksTemplates.find(
        t => t.name === this.medcertRemarksCustomTemplateForm.name
      );
      if (existingTemplate) {
        this.$message.error('A template with this name already exists');
        return;
      }

      try {
        const response = await createMedcertRemarksTemplate({
          name: this.medcertRemarksCustomTemplateForm.name,
          content: this.medcertRemarksCustomTemplateForm.content,
        });

        if (response.success) {
          await this.loadTemplatesFromDatabase();
          this.medcertRemarksCustomTemplateForm.name = '';
          this.medcertRemarksCustomTemplateForm.content = '';
          this.showMedcertRemarksCustomTemplateDialog = false;
          this.$message.success('Custom template saved successfully!');
        } else {
          this.$message.error(response.message || 'Failed to save template');
        }
      } catch (error) {
        console.error('Error saving med cert remarks template:', error);
        this.$message.error('Failed to save template. Please try again.');
      }
    },

    async deleteMedcertRemarksTemplate(templateId) {
      this.$confirm('Are you sure you want to delete this template?', 'Confirm Delete', {
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(async() => {
        try {
          const response = await deleteMedcertRemarksTemplateApi(templateId);

          if (response.success) {
            await this.loadTemplatesFromDatabase();
            this.$message.success('Template deleted successfully');
          } else {
            this.$message.error(response.message || 'Failed to delete template');
          }
        } catch (error) {
          console.error('Error deleting med cert remarks template:', error);
          this.$message.error('Failed to delete template. Please try again.');
        }
      }).catch(() => {});
    },

    async deleteTemplate(templateId) {
      this.$confirm('Are you sure you want to delete this template?', 'Confirm Delete', {
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(async() => {
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
      if (command === 'update_diagnosis') {
        this.popconfirmUpddateDiagnosis = true;
        this.$nextTick(() => {
          this.$refs.updateDiagnosisBtn.$el.click();
        });
      }
      if (command === 'share_pdf') {
        this.openSharePdfDialog();
      }
      if (command === 'print_rx_current') {
        this.printCurrentRxGroup();
      }
      if (command === 'print_rx_all') {
        this.printAllRxGroups();
      }
      if (command === 'email_rx') {
        this.emailPrescription();
      }
      if (command === 'print_dx_current') {
        this.printCurrentDxGroup();
      }
      if (command === 'print_dx_all') {
        this.printAllDxGroups();
      }
      if (command === 'print_labs') {
        this.printCurrentDxGroup();
      }
      if (command === 'print_ancillary') {
        this.printrequest(2);
      }
      if (command === 'print_medcert') {
        this.printmedcert();
      }
      if (command === 'print_fees') {
        this.printfees();
      }
      if (command === 'print_riskstrat') {
        this.printriskstrat();
      }
      if (command === 'print_fit') {
        this.printfittowork();
      }
      if (command === 'print_clearance') {
        this.printclearance();
      }
      if (command === 'print_referral') {
        this.printreferral();
      }
      if (command === 'print_form') {
        this.printform();
      }
      if (command === 'cancel_apt') {
        this.cancelAppointment();
      }
      if (command === 'view_chart') {
        this.printChart();
      }
      if (command === 'load_form_template') {
        this.openFormTemplateDialog();
      }
      if (command === 'done_consult') {
        this.doneConsult();
      }
    },
    openSharePdfDialog() {
      this.sharePdfDialogVisible = true;
      this.sharePdfDoc = this.sharePdfDoc || 'rx';
      this.updateSharePdfLink();
    },
    buildPublicPdfUrl(doc) {
      const id = this.form && this.form.id ? this.form.id : this.$route.params.id;
      const origin = window.location.origin || '';
      const map = {
        rx: `/api/printpdf2/${id}`,
        diagnostics: `/api/printrequest/${id}/1`,
        referral: `/api/printreferral/${id}`,
        form: `/api/printform/${id}`,
        medcert: `/api/printmedcert/${id}`,
      };
      const path = map[doc] || map.rx;
      return `${origin}${path}`;
    },
    async updateSharePdfLink() {
      const id = this.form && this.form.id ? this.form.id : this.$route.params.id;
      const doc = this.sharePdfDoc || 'rx';
      const type = doc === 'diagnostics' ? 1 : undefined;

      try {
        const res = await Patients.getPublicPdfLink(id, doc, type);
        this.sharePdfLink = res && res.url ? res.url : '';
        if (!this.sharePdfLink) {
          throw new Error('No URL returned');
        }
      } catch (e) {
        // Do not fall back to internal /api/print* links because those require login.
        this.sharePdfLink = '';
        this.$message.error('Could not generate a no-login share link. Please refresh and try again.');
      }
    },
    async copySharePdfLink() {
      try {
        const text = this.sharePdfLink || '';
        if (navigator.clipboard && navigator.clipboard.writeText) {
          await navigator.clipboard.writeText(text);
          this.$message.success('Link copied.');
          return;
        }
        // Fallback for older browsers
        const el = document.createElement('textarea');
        el.value = text;
        el.setAttribute('readonly', '');
        el.style.position = 'absolute';
        el.style.left = '-9999px';
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        this.$message.success('Link copied.');
      } catch (e) {
        this.$message.error('Could not copy link.');
      }
    },
    sharePdfToWhatsApp() {
      const url = this.sharePdfLink || '';
      if (!url) {
        this.$message.error('No public share link yet. Please wait for the link to load, then try again.');
        return;
      }
      const msg = `PDF: ${url}`;
      window.open(`https://wa.me/?text=${encodeURIComponent(msg)}`, '_blank');
    },
    sharePdfToViber() {
      const url = this.sharePdfLink || '';
      if (!url) {
        this.$message.error('No public share link yet. Please wait for the link to load, then try again.');
        return;
      }
      const msg = `PDF: ${url}`;
      // Works best on mobile/desktop Viber installed; otherwise no-op.
      window.open(`viber://forward?text=${encodeURIComponent(msg)}`, '_blank');
    },
    sharePdfToMessenger() {
      const url = this.sharePdfLink || '';
      if (!url) {
        this.$message.error('No public share link yet. Please wait for the link to load, then try again.');
        return;
      }
      const fbAppId =
        (typeof process !== 'undefined' &&
          process.env &&
          (process.env.MIX_FACEBOOK_APP_ID || process.env.MIX_FB_APP_ID)) ||
        '';

      // Best UX on web: Messenger "Send Dialog" (requires a Facebook App ID).
      if (fbAppId) {
        const redirectUri = window.location.href;
        const sendDialogUrl =
          `https://www.facebook.com/dialog/send?` +
          `app_id=${encodeURIComponent(fbAppId)}` +
          `&link=${encodeURIComponent(url)}` +
          `&redirect_uri=${encodeURIComponent(redirectUri)}`;
        window.open(sendDialogUrl, 'fb_send', 'width=700,height=650,noopener,noreferrer');
        return;
      }

      // Fallback: try deep-linking the Messenger app (mobile). If blocked, user can still Copy+Paste.
      try {
        window.open(`fb-messenger://share?link=${encodeURIComponent(url)}`, '_blank');
      } catch (e) {
        // ignore
      }
      this.$message.info('Messenger popup needs a Facebook App ID. Use Copy link and paste in Messenger.');
    },
    autoResize() {
      this.$nextTick(() => {
        const textareas = [
          this.$refs.peInput,
          this.$refs.diagnosisInput,
          this.$refs.plansInput,
        ]
          .filter(ref => ref && ref.$el)
          .map(ref => ref.$el.querySelector('textarea'))
          .filter(Boolean);

        textareas.forEach(textarea => {
          textarea.style.height = 'auto';
          textarea.style.height = textarea.scrollHeight + 'px';
        });
      });
    },
    _hasUnsavedChanges() {
      return this._lastFormChangeAt &&
        (!this._lastSaveCompletedAt || this._lastFormChangeAt > this._lastSaveCompletedAt);
    },
    async _waitForSaveComplete() {
      while (this._saveInFlight) {
        await new Promise((resolve) => setTimeout(resolve, 50));
      }
    },
    async ensureSavedBeforePrint() {
      if (this._debouncedAutoSave && this._debouncedAutoSave.cancel) {
        this._debouncedAutoSave.cancel();
      }
      await this._waitForSaveComplete();
      if (this._hasUnsavedChanges()) {
        await this.onSubmit('pre-print');
      }
      await this._waitForSaveComplete();
    },
    async onSubmit(source = 'manual') {
      if (this._hydratingForm && source === 'autosave') {
        return;
      }
      await this._waitForSaveComplete();
      this._saveInFlight = true;
      this.form.followup = moment
        .tz(this.form.followup, 'Asia/Manila')
        .format('YYYY-MM-DD'); // moment(this.form.followup).tz('Asia/Manila').format('YYYY-MM-DD');
      this.form.medcert_undersigned = moment
        .tz(this.form.medcert_undersigned, 'Asia/Manila')
        .format('YYYY-MM-DD'); // moment(this.form.medcert_undersigned).tz('Asia/Manila').format('YYYY-MM-DD');
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

      let famVal = '';
      this.fam.forEach((element, index) => {
        if (index === this.fam.length - 1) {
          famVal += element;
        } else {
          famVal += element + ',';
        }
      });
      this.form.fam = famVal;
      this.form.fam_others = this.profile.fam_others;

      let socVal = '';
      this.soc.forEach((element, index) => {
        if (index === this.fam.length - 1) {
          socVal += element;
        } else {
          socVal += element + ',';
        }
      });
      this.form.soc = socVal;
      this.form.soc_others = this.profile.soc_others;
      this.form.vaccination_sup = this.profile.vaccination_sup;

      await this.saveActiveDiagnosticGroupMeta();
      this.syncFormLabRemarksFromActiveGroup();

      try {
        await Patients.updateDiagnose(this.form);
        this._lastSaveCompletedAt = Date.now();
        if (source === 'manual') {
          this.$message({
            message: 'Diagnosis has been created successfully.',
            type: 'success',
            duration: 5 * 1000,
          });
        } else if (source === 'autosave') {
          this.$message({
            message: 'Diagnosis has been updated successfully.',
            type: 'success',
            duration: 5 * 1000,
          });
        }
      } catch (err) {
        console.error('Error adding suggestions:', err);
      } finally {
        this._saveInFlight = false;
      }
    },
    checkIfMobile() {
      this.isMobile = window.innerWidth <= 768;
      console.log(this.isMobile);
      this.$nextTick(() => this.syncActionToolbarMetrics());
    },
    initActionToolbarPin() {
      const sentinel = this.$refs.toolbarSentinel;
      if (!sentinel) {
        return;
      }

      this.syncActionToolbarMetrics();
      this._onToolbarResize = () => this.syncActionToolbarMetrics();
      window.addEventListener('resize', this._onToolbarResize);
      window.addEventListener('scroll', this._onToolbarResize, { passive: true });

      if (window.IntersectionObserver) {
        this._toolbarObserver = new IntersectionObserver(
          ([entry]) => {
            this.toolbarPinned = !entry.isIntersecting;
            this.$nextTick(() => this.syncActionToolbarMetrics());
          },
          { root: null, threshold: 0 }
        );
        this._toolbarObserver.observe(sentinel);
      }
    },
    destroyActionToolbarPin() {
      if (this._toolbarObserver) {
        this._toolbarObserver.disconnect();
        this._toolbarObserver = null;
      }
      if (this._onToolbarResize) {
        window.removeEventListener('resize', this._onToolbarResize);
        window.removeEventListener('scroll', this._onToolbarResize);
        this._onToolbarResize = null;
      }
    },
    syncActionToolbarMetrics() {
      const toolbar = this.$refs.actionToolbar;
      const wrap = this.$refs.toolbarWrap;
      const container = this.$el;
      if (!toolbar || !wrap || !container) {
        return;
      }

      const height = toolbar.offsetHeight;
      const marginBottom = 20;
      wrap.style.minHeight = this.toolbarPinned ? `${height + marginBottom}px` : '';

      if (this.toolbarPinned) {
        const rect = container.getBoundingClientRect();
        this.pinnedToolbarLeft = rect.left;
        this.pinnedToolbarWidth = rect.width;
      }
    },
    onCancel() {
      this.$message({
        message: 'cancel!',
        type: 'warning',
      });
    },
    currentDt() {
      return moment(this.appointment_dt).format('MMMM DD, YYYY');
    },
    goToPatientProfile() {
      if (!this.hasPatientProfileLink) {
        return;
      }
      this.$router.push({ path: this.patientProfilePath });
    },
    consultFieldHasValue(val) {
      if (val === null || val === undefined) {
        return false;
      }
      if (typeof val === 'string') {
        return val.trim() !== '';
      }
      return String(val).trim() !== '';
    },
    normalizePrevConsultRecord(prev) {
      if (!prev || (Array.isArray(prev) && prev.length === 0)) {
        return null;
      }
      if (Array.isArray(prev)) {
        return prev[0] && prev[0].id != null ? prev[0] : null;
      }
      return prev.id != null ? prev : null;
    },
    applyLastConsultationChartFields(prevData) {
      const prev = this.normalizePrevConsultRecord(prevData);
      if (!prev) {
        return false;
      }
      const keys = [
        'nurse_remarks',
        'chiefcomplaints',
        'history',
        'pe',
        'diagnosis',
        'remarks',
      ];
      let copied = false;
      keys.forEach((key) => {
        if (this.consultFieldHasValue(this.form[key])) {
          return;
        }
        if (!this.consultFieldHasValue(prev[key])) {
          return;
        }
        const value = prev[key];
        this.form[key] =
          typeof value === 'string' ? value : String(value);
        copied = true;
      });
      return copied;
    },
    appointments() {
      this._hydratingForm = true;
      if (this._debouncedAutoSave && this._debouncedAutoSave.cancel) {
        this._debouncedAutoSave.cancel();
      }
      Patients.getAppointment(this.form.id)
        .then((response) => {
          this.autoResize();
          this.pageloading = false;
          this.$nextTick(() => this.syncActionToolbarMetrics());
          this.appointment_dt = response.data.appointment_dt;
          this.vitals_today = response.vitals_today || [];
          this.vitals_by_day = response.vitals_by_day || {};
          this.vitals_records = response.vitals_data || [];
          this.showTodayVitalsMore = false;
          this.showVitalsTabMore = false;
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
          const carriedOver = this.applyLastConsultationChartFields(response.prev_data);
          this.form_att.patientid = response.px_profile.patientid;
          this.patientid_id = response.px_profile.patientid;
          this.get_attachments(response.px_profile.patientid);

          if (response.px_profile.fam) {
            const fam = response.px_profile.fam.split(',');
            fam.forEach((element) => {
              this.fam.push(element);
            });
          }

          if (response.px_profile.soc) {
            const soc = response.px_profile.soc.split(',');
            soc.forEach((element) => {
              this.soc.push(element);
            });
          }

          this.form.medcert_diagnosis = this.form.diagnosis || '';
          if (!this.form.referral_diagnosis) {
            this.form.referral_diagnosis = this.form.diagnosis || '';
          }

          this.form.pregnancy = response.px_profile.pregnancy;
          this.form.lmp = response.px_profile.lmp;
          this.form.contraceptive_use = response.px_profile.contraceptive_use;
          this.form.menopause = response.px_profile.menopause;
          this.form.mother_details = response.px_profile.mother_details;
          this.form.father_details = response.px_profile.father_details;

          this.$nextTick(async () => {
            if (this._debouncedAutoSave && this._debouncedAutoSave.cancel) {
              this._debouncedAutoSave.cancel();
            }
            if (carriedOver) {
              await this.onSubmit('hydrate');
            }
            this._hydratingForm = false;
            if (this._debouncedAutoSave && this._debouncedAutoSave.cancel) {
              this._debouncedAutoSave.cancel();
            }
          });
        })
        .catch((error) => {
          this._hydratingForm = false;
          console.log(error);
        });
    },
    async loadRxFavoriteMedicines() {
      try {
        const res = await listFavoriteMedicines();
        this.rxFavoriteMedicines = (res && res.data) || [];
      } catch (e) {
        this.rxFavoriteMedicines = [];
      }
    },
    findFavoriteMetaForSuggestion(medicineId) {
      if (!medicineId) {
        return { favoriteId: null, favoriteRecord: null };
      }
      const f = (this.rxFavoriteMedicines || []).find((x) => x.medicine_id === medicineId);
      return { favoriteId: f ? f.id : null, favoriteRecord: f || null };
    },
    rxMedicineStringOrEmpty(val) {
      if (val === null || val === undefined) {
        return '';
      }
      const s = String(val).trim();
      return s !== '' ? s : '';
    },
    mealTimingFieldsFromMedicineRecord(item) {
      if (!item) {
        return { bf_b: '', bf_a: '', l_b: '', l_a: '', s_b: '', s_a: '', bt: '' };
      }
      return {
        bf_b: this.rxMedicineStringOrEmpty(item.default_bf_b),
        bf_a: this.rxMedicineStringOrEmpty(item.default_bf_a),
        l_b: this.rxMedicineStringOrEmpty(item.default_l_b),
        l_a: this.rxMedicineStringOrEmpty(item.default_l_a),
        s_b: this.rxMedicineStringOrEmpty(item.default_s_b),
        s_a: this.rxMedicineStringOrEmpty(item.default_s_a),
        bt: this.rxMedicineStringOrEmpty(item.default_bt),
      };
    },
    buildRxSuggestionList(searchItems, favItems) {
      const out = [];
      if (favItems.length) {
        out.push({ medicine: '—', isSectionHeader: true, sectionLabel: 'Favorites' });
        favItems.forEach((x) => out.push(x));
      }
      if (searchItems.length) {
        if (favItems.length) {
          out.push({ medicine: '—', isSectionHeader: true, sectionLabel: 'Search results' });
        }
        searchItems.forEach((x) => out.push(x));
      }
      return out;
    },
    buildFavoritePayloadFromRxSuggestion(item) {
      const hasId = item.id && item.id !== 0;
      const meal = this.rxMealTimingFromMedsArr();
      return {
        medicine_id: hasId ? item.id : null,
        drug_name: hasId ? item.medicine : (this.rxMedicineStringOrEmpty(this.medsArr.custom_brand) || item.medicine),
        custom_generic_name: this.rxMedicineStringOrEmpty(this.medsArr.custom_generic || item.generic_name) || null,
        default_qty: this.rxMedicineStringOrEmpty(this.medsArr.qty) || null,
        default_dosage: this.rxMedicineStringOrEmpty(this.medsArr.custom_dosage || item.unit) || null,
        default_remarks: this.rxMedicineStringOrEmpty(this.medsArr.remarks) || null,
        default_bf_b: meal.bf_b || null,
        default_bf_a: meal.bf_a || null,
        default_l_b: meal.l_b || null,
        default_l_a: meal.l_a || null,
        default_s_b: meal.s_b || null,
        default_s_a: meal.s_a || null,
        default_bt: meal.bt || null,
      };
    },
    async toggleRxFavoriteStar(item) {
      if (item.isSectionHeader) {
        return;
      }
      try {
        if (item.favoriteId) {
          await deleteFavoriteMedicine(item.favoriteId);
          await this.loadRxFavoriteMedicines();
          this.$message.success('Removed from favorites');
          return;
        }
        await createFavoriteMedicine(this.buildFavoritePayloadFromRxSuggestion(item));
        await this.loadRxFavoriteMedicines();
        this.$message.success('Saved to favorites');
      } catch (e) {
        this.$message.error('Could not update favorites');
      }
    },
    rxTimingDoseFilled(val) {
      return isPositiveMealDoseString(val);
    },
    emptyRxMealTiming() {
      return { bf_b: '', bf_a: '', l_b: '', l_a: '', s_b: '', s_a: '', bt: '' };
    },
    rxMealTimingFromMedsArr() {
      return {
        bf_b: this.rxMedicineStringOrEmpty(this.medsArr.bf_b),
        bf_a: this.rxMedicineStringOrEmpty(this.medsArr.bf_a),
        l_b: this.rxMedicineStringOrEmpty(this.medsArr.l_b),
        l_a: this.rxMedicineStringOrEmpty(this.medsArr.l_a),
        s_b: this.rxMedicineStringOrEmpty(this.medsArr.s_b),
        s_a: this.rxMedicineStringOrEmpty(this.medsArr.s_a),
        bt: this.rxMedicineStringOrEmpty(this.medsArr.bt),
      };
    },
    rxMealTimingFromListRow(row) {
      if (!row) {
        return this.emptyRxMealTiming();
      }
      const pick = (primary, legacy) => {
        const a = this.rxMedicineStringOrEmpty(primary);
        if (a !== '') {
          return a;
        }
        return this.rxMedicineStringOrEmpty(legacy);
      };
      return {
        bf_b: pick(row.bf_b, row.bb),
        bf_a: pick(row.bf_a, row.ab),
        l_b: pick(row.l_b, row.bl),
        l_a: pick(row.l_a, row.al),
        s_b: pick(row.s_b, row.bs),
        s_a: pick(row.s_a, row.as),
        bt: this.rxMedicineStringOrEmpty(row.bt),
      };
    },
    applyMealTimingToMedsArr(meal) {
      const m = meal || this.emptyRxMealTiming();
      this.medsArr.bf_b = m.bf_b || '';
      this.medsArr.bf_a = m.bf_a || '';
      this.medsArr.l_b = m.l_b || '';
      this.medsArr.l_a = m.l_a || '';
      this.medsArr.s_b = m.s_b || '';
      this.medsArr.s_a = m.s_a || '';
      this.medsArr.bt = m.bt || '';
    },
    itemHasMealTiming(row) {
      const meal = row && (row.bf_b !== undefined || row.bb !== undefined)
        ? this.rxMealTimingFromListRow(row)
        : this.rxMealTimingFromMedsArr();
      return ['bf_b', 'bf_a', 'l_b', 'l_a', 's_b', 's_a', 'bt'].some((k) =>
        this.rxTimingDoseFilled(meal[k])
      );
    },
    mealTimingFieldsFromFavoriteRecord(r) {
      const fromFreq = appointmentMealTimingStringsFromFrequency(r.default_frequency || '');
      const pick = (col, freqVal) => {
        const c = col != null ? String(col).trim() : '';
        if (c !== '') {
          return c;
        }
        return freqVal != null && String(freqVal).trim() !== '' ? String(freqVal).trim() : '';
      };
      return {
        bf_b: pick(r.default_bf_b, fromFreq.bf_b),
        bf_a: pick(r.default_bf_a, fromFreq.bf_a),
        l_b: pick(r.default_l_b, fromFreq.l_b),
        l_a: pick(r.default_l_a, fromFreq.l_a),
        s_b: pick(r.default_s_b, fromFreq.s_b),
        s_a: pick(r.default_s_a, fromFreq.s_a),
        bt: pick(r.default_bt, fromFreq.bt),
      };
    },
    applyMedicineDefaultsToMedsArr(item, favoriteRecord = null) {
      this.medsArr.custom_meds = false;
      this.medsArr.med_id = item.id;
      this.medsArr.custom_generic = this.rxMedicineStringOrEmpty(item.generic_name);
      this.medsArr.custom_brand = this.rxMedicineStringOrEmpty(item.medicine);
      this.medsArr.custom_dosage = this.rxMedicineStringOrEmpty(item.unit);
      this.medsArr.meds = this.medsArr.custom_brand;

      const masterQty = this.rxMedicineStringOrEmpty(item.default_qty);
      let qty = masterQty;
      if (favoriteRecord) {
        const favQty = this.rxMedicineStringOrEmpty(favoriteRecord.default_qty);
        if (favQty !== '') {
          qty = favQty;
        }
        const favDosage = this.rxMedicineStringOrEmpty(favoriteRecord.default_dosage);
        if (favDosage !== '') {
          this.medsArr.custom_dosage = favDosage;
        }
      }
      this.medsArr.qty = qty !== '' ? qty : '1';

      const masterMeal = this.mealTimingFieldsFromMedicineRecord(item);
      let meal = { ...masterMeal };
      if (favoriteRecord) {
        const favMeal = this.mealTimingFieldsFromFavoriteRecord(favoriteRecord);
        meal = {
          bf_b: favMeal.bf_b || masterMeal.bf_b,
          bf_a: favMeal.bf_a || masterMeal.bf_a,
          l_b: favMeal.l_b || masterMeal.l_b,
          l_a: favMeal.l_a || masterMeal.l_a,
          s_b: favMeal.s_b || masterMeal.s_b,
          s_a: favMeal.s_a || masterMeal.s_a,
          bt: favMeal.bt || masterMeal.bt,
        };
      }
      this.applyMealTimingToMedsArr(meal);

      const masterRemarks = this.rxMedicineStringOrEmpty(item.default_remarks);
      const favRemarks = favoriteRecord
        ? this.rxMedicineStringOrEmpty(favoriteRecord.default_remarks)
        : '';
      this.medsArr.remarks = favRemarks || masterRemarks;
    },
    async applyFavoriteRecordToMedsArr(r) {
      const hasMaster = r.medicine_id != null && r.medicine_id !== 0;
      let masterItem = null;
      if (hasMaster) {
        try {
          const searchTerm = this.rxMedicineStringOrEmpty(r.custom_generic_name || r.drug_name);
          const response = await Medicine.findmedicine(searchTerm);
          masterItem =
            (response.suggestions || []).find((s) => s.id === r.medicine_id) || null;
        } catch (e) {
          masterItem = null;
        }
      }

      this.medsArr.custom_meds = !hasMaster;
      if (hasMaster && masterItem) {
        this.applyMedicineDefaultsToMedsArr(masterItem, r);
        return;
      }

      this.medsArr.meds = '';
      this.medsArr.med_id = hasMaster ? r.medicine_id : 0;
      const gen = this.rxMedicineStringOrEmpty(r.custom_generic_name);
      const brand = this.rxMedicineStringOrEmpty(r.drug_name);
      this.medsArr.custom_generic = gen || brand;
      this.medsArr.custom_brand = brand || gen;
      this.medsArr.custom_dosage = this.rxMedicineStringOrEmpty(r.default_dosage);

      const favQty = this.rxMedicineStringOrEmpty(r.default_qty);
      this.medsArr.qty = favQty !== '' ? favQty : '1';

      this.applyMealTimingToMedsArr(this.mealTimingFieldsFromFavoriteRecord(r));
      this.medsArr.remarks = this.rxMedicineStringOrEmpty(r.default_remarks);
    },
    buildRxPayloadFromFavorite(r) {
      const hasMaster = r.medicine_id != null && r.medicine_id !== 0;
      const dq = r.default_qty != null ? String(r.default_qty).trim() : '';
      const qty = dq !== '' ? dq : '1';
      const groupId = this.getActivePrescriptionGroupId();
      const meal = this.mealTimingFieldsFromFavoriteRecord(r);
      if (hasMaster) {
        const brand = (r.drug_name || '').trim();
        const generic = (r.custom_generic_name || '').trim();
        const dosage = (r.default_dosage || '').trim();
        return {
          id: this.$route.params.id,
          prescription_group_id: groupId,
          custom_meds: false,
          med_id: r.medicine_id,
          meds: brand,
          custom_generic: generic,
          custom_brand: brand,
          custom_dosage: dosage,
          qty,
          ...meal,
          remarks: r.default_remarks || '',
        };
      }
      const gen = (r.custom_generic_name || '').trim();
      const brand = (r.drug_name || '').trim();
      return {
        id: this.$route.params.id,
        prescription_group_id: groupId,
        custom_meds: true,
        med_id: 0,
        meds: '',
        custom_generic: gen || brand,
        custom_brand: brand || gen,
        custom_dosage: (r.default_dosage || '').trim(),
        qty,
        ...meal,
        remarks: r.default_remarks || '',
      };
    },
    openRxFavoritesDialog() {
      this.rxFavoritesDialogSelection = [];
      this.rxFavoritesDialogVisible = true;
      this.loadRxFavoriteMedicines();
      this.$nextTick(() => {
        if (this.$refs.rxFavoritesTable) {
          this.$refs.rxFavoritesTable.clearSelection();
        }
      });
    },
    handleRxFavoritesSelectionChange(val) {
      this.rxFavoritesDialogSelection = val || [];
    },
    rxFavoriteSchedulePreview(row) {
      const parts = [];
      if (row.default_bf_b || row.default_bf_a) {
        parts.push(`BF ${row.default_bf_b || '—'}/${row.default_bf_a || '—'}`);
      }
      if (row.default_l_b || row.default_l_a) {
        parts.push(`L ${row.default_l_b || '—'}/${row.default_l_a || '—'}`);
      }
      if (row.default_s_b || row.default_s_a) {
        parts.push(`D ${row.default_s_b || '—'}/${row.default_s_a || '—'}`);
      }
      if (row.default_bt) {
        parts.push(`HS ${row.default_bt}`);
      }
      if (row.default_remarks) {
        parts.push(row.default_remarks);
      }
      return parts.length ? parts.join(' · ') : '—';
    },
    async applyRxFavoritesSelection() {
      const rows = this.rxFavoritesDialogSelection;
      if (!rows || !rows.length) {
        this.$message.warning('Select at least one favorite.');
        return;
      }
      this.rxFavoritesApplyLoading = true;
      try {
        for (const fav of rows) {
          await Medicine.add_rx(this.buildRxPayloadFromFavorite(fav));
        }
        await this.getmeds();
        this.$message.success('Medicines added from favorites.');
        this.rxFavoritesDialogVisible = false;
      } catch (e) {
        this.$message.error('Could not add medicines from favorites.');
      } finally {
        this.rxFavoritesApplyLoading = false;
      }
    },
    openRxFavoriteEdit(row) {
      if (!row) {
        return;
      }
      this.rxFavoriteEditForm = {
        id: row.id,
        drug_name: row.drug_name || '',
        default_qty: row.default_qty != null ? String(row.default_qty) : '',
        default_remarks: row.default_remarks || '',
      };
      this.rxFavoriteEditDialogVisible = true;
    },
    async saveRxFavoriteEdit() {
      if (!this.rxFavoriteEditForm.id) {
        return;
      }
      this.rxFavoriteEditSaving = true;
      try {
        await updateFavoriteMedicine(this.rxFavoriteEditForm.id, {
          default_qty: (this.rxFavoriteEditForm.default_qty || '').trim() || null,
          default_remarks: (this.rxFavoriteEditForm.default_remarks || '').trim() || null,
        });
        await this.loadRxFavoriteMedicines();
        this.$message.success('Favorite updated.');
        this.rxFavoriteEditDialogVisible = false;
      } catch (e) {
        this.$message.error('Could not update favorite.');
      } finally {
        this.rxFavoriteEditSaving = false;
      }
    },
    async removeRxFavorite(row) {
      if (!row || !row.id) {
        return;
      }
      try {
        await this.$confirm('Remove this medicine from favorites?', 'Remove favorite', {
          confirmButtonText: 'Remove',
          cancelButtonText: 'Cancel',
          type: 'warning',
        });
      } catch (_) {
        return;
      }
      try {
        await deleteFavoriteMedicine(row.id);
        await this.loadRxFavoriteMedicines();
        this.$message.success('Removed from favorites.');
      } catch (e) {
        this.$message.error('Could not remove favorite.');
      }
    },
    async querySearch(queryString, cb) {
      const q = (queryString || '').trim().toLowerCase();
      const favList = this.rxFavoriteMedicines || [];

      const favMatches = favList.filter((f) => {
        if (!q) {
          return true;
        }
        return (f.drug_name || '').toLowerCase().includes(q) ||
          (f.custom_generic_name || '').toLowerCase().includes(q);
      });

      const favSuggestions = favMatches.map((f) => ({
        medicine: f.drug_name,
        id: f.medicine_id || 0,
        generic_name: (f.custom_generic_name || f.drug_name || '').trim(),
        unit: f.default_dosage || '',
        isFavoriteRow: true,
        favoriteId: f.id,
        favoriteRecord: f,
        isFavorite: true,
      }));

      const favByMedId = new Set(
        favMatches.filter((f) => f.medicine_id).map((f) => f.medicine_id)
      );

      if (q === '') {
        cb(this.buildRxSuggestionList([], favSuggestions));
        return;
      }
      try {
        this.loading = true;
        const response = await Medicine.findmedicine(queryString);
        const suggestions = response.suggestions ? response.suggestions : [];
        const filtered = suggestions.filter((s) => !favByMedId.has(s.id));
        const withFav = filtered.map((s) => {
          const meta = this.findFavoriteMetaForSuggestion(s.id);
          return {
            ...s,
            favoriteId: meta.favoriteId,
            favoriteRecord: meta.favoriteRecord,
            isFavorite: !!meta.favoriteId,
          };
        });
        cb(this.buildRxSuggestionList(withFav, favSuggestions));
      } catch (error) {
        console.error('Error fetching suggestions:', error);
        cb(this.buildRxSuggestionList([], favSuggestions));
      } finally {
        this.loading = false;
      }
    },
    async handleSelect(ev) {
      if (!ev || ev.isSectionHeader) {
        return;
      }
      if (ev.isFavoriteRow && ev.favoriteRecord) {
        await this.applyFavoriteRecordToMedsArr(ev.favoriteRecord);
        return;
      }
      const favoriteRecord =
        ev.favoriteRecord ||
        (ev.favoriteId &&
          (this.rxFavoriteMedicines || []).find((x) => x.id === ev.favoriteId)) ||
        null;
      this.$nextTick(() => {
        this.applyMedicineDefaultsToMedsArr(ev, favoriteRecord);
      });
    },
    removeItem(id) { },
    async queryProcedure(queryString, cb) {
      if (queryString === '') {
        cb([]);
        return;
      }
      try {
        this.loading = true;
        const response = await Procedure.findprocedure(queryString);
        const suggestions = response.suggestions;
        cb(suggestions);
      } catch (error) {
        console.error('Error fetching suggestions:', error);
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
    handleDxListSelectionChange(val) {
      this.dxListSelection = val || [];
    },
    clearDxTableSelection() {
      this.dxListSelection = [];
      const ref = this.$refs.dxTable;
      if (!ref) {
        return;
      }
      const tables = Array.isArray(ref) ? ref : [ref];
      tables.forEach((t) => {
        if (t && typeof t.clearSelection === 'function') {
          t.clearSelection();
        }
      });
    },
    deleteSelectedDiagnostics() {
      const rows = this.dxListSelection || [];
      if (!rows.length) {
        this.$message.warning('Select at least one diagnostic to delete.');
        return;
      }
      const count = rows.length;
      this.$confirm(
        `Delete ${count} selected diagnostic${count === 1 ? '' : 's'}?`,
        'Warning',
        {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
          type: 'warning',
        }
      )
        .then(async() => {
          this.dxListDeleteLoading = true;
          try {
            for (const row of rows) {
              await Procedure.remove_diagnostic(row.id);
            }
            this.clearDxTableSelection();
            await this.getdiagnostics();
            this.$message.success(
              `Deleted ${count} diagnostic${count === 1 ? '' : 's'}.`
            );
          } catch (err) {
            console.error('Error deleting diagnostics:', err);
            this.$message.error('Could not delete all selected diagnostics.');
          } finally {
            this.dxListDeleteLoading = false;
          }
        })
        .catch(() => {
          this.$message({
            type: 'info',
            message: 'Delete canceled',
          });
        });
    },
    removeProcedure(index) {
      this.$confirm('Are you sure you want to delete this item?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      })
        .then(() => {
          Procedure.remove_diagnostic(index)
            .then((response) => {
              if ((this.dxListSelection || []).some((r) => r.id === index)) {
                this.clearDxTableSelection();
              }
              this.getdiagnostics();
            })
            .catch((err) => {
              console.error('Error adding suggestions:', err);
            });
        })
        .catch(() => {
          this.$message({
            type: 'info',
            message: 'Delete canceled',
          });
        });
    },
    async queryService(queryString, cb) {
      if (queryString === '') {
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
        console.error('Error fetching suggestions:', error);
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
      this.$confirm('Are you sure you want to delete this item?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      })
        .then(() => {
          Patients.remove_service(row)
            .then((response) => {
              this.getservices();
            })
            .catch((err) => {
              console.error('Error adding suggestions:', err);
            });
        })
        .catch(() => {
          this.$message({
            type: 'info',
            message: 'Delete canceled',
          });
        });
    },
    async getAllServices() {
      await Services.getAllServices()
        .then((response) => {
          this.getAllServicesOffered = response;
        })
        .catch((err) => {
          console.error('Error adding suggestions:', err);
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
          console.error('Error adding suggestions:', err);
        });
    },
    addServices() {
      if (this.servicesRendered.rendered.length > 0) {
        Patients.add_service(this.servicesRendered)
          .then((response) => {
            this.getservices();
            this.service.service = '';
            this.service.fee = 0;
            this.service.discount = 0;
            this.servicesRendered.rendered = [];
            this.servicesRenderedModel = [];
            this.viewServicesTbl = false;
          })
          .catch((err) => {
            console.error('Error adding suggestions:', err);
          });
      } else {
        this.$message.warning('Select at least one service to add.');
      }
    },
    addNewServices(e) {
      if (this.servicesRenderedModel.includes(e.description)) {
        if (!this.servicesRendered.rendered.find((s) => s.service_id === e.service_id)) {
          this.servicesRendered.rendered.push({
            service: e.description,
            id: this.$route.params.id,
            fee: e.fee,
            service_id: e.service_id,
          });
        }
      } else {
        this.servicesRendered.rendered = this.servicesRendered.rendered.filter(
          (s) => s.service_id !== e.service_id
        );
      }
    },
    openRxOrderDialog() {
      this.clearMedsForm();
      this.rxOrderDialogVisible = true;
    },
    closeRxOrderDialog() {
      this.rxOrderDialogVisible = false;
    },
    onRxOrderDialogClose() {
      this.cancelEdit();
    },
    editMed(row) {
      this.isEditMode = true;
      this.editingMedId = row.id;

      this.medsArr.qty = this.rxMedicineStringOrEmpty(row.qty);
      this.medsArr.remarks = this.rxMedicineStringOrEmpty(row.remarks);
      this.medsArr.med_id = row.medicineId;
      this.medsArr.custom_generic = this.rxMedicineStringOrEmpty(row.generic);
      this.medsArr.custom_brand = this.rxMedicineStringOrEmpty(row.brand);
      this.medsArr.custom_dosage = this.rxMedicineStringOrEmpty(row.dosage);
      this.medsArr.custom_meds = row.medicineId == 0;
      this.medsArr.meds = this.rxMedicineStringOrEmpty(row.brand);
      this.applyMealTimingToMedsArr(this.rxMealTimingFromListRow(row));
      this.rxOrderDialogVisible = true;
    },
    handleRxListSelectionChange(val) {
      this.rxListSelection = val || [];
    },
    clearRxMedTableSelection() {
      this.rxListSelection = [];
      const ref = this.$refs.rxMedTable;
      if (!ref) {
        return;
      }
      const tables = Array.isArray(ref) ? ref : [ref];
      tables.forEach((t) => {
        if (t && typeof t.clearSelection === 'function') {
          t.clearSelection();
        }
      });
    },
    deleteSelectedMeds() {
      const rows = this.rxListSelection || [];
      if (!rows.length) {
        this.$message.warning('Select at least one medicine to delete.');
        return;
      }
      const count = rows.length;
      const deletingEditTarget = this.isEditMode && rows.some((r) => r.id === this.editingMedId);
      this.$confirm(
        `Delete ${count} selected medicine${count === 1 ? '' : 's'}?`,
        'Warning',
        {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
          type: 'warning',
        }
      )
        .then(async() => {
          this.rxListDeleteLoading = true;
          try {
            for (const row of rows) {
              await Medicine.remove_rx(row.id);
            }
            this.clearRxMedTableSelection();
            if (deletingEditTarget) {
              this.cancelEdit();
            }
            await this.getmeds();
            this.$message.success(
              `Deleted ${count} medicine${count === 1 ? '' : 's'}.`
            );
          } catch (err) {
            console.error('Error deleting medicines:', err);
            this.$message.error('Could not delete all selected medicines.');
          } finally {
            this.rxListDeleteLoading = false;
          }
        })
        .catch(() => {
          this.$message({
            type: 'info',
            message: 'Delete canceled',
          });
        });
    },
    deleteMed(row) {
      this.$confirm('Are you sure you want to delete this item?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      })
        .then(() => {
          Medicine.remove_rx(row)
            .then((response) => {
              if (this.isEditMode && this.editingMedId === row) {
                this.cancelEdit();
              }
              if ((this.rxListSelection || []).some((r) => r.id === row)) {
                this.clearRxMedTableSelection();
              }
              this.getmeds();
            })
            .catch((err) => {
              console.error('Error adding suggestions:', err);
            });
        })
        .catch(() => {
          this.$message({
            type: 'info',
            message: 'Delete canceled',
          });
        });
    },
    cancelEdit() {
      this.isEditMode = false;
      this.editingMedId = null;
      this.rxOrderDialogVisible = false;
      this.clearMedsForm();
    },
    clearMedsForm() {
      this.medsArr.qty = '';
      this.medsArr.bf_b = '';
      this.medsArr.bf_a = '';
      this.medsArr.l_a = '';
      this.medsArr.l_b = '';
      this.medsArr.s_b = '';
      this.medsArr.s_a = '';
      this.medsArr.bt = '';
      this.medsArr.meds = '';
      this.medsArr.remarks = '';
      this.medsArr.custom_generic = '';
      this.medsArr.custom_brand = '';
      this.medsArr.custom_dosage = '';
      this.medsArr.custom_meds = false;
      this.medsArr.med_id = 0;
      this.isEditMode = false;
      this.editingMedId = null;
    },
    newMedicine() {
      this.clearMedsForm();
    },
    addMeds() {
      const generic = this.rxMedicineStringOrEmpty(this.medsArr.custom_generic);
      const brand = this.rxMedicineStringOrEmpty(this.medsArr.custom_brand);
      const qty = this.rxMedicineStringOrEmpty(this.medsArr.qty);
      if (generic !== '' && qty !== '') {
        if (!this.itemHasMealTiming(this.medsArr)) {
          this.$message.warning('Enter at least one meal timing dose.');
          return;
        }
        const payload = {
          ...this.medsArr,
          custom_generic: generic,
          custom_brand: brand,
          qty,
          custom_dosage: this.rxMedicineStringOrEmpty(this.medsArr.custom_dosage),
          ...this.rxMealTimingFromMedsArr(),
          prescription_group_id: this.getActivePrescriptionGroupId(),
        };
        if (this.isEditMode) {
          // Update existing medicine
          Medicine.update_rx(this.editingMedId, payload)
            .then((response) => {
              this.getmeds();
              this.isEditMode = false;
              this.editingMedId = null;
              this.rxOrderDialogVisible = false;
              this.clearMedsForm();
              this.$message({
                type: 'success',
                message: 'Medicine updated successfully.',
              });
            })
            .catch((err) => {
              console.error('Error updating medicine:', err);
              this.$message.error('Failed to update medicine.');
            });
        } else {
          // Add new medicine
          Medicine.add_rx(payload)
            .then((response) => {
              this.getmeds();
              this.rxOrderDialogVisible = false;
              this.clearMedsForm();
              this.$message({
                type: 'success',
                message: 'Medicine added successfully.',
              });
            })
            .catch((err) => {
              console.error('Error adding medicine:', err);
              this.$message.error('Failed to add medicine.');
            });
        }
      } else {
        alert('Generic name and quantity are required.');
      }
    },
    filterDiagnosticsList(list) {
      const q = (this.diagnosticsFilterQuery || '').trim().toLowerCase();
      if (!q) {
        return list || [];
      }
      return (list || []).filter((item) =>
        (item.lab_test || '').toLowerCase().includes(q)
      );
    },
    addProcedure() {
      this.processLabOthersInput(this.form.lab_others);
      if (this.diagnosticsRendered.rendered.length > 0) {
        Procedure.add_diagnostic({
          diagnostic_group_id: this.getActiveDiagnosticGroupId(),
          rendered: this.diagnosticsRendered.rendered,
        })
          .then((response) => {
            this.getdiagnostics();
            this.procedure.procedure = '';
            this.procedure.remarks = '';
            this.procedure.type = 0;
            this.diagnosticsRendered.rendered = [];
            this.diagnosticsRenderedModel = [];
            this.diagnosticsFilterQuery = '';
            this.form.lab_others = null;
            this.form.anc_others = null;
            this.viewDiagnosticsTbl = false;
          })
          .catch((err) => {
            console.error('Error adding suggestions:', err);
          });
      } else {
        alert('Diagnostic are required.');
      }
    },
    addNewProcedure2(e) {
      console.log(e);
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
            remarks: '',
            type: e.lab_category_id,
            lab_micro_remarks: this.lab_micro_remarks,
            xray_remarks: '',
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
    handleSynovialFluidExtraOption(option) {
      const procedureName = option.procedureName;
      const isSelected = this.synovialFluidExtraOptions.includes(option.label);

      if (isSelected) {
        // Add to diagnosticsRenderedModel if not already present
        if (!this.diagnosticsRenderedModel.includes(procedureName)) {
          this.diagnosticsRenderedModel.push(procedureName);
        }
        // Add to diagnosticsRendered.rendered if not already added
        if (!this.diagnosticsRendered.rendered.find(p => p.procedure === procedureName)) {
          this.diagnosticsRendered.rendered.push({
            id: this.$route.params.id,
            procedure_id: 0, // Using 0 for custom procedures
            procedure: procedureName,
            remarks: 'extra',
            type: 19, // Using crystal analysis category ID
            lab_micro_remarks: this.lab_micro_remarks,
            xray_remarks: '',
          });
        }
      } else {
        // Remove from diagnosticsRenderedModel
        this.diagnosticsRenderedModel = this.diagnosticsRenderedModel.filter(
          item => item !== procedureName
        );
        // Remove from diagnosticsRendered.rendered
        this.diagnosticsRendered.rendered = this.diagnosticsRendered.rendered.filter(
          p => p.procedure !== procedureName
        );
      }
    },
    processLabOthersInput(v) {
      if (!v) {
        return;
      }
      const items = String(v)
        .split(',')
        .map((s) => s.trim())
        .filter(Boolean);

      items.forEach((item) => {
        const exists = this.diagnosticsRendered.rendered.some(
          (p) => p.procedure_id === 0 && p.procedure === item
        );
        if (exists) {
          return;
        }

        this.diagnosticsRendered.rendered.push({
          id: this.$route.params.id,
          procedure_id: 0,
          procedure: item,
          remarks: '',
          type: 1,
          lab_micro_remarks: this.lab_micro_remarks,
          xray_remarks: '',
        });
      });

      this.form.lab_others = null;
    },
    addLabOthers(v) {
      this.processLabOthersInput(v);
    },
    addAncOthers(v) {
      if (v !== null) {
        this.diagnosticsRendered.rendered.push({
          id: this.$route.params.id,
          procedure_id: 0,
          procedure: v,
          remarks: '',
          type: 2,
        });
      }
    },
    destroyRxMedSortables() {
      (this.rxMedSortables || []).forEach((s) => s.destroy());
      this.rxMedSortables = [];
    },
    initRxMedTableSortable() {
      this.destroyRxMedSortables();
      if (this.tab !== 'fourth' || !this.activeRxList.length) {
        return;
      }
      this.$nextTick(() => {
        const ref = this.$refs.rxMedTable;
        if (!ref) {
          return;
        }
        const tables = Array.isArray(ref) ? ref : [ref];
        tables.forEach((table) => {
          const tbody = table.$el.querySelector('.el-table__body-wrapper tbody');
          if (!tbody) {
            return;
          }
          const sortable = Sortable.create(tbody, {
            handle: '.rx-drag-handle',
            animation: 150,
            ghostClass: 'rx-med-row-ghost',
            onEnd: (evt) => {
              const { oldIndex, newIndex } = evt;
              if (oldIndex === newIndex || oldIndex == null || newIndex == null) {
                return;
              }
              const gid = this.activePrescriptionGroupId
                ? Number(this.activePrescriptionGroupId)
                : null;
              const list = this.rx_list.slice();
              const indices = [];
              const groupItems = [];
              list.forEach((r, i) => {
                if (Number(r.prescription_group_id) === gid) {
                  indices.push(i);
                  groupItems.push(r);
                }
              });
              const [moved] = groupItems.splice(oldIndex, 1);
              groupItems.splice(newIndex, 0, moved);
              let j = 0;
              indices.forEach((idx) => {
                list[idx] = groupItems[j++];
              });
              this.rx_list = list;
              this.saveRxMedOrder();
            },
          });
          this.rxMedSortables.push(sortable);
        });
      });
    },
    async saveRxMedOrder() {
      const appointmentId = this.$route.params.id;
      if (!appointmentId || !this.activeRxList.length) {
        return;
      }
      this.rxListReorderLoading = true;
      try {
        await Medicine.reorderAppointmentMeds({
          appointment_id: appointmentId,
          prescription_group_id: this.activePrescriptionGroupId
            ? Number(this.activePrescriptionGroupId)
            : null,
          order: this.activeRxList.map((r) => r.id),
        });
      } catch (err) {
        console.error('Error saving medicine order:', err);
        this.$message.error('Could not save medicine order.');
        await this.getmeds();
      } finally {
        this.rxListReorderLoading = false;
      }
    },
    async refreshRxList() {
      this.rxListRefreshLoading = true;
      try {
        await this.getmeds();
        this.clearRxMedTableSelection();
      } finally {
        this.rxListRefreshLoading = false;
      }
    },
    getmeds() {
      this.rx_list = [];
      return Medicine.getAppointmentMeds(this.$route.params.id)
        .then((response) => {
          const payload = response || {};
          this.prescription_groups = payload.groups || [];
          const meds = payload.medicines || payload.data || [];
          this.rx_list = meds.map((row) => ({
            ...row,
            ...this.rxMealTimingFromListRow(row),
          }));
          this.ensureActivePrescriptionGroup();
          this.initRxMedTableSortable();
        })
        .catch((err) => {
          console.error('Error adding suggestions:', err);
        });
    },
    ensureActivePrescriptionGroup() {
      if (!this.prescription_groups.length) {
        this.activePrescriptionGroupId = null;
        return;
      }
      const ids = this.prescription_groups.map((g) => String(g.id));
      if (
        !this.activePrescriptionGroupId ||
        !ids.includes(String(this.activePrescriptionGroupId))
      ) {
        this.activePrescriptionGroupId = String(this.prescription_groups[0].id);
      }
    },
    getActivePrescriptionGroupId() {
      this.ensureActivePrescriptionGroup();
      return this.activePrescriptionGroupId
        ? Number(this.activePrescriptionGroupId)
        : null;
    },
    async addPrescriptionGroup() {
      const appointmentId = this.$route.params.id;
      if (!appointmentId) {
        return;
      }
      this.rxGroupActionLoading = true;
      try {
        const group = await Medicine.createPrescriptionGroup({
          appointment_id: appointmentId,
        });
        await this.getmeds();
        if (group && group.id) {
          this.activePrescriptionGroupId = String(group.id);
        }
        this.$message.success('Prescription group added.');
      } catch (e) {
        this.$message.error('Could not add prescription group.');
      } finally {
        this.rxGroupActionLoading = false;
      }
    },
    renamePrescriptionGroup(group) {
      if (!group || !group.id) {
        return;
      }
      this.$prompt('Prescription group name', 'Rename', {
        confirmButtonText: 'Save',
        cancelButtonText: 'Cancel',
        inputValue: group.title || '',
        inputPattern: /\S+/,
        inputErrorMessage: 'Name is required',
      })
        .then(async({ value }) => {
          const title = String(value || '').trim();
          if (!title) {
            return;
          }
          this.rxGroupActionLoading = true;
          try {
            await Medicine.updatePrescriptionGroup(group.id, { title });
            group.title = title;
            this.$message.success('Prescription group renamed.');
          } catch (e) {
            this.$message.error('Could not rename prescription group.');
          } finally {
            this.rxGroupActionLoading = false;
          }
        })
        .catch(() => {});
    },
    async deleteActivePrescriptionGroup() {
      const gid = this.getActivePrescriptionGroupId();
      if (!gid) {
        return;
      }
      const group = this.activePrescriptionGroup;
      const title = group ? group.title : 'this group';
      try {
        await this.$confirm(
          `Delete "${title}" and all medicines in this group?`,
          'Delete prescription group',
          { type: 'warning', confirmButtonText: 'Delete', cancelButtonText: 'Cancel' }
        );
      } catch (e) {
        return;
      }
      this.rxGroupActionLoading = true;
      try {
        if (this.isEditMode && this.editingMedId) {
          const editing = (this.rx_list || []).find((r) => r.id === this.editingMedId);
          if (editing && Number(editing.prescription_group_id) === gid) {
            this.cancelEdit();
          }
        }
        this.clearRxMedTableSelection();
        await Medicine.deletePrescriptionGroup(gid);
        await this.getmeds();
        this.$message.success('Prescription group deleted.');
      } catch (e) {
        this.$message.error('Could not delete prescription group.');
      } finally {
        this.rxGroupActionLoading = false;
      }
    },
    handlePrintRxCommand(command) {
      if (command === 'all') {
        this.printAllRxGroups();
        return;
      }
      this.printCurrentRxGroup();
    },
    printCurrentRxGroup() {
      const gid = this.getActivePrescriptionGroupId();
      if (!gid) {
        this.$message.warning('Select a prescription group first.');
        return;
      }
      this.printpdf2(gid);
    },
    printAllRxGroups() {
      this.printpdf2('all');
    },
    destroyDxTableSortables() {
      (this.dxTableSortables || []).forEach((s) => s.destroy());
      this.dxTableSortables = [];
    },
    initDxTableSortable() {
      this.destroyDxTableSortables();
      if (this.tab !== 'fifth' || !this.activeDiagnosticList.length) {
        return;
      }
      this.$nextTick(() => {
        const ref = this.$refs.dxTable;
        if (!ref) {
          return;
        }
        const tables = Array.isArray(ref) ? ref : [ref];
        tables.forEach((table) => {
          const tbody = table.$el.querySelector('.el-table__body-wrapper tbody');
          if (!tbody) {
            return;
          }
          const sortable = Sortable.create(tbody, {
            handle: '.rx-drag-handle',
            animation: 150,
            ghostClass: 'rx-med-row-ghost',
            onEnd: (evt) => {
              const { oldIndex, newIndex } = evt;
              if (oldIndex === newIndex || oldIndex == null || newIndex == null) {
                return;
              }
              const gid = this.activeDiagnosticGroupId
                ? Number(this.activeDiagnosticGroupId)
                : null;
              const list = this.diagnostic_list.slice();
              const indices = [];
              const groupItems = [];
              list.forEach((r, i) => {
                if (Number(r.diagnostic_group_id) === gid) {
                  indices.push(i);
                  groupItems.push(r);
                }
              });
              const [moved] = groupItems.splice(oldIndex, 1);
              groupItems.splice(newIndex, 0, moved);
              let j = 0;
              indices.forEach((idx) => {
                list[idx] = groupItems[j++];
              });
              this.diagnostic_list = list;
              this.saveDxOrder();
            },
          });
          this.dxTableSortables.push(sortable);
        });
      });
    },
    async saveDxOrder() {
      const appointmentId = this.$route.params.id;
      if (!appointmentId || !this.activeDiagnosticList.length) {
        return;
      }
      this.dxListReorderLoading = true;
      try {
        await Procedure.reorderAppointmentDiagnostics({
          appointment_id: appointmentId,
          diagnostic_group_id: this.activeDiagnosticGroupId
            ? Number(this.activeDiagnosticGroupId)
            : null,
          order: this.activeDiagnosticList.map((r) => r.id),
        });
      } catch (err) {
        console.error('Error saving diagnostic order:', err);
        this.$message.error('Could not save diagnostic order.');
        await this.getdiagnostics();
      } finally {
        this.dxListReorderLoading = false;
      }
    },
    getdiagnostics() {
      this.diagnostic_list = [];
      return Procedure.getAppointmentDiagnostics(this.$route.params.id)
        .then((response) => {
          const payload = response || {};
          this.diagnostic_groups = (payload.groups || []).map((g) => ({
            ...g,
            lab_remarks: g.lab_remarks || '',
            request_date: g.request_date || null,
            findings: g.findings || '',
            notes: g.notes || '',
            recommendations: g.recommendations || '',
          }));
          this.diagnostic_list = payload.diagnostics || payload.data || [];
          this.ensureActiveDiagnosticGroup();
          this.syncFormLabRemarksFromActiveGroup();
          this.initDxTableSortable();
        })
        .catch((err) => {
          console.error('Error adding suggestions:', err);
        });
    },
    ensureActiveDiagnosticGroup() {
      if (!this.diagnostic_groups.length) {
        this.activeDiagnosticGroupId = null;
        return;
      }
      const ids = this.diagnostic_groups.map((g) => String(g.id));
      if (
        !this.activeDiagnosticGroupId ||
        !ids.includes(String(this.activeDiagnosticGroupId))
      ) {
        this.activeDiagnosticGroupId = String(this.diagnostic_groups[0].id);
      }
    },
    getActiveDiagnosticGroupId() {
      this.ensureActiveDiagnosticGroup();
      return this.activeDiagnosticGroupId
        ? Number(this.activeDiagnosticGroupId)
        : null;
    },
    syncFormLabRemarksFromActiveGroup() {
      const group = this.activeDiagnosticGroup;
      if (group) {
        this.form.lab_remarks = group.lab_remarks || '';
      }
    },
    async saveActiveDiagnosticGroupMeta() {
      const group = this.activeDiagnosticGroup;
      if (!group || !group.id) {
        return;
      }
      try {
        await Procedure.updateDiagnosticGroup(group.id, {
          lab_remarks: group.lab_remarks || '',
          request_date: group.request_date || null,
          findings: group.findings || '',
          notes: group.notes || '',
          recommendations: group.recommendations || '',
        });
        this.syncFormLabRemarksFromActiveGroup();
      } catch (e) {
        this.$message.error('Could not save diagnostic group details.');
      }
    },
    onDiagnosticGroupTabClick() {
      this.saveActiveDiagnosticGroupMeta();
      this.syncFormLabRemarksFromActiveGroup();
    },
    async addDiagnosticGroup() {
      const appointmentId = this.$route.params.id;
      if (!appointmentId) {
        return;
      }
      this.dxGroupActionLoading = true;
      try {
        await this.saveActiveDiagnosticGroupMeta();
        const group = await Procedure.createDiagnosticGroup({
          appointment_id: appointmentId,
        });
        await this.getdiagnostics();
        if (group && group.id) {
          this.activeDiagnosticGroupId = String(group.id);
        }
        this.$message.success('Diagnostic group added.');
      } catch (e) {
        this.$message.error('Could not add diagnostic group.');
      } finally {
        this.dxGroupActionLoading = false;
      }
    },
    renameDiagnosticGroup(group) {
      if (!group || !group.id) {
        return;
      }
      this.$prompt('Diagnostic group name', 'Rename', {
        confirmButtonText: 'Save',
        cancelButtonText: 'Cancel',
        inputValue: group.title || '',
        inputPattern: /\S+/,
        inputErrorMessage: 'Name is required',
      })
        .then(async({ value }) => {
          const title = String(value || '').trim();
          if (!title) {
            return;
          }
          this.dxGroupActionLoading = true;
          try {
            await Procedure.updateDiagnosticGroup(group.id, { title });
            group.title = title;
            this.$message.success('Diagnostic group renamed.');
          } catch (e) {
            this.$message.error('Could not rename diagnostic group.');
          } finally {
            this.dxGroupActionLoading = false;
          }
        })
        .catch(() => {});
    },
    async deleteActiveDiagnosticGroup() {
      const gid = this.getActiveDiagnosticGroupId();
      if (!gid) {
        return;
      }
      const group = this.activeDiagnosticGroup;
      const title = group ? group.title : 'this group';
      try {
        await this.$confirm(
          `Delete "${title}" and all diagnostics in this group?`,
          'Delete diagnostic group',
          { type: 'warning', confirmButtonText: 'Delete', cancelButtonText: 'Cancel' }
        );
      } catch (e) {
        return;
      }
      this.dxGroupActionLoading = true;
      try {
        this.clearDxListSelection();
        await Procedure.deleteDiagnosticGroup(gid);
        await this.getdiagnostics();
        this.$message.success('Diagnostic group deleted.');
      } catch (e) {
        this.$message.error('Could not delete diagnostic group.');
      } finally {
        this.dxGroupActionLoading = false;
      }
    },
    handlePrintDxCommand(command) {
      if (command === 'all') {
        this.printAllDxGroups();
        return;
      }
      this.printCurrentDxGroup();
    },
    printCurrentDxGroup() {
      const gid = this.getActiveDiagnosticGroupId();
      if (!gid) {
        this.$message.warning('Select a diagnostic group first.');
        return;
      }
      this.printrequest(1, gid);
    },
    printAllDxGroups() {
      this.printrequest(1, 'all');
    },
    getservices() {
      this.services_list = [];
      Services.getAppointmentService(this.$route.params.id)
        .then((response) => {
          this.services_list = response.data;
        })
        .catch((err) => {
          console.error('Error adding suggestions:', err);
        });
    },
    openCompareDrawer() {
      if (!this.patientid_id) {
        this.$message.warning('Patient information is still loading.');
        return;
      }
      this.compareDrawerVisible = true;
      this.loadComparePastVisits();
    },
    loadComparePastVisits() {
      this.compareListLoading = true;
      Patients.getpatientpastconsult(this.patientid_id)
        .then((response) => {
          const rows = (response && response.data) || [];
          this.comparePastVisits = rows.filter(
            (v) => String(v.id) !== String(this.form.id)
          );
        })
        .catch((err) => {
          console.error('Error loading past consultations:', err);
          this.$message.error('Could not load previous visits.');
        })
        .finally(() => {
          this.compareListLoading = false;
        });
    },
    loadCompareVisit(id) {
      if (!id) {
        return;
      }
      this.compareLoading = true;
      this.resetCompareData();
      Promise.all([
        Patients.getAppointment(id),
        Medicine.getAppointmentMeds(id),
        Procedure.getAppointmentDiagnostics(id),
        Services.getAppointmentService(id),
      ])
        .then(([apt, meds, dx, services]) => {
          this.compareData.form = (apt && apt.data) || {};
          const medsPayload = meds || {};
          this.compareData.prescription_groups = medsPayload.groups || [];
          this.compareData.rx_list =
            medsPayload.medicines || medsPayload.data || [];
          const dxPayload = dx || {};
          this.compareData.diagnostic_groups = dxPayload.groups || [];
          this.compareData.diagnostic_list =
            dxPayload.diagnostics || dxPayload.data || [];
          this.compareData.services_list = (services && services.data) || [];
        })
        .catch((err) => {
          console.error('Error loading visit for comparison:', err);
          this.$message.error('Could not load the selected visit.');
        })
        .finally(() => {
          this.compareLoading = false;
        });
    },
    resetCompareData() {
      this.compareData = {
        form: {},
        rx_list: [],
        prescription_groups: [],
        diagnostic_list: [],
        diagnostic_groups: [],
        services_list: [],
      };
    },
    closeCompareDrawer() {
      this.compareDrawerVisible = false;
      this.compareSelectedId = null;
      this.resetCompareData();
    },
    printpdf(groupId = 'all') {
      const query =
        groupId && groupId !== 'all' ? `?group_id=${groupId}` : '';
      window.open('/api/printpdf/' + this.form.id + query);
    },
    printpdf2(groupId = 'all') {
      const query =
        groupId && groupId !== 'all' ? `?group_id=${groupId}` : '';
      window.open('/api/printpdf2/' + this.form.id + query);
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
    printrequest(type, groupId = 'all') {
      const query =
        groupId && groupId !== 'all' ? `?group_id=${groupId}` : '';
      window.open('/api/printrequest/' + this.form.id + '/' + type + query);
    },
    async printmedcert(type) {
      await this.ensureSavedBeforePrint();
      window.open('/api/printmedcert/' + this.form.id);
    },
    printfees() {
      window.open('/api/printfees/' + this.form.id);
    },
    printriskstrat(type) {
      window.open('/api/printriskstrat/' + this.form.id);
    },
    printfittowork(type) {
      window.open('/api/printfittowork/' + this.form.id);
    },
    printclearance(type) {
      window.open('/api/printclearance/' + this.form.id);
    },
    printreferral(type) {
      window.open('/api/printreferral/' + this.form.id);
    },
    async printform(type) {
      await this.ensureSavedBeforePrint();
      window.open('/api/printform/' + this.form.id);
    },
    doneConsult() {
      this.$confirm('Are you done with this consultation?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      })
        .then(() => {
          Patients.doneConsult(this.$route.params.id)
            .then((response) => {
              this.$message({
                type: 'success',
                message: 'Done consultation',
              });
            })
            .catch((err) => {
              console.error('Error adding suggestions:', err);
            });
        })
        .catch(() => {
          this.$message({
            type: 'info',
            message: 'Canceled action.',
          });
        });
    },
    vitalsHistoryRowClass({ row }) {
      return row.is_current ? 'vitals-history-row--current' : '';
    },
    parseVitalNumber(value) {
      if (value === null || value === undefined || value === '') {
        return null;
      }
      const n = parseFloat(String(value).replace(/[^\d.-]/g, ''));
      return Number.isFinite(n) ? n : null;
    },
    vitalReadingHasValues(reading) {
      if (!reading) {
        return false;
      }
      return !!(
        reading.vit_sys || reading.vit_dia || reading.weight ||
        reading.height || reading.bmi || reading.vit_temp ||
        reading.vit_cr || reading.vit_rr || reading.o2_stat
      );
    },
    syncVitalsAfterBpUpdate(entry) {
      if (!entry) {
        return;
      }
      this.vitals_today = (this.vitals_today || []).map((record) => ({
        ...record,
        is_latest: false,
      }));
      this.vitals_today.unshift({ ...entry, is_latest: true });

      const dayKey = entry.day_key;
      const dayReadings = (this.vitals_by_day[dayKey] || []).map((record) => ({
        ...record,
        is_latest: false,
      }));
      dayReadings.unshift({ ...entry, is_latest: true });
      this.$set(this.vitals_by_day, dayKey, dayReadings);

      const historyRow = {
        ...entry,
        reading_count: dayReadings.length,
        is_current: true,
        id: Number(this.form.id || this.$route.params.id),
      };
      const idx = this.vitals_records.findIndex((record) => record.day_key === dayKey);
      if (idx >= 0) {
        this.$set(this.vitals_records, idx, historyRow);
      } else {
        this.vitals_records.unshift(historyRow);
      }
    },
    isPeVitalAbnormal(type, reading = null) {
      const f = reading || this.form;
      switch (type) {
        case 'bp': {
          const sys = this.parseVitalNumber(f.vit_sys);
          const dia = this.parseVitalNumber(f.vit_dia);
          const sysAbnormal = sys !== null && (sys < 90 || sys > 140);
          const diaAbnormal = dia !== null && (dia < 60 || dia > 90);
          return sysAbnormal || diaAbnormal;
        }
        case 'temp': {
          const temp = this.parseVitalNumber(f.vit_temp);
          return temp !== null && (temp < 36 || temp > 37.5);
        }
        case 'hr': {
          const hr = this.parseVitalNumber(f.vit_cr);
          return hr !== null && (hr < 60 || hr > 100);
        }
        case 'rr': {
          const rr = this.parseVitalNumber(f.vit_rr);
          return rr !== null && (rr < 12 || rr > 20);
        }
        case 'o2': {
          const o2 = this.parseVitalNumber(f.o2_stat);
          return o2 !== null && o2 < 95;
        }
        case 'bmi': {
          const bmi = this.parseVitalNumber(f.bmi);
          return bmi !== null && (bmi < 18.5 || bmi >= 25);
        }
        default:
          return false;
      }
    },
    buildCurrentVitalsRow() {
      const sys = (this.form.vit_sys || '').toString().trim();
      const dia = (this.form.vit_dia || '').toString().trim();
      let bp = '';
      if (sys || dia) {
        bp = `${sys || '—'}/${dia || '—'}`;
      }
      return {
        id: Number(this.form.id || this.$route.params.id),
        is_current: true,
        date: this.currentDt(),
        date_sort: this.appointment_dt,
        bp,
        vit_sys: this.form.vit_sys,
        vit_dia: this.form.vit_dia,
        weight: this.form.weight,
        height: this.form.height,
        bmi: this.form.bmi,
        vit_temp: this.form.vit_temp,
        vit_cr: this.form.vit_cr,
        vit_rr: this.form.vit_rr,
        o2_stat: this.form.o2_stat,
      };
    },
    syncVitalsHistoryAfterUpdate() {
      const row = this.buildCurrentVitalsRow();
      if (!this.vitalReadingHasValues(row)) {
        return;
      }
      const dayKey = this.appointment_dt
        ? moment(this.appointment_dt).format('YYYY-MM-DD')
        : moment().format('YYYY-MM-DD');
      const entry = {
        id: `local-${Date.now()}`,
        appointment_id: Number(this.form.id || this.$route.params.id),
        recorded_at: new Date().toISOString(),
        time_display: moment().format('h:mm A'),
        date: row.date,
        date_sort: row.date_sort,
        day_key: dayKey,
        bp: row.bp,
        vit_sys: row.vit_sys,
        vit_dia: row.vit_dia,
        weight: row.weight,
        height: row.height,
        bmi: row.bmi,
        vit_temp: row.vit_temp,
        vit_cr: row.vit_cr,
        vit_rr: row.vit_rr,
        o2_stat: row.o2_stat,
        is_latest: true,
      };
      this.syncVitalsAfterBpUpdate(entry);
    },
    upDateBP() {
      this.$confirm('Are you done with this update?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
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
            bmi: this.form.bmi,
            o2_stat: this.form.o2_stat,
            id: this.$route.params.id,
          };
          Patients.update_bp(bp)
            .then((response) => {
              if (response && response.vitals_entry) {
                this.syncVitalsAfterBpUpdate(response.vitals_entry);
              } else {
                this.syncVitalsHistoryAfterUpdate();
              }
              this.$message({
                type: 'success',
                message: 'Done updating',
              });
            })
            .catch((err) => {
              console.error('Error adding suggestions:', err);
            });
        })
        .catch(() => {
          this.$message({
            type: 'info',
            message: 'Canceled action.',
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
          console.error('Error adding suggestions:', err);
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
          let { width, height } = img;

          if (width > maxWidth || height > maxHeight) {
            const ratio = Math.min(maxWidth / width, maxHeight / height);
            width *= ratio;
            height *= ratio;
          }

          canvas.width = width;
          canvas.height = height;

          ctx.drawImage(img, 0, 0, width, height);

          canvas.toBlob(
            (blob) => {
              const compressedFile = new File([blob], file.name, {
                type: 'image/jpeg',
                lastModified: Date.now(),
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
      formData.append('patientid', this.form_att.patientid);

      // Check network connection
      const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
      const isSlowConnection = connection && (connection.effectiveType === 'slow-2g' || connection.effectiveType === '2g');

      if (isSlowConnection) {
        this.$message.warning('Slow network detected. Upload may take longer...');
      }

      const totalFiles = this.form_att.files.length;
      let processedFiles = 0;

      for (let i = 0; i < this.form_att.files.length; i++) {
        const file = this.form_att.files[i];
        const extension = file.name.split('.').pop().toLowerCase();

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
        if (extension === 'heic' || extension === 'heif') {
          try {
            this.uploadStatus = `Converting HEIC file: ${file.name}`;
            const bmpBlob = await heic2any({
              blob: file,
              toType: 'image/bmp',
              quality: 0.9,
            });

            processedFile = new File(
              [bmpBlob],
              file.name.replace(/\.(heic|heif)$/i, '.jpg'),
              { type: 'image/bmp' }
            );
          } catch (error) {
            console.error('HEIC conversion failed:', error);
            this.$message.error('HEIC conversion failed.');
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
            console.error('Image compression failed:', error);
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

          this.form_att.file = '';
          this.get_attachments(this.form_att.patientid);
          this.$message.success('File uploaded successfully!');
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
          let errorMessage = 'Upload failed.';

          if (isNetworkError) {
            errorMessage = 'Network error. Please check your connection and try again.';
          } else if (err.response && err.response.data) {
            if (err.response.data.message) {
              errorMessage += ' ' + err.response.data.message;
            } else if (err.response.data.error) {
              errorMessage += ' ' + err.response.data.error;
            } else if (typeof err.response.data === 'string') {
              errorMessage += ' ' + err.response.data;
            }
          } else if (err.message) {
            errorMessage += ' ' + err.message;
          } else if (typeof err === 'string') {
            errorMessage += ' ' + err;
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
      this.$confirm('Are you done with this file?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      })
        .then(() => {
          Patients.deleteAttachments(id)
            .then((response) => {
              this.$message({
                type: 'success',
                message: 'Deleted File',
              });
              this.get_attachments(this.form_att.patientid);
            })
            .catch((err) => {
              console.error('Error adding suggestions:', err);
            });
        })
        .catch(() => {
          this.$message({
            type: 'info',
            message: 'Canceled action.',
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
      this.$refs['appForm'].validate((valid) => {
        if (valid) {
          this.isProcessing = true;
          Patients.cancel_appointment(this.form)
            .then((response) => {
              this.dialogFormVisible = false;
              this.$message({
                message: 'Appointment Cancelled',
                type: 'success',
                duration: 5 * 1000,
              });
              this.$router.push({ path: '/appointments/appointments' });
            })
            .catch((err) => {
              console.error('Error adding suggestions:', err);
            })
            .finally(() => {
              // This will always run, regardless of the request outcome
              this.isProcessing = false;
            });
          // }, 5000);
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    printChart() {
      window.open('/api/printchart/' + this.patientid_id);
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
      return a.split('.');
    },
    dateFormat(dt) {
      return moment(dt).format('MMMM D, YYYY');
    },
    viewFile(s, e) {
      this.isPdf = e == 'pdf';
      this.viewFileModel = true;
      this.sourceFile = s;
    },
    openRxTemplateDialog() {
      this.rxTemplateDialogVisible = true;
      this.rxTemplateSelectId = null;
      this.fetchRxTemplatesForDialog();
    },
    openDxTemplateDialog() {
      this.dxTemplateDialogVisible = true;
      this.dxTemplateSelectId = null;
      this.fetchDxTemplatesForDialog('');
    },
    openFormTemplateDialog() {
      this.formTemplateDialogVisible = true;
      this.formTemplateSelectId = null;
      this.formTemplateSearchKeyword = '';
      this.formTemplateFilterCategory = '';
      this.loadFormTemplateRecentFromStorage();
      this.fetchFormTemplateCategoriesForDialog();
      this.fetchFormTemplatesForDialog();
    },
    loadFormTemplateRecentFromStorage() {
      try {
        const raw = localStorage.getItem('formTemplateRecent');
        const parsed = raw ? JSON.parse(raw) : [];
        this.formTemplateRecent = Array.isArray(parsed) ? parsed.filter((x) => x && x.id) : [];
      } catch (e) {
        this.formTemplateRecent = [];
      }
    },
    rememberFormTemplateRecent(id, name) {
      const sid = Number(id);
      const nm = (name || 'Template').trim();
      const next = [{ id: sid, name: nm }];
      for (const r of this.formTemplateRecent) {
        if (next.length >= 8) {
          break;
        }
        if (r && Number(r.id) !== sid) {
          next.push({ id: Number(r.id), name: r.name || 'Template' });
        }
      }
      this.formTemplateRecent = next.slice(0, 8);
      try {
        localStorage.setItem('formTemplateRecent', JSON.stringify(this.formTemplateRecent));
      } catch (e) {
        /* ignore quota */
      }
    },
    formTemplateOptionLabel(t) {
      if (!t) {
        return '';
      }
      const cat = (t.category || '').trim();
      return cat ? `${t.name} — ${cat}` : t.name;
    },
    quickSelectFormTemplate(id) {
      this.formTemplateSelectId = id;
    },
    onFormTemplateSelectOpen(visible) {
      if (visible) {
        this.fetchFormTemplatesForDialog();
      }
    },
    async fetchFormTemplateCategoriesForDialog() {
      try {
        const res = await getFormTemplateCategories();
        this.formTemplateCategoryOptions = res.data || [];
      } catch (e) {
        this.formTemplateCategoryOptions = [];
      }
    },
    async fetchFormTemplatesForDialog() {
      this.formTemplateLoading = true;
      try {
        const res = await listFormTemplates({
          page: 1,
          limit: 400,
          keyword: (this.formTemplateSearchKeyword || '').trim(),
          category: (this.formTemplateFilterCategory || '').trim(),
        });
        this.formTemplateList = res.data || [];
      } catch (e) {
        this.$message.error('Could not load form templates.');
        this.formTemplateList = [];
      } finally {
        this.formTemplateLoading = false;
      }
    },
    async applyFormTemplate() {
      if (!this.formTemplateSelectId) {
        return;
      }
      this.formTemplateApplyLoading = true;
      try {
        const res = await getFormTemplate(this.formTemplateSelectId);
        const row = res.data || {};
        let html = row.content_html || '';
        html = replaceFormTemplatePlaceholders(html, buildFormTemplateContext(this));
        const existing = (this.form.form_content || '').trim();
        if (existing) {
          this.form.form_content = `${existing}<p><br></p>${html}`;
        } else {
          this.form.form_content = html;
        }
        await this.$nextTick();
        this.rememberFormTemplateRecent(row.id, row.name);
        this.tab = 'form';
        this.$message.success('Template loaded into the form editor.');
        this.formTemplateDialogVisible = false;
      } catch (e) {
        this.$message.error('Could not load template.');
      } finally {
        this.formTemplateApplyLoading = false;
      }
    },
    async fetchRxTemplatesForDialog() {
      this.rxTemplateLoading = true;
      try {
        const res = await listPrescriptionDiagnosisTemplates({
          page: 1,
          limit: 200,
          keyword: '',
        });
        this.rxTemplateList = res.data || [];
      } catch (e) {
        this.$message.error('Could not load prescription templates.');
      } finally {
        this.rxTemplateLoading = false;
      }
    },
    async fetchDxTemplatesForDialog(keyword) {
      const kw = (keyword || '').trim();
      this.dxTemplateLoading = true;
      try {
        const res = await listDiagnosticTemplates({
          page: 1,
          limit: 200,
          keyword: kw,
        });
        this.dxTemplateList = res.data || [];
      } catch (e) {
        this.$message.error('Could not load diagnostic templates.');
      } finally {
        this.dxTemplateLoading = false;
      }
    },
    qtyFromTemplateDuration(duration) {
      if (!duration) {
        return '1';
      }
      const m = String(duration).match(/\d+/);
      return m ? m[0] : '1';
    },
    buildRxPayloadFromTemplateItem(item) {
      const parts = [];
      if (item.instructions) {
        parts.push(`${item.instructions}`);
      }
      const remarks = parts.join(' | ');
      const hasMaster = item.medicine_id != null && item.medicine_id !== 0;
      const brand = (item.brand_name || '').trim();
      const generic = (item.generic_name || '').trim();
      const groupId = this.getActivePrescriptionGroupId();
      const meal = appointmentMealTimingStringsFromFrequency(item.frequency);

      if (hasMaster) {
        return {
          id: this.$route.params.id,
          prescription_group_id: groupId,
          custom_meds: false,
          custom_generic: generic,
          custom_brand: brand,
          custom_dosage: (item.dosage || '').trim(),
          qty: (item.quantity || '').trim() || this.qtyFromTemplateDuration(item.duration),
          ...meal,
          meds: brand,
          remarks,
          med_id: item.medicine_id,
        };
      }

      return {
        id: this.$route.params.id,
        prescription_group_id: groupId,
        custom_meds: true,
        custom_generic: generic || brand,
        custom_brand: brand || generic,
        custom_dosage: (item.dosage || '').trim(),
        qty: (item.quantity || '').trim() || this.qtyFromTemplateDuration(item.duration),
        ...meal,
        meds: '',
        remarks,
        med_id: 0,
      };
    },
    async applyRxTemplate() {
      if (!this.rxTemplateSelectId) {
        return;
      }
      this.rxTemplateApplyLoading = true;
      try {
        const res = await getPrescriptionDiagnosisTemplate(this.rxTemplateSelectId);
        const items = (res.data && res.data.items) || [];
        if (!items.length) {
          this.$message.warning('This template has no medications.');
          return;
        }
        for (const item of items) {
          await Medicine.add_rx(this.buildRxPayloadFromTemplateItem(item));
        }
        this.getmeds();
        this.$message.success('Template applied to prescription.');
        this.rxTemplateDialogVisible = false;
      } catch (e) {
        this.$message.error('Could not apply template.');
      } finally {
        this.rxTemplateApplyLoading = false;
      }
    },
    buildDiagnosticsLookup() {
      const lists = [
        this.getAllDiagnosticsOfferedHema,
        this.getAllDiagnosticsOfferedChem,
        this.getAllDiagnosticsOfferedBleed,
        this.getAllDiagnosticsOfferedCardiac,
        this.getAllDiagnosticsOfferedCardiacTest,
        this.getAllDiagnosticsOfferedXray,
        this.getAllDiagnosticsOfferedCt,
        this.getAllDiagnosticsOfferedMri,
        this.getAllDiagnosticsOfferedUtz,
        this.getAllDiagnosticsOfferedVascular,
        this.getAllDiagnosticsOfferedOth,
        this.getAllDiagnosticsOfferedImmonulogy,
        this.getAllDiagnosticsOfferedMirco,
        this.getAllDiagnosticsOfferedCrystal,
        this.getAllDiagnosticsOfferedMicroscopy,
      ];
      const all = [];
      lists.forEach((x) => {
        if (Array.isArray(x)) {
          all.push(...x);
        }
      });
      const byName = new Map();
      all.forEach((t) => {
        const name = (t && t.lab_test) ? String(t.lab_test).trim() : '';
        if (!name) {
          return;
        }
        if (!byName.has(name.toLowerCase())) {
          byName.set(name.toLowerCase(), t);
        }
      });
      return byName;
    },
    async applyDxTemplate() {
      if (!this.dxTemplateSelectId) {
        return;
      }
      this.dxTemplateApplyLoading = true;
      try {
        const res = await getDiagnosticTemplate(this.dxTemplateSelectId);
        const items = (res.data && res.data.items) || [];
        const activeItems = items.filter((x) => x && x.active !== false);
        if (!activeItems.length) {
          this.$message.warning('This template has no active diagnostic items.');
          return;
        }

        const lookup = this.buildDiagnosticsLookup();
        const existingIds = new Set(
          (this.activeDiagnosticList || []).map((r) => String(r.ancillary_id))
        );
        const existingCustom = new Set(
          (this.activeDiagnosticList || []).map((r) => (r.diagnostic || '').trim().toLowerCase())
        );

        const toAdd = [];
        let added = 0;
        for (const item of activeItems) {
          const name = (item.diagnostic_name || '').trim();
          if (!name) {
            continue;
          }
          const match = lookup.get(name.toLowerCase());

          if (match && match.lab_test_id != null) {
            const pid = String(match.lab_test_id);
            if (existingIds.has(pid)) {
              continue;
            }
            if (!this.diagnosticsRenderedModel.includes(match.lab_test)) {
              this.diagnosticsRenderedModel.push(match.lab_test);
            }
            const row = {
              id: this.$route.params.id,
              procedure_id: match.lab_test_id,
              procedure: match.lab_test,
              remarks: (item.notes || '') || '',
              type: match.lab_category_id,
              lab_micro_remarks: this.lab_micro_remarks,
              xray_remarks: '',
            };
            this.diagnosticsRendered.rendered.push(row);
            toAdd.push(row);
            existingIds.add(pid);
            added++;
            continue;
          }

          const key = name.toLowerCase();
          if (existingCustom.has(key)) {
            continue;
          }
          if (!this.diagnosticsRenderedModel.includes(name)) {
            this.diagnosticsRenderedModel.push(name);
          }
          const row = {
            id: this.$route.params.id,
            procedure_id: 0,
            procedure: name,
            remarks: (item.notes || '') || '',
            type: 1,
            lab_micro_remarks: this.lab_micro_remarks,
            xray_remarks: '',
          };
          this.diagnosticsRendered.rendered.push(row);
          toAdd.push(row);
          existingCustom.add(key);
          added++;
        }

        if (!added) {
          this.$message.info('All template items are already loaded.');
          return;
        }

        await Procedure.add_diagnostic({
          diagnostic_group_id: this.getActiveDiagnosticGroupId(),
          rendered: toAdd,
        });
        this.getdiagnostics();

        // Clear the selection buffer, since items are already saved.
        this.diagnosticsRendered.rendered = [];
        this.diagnosticsRenderedModel = [];
        this.form.lab_others = null;
        this.form.anc_others = null;

        this.$message.success(`Added ${added} diagnostic item(s) from template.`);
        this.dxTemplateDialogVisible = false;
      } catch (e) {
        this.$message.error('Could not apply diagnostic template.');
      } finally {
        this.dxTemplateApplyLoading = false;
      }
    },
    rxPastRowKey(row) {
      return String(row.appointment_id);
    },
    formatPastRxDate(dt) {
      if (!dt) {
        return '—';
      }
      const m = moment(dt);
      return m.isValid() ? m.format('MMM D, YYYY') : String(dt);
    },
    medicationLineLabel(m) {
      const g = (m.generic_name || '').trim();
      const b = (m.medicine || '').trim();
      if (g && b) {
        return `${g} ${b}`;
      }
      return g || b || '—';
    },
    pastRxPreview(medications) {
      const list = Array.isArray(medications) ? medications : [];
      if (!list.length) {
        return '—';
      }
      const parts = list.slice(0, 3).map((m) => this.medicationLineLabel(m));
      const extra = list.length > 3 ? ` (+${list.length - 3} more)` : '';
      return parts.join(', ') + extra;
    },
    async openRxPastPrescriptionDialog() {
      const pid = this.form_att.patientid || this.patientid_id;
      if (!pid) {
        this.$message.warning('Patient information is still loading. Try again in a moment.');
        return;
      }
      this.rxPastDialogVisible = true;
      this.rxPastSearch = '';
      this.rxPastLoading = true;
      try {
        const res = await Patients.getPatientPastPrescriptions(pid, this.$route.params.id);
        this.rxPastRows = (res && res.data) || [];
      } catch (e) {
        this.rxPastRows = [];
      } finally {
        this.rxPastLoading = false;
        this.$nextTick(() => {
          if (this.$refs.rxPastTable) {
            this.$refs.rxPastTable.bodyWrapper.scrollTop = 0;
          }
        });
      }
    },
    togglePastRxExpand(row) {
      if (this.$refs.rxPastTable) {
        this.$refs.rxPastTable.toggleRowExpansion(row);
      }
    },
    buildRxPayloadFromPastRx(rx) {
      const custom = !rx.medicine_id || rx.medicine_id === 0;
      const generic = String(rx.generic_name || '').trim();
      const brand = String(rx.medicine || '').trim();
      return {
        id: this.$route.params.id,
        prescription_group_id: this.getActivePrescriptionGroupId(),
        custom_meds: custom,
        med_id: custom ? 0 : rx.medicine_id,
        meds: custom ? '' : brand,
        custom_generic: generic,
        custom_brand: brand,
        custom_dosage: '',
        qty: rx.qty != null ? String(rx.qty) : '',
        bf_b: this.rxMedicineStringOrEmpty(rx.bf_b),
        bf_a: this.rxMedicineStringOrEmpty(rx.bf_a),
        l_b: this.rxMedicineStringOrEmpty(rx.l_b),
        l_a: this.rxMedicineStringOrEmpty(rx.l_a),
        s_b: this.rxMedicineStringOrEmpty(rx.s_b),
        s_a: this.rxMedicineStringOrEmpty(rx.s_a),
        bt: this.rxMedicineStringOrEmpty(rx.bt),
        remarks: rx.remarks || '',
      };
    },
    async clearAllCurrentRx() {
      const ids = (this.rx_list || []).map((r) => r.id);
      for (const id of ids) {
        await Medicine.remove_rx(id);
      }
    },
    async usePastPrescription(row) {
      if (!row.medications || !row.medications.length) {
        this.$message.warning('This visit has no medications to copy.');
        return;
      }
      const hasExisting = this.rx_list && this.rx_list.length > 0;
      let mode = 'merge';
      if (hasExisting) {
        try {
          await this.$confirm(
            'You already have medications on this appointment. Replace all clears the current list and copies only the selected past prescription. Merge adds these medications alongside current ones.',
            'Apply past prescription',
            {
              confirmButtonText: 'Replace all',
              cancelButtonText: 'Merge with current',
              type: 'warning',
            }
          );
          mode = 'replace';
        } catch (action) {
          if (action !== 'cancel') {
            return;
          }
          mode = 'merge';
        }
      }
      this.rxPastUseLoading = true;
      try {
        if (mode === 'replace') {
          await this.clearAllCurrentRx();
        }
        for (const rx of row.medications) {
          await Medicine.add_rx(this.buildRxPayloadFromPastRx(rx));
        }
        await this.getmeds();
        this.$message.success('Past prescription applied.');
        this.rxPastDialogVisible = false;
      } catch (e) {
        this.$message.error('Could not apply prescription.');
      } finally {
        this.rxPastUseLoading = false;
      }
    },
    importMedicine() {
      this.$confirm('Are you sure you want to import last prescriptions?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      })
        .then(() => {
          Patients.ImportMedicine(this.form_att.patientid, this.$route.params.id)
            .then((response) => {
              this.getmeds();
            })
            .catch((err) => {
              console.error('Error adding suggestions:', err);
            });
        })
        .catch(() => {
          this.$message({
            type: 'info',
            message: 'Delete canceled',
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
.rx-template-dialog__hint {
  font-size: 13px;
  color: #606266;
  line-height: 1.5;
  margin: 0 0 12px;
}

.rx-fav-dialog__hint {
  font-size: 13px;
  color: #606266;
  line-height: 1.5;
  margin: 0 0 12px;
}

.rx-fav-meds-dropdown {
  .rx-ac-section-header {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #909399;
    padding: 2px 0 6px;
    pointer-events: none;
  }

  .rx-ac-suggestion-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
    width: 100%;
  }

  .rx-ac-suggestion-row--fav {
    background: linear-gradient(90deg, rgba(230, 162, 60, 0.14), transparent);
    margin: 0 -20px;
    padding: 4px 20px;
  }

  .rx-ac-suggestion-main {
    flex: 1;
    min-width: 0;
  }

  .rx-ac-name {
    font-weight: 500;
    color: #303133;
  }

  .rx-ac-meta {
    display: block;
    font-size: 12px;
    color: #909399;
    margin-top: 2px;
  }

  .rx-ac-star {
    flex-shrink: 0;
    cursor: pointer;
    padding: 2px 0 6px 4px;
    font-size: 16px;
    color: #e6a23c;
  }

  .rx-ac-star.el-icon-star-off {
    color: #c0c4cc;
  }
}

.rx-past-dialog .el-dialog__body {
  padding-top: 8px;
}

.rx-past-dialog__toolbar {
  margin-bottom: 12px;
}

.rx-past-dialog__search {
  max-width: 440px;
}

.rx-past-dialog__body-wrap {
  min-height: 100px;
}

.rx-past-dialog__inner-table {
  margin: 4px 0 8px;
}

.diagnostics-select-dialog .el-dialog__body {
  max-height: calc(100vh - 180px);
  overflow-y: auto;
  padding-top: 12px;
}

.diagnostics-select-dialog__search {
  width: 100%;
  margin-bottom: 16px;
}

.diagnostics-select-dialog__search,
.diagnostics-remark-input {
  width: 100%;
}

.diagnostics-remark-input {
  max-width: 400px;
  margin-top: 6px;
  display: block;
}

.diagnostics-select-dialog .el-checkbox {
  display: flex;
  align-items: flex-start;
  white-space: normal;
  margin-bottom: 8px;
}

.diagnostics-select-dialog .el-checkbox__label {
  white-space: normal;
  line-height: 1.4;
}

.diagnostics-synovial-extra {
  margin-top: 15px;
  padding-left: 20px;
  border-left: 3px solid #409EFF;
}

.diagnostics-synovial-extra__title {
  margin-bottom: 10px;
  font-weight: bold;
  color: #409EFF;
}

@media (max-width: 768px) {
  .diagnostics-select-dialog.el-dialog {
    width: 95% !important;
    margin-top: 2vh !important;
  }

  .diagnostics-select-dialog .el-dialog__body {
    max-height: calc(100vh - 120px);
    padding: 12px 16px;
  }

  .diagnostics-remark-input {
    max-width: 100%;
  }

  .diagnostics-select-dialog__footer .el-button {
    width: 100%;
  }
}

.search-medicine-item {
  width: 100%;
}

.search-medicine-item .el-form-item__content {
  width: 100%;
}

.search-medicine-autocomplete {
  width: 100% !important;
  max-width: 100%;
}

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
.compare-drawer {
  padding: 0 16px 24px;
}

.compare-drawer__controls {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
  margin-bottom: 16px;
}

.compare-drawer__label {
  font-weight: 600;
}

.compare-drawer__empty {
  color: #909399;
  font-size: 13px;
}

.compare-columns {
  align-items: stretch;
}

.compare-column {
  height: 100%;
}

.compare-column__header {
  display: flex;
  flex-direction: column;
}

.compare-column__subtitle {
  color: #909399;
  font-size: 12px;
  margin-top: 2px;
}

.compare-section {
  margin-bottom: 18px;
}

.compare-section h4 {
  margin: 0 0 8px;
  padding-bottom: 4px;
  border-bottom: 1px solid #ebeef5;
  color: #303133;
}

.compare-field {
  margin-bottom: 8px;
}

.compare-field label {
  display: block;
  font-size: 12px;
  font-weight: 600;
  color: #606266;
}

.compare-field p {
  margin: 2px 0 0;
  white-space: pre-wrap;
  word-break: break-word;
}

.compare-vitals {
  display: flex;
  flex-wrap: wrap;
  gap: 6px 16px;
  font-size: 13px;
}

.pe-latest-vitals {
  margin-top: 12px;
  padding: 10px 12px;
  background: #f5f7fa;
  border: 1px solid #e4e7ed;
  border-radius: 4px;
}

.pe-latest-vitals__header {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 8px;
  font-size: 13px;
  color: #606266;
}

.pe-latest-vitals__title {
  font-weight: 600;
  color: #303133;
}

.pe-latest-vitals__date {
  margin-left: auto;
  font-size: 12px;
  color: #909399;
}

.pe-latest-vitals__time {
  font-size: 12px;
  color: #409eff;
  font-weight: 500;
}

.pe-latest-vitals__toggle {
  margin-top: 8px;
  padding: 0;
  border: 0;
  background: none;
  color: #409eff;
  font-size: 12px;
  cursor: pointer;
}

.pe-latest-vitals__toggle:hover {
  text-decoration: underline;
}

.pe-latest-vitals__more {
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px dashed #dcdfe6;
}

.pe-latest-vitals__reading {
  margin-bottom: 10px;
}

.pe-latest-vitals__reading:last-child {
  margin-bottom: 0;
}

.pe-latest-vitals__reading-time {
  font-size: 12px;
  font-weight: 600;
  color: #606266;
  margin-bottom: 6px;
}

.pe-latest-vitals--inline {
  margin-top: 0;
  background: transparent;
  border: 0;
  padding: 0;
}

.vitals-today-card {
  margin-bottom: 16px;
}

.vitals-history-expand {
  padding: 8px 12px 12px;
}

.vitals-history-expand__title {
  margin: 0 0 8px;
  font-size: 13px;
  font-weight: 600;
  color: #606266;
}

.vitals-history-expand__empty {
  margin: 0;
  padding: 8px 12px;
  font-size: 12px;
  color: #909399;
}

.pe-latest-vitals__empty {
  margin: 0;
  font-size: 13px;
  color: #909399;
}

.pe-latest-vitals__abnormal {
  color: #f56c6c;
  font-weight: 600;
}

.compare-empty {
  color: #909399;
  font-size: 13px;
  padding: 6px 0;
}

.meds-remarks-form::v-deep .el-form-item {
  width: 100%;
  margin-right: 0 !important;
}

.meds-remarks-form::v-deep .el-form-item__content {
  width: 100%;
}

.iframe-wrapper {
  text-align: center;
  padding: 10px;
}

.att-gallery {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 12px;
}

.att-tile {
  border: 1px solid #ebeef5;
  border-radius: 10px;
  overflow: hidden;
  background: #fff;
}

.att-tile__media {
  position: relative;
  height: 140px;
  background: #f5f7fa;
}

.att-tile__image {
  width: 100%;
  height: 100%;
}

.att-tile__file {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #606266;
  user-select: none;
}

.att-tile__fileext {
  margin-top: 6px;
  font-size: 12px;
  letter-spacing: 0.5px;
  color: #909399;
}

.att-tile__delete {
  position: absolute;
  top: 8px;
  right: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.18);
}

.att-tile__meta {
  padding: 10px 10px 12px;
}

.att-tile__name {
  font-size: 13px;
  font-weight: 600;
  color: #303133;
  line-height: 1.2;
  display: -webkit-box;
  line-clamp: 2;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  min-height: 32px;
}

.att-tile__date {
  margin-top: 6px;
  font-size: 12px;
  color: #909399;
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

.medcert-field-hint {
  margin: 0 0 8px;
  font-size: 12px;
  color: #909399;
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

.vitals-history-card {
  margin-top: 16px;
}

.vitals-history-empty {
  margin: 0;
  color: #909399;
  font-size: 14px;
}

.vitals-history-table >>> .vitals-history-row--current > td {
  background-color: #ecf5ff !important;
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
.action-toolbar-wrap {
  position: relative;
}

.action-toolbar-sentinel {
  height: 1px;
  width: 100%;
  pointer-events: none;
}

.action-toolbar {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
  border-radius: 12px;
  margin-bottom: 20px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.action-toolbar.is-pinned {
  position: fixed;
  top: 12px;
  z-index: 100;
  margin-bottom: 0;
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
    padding: 10px 12px;
  }

  .action-buttons {
    flex-direction: row;
    flex-wrap: nowrap;
    overflow-x: auto;
    align-items: center;
  }

  .action-btn {
    width: auto;
    flex-shrink: 0;
    margin-bottom: 0;
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

/* Single scrollable form layout */
.appointment-form-page {
  width: 100%;
}

.appointment-form-container {
  max-width: none;
  margin: 0;
  padding: 0 12px;
  flex: 1 1 auto;
  width: 100%;
  min-width: 0;
}

.appointment-form-layout {
  display: flex;
  align-items: flex-start;
  gap: 16px;
}

.appointment-form-sidebar {
  width: 280px;
  flex: 0 0 280px;
  position: sticky;
  top: 12px;
  align-self: flex-start;
}

.appointment-sidebar-menu {
  border: 1px solid #ebeef5;
  border-radius: 8px;
}

.appointment-sidebar-menu .el-menu-item.is-active {
  font-weight: 700;
}

.sidebar-check {
  float: right;
  color: #67c23a;
  margin-top: 12px;
}

.mobile-section-nav {
  max-width: none;
  margin: 0;
  padding: 0 12px;
}

.section-card {
  border: 1px solid #ebeef5;
  width: 100%;
}

.section-card__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 10px 12px;
}

.section-card__title {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
}

.form-bottom-actions {
  display: flex;
  justify-content: flex-end;
}

.mobile-sticky-actions {
  position: fixed;
  left: 0;
  right: 0;
  bottom: 0;
  padding: 12px;
  background: rgba(255, 255, 255, 0.96);
  border-top: 1px solid #ebeef5;
  z-index: 2000;
}

.mobile-sticky-actions__btn {
  width: 100%;
}

@media (max-width: 768px) {
  .appointment-form-container {
    padding-bottom: 84px; /* space for sticky actions */
  }
}

/* Prescription meal timing: compact grid + filled-state highlight */
.rx-meal-timing-inputs .el-form-item {
  margin-bottom: 8px;
}

.rx-meal-timing-inputs .el-form-item__label {
  line-height: 1.2;
  padding-bottom: 2px;
  font-size: 12px;
}

.rx-dose-input--filled .el-input__inner {
  background-color: #f0f9eb;
  border-color: #c2e7b0;
  font-weight: 600;
}

.rx-med-table .rx-timing-dose-filled {
  display: inline-block;
  min-width: 1.25em;
  padding: 2px 6px;
  border-radius: 4px;
  background: #ecf5ff;
  color: #1f5896;
  font-weight: 600;
  font-size: 12px;
}

.rx-drag-handle {
  cursor: grab;
  color: #909399;
  font-size: 16px;
  line-height: 1;
  user-select: none;
}

.rx-drag-handle:active {
  cursor: grabbing;
}

.rx-prescription-groups-bar {
  margin-bottom: 12px;
}

.rx-prescription-groups-tabs-wrap {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-start;
  gap: 8px;
}

.rx-prescription-tabs {
  flex: 1 1 280px;
  min-width: 0;
}

.rx-prescription-tabs .el-tabs__header {
  margin-bottom: 0;
}

.rx-prescription-tab-label {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.rx-prescription-tab-edit {
  font-size: 12px;
  color: #909399;
  cursor: pointer;
}

.rx-prescription-tab-edit:hover {
  color: #409eff;
}

.rx-prescription-add-btn,
.rx-prescription-delete-btn {
  flex-shrink: 0;
  margin-top: 4px;
}

.rx-med-row-ghost {
  opacity: 0.45;
  background: #ecf5ff !important;
}
</style>
