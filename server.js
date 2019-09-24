const WebSocket = require('ws'); // socket.io 支持的协议版本（4）和 微信小程序 websocket 协议版本（13）不一致，所以选用ws
const Redis = require('ioredis');
const fs = require('fs');
const ini = require('ini');
const jwt = require('jsonwebtoken');
const url = require('url');
const mysql = require('mysql');

const config = ini.parse(fs.readFileSync('./.env', 'utf8')); // 读取.env配置

const redis = new Redis({
    port: env('REDIS_PORT', 6379),          // Redis port
    host: env('REDIS_HOST', '127.0.0.1'),   // Redis host
    // family: 4,           // 4 (IPv4) or 6 (IPv6)
    password: env('REDIS_PASSWORD', null),
    db: 0,
});

const ws = new WebSocket.Server({
    port: 6001,
    clientTracking: false,
    verifyClient({req}, cb) {
        try {
            const urlParams = url.parse(req.url, true);
            const token = urlParams.query.token || req.headers.authorization.split(' ')[1];
            const jwtSecret = env('JWT_SECRET');
            const algorithm = env('JWT_ALGO', 'HS256');

            const {sub, nbf, exp} = jwt.verify(token, jwtSecret, {algorithm});

            if (Date.now() / 1000 > exp) {
                cb(false, 401, 'token已过期.')
            }

            if (Date.now() / 1000 < nbf) {
                cb(false, 401, 'token未到生效时间.')
            }

            if (!sub) {
                cb(false, 401, '无法验证令牌签名.')
            }

            cb(true)
        } catch (e) {
            console.info(e);
            cb(false, 401, 'Token could not be parsed from the request.');
        }

    },
});

const clients = {};

ws.on('connection', (ws, req) => {
    try {
        const urlParams = url.parse(req.url, true);
        const token = urlParams.query.token || req.headers.authorization.split(' ')[1];
        const jwtSecret = env('JWT_SECRET');
        const algorithm = env('JWT_ALGO', 'HS256');

        const {sub, exp} = jwt.verify(token, jwtSecret, {algorithm});
        const uuid = sub;

        ws.uuid = uuid;
        ws.exp = exp;
        if (!clients[uuid]) {
            clients[uuid] = [];
        }

        clients[uuid].push(ws);

        if (/^\d{1,15}$/.test(String(uuid))) {
            userLogged(ws, req); // 记录用户登录
        }
    } catch (e) {
        console.info(e.message);
        ws.close();
    }

    ws.on('message', message => { // 接收消息事件
        if (ws.uuid) {
            console.info('[%s] message：%s %s', getNowDateTimeString(), ws.uuid, message);
        }
        if (ws.exp && Date.now() / 1000 > ws.exp) {
            ws.close();
        }
    });

    ws.on('close', () => { // 关闭链接事件
        if (ws.uuid) {
            console.info('[%s] closed：%s', getNowDateTimeString(), ws.uuid);

            const wss = clients[ws.uuid];

            if (wss instanceof Array) {
                const index = wss.indexOf(ws);

                if (index > -1) {
                    wss.splice(index, 1);
                    if (/^\d{1,15}$/.test(String(ws.uuid))) {
                        userLoggedOut(ws, req); // 用户退出
                    }
                    if (wss.length === 0) {
                        delete clients[ws.uuid];
                    }
                }
            }
        }
    });
});


// redis 订阅
redis.psubscribe('*', function (err, count) {
});

redis.on('pmessage', (subscrbed, channel, message) => { // 接收 laravel 推送的消息
    console.info('[%s] %s %s', getNowDateTimeString(), channel, message);

    const {event} = JSON.parse(message);
    const uuid = channel.split('.')[1];
    const wss = clients[uuid];

    switch (event) {
        case 'Illuminate\\Notifications\\Events\\BroadcastNotificationCreated':
        case 'App\\Events\\WechatScanLogin':
            if (wss instanceof Array) {
                wss.forEach(ws => {
                    if (ws.readyState === 1) {
                        ws.send(message);
                    }
                });
            }
            break;
    }
});

function env(key, def = '') {
    return config[key] || def
}

function getNowDateTimeString() {
    const date = new Date();
    return `${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()} ${date.getHours()}:${date.getMinutes()}:${date.getSeconds()}`;
}

function clearUsersOnline() {
    const connection = mysqlConnection();

    const sql = 'truncate table users_online';

    connection.query(sql, function (err) {

    });

    connection.end();
}


async function userLogged(ws, req) {
    const connection = mysqlConnection();

    try {
        const result = await new Promise((resolve, reject) => {
            const sql = 'SELECT * FROM users_online WHERE user_id = ? LIMIT 1';

            connection.query(sql, [ws.uuid], function (err, result) {
                if (err) {
                    console.log('[SELECT ERROR] - ', err.message);
                    return reject(err);
                }

                resolve(result);
            });
        });

        const ip = getClientIp(req);
        const now = getNowDateTimeString();
        const wss = clients[ws.uuid];
        const stackLevel = wss.length;
        console.info(stackLevel);

        if (result.length === 0) {
            const sql = 'INSERT INTO users_online (user_id, ip, stack_level, created_at, updated_at) VALUES (?, ?, ?, ?, ?)';

            connection.query(sql, [ws.uuid, ip, stackLevel, now, now], function (err) {
                if (err) {
                    console.log('[SELECT ERROR] - ', err.message);
                    throw new Error(err);
                }
            });
        } else {
            const sql = 'UPDATE users_online set ip = ?, stack_level = ?, updated_at = ? WHERE user_id = ?';
            connection.query(sql, [ip, stackLevel, now, ws.uuid], function (err) {
                if (err) {
                    console.log('[SELECT ERROR] - ', err.message);
                    throw new Error(err);
                }
            });
        }
    } catch (e) {
        console.info(e.message);
    }

    connection.end();
}

function userLoggedOut(ws, req) {
    const connection = mysqlConnection();

    const now = getNowDateTimeString();
    const wss = clients[ws.uuid];
    const stackLevel = wss.length;

    try {
        if (stackLevel === 0) {
            const sql = 'DELETE FROM users_online WHERE user_id = ?';

            connection.query(sql, [ws.uuid], function (err) {
                if (err) {
                    console.log('[SELECT ERROR] - ', err.message);
                    throw new Error(err);
                }
            });
        } else {
            const sql = 'UPDATE users_online set stack_level = ?, updated_at = ? WHERE user_id = ?';
            connection.query(sql, [stackLevel, now, ws.uuid], function (err) {
                if (err) {
                    console.log('[SELECT ERROR] - ', err.message);
                    throw new Error(err);
                }
            });
        }
    } catch (e) {
        console.info(e.message);
    }

    connection.end();
}

function getClientIp(req) {
    return req.headers['x-forwarded-for'] ||
        req.connection.remoteAddress ||
        req.socket.remoteAddress ||
        req.connection.socket.remoteAddress;
}

function mysqlConnection() {
    const dbConfig = {
        user: env('DB_USERNAME'),
        password: env('DB_PASSWORD'),
        database: env('DB_DATABASE'),
        charset: 'utf8mb4',
    };

    const unixSocket = env('DB_SOCKET');
    if (unixSocket) {
        dbConfig.socketPath = unixSocket;
    } else {
        dbConfig.host = env('DB_HOST', 'localhost');
        dbConfig.port = env('DB_PORT', 3306);
    }

    const connection = mysql.createConnection(dbConfig);

    connection.connect();

    return connection;
}

clearUsersOnline();