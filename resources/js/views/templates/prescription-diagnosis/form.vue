<template>
  <div class="app-container rx-dx-tpl-form-page">
    <el-card class="page-header-card" shadow="never">
      <div class="page-header">
        <div>
          <h2 class="page-title">{{ isEdit ? 'Edit template' : 'New template' }}</h2>
          <p class="page-subtitle">
            Link a diagnosis to a medication list. Use <strong>Add order</strong> to add medicines from the master list
            or as custom (not carried) entries—the same flow as appointment prescriptions.
          </p>
          <el-alert
            class="page-context-alert"
            type="info"
            :closable="false"
            show-icon
            title="Rich formatted clinic forms use Form templates instead of this screen."
          >
            <p class="alert-body">
              For bold text, headings, coloured text, spacing, checklist lines, aligned blocks, tables, links, images, and
              placeholders (<code v-pre>{{patient_name}}</code>,
              <code v-pre>{{date}}</code>, …),
              create or edit
              <router-link :to="{ name: 'FormTemplates' }"><strong>Form templates</strong></router-link>.
              Saved HTML loads into the appointment
              <strong>Form</strong> tab with formatting preserved.
            </p>
          </el-alert>
        </div>
        <div class="header-actions">
          <el-button @click="goBack">Cancel</el-button>
          <el-button type="primary" :loading="saving" @click="submit">
            Save
          </el-button>
        </div>
      </div>
    </el-card>

    <el-form ref="formRef" :model="form" :rules="rules" label-position="top">
      <el-card shadow="never" class="mb-3">
        <div slot="header" class="card-header-title">
          <span>Diagnosis</span>
        </div>
        <el-form-item label="Diagnosis name" prop="diagnosis_name">
          <el-autocomplete
            v-model="form.diagnosis_name"
            class="w-100"
            :fetch-suggestions="fetchDiagnosisSuggestions"
            placeholder="e.g. Acute Gastroenteritis"
            clearable
            @select="onDiagnosisSelect"
          />
        </el-form-item>
      </el-card>

      <el-card shadow="never">
        <div slot="header" class="card-header-title card-header-row">
          <span>Medications</span>
          <div class="card-header-actions">
            <el-button size="small" plain @click="openTplFavoritesDialog">
              <i class="el-icon-star-on" style="margin-right: 4px" />
              Favorites
            </el-button>
            <el-button type="primary" size="small" icon="el-icon-plus" plain @click="openMedDialog">
              Add order
            </el-button>
          </div>
        </div>

        <el-table :data="form.items" border class="med-table rx-med-table" empty-text="Add at least one medication with Add order">
          <el-table-column type="index" label="#" width="50" align="center" />
          <el-table-column label="Medicine" min-width="280">
            <template slot-scope="scope">
              <span>{{ displayMedicineName(scope.row) }}</span>
              <div class="drug-source-row">
                <el-tag
                  v-if="scope.row.medicine_id"
                  type="success"
                  size="mini"
                  effect="plain"
                >
                  Master list
                </el-tag>
                <el-tag
                  v-else-if="scope.row.custom_meds"
                  type="info"
                  size="mini"
                  effect="plain"
                >
                  Not carried
                </el-tag>
              </div>
            </template>
          </el-table-column>
          <el-table-column prop="qty" label="Qty" width="72" align="center" />
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
          <el-table-column label="Bedtime" width="72" align="center">
            <template slot-scope="scope">
              <span :class="{ 'rx-timing-dose-filled': rxTimingDoseFilled(scope.row.bt) }">{{ scope.row.bt }}</span>
            </template>
          </el-table-column>
          <el-table-column prop="instructions" label="Remarks" min-width="180" show-overflow-tooltip />
          <el-table-column label="" width="120" align="center" fixed="right">
            <template slot-scope="scope">
              <el-button
                type="primary"
                icon="el-icon-edit"
                size="mini"
                plain
                @click="editMedRow(scope.$index)"
              />
              <el-button
                type="danger"
                icon="el-icon-delete"
                size="mini"
                plain
                @click="removeMedRow(scope.$index)"
              />
            </template>
          </el-table-column>
        </el-table>
      </el-card>
    </el-form>

    <el-dialog
      :title="isMedEditMode ? 'Edit order' : 'Add order'"
      :visible.sync="medDialogVisible"
      width="90%"
      top="4vh"
      class="rx-order-dialog"
      :close-on-click-modal="false"
      append-to-body
      @close="onMedDialogClose"
    >
      <el-form :inline="true" label-position="top" class="demo-form-inline" style="width: 100%;">
        <el-row :gutter="24">
          <el-col :xs="24" :sm="24" :md="24">
            <el-checkbox v-model="medsArr.custom_meds" label="Not carried" size="large" />
          </el-col>
          <el-col :xs="24" :sm="6" :md="4" :lg="3">
            <el-form-item label="Quantity">
              <el-input v-model="medsArr.qty" autosize clearable />
            </el-form-item>
          </el-col>
          <el-col v-if="!medsArr.custom_meds" :xs="24" :sm="18" :md="20" :lg="21">
            <el-form-item label="Search Medicine" class="search-medicine-item">
              <el-autocomplete
                v-model="medsArr.meds"
                value-key="medicine"
                :fetch-suggestions="querySearch"
                popper-class="my-autocomplete rx-fav-meds-dropdown"
                placeholder="Please input"
                class="search-medicine-autocomplete"
                style="width: 100%"
                @select="handleSelect"
              >
                <template slot-scope="{ item }">
                  <div v-if="item.isSectionHeader" class="rx-ac-section-header">{{ item.sectionLabel }}</div>
                  <div
                    v-else
                    class="rx-ac-suggestion-row"
                    :class="{ 'rx-ac-suggestion-row--fav': item.isFavoriteRow }"
                  >
                    <div class="rx-ac-suggestion-main">
                      <span class="rx-ac-name">{{ item.medicine }}</span>
                      <span v-if="item.generic_name" class="rx-ac-meta">{{ item.generic_name }}</span>
                    </div>
                    <i
                      class="rx-ac-star"
                      :class="(item.favoriteId || item.isFavoriteRow) ? 'el-icon-star-on' : 'el-icon-star-off'"
                      @click.stop="toggleTplFavoriteStar(item)"
                    />
                  </div>
                </template>
              </el-autocomplete>
            </el-form-item>
          </el-col>
          <el-col v-if="medsArr.custom_meds" :xs="24" :sm="12" :md="12" :lg="4">
            <el-form-item label="Generic Name">
              <el-input v-model="medsArr.custom_generic" autosize clearable />
            </el-form-item>
          </el-col>
          <el-col v-if="medsArr.custom_meds" :xs="24" :sm="12" :md="12" :lg="4">
            <el-form-item label="Brand Name">
              <el-input v-model="medsArr.custom_brand" autosize clearable />
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
        <el-button @click="closeMedDialog">Cancel</el-button>
        <el-button type="success" @click="addOrUpdateMed">
          {{ isMedEditMode ? 'Update' : 'Add' }}
        </el-button>
      </span>
    </el-dialog>

    <el-dialog
      title="Favorite medicines"
      :visible.sync="tplFavDialogVisible"
      width="720px"
      top="8vh"
      :close-on-click-modal="false"
      append-to-body
    >
      <p class="tpl-fav-dialog__hint">
        Select favorites to add to this template. You can edit them in the table after adding.
      </p>
      <el-table
        ref="tplFavoritesTable"
        :data="favoriteMedicinesCache"
        max-height="420"
        border
        size="small"
        empty-text="No favorites yet. Use the star in the drug search or next to a row."
        @selection-change="handleTplFavoritesSelectionChange"
      >
        <el-table-column type="selection" width="48" align="center" />
        <el-table-column prop="drug_name" label="Medicine" min-width="200" show-overflow-tooltip />
        <el-table-column prop="default_qty" label="Qty" width="80" show-overflow-tooltip />
        <el-table-column label="Timing" min-width="140" show-overflow-tooltip>
          <template slot-scope="{ row }">
            {{ timingFavoriteColumn(row.default_frequency) }}
          </template>
        </el-table-column>
      </el-table>
      <span slot="footer" class="dialog-footer">
        <el-button @click="tplFavDialogVisible = false">Cancel</el-button>
        <el-button
          type="primary"
          :loading="tplFavApplyLoading"
          :disabled="!tplFavSelection.length"
          @click="applyTplFavoritesSelection"
        >
          Add selected
        </el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
