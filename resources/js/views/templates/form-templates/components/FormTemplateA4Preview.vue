<template>
  <div class="form-page-preview-wrap">
    <div v-if="!(contentHtml || '').trim()" class="form-page-preview-empty">
      <slot name="empty">No content to preview.</slot>
    </div>
    <div
      v-else
      class="form-page-preview-page ql-snow"
      :style="pageStyle"
    >
      <div class="ql-editor form-page-preview-page__inner" :style="innerStyle" v-html="contentHtml" />
    </div>
  </div>
</template>

<script>
import { FORM_PAGE_SIZE } from '../constants';

export default {
  name: 'FormTemplateA4Preview',
  props: {
    contentHtml: {
      type: String,
      default: '',
    },
  },
  computed: {
    pageStyle() {
      return {
        width: `${FORM_PAGE_SIZE.widthMm}mm`,
        minHeight: `${FORM_PAGE_SIZE.heightMm}mm`,
      };
    },
    innerStyle() {
      return {
        padding: FORM_PAGE_SIZE.contentPadding,
        fontSize: FORM_PAGE_SIZE.previewFontSize,
        lineHeight: FORM_PAGE_SIZE.previewLineHeight,
      };
    },
  },
};
</script>

<style scoped>
.form-page-preview-wrap {
  display: flex;
  justify-content: center;
  padding: 12px 0;
  background: #e8eaed;
  border-radius: 4px;
  min-height: 160px;
  overflow-x: auto;
}

.form-page-preview-empty {
  padding: 32px 16px;
  color: #909399;
  font-size: 14px;
  text-align: center;
}

.form-page-preview-page {
  max-width: 100%;
  background: #fff;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.12);
  box-sizing: border-box;
}

.form-page-preview-page__inner {
  min-height: 120mm !important;
  color: #303133;
}

.form-page-preview-page__inner :deep(table) {
  border-collapse: collapse;
  width: 100%;
  margin: 0.4em 0;
}

.form-page-preview-page__inner :deep(td),
.form-page-preview-page__inner :deep(th) {
  border: 1px solid #dcdfe6;
  padding: 6px;
  vertical-align: top;
  font-size: inherit;
}

.form-page-preview-page__inner :deep(hr) {
  border: none;
  border-top: 1px solid #dcdfe6;
  margin: 10px 0;
}

.form-page-preview-page__inner :deep(img) {
  max-width: 100%;
  height: auto;
}

@media print {
  .form-page-preview-wrap {
    background: transparent;
    padding: 0;
  }

  .form-page-preview-page {
    box-shadow: none;
    width: 100% !important;
    min-height: auto !important;
  }
}
</style>
