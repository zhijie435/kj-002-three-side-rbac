<template>
  <div class="app-container">
    <el-container>
      <el-header class="header">
        <div class="header-content">
          <div class="logo">
            <el-icon :size="28" color="#409eff"><UserFilled /></el-icon>
            <span class="title">电商订单库存后台</span>
          </div>
          <div class="header-right">
            <el-dropdown @command="handleCommand">
              <span class="user-info">
                <el-avatar :size="32" icon="UserFilled" />
                <span class="username">管理员</span>
                <el-icon><CaretBottom /></el-icon>
              </span>
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item command="profile">个人中心</el-dropdown-item>
                  <el-dropdown-item command="logout" divided>退出登录</el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>
          </div>
        </div>
      </el-header>
      <el-container>
        <el-aside width="220px" class="sidebar">
          <el-menu
            :default-active="activeMenu"
            router
            background-color="#001529"
            text-color="#fff"
            active-text-color="#409eff"
          >
            <el-menu-item index="/">
              <el-icon><DataAnalysis /></el-icon>
              <span>数据面板</span>
            </el-menu-item>
            <el-sub-menu index="/user">
              <template #title>
                <el-icon><User /></el-icon>
                <span>用户管理</span>
              </template>
              <el-menu-item index="/user/list">用户列表</el-menu-item>
            </el-sub-menu>
            <el-sub-menu index="/role">
              <template #title>
                <el-icon><Lock /></el-icon>
                <span>角色权限</span>
              </template>
              <el-menu-item index="/role/list">角色全览</el-menu-item>
              <el-menu-item index="/permission/list">权限管理</el-menu-item>
            </el-sub-menu>
            <el-sub-menu index="/order">
              <template #title>
                <el-icon><Document /></el-icon>
                <span>订单管理</span>
              </template>
              <el-menu-item index="/order/list">订单列表</el-menu-item>
            </el-sub-menu>
            <el-sub-menu index="/product">
              <template #title>
                <el-icon><Goods /></el-icon>
                <span>商品管理</span>
              </template>
              <el-menu-item index="/product/list">商品列表</el-menu-item>
            </el-sub-menu>
            <el-sub-menu index="/inventory">
              <template #title>
                <el-icon><Box /></el-icon>
                <span>库存管理</span>
              </template>
              <el-menu-item index="/inventory/list">库存列表</el-menu-item>
            </el-sub-menu>
          </el-menu>
        </el-aside>
        <el-main class="main-content">
          <router-view v-slot="{ Component }">
            <transition name="fade" mode="out-in">
              <component :is="Component" />
            </transition>
          </router-view>
        </el-main>
      </el-container>
    </el-container>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { ElMessage } from 'element-plus'

const route = useRoute()
const activeMenu = computed(() => route.path)

const handleCommand = (command) => {
  if (command === 'logout') {
    ElMessage.success('已退出登录')
  } else if (command === 'profile') {
    ElMessage.info('个人中心')
  }
}
</script>

<style scoped>
.app-container {
  height: 100vh;
}

.header {
  background: #fff;
  border-bottom: 1px solid #e4e7ed;
  padding: 0;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 100%;
  padding: 0 24px;
}

.logo {
  display: flex;
  align-items: center;
  gap: 10px;
}

.title {
  font-size: 18px;
  font-weight: 600;
  color: #303133;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  color: #606266;
}

.username {
  font-size: 14px;
}

.sidebar {
  background: #001529;
}

.main-content {
  background: #f5f7fa;
  padding: 20px;
  overflow-y: auto;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