import Medicine from '@/api/medicine';
import {
  getPrescriptionDiagnosisTemplate,
  createPrescriptionDiagnosisTemplate,
  updatePrescriptionDiagnosisTemplate,
  diagnosisNameSuggestions,
} from '@/api/prescriptionDiagnosisTemplate';
import { listFavoriteMedicines, createFavoriteMedicine, deleteFavoriteMedicine } from '@/api/favoriteMedicine';
import {
  appointmentMealTimingStringsFromFrequency,
  serializeTimingFromAppointmentMealStrings,
  displayStoredTimingOrLegacy,
} from '@/utils/medicationTemplateTiming';

function emptyMedsArr() {
  return {
    custom_meds: false,
    qty: '',
    bf_b: '',
    bf_a: '',
    l_b: '',
    l_a: '',
    s_b: '',
    s_a: '',
    bt: '',
    meds: '',
    med_id: 0,
    master_generic: '',
    remarks: '',
    custom_generic: '',
    custom_brand: '',
  };
}

function emptyItem() {
  return {
    _uid: 0,
    custom_meds: false,
    brand_name: '',
    generic_name: '',
    medicine_id: null,
    qty: '',
    bf_b: '',
    bf_a: '',
    l_b: '',
    l_a: '',
    s_b: '',
    s_a: '',
    bt: '',
    instructions: '',
  };
}

