<?php
// Simulator pembayaran — HANYA aktif saat PAY_GATEWAY = 'mock'
require 'includes/pay_boot.php'; wajibLogin(); $u = user();
if (PAY_GATEWAY !== 'mock') { http_response_code(403); exit('Simulator nonaktif.'); }
$s = $pdo->prepare('SELECT * FROM pembayaran WHERE order_id=? AND pengguna_id=? AND gateway="mock"'); $s->execute([$_GET['order'] ?? '', $u['id']]);
$o = $s->fetch(); if (!$o) { http_response_code(404); exit('Pesanan tidak ditemukan.'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrfCek();
  $a = $_POST['aksi'] ?? '';
  if ($a === 'sukses') tandaiLunas($pdo, $o['order_id'], 'mock', '{"mock":true}');
  elseif ($a === 'gagal') tandaiGagal($pdo, $o['order_id'], 'failed', '{"mock":true}');
  elseif ($a === 'expired') tandaiGagal($pdo, $o['order_id'], 'expired', '{"mock":true}');
  header('Location: akun.php?order=' . urlencode($o['order_id'])); exit;
}
$pageTitle = 'Simulator Bayar'; require 'includes/header.php'; ?>
<div class="row justify-content-center"><div class="col-md-6"><div class="cardx p-4 text-center">
  <span class="badge bg-warning text-dark mb-2">SIMULATOR — tidak ada uang sungguhan</span>
  <h4 class="gold">Bayar <?= rupiah($o['nominal']) ?></h4>
  <p class="small text-secondary">Order <?= e($o['order_id']) ?> · <?= (int)$o['jumlah_suara'] ?> suara</p>
  <form method="post" class="d-grid gap-2"><?= csrfField() ?>
    <button name="aksi" value="sukses" class="btn btn-gold">✅ Simulasikan Berhasil</button>
    <button name="aksi" value="gagal" class="btn btn-outline-danger">❌ Simulasikan Gagal</button>
    <button name="aksi" value="expired" class="btn btn-outline-secondary">⌛ Simulasikan Kedaluwarsa</button>
  </form>
</div></div></div>
<?php require 'includes/footer.php'; ?>