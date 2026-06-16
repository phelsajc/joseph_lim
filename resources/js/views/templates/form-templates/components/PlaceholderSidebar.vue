<template>
  <div class="placeholder-sidebar">
    <div class="placeholder-sidebar__title">Placeholders</div>
    <p class="placeholder-sidebar__hint">
      Click to insert at the cursor. Filled automatically when loading from an appointment.
    </p>
    <el-button
      v-for="p in placeholders"
      :key="p.token"
      size="mini"
      class="placeholder-chip"
      @click="$emit('insert', placeholderToken(p.token))"
    >
      <span class="placeholder-chip__label">{{ p.label }}</span>
      <code class="placeholder-chip__code">{{ placeholderToken(p.token) }}</code>
    </el-button>
    <el-divider />
    <div class="placeholder-sidebar__title">Snippets</div>
    <el-button size="mini" class="snippet-btn" @click="$emit('insert-snippet', 'clinicHeader')">
      Clinic header
    </el-button>
    <el-button size="mini" class="snippet-btn" @click="$emit('insert-snippet', 'signature')">
      Signature block
    </el-button>
    <el-button size="mini" class="snippet-btn" @click="$emit('insert-snippet', 'hr')">
      Horizontal line
    </el-button>
  </div>
</template>

<script>
import { FORM_TEMPLATE_PLACEHOLDERS } from '../constants';

export default {
  name: 'FormTemplatePlaceholderSidebar',
  props: {
    items: {
      type: Array,
      default: null,
    },
  },
  computed: {
    placeholders() {
      return this.items && this.items.length ? this.items : FORM_TEMPLATE_PLACEHOLDERS;
    },
  },
  methods: {
    placeholderToken(token) {
      return `{{${token}}}`;
    },
  },
};
</script>

<style scoped>
.placeholder-sidebar {
  padding: 12px;
  background: #fafafa;
  border: 1px solid #ebeef5;
  border-radius: 4px;
  height: 100%;
  overflow-y: auto;
}

.placeholder-sidebar__title {
  font-size: 13px;
  font-weight: 600;
  color: #303133;
  margin-bottom: 6px;
}

.placeholder-sidebar__hint {
  margin: 0 0 10px;
  font-size: 12px;
  color: #909399;
  line-height: 1.45;
}

.placeholder-chip {
  display: block;
  width: 100%;
  margin: 0 0 6px;
  text-align: left;
  white-space: normal;
  height: auto;
  padding: 8px 10px;
}

.placeholder-chip__label {
  display: block;
  font-size: 12px;
  font-weight: 600;
  margin-bottom: 2px;
}

.placeholder-chip__code {
  font-size: 11px;
  background: transparent;
  padding: 0;
  color: #606266;
}

.snippet-btn {
  display: block;
  width: 100%;
  margin: 0 0 6px;
  text-align: left;
}
</style>
