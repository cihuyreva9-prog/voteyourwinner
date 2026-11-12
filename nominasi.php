<?php $pageTitle = 'Nominasi'; require 'includes/header.php';
$kats = $pdo->query('SELECT * FROM kategori WHERE aktif=1 ORDER BY urutan,id')->fetchAll();
$kid = (int)($_GET['kategori'] ?? ($kats[0]['id'] ?? 0));
$q = trim($_GET['q'] ?? '');
$d = peringkat($kid); $hot = hotId($kid); $buka = votingBuka();
$list = array_filter($d['rows'], fn($r) => $q === '' || stripos($r['nama'] . ' ' . $r['klub'], $q) !== false);
?>
<div class="sec-title mt-2"><h3>Daftar Nominasi</h3></div>
<ul class="nav nav-pills mb-3">
<?php foreach ($kats as $i => $k): ?>
  <li class="nav-item"><a class="nav-link <?= $k['id'] == $kid ? 'active' : '' ?>" href="?kategori=<?= $k['id'] ?>"><?= ikonKategori($i) ?> <?= e($k['nama']) ?></a></li>
<?php endforeach; ?>
</ul>
<form class="row g-2 mb-4" method="get"><input type="hidden" name="kategori" value="<?= $kid ?>">
  <div class="col"><input class="form-control" name="q" value="<?= e($q) ?>" placeholder="🔍 Cari nama pemain atau klub..."></div>
  <div class="col-auto"><button class="btn btn-gold px-4">Cari</button></div>
  <div class="col-auto"><a class="btn btn-outline-gold" href="bandingkan.php?kategori=<?= $kid ?>">⚔ Bandingkan</a></div></form>
<?php if (!$buka): ?><div class="alert alert-warning">Voting sedang ditutup. Kamu tetap bisa melihat profil nominasi.</div><?php endif; ?>
<div class="row g-3">
<?php foreach ($list as $n): ?>
  <div class="col-6 col-md-4 col-lg-3 rv"><div class="ncard tilt">
    <span class="nomor"><?= e($n['nomor']) ?></span>
    <?php if ($n['id'] == $hot): ?><span class="hot">🔥 Trending</span><?php endif; ?>
    <a class="pic" href="detail.php?id=<?= $n['id'] ?>"><?= avatar($n) ?><span class="rk">Peringkat <?= $n['rank'] ?> · <?= $n['persen'] ?>%</span></a>
    <div class="info">
      <h6 class="mb-0 fw-bold"><?= e($n['nama']) ?></h6>
      <div class="small text-secondary"><?= e($n['klub']) ?></div>
      <div class="small my-2"><?= e($n['statistik']) ?></div>
      <div class="bar mb-1"><i style="width:<?= $n['persen'] ?>%"></i></div>
      <div class="small text-secondary mb-3"><b class="gold"><?= $n['total'] ?></b> suara</div>
      <div class="mt-auto d-grid gap-2">
        <a class="btn btn-sm btn-outline-gold" href="detail.php?id=<?= $n['id'] ?>">Lihat Detail</a>
        <?php if ($buka): ?><button class="btn btn-sm btn-gold" data-vote data-id="<?= $n['id'] ?>" data-nama="<?= e($n['nama']) ?>" data-klub="<?= e($n['klub']) ?>">🏆 Beri Suara</button><?php endif; ?>
      </div></div></div></div>
<?php endforeach; if (!$list) echo '<p class="text-secondary">Tidak ada nominasi ditemukan.</p>'; ?>
</div>
<?php require 'includes/footer.php'; ?>
