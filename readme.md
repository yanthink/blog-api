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
- MySQL 8.0+
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
    
#### 6. 安装 telescope
    
    php artisan telescope:install
    php artisan telescope:publish
    
> telescope 需要安装 bcmath 扩展，否则程序无报错但也无法运行（可以选择不启用，或者注释掉 `config/app.php` 下面的 `TelescopeServiceProvider` 服务提供注册）

#### 7. 生成数据表及生成测试数据

    php artisan migrate --seed

#### 8. 生成加密 oauth_clients

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

#### 11. 安装 laravel-echo-server

    npm install -g laravel-echo-server
    laravel-echo-server client:add

根据情况修改 `laravel-echo-server.json` 和 `.env` 配置，最后启动 laravel-echo server

    laravel-echo-server start
    
nginx 配置

    location /socket.io {
        proxy_pass http://127.0.0.1:6001;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header Host $host;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_redirect off;
        proxy_read_timeout 60s;
    }
    

至此, 安装完成 ^_^。
