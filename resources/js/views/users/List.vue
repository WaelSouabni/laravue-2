<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input
        v-model="query.keyword"
        :placeholder="$t('table.keyword')"
        style="width: 200px"
        class="filter-item"
        @keyup.enter.native="handleFilter"
      />
      <el-button
        v-waves
        class="filter-item"
        type="primary"
        icon="el-icon-search"
        @click="handleFilter"
      />
      <el-button
        class="filter-item"
        style="margin-left: 10px"
        type="primary"
        icon="el-icon-plus"
        @click="handleCreate"
      />
      <el-button
        v-waves
        :loading="downloading"
        class="filter-item"
        type="primary"
        icon="el-icon-download"
        @click="handleDownload"
      />
    </div>

    <el-table
      v-loading="loading"
      :data="list"
      border
      fit
      highlight-current-row
      style="width: 100%"
    >
      <el-table-column align="center" label="ID" width="80">
        <template slot-scope="scope">
          <span>{{ scope.row.id }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="إسم المستخدم">
        <template slot-scope="scope">
          <span>{{ scope.row.name }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="البريد الإلكتروني">
        <template slot-scope="scope">
          <span>{{ scope.row.email }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="أجراءات" width="350">
        <template slot-scope="scope">
          <el-button type="primary" size="small" icon="el-icon-edit" @click="handleUpdate(scope.row)" />
          <el-button
            v-if="!scope.row.roles.includes('admin')"
            type="danger"
            size="small"
            icon="el-icon-delete"
            @click="handleDelete(scope.row.id, scope.row.name)"
          />
        </template>
      </el-table-column>
    </el-table>

    <pagination
      v-show="total > 0"
      :total="total"
      :page.sync="query.page"
      :limit.sync="query.limit"
      @pagination="getList"
    />

    <el-dialog :title="'زيادة مستعمل جديد'" :visible.sync="dialogFormVisible">
      <div v-loading="userCreating" class="form-container">
        <el-form
          ref="userForm"
          :rules="rules"
          :model="newUser"
          label-position="top"
          label-width="150px"
          style="max-width: 1000px"
        >
          <el-row>
            <el-col :span="11">
              <el-form-item :label="$t('user.email')" prop="email">
                <el-input v-model="newUser.email" />
              </el-form-item>
            </el-col>
            <el-col :span="2"> - </el-col>
            <el-col :span="11">
              <el-form-item :label="$t('user.name')" prop="name">
                <el-input v-model="newUser.name" />
              </el-form-item>
            </el-col>
          </el-row>
          <el-row>
            <el-col :span="11">
              <el-form-item
                :label="$t('user.confirmPassword')"
                prop="confirmPassword"
              >
                <el-input v-model="newUser.confirmPassword" show-password />
              </el-form-item>
            </el-col>
            <el-col :span="2"> - </el-col>
            <el-col :span="11">
              <el-form-item :label="$t('user.password')" prop="password">
                <el-input v-model="newUser.password" show-password />
              </el-form-item>
            </el-col>
          </el-row>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">
            {{ $t('table.cancel') }}
          </el-button>
          <el-button type="primary" @click="dialogStatus === 'create' ? createUser() : updateData()">
            {{ $t('table.confirm') }}
          </el-button>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import UserResource from '@/api/user';
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Permission directive

const userResource = new UserResource();

export default {
  name: 'UserList',
  components: { Pagination },
  directives: { waves, permission },
  data() {
    var validateConfirmPassword = (value, callback) => {
      if (value !== this.newUser.password) {
        callback(new Error('كلمة المرور غير متطابقة!'));
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
      },
      roles: ['أدمين', 'مستخدم عادي'],
      nonAdminRoles: ['أدمين', 'مستخدم عادي'],
      newUser: {},
      dialogFormVisible: false,
      dialogPermissionVisible: false,
      dialogPermissionLoading: false,
      currentUserId: 0,
      currentUser: {
        name: '',
        permissions: [],
        rolePermissions: [],
      },
      rules: {
        name: [
          { required: true, message: 'الاسم إجباري', trigger: 'blur' },
        ],
        email: [
          { required: true, message: 'البريد الإلكتروني إجباري', trigger: 'blur' },
          {
            type: 'email',
            message: 'الرجاء إدخال عنوان البريد الإلكتروني الصحيح',
            trigger: ['blur', 'change'],
          },
        ],
        password: [
          { required: true, message: 'كلمة المرور  إجبارية', trigger: 'blur' },
        ],
        confirmPassword: [
          { validator: validateConfirmPassword, trigger: 'blur' },
        ],
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
  computed: {},
  created() {
    this.resetNewUser();
    this.getList();
  },
  methods: {
    async getList() {
      const { limit, page } = this.query;
      this.loading = true;
      const { data, meta } = await userResource.list(this.query);
      this.list = data;
      this.list.forEach((element, index) => {
        element['index'] = (page - 1) * limit + index + 1;
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
      this.dialogStatus = 'create';
      this.$nextTick(() => {
        this.$refs['userForm'].clearValidate();
      });
    },
    handleDelete(id, name) {
      this.$confirm(
        'هل انت متؤكد من الحذف',
        'Warning',
        {
          confirmButtonText: 'نعم',
          cancelButtonText: 'لا',
          type: 'warning',
        }
      )
        .then(() => {
          userResource
            .destroy(id)
            .then((response) => {
              this.$message({
                type: 'success',
                message: 'اكتمل الحذف',
              });
              this.handleFilter();
            })
            .catch((error) => {
              console.log(error);
            });
        })
        .catch(() => {
          this.$message({
            type: 'info',
            message: 'تم إلغاء الحذف',
          });
        });
    },
    createUser() {
      this.$refs['userForm'].validate((valid) => {
        if (valid) {
          this.userCreating = true;
          userResource
            .store(this.newUser)
            .then((response) => {
              this.$message({
                message:
                 'تمت الاضافة بنجاح',
                type: 'success',
                duration: 5 * 1000,
              });
              this.resetNewUser();
              this.dialogFormVisible = false;
              this.handleFilter();
            })
            .catch((error) => {
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
        email: '',
        password: '',
        confirmPassword: '',
        role: 'user',
      };
    },
    //
    handleUpdate(row) {
      this.newUser = Object.assign({}, row); // copy obj
      this.dialogStatus = 'update';
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['userForm'].clearValidate();
      });
    },
    //
    updateData() {
      this.$refs['userForm'].validate((valid) => {
        if (valid) {
          const tempData = Object.assign({}, this.newUser);
          userResource.update(this.temp.id, tempData)
            .then((response) => {
              this.$message({
                type: 'success',
                message: 'تم تحديث المعلومات بنجاح',
                duration: 5 * 1000,
              });
              this.resetNewUser();
              this.dialogFormVisible = false;
              this.handleFilter();
            })
            .catch((error) => {
              console.log(error);
            })
            .finally(() => {
              this.getList();
              this.dialogVisible = false;
            });
        }
      });
    },
    //
    handleDownload() {
      this.downloading = true;
      import('@/vendor/Export2Excel').then((excel) => {
        const tHeader = ['id', 'user_id', 'name', 'email', 'role'];
        const filterVal = ['index', 'id', 'name', 'email', 'role'];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: 'قائمة المستخدم',
        });
        this.downloading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map((v) => filterVal.map((j) => v[j]));
    },
    permissionKeys(permissions) {
      return permissions.map((permssion) => permssion.id);
    },
    classifyPermissions(permissions) {
      const all = [];
      const menu = [];
      const other = [];
      permissions.forEach((permission) => {
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
      return {
        id: permission.id,
        name: this.$options.filters.uppercaseFirst(
          permission.name.substring(10)
        ),
        disabled: permission.disabled || false,
      };
    },

    normalizePermission(permission) {
      const disabled =
        permission.disabled || permission.name === 'manage permission';
      return {
        id: permission.id,
        name: this.$options.filters.uppercaseFirst(permission.name),
        disabled: disabled,
      };
    },

    confirmPermission() {
      const checkedMenu = this.$refs.menuPermissions.getCheckedKeys();
      const checkedOther = this.$refs.otherPermissions.getCheckedKeys();
      const checkedPermissions = checkedMenu.concat(checkedOther);
      this.dialogPermissionLoading = true;

      userResource
        .updatePermission(this.currentUserId, {
          permissions: checkedPermissions,
        })
        .then((response) => {
          this.$message({
            message: 'Permissions has been updated successfully',
            type: 'success',
            duration: 5 * 1000,
          });
          this.dialogPermissionLoading = false;
          this.dialogPermissionVisible = false;
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
  margin-left: 0;
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
