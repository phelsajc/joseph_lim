<template>
  <div class="health-record-card-wrap">
  <article
    class="health-record-card"
    :class="{ 'health-record-card--readonly': readonly, 'health-record-card--interactive': !readonly }"
    :role="readonly ? undefined : 'button'"
    :tabindex="readonly ? undefined : 0"
    v-on="readonly ? {} : { click: openRecord, keydown: onKeydown }"
  >
    <header class="health-record-card__header">
      <h4 class="health-record-card__title">Visit Summary</h4>
      <div v-if="headerMeta" class="health-record-card__meta">
        {{ headerMeta }}
      </div>
    </header>

    <div class="health-record-card__clinical">
      <div v-if="hasClinicalField('history')" class="health-record-card__row">
        <span class="health-record-card__label">HPI</span>
        <p class="health-record-card__value">{{ record.clinical.history }}</p>
      </div>
      <div v-if="hasClinicalField('remarks')" class="health-record-card__row">
        <span class="health-record-card__label">Remarks</span>
        <p class="health-record-card__value">{{ record.clinical.remarks }}</p>
      </div>
      <div v-if="hasClinicalField('diagnosis')" class="health-record-card__row">
        <span class="health-record-card__label">Diagnosis</span>
        <p class="health-record-card__value">{{ record.clinical.diagnosis }}</p>
      </div>
      <div v-if="hasClinicalField('plan')" class="health-record-card__row">
        <span class="health-record-card__label">Plan</span>
        <p class="health-record-card__value">{{ record.clinical.plan }}</p>
      </div>
      <div v-if="hasClinicalField('pe')" class="health-record-card__row">
        <span class="health-record-card__label">P.E.</span>
        <p class="health-record-card__value">{{ record.clinical.pe }}</p>
      </div>
    </div>

    <section v-if="hasVitals" class="health-record-card__vitals-section">
      <h5 class="health-record-card__section-title">Vitals</h5>
      <p class="health-record-card__section-subtitle">General Vitals</p>
      <div class="health-record-card__vitals">
        <div v-if="record.vitals.weight" class="health-record-card__vital health-record-card__vital--weight">
          <i class="el-icon-s-data"></i>
          <div>
            <span class="health-record-card__vital-label">Wt</span>
            <span class="health-record-card__vital-value">{{ record.vitals.weight }}</span>
          </div>
        </div>
        <div v-if="record.vitals.bsa" class="health-record-card__vital health-record-card__vital--bsa">
          <i class="el-icon-user"></i>
          <div>
            <span class="health-record-card__vital-label">BSA</span>
            <span class="health-record-card__vital-value">{{ record.vitals.bsa }}</span>
          </div>
        </div>
        <div v-if="record.vitals.bp" class="health-record-card__vital health-record-card__vital--bp">
          <i class="el-icon-odometer"></i>
          <div>
            <span class="health-record-card__vital-label">BP</span>
            <span class="health-record-card__vital-value">{{ record.vitals.bp }}</span>
          </div>
        </div>
        <div v-if="record.vitals.hr" class="health-record-card__vital health-record-card__vital--hr">
          <i class="el-icon-s-opportunity"></i>
          <div>
            <span class="health-record-card__vital-label">HR</span>
            <span class="health-record-card__vital-value">{{ record.vitals.hr }}</span>
          </div>
        </div>
      </div>
    </section>

    <section v-if="hasNotes" class="health-record-card__notes-section">
      <h5 class="health-record-card__section-title">Notes</h5>
      <div class="health-record-card__notes-grid">
        <div v-if="record.prescriptions.length" class="health-record-card__note-card">
          <div class="health-record-card__note-header">
            <i class="el-icon-first-aid-kit"></i>
            <span>Prescription</span>
          </div>
          <ul class="health-record-card__note-list">
            <li v-for="(rx, idx) in record.prescriptions" :key="'rx-' + idx">
              {{ formatPrescription(rx) }}
            </li>
          </ul>
        </div>

        <div v-if="record.diagnostics.length" class="health-record-card__note-card">
          <div class="health-record-card__note-header">
            <span>Test Request</span>
          </div>
          <p class="health-record-card__note-subtitle">LABORATORY</p>
          <ul class="health-record-card__note-list">
            <li v-for="(dx, idx) in record.diagnostics" :key="'dx-' + idx">
              {{ dx.diagnostic }}
            </li>
          </ul>
        </div>

        <div v-if="record.forms.medcert.has_content" class="health-record-card__note-card">
          <div class="health-record-card__note-header">
            <i class="el-icon-document-copy"></i>
            <span>Med Cert</span>
          </div>
          <p v-if="record.forms.medcert.diagnosis" class="health-record-card__note-text">
            {{ record.forms.medcert.diagnosis }}
          </p>
          <p v-if="record.forms.medcert.remarks" class="health-record-card__note-text health-record-card__note-text--muted">
            {{ record.forms.medcert.remarks }}
          </p>
        </div>

        <div v-if="hasReferralNote()" class="health-record-card__note-card">
          <div class="health-record-card__note-header">
            <i class="el-icon-s-promotion"></i>
            <span>Referral</span>
          </div>
          <p v-if="record.forms.referral.doctor" class="health-record-card__note-text">
            To: {{ record.forms.referral.doctor }}
          </p>
          <p v-if="record.forms.referral.diagnosis" class="health-record-card__note-text health-record-card__note-text--muted">
            {{ record.forms.referral.diagnosis }}
          </p>
          <p v-if="record.forms.referral.remarks" class="health-record-card__note-text health-record-card__note-text--muted">
            {{ record.forms.referral.remarks }}
          </p>
        </div>

        <div v-if="record.forms.form.has_content" class="health-record-card__note-card">
          <div class="health-record-card__note-header">
            <i class="el-icon-document"></i>
            <span>Form</span>
          </div>
          <p class="health-record-card__note-text health-record-card__note-text--muted">
            {{ formatFormPreview(record.forms.form.preview) }}
          </p>
        </div>
      </div>
    </section>

    <section v-if="hasAttachments" class="health-record-card__attachments-section">
      <h5 class="health-record-card__section-title">Attachments</h5>
      <div class="health-record-card__attachments-grid">
        <div
          v-for="item in visitDayAttachments"
          :key="item.id"
          class="health-record-card__attachment"
          :title="displayFileName(item)"
        >
          <span class="health-record-card__attachment-thumb">
            <el-image
              v-if="isImageAttachment(item.extension)"
              class="health-record-card__attachment-image"
              :src="item.newfile"
              fit="cover"
              :preview-src-list="visitDayImageUrls"
              :initial-index="getImagePreviewIndex(item)"
              @click.native.stop
            />
            <span
              v-else
              class="health-record-card__attachment-file"
              role="button"
              tabindex="0"
              @click.stop="viewFile(item.newfile, item.extension)"
              @keydown.enter.stop="viewFile(item.newfile, item.extension)"
              @keydown.space.prevent.stop="viewFile(item.newfile, item.extension)"
            >
              <i :class="driveFileIcon(item.extension)" />
              <span>{{ fileExtensionLabel(item.extension) }}</span>
            </span>
          </span>
          <span class="health-record-card__attachment-name">{{ displayFileName(item) }}</span>
        </div>
      </div>
    </section>
  </article>

  <el-dialog
    :visible.sync="viewFileModel"
    :fullscreen="false"
    :close-on-click-modal="false"
    append-to-body
    @click.native.stop
  >
    <div class="health-record-card__file-preview">
      <iframe
        v-if="isPdf"
        :src="sourceFile"
        frameborder="0"
        class="health-record-card__file-preview-iframe"
      />
      <el-image
        v-if="!isPdf"
        style="width: 100px; height: 100px"
        :src="sourceFile"
        :zoom-rate="1.2"
        :max-scale="7"
        :min-scale="0.2"
        :preview-src-list="[sourceFile]"
        show-progress
        :initial-index="0"
        fit="cover"
      />
    </div>
  </el-dialog>
  </div>
