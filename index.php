<?php $pageTitle = 'Beranda'; require 'includes/header.php';
$p = periode(); $buka = votingBuka(); $pid = $p['id'] ?? 0;
$kats = $pdo->query('SELECT * FROM kategori WHERE aktif=1 ORDER BY urutan,id')->fetchAll();
$q = $pdo->prepare('SELECT COUNT(*) FROM suara WHERE periode_id=?'); $q->execute([$pid]); $totSuara = (int)$q->fetchColumn();
$totNom = (int)$pdo->query('SELECT COUNT(*) FROM nominasi WHERE aktif=1')->fetchColumn();
$totPesan = (int)$pdo->query('SELECT COUNT(*) FROM pesan WHERE tampil=1')->fetchColumn();
$names = $pdo->query('SELECT nomor,nama FROM nominasi WHERE aktif=1 ORDER BY id')->fetchAll();
$pesan = $pdo->query('SELECT ps.nama, ps.isi, ps.waktu, n.nama AS untuk FROM pesan ps JOIN nominasi n ON n.id=ps.nominasi_id WHERE ps.tampil=1 ORDER BY ps.id DESC LIMIT 5')->fetchAll();
?>
<section class="hero">
  <div class="spot l"></div><div class="spot r"></div>
  <div class="troph">🏆</div>
  <div class="eyebrow mt-2">Penghargaan Pilihan Publik</div>
  <h1><?= e(setting('judul')) ?></h1>
  <p class="lead mx-auto text-secondary" style="max-width:660px"><?= e(setting('deskripsi')) ?></p>
  <?php if ($p): ?>
    <div class="mb-1"><?= $buka ? '<span class="live"><i></i> VOTING DIBUKA</span>' : '<span class="badge bg-secondary">Voting ditutup</span>' ?></div>
    <?php if ($buka): ?>
    <div class="countdown" data-end="<?= strtotime($p['selesai']) * 1000 ?>" id="cd">
      <div><b id="d">0</b>hari</div><div><b id="h">0</b>jam</div><div><b id="m">0</b>menit</div><div><b id="s">0</b>detik</div></div>
    <?php endif; ?>
    <div class="small text-secondary"><?= e($p['nama']) ?> · <?= date('d M Y H:i', strtotime($p['mulai'])) ?> – <?= date('d M Y H:i', strtotime($p['selesai'])) ?> WIB</div>
  <?php endif; ?>
  <div class="mt-4 d-flex gap-2 justify-content-center flex-wrap">
    <a href="nominasi.php" class="btn btn-gold btn-lg px-5">Mulai Memilih</a>
    <a href="peringkat.php" class="btn btn-outline-gold btn-lg px-4">📈 Peringkat Live</a>
  </div>
  <div class="row g-3 mt-4 justify-content-center" style="max-width:760px;margin-inline:auto">
    <?php foreach ([['Suara Masuk', $totSuara], ['Nominasi', $totNom], ['Kategori', count($kats)], ['Pesan Dukungan', $totPesan]] as [$l, $v]): ?>
      <div class="col-6 col-md-3"><div class="stat"><b data-count="<?= $v ?>">0</b><small class="text-secondary"><?= $l ?></small></div></div>
    <?php endforeach; ?>
  </div>
</section>

<div class="marquee"><div><?php for ($i = 0; $i < 2; $i++) foreach ($names as $n) echo '<span>★ #' . e($n['nomor']) . ' ' . e($n['nama']) . '</span>'; ?></div></div>

<div class="sec-title rv"><h3>Pemuncak Sementara</h3></div>
<div class="row g-3">
<?php foreach ($kats as $i => $k): $d = peringkat($k['id']); $w = $d['rows'][0] ?? null; if (!$w) continue; ?>
  <div class="col-md-6 col-lg-4 rv"><a href="detail.php?id=<?= $w['id'] ?>" class="cardx lead-card h-100">
    <div class="circ"><?= avatar($w, '') ?></div>
    <div><div class="small gold"><?= ikonKategori($i) ?> <?= e($k['nama']) ?></div>
      <div class="fw-bold fs-5">👑 <?= e($w['nama']) ?></div>
      <div class="small text-secondary"><?= e($w['klub']) ?></div>
      <div class="small mt-1"><b class="gold"><?= $w['total'] ?></b> suara · <?= $w['persen'] ?>%</div></div></a></div>
<?php endforeach; ?>
</div>

<div class="sec-title rv"><h3>Kategori Penghargaan</h3></div>
<div class="row g-3">
<?php foreach ($kats as $i => $k): ?>
  <div class="col-md-4 rv"><a class="cardx kcard" href="nominasi.php?kategori=<?= $k['id'] ?>">
    <span class="big"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span><div class="ik"><?= ikonKategori($i) ?></div>
    <h5 class="gold mt-2 mb-1"><?= e($k['nama']) ?></h5><p class="text-secondary mb-2 small"><?= e($k['deskripsi']) ?></p><span class="small gold">Lihat nominasi →</span></a></div>
<?php endforeach; ?>
</div>

<div class="row g-4 mt-2">
  <div class="col-lg-6 rv"><div class="sec-title mt-3"><h3>Dukungan Terbaru</h3></div>
    <div class="cardx p-3 feed" id="feed"><div class="text-secondary small">Memuat...</div></div></div>
  <div class="col-lg-6 rv"><div class="sec-title mt-3"><h3>Pesan untuk Sang Idola</h3></div>
    <div class="cardx p-3 feed">
      <?php foreach ($pesan as $m): ?><div class="bubble"><div class="small"><b class="gold"><?= e($m['nama'] ?: 'Penggemar') ?></b> untuk <b><?= e($m['untuk']) ?></b> <span class="text-secondary">· <?= waktuLalu($m['waktu']) ?></span></div><?= e($m['isi']) ?></div><?php endforeach; ?>
      <?php if (!$pesan) echo '<div class="text-secondary small">Belum ada pesan. Jadilah yang pertama!</div>'; ?>
    </div></div>
</div>
<script>
const cd=document.getElementById('cd');
if(cd){const end=+cd.dataset.end;const t=()=>{let x=Math.max(0,end-Date.now())/1000|0;d.textContent=x/86400|0;h.textContent=x%86400/3600|0;m.textContent=x%3600/60|0;s.textContent=x%60};t();setInterval(t,1000)}
const esc=s=>String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
async function feedMuat(){try{const r=await (await fetch('api-terbaru.php')).json();
 feed.innerHTML=r.map(x=>`<div class="it">🏆 Seseorang mendukung <b>${esc(x.nama)}</b> <span class="text-secondary small">· ${esc(x.kategori)} · ${esc(x.lalu)}</span></div>`).join('')||'<div class="text-secondary small">Belum ada suara.</div>'}catch(e){}}
feedMuat();setInterval(feedMuat,8000);
</script>
<?php require 'includes/footer.php'; ?>
