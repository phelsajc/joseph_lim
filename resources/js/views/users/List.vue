<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input
        v-model="query.keyword" :placeholder="$t('table.keyword')" style="width: 200px;" class="filter-item"
        @keyup.enter.native="handleFilter"
      />
      <el-select
        v-model="query.role" :placeholder="$t('table.role')" clearable style="width: 90px" class="filter-item"
        @change="handleFilter"
      >
        <el-option v-for="item in roles" :key="item" :label="item | uppercaseFirst" :value="item" />
      </el-select>
      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
        {{ $t('table.search') }}
      </el-button>
      <el-button
        class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-plus"
        @click="handleCreate"
      >
        {{ $t('table.add') }}
      </el-button>
      <el-button
        v-waves :loading="downloading" class="filter-item" type="primary" icon="el-icon-download"
        @click="handleDownload"
      >
        {{ $t('table.export') }}
      </el-button>
    </div>

    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
      <el-table-column align="center" label="ID" width="80">
        <template slot-scope="scope">
          <span>{{ scope.row.index }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Name">
        <template slot-scope="scope">
          <span>{{ scope.row.name }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Email">
        <template slot-scope="scope">
          <span>{{ scope.row.email }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Role" width="120">
        <template slot-scope="scope">
          <span>{{ scope.row.roles.join(', ') }}</span>
        </template>
      </el-table-column>

      <el-table-column v-if="isAdminUser" align="center" label="Login OTP" width="130">
        <template slot-scope="scope">
          <el-tooltip
            content="On: user receives a code by email at login. Off: username and password only."
            placement="top"
          >
            <el-switch
              :value="scope.row.login_otp_enabled"
              :disabled="loginOtpTogglingId === scope.row.id"
              @change="handleLoginOtpToggle(scope.row, $event)"
            />
          </el-tooltip>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Actions" width="480">
        <template slot-scope="scope">
          <router-link v-if="!isAdminUser && !scope.row.roles.includes('admin')" :to="'/administrator/users/edit/' + scope.row.id">
            <el-button v-permission="['manage user']" type="primary" size="small" icon="el-icon-edit">
              Edit
            </el-button>
          </router-link>
          <el-button
            v-if="isAdminUser"
            type="primary"
            size="small"
            icon="el-icon-edit"
            @click="openEditUserDialog(scope.row)"
          >
            Edit
          </el-button>
          <el-button
            v-if="isAdminUser"
            type="warning"
            plain
            size="small"
            icon="el-icon-lock"
            @click="openResetPasswordDialog(scope.row)"
          >
            Reset Password
          </el-button>
          <!-- <el-button
            v-if="!scope.row.roles.includes('admin')" v-permission="['manage permission']" type="warning"
            size="small" icon="el-icon-edit" @click="handleEditPermissions(scope.row.id);"
          >
            Permissions
          </el-button> -->
          <el-button
            v-if="scope.row.roles.includes('visitor')" v-permission="['manage user']" type="danger"
            size="small" icon="el-icon-delete" @click="handleDelete(scope.row.id, scope.row.name);"
          >
            Delete
          </el-button>
        </template>
      </el-table-column>
    </el-table>

    <pagination
      v-show="total > 0" :total="total" :page.sync="query.page" :limit.sync="query.limit"
      @pagination="getList"
    />

    <el-dialog :visible.sync="dialogPermissionVisible" :title="'Edit Permissions - ' + currentUser.name">
      <div v-if="currentUser.name" v-loading="dialogPermissionLoading" class="form-container">
        <div class="permissions-container">
          <div class="block">
            <el-form :model="currentUser" label-width="80px" label-position="top">
              <el-form-item label="Menus">
                <el-tree
                  ref="menuPermissions" :data="normalizedMenuPermissions"
                  :default-checked-keys="permissionKeys(userMenuPermissions)" :props="permissionProps" show-checkbox
                  node-key="id" class="permission-tree"
                />
              </el-form-item>
            </el-form>
          </div>
          <div class="block">
            <el-form :model="currentUser" label-width="80px" label-position="top">
              <el-form-item label="Permissions">
                <el-tree
                  ref="otherPermissions" :data="normalizedOtherPermissions"
                  :default-checked-keys="permissionKeys(userOtherPermissions)" :props="permissionProps" show-checkbox
                  node-key="id" class="permission-tree"
                />
              </el-form-item>
            </el-form>
          </div>
          <div class="clear-left" />
        </div>
        <div style="text-align:right;">
          <el-button type="danger" @click="dialogPermissionVisible = false">
            {{ $t('permission.cancel') }}
          </el-button>
          <el-button type="primary" @click="confirmPermission">
            {{ $t('permission.confirm') }}
          </el-button>
        </div>
      </div>
    </el-dialog>

    <el-dialog :title="'Create new user'" :visible.sync="dialogFormVisible">
      <div v-loading="userCreating" class="form-container">
        <el-form
          ref="userForm" :rules="rules" :model="newUser" label-position="left" label-width="150px"
          style="max-width: 500px;"
        >
          <el-form-item :label="$t('user.role')" prop="role">
            <el-select v-model="newUser.role" class="filter-item" placeholder="Please select role">
              <el-option v-for="item in nonAdminRoles" :key="item" :label="item | uppercaseFirst" :value="item" />
            </el-select>
          </el-form-item>
          <el-form-item :label="$t('user.name')" prop="name">
            <el-input v-model="newUser.name" />
          </el-form-item>
          <el-form-item label="Username" prop="username">
            <el-input v-model="newUser.username" />
          </el-form-item>
          <el-form-item :label="$t('user.email')" prop="email">
            <el-input v-model="newUser.email" />
          </el-form-item>
          <el-form-item :label="$t('user.password')" prop="password">
            <el-input v-model="newUser.password" show-password />
          </el-form-item>
          <el-form-item :label="$t('user.confirmPassword')" prop="confirmPassword">
            <el-input v-model="newUser.confirmPassword" show-password />
          </el-form-item>
          <el-form-item label="Require login OTP">
            <el-switch
              v-model="newUser.login_otp_enabled"
              active-text="On"
              inactive-text="Off"
            />
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">
            {{ $t('table.cancel') }}
          </el-button>
          <el-button type="primary" @click="createUser()">
            {{ $t('table.confirm') }}
          </el-button>
        </div>
      </div>
    </el-dialog>

    <el-dialog :title="'Edit user — ' + (editUserForm.name || '')" :visible.sync="dialogEditUserVisible" width="480px">
      <div v-loading="editUserUpdating" class="form-container">
        <el-form ref="editUserForm" :rules="editUserRules" :model="editUserForm" label-position="left" label-width="100px">
          <el-form-item :label="$t('user.email')" prop="email">
            <el-input v-model="editUserForm.email" autocomplete="off" />
          </el-form-item>
          <el-form-item label="Role" prop="role">
            <el-select v-model="editUserForm.role" placeholder="Role" style="width: 100%;">
              <el-option v-for="item in adminEditableRoles" :key="item" :label="item | uppercaseFirst" :value="item" />
            </el-select>
          </el-form-item>
          <el-form-item label="Require login OTP">
            <el-switch
              v-model="editUserForm.login_otp_enabled"
              active-text="On"
              inactive-text="Off"
            />
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogEditUserVisible = false">{{ $t('table.cancel') }}</el-button>
          <el-button type="primary" :loading="editUserUpdating" @click="submitEditUser">
            {{ $t('table.confirm') }}
          </el-button>
        </div>
      </div>
    </el-dialog>

    <el-dialog :title="'Reset password — ' + resetPasswordTargetName" :visible.sync="dialogResetPasswordVisible" width="480px">
      <div v-loading="resetPasswordSubmitting" class="form-container">
        <el-form ref="resetPasswordForm" :rules="resetPasswordRules" :model="resetPasswordForm" label-position="left" label-width="160px">
          <el-form-item :label="$t('user.password')" prop="password">
            <el-input v-model="resetPasswordForm.password" type="password" show-password autocomplete="new-password" />
          </el-form-item>
          <el-form-item :label="$t('user.confirmPassword')" prop="password_confirmation">
            <el-input v-model="resetPasswordForm.password_confirmation" type="password" show-password autocomplete="new-password" />
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogResetPasswordVisible = false">{{ $t('table.cancel') }}</el-button>
          <el-button type="primary" :loading="resetPasswordSubmitting" @click="submitResetPassword">
            {{ $t('table.confirm') }}
          </el-button>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import UserResource from '@/api/user';
import Resource from '@/api/resource';
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Permission directive
import checkPermission from '@/utils/permission'; // Permission checking

const userResource = new UserResource();
const permissionResource = new Resource('permissions');

export default {
  name: 'UserList',
  components: { Pagination },
  directives: { waves, permission },
  data() {
    var validateConfirmPassword = (rule, value, callback) => {
      if (value !== this.newUser.password) {
        callback(new Error('Password is mismatched!'));
      } else {
        callback();
      }
    };
    var validateResetPasswordConfirm = (rule, value, callback) => {
      if (value !== this.resetPasswordForm.password) {
        callback(new Error('Password is mismatched!'));
      } else {
        callback();
      }
    };
    return {
      list: null,
      total: 0,
      loading: true,
      downloading: false,
      userCreating: false,
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        role: '',
      },
      roles: ['admin', 'manager', 'editor', 'user', 'visitor'],
      nonAdminRoles: ['editor', 'user', 'visitor', 'doctor', 'secretary', 'admin'],
      adminEditableRoles: ['admin', 'doctor', 'pt', 'user'],
      newUser: {},
      dialogFormVisible: false,
      dialogPermissionVisible: false,
      dialogPermissionLoading: false,
      dialogEditUserVisible: false,
      dialogResetPasswordVisible: false,
      editUserUpdating: false,
      loginOtpTogglingId: null,
      resetPasswordSubmitting: false,
      editUserForm: {
        id: null,
        name: '',
        email: '',
        role: 'user',
        login_otp_enabled: true,
      },
      editUserRules: {
        email: [
          { required: true, message: 'Email is required', trigger: 'blur' },
          { type: 'email', message: 'Please input correct email address', trigger: ['blur', 'change'] },
        ],
        role: [{ required: true, message: 'Role is required', trigger: 'change' }],
      },
      resetPasswordUserId: null,
      resetPasswordTargetName: '',
      resetPasswordForm: {
        password: '',
        password_confirmation: '',
      },
      resetPasswordRules: {
        password: [{ required: true, min: 6, message: 'Password must be at least 6 characters', trigger: 'blur' }],
        password_confirmation: [{ validator: validateResetPasswordConfirm, trigger: 'blur' }],
      },
      currentUserId: 0,
      currentUser: {
        name: '',
        permissions: [],
        rolePermissions: [],
      },
      rules: {
        role: [{ required: true, message: 'Role is required', trigger: 'change' }],
        name: [{ required: true, message: 'Name is required', trigger: 'blur' }],
        username: [{ required: true, message: 'Name is required', trigger: 'blur' }],
        email: [
          { required: true, message: 'Email is required', trigger: 'blur' },
          { type: 'email', message: 'Please input correct email address', trigger: ['blur', 'change'] },
        ],
        password: [{ required: true, message: 'Password is required', trigger: 'blur' }],
        confirmPassword: [{ validator: validateConfirmPassword, trigger: 'blur' }],
      },
      permissionProps: {
        children: 'children',
        label: 'name',
        disabled: 'disabled',
      },
      permissions: [],
      menuPermissions: [],
      otherPermissions: [],
    };
  },
  computed: {
    ...mapGetters({ userRoles: 'roles' }),
    isAdminUser() {
      return Array.isArray(this.userRoles) && this.userRoles.includes('admin');
    },
    normalizedMenuPermissions() {
      let tmp = [];
      this.currentUser.permissions.role.forEach(permission => {
        tmp.push({
          id: permission.id,
          name: permission.name,
          disabled: true,
        });
      });
      const rolePermissions = {
        id: -1, // Just a faked ID
        name: 'Inherited from role',
        disabled: true,
        children: this.classifyPermissions(tmp).menu,
      };

      tmp = this.menuPermissions.filter(permission => !this.currentUser.permissions.role.find(p => p.id === permission.id));
      const userPermissions = {
        id: 0, // Faked ID
        name: 'Extra menus',
        children: tmp,
        disabled: tmp.length === 0,
      };

      return [rolePermissions, userPermissions];
    },
    normalizedOtherPermissions() {
      let tmp = [];
      this.currentUser.permissions.role.forEach(permission => {
        tmp.push({
          id: permission.id,
          name: permission.name,
          disabled: true,
        });
      });
      const rolePermissions = {
        id: -1,
        name: 'Inherited from role',
        disabled: true,
        children: this.classifyPermissions(tmp).other,
      };

      tmp = this.otherPermissions.filter(permission => !this.currentUser.permissions.role.find(p => p.id === permission.id));
      const userPermissions = {
        id: 0,
        name: 'Extra permissions',
        children: tmp,
        disabled: tmp.length === 0,
      };

      return [rolePermissions, userPermissions];
    },
    userMenuPermissions() {
      return this.classifyPermissions(this.userPermissions).menu;
    },
    userOtherPermissions() {
      return this.classifyPermissions(this.userPermissions).other;
    },
    userPermissions() {
      return this.currentUser.permissions.role.concat(this.currentUser.permissions.user);
    },
  },
  mounted() {
    Echo.channel('users')
      .listen('NewUserAdded', (e) => {
        this.getList();
      });
  },
  created() {
    this.resetNewUser();
    this.getList();
    if (checkPermission(['manage permission'])) {
      this.getPermissions();
    }
  },
  methods: {
    checkPermission,
    async getPermissions() {
      const { data } = await permissionResource.list({});
      const { all, menu, other } = this.classifyPermissions(data);
      this.permissions = all;
      this.menuPermissions = menu;
      this.otherPermissions = other;
    },

    async getList() {
      const { limit, page } = this.query;
      this.loading = true;
      const { data, meta } = await userResource.list(this.query);
      this.list = data;
      this.list.forEach((element, index) => {
        element['index'] = (page - 1) * limit + index + 1;
        if (typeof element.login_otp_enabled === 'undefined') {
          element.login_otp_enabled = true;
        }
      });
      this.total = meta.total;
      this.loading = false;
    },
    handleFilter() {
      this.query.page = 1;
      this.getList();
    },
    handleCreate() {
      this.resetNewUser();
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['userForm'].clearValidate();
      });
    },
    handleDelete(id, name) {
      this.$confirm('This will permanently delete user ' + name + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        userResource.destroy(id).then(response => {
          this.$message({
            type: 'success',
            message: 'Delete completed',
          });
          this.handleFilter();
        }).catch(error => {
          console.log(error);
        });
      }).catch(() => {
        this.$message({
          type: 'info',
          message: 'Delete canceled',
        });
      });
    },
    async handleEditPermissions(id) {
      this.currentUserId = id;
      this.dialogPermissionLoading = true;
      this.dialogPermissionVisible = true;
      const found = this.list.find(user => user.id === id);
      const { data } = await userResource.permissions(id);
      this.currentUser = {
        id: found.id,
        name: found.name,
        permissions: data,
      };
      this.dialogPermissionLoading = false;
      this.$nextTick(() => {
        this.$refs.menuPermissions.setCheckedKeys(this.permissionKeys(this.userMenuPermissions));
        this.$refs.otherPermissions.setCheckedKeys(this.permissionKeys(this.userOtherPermissions));
      });
    },
    createUser() {
      this.$refs['userForm'].validate((valid) => {
        if (valid) {
          this.newUser.roles = [this.newUser.role];
          this.userCreating = true;
          userResource
            .store(this.newUser)
            .then(response => {
              this.$message({
                message: 'New user ' + this.newUser.name + '(' + this.newUser.email + ') has been created successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
              this.resetNewUser();
              this.dialogFormVisible = false;
              this.handleFilter();
            })
            .catch(error => {
              console.log(error);
            })
            .finally(() => {
              this.userCreating = false;
            });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    resetNewUser() {
      this.newUser = {
        name: '',
        username: '',
        email: '',
        password: '',
        confirmPassword: '',
        role: 'user',
        login_otp_enabled: true,
      };
    },
    handleDownload() {
      this.downloading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const tHeader = ['id', 'user_id', 'name', 'email', 'role'];
        const filterVal = ['index', 'id', 'name', 'email', 'role'];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: 'user-list',
        });
        this.downloading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => v[j]));
    },
    permissionKeys(permissions) {
      return permissions.map(permssion => permssion.id);
    },
    classifyPermissions(permissions) {
      const all = []; const menu = []; const other = [];
      permissions.forEach(permission => {
        const permissionName = permission.name;
        all.push(permission);
        if (permissionName.startsWith('view menu')) {
          menu.push(this.normalizeMenuPermission(permission));
        } else {
          other.push(this.normalizePermission(permission));
        }
      });
      return { all, menu, other };
    },

    normalizeMenuPermission(permission) {
      return { id: permission.id, name: this.$options.filters.uppercaseFirst(permission.name.substring(10)), disabled: permission.disabled || false };
    },

    normalizePermission(permission) {
      const disabled = permission.disabled || permission.name === 'manage permission';
      return { id: permission.id, name: this.$options.filters.uppercaseFirst(permission.name), disabled: disabled };
    },

    confirmPermission() {
      const checkedMenu = this.$refs.menuPermissions.getCheckedKeys();
      const checkedOther = this.$refs.otherPermissions.getCheckedKeys();
      const checkedPermissions = checkedMenu.concat(checkedOther);
      this.dialogPermissionLoading = true;

      userResource.updatePermission(this.currentUserId, { permissions: checkedPermissions }).then(response => {
        this.$message({
          message: 'Permissions has been updated successfully',
          type: 'success',
          duration: 5 * 1000,
        });
        this.dialogPermissionLoading = false;
        this.dialogPermissionVisible = false;
      });
    },
    resolveEditableRole(row) {
      const assigned = row.roles || [];
      const match = this.adminEditableRoles.find(r => assigned.includes(r));
      return match || 'user';
    },
    handleLoginOtpToggle(row, enabled) {
      if (!this.isAdminUser) {
        return;
      }
      const prev = row.login_otp_enabled !== false;
      if (prev === enabled) {
        return;
      }
      this.$set(row, 'login_otp_enabled', enabled);
      this.loginOtpTogglingId = row.id;
      userResource
        .update(row.id, {
          name: row.name,
          email: row.email,
          roles: row.roles,
          login_otp_enabled: enabled,
        })
        .then(() => {
          this.$message({
            message: 'Login OTP setting updated.',
            type: 'success',
            duration: 3000,
          });
        })
        .catch(() => {
          this.$set(row, 'login_otp_enabled', prev);
        })
        .finally(() => {
          this.loginOtpTogglingId = null;
        });
    },
    openEditUserDialog(row) {
      this.editUserForm = {
        id: row.id,
        name: row.name,
        email: row.email,
        role: this.resolveEditableRole(row),
        login_otp_enabled: row.login_otp_enabled !== false,
      };
      this.dialogEditUserVisible = true;
      this.$nextTick(() => {
        if (this.$refs.editUserForm) {
          this.$refs.editUserForm.clearValidate();
        }
      });
    },
    submitEditUser() {
      this.$refs.editUserForm.validate((valid) => {
        if (!valid) {
          return false;
        }
        this.editUserUpdating = true;
        userResource
          .update(this.editUserForm.id, {
            name: this.editUserForm.name,
            email: this.editUserForm.email,
            roles: [this.editUserForm.role],
            login_otp_enabled: this.editUserForm.login_otp_enabled,
          })
          .then(() => {
            this.$message({
              message: 'User has been updated successfully.',
              type: 'success',
              duration: 5 * 1000,
            });
            this.dialogEditUserVisible = false;
            this.getList();
          })
          .catch((error) => {
            console.log(error);
          })
          .finally(() => {
            this.editUserUpdating = false;
          });
      });
    },
    openResetPasswordDialog(row) {
      this.resetPasswordUserId = row.id;
      this.resetPasswordTargetName = row.name || row.email || '';
      this.resetPasswordForm = {
        password: '',
        password_confirmation: '',
      };
      this.dialogResetPasswordVisible = true;
      this.$nextTick(() => {
        if (this.$refs.resetPasswordForm) {
          this.$refs.resetPasswordForm.clearValidate();
        }
      });
    },
    submitResetPassword() {
      this.$refs.resetPasswordForm.validate((valid) => {
        if (!valid) {
          return false;
        }
        this.resetPasswordSubmitting = true;
        userResource
          .resetPassword(this.resetPasswordUserId, {
            password: this.resetPasswordForm.password,
            password_confirmation: this.resetPasswordForm.password_confirmation,
          })
          .then(() => {
            this.$message({
              message: 'Password has been reset successfully.',
              type: 'success',
              duration: 5 * 1000,
            });
            this.dialogResetPasswordVisible = false;
          })
          .catch((error) => {
            console.log(error);
          })
          .finally(() => {
            this.resetPasswordSubmitting = false;
          });
      });
    },
  },
};
</script>

<style lang="scss" scoped>
.edit-input {
  padding-right: 100px;
}

.cancel-btn {
  position: absolute;
  right: 15px;
  top: 10px;
}

.dialog-footer {
  text-align: left;
  padding-top: 0;
  margin-left: 150px;
}

.app-container {
  flex: 1;
  justify-content: space-between;
  font-size: 14px;
  padding-right: 8px;

  .block {
    float: left;
    min-width: 250px;
  }

  .clear-left {
    clear: left;
  }
}
</style>
