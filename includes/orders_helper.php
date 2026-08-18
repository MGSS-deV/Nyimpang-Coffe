<?php
// ==========================================
// HELPER PESANAN — dipakai bareng oleh orders_*.php & finance_summary.php
// ==========================================

// Ubah 1 baris hasil query jadi bentuk yang dipakai frontend (camelCase, items di-decode)
function formatOrderRow($row)
{
    return [
        'id' => $row['id'],
        'customerName' => $row['customer_name'],
        'customerPhone' => $row['customer_phone'] ?? null,
        'orderType' => $row['order_type'],
        'tableNo' => $row['table_no'],
        'paymentMethod' => $row['payment_method'],
        'items' => json_decode($row['items'], true),
        'totalAmount' => (int) $row['total_amount'],
        'voucherCode' => $row['voucher_code'] ?? null,
        'discountAmount' => (int) ($row['discount_amount'] ?? 0),
        'status' => $row['status'],
        'createdAt' => date('H.i.s', strtotime($row['created_at'])),
        'updatedAt' => $row['updated_at'] ? date('H.i.s', strtotime($row['updated_at'])) : null
    ];
}

// Sama seperti formatOrderRow, tapi createdAt/updatedAt pakai tanggal LENGKAP
// (bukan cuma jam) — dipakai halaman Riwayat yang nampilin data lintas hari.
function formatOrderRowFull($row)
{
    return [
        'id' => $row['id'],
        'customerName' => $row['customer_name'],
        'customerPhone' => $row['customer_phone'] ?? null,
        'orderType' => $row['order_type'],
        'tableNo' => $row['table_no'],
        'paymentMethod' => $row['payment_method'],
        'items' => json_decode($row['items'], true),
        'totalAmount' => (int) $row['total_amount'],
        'voucherCode' => $row['voucher_code'] ?? null,
        'discountAmount' => (int) ($row['discount_amount'] ?? 0),
        'status' => $row['status'],
        'createdAt' => date('d/m/Y H.i', strtotime($row['created_at'])),
        'updatedAt' => $row['updated_at'] ? date('d/m/Y H.i', strtotime($row['updated_at'])) : null
    ];
}

function computeOrderStats($pdo)
{
    $totalRevenue = (int) $pdo->query(
        "SELECT COALESCE(SUM(total_amount), 0) AS total FROM orders WHERE status = 'Selesai'"
    )->fetch()['total'];

    $pendingOrders = (int) $pdo->query(
        "SELECT COUNT(*) AS total FROM orders WHERE status NOT IN ('Selesai', 'Dibatalkan')"
    )->fetch()['total'];

    $completedOrders = (int) $pdo->query(
        "SELECT COUNT(*) AS total FROM orders WHERE status = 'Selesai'"
    )->fetch()['total'];

    $totalOrders = (int) $pdo->query("SELECT COUNT(*) AS total FROM orders")->fetch()['total'];

    return [
        'totalRevenue' => $totalRevenue,
        'pendingOrders' => $pendingOrders,
        'completedOrders' => $completedOrders,
        'totalOrders' => $totalOrders
    ];
}

function generateOrderId()
{
    // bin2hex(random_bytes(4)) = 8 karakter hex acak, praktis nggak mungkin
    // tabrakan meskipun banyak pesanan masuk di detik yang sama.
    return 'ORD-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
}

