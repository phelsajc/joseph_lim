<template>
  <el-card
    v-if="user.name"
    class="user-activity-card"
    shadow="hover"
    :body-style="{ padding: '0' }"
  >
    <LoadingOverlay :visible="pageloading" text="Loading..." />
    <header class="ua-profile-header">
      <div class="ua-profile-header__row">
        <div class="ua-profile-header__identity">
          <div class="ua-profile-header__avatar-wrap">
            <el-image
              v-if="headerPhotoSrc"
              :src="headerPhotoSrc"
              fit="cover"
              class="ua-profile-header__avatar"
              :alt="displayPatientName + ' photo'"
            >
              <div slot="error" class="ua-profile-header__avatar-fallback">
                <i class="el-icon-user-solid" />
              </div>
            </el-image>
            <div
              v-else
              class="ua-profile-header__avatar ua-profile-header__avatar--placeholder"
              role="img"
              :aria-label="displayPatientName"
            >
              <i class="el-icon-user-solid" />
            </div>
          </div>
          <div class="ua-profile-header__text">
            <h2 class="ua-profile-header__name">
              {{ displayPatientName }}
            </h2>
            <dl class="ua-profile-header__dl">
              <div class="ua-profile-header__dl-item">
                <dt>Birthdate</dt>
                <dd>{{ formattedPatientBirthdate }}</dd>
              </div>
              <div class="ua-profile-header__dl-item">
                <dt>Age</dt>
                <dd>{{ patientAgeDisplay }}</dd>
              </div>
              <div class="ua-profile-header__dl-item">
                <dt>Contact</dt>
                <dd>{{ form.contactno || '—' }}</dd>
              </div>
            </dl>
            <div class="ua-profile-header__upload">
              <el-upload
                class="ua-profile-upload"
                :on-change="handleProfileUploadChange"
                :limit="1"
                :auto-upload="false"
                :show-file-list="false"
              >
                <el-button size="small" type="primary" plain>
                  Change photo
                </el-button>
              </el-upload>
            </div>
          </div>
        </div>
        <div
          class="ua-profile-header__actions"
          role="toolbar"
          aria-label="Patient record actions"
        >
          <el-popconfirm
            confirm-button-text="Yes"
            cancel-button-text="No"
            icon-color="#409EFF"
            title="Do you want to proceed?"
            @confirm="onSubmit"
          >
            <template #reference>
              <el-button type="primary">
                Update
              </el-button>
            </template>
          </el-popconfirm>

          <el-popconfirm
            confirm-button-text="Yes"
            cancel-button-text="No"
            icon-color="#409EFF"
            title="Do you want to delete this patient?"
            @confirm="deletePatient"
          >
            <template #reference>
              <el-button type="danger">
                Delete
              </el-button>
            </template>
          </el-popconfirm>
          <el-button
            v-role="['doctor', 'admin']"
            type="warning"
            @click="printChart()"
          >
            Print chart
          </el-button>
          <el-button
            v-role="['secretary', 'admin', 'doctor']"
            type="primary"
            icon="el-icon-edit-outline"
            @click="recordVitalsDialogVisible = true"
          >
            Record vitals
          </el-button>
          <PatientAppointmentActions
            :patient-id="$route.params.pid"
            @created="past_consult"
          />
        </div>
      </div>
    </header>

    <div v-loading="updating" class="ua-page-scroll">
      <div class="ua-page-inner">
        <ConsultationHistoryPanel
          v-if="checkRole(['admin', 'doctor'])"
          ref="consultationHistoryPanel"
          :profile-id="$route.params.id"
          :patient-id="$route.params.pid"
          :records="consultationHistory"
          :loading="consultationHistoryLoading"
          :attachments="attachments"
          :default-expanded="true"
        />

        <section class="ua-section-card" aria-labelledby="ua-h-identity">
          <h3 id="ua-h-identity" class="ua-section-card__title">
            Identity
          </h3>
          <el-form label-position="top" class="ua-form">
            <el-row :gutter="24">
              <el-col :xs="24" :sm="12" :md="8">
                <el-form-item label="Last name">
                  <el-input v-model="form.lastname" clearable placeholder="Family name" />
                </el-form-item>
              </el-col>
              <el-col :xs="24" :sm="12" :md="8">
                <el-form-item label="First name">
                  <el-input v-model="form.firstname" clearable placeholder="Given name" />
                </el-form-item>
              </el-col>
              <el-col :xs="24" :sm="12" :md="8">
                <el-form-item label="Middle name">
                  <el-input v-model="form.middlename" clearable placeholder="Optional" />
                </el-form-item>
              </el-col>
            </el-row>
          </el-form>
        </section>

        <section class="ua-section-card" aria-labelledby="ua-h-address">
          <h3 id="ua-h-address" class="ua-section-card__title">
            Address
          </h3>
          <el-form label-position="top" class="ua-form">
            <el-row :gutter="24">
              <el-col :span="24">
                <el-form-item label="Home address">
                  <el-input
                    v-model="form.address"
                    type="textarea"
                    :autosize="{ minRows: 2, maxRows: 6 }"
                    placeholder="Street, city, region"
                  />
                </el-form-item>
              </el-col>
            </el-row>
          </el-form>
        </section>

        <section class="ua-section-card" aria-labelledby="ua-h-contact">
          <h3 id="ua-h-contact" class="ua-section-card__title">
            Contact &amp; Demographics
          </h3>
          <el-form label-position="top" class="ua-form">
            <el-row :gutter="24">
              <el-col :xs="24" :sm="12">
                <el-form-item label="Contact number">
                  <el-input v-model="form.contactno" clearable placeholder="Mobile or landline" />
                </el-form-item>
              </el-col>
              <el-col :xs="24" :sm="12">
                <el-form-item label="Email">
                  <el-input v-model="form.email" clearable placeholder="name@example.com" />
                </el-form-item>
              </el-col>
              <el-col :xs="24" :sm="12" :md="8">
                <el-form-item label="Birthday">
                  <el-date-picker
                    v-model="form.birthdate"
                    type="date"
                    placeholder="Pick a date"
                    class="ua-date"
                  />
                </el-form-item>
              </el-col>
              <el-col :xs="24" :sm="12" :md="8">
                <el-form-item label="Gender">
                  <el-select
                    v-model="form.sex"
                    placeholder="Select"
                    class="ua-select"
                  >
                    <el-option label="Female" value="2" />
                    <el-option label="Male" value="1" />
                  </el-select>
                </el-form-item>
              </el-col>
              <el-col :xs="24" :sm="12" :md="8">
                <el-form-item label="Blood type">
                  <el-select
                    v-model="form.blood_type"
                    placeholder="Select"
                    class="ua-select"
                  >
                    <el-option label="A+" value="A+" />
                    <el-option label="A-" value="A-" />
                    <el-option label="B+" value="B+" />
                    <el-option label="B-" value="B-" />
                    <el-option label="AB-" value="AB-" />
                    <el-option label="AB+" value="AB+" />
                    <el-option label="O+" value="O+" />
                    <el-option label="O-" value="O-" />
                  </el-select>
                </el-form-item>
              </el-col>
            </el-row>
          </el-form>
        </section>

        <section class="ua-section-card" aria-labelledby="ua-h-caregiver">
          <h3 id="ua-h-caregiver" class="ua-section-card__title">
            Caregiver
          </h3>
          <el-form label-position="top" class="ua-form">
            <el-row :gutter="24">
              <el-col :xs="24" :sm="12" :md="8">
                <el-form-item label="Caregiver's name">
                  <el-input v-model="form.caregiver_name" clearable />
                </el-form-item>
              </el-col>
              <el-col :xs="24" :sm="12" :md="8">
                <el-form-item label="Age">
                  <el-input v-model="form.caregiver_age" clearable />
                </el-form-item>
              </el-col>
              <el-col :xs="24" :sm="12" :md="8">
                <el-form-item label="Relationship">
                  <el-input v-model="form.caregiver_rel" clearable />
                </el-form-item>
              </el-col>
              <el-col :xs="24" :sm="12">
                <el-form-item label="Contact number">
                  <el-input v-model="form.caregiver_contact" clearable />
                </el-form-item>
              </el-col>
              <el-col :xs="24" :sm="12">
                <el-form-item label="Occupation">
                  <el-input v-model="form.caregiver_occupation" clearable />
                </el-form-item>
              </el-col>
            </el-row>
            <h4 id="ua-h-siblings" class="ua-section-card__subtitle">
              Siblings
            </h4>
            <el-row :gutter="24">
              <el-col :span="24">
                <el-form-item label="Sibling's age / sex">
                  <el-input
                    v-model="form.siblings_details"
                    type="textarea"
                    :autosize="{ minRows: 2, maxRows: 6 }"
                    placeholder="Optional details"
                  />
                </el-form-item>
              </el-col>
            </el-row>
          </el-form>
        </section>

        <div
          v-if="checkRole(['admin','doctor'])"
          class="ua-medical-history"
          aria-labelledby="ua-h-medical"
        >
          <h2 id="ua-h-medical" class="ua-medical-history__heading">
            Medical History
          </h2>
          <section class="ua-section-card ua-section-card--nested" aria-labelledby="ua-h-pmh-general">
            <h3 id="ua-h-pmh-general" class="ua-section-card__title">
              Admissions &amp; procedures
            </h3>
            <el-form label-position="top" class="ua-form">
              <el-row :gutter="24">
                <el-col :xs="24" :sm="12" :md="8">
                  <el-form-item label="Previous admission">
                    <el-input v-model="form.prev_admission" clearable />
                  </el-form-item>
                </el-col>
                <el-col :xs="24" :sm="12" :md="8">
                  <el-form-item label="Previous surgeries">
                    <el-input v-model="form.prev_surgeries" clearable />
                  </el-form-item>
                </el-col>
                <el-col :xs="24" :sm="12" :md="8">
                  <el-form-item label="Allergies">
                    <el-input v-model="form.allergies" clearable />
                  </el-form-item>
                </el-col>
              </el-row>
            </el-form>
          </section>

          <el-divider class="ua-divider ua-divider--nested" />

          <section class="ua-section-card ua-section-card--nested" aria-labelledby="ua-h-pmh-resp">
            <h3 id="ua-h-pmh-resp" class="ua-section-card__title">
              Respiratory &amp; infectious
            </h3>
            <el-form label-position="top" class="ua-form">
              <el-row :gutter="24">
                <el-col :xs="24" :md="12">
                  <el-form-item label="Asthma / allergic rhinitis / atopic dermatitis">
                    <el-input v-model="form.asthma" clearable />
                  </el-form-item>
                </el-col>
                <el-col :xs="24" :sm="12" :md="6">
                  <el-form-item label="TB">
                    <el-input v-model="form.tb" clearable />
                  </el-form-item>
                </el-col>
                <el-col :xs="24" :sm="12" :md="6">
                  <el-form-item label="Seizure">
                    <el-input v-model="form.seizure" clearable />
                  </el-form-item>
                </el-col>
              </el-row>
            </el-form>
          </section>

          <el-divider class="ua-divider ua-divider--nested" />

          <section class="ua-section-card ua-section-card--nested" aria-labelledby="ua-h-pmh-chronic">
            <h3 id="ua-h-pmh-chronic" class="ua-section-card__title">
              Chronic conditions
            </h3>
            <el-form label-position="top" class="ua-form">
              <el-row :gutter="24">
                <el-col :xs="24" :sm="12" :md="8">
                  <el-form-item label="Hypertension">
                    <el-input v-model="form.hypertension" clearable />
                  </el-form-item>
                </el-col>
                <el-col :xs="24" :sm="12" :md="8">
                  <el-form-item label="Diabetes">
                    <el-input v-model="form.diabetes" clearable />
                  </el-form-item>
                </el-col>
                <el-col :xs="24" :sm="12" :md="8">
                  <el-form-item label="COPD">
                    <el-input v-model="form.copd" clearable />
                  </el-form-item>
                </el-col>
              </el-row>
            </el-form>
          </section>

          <el-divider class="ua-divider ua-divider--nested" />

          <section class="ua-section-card ua-section-card--nested" aria-labelledby="ua-h-pmh-other">
            <h3 id="ua-h-pmh-other" class="ua-section-card__title">
              Other notes
            </h3>
            <el-form label-position="top" class="ua-form">
              <el-row :gutter="24">
                <el-col :span="24">
                  <el-form-item label="Others">
                    <el-input
                      v-model="form.pmh_others"
                      type="textarea"
                      :autosize="{ minRows: 3, maxRows: 8 }"
                      placeholder="Additional past medical history"
                    />
                  </el-form-item>
                </el-col>
              </el-row>
            </el-form>
          </section>

          <section class="ua-section-card ua-section-card--nested" aria-labelledby="ua-h-fam">
            <h3 id="ua-h-fam" class="ua-section-card__title">
              Family history
            </h3>
            <el-form label-position="top" class="ua-form">
              <el-form-item label="Conditions">
                <el-checkbox-group v-model="form.fam" size="large" class="ua-checkbox-group">
                  <el-checkbox-button label="Hypertension">
                    Hypertension
                  </el-checkbox-button>
                  <el-checkbox-button label="Diabetes Mellitus">
                    Diabetes mellitus
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
                <el-input
                  v-model="form.fam_others"
                  :autosize="{ minRows: 2, maxRows: 6 }"
                  type="textarea"
                  placeholder="Additional family history"
                />
              </el-form-item>
            </el-form>
          </section>

          <section class="ua-section-card ua-section-card--nested" aria-labelledby="ua-h-soc">
            <h3 id="ua-h-soc" class="ua-section-card__title">
              Social &amp; environment
            </h3>
            <el-form label-position="top" class="ua-form">
              <el-form-item label="History">
                <el-checkbox-group v-model="form.soc" size="large" class="ua-checkbox-group">
                  <el-checkbox-button label="Smoking">
                    Smoking
                  </el-checkbox-button>
                  <el-checkbox-button label="Alcoholic Beverage Drinking">
                    Alcoholic beverage drinking
                  </el-checkbox-button>
                </el-checkbox-group>
              </el-form-item>
              <el-form-item label="Others">
                <el-input
                  v-model="form.soc_others"
                  :autosize="{ minRows: 2, maxRows: 6 }"
                  type="textarea"
                  placeholder="Additional social or environmental notes"
                />
              </el-form-item>
              <el-form-item label="Vaccinations">
                <el-input
                  v-model="form.vaccination_sup"
                  :autosize="{ minRows: 2, maxRows: 6 }"
                  type="textarea"
                  placeholder="Vaccination history or supplements"
                />
              </el-form-item>
            </el-form>
          </section>
        </div>

        <section class="ua-section-card ua-drive-section" aria-labelledby="ua-h-attachments">
          <h3 id="ua-h-attachments" class="ua-section-card__title">
            Attachments
            <span v-if="attachments.length" class="ua-drive-count">{{ attachments.length }} items</span>
          </h3>
          <div class="ua-section-card__body ua-attachments-body">
            <div class="ua-drive">
              <header class="ua-drive__toolbar">
                <div class="ua-drive__toolbar-left">
                  <el-upload
                    ref="uploadRef"
                    action="#"
                    :auto-upload="false"
                    multiple
                    :show-file-list="false"
                    :disabled="isUploading"
                    :on-change="handleGalleryUploadChange"
                  >
                    <el-button type="primary" size="small" icon="el-icon-upload2">
                      Upload
                    </el-button>
                  </el-upload>
                  <el-button
                    v-if="pendingGalleryFiles.length"
                    size="small"
                    type="success"
                    :loading="isUploading"
                    @click="submitUpload"
                  >
                    Upload {{ pendingGalleryFiles.length }} file{{ pendingGalleryFiles.length === 1 ? '' : 's' }}
                  </el-button>
                  <el-button
                    v-if="gallerySelectedIds.length"
                    size="small"
                    type="danger"
                    plain
                    icon="el-icon-delete"
                    @click="deleteSelectedAttachments"
                  >
                    Delete ({{ gallerySelectedIds.length }})
                  </el-button>
                </div>
                <div class="ua-drive__toolbar-right">
                  <el-input
                    v-model="gallerySearch"
                    size="small"
                    clearable
                    prefix-icon="el-icon-search"
                    placeholder="Search in attachments"
                    class="ua-drive__search"
                  />
                  <el-button-group class="ua-drive__view-toggle">
                    <el-button
                      size="small"
                      :type="galleryView === 'grid' ? 'primary' : 'default'"
                      icon="el-icon-menu"
                      aria-label="Grid view"
                      @click="galleryView = 'grid'"
                    />
                    <el-button
                      size="small"
                      :type="galleryView === 'list' ? 'primary' : 'default'"
                      icon="el-icon-s-operation"
                      aria-label="List view"
                      @click="galleryView = 'list'"
                    />
                  </el-button-group>
                </div>
              </header>

              <div v-if="isUploading" class="ua-upload-progress" aria-live="polite">
                <el-progress
                  :percentage="uploadProgress"
                  :status="uploadProgress === 100 ? 'success' : ''"
                />
                <p class="ua-upload-status">{{ uploadStatus }}</p>
              </div>

              <el-upload
                drag
                action="#"
                :auto-upload="false"
                multiple
                :show-file-list="false"
                :disabled="isUploading"
                class="ua-drive__dropzone"
                :class="{ 'ua-drive__dropzone--hidden': attachments.length > 0 }"
                :on-change="handleGalleryUploadChange"
              >
                <i class="el-icon-upload" />
                <p>Drop files here to upload</p>
              </el-upload>

              <div v-if="galleryDisplayItems.length" class="ua-drive__groups">
                <section
                  v-for="section in galleryDisplayByDate"
                  :key="section.key"
                  class="ua-drive__group"
                  :aria-labelledby="'ua-drive-date-' + section.key"
                >
                  <h4
                    :id="'ua-drive-date-' + section.key"
                    class="ua-drive__date-heading"
                  >
                    {{ section.label }}
                    <span class="ua-drive__date-count">({{ section.items.length }})</span>
                  </h4>
                  <div
                    class="ua-drive__content"
                    :class="'ua-drive__content--' + galleryView"
                  >
                    <button
                      v-for="item in section.items"
                      :key="item.id"
                      type="button"
                      class="ua-drive-item"
                      :class="{
                        'ua-drive-item--selected': isGallerySelected(item.id),
                        'ua-drive-item--list': galleryView === 'list',
                      }"
                      @click.exact="onGalleryItemClick(item)"
                      @click.ctrl.exact="toggleGallerySelect(item.id)"
                      @click.meta.exact="toggleGallerySelect(item.id)"
                    >
                      <span
                        class="ua-drive-item__check"
                    :class="{ 'ua-drive-item__check--on': isGallerySelected(item.id) }"
                    @click.stop="toggleGallerySelect(item.id)"
                  >
                    <i v-if="isGallerySelected(item.id)" class="el-icon-check" />
                  </span>
                  <span class="ua-drive-item__thumb">
                    <img
                      v-if="isImageAttachment(item.extension)"
                      :src="item.newfile"
                      :alt="previewAltText(item)"
                      loading="lazy"
                    >
                    <span v-else class="ua-drive-item__file-icon">
                      <i :class="driveFileIcon(item.extension)" />
                      <span>{{ fileExtensionLabel(item.extension) }}</span>
                    </span>
                  </span>
                  <span class="ua-drive-item__info">
                    <span class="ua-drive-item__name" :title="displayFileName(item)">
                      {{ displayFileName(item) }}
                    </span>
                    <span class="ua-drive-item__meta">{{ item.created_dt }}</span>
                  </span>
                    </button>
                  </div>
                </section>
              </div>

              <p v-else-if="attachments.length && gallerySearch" class="ua-attach-empty">
                No files match "{{ gallerySearch }}".
              </p>
              <p v-else class="ua-attach-empty">
                No attachments yet. Click Upload or drop files here.
              </p>
            </div>

            <transition name="ua-lightbox-fade">
              <div
                v-if="lightboxOpen"
                class="ua-lightbox"
                role="dialog"
                aria-modal="true"
                :aria-label="lightboxCurrentItem ? displayFileName(lightboxCurrentItem) : 'Preview'"
                @click.self="closeLightbox"
              >
                <header class="ua-lightbox__bar">
                  <button type="button" class="ua-lightbox__btn" aria-label="Close" @click="closeLightbox">
                    <i class="el-icon-close" />
                  </button>
                  <p class="ua-lightbox__title">
                    {{ lightboxCurrentItem ? displayFileName(lightboxCurrentItem) : '' }}
                  </p>
                  <div class="ua-lightbox__actions">
                    <span class="ua-lightbox__counter">{{ lightboxPositionLabel }}</span>
                    <button
                      type="button"
                      class="ua-lightbox__btn"
                      aria-label="Download"
                      @click="downloadAttachment(lightboxCurrentItem)"
                    >
                      <i class="el-icon-download" />
                    </button>
                    <button
                      type="button"
                      class="ua-lightbox__btn ua-lightbox__btn--danger"
                      aria-label="Delete"
                      @click="deleteLightboxItem"
                    >
                      <i class="el-icon-delete" />
                    </button>
                  </div>
                </header>

                <button
                  v-if="lightboxHasPrev"
                  type="button"
                  class="ua-lightbox__nav ua-lightbox__nav--prev"
                  aria-label="Previous"
                  @click="lightboxGo(-1)"
                >
                  <i class="el-icon-arrow-left" />
                </button>
                <button
                  v-if="lightboxHasNext"
                  type="button"
                  class="ua-lightbox__nav ua-lightbox__nav--next"
                  aria-label="Next"
                  @click="lightboxGo(1)"
                >
                  <i class="el-icon-arrow-right" />
                </button>

                <div class="ua-lightbox__stage">
                  <iframe
                    v-if="lightboxIsPdf"
                    :key="'pdf-' + lightboxIndex"
                    :src="lightboxCurrentItem.newfile"
                    title="PDF preview"
                    class="ua-lightbox__pdf"
                  />
                  <img
                    v-else-if="lightboxCurrentItem && isImageAttachment(lightboxCurrentItem.extension)"
                    :key="'img-' + lightboxIndex"
                    :src="lightboxCurrentItem.newfile"
                    :alt="previewAltText(lightboxCurrentItem)"
                    class="ua-lightbox__image"
                  >
                  <div v-else class="ua-lightbox__unsupported">
                    <i class="el-icon-document" />
                    <p>Preview not available</p>
                    <el-button type="primary" size="small" @click="downloadAttachment(lightboxCurrentItem)">
                      Download
                    </el-button>
                  </div>
                </div>

                <footer v-if="galleryDisplayItems.length > 1" class="ua-lightbox__filmstrip">
                  <button
                    v-for="(item, index) in galleryDisplayItems"
                    :key="'strip-' + item.id"
                    type="button"
                    class="ua-lightbox__thumb"
                    :class="{ 'ua-lightbox__thumb--active': index === lightboxIndex }"
                    :aria-label="'View ' + displayFileName(item)"
                    @click="openLightboxAt(index)"
                  >
                    <img
                      v-if="isImageAttachment(item.extension)"
                      :src="item.newfile"
                      alt=""
                    >
                    <span v-else class="ua-lightbox__thumb-file">
                      <i :class="driveFileIcon(item.extension)" />
                    </span>
                  </button>
                </footer>
              </div>
            </transition>
          </div>
        </section>

      </div>
    </div>

    <PatientRecordVitalsDialog
      :visible.sync="recordVitalsDialogVisible"
      :patient-id="$route.params.pid"
      @saved="onVitalsSaved"
    />
  </el-card>
