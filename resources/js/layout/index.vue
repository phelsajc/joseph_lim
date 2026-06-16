<template>
  <div :class="classObj" class="app-wrapper">
    <div :class="{hasTagsView:needTagsView}" class="main-container">
      <div :class="{'fixed-header':fixedHeader}">
        <navbar />
        <tags-view v-if="needTagsView" />
      </div>
      <app-main />
      <div
        v-if="pageLoading"
        class="page-loading-overlay"
        :style="{ top: loadingOverlayTop + 'px' }"
      >
        <i class="el-icon-loading" />
        <span>Loading...</span>
      </div>
      <right-panel v-if="showSettings">
        <settings />
      </right-panel>
    </div>
  </div>
</template>

<script>
import RightPanel from '@/components/RightPanel';
import { Navbar, Sidebar, AppMain, TagsView, Settings } from './components';
import ResizeMixin from './mixin/resize-handler.js';
import { mapState, mapGetters } from 'vuex';

export default {
  name: 'Layout',
  components: {
    AppMain,
    Navbar,
    RightPanel,
    Settings,
    TagsView,
  },
  mixins: [ResizeMixin],
  computed: {
    ...mapGetters(['pageLoading']),
    ...mapState({
      sidebar: state => state.app.sidebar,
      device: state => state.app.device,
      showSettings: state => state.settings.showSettings,
      needTagsView: state => state.settings.tagsView,
      fixedHeader: state => state.settings.fixedHeader,
    }),
    loadingOverlayTop() {
      if (!this.fixedHeader) {
        return 0;
      }
      return this.needTagsView ? 84 : 50;
    },
    classObj() {
      return {
        withoutAnimation: this.sidebar.withoutAnimation,
        mobile: this.device === 'mobile',
      };
    },
  },
};
</script>

<style lang="scss" scoped>
  @import "~@/styles/mixin.scss";
  @import "~@/styles/variables.scss";

  .app-wrapper {
    @include clearfix;
    position: relative;
    height: 100%;
    width: 100%;

    &.mobile.openSidebar {
      position: fixed;
      top: 0;
    }
  }

  .fixed-header {
    position: fixed;
    top: 0;
    right: 0;
    z-index: 9;
    width: 100%;
    transition: width 0.28s;
  }

  .mobile .fixed-header {
    width: 100%;
  }

  .page-loading-overlay {
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 8;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.82);
    pointer-events: none;

    .el-icon-loading {
      font-size: 40px;
      color: #409EFF;
      margin-bottom: 12px;
    }

    span {
      font-size: 14px;
      font-weight: 500;
      color: #606266;
    }
  }
</style>
