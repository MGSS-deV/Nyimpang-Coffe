<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';
requireRoleApi(['Admin']);

$rows = $pdo->query("SELECT * FROM shifts ORDER BY clock_in DESC LIMIT 100")->fetchAll();
$shifts = array_map(function ($r) {
    $durationMin = $r['clock_out'] ? round((strtotime($r['clock_out']) - strtotime($r['clock_in'])) / 60) : null;
    return [
        'id' => (int) $r['id'],
        'staffUsername' => $r['staff_username'],
        'clockIn' => date('d/m/Y H.i', strtotime($r['clock_in'])),
        'clockOut' => $r['clock_out'] ? date('d/m/Y H.i', strtotime($r['clock_out'])) : null,
        'durationMin' => $durationMin
    ];
}, $rows);

jsonResponse(['success' => true, 'shifts' => $shifts]);
