<?php $pageTitle = 'Detail Nominasi'; require 'includes/header.php';
$st = $pdo->prepare('SELECT n.*, k.nama AS kategori FROM nominasi n JOIN kategori k ON k.id=n.kategori_id WHERE n.id=? AND n.aktif=1');
$st->execute([(int)($_GET['id'] ?? 0)]); $n = $st->fetch();
if (!$n) { echo '<div class="alert alert-danger">Nominasi tidak ditemukan.</div>'; require 'includes/footer.php'; exit; }
$d = peringkat($n['kategori_id']); $me = null;
foreach ($d['rows'] as $r) if ($r['id'] == $n['id']) $me = $r;
$embed = null;
if ($n['video_url'] && preg_match('~(?:youtu\.be/|v=)([\w-]{11})~', $n['video_url'], $m)) $embed = 'https://www.youtube.com/embed/' . $m[1];
$p = periode();
$tr = $pdo->prepare('SELECT DATE(waktu) d, COUNT(*) t FROM suara WHERE nominasi_id=? AND periode_id=? AND waktu >= CURDATE() - INTERVAL 6 DAY GROUP BY d');
$tr->execute([$n['id'], $p['id'] ?? 0]); $map = array_column($tr->fetchAll(), 't', 'd');
$hari = []; for ($i = 6; $i >= 0; $i--) { $k = date('Y-m-d', strtotime("-$i day")); $hari[] = [date('d/m', strtotime($k)), (int)($map[$k] ?? 0)]; }
$mx = max(1, max(array_column($hari, 1)));
$ps = $pdo->prepare('SELECT nama,isi,waktu FROM pesan WHERE nominasi_id=? AND tampil=1 ORDER BY id DESC LIMIT 15'); $ps->execute([$n['id']]); $pesan = $ps->fetchAll();
$lain = array_filter($d['rows'], fn($r) => $r['id'] != $n['id']);
$link = baseUrl() . '/detail.php?id=' . $n['id'];
$wa = 'https://wa.me/?text=' . rawurlencode('Dukung ' . $n['nama'] . ' di ' . setting('judul') . '! ' . $link);
?>
<a href="nominasi.php?kategori=<?= $n['kategori_id'] ?>" class="text-decoration-none gold">&larr; Kembali ke nominasi</a>
<div class="row g-4 mt-1">
  <div class="col-md-4"><div class="ncard tilt <?= $me && $me['rank'] == 1 ? 'winner-glow' : '' ?>">
    <span class="nomor"><?= e($n['nomor']) ?></span><div class="pic"><?= avatar($n) ?></div>
    <div class="info text-center"><div class="small text-secondary">Peringkat</div>
      <div class="cinzel gold" style="font-size:2.4rem;line-height:1"><?= $me['rank'] ?? '-' ?></div>
      <div class="bar my-2"><i style="width:<?= $me['persen'] ?? 0 ?>%"></i></div>
      <div><b class="gold"><?= $me['total'] ?? 0 ?></b> suara · <?= $me['persen'] ?? 0 ?>%</div></div></div></div>
  <div class="col-md-8">
    <span class="badge" style="background:var(--gold);color:#000"><?= e($n['kategori']) ?></span>
    <h1 class="gold mt-2 mb-0"><?= e($n['nama']) ?></h1>
    <p class="text-secondary"><?= e($n['klub']) ?></p>
    <div class="row g-2 mb-3">
      <div class="col-sm-6"><div class="cardx p-3 h-100"><div class="small text-secondary">Statistik</div><b><?= e($n['statistik'] ?: '-') ?></b></div></div>
      <div class="col-sm-6"><div class="cardx p-3 h-100"><div class="small text-secondary">Prestasi</div><b><?= nl2br(e($n['prestasi'] ?: '-')) ?></b></div></div></div>
    <p><?= nl2br(e($n['deskripsi'])) ?></p>
    <div class="cardx p-3 mb-3"><div class="small text-secondary mb-2">Suara 7 hari terakhir</div>
      <svg viewBox="0 0 280 70" width="100%" height="90"><?php foreach ($hari as $i => [$l, $v]): $h = $v / $mx * 44; ?>
        <rect x="<?= $i * 40 + 6 ?>" y="<?= 52 - $h ?>" width="28" height="<?= max(2, $h) ?>" rx="4" fill="#d4af37"/>
        <text x="<?= $i * 40 + 20 ?>" y="66" font-size="9" text-anchor="middle" fill="#999"><?= $l ?></text>
        <text x="<?= $i * 40 + 20 ?>" y="<?= 48 - $h ?>" font-size="9" text-anchor="middle" fill="#d4af37"><?= $v ?></text><?php endforeach; ?></svg></div>
    <?php if ($embed): ?><div class="ratio ratio-16x9 mb-3 rounded overflow-hidden"><iframe src="<?= e($embed) ?>" allowfullscreen></iframe></div><?php endif; ?>
    <div class="d-flex flex-wrap gap-2">
      <?php if (votingBuka()): ?><button class="btn btn-gold btn-lg px-4" data-vote data-id="<?= $n['id'] ?>" data-nama="<?= e($n['nama']) ?>" data-klub="<?= e($n['klub']) ?>">🏆 Beri Suara</button><?php endif; ?>
      <a class="btn btn-outline-gold btn-lg" target="_blank" href="<?= e($wa) ?>">📲 Bagikan WhatsApp</a>
      <button class="btn btn-outline-gold btn-lg" data-copy="<?= e($link) ?>">🔗 Salin Tautan</button>
      <a class="btn btn-outline-gold btn-lg" href="bandingkan.php?kategori=<?= $n['kategori_id'] ?>&a=<?= $n['id'] ?>">⚔ Bandingkan</a>
    </div>
  </div>
</div>
<div class="sec-title"><h3>Pesan Dukungan</h3></div>
<?php foreach ($pesan as $m): ?><div class="bubble"><div class="small"><b class="gold"><?= e($m['nama'] ?: 'Penggemar') ?></b> <span class="text-secondary">· <?= waktuLalu($m['waktu']) ?></span></div><?= e($m['isi']) ?></div><?php endforeach; ?>
<?php if (!$pesan) echo '<p class="text-secondary">Belum ada pesan. Beri suara dan tinggalkan pesan pertamamu!</p>'; ?>
<div class="sec-title"><h3>Nominasi Lainnya</h3></div>
<div class="row g-3"><?php foreach (array_slice($lain, 0, 4) as $o): ?>
  <div class="col-6 col-md-3"><a href="detail.php?id=<?= $o['id'] ?>" class="cardx lead-card flex-column text-center"><div class="circ"><?= avatar($o, '') ?></div><div class="fw-bold small"><?= e($o['nama']) ?></div><div class="small gold"><?= $o['total'] ?> suara</div></a></div>
<?php endforeach; ?></div>
<?php require 'includes/footer.php'; ?>
