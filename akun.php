<?php
require 'includes/pay_boot.php'; wajibLogin(); $u = user();
// Sinkron pesanan pending (berguna di localhost tanpa webhook)
if (PAY_GATEWAY === 'midtrans') {
  $q = $pdo->prepare("SELECT order_id FROM pembayaran WHERE pengguna_id=? AND status='pending' AND snap_token IS NOT NULL ORDER BY id DESC LIMIT 5");
  $q->execute([$u['id']]);
  foreach ($q->fetchAll(PDO::FETCH_COLUMN) as $oid) sinkronMidtrans($pdo, $oid);
  $u = $pdo->query('SELECT * FROM pengguna WHERE id=' . (int)$u['id'])->fetch();
}
$h = $pdo->prepare('SELECT * FROM pembayaran WHERE pengguna_id=? ORDER BY id DESC LIMIT 20'); $h->execute([$u['id']]); $riw = $h->fetchAll();
$m = $pdo->prepare('SELECT * FROM mutasi_suara WHERE pengguna_id=? ORDER BY id DESC LIMIT 20'); $m->execute([$u['id']]); $mut = $m->fetchAll();
$badge = ['pending' => 'warning text-dark', 'paid' => 'success', 'failed' => 'danger', 'expired' => 'secondary'];
$pageTitle = 'Akun Saya'; require 'includes/header.php'; ?>
<div class="sec-title"><h3>Halo, <?= e($u['nama']) ?></h3></div>
<div class="row g-3 mb-3">
  <div class="col-md-4"><div class="cardx p-3 text-center"><div class="small text-secondary">Saldo Suara</div><div class="display-5 gold fw-bold"><?= (int)$u['saldo_suara'] ?></div>
    <a href="beli.php" class="btn btn-gold btn-sm mt-2">+ Beli Suara</a> <a href="logout.php" class="btn btn-outline-secondary btn-sm mt-2">Keluar</a></div></div>
  <div class="col-md-8"><div class="cardx p-3"><h6 class="gold">Riwayat Pembayaran</h6>
    <div class="table-responsive"><table class="table table-sm align-middle mb-0">
      <?php foreach ($riw as $r): ?><tr>
        <td class="small"><?= e($r['order_id']) ?></td><td><?= (int)$r['jumlah_suara'] ?> suara</td><td><?= rupiah($r['nominal']) ?></td>
        <td><span class="badge bg-<?= $badge[$r['status']] ?>"><?= e($r['status']) ?></span>
          <?php if ($r['status'] === 'pending'): ?> <a class="small gold" href="bayar.php?order=<?= urlencode($r['order_id']) ?>">bayar</a><?php endif; ?></td>
      </tr><?php endforeach; if (!$riw) echo '<tr><td class="text-secondary small">Belum ada transaksi.</td></tr>'; ?>
    </table></div></div></div>
</div>
<div class="cardx p-3"><h6 class="gold">Mutasi Suara</h6><table class="table table-sm mb-0">
  <?php foreach ($mut as $x): ?><tr><td class="small"><?= e($x['waktu']) ?></td><td><?= e($x['keterangan']) ?></td>
    <td class="text-end <?= $x['jumlah'] > 0 ? 'text-success' : 'text-danger' ?>"><?= $x['jumlah'] > 0 ? '+' : '' ?><?= (int)$x['jumlah'] ?></td></tr><?php endforeach;
  if (!$mut) echo '<tr><td class="text-secondary small">Belum ada mutasi.</td></tr>'; ?></table></div>
<?php require 'includes/footer.php'; ?>
