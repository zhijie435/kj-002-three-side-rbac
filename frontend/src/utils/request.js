import axios from 'axios'
import { ElMessage, ElMessageBox } from 'element-plus'

const request = axios.create({
  baseURL: '/api',
  timeout: 15000,
  headers: {
    'Content-Type': 'application/json',
  },
})

request.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

request.interceptors.response.use(
  (response) => {
    const res = response.data

    if (res.code === 0) {
      return res.data
    }

    if (res.code === 401) {
      ElMessageBox.confirm('登录状态已过期，请重新登录', '系统提示', {
        confirmButtonText: '重新登录',
        cancelButtonText: '取消',
        type: 'warning',
      }).then(() => {
        localStorage.removeItem('token')
        window.location.reload()
      })
      return Promise.reject(new Error(res.message || '未授权'))
    }

    ElMessage.error(res.message || '请求失败')
    return Promise.reject(new Error(res.message || '请求失败'))
  },
  (error) => {
    const { response } = error

    if (response) {
      if (response.status === 422) {
        const errors = response.data.errors
        if (errors) {
          const messages = Object.values(errors).flat().join('；')
          ElMessage.error(messages)
        } else {
          ElMessage.error(response.data.message || '参数验证失败')
        }
      } else if (response.status === 403) {
        ElMessage.error(response.data.message || '没有权限执行此操作')
      } else if (response.status === 404) {
        ElMessage.error('请求的资源不存在')
      } else {
        ElMessage.error(response.data?.message || `请求错误：${response.status}`)
      }
    } else if (error.code === 'ECONNABORTED') {
      ElMessage.error('请求超时，请稍后重试')
    } else {
      ElMessage.error('网络错误，请检查网络连接')
    }

    return Promise.reject(error)
  }
)

export default request
