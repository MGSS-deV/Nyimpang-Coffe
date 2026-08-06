require('dotenv').config();

const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const path = require('path');

const orderRoutes = require('./routes/orderRoutes');
const productRoutes = require('./routes/productRoutes');
const financeRoutes = require('./routes/financeRoutes');

const app = express();
const server = http.createServer(app);
const io = new Server(server);

app.use(express.json());
app.use(express.static(path.join(__dirname, 'public')));

// Simpan instance io di app supaya bisa dipakai controller
// untuk broadcast realtime (req.app.get('io'))
app.set('io', io);

// Routing API
app.use('/api/orders', orderRoutes);
app.use('/api/products', productRoutes);
app.use('/api/finance', financeRoutes);

// Log koneksi socket (opsional, membantu saat debugging realtime)
io.on('connection', (socket) => {
    console.log(`🔌 Client terhubung: ${socket.id}`);

    socket.on('disconnect', () => {
        console.log(`❌ Client terputus: ${socket.id}`);
    });
});

// Jalankan Server di Port 3000
const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
    console.log(`🚀 Server Nyimpang Coffee aktif di http://localhost:${PORT}`);
});