</template>

<script>
import role from '@/directive/role/index.js';
import LoadingOverlay from '../../components/loading.vue';
import ConsultationHistoryPanel from '@/views/components/ConsultationHistoryPanel.vue';
import PatientAppointmentActions from '@/views/components/PatientAppointmentActions.vue';
import PatientRecordVitalsDialog from '@/views/components/PatientRecordVitalsDialog.vue';
import Patients from '@/api/patients';
import Pagination from '@/components/Pagination';
import moment from 'moment-timezone';
import checkRole from '@/utils/role'; // Role checking
import { orientAndCompressImage } from '@/utils/orientImage';
import heic2any from 'heic2any';
export default {
  components: {
    Pagination,
    LoadingOverlay,
    ConsultationHistoryPanel,
    PatientAppointmentActions,
    PatientRecordVitalsDialog,
  },
  directives: { role },
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
      isPdf: false,
      viewFileModel: false,
      sourceFile: null,
      galleryView: 'grid',
      gallerySearch: '',
      lightboxOpen: false,
      lightboxIndex: 0,
      gallerySelectedIds: [],
      pendingGalleryFiles: [],
      pageloading: true,
      isProcessing: false,
      isProcessingAdolecents: false,
      isProcessingVax: false,
      isUploading: false,
      // Upload progress tracking
      uploadProgress: 0,
      uploadStatus: '',
      popconfirmDeleteProblem: false,
      additionalIdForDelete: 0,
      selectedOldRecords: {},
      oldRecordsdialogVisible: false,
      old_records: [],
      dialogVisible: false,
      selectedImage: {},
      attachments: [],
      consultationHistory: [],
      consultationHistoryLoading: false,
      recordVitalsDialogVisible: false,
      pid: '',
      past_meds: [
        'Bronchial Asthma',
        'Hypertension',
        'Previous MI',
        'Allergies',
        'Prior Surgery',
        'Prior Angina',
        'PAD',
        'PTB',
        'Previous Myocardial infraction',
        'Dyslipidemia',
        'COPD',
      ],
      formProblem: {
        id: 0,
        description: '',
        value: '',
        isactive: true,
        pid: this.$route.params.id,
      },
      formAdolecents: {
        id: 0,
        description: '',
        value: '',
        pid: this.$route.params.id,
      },
      formVax: {
        id: 0,
        vax: '',
        first_dose: '',
        second_dose: '',
        third_dose: '',
        booster: '',
        pid: this.$route.params.id,
      },
      formGd: {
        id: 0,
        gross_motor: '',
        gross_motor_age: '',
        fine_motor: '',
        fine_motor_age: '',
        language: '',
        language_age: '',
        social: '',
        social_age: '',
        pid: this.$route.params.id,
      },
      form: {
        hypertension: '',
        prev_admission: '',
        prev_surgeries: '',
        allergies: '',
        asthma: '',
        newborn_hearing: '',
        tb: '',
        seizure: '',
        copd: '',
        diabetes: '',
        mo_comorb: '',
        fa_comorb: '',
        blood_type: '',
        number_members: 0,
        water_source: '',
        breastfeed_dur: '',
        milk_dur: '',
        complementary_feeding: '',
        ob_score: '',
        cog_aog: '',
        maternal_illness: '',
        prenatal_checkup: '',
        vaccination_sup: '',
        maternal_age_dur_preg: '',
        maternal_b_type: '',
        term_pre_post: '',
        nsd_cs: '',
        birth_weight: '',
        cry: '',
        palce_delivery: '',
        complications: '',
        caregiver_name: '',
        caregiver_age: 0,
        caregiver_rel: '',
        caregiver_contact: '',
        caregiver_occupation: '',
        siblings_details: '',
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
        contactno: '',
        email: '',
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
        files: [],
      },
      updating: false,
      uniqueArray: [],
      dialogFormVisible: false,
      dialogFormAdolecentsVisible: false,
      dialogFormVaxVisible: false,
      dialogFormGdVisible: false,
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        role: '',
        date: moment().format('YYYY-MM-DD'),
        isdone: false,
        state: 0, // meaning not done
      },
      rules: {
        description: [
          { required: true, message: 'Please input your description', trigger: 'blur' },
        ],
      },
      rulesAdolecense: {
        description: [
          { required: true, message: 'Please input your description', trigger: 'blur' },
        ],
      },
      rulesVax: {
        vax: [
          { required: true, message: 'Please input your Vaccination', trigger: 'blur' },
        ],
      },
      additionalList: [],
      adolecents: [],
      vaxs: [],
      growthdevs: [],
    };
  },
  computed: {
    /**
     * Groups attachments by calendar month (newest months first).
     * `created_dt` comes from the API as e.g. "April 05, 2026" (PHP F d, Y).
     */
    galleryDisplayItems() {
      const q = (this.gallerySearch || '').trim().toLowerCase();
      const dateFormats = ['MMMM DD, YYYY', 'MMMM D, YYYY', 'MMM DD, YYYY', 'MMM D, YYYY'];
      let list = [...this.attachments];
      if (q) {
        list = list.filter((item) =>
          this.displayFileName(item).toLowerCase().includes(q)
        );
      }
      list.sort((a, b) => {
        const ma = moment(a.created_dt, dateFormats, true);
        const mb = moment(b.created_dt, dateFormats, true);
        return (mb.isValid() ? mb.valueOf() : 0) - (ma.isValid() ? ma.valueOf() : 0);
      });
      return list;
    },
    galleryDisplayByDate() {
      const dateFormats = ['MMMM DD, YYYY', 'MMMM D, YYYY', 'MMM DD, YYYY', 'MMM D, YYYY'];
      const bucket = new Map();
      this.galleryDisplayItems.forEach((item) => {
        const m = moment(item.created_dt, dateFormats, true);
        const key = m.isValid() ? m.format('YYYY-MM') : '_unknown';
        if (!bucket.has(key)) {
          bucket.set(key, {
            key,
            sortKey: m.isValid() ? m.format('YYYY-MM') : '0000-00',
            label: m.isValid() ? m.format('MMMM YYYY') : 'Unknown date',
            items: [],
          });
        }
        bucket.get(key).items.push(item);
      });
      const list = Array.from(bucket.values());
      list.sort((a, b) => {
        if (a.key === '_unknown') {
          return 1;
        }
        if (b.key === '_unknown') {
          return -1;
        }
        return b.sortKey.localeCompare(a.sortKey);
      });
      return list;
    },
    lightboxCurrentItem() {
      return this.galleryDisplayItems[this.lightboxIndex] || null;
    },
    lightboxHasPrev() {
      return this.lightboxIndex > 0;
    },
    lightboxHasNext() {
      return this.lightboxIndex < this.galleryDisplayItems.length - 1;
    },
    lightboxIsPdf() {
      const item = this.lightboxCurrentItem;
      if (!item) {
        return false;
      }
      return String(item.extension || '')
        .toLowerCase()
        .replace(/^\./, '') === 'pdf';
    },
    lightboxPositionLabel() {
      if (!this.galleryDisplayItems.length) {
        return '';
      }
      return `${this.lightboxIndex + 1} / ${this.galleryDisplayItems.length}`;
    },
    attachmentsByMonthYear() {
      const bucket = new Map();
      this.attachments.forEach((item) => {
        const m = moment(
          item.created_dt,
          ['MMMM DD, YYYY', 'MMMM D, YYYY', 'MMM DD, YYYY', 'MMM D, YYYY'],
          true
        );
        const key = m.isValid() ? m.format('YYYY-MM') : '_unknown';
        if (!bucket.has(key)) {
          bucket.set(key, {
            key,
            sortKey: m.isValid() ? m.format('YYYY-MM') : '0000-00',
            label: m.isValid() ? m.format('MMMM YYYY') : 'Unknown date',
            items: [],
          });
        }
        bucket.get(key).items.push(item);
      });
      const list = Array.from(bucket.values());
      list.sort((a, b) => {
        if (a.key === '_unknown') {
          return 1;
        }
        if (b.key === '_unknown') {
          return -1;
        }
        return b.sortKey.localeCompare(a.sortKey);
      });
      return list;
    },
    attachmentImageUrls() {
      const urls = [];
      this.attachmentsByMonthYear.forEach((section) => {
        section.items.forEach((item) => {
          if (this.isImageAttachment(item.extension)) {
            urls.push(item.newfile);
          }
        });
      });
      return urls;
    },
    headerPhotoSrc() {
      const fp = this.form.profile;
      if (fp && Array.isArray(fp) && fp.length > 0) {
        const f = fp[0];
        if (f.url) {
          return f.url;
        }
        if (f.raw) {
          try {
            return URL.createObjectURL(f.raw);
          } catch (e) {
            return '';
          }
        }
      }
      const p = this.profile;
      if (!p || !p.patientid) {
        return p && p.profile ? p.profile : '';
      }
      return p.isold_patient === 1
        ? '/public/photos/' + p.patientid + '.jpg'
        : p.profile || '';
    },
    displayPatientName() {
      if (this.profile && this.profile.patientname) {
        return this.profile.patientname;
      }
      const parts = [this.form.lastname, this.form.firstname, this.form.middlename].filter(
        (x) => x && String(x).trim()
      );
      return parts.length ? parts.join(', ') : '—';
    },
    formattedPatientBirthdate() {
      const d = this.form.birthdate || (this.profile && this.profile.birthdate);
      if (!d) {
        return '—';
      }
      const m = moment(d);
      return m.isValid() ? m.format('MMMM DD, YYYY') : '—';
    },
    patientAgeDisplay() {
      const d = this.form.birthdate || (this.profile && this.profile.birthdate);
      if (!d) {
        return '—';
      }
      const age = this.computeAgeFromDate(d);
      return age !== null && age !== undefined ? String(age) : '—';
    },
  },
  watch: {
    // Watch for changes in the any props
    profile: {
      handler(newValue) {
        this.patientid_id = newValue.id;
        this.form.patientid = newValue.patientid;
        this.form.firstname = newValue.firstname
          ? newValue.firstname
          : this.form.firstname;
        this.form.middlename = newValue.middlename
          ? newValue.middlename
          : this.form.middlename;
        this.form.lastname = newValue.lastname ? newValue.lastname : this.form.lastname;
        this.form.address = newValue.address ? newValue.address : this.form.address;
        this.form.birthdate = newValue.birthdate
          ? newValue.birthdate
          : this.form.birthdate;
        this.form.contactno = newValue.contactno
          ? newValue.contactno
          : this.form.contactno;
        this.form.email = newValue.email
          ? newValue.email
          : this.form.email;
        this.form.occupation = newValue.occupation
          ? newValue.occupation
          : this.form.occupation;
        this.form.sex = newValue.sex ? newValue.sex : this.form.sex;
        this.form.civil_status = newValue.civil_status
          ? newValue.civil_status
          : this.form.civil_status;
        this.form.referredby = newValue.referredby
          ? newValue.referredby
          : this.form.referredby;
        this.form.blood_type = newValue.blood_type
          ? newValue.blood_type
          : this.form.blood_type;
        this.form.prev_admission = newValue.prev_admission ?? this.form.prev_admission;
        this.form.prev_surgeries = newValue.prev_surgeries
          ? newValue.prev_surgeries
          : this.form.prev_surgeries;
        this.form.allergies = newValue.allergies
          ? newValue.allergies
          : this.form.allergies;
        this.form.asthma = newValue.asthma ? newValue.asthma : this.form.asthma;
        this.form.newborn_hearing = newValue.newborn_hearing
          ? newValue.newborn_hearing
          : this.form.newborn_hearing;
        this.form.tb = newValue.tb ? newValue.tb : this.form.tb;
        this.form.seizure = newValue.seizure ? newValue.seizure : this.form.seizure;
        this.form.hypertension = newValue.hypertension ? newValue.hypertension : this.form.hypertension;
        this.form.copd = newValue.copd ? newValue.copd : this.form.copd;
        this.form.diabetes = newValue.diabetes ? newValue.diabetes : this.form.diabetes;
        this.form.mo_comorb = newValue.mo_comorb
          ? newValue.mo_comorb
          : this.form.mo_comorb;
        this.form.fa_comorb = newValue.fa_comorb
          ? newValue.fa_comorb
          : this.form.fa_comorb;
        this.form.number_members = newValue.number_members
          ? newValue.number_members
          : this.form.number_members;
        this.form.water_source = newValue.water_source
          ? newValue.water_source
          : this.form.water_source;
        this.form.breastfeed_dur = newValue.breastfeed_dur
          ? newValue.breastfeed_dur
          : this.form.breastfeed_dur;
        this.form.milk_dur = newValue.milk_dur ? newValue.milk_dur : this.form.milk_dur;
        this.form.complementary_feeding = newValue.complementary_feeding
          ? newValue.complementary_feeding
          : this.form.complementary_feeding;
        this.form.ob_score = newValue.ob_score ? newValue.ob_score : this.form.ob_score;
        this.form.cog_aog = newValue.cog_aog ? newValue.cog_aog : this.form.cog_aog;
        this.form.maternal_illness = newValue.maternal_illness
          ? newValue.maternal_illness
          : this.form.maternal_illness;
        this.form.prenatal_checkup = newValue.prenatal_checkup
          ? newValue.prenatal_checkup
          : this.form.prenatal_checkup;
        this.form.vaccination_sup = newValue.vaccination_sup
          ? newValue.vaccination_sup
          : this.form.vaccination_sup;
        this.form.maternal_age_dur_preg = newValue.maternal_age_dur_preg
          ? newValue.maternal_age_dur_preg
          : this.form.maternal_age_dur_preg;
        this.form.maternal_b_type = newValue.maternal_b_type
          ? newValue.maternal_b_type
          : this.form.maternal_b_type;
        this.form.term_pre_post = newValue.term_pre_post
          ? newValue.term_pre_post
          : this.form.term_pre_post;
        this.form.nsd_cs = newValue.nsd_cs ? newValue.nsd_cs : this.form.nsd_cs;
        this.form.birth_weight = newValue.birth_weight
          ? newValue.birth_weight
          : this.form.birth_weight;
        this.form.cry = newValue.cry ? newValue.cry : this.form.cry;
        this.form.palce_delivery = newValue.palce_delivery
          ? newValue.palce_delivery
          : this.form.palce_delivery;
        this.form.complications = newValue.complications
          ? newValue.complications
          : this.form.complications;
        this.form.caregiver_name = newValue.caregiver_name
          ? newValue.caregiver_name
          : this.form.caregiver_name;
        this.form.caregiver_age = newValue.caregiver_age
          ? newValue.caregiver_age
          : this.form.caregiver_age;
        this.form.caregiver_rel = newValue.caregiver_rel
          ? newValue.caregiver_rel
          : this.form.caregiver_rel;
        this.form.caregiver_contact = newValue.caregiver_contact
          ? newValue.caregiver_contact
          : this.form.caregiver_contact;
        this.form.caregiver_occupation = newValue.caregiver_occupation
          ? newValue.caregiver_occupation
          : this.form.caregiver_occupation;
        this.form.siblings_details = newValue.siblings_details
          ? newValue.siblings_details
          : this.form.siblings_details;

        /* const pmh = newValue.pmh.split(",");
        pmh.forEach((element) => {
          this.form.pmh.push(element);
        }); */
        this.form.pmh_others = newValue.pmh_others
          ? newValue.pmh_others
          : this.form.pmh_others;
        if (newValue.fam){
          const fam = newValue.fam.split(',');
          fam.forEach((element) => {
            this.form.fam.push(element);
          });
        }
        this.form.fam_others = newValue.fam_others;
        if (newValue.soc){
          const soc = newValue.soc.split(',');
          soc.forEach((element) => {
            this.form.soc.push(element);
          });
        }
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
      handler(v) {},
      immediate: true,
    },
  },
  created() {
    this.past_consult();
    this.get_attachments();
    /* this.getProblemList();
    this.getAdolecentsList();
    this.getVaxsList();
    this.getGrowthDevList(); */
  },
  mounted() {
    window.addEventListener('keydown', this.onLightboxKeydown);
  },
  beforeDestroy() {
    window.removeEventListener('keydown', this.onLightboxKeydown);
    document.body.style.overflow = '';
  },
  methods: {
    checkRole,
    handleCreateAdolecents() {
      this.dialogFormAdolecentsVisible = true;
      this.$nextTick(() => {
        this.$refs['appFormAdolecents'].clearValidate();
      });
    },
    handleCreateVax() {
      this.dialogFormVaxVisible = true;
      this.$nextTick(() => {
        this.$refs['appFormVax'].clearValidate();
      });
    },
    handleCreateGd() {
      this.dialogFormGdVisible = true;
      this.$nextTick(() => {
        this.$refs['appFormGrowthDev'].clearValidate();
      });
    },
    openDialog(image) {
      this.selectedImage = image;
      this.dialogVisible = true;
    },
    async handleProfileUploadChange(file, fileList) {
      let list = fileList.slice(-1);
      const item = list[0];
      if (item && item.raw && item.raw.type && item.raw.type.startsWith('image/')) {
        try {
          const normalized = await this.compressImage(item.raw, 0.92, 4096, 4096);
          if (item.url) {
            try {
              URL.revokeObjectURL(item.url);
            } catch (e) {
              /* ignore */
            }
          }
          list = [{
            ...item,
            raw: normalized,
            url: URL.createObjectURL(normalized),
          }];
        } catch (e) {
          console.error('Profile photo orientation fix failed:', e);
        }
      }
      this.form.profile = list;
      this.$emit('return-img', list);
      this.$store.dispatch('globalvar/changeval', {
        key: 'img_val',
        value: list,
      });
    },
    computeAgeFromDate(dateString) {
      const today = new Date();
      const birthDate = new Date(dateString);
      if (Number.isNaN(birthDate.getTime())) {
        return null;
      }
      let age = today.getFullYear() - birthDate.getFullYear();
      const m = today.getMonth() - birthDate.getMonth();
      if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
      }
      return age;
    },
    async onSubmit() {
      this.pageloading = true;
      const formData = new FormData();
      for (const key in this.form) {
        formData.append(key, this.form[key]);
      }

      if (this.form.profile.length > 0) {
        formData.append('profile_pic', this.form.profile[0].raw);
      }

      let socVal = '';
      this.form.soc.forEach(element => {
        socVal += element;
      });
      this.form.soc = socVal;

      let famVal = '';
      this.form.fam.forEach(element => {
        famVal += element;
        console.log(element);
      });
      this.form.fam = famVal;

      await Patients.update(formData)
        .then((response) => {
          this.$message({
            message: 'Patient profile has been updated successfully.',
            type: 'success',
            duration: 5 * 1000,
          });
          location.reload();
          this.pageloading = false;
        })
        .catch((err) => {
          console.error('Error adding suggestions:', err);
        });
    },
    async past_consult() {
      this.consultationHistoryLoading = true;
      await Patients.getPatientConsultationHistory(this.$route.params.pid)
        .then((response) => {
          this.consultationHistory = response.data || [];
        })
        .catch((err) => {
          console.error('Error loading consultation history:', err);
        })
        .finally(() => {
          this.consultationHistoryLoading = false;
        });
    },
    onVitalsSaved() {
      if (this.$refs.consultationHistoryPanel) {
        this.$refs.consultationHistoryPanel.refreshVitalsHistory();
      }
    },
    async get_attachments() {
      this.attachments = [];
      await Patients.getAttachments(this.$route.params.pid)
        .then((response) => {
          this.attachments = response.data;
          this.pageloading = false;
        })
        .catch((err) => {
          console.error('Error adding suggestions:', err);
          this.pageloading = false;
        });
    },
    editAdditional(index, row) {
      this.dialogFormVisible = true;
      this.formProblem.id = row.id;
      this.formProblem.description = row.description;
      this.formProblem.value = row.value;
      this.formProblem.isactive = row.ischeck == 1;
    },
    handleChange(file, fileList) {
      this.handleGalleryUploadChange(file, fileList);
    },
    handleGalleryUploadChange(file, fileList) {
      this.form_att.files = fileList.map((fileItem) => fileItem.raw).filter(Boolean);
      this.pendingGalleryFiles = fileList.map((f) => ({
        uid: f.uid,
        name: f.name,
      }));
    },
    isGallerySelected(id) {
      return this.gallerySelectedIds.includes(id);
    },
    toggleGallerySelect(id) {
      const i = this.gallerySelectedIds.indexOf(id);
      if (i === -1) {
        this.gallerySelectedIds.push(id);
      } else {
        this.gallerySelectedIds.splice(i, 1);
      }
    },
    clearGallerySelection() {
      this.gallerySelectedIds = [];
    },
    onGalleryItemClick(item) {
      const index = this.galleryDisplayItems.findIndex((i) => i.id === item.id);
      if (index >= 0) {
        this.openLightboxAt(index);
      }
    },
    openLightboxAt(index) {
      this.lightboxIndex = index;
      this.lightboxOpen = true;
      document.body.style.overflow = 'hidden';
    },
    closeLightbox() {
      this.lightboxOpen = false;
      document.body.style.overflow = '';
    },
    lightboxGo(delta) {
      const next = this.lightboxIndex + delta;
      if (next >= 0 && next < this.galleryDisplayItems.length) {
        this.lightboxIndex = next;
      }
    },
    onLightboxKeydown(e) {
      if (!this.lightboxOpen) {
        return;
      }
      if (e.key === 'Escape') {
        this.closeLightbox();
      } else if (e.key === 'ArrowLeft') {
        this.lightboxGo(-1);
      } else if (e.key === 'ArrowRight') {
        this.lightboxGo(1);
      }
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
    downloadAttachment(item) {
      if (!item || !item.newfile) {
        return;
      }
      const link = document.createElement('a');
      link.href = item.newfile;
      link.target = '_blank';
      link.rel = 'noopener';
      link.download = item.fname || this.displayFileName(item);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    },
    deleteLightboxItem() {
      const item = this.lightboxCurrentItem;
      if (!item) {
        return;
      }
      this.$confirm('Delete this attachment permanently?', 'Delete file', {
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
        type: 'warning',
      })
        .then(() => {
          return Patients.deleteAttachments(item.id);
        })
        .then(() => {
          this.$message.success('Attachment deleted');
          const idx = this.lightboxIndex;
          return this.get_attachments();
        })
        .then(() => {
          if (!this.galleryDisplayItems.length) {
            this.closeLightbox();
          } else if (this.lightboxIndex >= this.galleryDisplayItems.length) {
            this.lightboxIndex = this.galleryDisplayItems.length - 1;
          }
        })
        .catch(() => {});
    },
    deleteSelectedAttachments() {
      if (!this.gallerySelectedIds.length) {
        return;
      }
      const count = this.gallerySelectedIds.length;
      this.$confirm(`Delete ${count} selected file${count === 1 ? '' : 's'}?`, 'Delete files', {
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
        type: 'warning',
      })
        .then(() =>
          Promise.all(
            this.gallerySelectedIds.map((id) => Patients.deleteAttachments(id))
          )
        )
        .then(() => {
          this.$message.success('Selected files deleted');
          this.clearGallerySelection();
          this.get_attachments();
          if (this.lightboxOpen && !this.galleryDisplayItems.length) {
            this.closeLightbox();
          }
        })
        .catch(() => {});
    },
    compressImage(file, quality = 0.8, maxWidth = 1920, maxHeight = 1080) {
      return orientAndCompressImage(file, { quality, maxWidth, maxHeight });
    },
    async submitUpload() {
      if (!this.form_att.files || !this.form_att.files.length) {
        this.$message.warning('Select files to upload first.');
        return;
      }
      // Initialize upload progress
      this.uploadProgress = 0;
      this.isUploading = true;
      this.uploadStatus = 'Preparing files...';

      const formData = new FormData();
      formData.append('patientid', this.form_att.patientid);

      const totalFiles = this.form_att.files.length;
      let processedFiles = 0;

      for (let i = 0; i < this.form_att.files.length; i++) {
        const file = this.form_att.files[i];
        const extension = file.name.split('.').pop().toLowerCase();

        // Update progress
        this.uploadStatus = `Processing file ${i + 1} of ${totalFiles}: ${file.name}`;
        this.uploadProgress = (processedFiles / totalFiles) * 30; // 30% for processing

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
            this.uploadProgress = 0;
            this.uploadStatus = '';
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

      try {
        this.uploadStatus = 'Uploading files...';
        this.uploadProgress = 30; // Start upload at 30%

        const response = await Patients.addAttachments(formData);

        // Upload successful
        this.uploadProgress = 100;
        this.uploadStatus = 'Upload completed!';

        this.get_attachments(this.form_att.patientid);
        this.$message.success('File uploaded successfully!');
        if (this.$refs.uploadRef) {
          this.$refs.uploadRef.clearFiles();
        }
        this.form_att.files = [];
        this.pendingGalleryFiles = [];

        // Reset upload state
        this.isUploading = false;
        this.uploadProgress = 0;
        this.uploadStatus = '';
      } catch (err) {
        console.error('Error uploading files:', err);
        this.$message.error('Upload failed.');

        // Reset upload state on failure
        this.isUploading = false;
        this.uploadProgress = 0;
        this.uploadStatus = '';
      }
    },
    getBase64(file) {
      return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = () => resolve(reader.result);
        reader.onerror = (error) => reject(error);
      });
    },
    imgSrc(newfile, oldfile, isold) {
      try {
        if (isold === 0) {
          return newfile;
        } else {
          return `public/${oldfile}`;
        }
      } catch (e) {
        return `public/${oldfile}`;
      }
    },
    async deleteAtt(id) {
      await this.$confirm('Are you done with this file?', 'Warning', {
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
              this.get_attachments();
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
    async deletePatient() {
      await Patients.delete(this.$route.params.pid)
        .then((response) => {
          this.$message({
            message: 'Patient has been deleted successfully.',
            type: 'success',
            duration: 5 * 1000,
          });
          this.$router.push({ path: '/masterfile/patients' });
        })
        .catch((err) => {
          console.error('Error adding suggestions:', err);
        });
    },
    printChart() {
      window.open('/api/printchart/' + this.$route.params.pid);
    },
    handleRowClick(row, column, event) {
      this.selectedOldRecords = row;
      this.oldRecordsdialogVisible = true;
    },
    async getProblemList() {
      this.additionalList = [];
      await Patients.getPatientAdditionalCheckList(this.$route.params.id)
        .then((response) => {
          this.additionalList = response.data;
        })
        .catch((err) => {
          console.error('Error adding suggestions:', err);
        });
    },
    async AddProblem() {
      await this.$refs['appForm'].validate((valid) => {
        if (valid) {
          this.isProcessing = true;
          this.form.apt_dt = moment(this.form.apt_dt)
            .tz('Asia/Manila')
            .format('YYYY-MM-DD');
          Patients.AddProblem(this.formProblem)
            .then((response) => {
              this.$message({
                message: 'Created successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
              this.getProblemList();
            })
            .catch((err) => {
              console.error('Error adding suggestions:', err);
            })
            .finally(() => {
              // This will always run, regardless of the request outcome
              this.isProcessing = false;
              this.dialogFormVisible = false;
              this.formProblem.id = 0;
              this.formProblem.description = '';
              this.formProblem.value = '';
              this.formProblem.isactive = true;
            });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    async confirmDeleteProblem(index, row) {
      await Patients.deleteProblem(row.id)
        .then((response) => {
          this.$message({
            type: 'success',
            message: 'Successfully Deleted',
          });
          this.getProblemList();
        })
        .catch((err) => {
          console.error('Error adding suggestions:', err);
        });
    },
    async getAdolecentsList() {
      this.adolecents = [];
      await Patients.getPatientAdolecense(this.$route.params.id)
        .then((response) => {
          this.adolecents = response.data;
        })
        .catch((err) => {
          console.error('Error adding suggestions:', err);
        });
    },
    async AddAdolecense() {
      await this.$refs['appFormAdolecents'].validate((valid) => {
        if (valid) {
          this.isProcessingAdolecents = true;
          Patients.AddAdolecense(this.formAdolecents)
            .then((response) => {
              this.$message({
                message: 'Created successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
              this.getAdolecentsList();
            })
            .catch((err) => {
              console.error('Error adding suggestions:', err);
            })
            .finally(() => {
              this.isProcessingAdolecents = false;
              this.dialogFormAdolecentsVisible = false;
              this.formAdolecents.id = 0;
              this.formAdolecents.description = '';
              this.formAdolecents.value = '';
            });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    async confirmDeleteAdolecents(index, row) {
      await Patients.deleteAdolecense(row.id)
        .then((response) => {
          this.$message({
            type: 'success',
            message: 'Successfully Deleted',
          });
          this.getAdolecentsList();
        })
        .catch((err) => {
          console.error('Error adding suggestions:', err);
        });
    },
    editAdolecents(index, row) {
      this.dialogFormAdolecentsVisible = true;
      this.formAdolecents.id = row.id;
      this.formAdolecents.description = row.description;
      this.formAdolecents.value = row.value;
    },
    async getVaxsList() {
      this.vaxs = [];
      await Patients.getPatientVaxs(this.$route.params.id)
        .then((response) => {
          this.vaxs = response.data;
        })
        .catch((err) => {
          console.error('Error adding suggestions:', err);
        });
    },
    async AddVax() {
      await this.$refs['appFormVax'].validate((valid) => {
        if (valid) {
          this.isProcessingVax = true;
          Patients.AddVax(this.formVax)
            .then((response) => {
              this.$message({
                message: 'Vaccination has been created successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
              this.getVaxsList();
            })
            .catch((err) => {
              console.error('Error adding suggestions:', err);
            })
            .finally(() => {
              this.isProcessingVax = false;
              this.dialogFormVaxVisible = false;
              this.formAdolecents.id = 0;
              this.formAdolecents.vax = '';
              this.formAdolecents.first_dose = '';
              this.formAdolecents.second_dose = '';
              this.formAdolecents.third_dose = '';
              this.formAdolecents.booster = '';
            });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    async confirmDeleteVax(index, row) {
      await Patients.deleteVax(row.id)
        .then((response) => {
          this.$message({
            type: 'success',
            message: 'Successfully Deleted',
          });
          this.getVaxsList();
        })
        .catch((err) => {
          console.error('Error adding suggestions:', err);
        });
    },
    editVax(index, row) {
      this.dialogFormVaxVisible = true;
      this.formVax.id = row.id;
      this.formVax.vax = row.vax;
      this.formVax.first_dose = row.first_dose;
      this.formVax.second_dose = row.second_dose;
      this.formVax.third_dose = row.third_dose;
      this.formVax.booster = row.booster;
    },
    async getGrowthDevList() {
      this.growthdevs = [];
      await Patients.getPatientGrowthDev(this.$route.params.id)
        .then((response) => {
          this.growthdevs = response.data;
        })
        .catch((err) => {
          console.error('Error adding suggestions:', err);
        });
    },
    async AddGrowthDev() {
      await this.$refs['appFormGrowthDev'].validate((valid) => {
        if (valid) {
          this.isProcessingGrowthDev = true;
          Patients.AddGrowthDev(this.formGd)
            .then((response) => {
              this.$message({
                message: 'Created successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
              this.getGrowthDevList();
            })
            .catch((err) => {
              console.error('Error adding suggestions:', err);
            })
            .finally(() => {
              this.isProcessingGrowthDev = false;
              this.dialogFormGdVisible = false;
              this.formGd.id = 0;
              this.formGd.gross_motor = '';
              this.formGd.gross_motor_age = '';
              this.formGd.fine_motor = '';
              this.formGd.fine_motor_age = '';
              this.formGd.language = '';
              this.formGd.language_age = '';
              this.formGd.social = '';
              this.formGd.social_age = '';
            });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    async confirmDeleteGrowthDev(index, row) {
      await Patients.deleteGrowthDev(row.id)
        .then((response) => {
          this.$message({
            type: 'success',
            message: 'Successfully Deleted',
          });
          this.getGrowthDevList();
        })
        .catch((err) => {
          console.error('Error adding suggestions:', err);
        });
    },
    editGrowthDev(index, row) {
      this.dialogFormGdVisible = true;
      this.formGd.id = row.id;
      this.formGd.gross_motor = row.gross_motor;
      this.formGd.gross_motor_age = row.gross_motor_age;
      this.formGd.fine_motor = row.fine_motor;
      this.formGd.fine_motor_age = row.fine_motor_age;
      this.formGd.language = row.language;
      this.formGd.language_age = row.language_age;
      this.formGd.social = row.social;
      this.formGd.social_age = row.social_age;
    },
    checkExtn(a) {
      const b = a.split('.');
      return b[1];
    },
    isImageAttachment(ext) {
      if (ext === null || ext === undefined || ext === '') {
        return false;
      }
      const e = String(ext).toLowerCase().replace(/^\./, '');
      return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'].includes(e);
    },
    getImagePreviewIndex(item) {
      const i = this.attachmentImageUrls.indexOf(item.newfile);
      return i >= 0 ? i : 0;
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
    previewAltText(item) {
      const name = this.displayFileName(item);
      return name ? `Preview: ${name}` : 'Attachment preview';
    },
    viewFile(s, e) {
      this.isPdf = e == 'pdf';
      this.viewFileModel = true;
      this.sourceFile = s;
    },
  },
};
</script>

<style lang="scss" scoped>
/* —— Layout: SaaS-style patient activity panel —— */
.user-activity-card {
  border-radius: 8px;
  border: 1px solid #ebeef5;
  overflow: hidden;
}

.ua-profile-header {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #ebeef5;
  background: linear-gradient(180deg, #fafbfc 0%, #fff 100%);
}

.ua-profile-header__row {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

@media (min-width: 768px) {
  .ua-profile-header__row {
    flex-direction: row;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1.25rem;
  }
}

.ua-profile-header__identity {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0.75rem;
  min-width: 0;
}

@media (min-width: 576px) {
  .ua-profile-header__identity {
    flex-direction: row;
    align-items: center;
    gap: 1rem;
  }
}

.ua-profile-header__avatar-wrap {
  flex-shrink: 0;
}

.ua-profile-header__avatar {
  width: 100px;
  height: 100px;
  border-radius: 12px;
  border: 2px solid #ebeef5;
  display: block;
  background: #f0f2f5;
}

@media (min-width: 768px) {
  .ua-profile-header__avatar {
    width: 112px;
    height: 112px;
  }
}

.ua-profile-header__avatar ::v-deep .el-image__inner,
.ua-profile-header__avatar ::v-deep img {
  border-radius: 10px;
}

.ua-profile-header__avatar--placeholder,
.ua-profile-header__avatar-fallback {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100px;
  height: 100px;
  border-radius: 12px;
  background: #eef1f6;
  color: #909399;
  font-size: 2.25rem;
  border: 2px solid #ebeef5;
}

@media (min-width: 768px) {
  .ua-profile-header__avatar--placeholder,
  .ua-profile-header__avatar-fallback {
    width: 112px;
    height: 112px;
  }
}

.ua-profile-header__text {
  flex: 1;
  min-width: 0;
}

.ua-profile-header__name {
  margin: 0 0 0.5rem;
  font-size: 1.25rem;
  font-weight: 600;
  color: #303133;
  line-height: 1.3;
  letter-spacing: -0.02em;
}

@media (min-width: 768px) {
  .ua-profile-header__name {
    font-size: 1.375rem;
  }
}

.ua-profile-header__dl {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  margin: 0 0 0.75rem;
  font-size: 0.875rem;
}

@media (min-width: 576px) {
  .ua-profile-header__dl {
    flex-direction: row;
    flex-wrap: wrap;
    gap: 0.75rem 1.25rem;
  }
}

.ua-profile-header__dl-item {
  display: flex;
  gap: 0.35rem;
  align-items: baseline;
  margin: 0;
}

.ua-profile-header__dl-item dt {
  margin: 0;
  font-weight: 600;
  color: #909399;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.ua-profile-header__dl-item dd {
  margin: 0;
  color: #606266;
}

.ua-profile-upload {
  display: inline-block;
}

.ua-profile-header__actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem 0.75rem;
}

@media (min-width: 768px) {
  .ua-profile-header__actions {
    flex-shrink: 0;
    margin-left: auto;
    justify-content: flex-end;
  }
}

.ua-page-inner {
  padding: 1.25rem 1.25rem 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

@media (min-width: 768px) {
  .ua-page-inner {
    padding: 1.5rem 1.75rem 2rem;
  }
}

.ua-section-card {
  background: #fff;
  border: 1px solid #e4e7ed;
  border-radius: 10px;
  padding: 1.25rem 1.25rem 1.35rem;
}

.ua-section-card__title {
  margin: 0 0 1rem;
  padding-bottom: 0.65rem;
  border-bottom: 1px solid #ebeef5;
  font-size: 0.8125rem;
  font-weight: 600;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: #606266;
  line-height: 1.3;
}

.ua-section-card__subtitle {
  margin: 1.25rem 0 0.75rem;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.03em;
  text-transform: uppercase;
  color: #909399;
  line-height: 1.3;
}

.ua-section-card__body {
  margin-top: 0;
}

.ua-drive-count {
  margin-left: 0.5rem;
  font-size: 0.8125rem;
  font-weight: 500;
  color: #909399;
}

.ua-drive {
  border: 1px solid #ebeef5;
  border-radius: 10px;
  background: #fff;
  overflow: hidden;
}

.ua-drive__toolbar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #ebeef5;
  background: #fafbfc;
}

.ua-drive__toolbar-left,
.ua-drive__toolbar-right {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem;
}

.ua-drive__search {
  width: 220px;
  max-width: 100%;
}

.ua-drive__dropzone {
  margin: 1rem;

  ::v-deep .el-upload {
    width: 100%;
  }

  ::v-deep .el-upload-dragger {
    width: 100%;
    padding: 1.25rem;
    border-radius: 8px;
    border-style: dashed;
  }

  &--hidden {
    display: none;
  }
}

.ua-drive__groups {
  padding: 0.5rem 0 1rem;
}

.ua-drive__group {
  & + & {
    margin-top: 1.5rem;
    padding-top: 1.25rem;
    border-top: 1px solid #ebeef5;
  }
}

.ua-drive__date-heading {
  margin: 0 0 0.75rem;
  padding: 0 1rem;
  font-size: 0.9375rem;
  font-weight: 600;
  color: #303133;
  letter-spacing: -0.01em;
}

.ua-drive__date-count {
  font-weight: 500;
  color: #909399;
  font-size: 0.875em;
}

.ua-drive__content {
  padding: 0 1rem;
  min-height: 0;
}

.ua-drive__content--grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 12px;
}

.ua-drive__content--list {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.ua-drive-item {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: stretch;
  padding: 0;
  border: 2px solid transparent;
  border-radius: 8px;
  background: #f8f9fa;
  cursor: pointer;
  text-align: left;
  font: inherit;
  color: inherit;
  transition: box-shadow 0.15s ease, border-color 0.15s ease, background 0.15s ease;

  &:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    background: #fff;

    .ua-drive-item__check {
      opacity: 1;
    }
  }

  &--selected {
    border-color: #409eff;
    background: #ecf5ff;
  }

  &--list {
    flex-direction: row;
    align-items: center;
    gap: 12px;
    padding: 8px 12px;
    background: #fff;
    border: 1px solid #ebeef5;

    &.ua-drive-item--selected {
      border-color: #409eff;
      background: #ecf5ff;
    }

    .ua-drive-item__thumb {
      width: 48px;
      height: 48px;
      flex-shrink: 0;
    }

    .ua-drive-item__info {
      padding: 0;
      flex: 1;
    }
  }
}

.ua-drive-item__check {
  position: absolute;
  top: 8px;
  left: 8px;
  z-index: 2;
  width: 20px;
  height: 20px;
  border-radius: 4px;
  border: 2px solid #fff;
  background: rgba(255, 255, 255, 0.9);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.15s ease, background 0.15s ease;

  &--on {
    opacity: 1;
    background: #409eff;
    border-color: #409eff;
    color: #fff;
  }
}

.ua-drive-item--selected .ua-drive-item__check,
.ua-drive-item:hover .ua-drive-item__check {
  opacity: 1;
}

.ua-drive-item__thumb {
  display: block;
  width: 100%;
  aspect-ratio: 4 / 3;
  overflow: hidden;
  background: #ebeef5;

  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }
}

.ua-drive-item__file-icon {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  min-height: 80px;
  color: #909399;
  font-size: 2rem;
  gap: 0.35rem;

  span {
    font-size: 0.6875rem;
    font-weight: 600;
    letter-spacing: 0.04em;
  }
}

.ua-drive-item__info {
  padding: 8px 10px 10px;
  min-width: 0;
}

.ua-drive-item__name {
  display: block;
  font-size: 0.8125rem;
  font-weight: 500;
  color: #303133;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.ua-drive-item__meta {
  display: block;
  margin-top: 2px;
  font-size: 0.75rem;
  color: #909399;
}

/* Fullscreen lightbox (Google Photos / Drive style) */
.ua-lightbox {
  position: fixed;
  inset: 0;
  z-index: 3000;
  display: flex;
  flex-direction: column;
  background: rgba(0, 0, 0, 0.92);
  color: #fff;
}

.ua-lightbox__bar {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  flex-shrink: 0;
}

.ua-lightbox__title {
  flex: 1;
  margin: 0;
  font-size: 0.9375rem;
  font-weight: 500;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.ua-lightbox__actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.ua-lightbox__counter {
  font-size: 0.8125rem;
  color: rgba(255, 255, 255, 0.75);
  margin-right: 4px;
}

.ua-lightbox__btn {
  width: 40px;
  height: 40px;
  border: none;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.12);
  color: #fff;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.125rem;
  transition: background 0.15s ease;

  &:hover {
    background: rgba(255, 255, 255, 0.22);
  }

  &--danger:hover {
    background: rgba(245, 108, 108, 0.5);
  }
}

.ua-lightbox__nav {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 48px;
  height: 48px;
  border: none;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.15);
  color: #fff;
  cursor: pointer;
  z-index: 2;
  font-size: 1.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s ease;

  &:hover {
    background: rgba(255, 255, 255, 0.28);
  }

  &--prev {
    left: 16px;
  }

  &--next {
    right: 16px;
  }
}

.ua-lightbox__stage {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 0;
  padding: 0 72px;
}

.ua-lightbox__image {
  max-width: 100%;
  max-height: calc(100vh - 180px);
  object-fit: contain;
}

.ua-lightbox__pdf {
  width: 100%;
  max-width: 900px;
  height: calc(100vh - 200px);
  border: 0;
  background: #fff;
  border-radius: 4px;
}

.ua-lightbox__unsupported {
  text-align: center;
  color: rgba(255, 255, 255, 0.8);

  i {
    font-size: 3rem;
    display: block;
    margin-bottom: 0.75rem;
  }

  p {
    margin: 0 0 1rem;
  }
}

.ua-lightbox__filmstrip {
  display: flex;
  gap: 8px;
  padding: 12px 16px 16px;
  overflow-x: auto;
  flex-shrink: 0;
  justify-content: center;
  -webkit-overflow-scrolling: touch;
}

.ua-lightbox__thumb {
  flex-shrink: 0;
  width: 56px;
  height: 56px;
  padding: 0;
  border: 2px solid transparent;
  border-radius: 6px;
  overflow: hidden;
  cursor: pointer;
  background: rgba(255, 255, 255, 0.1);
  opacity: 0.65;
  transition: opacity 0.15s ease, border-color 0.15s ease;

  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  &--active {
    opacity: 1;
    border-color: #409eff;
  }

  &:hover {
    opacity: 1;
  }
}

.ua-lightbox__thumb-file {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  color: #fff;
  font-size: 1.25rem;
}

.ua-lightbox-fade-enter-active,
.ua-lightbox-fade-leave-active {
  transition: opacity 0.2s ease;
}

.ua-lightbox-fade-enter,
.ua-lightbox-fade-leave-to {
  opacity: 0;
}

.ua-attachments-body .ua-upload-progress {
  padding: 0 1rem 0.75rem;
}

.ua-medical-history {
  background: #fafbfc;
  border: 1px solid #e4e7ed;
  border-radius: 10px;
  padding: 1.25rem 1.25rem 1.35rem;
}

.ua-medical-history__heading {
  margin: 0 0 1rem;
  padding-bottom: 0.65rem;
  border-bottom: 1px solid #dcdfe6;
  font-size: 1rem;
  font-weight: 600;
  color: #303133;
  letter-spacing: -0.01em;
}

.ua-section-card--nested {
  background: #fff;
  border: 1px solid #ebeef5;
  border-radius: 8px;
  padding: 1rem 1rem 1.1rem;
  margin-top: 1rem;
}

.ua-section-card--nested:first-of-type {
  margin-top: 0;
}

.ua-divider--nested {
  margin: 1rem 0;
  opacity: 0.85;
}

.ua-form {
  ::v-deep .el-form-item {
    margin-bottom: 1rem;
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

  ::v-deep .el-textarea__inner {
    min-height: 80px;
  }
}

.ua-form--dialog ::v-deep .el-form-item {
  margin-bottom: 0.85rem;
}

.ua-select,
.ua-date {
  width: 100%;
}

.ua-form ::v-deep .ua-select.el-select,
.ua-form ::v-deep .ua-date.el-input {
  width: 100%;
}

.ua-divider {
  margin: 1.25rem 0;
  background-color: #ebeef5;
}

.ua-checkbox-group {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;

  ::v-deep .el-checkbox-button {
    margin: 0;
  }

  ::v-deep .el-checkbox-button__inner {
    border-radius: 6px !important;
    border-left: 1px solid #dcdfe6;
    box-shadow: none;
  }
}

.ua-nested-card {
  border-radius: 8px;
  border: 1px solid #ebeef5;

  ::v-deep .el-card__body {
    padding: 0;
  }
}

.ua-table-responsive {
  width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

.ua-table {
  ::v-deep .el-table__header th {
    font-weight: 600;
    color: #606266;
    background: #fafafa;
  }

  ::v-deep .el-table__cell {
    font-size: 13px;
  }
}

.ua-upload-toolbar {
  margin-bottom: 1rem;
}

.ua-upload {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem 0.75rem;
}

.ua-upload-progress {
  margin-top: 0.75rem;
  max-width: 100%;
}

.ua-upload-status {
  margin: 0.5rem 0 0;
  font-size: 0.8125rem;
  color: #909399;
}

.ua-attach-name {
  margin-left: 0.35rem;
  vertical-align: middle;
  word-break: break-word;
}

/* Attachments tab: tile gallery */
.ua-attach-panel__body {
  padding: 1rem 1.25rem 1.5rem;
}

@media (min-width: 768px) {
  .ua-attach-panel__body {
    padding: 1.25rem 1.5rem 1.75rem;
  }
}

.ua-attach-by-month {
  display: flex;
  flex-direction: column;
  gap: 0;
}

.ua-attach-month-section {
  & + & {
    margin-top: 1.75rem;
    padding-top: 1.5rem;
    border-top: 1px solid #ebeef5;
  }
}

.ua-attach-month-heading {
  margin: 0 0 1rem;
  font-size: 1rem;
  font-weight: 600;
  line-height: 1.3;
  color: #303133;
  letter-spacing: -0.01em;
}

.ua-attach-gallery {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 1rem 1.25rem;
  list-style: none;
  margin: 0;
  padding: 0;
}

.ua-attach-tile {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.ua-attach-tile__media {
  position: relative;
  border-radius: 8px;
  overflow: hidden;
  background: #f0f2f5;
  aspect-ratio: 1;
  border: 1px solid #ebeef5;
}

.ua-attach-tile__image {
  display: block;
  width: 100%;
  height: 100%;

  ::v-deep .el-image {
    display: block;
    width: 100%;
    height: 100%;
  }

  ::v-deep .el-image__inner,
  ::v-deep img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover;
  }
}

.ua-attach-tile__file {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  min-height: 0;
  padding: 0.75rem;
  border: none;
  margin: 0;
  cursor: pointer;
  background: linear-gradient(145deg, #f8f9fb 0%, #ebeef5 100%);
  color: #606266;
  font: inherit;
  transition: background 0.15s ease, color 0.15s ease;
}

.ua-attach-tile__file:hover,
.ua-attach-tile__file:focus-visible {
  background: linear-gradient(145deg, #ecf5ff 0%, #d9ecff 100%);
  color: #409eff;
  outline: none;
}

.ua-attach-tile__file-icon {
  font-size: 2rem;
  margin-bottom: 0.35rem;
  color: #909399;
}

.ua-attach-tile__file:hover .ua-attach-tile__file-icon,
.ua-attach-tile__file:focus-visible .ua-attach-tile__file-icon {
  color: #409eff;
}

.ua-attach-tile__file-ext {
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.04em;
}

.ua-attach-tile__file-hint {
  margin-top: 0.35rem;
  font-size: 0.6875rem;
  color: #909399;
}

.ua-attach-tile__delete {
  position: absolute;
  top: 6px;
  right: 6px;
  z-index: 2;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.12);
}

.ua-attach-tile__caption {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
  margin-top: 0.5rem;
  min-width: 0;
}

.ua-attach-tile__title {
  font-size: 0.8125rem;
  font-weight: 500;
  line-height: 1.35;
  color: #303133;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  word-break: break-word;
}

.ua-attach-tile__date {
  font-size: 0.75rem;
  color: #909399;
}

.ua-attach-empty {
  margin: 0;
  padding: 2.5rem 1.25rem;
  text-align: center;
  font-size: 0.875rem;
  color: #909399;
  line-height: 1.5;
}

.ua-dialog-body {
  padding: 0 0 0.5rem;
}

.ua-dialog-footer {
  display: inline-flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  justify-content: flex-end;
  width: 100%;
}

/* Dialog width cap (Element UI applies inline width from `width` prop) */
::v-deep .ua-appointment-dialog {
  max-width: 720px;
}

::v-deep .ua-preview-dialog .el-dialog__body {
  padding: 12px 16px 20px;
}

.ua-preview-thumb {
  width: 100px;
  height: 100px;
}

@media (max-width: 767px) {
  .ua-page-inner {
    padding: 1rem 0.75rem 1.25rem;
  }

  .ua-profile-header__actions {
    flex-direction: column;
    align-items: stretch;
    width: 100%;
  }

  .ua-profile-header__actions .el-button {
    width: 100%;
    margin-left: 0 !important;
  }
}

/* Legacy nested styles (kept for compatibility) */
.user-activity {
  .user-block {
    .username,
    .description {
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
      &:hover,
      &:focus {
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

  .el-carousel__item:nth-child(2n + 1) {
    background-color: #d3dce6;
  }

  .compact-table .el-table__cell {
    padding: 5px 10px;
    font-size: 12px;
  }
}

.iframe-wrapper {
  text-align: center;
  padding: 10px;
  max-width: 100%;
}

.iframe-transform-container {
  display: inline-block;
  overflow: hidden;
}

.iframe-full {
  width: 100%;
  max-width: 800px;
  /* Pass through to CSS: Sass min() cannot mix vh and px */
  height: #{"min(70vh, 600px)"};
  border: 0;
}

@media (max-width: 767px) {
  .iframe-full {
    height: 50vh;
    min-height: 240px;
  }
}
</style>
