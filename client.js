let io = require('socket.io-client')

var socket = io('wss://api.blog.test/wss')
console.info(socket)
socket.on('xcx:wechat.login', (data) => {
    socket.close()
    // save user token and redirect to dashboard
})