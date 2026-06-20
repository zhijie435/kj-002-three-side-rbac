<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <h2 class="page-title">任务管理</h2>
        <p class="page-subtitle">管理剪切线生产任务分配与进度</p>
      </div>
      <div style="display: flex; gap: 10px">
        <el-button :icon="Back" @click="goBack">
          返回剪切线
        </el-button>
        <el-button type="primary" :icon="Plus" @click="handleCreate">
          新建任务
        </el-button>
      </div>
    </div>

    <el-row :gutter="16" style="margin-bottom: 20px">
      <el-col :span="6">
        <div class="stat-card">
          <div class="stat-icon primary">
            <el-icon :size="28"><List /></el-icon>
          </div>
          <div class="stat-info">
            <div class="stat-value">{{ stats.total }}</div>
            <div class="stat-label">任务总数</div>
          </div>
        </div>
      </el-col>
      <el-col :span="6">
        <div class="stat-card">
          <div class="stat-icon warning">
            <el-icon :size="28"><Clock /></el-icon>
          </div>
          <div class="stat-info">
            <div class="stat-value">{{ stats.pending }}</div>
            <div class="stat-label">待处理</div>
          </div>
        </div>
      </el-col>
      <el-col :span="6">
        <div class="stat-card">
          <div class="stat-icon running">
            <el-icon :size="28"><Loading /></el-icon>
          </div>
          <div class="stat-info">
            <div class="stat-value">{{ stats.processing }}</div>
            <div class="stat-label">处理中</div>
          </div>
        </div>
      </el-col>
      <el-col :span="6">
        <div class="stat-card">
          <div class="stat-icon success">
            <el-icon :size="28"><CircleCheck /></el-icon>
          </div>
          <div class="stat-info">
            <div class="stat-value">{{ stats.completed }}</div>
            <div class="stat-label">已完成</div>
          </div>
        </div>
      </el-col>
    </el-row>

    <div class="filter-bar">
      <el-form :inline="true" :model="filterForm" @submit.prevent>
        <el-form-item label="关键词">
          <el-input
            v-model="filterForm.keyword"
            placeholder="订单号/产品名称"
            clearable
            @keyup.enter="handleSearch"
          />
        </el-form-item>
        <el-form-item label="状态">
          <el-select
            v-model="filterForm.status"
            placeholder="全部状态"
            clearable
            style="width: 140px"
          >
            <el-option label="待处理" value="pending" />
            <el-option label="已分配" value="assigned" />
            <el-option label="处理中" value="processing" />
            <el-option label="已完成" value="completed" />
            <el-option label="已取消" value="cancelled" />
          </el-select>
        </el-form-item>
        <el-form-item label="优先级">
          <el-select
            v-model="filterForm.priority"
            placeholder="全部优先级"
            clearable
            style="width: 140px"
          >
            <el-option label="低" value="low" />
            <el-option label="中" value="medium" />
            <el-option label="高" value="high" />
            <el-option label="紧急" value="urgent" />
          </el-select>
        </el-form-item>
        <el-form-item label="剪切线">
          <el-select
            v-model="filterForm.shearerline_id"
            placeholder="全部剪切线"
            clearable
            style="width: 180px"
          >
            <el-option
              v-for="item in shearerOptions"
              :key="item.id"
              :label="`${item.code} - ${item.name}`"
              :value="item.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" :icon="Search" @click="handleSearch">
            搜索
          </el-button>
          <el-button :icon="Refresh" @click="handleReset">重置</el-button>
        </el-form-item>
      </el-form>
    </div>

    <div class="table-toolbar">
      <div>
        <span style="color: #909399; font-size: 13px">
          共 <b style="color: #303133">{{ pagination.total }}</b> 条记录
        </span>
      </div>
      <div>
        <el-button :icon="Refresh" @click="fetchList">刷新</el-button>
      </div>
    </div>

    <el-table
      :data="taskList"
      v-loading="loading"
      border
      stripe
      style="width: 100%"
      :default-sort="{ prop: 'sort_order', order: 'ascending' }"
    >
      <el-table-column type="index" label="#" width="60" align="center" />
      <el-table-column
        prop="order_no"
        label="订单号"
        width="140"
        sortable
      >
        <template #default="{ row }">
          <el-tag type="primary" size="small">{{ row.order_no }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column
        prop="product_name"
        label="产品名称"
        min-width="180"
        show-overflow-tooltip
      />
      <el-table-column prop="quantity" label="数量" width="100" align="center" />
      <el-table-column label="优先级" width="100" align="center">
        <template #default="{ row }">
          <el-tag :type="getPriorityType(row.priority)" size="small">
            {{ row.priority_label }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column label="剪切线" width="160">
        <template #default="{ row }">
          <span v-if="row.shearerline">
            {{ row.shearerline.code }} - {{ row.shearerline.name }}
          </span>
          <span v-else style="color: #c0c4cc">未分配</span>
        </template>
      </el-table-column>
      <el-table-column label="状态" width="100" align="center">
        <template #default="{ row }">
          <span
            class="status-tag"
            :class="getStatusClass(row.status)"
          >
            {{ row.status_label }}
          </span>
        </template>
      </el-table-column>
      <el-table-column label="开始时间" width="170" align="center">
        <template #default="{ row }">
          {{ formatDate(row.started_at) }}
        </template>
      </el-table-column>
      <el-table-column label="完成时间" width="170" align="center">
        <template #default="{ row }">
          {{ formatDate(row.completed_at) }}
        </template>
      </el-table-column>
      <el-table-column label="创建时间" width="170" align="center">
        <template #default="{ row }">
          {{ formatDate(row.created_at) }}
        </template>
      </el-table-column>
      <el-table-column label="操作" width="320" fixed="right" align="center">
        <template #default="{ row }">
          <template v-if="row.status === 'pending'">
            <el-button
              type="primary"
              size="small"
              :icon="Promotion"
              @click="handleAssign(row)"
            >
              分配
            </el-button>
          </template>
          <template v-if="row.status === 'assigned'">
            <el-button
              type="success"
              size="small"
              :icon="VideoPlay"
              @click="handleStart(row)"
            >
              开始
            </el-button>
          </template>
          <template v-if="row.status === 'processing'">
            <el-button
              type="success"
              size="small"
              :icon="CircleCheck"
              @click="handleComplete(row)"
            >
              完成
            </el-button>
          </template>
          <template v-if="row.status !== 'completed' && row.status !== 'cancelled'">
            <el-button
              type="info"
              size="small"
              :icon="Close"
              @click="handleCancel(row)"
            >
              取消
            </el-button>
          </template>
          <el-button
            type="warning"
            size="small"
            :icon="Edit"
            @click="handleEdit(row)"
          >
            编辑
          </el-button>
          <el-button
            type="danger"
            size="small"
            :icon="Delete"
            @click="handleDelete(row)"
          >
            删除
          </el-button>
        </template>
      </el-table-column>
    </el-table>

    <div style="margin-top: 20px; display: flex; justify-content: flex-end">
      <el-pagination
        v-model:current-page="pagination.page"
        v-model:page-size="pagination.per_page"
        :page-sizes="[10, 15, 20, 50, 100]"
        :total="pagination.total"
        layout="total, sizes, prev, pager, next, jumper"
        @size-change="handleSizeChange"
        @current-change="handlePageChange"
      />
    </div>

    <el-dialog
      v-model="dialogVisible"
      :title="dialogTitle"
      width="640px"
      :close-on-click-modal="false"
      @close="handleDialogClose"
    >
      <el-form
        ref="formRef"
        :model="formData"
        :rules="formRules"
        label-width="100px"
      >
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="订单号" prop="order_no">
              <el-input
                v-model="formData.order_no"
                placeholder="如：ORD20260621001"
                maxlength="50"
                show-word-limit
              />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="数量" prop="quantity">
              <el-input-number
                v-model="formData.quantity"
                :min="0"
                :max="99999"
                style="width: 100%"
              />
            </el-form-item>
          </el-col>
        </el-row>
        <el-form-item label="产品名称" prop="product_name">
          <el-input
            v-model="formData.product_name"
            placeholder="请输入产品名称"
            maxlength="200"
            show-word-limit
          />
        </el-form-item>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="优先级">
              <el-select v-model="formData.priority" style="width: 100%">
                <el-option label="低" value="low" />
                <el-option label="中" value="medium" />
                <el-option label="高" value="high" />
                <el-option label="紧急" value="urgent" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="剪切线">
              <el-select
                v-model="formData.shearerline_id"
                placeholder="请选择剪切线（可选）"
                clearable
                style="width: 100%"
              >
                <el-option
                  v-for="item in shearerOptions"
                  :key="item.id"
                  :label="`${item.code} - ${item.name}`"
                  :value="item.id"
                />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="状态">
              <el-select v-model="formData.status" style="width: 100%">
                <el-option label="待处理" value="pending" />
                <el-option label="已分配" value="assigned" />
                <el-option label="处理中" value="processing" />
                <el-option label="已完成" value="completed" />
                <el-option label="已取消" value="cancelled" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="排序">
              <el-input-number
                v-model="formData.sort_order"
                :min="0"
                :max="999"
                style="width: 100%"
              />
            </el-form-item>
          </el-col>
        </el-row>
        <el-form-item label="描述">
          <el-input
            v-model="formData.description"
            type="textarea"
            :rows="3"
            placeholder="请输入任务描述"
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

    <el-dialog
      v-model="assignVisible"
      title="分配剪切线"
      width="480px"
    >
      <el-form label-width="100px">
        <el-form-item label="选择剪切线">
          <el-select
            v-model="assignShearerlineId"
            placeholder="请选择剪切线"
            style="width: 100%"
          >
            <el-option
              v-for="item in availableShearers"
              :key="item.id"
              :label="`${item.code} - ${item.name} (${item.status_label})`"
              :value="item.id"
              :disabled="item.status === 'disabled' || item.status === 'error'"
            />
          </el-select>
        </el-form-item>
      </el-form>
      <template #footer>
        <div class="dialog-footer">
          <el-button @click="assignVisible = false">取消</el-button>
          <el-button
            type="primary"
            :loading="assignLoading"
            @click="confirmAssign"
          >
            确认分配
          </el-button>
        </div>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  Plus,
  Search,
  Refresh,
  Edit,
  Delete,
  Back,
  List,
  Clock,
  Loading,
  CircleCheck,
  VideoPlay,
  Close,
  Promotion,
} from '@element-plus/icons-vue'
import {
  getTaskList,
  createTask,
  updateTask,
  deleteTask,
  assignTask,
  startTask,
  completeTask,
  cancelTask,
  getAllShearers,
} from '@/extensions/shearerline/api/shearerline'

const router = useRouter()
const loading = ref(false)
const submitLoading = ref(false)
const assignLoading = ref(false)
const dialogVisible = ref(false)
const assignVisible = ref(false)
const dialogType = ref('create')
const taskList = ref([])
const shearerOptions = ref([])
const availableShearers = ref([])
const formRef = ref(null)
const currentTask = ref(null)
const assignShearerlineId = ref(null)

const filterForm = reactive({
  keyword: '',
  status: '',
  priority: '',
  shearerline_id: '',
})

const pagination = reactive({
  page: 1,
  per_page: 15,
  total: 0,
  total_pages: 0,
})

const stats = reactive({
  total: 0,
  pending: 0,
  processing: 0,
  completed: 0,
})

const formData = reactive({
  id: null,
  shearerline_id: null,
  order_no: '',
  product_name: '',
  quantity: 0,
  priority: 'medium',
  status: 'pending',
  description: '',
  sort_order: 0,
})

const formRules = {
  order_no: [
    { required: true, message: '请输入订单号', trigger: 'blur' },
    { min: 2, max: 50, message: '长度在 2 到 50 个字符', trigger: 'blur' },
  ],
  product_name: [
    { required: true, message: '请输入产品名称', trigger: 'blur' },
    { min: 2, max: 200, message: '长度在 2 到 200 个字符', trigger: 'blur' },
  ],
}

const dialogTitle = computed(() => {
  const titles = {
    create: '新建任务',
    edit: '编辑任务',
  }
  return titles[dialogType.value] || '任务信息'
})

function getStatusClass(status) {
  const classes = {
    pending: 'warning',
    assigned: 'primary',
    processing: 'active',
    completed: 'success',
    cancelled: 'system',
  }
  return classes[status] || ''
}

function getPriorityType(priority) {
  const types = {
    low: 'info',
    medium: '',
    high: 'warning',
    urgent: 'danger',
  }
  return types[priority] || ''
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

async function fetchShearers() {
  try {
    const data = await getAllShearers()
    shearerOptions.value = data
    availableShearers.value = data.filter((s) => s.status !== 'disabled' && s.status !== 'error')
  } catch (error) {
    console.error('获取剪切线列表失败:', error)
  }
}

async function fetchList() {
  loading.value = true
  try {
    const params = {
      page: pagination.page,
      per_page: pagination.per_page,
      ...filterForm,
    }
    const data = await getTaskList(params)
    taskList.value = data.list
    pagination.total = data.pagination.total
    pagination.total_pages = data.pagination.total_pages

    if (data.stats) {
      stats.total = data.stats.total
      stats.pending = data.stats.pending
      stats.processing = data.stats.processing
      stats.completed = data.stats.completed
    }
  } catch (error) {
    console.error('获取任务列表失败:', error)
  } finally {
    loading.value = false
  }
}

function handleSearch() {
  pagination.page = 1
  fetchList()
}

function handleReset() {
  filterForm.keyword = ''
  filterForm.status = ''
  filterForm.priority = ''
  filterForm.shearerline_id = ''
  pagination.page = 1
  fetchList()
}

function handleSizeChange(val) {
  pagination.per_page = val
  pagination.page = 1
  fetchList()
}

function handlePageChange(val) {
  pagination.page = val
  fetchList()
}

function handleCreate() {
  dialogType.value = 'create'
  resetForm()
  dialogVisible.value = true
}

function handleEdit(row) {
  dialogType.value = 'edit'
  formData.id = row.id
  formData.shearerline_id = row.shearerline_id
  formData.order_no = row.order_no
  formData.product_name = row.product_name
  formData.quantity = row.quantity
  formData.priority = row.priority
  formData.status = row.status
  formData.description = row.description || ''
  formData.sort_order = row.sort_order
  dialogVisible.value = true
}

function handleDelete(row) {
  ElMessageBox.confirm(
    `确定要删除任务「${row.order_no}」吗？`,
    '删除确认',
    {
      confirmButtonText: '确定删除',
      cancelButtonText: '取消',
      type: 'warning',
    }
  )
    .then(async () => {
      try {
        await deleteTask(row.id)
        ElMessage.success('删除成功')
        fetchList()
      } catch (error) {
        console.error('删除失败:', error)
      }
    })
    .catch(() => {})
}

function handleAssign(row) {
  currentTask.value = row
  assignShearerlineId.value = null
  assignVisible.value = true
}

async function confirmAssign() {
  if (!assignShearerlineId.value) {
    ElMessage.warning('请选择剪切线')
    return
  }

  assignLoading.value = true
  try {
    await assignTask(currentTask.value.id, {
      shearerline_id: assignShearerlineId.value,
    })
    ElMessage.success('分配成功')
    assignVisible.value = false
    fetchList()
  } catch (error) {
    console.error('分配失败:', error)
  } finally {
    assignLoading.value = false
  }
}

async function handleStart(row) {
  try {
    await startTask(row.id)
    ElMessage.success('任务已开始')
    fetchList()
  } catch (error) {
    console.error('操作失败:', error)
  }
}

async function handleComplete(row) {
  try {
    await completeTask(row.id)
    ElMessage.success('任务已完成')
    fetchList()
  } catch (error) {
    console.error('操作失败:', error)
  }
}

async function handleCancel(row) {
  ElMessageBox.confirm(
    `确定要取消任务「${row.order_no}」吗？`,
    '取消确认',
    {
      confirmButtonText: '确定取消',
      cancelButtonText: '继续执行',
      type: 'warning',
    }
  )
    .then(async () => {
      try {
        await cancelTask(row.id)
        ElMessage.success('任务已取消')
        fetchList()
      } catch (error) {
        console.error('操作失败:', error)
      }
    })
    .catch(() => {})
}

function goBack() {
  router.push('/shearerline/list')
}

function resetForm() {
  formData.id = null
  formData.shearerline_id = null
  formData.order_no = ''
  formData.product_name = ''
  formData.quantity = 0
  formData.priority = 'medium'
  formData.status = 'pending'
  formData.description = ''
  formData.sort_order = 0

  if (formRef.value) {
    formRef.value.resetFields()
  }
}

function handleDialogClose() {
  resetForm()
  dialogType.value = 'create'
}

async function handleSubmit() {
  if (!formRef.value) return

  await formRef.value.validate(async (valid) => {
    if (valid) {
      submitLoading.value = true
      try {
        const data = {
          shearerline_id: formData.shearerline_id,
          order_no: formData.order_no,
          product_name: formData.product_name,
          quantity: formData.quantity,
          priority: formData.priority,
          status: formData.status,
          description: formData.description,
          sort_order: formData.sort_order,
        }

        if (dialogType.value === 'create') {
          await createTask(data)
          ElMessage.success('创建成功')
        } else if (dialogType.value === 'edit') {
          await updateTask(formData.id, data)
          ElMessage.success('更新成功')
        }

        dialogVisible.value = false
        fetchList()
      } catch (error) {
        console.error('提交失败:', error)
      } finally {
        submitLoading.value = false
      }
    }
  })
}

onMounted(() => {
  fetchShearers()
  fetchList()
})
</script>
