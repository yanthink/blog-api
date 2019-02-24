const WebSocket = require('ws') // socket.io 支持的协议版本（4）和 微信小程序 websocket 协议版本（13）不一致，所以选用ws
const Redis = require('ioredis')
const fs = require('fs')
const ini = require('ini')
const jwt = require('jsonwebtoken')


const config = ini.parse(fs.readFileSync('./.env', 'utf8')) // 读取.env配置

const redis = new Redis({
    port: env('REDIS_PORT', 6379),          // Redis port
    host: env('REDIS_HOST', '127.0.0.1'),   // Redis host
    // family: 4,           // 4 (IPv4) or 6 (IPv6)
    password: env('REDIS_PASSWORD', null),
    db: 0,
})

const wss = new WebSocket.Server({
    port: 6001,
    clientTracking: false,
    verifyClient ({ req }, cb) {
        try {
            const { authorization } = req.headers
            const token = authorization.split(' ')[1]
            const jwtSecret = env('JWT_SECRET')
            const algorithm = env('JWT_ALGO', 'HS256')

            const { sub, nbf, exp } = jwt.verify(token, jwtSecret, { algorithm })

            // if (Date.now() / 1000 + 30 * 60 > exp) {
            //     cb(false, 401, 'token已过期.')
            // }
            //
            // if (Date.now() /1000 < nbf) {
            //     cb(false, 401, 'token在（nbf）时间之前不能被接收处理.')
            // }

            if (!(sub > 0)) {
                console.info('无法验证令牌签名.')
                cb(false, 401, '无法验证令牌签名.')
            }

            cb(true)
        } catch (e) {
            console.info(e)
            cb(false, 401, 'Token could not be parsed from the request.')
        }

    },
})

const clients = {}
const clientStatistics = {
    clients: {},
    count: 0,
}
wss.on('connection', (ws, req) => {
    try {
        const { authorization } = req.headers
        const token = authorization.split(' ')[1]
        const jwtSecret = env('JWT_SECRET')
        const algorithm = env('JWT_ALGO', 'HS256')

        const { sub } = jwt.verify(token, jwtSecret, {algorithm})

        if (clients[sub] && clients[sub].readyState === 1) {
            try {
                clients[sub].close()
                clientStatistics.count--
            } catch (e) {
                //
            }
        }

        console.info('[%s] connection：%s', getNowDateTimeString(), sub)

        ws.user_id = sub
        clients[sub] = ws


        clientStatistics.clients[sub] = getNowDateTimeString()
        clientStatistics.count ++

        // redis.set('socket_clients', JSON.stringify(redisStorage.clients)) // 订阅模式不能用
        // redis.set('socket_count', redisStorage.count)
    } catch (e) {
        ws.close()
    }

    ws.on('message', message => { // 接收消息事件
        if (ws.user_id) {
            console.info('[%s] message：%s %s', getNowDateTimeString(), ws.user_id, message)

            if (ws.user_id === 1 && message === 'get_client_statistics') {
                ws.send(JSON.stringify(clientStatistics))
            }
        }
    }) // 当收到消息时，在控制台打印出来，并回复一条信息

    ws.on('close', () => { // 关闭链接事件
        if (ws.user_id) {
            console.info('[%s] closed：%s', getNowDateTimeString(), ws.user_id)
            try {
                delete clients[ws.user_id]
                delete clientStatistics.clients[ws.user_id]
                clientStatistics.count--
            } catch (e) {
                //
            }

            // redis.set('socket_clients', JSON.stringify(redisStorage.clients))
            // redis.set('socket_count', redisStorage.count)
        }
    })

})


// redis 订阅
redis.psubscribe('*', function (err, count) {
})

redis.on('pmessage', (subscrbed, channel, message) => { // 接收 laravel 推送的消息
    console.info('[%s] %s %s', getNowDateTimeString(), channel, message)

    const { event, data } = JSON.parse(message)
    switch (event) {
        case 'Illuminate\\Notifications\\Events\\BroadcastNotificationCreated':
            const userId = channel.split('.')[1]
            if (clients[userId] && clients[userId].readyState === 1) {
                clients[userId].send(message)
            }
        break
    }
})

function env(key, def = '') {
    return config[key] || def
}

function getNowDateTimeString () {
    const date = new Date()
    return `${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()} ${date.getHours()}:${date.getMinutes()}:${date.getSeconds()}`
}