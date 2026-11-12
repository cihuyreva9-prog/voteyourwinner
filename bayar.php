<?php
require 'includes/pay_boot.php'; wajibLogin(); $u = user();
$s = $pdo->prepare('SELECT * FROM pembayaran WHERE order_id=? AND pengguna_id=?'); $s->execute([$_GET['order'] ?? '', $u['id']]);
$o = $s->fetch(); if (!$o) { http_response_code(404); exit('Pesanan tidak ditemukan.'); }
if ($o['status'] !== 'pending') { header('Location: akun.php?order=' . urlencode($o['order_id'])); exit; }
if ($o['gateway'] === 'mock') { header('Location: mock-bayar.php?order=' . urlencode($o['order_id'])); exit; }
$pageTitle = 'Pembayaran'; require 'includes/header.php'; ?>
<div class="row justify-content-center"><div class="col-md-6"><div class="cardx p-4 text-center">
  <h4 class="gold">Selesaikan Pembayaran</h4>
  <p class="mb-1"><?= (int)$o['jumlah_suara'] ?> suara · <b><?= rupiah($o['nominal']) ?></b></p>
  <p class="small text-secondary">Order <?= e($o['order_id']) ?></p>
  <button id="pay" class="btn btn-gold btn-lg">Bayar Sekarang</button>
</div></div></div>
<script src="<?= midtransBase() ?>/snap/snap.js" data-client-key="<?= e(MIDTRANS_CLIENT_KEY) ?>"></script>
<script>
const go=()=>location.href='akun.php?order=<?= urlencode($o['order_id']) ?>';
const bayar=()=>snap.pay(<?= json_encode($o['snap_token']) ?>,{onSuccess:go,onPending:go,onError:go,onClose:()=>{}});
document.getElementById('pay').onclick=bayar; bayar();
</script>
<?php require 'includes/footer.php'; ?>
