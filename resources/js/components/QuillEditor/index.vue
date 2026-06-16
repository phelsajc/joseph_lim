<template>
  <div class="quill-editor-container">
    <div :id="editorId" ref="editorHost" class="quill-editor" />
  </div>
</template>

<script>
import Quill from 'quill';
import 'quill/dist/quill.snow.css';
import { SizeStyle } from 'quill/formats/size';
import {
  clinicHeaderSnippetHtml,
  horizontalRuleSnippetHtml,
  signatureBlockSnippetHtml,
} from '@/views/templates/form-templates/components/formTemplateSnippets';

const DEFAULT_FONT_SIZES = ['10px', '11px', '12px', '13px', '14px', '15px', '16px', '17px', '18px', '20px', '22px', '24px', '28px', '32px', '36px'];
const DOCUMENT_FONT_SIZES = ['8px', '9px', '10px', '11px', '12px', '14px', '16px', '18px', '20px', '24px'];
const LARGE_FONT_SIZES = ['28px', '32px', '36px', '40px', '48px', '60px', '72px'];
const ALL_REGISTERED_FONT_SIZES = [...new Set([
  ...DEFAULT_FONT_SIZES,
  ...DOCUMENT_FONT_SIZES,
  ...LARGE_FONT_SIZES,
])].sort(
  (a, b) => parseInt(a, 10) - parseInt(b, 10),
);
SizeStyle.whitelist = ALL_REGISTERED_FONT_SIZES;
// Overwrites class-based small/large/huge with inline font-size styles
Quill.register(SizeStyle, true);

export default {
  name: 'QuillEditor',
  props: {
    id: {
      type: String,
      default() {
        return `quill-editor-${+new Date()}${((Math.random() * 1000).toFixed(0))}`;
      },
    },
    value: {
      type: String,
      default: '',
    },
    height: {
      type: Number,
      default: 300,
    },
    placeholder: {
      type: String,
      default: 'Enter text...',
    },
    /**
     * full — headings, colours, checklist, alignment, tables, code block… (recommended for appointments + templates)
     * basic — lighter toolbar (no tables / checklist / code-block)
     */
    preset: {
      type: String,
      default: 'full',
      validator(v) {
        return v === 'full' || v === 'basic';
      },
    },
    /** @deprecated Prefer `preset`; when true while preset is unchanged, expands basic toolbar with quote + code-block */
    extendedToolbar: {
      type: Boolean,
      default: false,
    },
    fontSizes: {
      type: Array,
      default() {
        return [...DEFAULT_FONT_SIZES];
      },
    },
  },
  data() {
    return {
      editorId: this.id,
      quill: null,
      hasInit: false,
      lastEmittedHtml: '',
    };
  },
  watch: {
    value(val) {
      if (!this.quill || !this.hasInit) {
        return;
      }
      const next = typeof val === 'string' ? val : '';
      if (next === this.lastEmittedHtml) {
        return;
      }
      this.applyExternalHtml(next);
    },
  },
  mounted() {
    this.initQuill();
  },
  beforeDestroy() {
    this.quill = null;
  },
  methods: {
    buildToolbarRows() {
      const wantFull =
        this.preset === 'full' ||
        (this.preset === 'basic' && this.extendedToolbar);
      const sizePick = [...this.fontSizes, false];
      if (wantFull && this.preset === 'full') {
        return [
          [{ header: ['1', '2', '3', '4', '5', '6', false] }],
          [{ size: sizePick }],
          ['bold', 'italic', 'underline', 'strike'],
          [{ script: 'sub' }, { script: 'super' }],
          [{ color: [] }, { background: [] }],
          [{ list: 'ordered' }, { list: 'bullet' }, { list: 'check' }],
          [{ indent: '-1' }, { indent: '+1' }],
          [{ align: [] }],
          ['blockquote', 'code-block'],
          ['link', 'image'],
          ['insertTable'],
          ['divider', 'signatureLine', 'clinicHeader'],
          ['clean'],
        ];
      }
      const rows = [
        [{ header: ['1', '2', '3', false] }],
        [{ size: sizePick }],
        ['bold', 'italic', 'underline'],
        [{ color: [] }, { background: [] }],
        [{ list: 'ordered' }, { list: 'bullet' }],
        [{ indent: '-1' }, { indent: '+1' }],
        [{ align: [] }],
        ['link', 'image'],
        ['clean'],
      ];
      if (this.extendedToolbar) {
        rows.splice(7, 0, ['blockquote', 'code-block']);
      }
      return rows;
    },
    applyExternalHtml(html) {
      if (!this.quill) {
        return;
      }
      const clipboard = this.quill.getModule('clipboard');
      const normalized = typeof html === 'string' ? html : '';
      const fallback = normalized.trim() === '' ? '<p><br></p>' : normalized;
      const delta = clipboard.convert({ html: fallback });
      this.quill.setContents(delta, Quill.sources.API);
      this.lastEmittedHtml = this.quill.getSemanticHTML();
    },
    initQuill() {
      const host = this.$refs.editorHost || document.getElementById(this.editorId);
      if (!(host instanceof HTMLElement)) {
        return;
      }

      const toolbarRows = this.buildToolbarRows();
      const fullTable = this.preset === 'full';
      const handlers = {};
      if (fullTable) {
        handlers.insertTable = function insertTableHandler() {
          const mod = this.quill.getModule('table');
          if (mod && typeof mod.insertTable === 'function') {
            mod.insertTable(3, 3);
          }
        };
        handlers.divider = function dividerHandler() {
          insertSnippetHtml(this.quill, horizontalRuleSnippetHtml());
        };
        handlers.signatureLine = function signatureLineHandler() {
          insertSnippetHtml(this.quill, signatureBlockSnippetHtml());
        };
        handlers.clinicHeader = function clinicHeaderHandler() {
          insertSnippetHtml(this.quill, clinicHeaderSnippetHtml());
        };
      }

      function insertSnippetHtml(quill, html) {
        const range = quill.getSelection(true);
        const index = range ? range.index : quill.getLength();
        const delta = quill.clipboard.convert({ html });
        quill.updateContents(
          new (Quill.import('delta'))().retain(index).concat(delta),
          Quill.sources.USER
        );
        quill.setSelection(index + delta.length(), Quill.sources.SILENT);
      }

      const modules = {
        toolbar: {
          container: toolbarRows,
          handlers,
        },
        clipboard: {
          matchVisual: false,
        },
      };
      if (fullTable) {
        modules.table = true;
      }

      const quill = new Quill(host, {
        theme: 'snow',
        placeholder: this.placeholder,
        modules,
      });
      this.quill = quill;

      quill.container.style.height = `${this.height}px`;

      const initial = typeof this.value === 'string' ? this.value : '';
      if (initial.trim() !== '') {
        const delta = quill.clipboard.convert({ html: initial });
        quill.setContents(delta, Quill.sources.API);
        this.lastEmittedHtml = quill.getSemanticHTML();
      }

      quill.on(Quill.events.TEXT_CHANGE, () => {
        this.hasInit = true;
        const content = quill.getSemanticHTML();
        this.lastEmittedHtml = content;
        this.$emit('input', content);
      });

      quill.on(Quill.events.SELECTION_CHANGE, () => {
        this.hasInit = true;
      });

      quill.container.querySelectorAll(
        '.ql-picker.ql-size .ql-picker-label, .ql-picker.ql-size .ql-picker-item'
      ).forEach((el) => {
        el.addEventListener('mousedown', (e) => e.preventDefault());
      });

      this.hasInit = true;
    },
    getContent() {
      return this.quill ? this.quill.getSemanticHTML() : '';
    },
    setContent(content) {
      this.applyExternalHtml(content);
    },
    insertHtml(html) {
      if (!this.quill || !html) {
        return;
      }
      const range = this.quill.getSelection(true);
      const index = range ? range.index : this.quill.getLength();
      const delta = this.quill.clipboard.convert({ html });
      const Delta = Quill.import('delta');
      this.quill.updateContents(
        new Delta().retain(index).concat(delta),
        Quill.sources.USER
      );
      this.quill.setSelection(index + delta.length(), Quill.sources.SILENT);
      const content = this.quill.getSemanticHTML();
      this.lastEmittedHtml = content;
      this.$emit('input', content);
    },
  },
};
</script>

