const express = require('express');
const router = express.Router();
const financeController = require('../controllers/financeController');
const requireAuth = require('../middleware/requireAuth');

// Seluruh endpoint keuangan hanya untuk staff yang sudah login
router.get('/summary', requireAuth, financeController.getSummary);
router.post('/expenses', requireAuth, financeController.addExpense);
router.delete('/expenses/:id', requireAuth, financeController.deleteExpense);

module.exports = router;
