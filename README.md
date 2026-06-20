# 电商订单库存后台 - 三端角色RBAC模块

基于 Vue 3 + Laravel 11 开发的电商订单库存后台管理系统，重点实现**三端角色RBAC**（平台端、商家端、仓库端）全览功能模块。

## 项目结构

```
002-电商订单库存后台/
├── backend/                           # Laravel 后端
│   ├── app/
│   │   ├── Enums/
│   │   │   ├── GuardType.php          # 守卫端枚举（platform/merchant/warehouse）
│   │   │   └── RoleStatus.php         # 角色状态枚举
│   │   ├── Exceptions/
│   │   │   └── BusinessException.php  # 业务异常类
│   │   ├── Http/
│   │   │   └── Controllers/
│   │   │       └── Api/
│   │   │           ├── RoleController.php       # 角色控制器
│   │   │           └── PermissionController.php # 权限控制器
│   │   ├── Models/
│   │   │   ├── User.php                # 用户模型
│   │   │   ├── Role.php                # 角色模型
│   │   │   └── Permission.php          # 权限模型
│   │   ├── Services/
│   │   │   ├── AuthorizationService.php   # 授权服务
│   │   │   ├── RoleService.php            # 角色服务
│   │   │   └── PermissionService.php      # 权限服务
│   │   └── Traits/
│   │       ├── ApiResponse.php         # 统一响应格式
│   │       └── HasGuardScopes.php      # 守卫端查询Scope
│   ├── database/
│   │   ├── factories/
│   │   │   ├── RoleFactory.php
│   │   │   └── PermissionFactory.php
│   │   ├── migrations/
│   │   │   ├── 2026_06_21_000001_create_roles_table.php
│   │   │   └── 2026_06_21_000002_add_guard_to_roles_permissions.php
│   │   └── seeders/
│   │       └── RolePermissionSeeder.php   # 角色权限数据填充（三端）
│   ├── routes/
│   │   └── api.php                     # API 路由
│   ├── tests/
│   │   ├── TestCase.php
│   │   └── Unit/Services/
│   │       ├── AuthorizationServiceTest.php
│   │       ├── RoleServiceTest.php
│   │       └── PermissionServiceTest.php
│   ├── phpunit.xml
│   ├── .env.example                    # 环境变量示例
│   └── composer.json
└── frontend/                           # Vue 3 前端
    ├── src/
    │   ├── api/
    │   │   └── role.js                 # API 接口封装
    │   ├── constants/
    │   │   └── rbac.js                 # RBAC常量（三端守卫、分组）
    │   ├── extensions/shearerline/     # 剪线员扩展模块
    │   ├── stores/
    │   │   └── permission.js           # 权限状态管理
    │   ├── utils/
    │   │   ├── request.js              # Axios封装（支持mock降级）
    │   │   └── permission.js           # 权限工具
    │   ├── views/
    │   │   ├── role/
    │   │   │   └── Index.vue           # 角色全览页面
    │   │   └── permission/
    │   │       └── Index.vue           # 权限管理页面
    │   ├── router/
    │   │   └── index.js
    │   └── main.js
    ├── vite.config.js
    ├── .env.development                # 开发环境变量
    ├── .env.production                 # 生产环境变量
    └── package.json
```

## 三端守卫（Guard）体系

| 守卫标识 | 中文名 | 说明 | 预置角色 |
|----------|--------|------|----------|
| `platform` | 平台端 | 电商平台运营管理后台 | super_admin, admin, operation, customer_service |
| `merchant` | 商家端 | 入驻商家店铺管理后台 | merchant_owner, merchant_manager, merchant_operator |
| `warehouse` | 仓库端 | 仓储物流管理后台 | warehouse_manager, warehouse_operator, warehouse_picker |

角色和权限表通过 `guard + name` 联合唯一索引实现三端隔离。

## 功能特性

### 后端 API 接口

所有接口统一前缀 `/api`。

**角色管理接口：**

