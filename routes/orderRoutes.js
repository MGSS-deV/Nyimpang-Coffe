const express = require('express');
const router = express.Router();
const orderController = require('../controllers/orderController');

// Definisi Routing Order
router.get('/', orderController.getAllOrders);
router.post('/', orderController.createOrder);
router.patch('/:id', orderController.updateOrderStatus);

module.exports = router;
