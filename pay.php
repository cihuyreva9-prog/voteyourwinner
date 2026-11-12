<?php
function midtransBase($api = false){
  $sb = !MIDTRANS_PRODUCTION;
  return $api ? ($sb ? 'https://api.sandbox.midtrans.com' : 'https://api.midtrans.com')
              : ($sb ? 'https://app.sandbox.midtrans.com' : 'https://app.midtrans.com');
}
function midtransReq($method, $url, $body = null){
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 25, CURLOPT_CUSTOMREQUEST => $method,
    CURLOPT_HTTPHEADER => ['Accept: application/json', 'Content-Type: application/json',
      'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':')],
  ]);
  if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
  $res = curl_exec($ch); $err = curl_error($ch); curl_close($ch);
  if ($res === false) return [0, ['error' => $err]];
  return [0, json_decode($res, true) ?: []];
}

function buatOrder($pdo, $u, $paket){
  $oid = 'VOTE-' . date('ymdHis') . '-' . strtoupper(bin2hex(random_bytes(3)));
  $pdo->prepare('INSERT INTO pembayaran (order_id,pengguna_id,paket_id,jumlah_suara,nominal,gateway) VALUES (?,?,?,?,?,?)')
      ->execute([$oid, $u['id'], $paket['id'], $paket['jumlah_suara'], $paket['harga'], PAY_GATEWAY]);
  return $oid;
}

function midtransSnapToken($oid, $u, $paket){
  [, $r] = midtransReq('POST', midtransBase() . '/snap/v1/transactions', [
    'transaction_details' => ['order_id' => $oid, 'gross_amount' => (int)$paket['harga']],
    'item_details' => [['id' => 'P' . $paket['id'], 'price' => (int)$paket['harga'], 'quantity' => 1, 'name' => substr($paket['nama'] . ' (' . $paket['jumlah_suara'] . ' suara)', 0, 50)]],
    'customer_details' => ['first_name' => $u['nama'], 'email' => $u['email'], 'phone' => $u['hp'] ?? ''],
  ]);
  return $r['token'] ?? null;
}

// Idempotent: aman dipanggil berkali-kali (webhook + sinkron manual)
function tandaiLunas($pdo, $oid, $tipe = null, $raw = null){
  $pdo->beginTransaction();
  $s = $pdo->prepare('SELECT * FROM pembayaran WHERE order_id=? FOR UPDATE'); $s->execute([$oid]);
  $p = $s->fetch();
  if (!$p || $p['status'] === 'paid') { $pdo->commit(); return false; }
  $pdo->prepare("UPDATE pembayaran SET status='paid', tipe_bayar=?, raw_notif=?, dibayar=NOW() WHERE id=?")->execute([$tipe, $raw, $p['id']]);
  $pdo->prepare('UPDATE pengguna SET saldo_suara = saldo_suara + ? WHERE id=?')->execute([$p['jumlah_suara'], $p['pengguna_id']]);
  $pdo->prepare('INSERT INTO mutasi_suara (pengguna_id,jumlah,keterangan,ref) VALUES (?,?,?,?)')
      ->execute([$p['pengguna_id'], $p['jumlah_suara'], 'Pembelian paket suara', $oid]);
  $pdo->commit(); return true;
}
function tandaiGagal($pdo, $oid, $status = 'failed', $raw = null){
  $pdo->prepare("UPDATE pembayaran SET status=?, raw_notif=? WHERE order_id=? AND status='pending'")->execute([$status, $raw, $oid]);
}

// Terapkan data status Midtrans (dari webhook ATAU cek status)
function terapkanStatusMidtrans($pdo, $oid, $d){
  $ts = $d['transaction_status'] ?? ''; $fraud = $d['fraud_status'] ?? 'accept'; $raw = json_encode($d);
  if ($ts === 'settlement' || ($ts === 'capture' && $fraud === 'accept')) tandaiLunas($pdo, $oid, $d['payment_type'] ?? null, $raw);
  elseif ($ts === 'expire') tandaiGagal($pdo, $oid, 'expired', $raw);
  elseif (in_array($ts, ['deny', 'cancel', 'failure'], true)) tandaiGagal($pdo, $oid, 'failed', $raw);
}
// Untuk localhost (webhook tak bisa menjangkau): tarik status langsung dari Midtrans
function sinkronMidtrans($pdo, $oid){
  [, $d] = midtransReq('GET', midtransBase(true) . '/v2/' . rawurlencode($oid) . '/status');
  if (!empty($d['transaction_status'])) terapkanStatusMidtrans($pdo, $oid, $d);
}