| 方法 | 路径 | 描述 |
|------|------|------|
| GET | `/api/roles` | 获取角色列表（支持 `guard`/`keyword`/`status` 筛选 + 分页 + 统计） |
| GET | `/api/roles/all` | 获取所有启用的角色（下拉选择用，支持 `guard` 过滤） |
| GET | `/api/roles/{id}` | 获取角色详情（含权限列表） |
| POST | `/api/roles` | 创建角色（绑定权限） |
| PUT | `/api/roles/{id}` | 更新角色（系统角色不可改） |
| DELETE | `/api/roles/{id}` | 删除角色（系统角色不可删，软删除） |
| PATCH | `/api/roles/{id}/toggle-status` | 切换角色启用/禁用状态 |

**权限管理接口：**

| 方法 | 路径 | 描述 |
|------|------|------|
| GET | `/api/permissions` | 获取权限列表（按守卫端+分组嵌套） |
| GET | `/api/permissions/all` | 获取权限树形结构（角色配置用） |
| POST | `/api/permissions` | 创建权限 |

### 前端功能

**角色全览页面：**
- 数据统计卡片（总数 / 已启用 / 已禁用 / 系统角色）
- 守卫端切换（平台端 / 商家端 / 仓库端）
- 高级搜索筛选（关键词、状态）
- 角色列表展示（三端标签、头像、名称、标识、描述、权限数量、状态、类型）
- 分页功能
- 新建角色（表单 + 权限树配置）
- 编辑角色（表单 + 权限树配置）
- 查看角色详情
- 删除角色（带确认）
- 状态一键切换
- 系统内置角色保护（不可编辑/删除/禁用）

**权限管理页面：**
- 按守卫端+分组展示权限列表
- 新增权限功能（选择守卫端和分组）

---

## 环境变量配置

### 后端环境变量（backend/.env）

```bash
# ===== 基础配置 =====
APP_NAME="电商订单库存后台"
APP_ENV=local              # local / production / testing
APP_KEY=                   # php artisan key:generate 生成
APP_DEBUG=true             # 生产环境设为 false
APP_TIMEZONE=Asia/Shanghai
APP_URL=http://localhost:8000
APP_LOCALE=zh_CN

# ===== 数据库配置 =====
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce_rbac # 建议数据库名
DB_USERNAME=root
DB_PASSWORD=your_password

# ===== 会话配置 =====
SESSION_DRIVER=database    # 或 redis
SESSION_LIFETIME=120
SESSION_DOMAIN=localhost   # 生产环境改为 .your-domain.com

# ===== 缓存配置 =====
CACHE_STORE=database       # 或 redis
CACHE_PREFIX=ecommerce_rbac_

# ===== Redis配置（可选，用于缓存/队列/Session） =====
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# ===== 队列配置 =====
QUEUE_CONNECTION=database  # 开发用 sync，生产用 database/redis
QUEUE_FAILED_DRIVER=database-uuids

# ===== Sanctum 认证配置 =====
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:8000
# 生产环境：admin.your-domain.com,api.your-domain.com

# ===== CORS跨域配置 =====
CORS_ALLOWED_ORIGINS=http://localhost:3000
# 生产环境：https://admin.your-domain.com

# ===== 默认守卫端 =====
DEFAULT_GUARD=platform

# ===== 邮件配置（开发用log即可） =====
MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 前端环境变量

**开发环境（frontend/.env.development）：**
```bash
VITE_APP_TITLE="电商订单库存后台 - 开发环境"
VITE_APP_ENV=development
VITE_API_BASE_URL=http://localhost:8000
VITE_API_TIMEOUT=15000
VITE_DEFAULT_GUARD=platform
VITE_ENABLE_MOCK=true       # 后端API不可用时自动降级到mock
```

**生产环境（frontend/.env.production）：**
```bash
VITE_APP_TITLE="电商订单库存后台"
VITE_APP_ENV=production
VITE_API_BASE_URL=https://api.your-domain.com
VITE_API_TIMEOUT=15000
VITE_DEFAULT_GUARD=platform
VITE_ENABLE_MOCK=false
```

---

## 快速开始（本地开发）

### 0. 环境要求

| 依赖 | 版本要求 |
|------|----------|
| PHP | >= 8.2 |
| Composer | >= 2.5 |
| MySQL | >= 8.0 或 MariaDB >= 10.5 |
| Redis（可选） | >= 6.0 |
| Node.js | >= 18.0 |
| npm | >= 9.0 |

### 1. 后端部署

```bash
cd backend