export default {
  name: 'PrescriptionDiagnosisTemplateForm',
  data() {
    return {
      saving: false,
      favoriteMedicinesCache: [],
      tplFavDialogVisible: false,
      tplFavApplyLoading: false,
      tplFavSelection: [],
      medDialogVisible: false,
      isMedEditMode: false,
      editingMedIndex: null,
      medUidCounter: 0,
      medsArr: emptyMedsArr(),
      form: {
        diagnosis_name: '',
        items: [],
      },
      rules: {
        diagnosis_name: [{ required: true, message: 'Diagnosis is required', trigger: 'blur' }],
      },
    };
  },
  computed: {
    isEdit() {
      return Boolean(this.$route.params.id);
    },
  },
  created() {
    this.loadFavoriteMedicinesList();
    if (this.isEdit) {
      this.load();
    }
  },
  methods: {
    goBack() {
      this.$router.push({ name: 'PrescriptionDiagnosisTemplates' });
    },
    async loadFavoriteMedicinesList() {
      try {
        const res = await listFavoriteMedicines();
        this.favoriteMedicinesCache = (res && res.data) || [];
      } catch (e) {
        this.favoriteMedicinesCache = [];
      }
    },
    findFavoriteMetaForTpl(medicineId) {
      if (!medicineId) {
        return { favoriteId: null, favoriteRecord: null };
      }
      const f = (this.favoriteMedicinesCache || []).find((x) => x.medicine_id === medicineId);
      return { favoriteId: f ? f.id : null, favoriteRecord: f || null };
    },
    rxMedicineStringOrEmpty(val) {
      if (val === null || val === undefined) {
        return '';
      }
      const s = String(val).trim();
      return s !== '' ? s : '';
    },
    rxTimingDoseFilled(val) {
      if (val === null || val === undefined) {
        return false;
      }
      const s = String(val).trim();
      if (s === '') {
        return false;
      }
      const n = Number(s);
      return Number.isFinite(n) && n > 0;
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
    buildTplDrugSuggestionList(searchItems, favItems) {
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
    buildFavoritePayloadFromMedsArr(item) {
      const hasId = item.id && item.id !== 0;
      return {
        medicine_id: hasId ? item.id : null,
        drug_name: item.medicine,
        custom_generic_name: this.medsArr.custom_meds ? this.medsArr.custom_generic : null,
        default_qty: this.medsArr.qty || null,
        default_bf_b: this.medsArr.bf_b || null,
        default_bf_a: this.medsArr.bf_a || null,
        default_l_b: this.medsArr.l_b || null,
        default_l_a: this.medsArr.l_a || null,
        default_s_b: this.medsArr.s_b || null,
        default_s_a: this.medsArr.s_a || null,
        default_bt: this.medsArr.bt || null,
        default_remarks: this.medsArr.remarks || null,
      };
    },
    applyMedicineDefaultsToMedsArr(item, favoriteRecord = null) {
      this.medsArr.custom_meds = false;
      this.medsArr.meds = item.medicine;
      this.medsArr.med_id = item.id;
      this.medsArr.custom_generic = '';
      this.medsArr.custom_brand = '';
      this.medsArr.master_generic = this.rxMedicineStringOrEmpty(item.generic_name);

      const masterQty = this.rxMedicineStringOrEmpty(item.default_qty);
      let qty = masterQty;
      if (favoriteRecord) {
        const favQty = this.rxMedicineStringOrEmpty(favoriteRecord.default_qty);
        if (favQty !== '') {
          qty = favQty;
        }
      }
      this.medsArr.qty = qty;

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
      this.medsArr.bf_b = meal.bf_b;
      this.medsArr.bf_a = meal.bf_a;
      this.medsArr.l_b = meal.l_b;
      this.medsArr.l_a = meal.l_a;
      this.medsArr.s_b = meal.s_b;
      this.medsArr.s_a = meal.s_a;
      this.medsArr.bt = meal.bt;

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
          const response = await Medicine.findmedicine(r.drug_name || '');
          masterItem =
            (response.suggestions || []).find((s) => s.id === r.medicine_id) || null;
        } catch (e) {
          masterItem = null;
        }
      }

      this.medsArr.custom_meds = !hasMaster;
      if (hasMaster) {
        this.medsArr.meds = r.drug_name;
        this.medsArr.med_id = r.medicine_id;
        this.medsArr.custom_generic = '';
        this.medsArr.custom_brand = '';
        this.medsArr.master_generic = this.rxMedicineStringOrEmpty(r.custom_generic_name);
      } else {
        this.medsArr.meds = '';
        this.medsArr.med_id = 0;
        const gen = (r.custom_generic_name || '').trim();
        const brand = (r.drug_name || '').trim();
        this.medsArr.custom_generic = gen || brand;
        this.medsArr.custom_brand = brand || gen;
        this.medsArr.master_generic = '';
      }

      const favQty = this.rxMedicineStringOrEmpty(r.default_qty);
      const masterQty = masterItem ? this.rxMedicineStringOrEmpty(masterItem.default_qty) : '';
      this.medsArr.qty = favQty !== '' ? favQty : (masterQty !== '' ? masterQty : '1');

      const masterMeal = this.mealTimingFieldsFromMedicineRecord(masterItem);
      const favMeal = this.mealTimingFieldsFromFavoriteRecord(r);
      this.medsArr.bf_b = favMeal.bf_b || masterMeal.bf_b;
      this.medsArr.bf_a = favMeal.bf_a || masterMeal.bf_a;
      this.medsArr.l_b = favMeal.l_b || masterMeal.l_b;
      this.medsArr.l_a = favMeal.l_a || masterMeal.l_a;
      this.medsArr.s_b = favMeal.s_b || masterMeal.s_b;
      this.medsArr.s_a = favMeal.s_a || masterMeal.s_a;
      this.medsArr.bt = favMeal.bt || masterMeal.bt;

      const favRemarks = this.rxMedicineStringOrEmpty(r.default_remarks);
      const masterRemarks = masterItem
        ? this.rxMedicineStringOrEmpty(masterItem.default_remarks)
        : '';
      this.medsArr.remarks = favRemarks || masterRemarks;
    },
    async querySearch(queryString, cb) {
      const q = (queryString || '').trim().toLowerCase();
      const favList = this.favoriteMedicinesCache || [];
      const favMatches = favList.filter((f) => (!q ? true : (f.drug_name || '').toLowerCase().includes(q)));
      const favSuggestions = favMatches.map((f) => ({
        medicine: f.drug_name,
        id: f.medicine_id || 0,
        generic_name: '',
        unit: '',
        isFavoriteRow: true,
        favoriteId: f.id,
        favoriteRecord: f,
        isFavorite: true,
      }));
      const favByMedId = new Set(
        favMatches.filter((f) => f.medicine_id).map((f) => f.medicine_id)
      );

      if (q === '') {
        cb(this.buildTplDrugSuggestionList([], favSuggestions));
        return;
      }
      try {
        const response = await Medicine.findmedicine(queryString);
        const list = (response && response.suggestions) ? response.suggestions : [];
        const filtered = list.filter((s) => !favByMedId.has(s.id));
        const withFav = filtered.map((s) => {
          const meta = this.findFavoriteMetaForTpl(s.id);
          return {
            ...s,
            favoriteId: meta.favoriteId,
            favoriteRecord: meta.favoriteRecord,
            isFavorite: !!meta.favoriteId,
          };
        });
        cb(this.buildTplDrugSuggestionList(withFav, favSuggestions));
      } catch (e) {
        cb(this.buildTplDrugSuggestionList([], favSuggestions));
      }
    },
    async handleSelect(ev) {
      if (!ev || ev.isSectionHeader) {
        this.medsArr.meds = '';
        return;
      }
      if (ev.isFavoriteRow && ev.favoriteRecord) {
        await this.applyFavoriteRecordToMedsArr(ev.favoriteRecord);
        return;
      }
      const favoriteRecord =
        ev.favoriteRecord ||
        (ev.favoriteId &&
          (this.favoriteMedicinesCache || []).find((x) => x.id === ev.favoriteId)) ||
        null;
      this.$nextTick(() => {
        this.applyMedicineDefaultsToMedsArr(ev, favoriteRecord);
      });
    },
    async toggleTplFavoriteStar(item) {
      if (item.isSectionHeader) {
        return;
      }
      try {
        if (item.favoriteId) {
          await deleteFavoriteMedicine(item.favoriteId);
          await this.loadFavoriteMedicinesList();
          this.$message.success('Removed from favorites');
          return;
        }
        await createFavoriteMedicine(this.buildFavoritePayloadFromMedsArr(item));
        await this.loadFavoriteMedicinesList();
        this.$message.success('Saved to favorites');
      } catch (e) {
        this.$message.error('Could not update favorites');
      }
    },
    nextMedUid() {
      this.medUidCounter += 1;
      return this.medUidCounter;
    },
    itemFromMedsArr(medsArr, existingUid = null) {
      const custom = !!medsArr.custom_meds;
      return {
        _uid: existingUid != null ? existingUid : this.nextMedUid(),
        custom_meds: custom,
        brand_name: custom
          ? (medsArr.custom_brand || '').trim()
          : (medsArr.meds || '').trim(),
        generic_name: custom
          ? (medsArr.custom_generic || '').trim()
          : (medsArr.master_generic || '').trim(),
        medicine_id: custom ? null : (medsArr.med_id && medsArr.med_id !== 0 ? medsArr.med_id : null),
        qty: (medsArr.qty || '').trim(),
        bf_b: (medsArr.bf_b || '').trim(),
        bf_a: (medsArr.bf_a || '').trim(),
        l_b: (medsArr.l_b || '').trim(),
        l_a: (medsArr.l_a || '').trim(),
        s_b: (medsArr.s_b || '').trim(),
        s_a: (medsArr.s_a || '').trim(),
        bt: (medsArr.bt || '').trim(),
        instructions: (medsArr.remarks || '').trim(),
      };
    },
    loadItemIntoMedsArr(item) {
      const custom = !!item.custom_meds;
      this.medsArr.custom_meds = custom;
      this.medsArr.qty = item.qty || '';
      this.medsArr.bf_b = item.bf_b || '';
      this.medsArr.bf_a = item.bf_a || '';
      this.medsArr.l_b = item.l_b || '';
      this.medsArr.l_a = item.l_a || '';
      this.medsArr.s_b = item.s_b || '';
      this.medsArr.s_a = item.s_a || '';
      this.medsArr.bt = item.bt || '';
      this.medsArr.remarks = item.instructions || '';
      if (custom) {
        this.medsArr.meds = '';
        this.medsArr.med_id = 0;
        this.medsArr.custom_generic = item.generic_name || '';
        this.medsArr.custom_brand = item.brand_name || '';
        this.medsArr.master_generic = '';
      } else {
        this.medsArr.meds = item.brand_name || '';
        this.medsArr.med_id = item.medicine_id || 0;
        this.medsArr.custom_generic = '';
        this.medsArr.custom_brand = '';
        this.medsArr.master_generic = item.generic_name || '';
      }
    },
    itemFromApiRow(r) {
      const hasMaster = r.medicine_id != null && r.medicine_id !== 0;
      const meal = appointmentMealTimingStringsFromFrequency(r.frequency || '');
      const item = emptyItem();
      item._uid = this.nextMedUid();
      item.custom_meds = !hasMaster;
      item.brand_name = r.brand_name || '';
      item.generic_name = r.generic_name || '';
      item.medicine_id = hasMaster ? r.medicine_id : null;
      item.qty = r.quantity || '';
      item.instructions = r.instructions || '';
      Object.assign(item, meal);
      return item;
    },
    itemFromFavoriteRecord(f) {
      const hasMaster = f.medicine_id != null && f.medicine_id !== 0;
      const meal = this.mealTimingFieldsFromFavoriteRecord(f);
      const item = emptyItem();
      item._uid = this.nextMedUid();
      item.custom_meds = !hasMaster;
      item.qty = this.rxMedicineStringOrEmpty(f.default_qty) || '1';
      item.instructions = f.default_remarks || '';
      Object.assign(item, meal);
      if (hasMaster) {
        item.brand_name = f.drug_name || '';
        item.generic_name = this.rxMedicineStringOrEmpty(f.custom_generic_name);
        item.medicine_id = f.medicine_id;
      } else {
        const gen = (f.custom_generic_name || '').trim();
        const brand = (f.drug_name || '').trim();
        item.brand_name = brand || gen;
        item.generic_name = gen || brand;
      }
      return item;
    },
    displayMedicineName(row) {
      const brand = (row.brand_name || '').trim();
      const generic = (row.generic_name || '').trim();
      if (brand && generic && brand.toLowerCase() !== generic.toLowerCase()) {
        return `${brand} (${generic})`;
      }
      return brand || generic || '—';
    },
    itemHasMealTiming(row) {
      return ['bf_b', 'bf_a', 'l_b', 'l_a', 's_b', 's_a', 'bt'].some((k) => this.rxTimingDoseFilled(row[k]));
    },
    openMedDialog() {
      this.clearMedsForm();
      this.medDialogVisible = true;
    },
    closeMedDialog() {
      this.cancelMedEdit();
    },
    onMedDialogClose() {
      this.cancelMedEdit();
    },
    editMedRow(index) {
      const row = this.form.items[index];
      if (!row) {
        return;
      }
      this.isMedEditMode = true;
      this.editingMedIndex = index;
      this.loadItemIntoMedsArr(row);
      this.medDialogVisible = true;
    },
    removeMedRow(index) {
      this.form.items.splice(index, 1);
      if (this.isMedEditMode && this.editingMedIndex === index) {
        this.cancelMedEdit();
      }
    },
    cancelMedEdit() {
      this.isMedEditMode = false;
      this.editingMedIndex = null;
      this.medDialogVisible = false;
      this.clearMedsForm();
    },
    clearMedsForm() {
      this.medsArr = emptyMedsArr();
      this.isMedEditMode = false;
      this.editingMedIndex = null;
    },
    addOrUpdateMed() {
      const masterOk = !this.medsArr.custom_meds
        && (this.medsArr.meds || '').trim() !== ''
        && (this.medsArr.qty || '').trim() !== '';
      const customOk = this.medsArr.custom_meds
        && (this.medsArr.custom_generic || '').trim() !== ''
        && (this.medsArr.custom_brand || '').trim() !== '';
      if (!masterOk && !customOk) {
        this.$message.warning('Medicine details are required.');
        return;
      }
      const existingUid = this.isMedEditMode && this.editingMedIndex != null
        ? (this.form.items[this.editingMedIndex] && this.form.items[this.editingMedIndex]._uid)
        : null;
      const item = this.itemFromMedsArr(this.medsArr, existingUid);
      if (!this.itemHasMealTiming(item)) {
        this.$message.warning('Enter at least one meal timing dose.');
        return;
      }
      if (this.isMedEditMode && this.editingMedIndex != null) {
        this.$set(this.form.items, this.editingMedIndex, item);
        this.$message.success('Medicine updated.');
      } else {
        this.form.items.push(item);
        this.$message.success('Medicine added.');
      }
      this.cancelMedEdit();
    },
    timingFavoriteColumn(stored) {
      const t = displayStoredTimingOrLegacy(stored);
      return t || '—';
    },
    openTplFavoritesDialog() {
      this.tplFavSelection = [];
      this.tplFavDialogVisible = true;
      this.loadFavoriteMedicinesList();
      this.$nextTick(() => {
        if (this.$refs.tplFavoritesTable) {
          this.$refs.tplFavoritesTable.clearSelection();
        }
      });
    },
    handleTplFavoritesSelectionChange(val) {
      this.tplFavSelection = val || [];
    },
    async applyTplFavoritesSelection() {
      const rows = this.tplFavSelection;
      if (!rows || !rows.length) {
        this.$message.warning('Select at least one favorite.');
        return;
      }
      this.tplFavApplyLoading = true;
      try {
        rows.forEach((f) => {
          this.form.items.push(this.itemFromFavoriteRecord(f));
        });
        this.$message.success('Medicines added from favorites.');
        this.tplFavDialogVisible = false;
      } finally {
        this.tplFavApplyLoading = false;
      }
    },
    async load() {
      try {
        const res = await getPrescriptionDiagnosisTemplate(this.$route.params.id);
        const data = res.data;
        this.form.diagnosis_name = data.diagnosis_name;
        this.form.items = (data.items && data.items.length
          ? data.items.map((r) => this.itemFromApiRow(r))
          : []);
      } catch (e) {
        this.$message.error('Could not load template');
        this.goBack();
      }
    },
    async fetchDiagnosisSuggestions(queryString, cb) {
      try {
        const res = await diagnosisNameSuggestions({ q: queryString || '' });
        const list = (res.data || []).map((v) => ({ value: v }));
        cb(list);
      } catch (e) {
        cb([]);
      }
    },
    onDiagnosisSelect() {
      /* value already bound */
    },
    submit() {
      this.$refs.formRef.validate(async (valid) => {
        if (!valid) {
          return;
        }
        if (!this.form.items.length) {
          this.$message.error('Add at least one medication.');
          return;
        }
        const payload = {
          diagnosis_name: this.form.diagnosis_name.trim(),
          items: this.form.items.map((r) => ({
            brand_name: (r.brand_name || '').trim(),
            generic_name: (r.generic_name || '').trim() || null,
            medicine_id: r.custom_meds ? null : (r.medicine_id != null ? r.medicine_id : null),
            quantity: (r.qty || '').trim() || null,
            frequency: serializeTimingFromAppointmentMealStrings({
              bf_b: r.bf_b,
              bf_a: r.bf_a,
              l_b: r.l_b,
              l_a: r.l_a,
              s_b: r.s_b,
              s_a: r.s_a,
              bt: r.bt,
            }) || null,
            instructions: (r.instructions || '').trim() || null,
          })),
        };
        const missingBrand = payload.items.some((r) => !r.brand_name);
        if (missingBrand) {
          this.$message.error('Each medication must have a brand name.');
          return;
        }
        const missingGeneric = payload.items.some((r) => !r.medicine_id && !r.generic_name);
        if (missingGeneric) {
          this.$message.error('Each custom medication must have a generic name.');
          return;
        }
        const missingTiming = this.form.items.some((r) => !this.itemHasMealTiming(r));
        if (missingTiming) {
          this.$message.error('Each medication must have at least one meal timing dose.');
          return;
        }
        this.saving = true;
        try {
          if (this.isEdit) {
            await updatePrescriptionDiagnosisTemplate(this.$route.params.id, payload);
            this.$message.success('Template updated');
          } else {
            await createPrescriptionDiagnosisTemplate(payload);
            this.$message.success('Template created');
          }
          this.goBack();
        } catch (e) {
          this.$message.error('Could not save template. Check required fields and try again.');
        } finally {
          this.saving = false;
        }
      });
    },
  },
};
</script>

