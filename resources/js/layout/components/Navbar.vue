<template>
  <div class="navbar">
    <div class="left-nav">
      <el-dropdown
        ref="menuDropdown"
        class="menu-dropdown"
        trigger="click"
        @visible-change="handleMenuVisibleChange"
      >
        <div class="menu-trigger hover-effect" role="button" tabindex="0">
          <i class="el-icon-menu" />
          <span class="menu-label">Menu</span>
        </div>
        <el-dropdown-menu slot="dropdown" class="menu-dropdown-panel">
          <div class="menu-dropdown-inner">
            <el-scrollbar class="menu-scroll">
              <el-menu
                :default-active="$route.path"
                mode="vertical"
                background-color="#ffffff"
                text-color="#303133"
                active-text-color="#409EFF"
                class="menu-list"
                @select="closeMenu"
              >
                <sidebar-item
                  v-for="route in routes"
                  :key="route.path"
                  :item="route"
                  :base-path="route.path"
                />
              </el-menu>
            </el-scrollbar>
          </div>
        </el-dropdown-menu>
      </el-dropdown>
    </div>

    <breadcrumb id="breadcrumb-container" class="breadcrumb-container" />

    <div class="right-menu">
      <!-- <template v-if="device!=='mobile'">
        <search id="header-search" class="right-menu-item" />

        <screenfull id="screenfull" class="right-menu-item hover-effect" />

        <el-tooltip :content="$t('navbar.size')" effect="dark" placement="bottom">
          <size-select id="size-select" class="right-menu-item hover-effect" />
        </el-tooltip>

        <lang-select class="right-menu-item hover-effect" />
      </template> -->

      <el-dropdown class="avatar-container right-menu-item hover-effect" trigger="click">
        <div class="avatar-wrapper">
          <!-- <img :src="avatar+'/128'" class="user-avatar"> -->
          <img :src="img" class="user-avatar">
          <i class="el-icon-caret-bottom" />
        </div>
        <el-dropdown-menu slot="dropdown">
          <el-dropdown-item>
            {{ name }}
          </el-dropdown-item>
          <router-link to="/">
            <el-dropdown-item divided>
              {{ $t('navbar.dashboard') }}
            </el-dropdown-item>
          </router-link>
          <el-dropdown-item @click.native="openChangePasswordDialog">
            {{ $t('navbar.changePassword') }}
          </el-dropdown-item>
          <!-- <router-link v-show="userId !== null" :to="`/profile/edit`">
            <el-dropdown-item>
              {{ $t('navbar.profile') }}
            </el-dropdown-item>
          </router-link> -->
          <!-- <a target="_blank" href="https://github.com/tuandm/laravue/">
            <el-dropdown-item>
              {{ $t('navbar.github') }}
            </el-dropdown-item>
          </a> -->
          <el-dropdown-item divided>
            <span style="display:block;" @click="logout">{{ $t('navbar.logOut') }}</span>
          </el-dropdown-item>
        </el-dropdown-menu>
      </el-dropdown>
    </div>

    <el-dialog
      :title="$t('navbar.changePasswordTitle')"
      :visible.sync="changePasswordVisible"
      :width="changePasswordDialogWidth"
      custom-class="navbar-change-password-dialog"
      append-to-body
      :close-on-click-modal="false"
      @opened="onChangePasswordDialogOpened"
      @closed="onChangePasswordDialogClosed"
    >
      <el-form
        ref="passwordForm"
        :model="passwordForm"
        :rules="passwordFormRules"
        label-position="top"
        class="change-password-form"
        @submit.native.prevent="handleChangePasswordSubmit"
      >
        <el-form-item
          prop="current_password"
          :label="$t('navbar.changePasswordFieldCurrent')"
          :error="passwordFieldErrors.current_password"
        >
          <el-input
            v-model="passwordForm.current_password"
            type="password"
            name="current_password"
            autocomplete="current-password"
            show-password
            @input="clearPasswordFieldError('current_password')"
          />
        </el-form-item>
        <el-form-item
          prop="password"
          :label="$t('navbar.changePasswordFieldNew')"
          :error="passwordFieldErrors.password"
        >
          <el-input
            v-model="passwordForm.password"
            type="password"
            name="new_password"
            autocomplete="new-password"
            show-password
            @input="clearPasswordFieldError('password')"
          />
        </el-form-item>
        <el-form-item
          prop="password_confirmation"
          :label="$t('navbar.changePasswordFieldConfirm')"
          :error="passwordFieldErrors.password_confirmation"
        >
          <el-input
            v-model="passwordForm.password_confirmation"
            type="password"
            name="new_password_confirmation"
            autocomplete="new-password"
            show-password
            @input="clearPasswordFieldError('password_confirmation')"
          />
        </el-form-item>
      </el-form>
      <div slot="footer" class="change-password-dialog-footer">
        <el-button :disabled="passwordSubmitting" @click="handleCancelChangePassword">
          {{ $t('table.cancel') }}
        </el-button>
        <el-button
          type="primary"
          :loading="passwordSubmitting"
          :disabled="passwordSubmitting"
          @click="handleChangePasswordSubmit"
        >
          {{ $t('navbar.changePasswordSubmit') }}
        </el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';
