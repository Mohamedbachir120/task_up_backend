const express = require('express')
const app = express()

const server = require('http').createServer(app)


const io = require('socket.io')(server,{
    cors:{origin:'http://localhost:5173'}
})

io.on('connection',(socket)=>{
    console.log('connection');
    socket.on('sendNotificationToUser',(obj)=> {
        console.log(obj);
        socket.broadcast.emit('receiveNotificationToUser'+obj.user,obj.message)
    })
    socket.on('disconnect',(socket)=>{

    })
})

const port = 3000

server.listen(port, ()=>{
        console.log('server is running on port'+port)
})