// ==========================================
// SIMPAN PESANAN (dipakai bareng checkout manual & Midtrans)
// $verifiedItems & $totalAmount WAJIB udah divalidasi/dihitung ulang dari
// DB sebelum manggil ini (jangan percaya angka dari client).
// ==========================================
function persistOrder($pdo, $verifiedItems, $totalAmount, $meta)
{
    require_once __DIR__ . '/whatsapp.php';

    $pdo->beginTransaction();

    try {
        $neededPerIngredient = [];
        $recipeStmt = $pdo->prepare(
            "SELECT ingredient_id, qty_per_serving FROM product_ingredients WHERE product_id = :product_id"
        );

        foreach ($verifiedItems as $item) {
            $recipeStmt->execute(['product_id' => $item['id']]);
            foreach ($recipeStmt->fetchAll() as $req) {
                $ingId = (int) $req['ingredient_id'];
                $needed = (float) $req['qty_per_serving'] * $item['qty'];
                $neededPerIngredient[$ingId] = ($neededPerIngredient[$ingId] ?? 0) + $needed;
            }
        }

        if (!empty($neededPerIngredient)) {
            $lockStmt = $pdo->prepare("SELECT id, name, unit, stock_qty FROM ingredients WHERE id = :id FOR UPDATE");
            foreach ($neededPerIngredient as $ingId => $neededQty) {
                $lockStmt->execute(['id' => $ingId]);
                $ingredient = $lockStmt->fetch();

                if (!$ingredient || (float) $ingredient['stock_qty'] < $neededQty) {
                    $pdo->rollBack();
                    $name = $ingredient['name'] ?? 'bahan baku';
                    return ['success' => false, 'message' => "Maaf, stok {$name} nggak cukup. Coba pesan menu lain ya."];
                }
            }

            $deductStmt = $pdo->prepare("UPDATE ingredients SET stock_qty = stock_qty - :qty WHERE id = :id");
            foreach ($neededPerIngredient as $ingId => $neededQty) {
                $deductStmt->execute(['qty' => $neededQty, 'id' => $ingId]);
            }
        }

        $insertStmt = $pdo->prepare(
            "INSERT INTO orders (id, customer_name, customer_phone, order_type, table_no, payment_method, items, total_amount, voucher_code, discount_amount, status)
             VALUES (:id, :customer_name, :customer_phone, :order_type, :table_no, :payment_method, :items, :total_amount, :voucher_code, :discount_amount, 'Masuk')"
        );

        $id = null;
        $maxAttempts = 3;
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $id = generateOrderId();
            try {
                $insertStmt->execute([
                    'id' => $id,
                    'customer_name' => $meta['customerName'] ?: 'Pelanggan',
                    'customer_phone' => $meta['customerPhone'] ?: null,
                    'order_type' => $meta['orderType'] ?: 'Dine In',
                    'table_no' => $meta['tableNo'] ?: '-',
                    'payment_method' => $meta['paymentMethod'] ?: 'QRIS',
                    'items' => json_encode($verifiedItems),
                    'total_amount' => $totalAmount,
                    'voucher_code' => $meta['voucherCode'] ?: null,
                    'discount_amount' => $meta['discountAmount'] ?? 0
                ]);
                break;
            } catch (PDOException $e) {
                if ($e->getCode() !== '23000' || $attempt === $maxAttempts - 1) {
                    throw $e;
                }
            }
        }

        if (!empty($meta['voucherCode'])) {
            $pdo->prepare("UPDATE vouchers SET used_count = used_count + 1 WHERE code = :code")
                ->execute(['code' => $meta['voucherCode']]);
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Gagal menyimpan pesanan, coba lagi'];
    }

    $row = $pdo->prepare("SELECT * FROM orders WHERE id = :id");
    $row->execute(['id' => $id]);
    $order = formatOrderRow($row->fetch());

    sendWhatsAppNotification(formatOrderWhatsAppMessage($order));

    if (!empty($neededPerIngredient)) {
        $ids = implode(',', array_map('intval', array_keys($neededPerIngredient)));
        $lowStockRows = $pdo->query(
            "SELECT name, unit, stock_qty, low_stock_threshold FROM ingredients
             WHERE id IN ({$ids}) AND stock_qty <= low_stock_threshold"
        )->fetchAll();

        if (!empty($lowStockRows)) {
            sendWhatsAppNotification(formatLowStockWhatsAppMessage($lowStockRows));
        }
    }

    return ['success' => true, 'order' => $order];
}

// Validasi item dari client & hitung ulang harga dari DB (dipakai bareng
// checkout manual & Midtrans, biar logic anti-manipulasi harga konsisten).
function verifyAndPriceItems($pdo, $rawItems)
{
    if (empty($rawItems) || !is_array($rawItems)) {
        return ['success' => false, 'message' => 'Keranjang kosong!'];
    }

    $productStmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id = :id AND is_active = 1");
    $verifiedItems = [];
    $totalAmount = 0;

    foreach ($rawItems as $rawItem) {
        $productId = (int) ($rawItem['id'] ?? 0);
        $qty = (int) ($rawItem['qty'] ?? 0);

        if ($productId <= 0 || $qty <= 0 || $qty > 50) {
            return ['success' => false, 'message' => 'Item pesanan tidak valid'];
        }

        $productStmt->execute(['id' => $productId]);
        $product = $productStmt->fetch();

        if (!$product) {
            return ['success' => false, 'message' => 'Produk tidak ditemukan atau sudah tidak tersedia'];
        }

        $verifiedItems[] = [
            'id' => (int) $product['id'],
            'name' => $product['name'],
            'price' => (int) $product['price'],
            'qty' => $qty
        ];
        $totalAmount += $product['price'] * $qty;
    }

    return ['success' => true, 'items' => $verifiedItems, 'totalAmount' => $totalAmount];
}
