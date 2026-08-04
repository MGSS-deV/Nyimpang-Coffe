const express = require('express');
const router = express.Router();
const productController = require('../controllers/productController');

// Definisi Routing Produk
router.get('/', productController.getAllProducts);

module.exports = router;
