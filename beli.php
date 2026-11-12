<?php
require 'includes/pay_boot.php'; wajibLogin(); $u = user();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrfCek();
  $s = $pdo->prepare('SELECT * FROM paket_suara WHERE id=? AND aktif=1'); $s->execute([(int)($_POST['paket_id'] ?? 0)]);
  if ($paket = $s->fetch()) {
    $oid = buatOrder($pdo, $u, $paket);
    if (PAY_GATEWAY === 'midtrans') {
      $tok = midtransSnapToken($oid, $u, $paket);
      if (!$tok) { tandaiGagal($pdo, $oid); $_SESSION['flash'] = 'Gagal menghubungi Midtrans. Periksa Server Key.'; header('Location: beli.php'); exit; }
      $pdo->prepare('UPDATE pembayaran SET snap_token=? WHERE order_id=?')->execute([$tok, $oid]);
    }
    header('Location: bayar.php?order=' . urlencode($oid)); exit;
  }
}
$pakets = $pdo->query('SELECT * FROM paket_suara WHERE aktif=1 ORDER BY urutan,id')->fetchAll();
$flash = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']);
$pageTitle = 'Beli Suara'; require 'includes/header.php'; ?>
<div class="sec-title"><h3>Beli Paket Suara</h3></div>
<p class="text-secondary">Saldo kamu: <b class="gold"><?= (int)$u['saldo_suara'] ?></b> suara
 <?php if (PAY_GATEWAY === 'mock'): ?><span class="badge bg-warning text-dark ms-2">MODE UJI COBA</span><?php endif; ?></p>
<?php if ($flash): ?><div class="alert alert-danger"><?= e($flash) ?></div><?php endif; ?>
<div class="row g-3">
<?php foreach ($pakets as $p): ?>
  <div class="col-6 col-lg-3"><form method="post" class="cardx p-3 h-100 text-center"><?= csrfField() ?>
    <input type="hidden" name="paket_id" value="<?= $p['id'] ?>">
    <h5 class="gold"><?= e($p['nama']) ?></h5>
    <div class="display-6 fw-bold"><?= (int)$p['jumlah_suara'] ?></div><div class="small text-secondary mb-2">suara</div>
    <div class="mb-3"><?= rupiah($p['harga']) ?></div>
    <button class="btn btn-gold w-100">Beli</button>
  </form></div>
<?php endforeach; ?>
</div>
<?php require 'includes/footer.php'; ?>
