# 本地快速拉站

## 当前已验证路径

在当前维护分支上，下面这条路径已经完成了本地验证：

1. 运行 [/scripts/prepare-local-dev](/Users/apple/Documents/dujiaoshuka/scripts/prepare-local-dev)
2. 使用 [/.env.local.example](/Users/apple/Documents/dujiaoshuka/.env.local.example) 生成本地 `.env`
3. 检测到 `/private/tmp/mysql.sock` 时，自动切到 MariaDB socket 模式并使用当前系统用户
4. 使用 [/scripts/php74](/Users/apple/Documents/dujiaoshuka/scripts/php74) 启动 Laravel 内置服务器
5. 本地首页 HTTP 返回 `200 OK`

本次实际验证结果：

- `./scripts/php74 artisan --version`：`Laravel Framework 6.20.42`
- `./scripts/php74 artisan route:list`：路由注册成功
- `./scripts/php74 artisan migrate:status --no-ansi`：数据库连接成功
- `curl -I http://127.0.0.1:8030/`：返回 `HTTP/1.1 200 OK`

## 推荐步骤

```bash
./scripts/prepare-local-dev
./scripts/php74 artisan --version
./scripts/php74 artisan route:list
./scripts/php74 -S 127.0.0.1:8020 -t public
```

如果当前机器上存在 Homebrew MariaDB 的 socket：

- `/private/tmp/mysql.sock`

准备脚本会自动把 `.env` 切到：

- `DB_HOST=localhost`
- `DB_SOCKET=/private/tmp/mysql.sock`
- `DB_USERNAME=<当前系统用户>`
- `DB_PASSWORD=`

## 说明

- `.env` 仍然不会进入版本控制
- `install.lock` 会由准备脚本自动补齐
- 当前本地快速启动仍然依赖 PHP `7.4` 这条遗留基线
