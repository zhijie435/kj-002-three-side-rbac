# 电商订单库存后台 - 角色全览模块

基于 Vue 3 + Laravel 11 开发的电商订单库存后台管理系统，重点实现角色全览功能模块。

## 项目结构

```
002-电商订单库存后台/
├── backend/                    # Laravel 后端
│   ├── app/
│   │   ├── Http/
│   │   │   └── Controllers/
│   │   │       └── Api/
│   │   │           ├── RoleController.php       # 角色控制器
│   │   │           └── PermissionController.php # 权限控制器
│   │   └── Models/
│   │       ├── Role.php                         # 角色模型
│   │       └── Permission.php                   # 权限模型
│   ├── database/
│   │   ├── migrations/
│   │   │   └── 2026_06_21_000001_create_roles_table.php
│   │   └── seeders/
│   │       └── RolePermissionSeeder.php         # 角色权限数据填充
│   ├── routes/
│   │   └── api.php                              # API 路由
│   └── composer.json
└── frontend/                    # Vue 3 前端
    ├── src/
    │   ├── api/
    │   │   └── role.js                          # API 接口封装
    │   ├── views/
    │   │   ├── role/
    │   │   │   └── Index.vue                    # 角色全览页面
    │   │   └── permission/
    │   │       └── Index.vue                    # 权限管理页面
    │   ├── router/
    │   │   └── index.js                         # 路由配置
    │   ├── utils/
    │   │   └── request.js                       # Axios 封装
    │   ├── styles/
    │   │   └── index.css                        # 全局样式
    │   ├── App.vue                              # 根组件
    │   └── main.js                              # 入口文件
    ├── index.html
    ├── vite.config.js
    └── package.json
```

## 功能特性

### 后端 API 接口

| 方法 | 路径 | 描述 |
|------|------|------|
| GET | `/api/roles` | 获取角色列表（支持搜索、筛选、分页） |
| GET | `/api/roles/all` | 获取所有启用的角色（下拉选择用） |
| GET | `/api/roles/{id}` | 获取角色详情 |
| POST | `/api/roles` | 创建角色 |
| PUT | `/api/roles/{id}` | 更新角色 |
| DELETE | `/api/roles/{id}` | 删除角色 |
| PATCH | `/api/roles/{id}/toggle-status` | 切换角色状态 |
| GET | `/api/permissions` | 获取权限列表（按分组） |
| GET | `/api/permissions/all` | 获取权限树形结构 |
| POST | `/api/permissions` | 创建权限 |

### 前端功能

**角色全览页面：**
- 数据统计卡片（角色总数、已启用、已禁用、系统角色）
- 高级搜索筛选（关键词、状态）
- 角色列表展示（头像、名称、标识、描述、权限数量、状态、类型）
- 分页功能
- 新建角色（表单 + 权限配置）
- 编辑角色（表单 + 权限配置）
- 查看角色详情
- 删除角色（带确认）
- 系统内置角色保护（不可编辑/删除）

**权限管理页面：**
- 按分组展示权限列表
- 新增权限功能
- 权限分组管理

## 数据模型

### Role 角色模型

| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint | 主键 |
| name | varchar(50) | 角色标识（唯一） |
| display_name | varchar(100) | 角色显示名称 |
| description | text | 角色描述 |
| is_system | boolean | 是否系统内置 |
| status | boolean | 状态（启用/禁用） |
| sort_order | int | 排序 |
| created_at | timestamp | 创建时间 |
| updated_at | timestamp | 更新时间 |
| deleted_at | timestamp | 删除时间（软删除） |

### Permission 权限模型

| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint | 主键 |
| name | varchar(100) | 权限标识（唯一） |
| display_name | varchar(100) | 权限显示名称 |
| group | varchar(50) | 权限分组 |
| description | text | 权限描述 |
| created_at | timestamp | 创建时间 |
| updated_at | timestamp | 更新时间 |

### 预置数据

**系统角色：**
- `super_admin` - 超级管理员（全部权限）
- `admin` - 管理员（大部分权限，排除系统设置）
- `operation` - 运营专员（订单、商品、库存管理）
- `customer_service` - 客服人员（仅查看权限）
- `warehouse` - 仓库管理员（库存、商品管理）

**权限分组：**
- dashboard - 数据面板
- user - 用户管理
- role - 角色管理
- permission - 权限管理
- order - 订单管理
- product - 商品管理
- inventory - 库存管理
- system - 系统设置

## 快速开始

### 后端部署

```bash
cd backend

# 安装依赖
composer install

# 配置环境变量
cp .env.example .env
php artisan key:generate

# 修改 .env 中的数据库配置

# 运行迁移
php artisan migrate

# 填充测试数据
php artisan db:seed --class=RolePermissionSeeder

# 启动服务
php artisan serve --port=8000
```

### 前端部署

```bash
cd frontend

# 安装依赖
npm install

# 启动开发服务器
npm run dev

# 构建生产版本
npm run build
```

### 访问地址

- 前端：http://localhost:3000
- 后端 API：http://localhost:8000

## 技术栈

### 后端
- **框架**: Laravel 11
- **PHP**: >= 8.2
- **数据库**: MySQL / PostgreSQL
- **认证**: Laravel Sanctum

### 前端
- **框架**: Vue 3 (Composition API)
- **构建工具**: Vite 5
- **UI 组件**: Element Plus 2.4
- **路由**: Vue Router 4
- **HTTP 客户端**: Axios 1.6
- **图标**: Element Plus Icons

## API 响应格式

统一的响应结构：

```json
{
  "code": 0,
  "message": "success",
  "data": {}
}
```

- `code: 0` 表示成功
- `code: 非0` 表示失败，`message` 为错误信息

列表响应结构：

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "list": [...],
    "pagination": {
      "total": 100,
      "page": 1,
      "per_page": 15,
      "total_pages": 7
    }
  }
}
```

## 扩展开发

### 添加新角色
1. 通过页面的「新建角色」按钮创建
2. 配置角色信息和权限
3. 保存后即可使用

### 添加新权限
1. 进入「权限管理」页面
2. 点击「新增权限」
3. 填写权限信息并选择分组
4. 保存后即可在角色配置中选择

## 安全特性

1. **系统角色保护**：系统内置角色不可编辑和删除
2. **软删除**：角色删除采用软删除，数据可追溯
3. **权限验证**：API 层提供完整的参数验证
4. **关联删除**：删除角色时自动解绑权限关联

## License

MIT
