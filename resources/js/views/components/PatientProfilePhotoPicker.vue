<template>
  <div
    class="profile-photo-picker"
    :class="[
      'profile-photo-picker--' + variant,
      { 'profile-photo-picker--has-preview': !!displayPreviewUrl },
    ]"
  >
    <div v-if="variant === 'picture-card'" class="profile-photo-picker__card-row">
      <div
        v-if="displayPreviewUrl"
        class="profile-photo-picker__preview profile-photo-picker__preview--card"
      >
        <img :src="displayPreviewUrl" alt="Profile photo preview">
        <button
          type="button"
          class="profile-photo-picker__remove"
          aria-label="Remove photo"
          @click="clearPhoto"
        >
          <i class="el-icon-close" />
        </button>
      </div>
      <div
        v-else
        class="profile-photo-picker__placeholder profile-photo-picker__preview--card"
      >
        <i class="el-icon-picture-outline profile-photo-picker__placeholder-icon" />
      </div>
    </div>

    <div class="profile-photo-picker__actions">
      <input
        ref="fileInput"
        type="file"
        class="profile-photo-picker__file-input"
        :accept="accept"
        @change="onFileInputChange"
      >
      <el-button
        size="small"
        type="primary"
        plain
        :loading="processing"
        @click="triggerFileInput"
      >
        Upload image
      </el-button>
      <el-button
        v-if="webcamSupported"
        size="small"
        plain
        icon="el-icon-camera"
        :loading="processing"
        @click="openWebcamDialog"
      >
        Take photo
      </el-button>
    </div>

    <el-dialog
      title="Take profile photo"
      :visible.sync="webcamDialogVisible"
      width="480px"
      append-to-body
      :close-on-click-modal="false"
      @close="closeWebcamDialog"
    >
      <div class="profile-photo-picker__webcam">
        <video
          ref="webcamVideo"
          class="profile-photo-picker__video"
          autoplay
          playsinline
          muted
        />
        <p v-if="webcamLoading" class="profile-photo-picker__webcam-status">
          Starting camera...
        </p>
      </div>
      <template #footer>
        <el-button @click="closeWebcamDialog">
          Cancel
        </el-button>
        <el-button
          type="primary"
          :loading="capturing"
          :disabled="webcamLoading || !webcamReady"
          @click="capturePhoto"
        >
          Capture
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script>
import { prepareImageForUpload } from '@/utils/imageCompression';
import {
  isWebcamSupported,
  startWebcamStream,
  stopWebcamStream,
  captureVideoFrameAsFile,
  getWebcamErrorMessage,
} from '@/utils/webcamCapture';

let uidCounter = 0;

