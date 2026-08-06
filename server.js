require('dotenv').config();

const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const path = require('path');
const session = require('express-session');

const orderRoutes = require('./routes/orderRoutes');
const productRoutes = require('./routes/productRoutes');
const financeRoutes = require('./routes/financeRoutes');
const authRoutes = require('./routes/authRoutes');
const requireAuth = require('./middleware/requireAuth');

const app = express();
const server = http.createServer(app);
const io = new Server(server);

app.use(express.json());

// Sesi login staff (dipakai untuk proteksi dashboard barista & keuangan).
// SESSION_SECRET sebaiknya diisi di .env untuk produksi.
app.use(session({
    secret: process.env.SESSION_SECRET || 'nyimpang-coffee-dev-secret-ganti-ini',
    resave: false,
    saveUninitialized: false,
    cookie: {
        maxAge: 8 * 60 * 60 * 1000, // 8 jam, cukup untuk satu shift
        httpOnly: true
    }
}));

// Simpan instance io di app supaya bisa dipakai controller
// untuk broadcast realtime (req.app.get('io'))
app.set('io', io);

// Halaman terproteksi: harus login dulu baru bisa dibuka.
// Diletakkan SEBELUM express.static supaya bisa dicegat sebelum ke-serve gratis.
app.get('/bar.html', requireAuth, (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'bar.html'));
});
app.get('/keuangan.html', requireAuth, (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'keuangan.html'));
});

app.use(express.static(path.join(__dirname, 'public')));

// Routing API
app.use('/api/auth', authRoutes);
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
