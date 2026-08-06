const express = require('express');
const router = express.Router();
const financeController = require('../controllers/financeController');

router.get('/summary', financeController.getSummary);
router.post('/expenses', financeController.addExpense);
router.delete('/expenses/:id', financeController.deleteExpense);

module.exports = router;
