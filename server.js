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

            let uuid = urlParams.query.uuid;

            const jwtSecret = env('JWT_SECRET');
            const algorithm = env('JWT_ALGO', 'HS256');

            if (!uuid) {
                const {authorization} = req.headers;
                const jwtString = authorization.split(' ')[1];
                const payload = jwt.verify(jwtString, jwtSecret, {algorithm});
                uuid = payload.sub;
            }

            if (!uuid) {
                console.info('无法验证令牌签名.');
                cb(false, 401, '无法验证令牌签名.');
            }

            if (!uuid instanceof Number && clients[uuid]) {
                console.info('无法验证令牌签名.');
                cb(false, 401, '无法验证令牌签名.');
            }

            cb(true);
        } catch (e) {
            console.info(e);
            cb(false, 401, 'Token could not be parsed from the request.');
        }

    },
});

const clients = {};
const clientStatistics = {
    clients: {},
    count: 0,
};

wss.on('connection', (ws, req) => {
    try {
        const urlParams = url.parse(req.url, true);

        let uuid = urlParams.query.uuid;

        const jwtSecret = env('JWT_SECRET');
        const algorithm = env('JWT_ALGO', 'HS256');

        if (!uuid) {
            const {authorization} = req.headers;
            const jwtString = authorization.split(' ')[1];
            const payload = jwt.verify(jwtString, jwtSecret, {algorithm});
            uuid = payload.sub;
        }

        if (clients[uuid] && clients[uuid].readyState === 1) {
            try {
                clients[uuid].close();
                clientStatistics.count--;
            } catch (e) {
                //
            }
        }


        console.info('[%s] connection：%s', getNowDateTimeString(), uuid);

        ws.uuid = uuid;
        clients[uuid] = ws;

        clientStatistics.clients[uuid] = getNowDateTimeString();
        clientStatistics.count++;
    } catch (e) {
        ws.close();
    }

    ws.on('message', message => { // 接收消息事件
        if (ws.uuid) {
            console.info('[%s] message：%s %s', getNowDateTimeString(), ws.uuid, message);

            if (ws.uuid === 1 && message === 'get_client_statistics') {
                ws.send(JSON.stringify(clientStatistics));
            }
        }
    });

    ws.on('close', () => { // 关闭链接事件
        if (ws.uuid) {
            console.info('[%s] closed：%s', getNowDateTimeString(), ws.uuid);
            try {
                delete clients[ws.uuid];
                delete clientStatistics.clients[ws.uuid];
                clientStatistics.count--;
            } catch (e) {
                //
            }
        }
    })

});


// redis 订阅
redis.psubscribe('*', function (err, count) {
});

redis.on('pmessage', (subscrbed, channel, message) => { // 接收 laravel 推送的消息
    console.info('[%s] %s %s', getNowDateTimeString(), channel, message);

    const {event, data} = JSON.parse(message);
    const uuid = channel.split('.')[1];
    switch (event) {
        case 'Illuminate\\Notifications\\Events\\BroadcastNotificationCreated':
            if (clients[uuid] && clients[uuid].readyState === 1) {
                clients[uuid].send(message)
            }
            break;
        case 'App\\Events\\WechatScanLogin':
            if (clients[uuid] && clients[uuid].readyState === 1) {
                clients[uuid].send(message)
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