export default {
  name: 'PatientProfilePhotoPicker',
  props: {
    variant: {
      type: String,
      default: 'picture-card',
      validator: (v) => ['picture-card', 'inline'].includes(v),
    },
    previewUrl: {
      type: String,
      default: '',
    },
    accept: {
      type: String,
      default: 'image/*',
    },
  },
  data() {
    return {
      fileList: [],
      processing: false,
      webcamDialogVisible: false,
      webcamStream: null,
      webcamLoading: false,
      webcamReady: false,
      capturing: false,
      webcamSupported: isWebcamSupported(),
    };
  },
  computed: {
    displayPreviewUrl() {
      if (this.fileList.length > 0 && this.fileList[0].url) {
        return this.fileList[0].url;
      }
      return this.previewUrl || '';
    },
  },
  beforeDestroy() {
    this.revokePreviewUrls();
    this.stopWebcam();
  },
  methods: {
    triggerFileInput() {
      if (this.processing) {
        return;
      }
      this.$refs.fileInput.click();
    },
    async onFileInputChange(event) {
      const input = event.target;
      const file = input.files && input.files[0];
      input.value = '';

      if (!file) {
        return;
      }

      await this.applySelectedFile(file);
    },
    async applySelectedFile(file) {
      this.processing = true;
      try {
        const processedFile = await prepareImageForUpload(file, { compress: true });
        this.setFileList([this.buildFileItem(processedFile)]);
      } catch (error) {
        this.$message.error('Failed to process the selected image.');
      } finally {
        this.processing = false;
      }
    },
    buildFileItem(file) {
      uidCounter += 1;
      return {
        name: file.name,
        raw: file,
        url: URL.createObjectURL(file),
        status: 'ready',
        uid: Date.now() + uidCounter,
      };
    },
    setFileList(list) {
      this.revokePreviewUrls();
      this.fileList = list;
      const file = list[0] || null;
      this.$emit('change', file, list);
    },
    clearPhoto() {
      this.setFileList([]);
      this.$emit('remove');
    },
    revokePreviewUrls() {
      this.fileList.forEach((item) => {
        if (item.url && item.url.startsWith('blob:')) {
          URL.revokeObjectURL(item.url);
        }
      });
    },
    openWebcamDialog() {
      if (this.processing) {
        return;
      }
      this.webcamDialogVisible = true;
      this.webcamReady = false;
      this.$nextTick(() => {
        this.initWebcam();
      });
    },
    async initWebcam() {
      const videoEl = this.$refs.webcamVideo;
      if (!videoEl) {
        return;
      }

      this.webcamLoading = true;
      try {
        this.webcamStream = await startWebcamStream(videoEl);
        this.webcamReady = true;
      } catch (error) {
        this.$message.error(getWebcamErrorMessage(error));
        this.closeWebcamDialog();
      } finally {
        this.webcamLoading = false;
      }
    },
    stopWebcam() {
      stopWebcamStream(this.webcamStream);
      this.webcamStream = null;
      this.webcamReady = false;

      const videoEl = this.$refs.webcamVideo;
      if (videoEl) {
        videoEl.srcObject = null;
      }
    },
    closeWebcamDialog() {
      this.webcamDialogVisible = false;
      this.stopWebcam();
    },
    async capturePhoto() {
      const videoEl = this.$refs.webcamVideo;
      if (!videoEl || !this.webcamReady) {
        return;
      }

      this.capturing = true;
      try {
        const capturedFile = await captureVideoFrameAsFile(videoEl, 'profile-photo.jpg');
        this.closeWebcamDialog();
        await this.applySelectedFile(capturedFile);
      } catch (error) {
        this.$message.error(error.message || 'Failed to capture photo.');
      } finally {
        this.capturing = false;
      }
    },
  },
};
</script>

<style lang="scss" scoped>
.profile-photo-picker__file-input {
  display: none;
}

.profile-photo-picker__actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem;
}

.profile-photo-picker--picture-card .profile-photo-picker__card-row {
  margin-bottom: 0.75rem;
}

.profile-photo-picker__preview--card {
  position: relative;
  width: 148px;
  height: 148px;
  border-radius: 8px;
  overflow: hidden;
  border: 1px dashed #d9d9d9;
  background: #fafafa;
}

.profile-photo-picker__preview--card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.profile-photo-picker__placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
}

.profile-photo-picker__placeholder-icon {
  font-size: 28px;
  color: #8c939d;
}

.profile-photo-picker__remove {
  position: absolute;
  top: 6px;
  right: 6px;
  width: 24px;
  height: 24px;
  border: none;
  border-radius: 50%;
  background: rgba(0, 0, 0, 0.55);
  color: #fff;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
}

.profile-photo-picker__remove:hover {
  background: rgba(0, 0, 0, 0.75);
}

.profile-photo-picker__webcam {
  position: relative;
  background: #000;
  border-radius: 8px;
  overflow: hidden;
}

.profile-photo-picker__video {
  display: block;
  width: 100%;
  max-height: 360px;
  object-fit: cover;
  background: #000;
}

.profile-photo-picker__webcam-status {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0;
  color: #fff;
  background: rgba(0, 0, 0, 0.45);
  font-size: 0.875rem;
}

.profile-photo-picker--inline .profile-photo-picker__actions {
  margin-top: 0.25rem;
}
</style>
