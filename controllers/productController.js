exports.getAllProducts = async (req, res) => {
    try {
        // Data dummy produk
        const products = [
            { id: 1, name: 'Kopi Susu Gula Aren', price: 18000, category: 'Kopi', image_url: '' },
            { id: 2, name: 'Americano Hot/Ice', price: 15000, category: 'Kopi', image_url: '' },
            { id: 3, name: 'Matcha Latte', price: 22000, category: 'Non-Kopi', image_url: '' },
            { id: 4, name: 'Dark Chocolate', price: 20000, category: 'Non-Kopi', image_url: '' }
        ];

        res.json({ success: true, products });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: 'Gagal mengambil data produk',
            error: error.message
        });
    }
};
