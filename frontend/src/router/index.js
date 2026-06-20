import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    redirect: '/role/list',
  },
  {
    path: '/role/list',
    name: 'RoleList',
    component: () => import('@/views/role/Index.vue'),
    meta: { title: '角色全览' },
  },
  {
    path: '/permission/list',
    name: 'PermissionList',
    component: () => import('@/views/permission/Index.vue'),
    meta: { title: '权限管理' },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  document.title = to.meta.title ? `${to.meta.title} - 电商订单库存后台` : '电商订单库存后台'
  next()
})

export default router
