const WebSocket = require('ws'); // socket.io 支持的协议版本（4）和 微信小程序 websocket 协议版本（13）不一致，所以选用ws
const Redis = require('ioredis');
const fs = require('fs');
const ini = require('ini');
const jwt = require('jsonwebtoken');
const url = require('url');

const config = ini.parse(fs.readFileSync('./.env', 'utf8')); // 读取.env配置

const redis = new Redis({
    port: env('REDIS_PORT', 6379),          // Redis port
    host: env('REDIS_HOST', '127.0.0.1'),   // Redis host
    // family: 4,           // 4 (IPv4) or 6 (IPv6)
    password: env('REDIS_PASSWORD', null),
    db: 0,
});

const wss = new WebSocket.Server({
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

wss.on('connection', (ws, req) => {
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
    } catch (e) {
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
                    if (wss.length === 0) {
                        delete clients[ws.uuid];
                    }
                }
            }
        }
    })
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