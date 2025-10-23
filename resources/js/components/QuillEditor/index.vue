<template>
  <div class="quill-editor-container">
    <div :id="editorId" class="quill-editor"></div>
  </div>
</template>

<script>
export default {
  name: 'QuillEditor',
  props: {
    id: {
      type: String,
      default: function() {
        return 'quill-editor-' + +new Date() + ((Math.random() * 1000).toFixed(0) + '');
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
  },
  data() {
    return {
      editorId: this.id,
      quill: null,
      hasInit: false,
    };
  },
  watch: {
    value(val) {
      if (this.quill && this.hasInit) {
        const currentContent = this.quill.root.innerHTML;
        if (currentContent !== val) {
          this.quill.root.innerHTML = val || '';
        }
      }
    },
  },
  mounted() {
    this.waitForQuill();
  },
  beforeDestroy() {
    if (this.quill) {
      this.quill = null;
    }
  },
  methods: {
    waitForQuill() {
      let attempts = 0;
      const maxAttempts = 50; // 5 seconds max wait time
      
      const checkQuill = () => {
        attempts++;
        
        if (typeof window.Quill !== 'undefined') {
          console.log('Quill.js loaded successfully!');
          this.initQuill();
        } else if (attempts >= maxAttempts) {
          console.error('Quill.js failed to load after 5 seconds.');
          console.error('Current window.Quill:', typeof window.Quill);
        } else {
          console.log(`Waiting for Quill.js to load... (attempt ${attempts}/${maxAttempts})`);
          setTimeout(checkQuill, 100);
        }
      };
      
      setTimeout(checkQuill, 100);
    },
    initQuill() {
      if (typeof window.Quill === 'undefined') {
        console.error('Quill.js is not loaded.');
        return;
      }

      this.quill = new window.Quill(`#${this.editorId}`, {
        theme: 'snow',
        placeholder: this.placeholder,
        modules: {
          toolbar: [
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            [{ 'indent': '-1'}, { 'indent': '+1' }],
            [{ 'align': [] }],
            ['link', 'image'],
            ['clean']
          ]
        }
      });

      // Set initial content
      if (this.value) {
        this.quill.root.innerHTML = this.value;
      }

      // Set height
      this.quill.container.style.height = this.height + 'px';

      // Listen for changes
      this.quill.on('text-change', () => {
        this.hasInit = true;
        const content = this.quill.root.innerHTML;
        this.$emit('input', content);
      });

      this.hasInit = true;
    },
    getContent() {
      return this.quill ? this.quill.root.innerHTML : '';
    },
    setContent(content) {
      if (this.quill) {
        this.quill.root.innerHTML = content || '';
      }
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

/* Override Quill styles to match Element UI */
.quill-editor .ql-toolbar {
  border-top: none;
  border-left: none;
  border-right: none;
  border-bottom: 1px solid #dcdfe6;
}

.quill-editor .ql-container {
  border: none;
  font-size: 14px;
}

.quill-editor .ql-editor {
  min-height: 200px;
  padding: 12px 15px;
}
</style>

