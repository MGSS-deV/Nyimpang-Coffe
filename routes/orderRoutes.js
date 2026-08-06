const express = require('express');
const router = express.Router();
const orderController = require('../controllers/orderController');
const requireAuth = require('../middleware/requireAuth');

// Definisi Routing Order
// GET & PATCH dikunci hanya untuk staff yang sudah login (dipakai dashboard barista).
// POST tetap publik karena dipanggil dari halaman pelanggan saat checkout.
router.get('/', requireAuth, orderController.getAllOrders);
router.post('/', orderController.createOrder);
router.patch('/:id', requireAuth, orderController.updateOrderStatus);

module.exports = router;
