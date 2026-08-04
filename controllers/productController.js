// Data produk disimpan di memori dulu — di sinilah kamu nambah/ubah/hapus menu.
// Nanti kalau mau pindah ke database, tinggal ganti array ini
// dengan query ke db.js dan bentuk datanya dibuat sama persis.
const products = [
    {
        id: 1,
        name: 'Kopi Susu Gula Aren',
        description: 'Espresso, susu segar, gula aren',
        price: 18000,
        category: 'Kopi',
        icon: '☕'
    },
    {
        id: 2,
        name: 'Americano Hot/Ice',
        description: 'Double shot, dingin atau panas',
        price: 15000,
        category: 'Kopi',
        icon: '🧊'
    },
    {
        id: 3,
        name: 'Matcha Latte',
        description: 'Green tea premium, susu lembut',
        price: 22000,
        category: 'Non-Kopi',
        icon: '🥛'
    },
    {
        id: 4,
        name: 'Dark Chocolate',
        description: 'Cokelat pekat, rasa rich',
        price: 20000,
        category: 'Non-Kopi',
        icon: '🍫'
    }
];

// GET /api/products -> daftar menu, dipakai halaman pelanggan untuk render menu
exports.getAllProducts = async (req, res) => {
    try {
        res.json({ success: true, products });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: 'Gagal mengambil data produk',
            error: error.message
        });
    }
};