import Breadcrumb from '@/components/Breadcrumb';
import Screenfull from '@/components/Screenfull';
import SizeSelect from '@/components/SizeSelect';
import LangSelect from '@/components/LangSelect';
import Search from '@/components/HeaderSearch';
import Profile from '@/api/profile';
import { updatePassword } from '@/api/auth';
import SidebarItem from './Sidebar/SidebarItem';

const PASSWORD_MIN_LENGTH = 6;

export default {
  components: {
    Breadcrumb,
    Screenfull,
    SizeSelect,
    LangSelect,
    Search,
    SidebarItem,
  },
  data() {
    return {
      img: '',
      profilename: '',
      menuOpen: false,
      changePasswordVisible: false,
      passwordSubmitting: false,
      passwordFieldErrors: {},
      passwordForm: {
        current_password: '',
        password: '',
        password_confirmation: '',
      },
    };
  },
  created() {
    this.getDetail();
  },
  computed: {
    ...mapGetters([
      'name',
      'avatar',
      'device',
      'userId',
    ]),
    routes() {
      return this.$store.state.permission.routes;
    },
    changePasswordDialogWidth() {
      return this.device === 'mobile' ? '92%' : '440px';
    },
    passwordFormRules() {
      return {
        current_password: [
          { required: true, message: this.$t('navbar.changePasswordCurrentRequired'), trigger: 'blur' },
        ],
        password: [
          { required: true, message: this.$t('navbar.changePasswordNewRequired'), trigger: 'blur' },
          { min: PASSWORD_MIN_LENGTH, message: this.$t('navbar.changePasswordMinLength'), trigger: 'blur' },
        ],
        password_confirmation: [
          { required: true, message: this.$t('navbar.changePasswordConfirmRequired'), trigger: 'blur' },
          {
            validator: (rule, value, callback) => {
              if (value !== this.passwordForm.password) {
                callback(new Error(this.$t('navbar.changePasswordMismatch')));
              } else {
                callback();
              }
            },
            trigger: 'blur',
          },
        ],
      };
    },
  },
  watch: {
    $route() {
      this.closeMenu();
    },
  },
  methods: {
    closeMenu() {
      this.menuOpen = false;
      const dropdown = this.$refs.menuDropdown;
      if (dropdown && typeof dropdown.hide === 'function') {
        dropdown.hide();
      }
    },
    handleMenuVisibleChange(visible) {
      this.menuOpen = visible;
    },
    openChangePasswordDialog() {
      this.passwordFieldErrors = {};
      this.changePasswordVisible = true;
    },
    onChangePasswordDialogOpened() {
      this.passwordSubmitting = false;
      this.passwordFieldErrors = {};
      this.$nextTick(() => {
        if (this.$refs.passwordForm) {
          this.$refs.passwordForm.resetFields();
          this.$refs.passwordForm.clearValidate();
        }
      });
    },
    onChangePasswordDialogClosed() {
      this.passwordSubmitting = false;
      this.passwordFieldErrors = {};
      this.$nextTick(() => {
        if (this.$refs.passwordForm) {
          this.$refs.passwordForm.resetFields();
          this.$refs.passwordForm.clearValidate();
        }
      });
    },
    handleCancelChangePassword() {
      this.changePasswordVisible = false;
    },
    applyServerFieldErrors(errors) {
      this.passwordFieldErrors = {};
      Object.keys(errors).forEach((key) => {
        const val = errors[key];
        const msg = Array.isArray(val) ? val[0] : val;
        this.$set(this.passwordFieldErrors, key, msg);
      });
    },
    clearPasswordFieldError(field) {
      if (this.passwordFieldErrors[field]) {
        this.$delete(this.passwordFieldErrors, field);
      }
    },
    handleChangePasswordSubmit() {
      if (this.passwordSubmitting) {
        return;
      }
      if (!this.$refs.passwordForm) {
        return;
      }
      this.passwordFieldErrors = {};
      this.$refs.passwordForm.validate((valid) => {
        if (!valid) {
          return;
        }
        this.passwordSubmitting = true;
        updatePassword({
          current_password: this.passwordForm.current_password,
          password: this.passwordForm.password,
          password_confirmation: this.passwordForm.password_confirmation,
        })
          .then(() => {
            this.$message.success(this.$t('navbar.changePasswordSuccess'));
            this.changePasswordVisible = false;
          })
          .catch((err) => {
            const res = err.response;
            if (res && res.data && res.data.errors) {
              this.applyServerFieldErrors(res.data.errors);
            } else {
              this.$message.error(this.$t('navbar.changePasswordFailed'));
            }
          })
          .finally(() => {
            this.passwordSubmitting = false;
          });
      });
    },
    async logout() {
      await this.$store.dispatch('user/logout');
      this.$router.push(`/login?redirect=${this.$route.fullPath}`);
    },
    getDetail(){
      Profile.get().then((response) => {
        this.img = response.pic;
        this.profilename = response.name;
      })
        .catch((err) => {
          console.error('Error adding suggestions:', err);
        });
    },
  },
};
</script>

