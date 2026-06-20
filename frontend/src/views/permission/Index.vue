<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <h2 class="page-title">权限管理</h2>
        <p class="page-subtitle">管理系统权限配置</p>
      </div>
      <el-button type="primary" :icon="Plus" @click="handleCreate">
        新增权限
      </el-button>
    </div>

    <div v-for="group in permissionGroups" :key="group.group" style="margin-bottom: 24px">
      <el-card shadow="hover">
        <template #header>
          <div style="display: flex; justify-content: space-between; align-items: center">
            <div style="display: flex; align-items: center; gap: 8px">
              <el-icon :size="20" color="#409eff">
                <component :is="getGroupIcon(group.group)" />
              </el-icon>
              <span style="font-weight: 600; font-size: 16px">{{ group.group_name }}</span>
              <el-tag size="small" type="info">
                {{ group.permissions.length }} 个权限
              </el-tag>
            </div>
          </div>
        </template>

        <el-table :data="group.permissions" border size="small">
          <el-table-column type="index" label="#" width="60" align="center" />
          <el-table-column prop="name" label="权限标识" width="200">
            <template #default="{ row }">
              <el-tag type="success" size="small">{{ row.name }}</el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="display_name" label="权限名称" width="150" />
          <el-table-column prop="description" label="权限描述" show-overflow-tooltip />
          <el-table-column label="创建时间" width="170" align="center">
            <template #default="{ row }">
              {{ formatDate(row.created_at) }}
            </template>
          </el-table-column>
        </el-table>
      </el-card>
    </div>

    <el-dialog
      v-model="dialogVisible"
      title="新增权限"
      width="520px"
      @close="handleDialogClose"
    >
      <el-form
        ref="formRef"
        :model="formData"
        :rules="formRules"
        label-width="100px"
      >
        <el-form-item label="权限分组" prop="group">
          <el-select
            v-model="formData.group"
            placeholder="请选择权限分组"
            style="width: 100%"
          >
            <el-option
              v-for="item in groupOptions"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="权限标识" prop="name">
          <el-input
            v-model="formData.name"
            placeholder="如：user.create"
            maxlength="100"
            show-word-limit
          />
        </el-form-item>
        <el-form-item label="权限名称" prop="display_name">
          <el-input
            v-model="formData.display_name"
            placeholder="如：创建用户"
            maxlength="100"
            show-word-limit
          />
        </el-form-item>
        <el-form-item label="权限描述">
          <el-input
            v-model="formData.description"
            type="textarea"
            :rows="2"
            placeholder="请输入权限描述"
            maxlength="500"
            show-word-limit
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <div class="dialog-footer">
          <el-button @click="dialogVisible = false">取消</el-button>
          <el-button
            type="primary"
            :loading="submitLoading"
            @click="handleSubmit"
          >
            确认提交
          </el-button>
        </div>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import {
  Plus,
  User,
  Lock,
  Document,
  Goods,
  Box,
  DataAnalysis,
  Setting,
} from '@element-plus/icons-vue'
import { getPermissionList, createPermission } from '@/api/role'

const dialogVisible = ref(false)
const submitLoading = ref(false)
const permissionGroups = ref([])
const formRef = ref(null)

const formData = reactive({
  name: '',
  display_name: '',
  group: '',
  description: '',
})

const formRules = {
  group: [{ required: true, message: '请选择权限分组', trigger: 'change' }],
  name: [
    { required: true, message: '请输入权限标识', trigger: 'blur' },
    { min: 2, max: 100, message: '长度在 2 到 100 个字符', trigger: 'blur' },
  ],
  display_name: [
    { required: true, message: '请输入权限名称', trigger: 'blur' },
    { min: 2, max: 100, message: '长度在 2 到 100 个字符', trigger: 'blur' },
  ],
}

const groupOptions = [
  { value: 'dashboard', label: '数据面板' },
  { value: 'user', label: '用户管理' },
  { value: 'role', label: '角色管理' },
  { value: 'permission', label: '权限管理' },
  { value: 'order', label: '订单管理' },
  { value: 'product', label: '商品管理' },
  { value: 'inventory', label: '库存管理' },
  { value: 'system', label: '系统设置' },
]

function getGroupIcon(group) {
  const icons = {
    dashboard: DataAnalysis,
    user: User,
    role: Lock,
    permission: Lock,
    order: Document,
    product: Goods,
    inventory: Box,
    system: Setting,
  }
  return icons[group] || Setting
}

function formatDate(dateStr) {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return date.toLocaleString('zh-CN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}

async function fetchPermissions() {
  try {
    const data = await getPermissionList()
    permissionGroups.value = data
  } catch (error) {
    console.error('获取权限列表失败:', error)
  }
}

function handleCreate() {
  resetForm()
  dialogVisible.value = true
}

function resetForm() {
  formData.name = ''
  formData.display_name = ''
  formData.group = ''
  formData.description = ''
  if (formRef.value) {
    formRef.value.resetFields()
  }
}

function handleDialogClose() {
  resetForm()
}

async function handleSubmit() {
  if (!formRef.value) return

  await formRef.value.validate(async (valid) => {
    if (valid) {
      submitLoading.value = true
      try {
        await createPermission(formData)
        ElMessage.success('创建成功')
        dialogVisible.value = false
        fetchPermissions()
      } catch (error) {
        console.error('创建失败:', error)
      } finally {
        submitLoading.value = false
      }
    }
  })
}

onMounted(() => {
  fetchPermissions()
})
</script>
