// ==========================================
// NYIMPANG COFFEE - ORDER CONTROLLER
// Menyimpan pesanan di memori & broadcast
// perubahan secara realtime lewat Socket.io
// ==========================================

// Data pesanan disimpan di memori (reset kalau server restart).
// Kalau nanti mau persist, tinggal ganti array ini dengan query DB.
let orders = [];

const STATUS_FLOW = ['Masuk', 'Dibuat', 'Siap Diambil', 'Selesai'];
const VALID_STATUSES = [...STATUS_FLOW, 'Dibatalkan'];

function computeStats() {
    let totalRevenue = 0;
    let pendingOrders = 0;
    let completedOrders = 0;

    orders.forEach(o => {
        if (o.status === 'Selesai') {
            totalRevenue += o.totalAmount;
            completedOrders += 1;
        } else if (o.status !== 'Dibatalkan') {
            pendingOrders += 1;
        }
    });

    return {
        totalRevenue,
        pendingOrders,
        completedOrders,
        totalOrders: orders.length
    };
}

// GET /api/orders -> daftar pesanan + statistik (dipakai saat load pertama kali)
exports.getAllOrders = async (req, res) => {
    try {
        res.json({
            success: true,
            orders,
            stats: computeStats()
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: 'Gagal mengambil data pesanan',
            error: error.message
        });
    }
};

// POST /api/orders -> dipanggil setelah pelanggan SUKSES bayar di web pelanggan.
// Pesanan langsung masuk dengan status "Masuk" dan di-broadcast ke dashboard barista.
exports.createOrder = async (req, res) => {
    try {
        const { customerName, orderType, tableNo, paymentMethod, items } = req.body;

        if (!items || !Array.isArray(items) || items.length === 0) {
            return res.status(400).json({ success: false, message: 'Keranjang kosong!' });
        }

        const totalAmount = items.reduce((sum, item) => sum + (item.price * item.qty), 0);

        const newOrder = {
            id: 'ORD-' + Date.now(),
            customerName: customerName || 'Pelanggan',
            orderType: orderType || 'Dine In',
            tableNo: tableNo || '-',
            paymentMethod: paymentMethod || 'QRIS',
            items,
            totalAmount,
            status: 'Masuk',
            createdAt: new Date().toLocaleTimeString('id-ID'),
            createdAtISO: new Date().toISOString()
        };

        orders.unshift(newOrder);

        // Broadcast realtime ke semua client yang terhubung (dashboard barista & pelanggan)
        const io = req.app.get('io');
        if (io) {
            io.emit('pesanan-baru', { order: newOrder, stats: computeStats() });
        }

        res.status(201).json({
            success: true,
            message: 'Pembayaran berhasil, pesanan dikirim ke Barista',
            order: newOrder
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: 'Gagal membuat pesanan',
            error: error.message
        });
    }
};

// PATCH /api/orders/:id -> barista menggerakkan pesanan antar status queue
// (Masuk -> Dibuat -> Siap Diambil -> Selesai), atau membatalkan.
exports.updateOrderStatus = async (req, res) => {
    try {
        const { id } = req.params;
        const { status } = req.body;

        if (!VALID_STATUSES.includes(status)) {
            return res.status(400).json({ success: false, message: 'Status tidak valid' });
        }

        const order = orders.find(o => o.id === id);
        if (!order) {
            return res.status(404).json({ success: false, message: 'Pesanan tidak ditemukan' });
        }

        order.status = status;
        order.updatedAt = new Date().toLocaleTimeString('id-ID');

        const stats = computeStats();

        const io = req.app.get('io');
        if (io) {
            io.emit('status-diperbarui', { order, stats });
        }

        res.json({ success: true, message: 'Status pesanan diperbarui', order, stats });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: 'Gagal memperbarui status pesanan',
            error: error.message
        });
    }
};

// Dipakai controller lain (finance) untuk menghitung pemasukan dari pesanan Selesai
exports.getCompletedOrders = () => orders.filter(o => o.status === 'Selesai');