<style lang="scss" scoped>
.navbar {
  height: 50px;
  overflow: hidden;
  position: relative;
  background: #fff;
  box-shadow: 0 1px 4px rgba(0,21,41,.08);

  .left-nav {
    float: left;
    height: 100%;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .menu-trigger {
    height: 100%;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 0 14px;
    color: #303133;
    cursor: pointer;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
  }

  .menu-label {
    font-size: 14px;
    font-weight: 600;
  }

  .breadcrumb-container {
    float: left;
  }

  .errLog-container {
    display: inline-block;
    vertical-align: top;
  }

  .right-menu {
    float: right;
    height: 100%;
    line-height: 50px;

    &:focus {
      outline: none;
    }

    .right-menu-item {
      display: inline-block;
      padding: 0 8px;
      height: 100%;
      font-size: 18px;
      color: #5a5e66;
      vertical-align: text-bottom;

      &.hover-effect {
        cursor: pointer;
        transition: background .3s;

        &:hover {
          background: rgba(0, 0, 0, .025)
        }
      }
    }

    .avatar-container {
      margin-right: 30px;

      .avatar-wrapper {
        margin-top: 5px;
        position: relative;

        .user-avatar {
          cursor: pointer;
          width: 40px;
          height: 40px;
          border-radius: 4px;
        }

        .el-icon-caret-bottom {
          cursor: pointer;
          position: absolute;
          right: -20px;
          top: 25px;
          font-size: 12px;
        }
      }
    }
  }
}

.hover-effect {
  cursor: pointer;
  transition: background .3s;

  &:hover {
    background: rgba(0, 0, 0, .025)
  }
}

.menu-dropdown-panel {
  padding: 0;
}

.menu-dropdown-inner {
  width: 320px;
  max-height: 70vh;
  overflow: hidden;
}

.menu-scroll {
  height: 70vh;
  max-height: 70vh;
}

.menu-list {
  border: none;
}

.change-password-dialog-footer {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-end;
  gap: 8px;
}
</style>

<style lang="scss">
/* append-to-body dialog: not scoped */
.navbar-change-password-dialog {
  border-radius: 10px;
  overflow: hidden;

  .el-dialog__header {
    padding: 20px 20px 8px;
    border-bottom: 1px solid #ebeef5;
  }

  .el-dialog__title {
    font-size: 17px;
    font-weight: 600;
    color: #303133;
  }

  .el-dialog__body {
    padding: 16px 20px 8px;
  }

  .el-dialog__footer {
    padding: 12px 20px 18px;
    border-top: 1px solid #ebeef5;
  }

  .change-password-form .el-form-item {
    margin-bottom: 14px;
  }

  .change-password-form .el-form-item__label {
    line-height: 1.35;
    padding-bottom: 4px;
    color: #606266;
    font-weight: 500;
  }
}
</style>
