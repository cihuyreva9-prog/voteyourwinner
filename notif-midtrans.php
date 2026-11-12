<?php
// Daftarkan URL ini di Midtrans Dashboard > Settings > Configuration > Payment Notification URL
require __DIR__ . '/includes/config-pay.php';
if (!isset($pdo)) require_once __DIR__ . '/includes/config.php';   // sesuaikan seperti pay_boot.php
require __DIR__ . '/includes/pay.php';
$d = json_decode(file_get_contents('php://input'), true) ?: [];
$oid = $d['order_id'] ?? '';
$sig = hash('sha512', $oid . ($d['status_code'] ?? '') . ($d['gross_amount'] ?? '') . MIDTRANS_SERVER_KEY);
if (!$oid || !hash_equals($sig, $d['signature_key'] ?? '')) { http_response_code(403); exit('invalid signature'); }
$s = $pdo->prepare('SELECT nominal FROM pembayaran WHERE order_id=?'); $s->execute([$oid]);
$p = $s->fetch(); if (!$p) { http_response_code(404); exit('order not found'); }
if ((int)round((float)$d['gross_amount']) !== (int)$p['nominal']) { http_response_code(400); exit('amount mismatch'); }
terapkanStatusMidtrans($pdo, $oid, $d);
echo 'OK';
