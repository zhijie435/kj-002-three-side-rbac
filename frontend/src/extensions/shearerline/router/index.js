const routes = [
  {
    path: '/shearerline/list',
    name: 'ShearerlineList',
    component: () => import('@/extensions/shearerline/views/ShearerlineIndex.vue'),
    meta: { title: '剪切线管理' },
  },
  {
    path: '/shearerline/tasks',
    name: 'ShearerlineTaskList',
    component: () => import('@/extensions/shearerline/views/TaskIndex.vue'),
    meta: { title: '任务管理' },
  },
]

export default routes
