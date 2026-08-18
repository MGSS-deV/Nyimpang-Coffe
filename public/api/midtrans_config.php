<?php
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/midtrans.php';

jsonResponse([
    'success' => true,
    'configured' => isMidtransConfigured(),
    'clientKey' => getenv('MIDTRANS_CLIENT_KEY') ?: '',
    'isProduction' => getenv('MIDTRANS_IS_PRODUCTION') === 'true'
]);