# ===== Step 1: 安装依赖 =====
composer install

# ===== Step 2: 配置环境变量 =====
cp .env.example .env
php artisan key:generate

# 编辑 .env 填入数据库等配置
# vi .env

# ===== Step 3: 数据库迁移 =====
# 创建数据库（如果还没有）
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS ecommerce_rbac CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 执行迁移（2个迁移文件）
php artisan migrate

# 迁移内容：
#   2026_06_21_000001 - 创建 roles / permissions / role_permission / model_has_roles 表
#   2026_06_21_000002 - 为 roles/permissions 增加 guard 字段，改为 guard+name 联合唯一

# ===== Step 4: 数据填充（角色权限种子） =====
php artisan db:seed --class=RolePermissionSeeder

# 种子内容：
#   - platform 守卫：27个权限 + 4个预置角色
#   - merchant 守卫：15个权限 + 3个预置角色
#   - warehouse 守卫：11个权限 + 3个预置角色
#   - 总计：53个权限 + 10个预置角色

# ===== Step 5: 启动队列（如使用 database/redis 队列） =====
# 开发环境可跳过（QUEUE_CONNECTION=sync 同步执行）
php artisan queue:work --tries=3 --timeout=60

# ===== Step 6: 启动开发服务器 =====
php artisan serve --port=8000
```

### 2. 前端部署

```bash
cd frontend

# ===== Step 1: 安装依赖 =====
npm install

# ===== Step 2: 启动开发服务器（Vite HMR） =====
# 自动加载 .env.development
npm run dev

# ===== Step 3: 生产构建 =====
# 自动加载 .env.production
npm run build

# 本地预览构建结果
npm run preview
```

### 3. 访问地址

| 服务 | 地址 |
|------|------|
| 前端页面 | http://localhost:3000 |
| 后端 API | http://localhost:8000/api |
| 后端健康检查 | http://localhost:8000 |

---

## 数据库迁移与种子

### 迁移文件

| 文件 | 作用 |
|------|------|
| `2026_06_21_000001_create_roles_table.php` | 创建核心4表：roles, permissions, role_permission, model_has_roles |
| `2026_06_21_000002_add_guard_to_roles_permissions.php` | 加入 `guard` 字段，实现三端隔离 |

### 常用迁移命令

```bash
# 执行所有未执行的迁移
php artisan migrate

# 回滚最后一次迁移
php artisan migrate:rollback

# 回滚所有迁移并重新执行（危险！清空所有数据）
php artisan migrate:fresh

# 回滚+迁移+重新填充
php artisan migrate:fresh --seed --seeder=RolePermissionSeeder

# 查看迁移状态
php artisan migrate:status
```

### 种子数据填充

```bash
# 只填充角色权限
php artisan db:seed --class=RolePermissionSeeder

# 强制重新填充（生产环境需确认）
php artisan db:seed --class=RolePermissionSeeder --force
```

### 预置角色完整清单

**平台端（platform）：**
| 标识 | 名称 | 系统角色 | 权限分组 |
|------|------|----------|----------|
| super_admin | 超级管理员 | ✅ | 全部9组（dashboard, user, role, permission, order, product, inventory, system, merchant） |
| admin | 管理员 | ✅ | 8组（排除system） |
| operation | 运营专员 | ❌ | 4组（dashboard, order, product, inventory） |
| customer_service | 客服人员 | ❌ | 4组（dashboard, user, order, product） |

**商家端（merchant）：**
| 标识 | 名称 | 系统角色 | 权限分组 |
|------|------|----------|----------|
| merchant_owner | 店主 | ✅ | 全部6组（dashboard, order, product, inventory, merchant, staff） |
| merchant_manager | 店铺管理员 | ❌ | 5组（排除merchant） |
| merchant_operator | 运营人员 | ❌ | 3组（dashboard, order, product） |

**仓库端（warehouse）：**
| 标识 | 名称 | 系统角色 | 权限分组 |
|------|------|----------|----------|
| warehouse_manager | 仓库管理员 | ✅ | 全部6组（dashboard, order, product, inventory, warehouse, staff） |
| warehouse_operator | 仓库操作员 | ❌ | 4组（dashboard, order, product, inventory） |
| warehouse_picker | 拣货员 | ❌ | 3组（dashboard, order, inventory） |

---

## 队列任务配置

本项目使用 Laravel Queue 处理异步任务。

### 队列驱动选择

| 驱动 | 适用场景 | 配置值 |
|------|----------|--------|
| sync | 本地开发（同步执行） | `QUEUE_CONNECTION=sync` |
| database | 中小型项目 | `QUEUE_CONNECTION=database` |
| redis | 高并发生产环境 | `QUEUE_CONNECTION=redis` |

### 切换为 Database 队列

```bash
# 1. 创建队列任务表
php artisan queue:table
php artisan queue:failed-table
php artisan migrate