</template>

<script>
import moment from 'moment';
import { html2Text } from '@/utils';

const ATTACHMENT_DATE_FORMATS = [
  'MMMM DD, YYYY',
  'MMMM D, YYYY',
  'MMM DD, YYYY',
  'MMM D, YYYY',
];

export default {
  name: 'ConsultationRecordCard',
  props: {
    record: {
      type: Object,
      required: true,
    },
    attachments: {
      type: Array,
      default: () => [],
    },
    navigateOnClick: {
      type: Boolean,
      default: false,
    },
    readonly: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      viewFileModel: false,
      sourceFile: '',
      isPdf: false,
    };
  },
  computed: {
    headerMeta() {
      const parts = [
        this.record.patient_name,
        this.record.date_display,
        this.record.patient_age,
      ]
        .filter((value) => value != null && String(value).trim() !== '' && String(value).trim() !== '—');
      return parts.join(' · ');
    },
    hasVitals() {
      const v = this.record.vitals || {};
      return !!(v.weight || v.bsa || v.bp || v.hr);
    },
    hasNotes() {
      const forms = this.record.forms || {};
      return (
        (this.record.prescriptions && this.record.prescriptions.length > 0)
        || (this.record.diagnostics && this.record.diagnostics.length > 0)
        || (forms.medcert && forms.medcert.has_content)
        || this.hasReferralNote()
        || (forms.form && forms.form.has_content)
      );
    },
    visitDayKey() {
      const raw = this.record.appointment_dt || this.record.date_display;
      if (!raw) {
        return null;
      }
      const m = moment(raw);
      return m.isValid() ? m.format('YYYY-MM-DD') : null;
    },
    visitDayAttachments() {
      const dayKey = this.visitDayKey;
      if (!dayKey) {
        return [];
      }
      const list = (this.attachments || []).filter((item) => {
        const m = moment(item.created_dt, ATTACHMENT_DATE_FORMATS, true);
        return m.isValid() && m.format('YYYY-MM-DD') === dayKey;
      });
      list.sort((a, b) => {
        const ma = moment(a.created_dt, ATTACHMENT_DATE_FORMATS, true);
        const mb = moment(b.created_dt, ATTACHMENT_DATE_FORMATS, true);
        return (mb.isValid() ? mb.valueOf() : 0) - (ma.isValid() ? ma.valueOf() : 0);
      });
      return list;
    },
    hasAttachments() {
      return this.visitDayAttachments.length > 0;
    },
    visitDayImageUrls() {
      return this.visitDayAttachments
        .filter((item) => this.isImageAttachment(item.extension))
        .map((item) => item.newfile);
    },
  },
  methods: {
    hasClinicalField(field) {
      const clinical = this.record.clinical || {};
      const value = clinical[field];
      return value != null && String(value).trim() !== '';
    },
    hasReferralNote() {
      const referral = (this.record.forms && this.record.forms.referral) || {};
      const fields = [referral.doctor, referral.diagnosis, referral.remarks];
      return fields.some((value) => value != null && String(value).trim() !== '');
    },
    formatPrescription(rx) {
      const parts = [rx.medicine];
      if (rx.qty) {
        parts.push('#' + rx.qty);
      }
      return parts.filter(Boolean).join(' ');
    },
    formatFormPreview(preview) {
      if (!preview) {
        return 'Form attached';
      }
      return html2Text(preview).trim() || 'Form attached';
    },
    openRecord() {
      if (this.readonly) {
        return;
      }
      if (this.navigateOnClick) {
        this.$router.push({ path: '/appointments/form/' + this.record.id });
        return;
      }
      this.$emit('select', this.record);
    },
    onKeydown(event) {
      if (event.key === 'Enter' || event.key === ' ') {
        if (event.key === ' ') {
          event.preventDefault();
        }
        this.openRecord();
      }
    },
    isImageAttachment(ext) {
      if (ext === null || ext === undefined || ext === '') {
        return false;
      }
      const e = String(ext).toLowerCase().replace(/^\./, '');
      return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'].includes(e);
    },
    fileExtensionLabel(ext) {
      if (ext === null || ext === undefined || ext === '') {
        return 'FILE';
      }
      return String(ext).replace(/^\./, '').toUpperCase().slice(0, 8);
    },
    displayFileName(item) {
      return item.description || item.fname || 'Attachment';
    },
    driveFileIcon(ext) {
      const e = String(ext || '')
        .toLowerCase()
        .replace(/^\./, '');
      if (e === 'pdf') {
        return 'el-icon-document';
      }
      if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'].includes(e)) {
        return 'el-icon-picture-outline';
      }
      return 'el-icon-folder';
    },
    getImagePreviewIndex(item) {
      const i = this.visitDayImageUrls.indexOf(item.newfile);
      return i >= 0 ? i : 0;
    },
    viewFile(src, ext) {
      this.isPdf = String(ext || '').toLowerCase().replace(/^\./, '') === 'pdf';
      this.sourceFile = src;
      this.viewFileModel = true;
    },
  },
};
</script>

