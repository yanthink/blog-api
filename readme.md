## 项目概述

* 产品名称：个人博客系统api接口
* 项目代号：blog-api
* 演示地址：https://www.einsition.com
* 前端源码：https://github.com/yanthink/blog-v2

该项目基于 Laravel6.0 开发。


## 功能如下


- 文章列表 -- Elasticsearch搜索；
- 文章详情；
- 微信小程序评论、回复、收藏、点赞；
- 消息通知 -- 在线时 websocket 广播，离线是邮件通知；
- 用户认证 -- 登录、退出，小程序扫码登录；
- 多角色权限管理；
- 附件上传 -- 支持清除无效的附件；
- 文章管理 -- 列表、详情、发布、修改、删除；
- 用户管理 -- 列表、添加、修改、分配角色、分配权限；
- 在线用户 -- 实时查看在线用户数据；
- 定时清除无用的附件；
- Debugbar;


## 运行环境要求

- Nginx 1.8+
- PHP >= 7.1.3
- MySQL 5.7+
- Redis 3.0+
- Elasticsearch 6.0+

## 开发环境部署/安装

本项目代码使用 PHP 框架 [Laravel 6.0](https://learnku.com/docs/laravel/6.x) 开发，本地开发环境使用 [Laravel Valet](https://learnku.com/docs/laravel/6.x/valet/5128)。

### 基础安装

#### 1. 克隆源代码

克隆 `blog-api` 源代码到本地：

    git clone https://github.com/yanthink/blog-api.git

#### 2. 安装扩展包依赖

    composer install

#### 3. 生成配置文件

    cp .env.example .env

你可以根据情况修改 `.env` 文件里的内容，如数据库连接、缓存、邮件设置等：

```ini
APP_URL=http://api.blog.test
...
DB_HOST=localhost
DB_DATABASE=blog
DB_USERNAME=root
DB_PASSWORD=
```

#### 4. 生成秘钥

    php artisan key:generate

#### 5. 创建软连接

    php artisan storage:link

#### 6. 生成数据表及生成测试数据

    php artisan migrate --seed

#### 7. 生成加密 oauth_clients

    php artisan passport:install

初始的用户角色权限已使用数据迁移生成。

管理员账号密码如下:

```
username: admin
password: 888888
```

#### 8. 安装elasticsearch

    brew install elasticsearch

#### 9. 安装elasticsearch-analysis-ik

```bash
cd $(brew --prefix elasticsearch)
./bin/elasticsearch-plugin install https://github.com/medcl/elasticsearch-analysis-ik/releases/download/v6.2.4/elasticsearch-analysis-ik-6.2.4.zip
```

#### 10. 初始化Elasticsearch

```bash
php artisan es:init
```

至此, 安装完成 ^_^。


## 扩展包使用情况

| 扩展包 | 一句话描述 | 本项目应用场景 |
| --- | --- | --- |
| [dingo/api](https://github.com/dingo/api) | 处理api接口的开源插件 | 用于api接口 |
| [tymon/jwt-auth](https://github.com/tymondesigns/jwt-auth) | 身份验证的软件包 | 用于api认证  |
| [predis/predis](https://github.com/nrk/predis.git) | Redis 官方首推的 PHP 客户端开发包 | 缓存驱动 Redis 基础扩展包 |
| [spatie/laravel-permissiont](https://github.com/spatie/laravel-permission) | 角色权限管理 | 角色和权限控制 |
| [zgldh/qiniu-laravel-storage](https://github.com/zgldh/qiniu-laravel-storage) | Qiniu 云储存 Laravel 5 Storage版 | 存储附件 |
| [barryvdh/laravel-ide-helper](https://github.com/barryvdh/laravel-ide-helper) | 代码提示及补全工具 | 代码提示及补全 |
| [barryvdh/laravel-debugbar](https://github.com/barryvdh/laravel-debugbar) | 页面调试工具栏 (对 phpdebugbar 的封装) | 开发环境中的 DEBUG |
| [overtrue/laravel-wechat](https://github.com/overtrue/laravel-wechat) | 微信 SDK for Laravel / Lumen | 微信小程序登录 |

## 自定义 Artisan 命令

| 命令行名字 | 说明 | Cron | 代码调用 |
| --- | --- | --- | --- |
| `es:init` | 初始化elasticsearch | 无 | 无 |

## 队列清单

| 名称 | 说明 | 调用时机 |
| --- | --- | --- |
| PushArticleImagesToTargetDisk.php | 将文章图片保存到targetDisk | 发布文章和更新文章 |


## 小程序二维码

![file](http://qiniu.einsition.com/article/a27/126393085b4a7553b146d7099fa543fe.jpeg)