# 2. 修改 .env
QUEUE_CONNECTION=database

# 3. 启动队列监听器（生产环境建议用 Supervisor）
php artisan queue:work --tries=3 --timeout=60 --sleep=3
```

### 切换为 Redis 队列

```bash
# 1. 确保安装 phpredis 扩展或 predis/predis
composer require predis/predis  # 可选

# 2. 修改 .env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# 3. 启动队列
php artisan queue:work redis --tries=3 --timeout=60
```

### Supervisor 生产部署配置

```ini
# /etc/supervisor/conf.d/ecommerce-worker.conf
[program:ecommerce-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/backend/artisan queue:work database --sleep=3 --tries=3 --timeout=60
autostart=true
autorestart=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/backend/storage/logs/queue-worker.log
stopwaitsecs=3600
```

### 常用队列命令

```bash
# 启动队列 Worker
php artisan queue:work

# 处理单个任务后退出（调试用）
php artisan queue:work --once

# 监听队列（开发模式，代码变更自动重启）
php artisan queue:listen

# 查看失败任务
php artisan queue:failed

# 重试失败任务
php artisan queue:retry all
php artisan queue:retry 1            # 重试ID为1的任务

# 清除失败任务
php artisan queue:flush

# 重启所有队列Worker（代码部署后执行）
php artisan queue:restart
```

---

## 验收命令

完成部署后，按以下步骤逐步验证系统可用性。

### 1. 后端环境验证

```bash
cd backend

# 检查PHP版本和扩展
php -v                                # 需要 PHP >= 8.2
php -m | grep -E "mysql|redis|mbstring|openssl|pdo|tokenizer|xml|ctype|json|bcmath"

# 检查项目依赖
composer check-platform-reqs

# 检查环境配置
php artisan env                       # 确认 APP_ENV
php artisan config:cache              # 生产环境优化配置
php artisan route:clear
php artisan cache:clear

# 检查数据库连接
php artisan tinker --execute="echo DB::connection()->getDatabaseName();"
php artisan migrate:status            # 确认所有迁移已执行

# 检查数据表是否创建
php artisan tinker --execute="echo 'Roles: '.App\Models\Role::count().', Permissions: '.App\Models\Permission::count();"
# 预期输出：Roles: 10, Permissions: 53
```

### 2. 数据完整性验证

```bash
# 检查三个守卫端的角色数量
php artisan tinker --execute="
echo 'Platform roles: '.App\Models\Role::platform()->count().PHP_EOL;
echo 'Merchant roles: '.App\Models\Role::merchant()->count().PHP_EOL;
echo 'Warehouse roles: '.App\Models\Role::warehouse()->count().PHP_EOL;
echo 'Platform permissions: '.App\Models\Permission::platform()->count().PHP_EOL;
echo 'Merchant permissions: '.App\Models\Permission::merchant()->count().PHP_EOL;
echo 'Warehouse permissions: '.App\Models\Permission::warehouse()->count().PHP_EOL;
"

# 预期结果：
# Platform roles: 4, Merchant roles: 3, Warehouse roles: 3
# Platform permissions: 27, Merchant permissions: 15, Warehouse permissions: 11

# 验证超级管理员拥有所有平台权限
php artisan tinker --execute="
\$role = App\Models\Role::platform()->where('name','super_admin')->first();
echo 'Super admin permissions: '.\$role->permissions()->count();
"
# 预期结果：27
```

### 3. API 接口验收（curl 测试）

```bash
# 设置基础URL
BASE_URL=http://localhost:8000/api

# ===== 3.1 获取角色列表 =====
curl -s "$BASE_URL/roles?guard=platform&page=1&per_page=15" | python3 -m json.tool

# 预期响应：
# {
#   "code": 0,
#   "message": "success",
#   "data": {
#     "list": [...],
#     "pagination": { "total": 4, "page": 1, ... },
#     "stats": { "total": 4, "active": 4, "inactive": 0, "system": 2 }
#   }
# }

# ===== 3.2 获取三端所有角色（不带守卫过滤） =====
curl -s "$BASE_URL/roles?page=1&per_page=30" | python3 -m json.tool | grep '"total"'
# 预期："total": 10

# ===== 3.3 获取角色详情（含权限） =====
# 先获取ID，再查询
curl -s "$BASE_URL/roles/1" | python3 -m json.tool

# ===== 3.4 获取下拉角色列表 =====
curl -s "$BASE_URL/roles/all?guard=platform" | python3 -m json.tool
curl -s "$BASE_URL/roles/all?guard=merchant" | python3 -m json.tool
curl -s "$BASE_URL/roles/all?guard=warehouse" | python3 -m json.tool

# ===== 3.5 获取权限列表 =====
curl -s "$BASE_URL/permissions" | python3 -m json.tool        # 三端嵌套
curl -s "$BASE_URL/permissions/all?guard=platform" | python3 -m json.tool  # 仅平台端

# ===== 3.6 测试创建角色 =====
curl -s -X POST "$BASE_URL/roles" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "test_role",
    "guard": "platform",
    "display_name": "测试角色",
    "description": "验收创建的测试角色",
    "status": true,
    "sort_order": 100,
    "permissions": [1, 2, 6]
  }' | python3 -m json.tool