<style scoped>
.health-record-card {
  background: #fff;
  border: 1px solid #e4e7ed;
  border-radius: 10px;
  padding: 20px 24px;
  margin-bottom: 16px;
  min-width: 0;
  transition: box-shadow 0.2s ease, border-color 0.2s ease;
}

.health-record-card--interactive {
  cursor: pointer;
}

.health-record-card--interactive:hover,
.health-record-card--interactive:focus {
  outline: none;
  border-color: #c0c4cc;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.health-record-card--readonly {
  margin-bottom: 0;
  cursor: default;
}

.health-record-card--readonly:hover,
.health-record-card--readonly:focus {
  border-color: #e4e7ed;
  box-shadow: none;
}

.health-record-card__header {
  margin-bottom: 16px;
}

.health-record-card__title {
  margin: 0 0 6px;
  font-size: 18px;
  font-weight: 700;
  color: #303133;
}

.health-record-card__meta {
  font-size: 13px;
  color: #606266;
  line-height: 1.5;
}

.health-record-card__clinical {
  margin-bottom: 16px;
}

.health-record-card__row {
  display: grid;
  grid-template-columns: 100px 1fr;
  gap: 8px 16px;
  margin-bottom: 10px;
  align-items: start;
}

.health-record-card__label {
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: #909399;
  padding-top: 2px;
}

