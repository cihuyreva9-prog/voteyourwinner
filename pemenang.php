<?php $pageTitle = 'Malam Penghargaan'; require 'includes/header.php';
$kats = $pdo->query('SELECT * FROM kategori WHERE aktif=1 ORDER BY urutan,id')->fetchAll();
$resmi = setting('hasil_dikunci') === '1';
?>
<div class="hero" style="padding-top:20px"><div class="spot l"></div><div class="spot r"></div>
  <div class="eyebrow">Gala Penghargaan</div><h1 style="font-size:clamp(2rem,6vw,3.4rem)">Malam Penghargaan</h1></div>
<?php if (!$resmi): ?>
  <div class="text-center py-4"><div style="font-size:5rem">🔒</div>
  <h5>Pemenang belum diumumkan</h5><p class="text-secondary">Pengumuman dibuka panitia setelah voting ditutup. Sementara itu, pantau peringkatnya!</p>
  <a class="btn btn-gold" href="peringkat.php">Lihat Peringkat Sementara</a></div>
<?php else: ?>
<div class="text-center mb-4"><button class="btn btn-gold btn-lg" onclick="document.querySelectorAll('.env').forEach(e=>e.click())">✨ Buka Semua Amplop</button></div>
<div class="row g-4 justify-content-center">
<?php foreach ($kats as $i => $k): $d = peringkat($k['id']); $w = $d['rows'][0] ?? null; if (!$w) continue; ?>
  <div class="col-md-6 col-lg-4"><div class="cardx p-4 text-center h-100">
    <h5 class="gold"><?= ikonKategori($i) ?> <?= e($k['nama']) ?></h5>
    <div class="env py-3" onclick="buka(this)" role="button"><div class="envelope">✉️</div><br><small class="text-secondary">Klik untuk membuka amplop</small></div>
    <div class="hasil d-none reveal">
      <div class="mx-auto" style="width:150px;height:150px;border-radius:50%;overflow:hidden;border:4px solid var(--gold);box-shadow:0 0 50px var(--glow)"><?= avatar($w, '') ?></div>
      <div style="font-size:2.4rem" class="mt-2">🏆</div>
      <div class="small text-secondary">Pemenang</div>
      <h3 class="gold"><?= e($w['nama']) ?></h3>
      <div class="text-secondary"><?= e($w['klub']) ?></div>
      <div class="mt-2"><b><?= $w['total'] ?></b> suara (<?= $w['persen'] ?>%)</div>
      <?php foreach (array_slice($d['rows'], 1, 2) as $r): ?><div class="small text-secondary mt-1"><?= $r['rank'] == 2 ? '🥈' : '🥉' ?> <?= e($r['nama']) ?> · <?= $r['total'] ?> suara</div><?php endforeach; ?>
    </div></div></div>
<?php endforeach; ?>
</div>
<script>function buka(el){if(el.classList.contains('d-none'))return;el.classList.add('d-none');el.nextElementSibling.classList.remove('d-none');konfeti(60)}</script>
<?php endif; require 'includes/footer.php'; ?>