# 预期：code=0，data包含新角色信息

# ===== 3.7 测试更新角色 =====
# 先获取上一步创建的角色ID
curl -s -X PUT "$BASE_URL/roles/11" \
  -H "Content-Type: application/json" \
  -d '{
    "display_name": "测试角色-已修改",
    "permissions": [1, 6, 7, 8]
  }' | python3 -m json.tool

# ===== 3.8 测试切换状态 =====
curl -s -X PATCH "$BASE_URL/roles/11/toggle-status" | python3 -m json.tool
# 预期：status = false

# ===== 3.9 测试删除角色 =====
curl -s -X DELETE "$BASE_URL/roles/11" | python3 -m json.tool
# 预期：code=0, message="角色删除成功"

# ===== 3.10 验证系统角色保护 =====
# 尝试删除 super_admin (ID=1)
curl -s -X DELETE "$BASE_URL/roles/1" | python3 -m json.tool
# 预期：code=403, message="系统内置角色不允许删除"

# 尝试修改 admin (ID=2)
curl -s -X PUT "$BASE_URL/roles/2" \
  -H "Content-Type: application/json" \
  -d '{"display_name":"恶意修改"}' | python3 -m json.tool
# 预期：code=403, message="系统内置角色不允许修改"
```

### 4. 运行自动化测试

```bash
cd backend

# 运行所有单元测试
./vendor/bin/phpunit

# 或使用 artisan 命令
php artisan test

# 运行特定测试类
php artisan test --filter=RoleServiceTest
php artisan test --filter=PermissionServiceTest
php artisan test --filter=AuthorizationServiceTest

# 运行并生成覆盖率报告（需要xdebug/pcov扩展）
php artisan test --coverage
```

### 5. 前端构建验证

```bash
cd frontend

# 检查依赖
npm list --depth=0

# 生产构建（验证无编译错误）
npm run build