.health-record-card__value {
  margin: 0;
  font-size: 14px;
  color: #303133;
  white-space: pre-wrap;
  line-height: 1.5;
}

.health-record-card__section-title {
  margin: 0 0 4px;
  font-size: 13px;
  font-weight: 700;
  color: #303133;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.health-record-card__section-subtitle {
  margin: 0 0 10px;
  font-size: 12px;
  color: #909399;
}

.health-record-card__vitals-section {
  margin-bottom: 16px;
}

.health-record-card__vitals {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.health-record-card__vital {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 110px;
  padding: 10px 12px;
  border-radius: 8px;
  border: 1px solid #ebeef5;
  background: #fafafa;
}

.health-record-card__vital i {
  font-size: 18px;
}

.health-record-card__vital--weight i { color: #9b59b6; }
.health-record-card__vital--bsa i { color: #3498db; }
.health-record-card__vital--bp i { color: #e67e22; }
.health-record-card__vital--hr i { color: #e74c3c; }

.health-record-card__vital-label {
  display: block;
  font-size: 11px;
  color: #909399;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.health-record-card__vital-value {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #303133;
}

.health-record-card__notes-section {
  margin-top: 4px;
}

.health-record-card__notes-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.health-record-card__note-card {
  border: 1px solid #ebeef5;
  border-radius: 8px;
  padding: 12px 14px;
  background: #fff;
}

.health-record-card__note-header {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
  font-weight: 600;
  color: #303133;
  margin-bottom: 8px;
}

.health-record-card__note-subtitle {
  margin: 0 0 6px;
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.06em;
  color: #909399;
}

.health-record-card__note-list {
  margin: 0;
  padding-left: 18px;
  font-size: 13px;
  color: #303133;
  line-height: 1.5;
}

.health-record-card__note-list li {
  margin-bottom: 4px;
}

.health-record-card__note-text {
  margin: 0 0 6px;
  font-size: 13px;
  color: #303133;
  line-height: 1.5;
  white-space: pre-wrap;
}

.health-record-card__note-text--muted {
  color: #606266;
}

.health-record-card__attachments-section {
  margin-top: 16px;
}

.health-record-card__attachments-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 10px;
  margin-top: 8px;
}

.health-record-card-wrap {
  min-width: 0;
}

.health-record-card__attachment {
  display: flex;
  flex-direction: column;
  gap: 6px;
  min-width: 0;
  color: inherit;
  border: 1px solid #ebeef5;
  border-radius: 8px;
  padding: 8px;
  background: #fafafa;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.health-record-card__attachment:hover {
  border-color: #c0c4cc;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.health-record-card__attachment-thumb {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 72px;
  border-radius: 6px;
  overflow: hidden;
  background: #fff;
}

.health-record-card__attachment-image {
  width: 100%;
  height: 100%;
  cursor: pointer;
}

.health-record-card__attachment-image ::v-deep .el-image__inner {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.health-record-card__attachment-file {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  width: 100%;
  height: 100%;
  color: #909399;
  cursor: pointer;
}

.health-record-card__attachment-file i {
  font-size: 24px;
}

.health-record-card__attachment-file span {
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.04em;
}

.health-record-card__attachment-name {
  font-size: 11px;
  color: #606266;
  line-height: 1.3;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.health-record-card__file-preview {
  text-align: center;
  padding: 10px;
}

.health-record-card__file-preview-iframe {
  width: 800px;
  max-width: 100%;
  height: 600px;
  border: none;
}

@media (max-width: 767px) {
  .health-record-card {
    padding: 14px 12px;
    margin-bottom: 20px;
  }

  .health-record-card__title {
    font-size: 16px;
  }

  .health-record-card__meta {
    font-size: 12px;
    word-break: break-word;
  }

  .health-record-card__row {
    grid-template-columns: 1fr;
    gap: 4px;
  }

  .health-record-card__value,
  .health-record-card__note-text,
  .health-record-card__note-list {
    overflow-wrap: anywhere;
  }

  .health-record-card__vitals {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px;
  }

  .health-record-card__vital {
    min-width: 0;
  }

  .health-record-card__notes-grid {
    grid-template-columns: 1fr;
  }

  .health-record-card__note-card {
    padding: 10px 12px;
  }

  .health-record-card__attachments-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
</style>
