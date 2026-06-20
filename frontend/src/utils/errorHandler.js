import { ElMessage, ElNotification } from 'element-plus'
import { ERROR_CODES } from '@/constants/rbac'

const errorMessageMap = {
  [ERROR_CODES.UNAUTHORIZED]: '登录已过期，请重新登录',
  [ERROR_CODES.FORBIDDEN]: '没有权限执行此操作',
  [ERROR_CODES.NOT_FOUND]: '请求的资源不存在',
  [ERROR_CODES.VALIDATION_FAILED]: '参数验证失败',
  [ERROR_CODES.SERVER_ERROR]: '服务器错误，请稍后重试',
}

const errorTypeMap = {
  network: '网络连接失败，请检查网络设置',
  timeout: '请求超时，请稍后重试',
  canceled: '请求已取消',
  unknown: '请求失败，请稍后重试',
}

export function handleApiError(error) {
  const { response, code, message } = error

  if (response) {
    const status = response.status
    const data = response.data

    if (data && data.message) {
      return data.message
    }

    return errorMessageMap[status] || `请求错误：${status}`
  }

  if (code === 'ECONNABORTED' || code === 'ETIMEDOUT') {
    return errorTypeMap.timeout
  }

  if (code === 'ERR_NETWORK' || message === 'Network Error') {
    return errorTypeMap.network
  }

  if (code === 'ERR_CANCELED') {
    return errorTypeMap.canceled
  }

  return message || errorTypeMap.unknown
}

export function showError(error, type = 'message') {
  const errorMsg = handleApiError(error)

  if (type === 'notification') {
    ElNotification.error({
      title: '错误',
      message: errorMsg,
      duration: 3000,
    })
  } else {
    ElMessage.error(errorMsg)
  }

  return errorMsg
}

export function showSuccess(message, type = 'message') {
  if (type === 'notification') {
    ElNotification.success({
      title: '成功',
      message,
      duration: 2000,
    })
  } else {
    ElMessage.success(message)
  }
}

export function showWarning(message, type = 'message') {
  if (type === 'notification') {
    ElNotification.warning({
      title: '提示',
      message,
      duration: 3000,
    })
  } else {
    ElMessage.warning(message)
  }
}

export class AppError extends Error {
  constructor(message, code = ERROR_CODES.BAD_REQUEST, details = null) {
    super(message)
    this.name = 'AppError'
    this.code = code
    this.details = details
  }

  static fromApiError(error) {
    const message = handleApiError(error)
    const code = error?.response?.status || ERROR_CODES.BAD_REQUEST
    return new AppError(message, code, error)
  }

  isUnauthorized() {
    return this.code === ERROR_CODES.UNAUTHORIZED
  }

  isForbidden() {
    return this.code === ERROR_CODES.FORBIDDEN
  }

  isNotFound() {
    return this.code === ERROR_CODES.NOT_FOUND
  }

  isValidationError() {
    return this.code === ERROR_CODES.VALIDATION_FAILED
  }
}

export function withErrorHandler(fn, options = {}) {
  const {
    showError: showErrorFlag = true,
    errorType = 'message',
    defaultValue = null,
  } = options

  return async (...args) => {
    try {
      return await fn(...args)
    } catch (error) {
      if (showErrorFlag) {
        showError(error, errorType)
      }
      if (typeof defaultValue === 'function') {
        return defaultValue(error)
      }
      return defaultValue
    }
  }
}