<style scoped>
.quill-editor-container {
  border: 1px solid #dcdfe6;
  border-radius: 4px;
}

.quill-editor {
  min-height: 200px;
}

.quill-editor :deep(.ql-toolbar) {
  border-top: none;
  border-left: none;
  border-right: none;
  border-bottom: 1px solid #dcdfe6;
}

.quill-editor :deep(.ql-container) {
  border: none;
  font-size: 14px;
}

.quill-editor :deep(.ql-editor) {
  min-height: 200px;
  padding: 12px 15px;
}

.quill-editor :deep(table) {
  border-collapse: collapse;
  width: 100%;
  margin: 0.35em 0;
}

.quill-editor :deep(th),
.quill-editor :deep(td) {
  border: 1px solid #ccc;
  padding: 6px 8px;
  vertical-align: top;
}

.quill-editor :deep(th) {
  background: #f5f7fa;
  font-weight: 600;
}

.quill-editor :deep(.ql-toolbar .ql-insertTable::before),
.quill-editor :deep(.ql-toolbar button.ql-insertTable::before) {
  content: '▦';
  font-size: 16px;
  line-height: 1;
}

.quill-editor :deep(.ql-toolbar button.ql-divider::before) {
  content: '—';
  font-weight: 700;
}

.quill-editor :deep(.ql-toolbar button.ql-signatureLine::before) {
  content: '✎';
  font-size: 14px;
}

.quill-editor :deep(.ql-toolbar button.ql-clinicHeader::before) {
  content: '⌂';
  font-size: 14px;
}
</style>

<style>
/* Unscoped: overrides Quill Snow's default "Normal" labels for custom px sizes */
.ql-snow .ql-picker.ql-size .ql-picker-label[data-value]::before,
.ql-snow .ql-picker.ql-size .ql-picker-item[data-value]::before {
  content: attr(data-value) !important;
}

.ql-snow .ql-picker.ql-size .ql-picker-label:not([data-value])::before,
.ql-snow .ql-picker.ql-size .ql-picker-item:not([data-value])::before,
.ql-snow .ql-picker.ql-size .ql-picker-label[data-value=""]::before,
.ql-snow .ql-picker.ql-size .ql-picker-item[data-value=""]::before {
  content: 'Default' !important;
}

.ql-snow .ql-picker.ql-size .ql-picker-options {
  max-height: 220px;
  overflow-y: auto;
}

.ql-snow .ql-picker.ql-size .ql-picker-options .ql-picker-item {
  padding-top: 2px;
  padding-bottom: 2px;
}

.ql-snow .ql-picker.ql-size .ql-picker-item::before {
  font-size: 13px !important;
  line-height: 1.3;
}
</style>