# 检查构建产物大小
du -sh dist/
ls -lah dist/assets/
```

### 6. 端到端联调验收

1. 启动后端：`cd backend && php artisan serve --port=8000`
2. 启动前端：`cd frontend && npm run dev`
3. 打开浏览器访问 `http://localhost:3000`
4. 手动验证：
   - ✅ 角色全览页面加载，显示统计卡片和三端10个角色
   - ✅ 切换守卫端（平台/商家/仓库），角色列表正确过滤
   - ✅ 使用关键词搜索（如"管理员"），筛选正确
   - ✅ 按状态筛选（启用/禁用），结果正确
   - ✅ 点击"新建角色"，表单正常，权限树按守卫端正确加载
   - ✅ 创建非系统角色成功，列表中新增记录
   - ✅ 编辑非系统角色，权限修改保存成功
   - ✅ 切换状态开关，角色状态正确变更
   - ✅ 删除非系统角色，确认后消失
   - ✅ 系统角色（带"系统"标签）的编辑/删除按钮禁用
   - ✅ 切换到权限管理页面，三端分组权限正确展示
   - ✅ 新增权限功能正常（选守卫端+分组+填标识名称）

---

## 技术栈

### 后端
- **框架**: Laravel 11
- **PHP**: >= 8.2
- **数据库**: MySQL 8.0+ / MariaDB 10.5+ / PostgreSQL
- **认证**: Laravel Sanctum
- **缓存**: Database / Redis
- **队列**: Sync / Database / Redis
- **测试**: PHPUnit 10

### 前端
- **框架**: Vue 3 (Composition API)
- **构建工具**: Vite 5
- **UI 组件**: Element Plus 2.4
- **路由**: Vue Router 4
- **HTTP 客户端**: Axios 1.6（支持mock降级）
- **状态管理**: Pinia（预留，当前本地响应式）
- **图标**: Element Plus Icons

---

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
- 常见错误码：400参数错误 / 401未登录 / 403无权限 / 404不存在 / 422验证失败 / 500服务器错误

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
    },
    "stats": {
      "total": 100,
      "active": 80,
      "inactive": 20,
      "system": 5
    }
  }
}
```

---

## 安全特性

1. **三端隔离**：通过 `guard` 字段 + 联合唯一索引实现平台/商家/仓库三端权限体系完全隔离
2. **系统角色保护**：系统内置角色（`is_system=true`）不可编辑、删除、切换状态
3. **软删除**：角色删除采用软删除（`deleted_at`），数据可追溯
4. **参数验证**：所有API入口采用 Validator 完整校验，含中文错误信息
5. **关联删除**：删除角色时通过 `DB transaction` 自动解绑权限关联
6. **权限跨端校验**：给角色绑定权限时，仅允许绑定同一守卫端的权限
7. **重复检测**：同一守卫端内，角色标识/权限标识唯一，防重复创建

---

## 扩展开发

### 添加新守卫端（如供应商端 supplier）

1. 在 `GuardType.php` 中新增枚举 case：`case SUPPLIER = 'supplier';`
2. 在 `RolePermissionSeeder.php` 中新增 `supplier` 组的权限和角色
3. 前端 `rbac.js` 中补充 GUARD 常量和映射
4. 执行 `php artisan db:seed --class=RolePermissionSeeder` 填充

### 添加新权限分组

1. 后端 `PermissionService::getGroupName()` 中新增中文名称映射
2. 前端 `rbac.js` 的 `GROUP_NAMES` 中新增映射
3. 通过权限管理页面创建具体权限项

---

## 生产部署检查清单

- [ ] `.env` 中 `APP_ENV=production`，`APP_DEBUG=false`
- [ ] `APP_KEY` 已生成且保密
- [ ] 数据库账号权限最小化（非root）
- [ ] `QUEUE_CONNECTION` 改为 `redis` 或 `database`
- [ ] Supervisor 已配置队列 Worker 守护进程
- [ ] `CORS_ALLOWED_ORIGINS` 仅允许生产域名
- [ ] `SESSION_DOMAIN`、`SANCTUM_STATEFUL_DOMAINS` 配置正确
- [ ] 执行了 `php artisan config:cache` 和 `php artisan route:cache`
- [ ] 目录权限：`storage/` 和 `bootstrap/cache/` 可写
- [ ] 前端 `VITE_API_BASE_URL` 指向正式API域名
- [ ] 配置了HTTPS（SSL证书）
- [ ] 配置了日志轮转（daily日志）
- [ ] 跑通了所有验收命令和联调步骤

## License

MIT