<style scoped>
.rx-dx-tpl-form-page {
  max-width: 1280px;
  margin: 0 auto;
}

.page-header-card {
  margin-bottom: 16px;
}

.page-header {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  gap: 12px;
  align-items: flex-start;
}

.page-title {
  margin: 0 0 6px;
  font-size: 20px;
  font-weight: 600;
  color: #303133;
}

.page-subtitle {
  margin: 0;
  font-size: 13px;
  color: #606266;
  max-width: 720px;
  line-height: 1.5;
}

.page-context-alert {
  margin-top: 12px;
  max-width: 900px;
}

.page-context-alert .alert-body {
  margin: 0;
  padding-top: 4px;
  font-size: 13px;
  line-height: 1.55;
  color: #606266;
}

.page-context-alert .alert-body code {
  font-size: 12px;
  background: #f4f4f5;
  padding: 1px 5px;
  border-radius: 3px;
}

.header-actions .el-button + .el-button {
  margin-left: 8px;
}

.card-header-title {
  font-weight: 600;
  color: #303133;
}

.card-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-header-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.tpl-fav-star-btn {
  padding: 0 4px !important;
  margin-left: 4px;
  min-height: auto;
}

.tpl-fav-star-btn .el-icon-star-on {
  color: #e6a23c;
}

.tpl-fav-star-btn .el-icon-star-off {
  color: #c0c4cc;
}

.mb-3 {
  margin-bottom: 16px;
}

.w-100 {
  width: 100%;
}

.med-table {
  width: 100%;
}

.rx-meal-timing-inputs .el-form-item {
  margin-bottom: 8px;
}

.rx-meal-timing-inputs .el-form-item__label {
  line-height: 1.2;
  padding-bottom: 2px;
  font-size: 12px;
}

.rx-dose-input--filled >>> .el-input__inner {
  background-color: #f0f9eb;
  border-color: #c2e7b0;
  font-weight: 600;
}

.rx-med-table >>> .rx-timing-dose-filled {
  display: inline-block;
  min-width: 1.25em;
  padding: 2px 6px;
  border-radius: 4px;
  background: #ecf5ff;
  color: #1f5896;
  font-weight: 600;
  font-size: 12px;
}

.drug-source-row {
  margin-top: 6px;
  min-height: 22px;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 6px;
}

@media (max-width: 992px) {
  .page-header {
    flex-direction: column;
  }
}
</style>

<style lang="scss">
/* Popper is teleported; not under scoped root */
.tpl-fav-dialog__hint {
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
</style>
