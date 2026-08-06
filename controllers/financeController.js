// ==========================================
// NYIMPANG COFFEE - FINANCE CONTROLLER
// Pemasukan diambil otomatis dari pesanan berstatus "Selesai".
// Pengeluaran dicatat manual lewat form di halaman keuangan.
// ==========================================

const orderController = require('./orderController');

// Data pengeluaran disimpan di memori (reset kalau server restart,
// sama seperti data pesanan).
let expenses = [];

const CATEGORIES = ['Bahan Baku', 'Operasional', 'Gaji', 'Lainnya'];

// GET /api/finance/summary -> ringkasan pemasukan, pengeluaran, dan laba bersih
exports.getSummary = async (req, res) => {
    try {
        const completedOrders = orderController.getCompletedOrders();

        const incomeEntries = completedOrders.map(o => ({
            id: o.id,
            description: `Pesanan ${o.customerName} — ${o.items.map(i => i.name).join(', ')}`,
            amount: o.totalAmount,
            createdAt: o.createdAt
        })).reverse();

        const totalIncome = incomeEntries.reduce((sum, e) => sum + e.amount, 0);
        const totalExpense = expenses.reduce((sum, e) => sum + e.amount, 0);

        res.json({
            success: true,
            totalIncome,
            totalExpense,
            netProfit: totalIncome - totalExpense,
            incomeEntries,
            expenseEntries: expenses,
            categories: CATEGORIES
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: 'Gagal mengambil ringkasan keuangan',
            error: error.message
        });
    }
};

// POST /api/finance/expenses -> catat pengeluaran baru
exports.addExpense = async (req, res) => {
    try {
        const { description, amount, category } = req.body;
        const numericAmount = Number(amount);

        if (!description || !numericAmount || numericAmount <= 0) {
            return res.status(400).json({ success: false, message: 'Deskripsi dan nominal wajib diisi dengan benar' });
        }

        const newExpense = {
            id: 'EXP-' + Date.now(),
            description,
            amount: numericAmount,
            category: CATEGORIES.includes(category) ? category : 'Lainnya',
            createdAt: new Date().toLocaleString('id-ID')
        };

        expenses.unshift(newExpense);

        const io = req.app.get('io');
        if (io) io.emit('keuangan-diperbarui');

        res.status(201).json({ success: true, message: 'Pengeluaran dicatat', expense: newExpense });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: 'Gagal mencatat pengeluaran',
            error: error.message
        });
    }
};

// DELETE /api/finance/expenses/:id -> hapus catatan pengeluaran (misal salah input)
exports.deleteExpense = async (req, res) => {
    try {
        const { id } = req.params;
        const idx = expenses.findIndex(e => e.id === id);

        if (idx === -1) {
            return res.status(404).json({ success: false, message: 'Data pengeluaran tidak ditemukan' });
        }

        expenses.splice(idx, 1);

        const io = req.app.get('io');
        if (io) io.emit('keuangan-diperbarui');

        res.json({ success: true, message: 'Pengeluaran dihapus' });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: 'Gagal menghapus pengeluaran',
            error: error.message
        });
    }
};
