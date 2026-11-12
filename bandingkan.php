<?php $pageTitle = 'Bandingkan'; require 'includes/header.php';
$kats = $pdo->query('SELECT * FROM kategori WHERE aktif=1 ORDER BY urutan,id')->fetchAll();
$kid = (int)($_GET['kategori'] ?? ($kats[0]['id'] ?? 0));
$d = peringkat($kid); $rows = $d['rows']; $by = [];
foreach ($rows as $r) $by[$r['id']] = $r;
$a = $by[(int)($_GET['a'] ?? 0)] ?? ($rows[0] ?? null);
$b = $by[(int)($_GET['b'] ?? 0)] ?? null;
if (!$b || ($a && $b['id'] == $a['id'])) foreach ($rows as $r) if ($a && $r['id'] != $a['id']) { $b = $r; break; }
?>
<div class="sec-title mt-2"><h3>⚔ Bandingkan Nominasi</h3></div>
<ul class="nav nav-pills mb-3"><?php foreach ($kats as $i => $k): ?><li class="nav-item"><a class="nav-link <?= $k['id'] == $kid ? 'active' : '' ?>" href="?kategori=<?= $k['id'] ?>"><?= ikonKategori($i) ?> <?= e($k['nama']) ?></a></li><?php endforeach; ?></ul>
<?php if (!$a || !$b): ?><div class="alert alert-warning">Butuh minimal dua nominasi pada kategori ini.</div>
<?php else: ?>
<form method="get" class="row g-2 mb-4"><input type="hidden" name="kategori" value="<?= $kid ?>">
  <?php foreach (['a' => $a, 'b' => $b] as $nm => $sel): ?>
  <div class="col-6"><select class="form-select" name="<?= $nm ?>" onchange="this.form.submit()"><?php foreach ($rows as $r): ?><option value="<?= $r['id'] ?>" <?= $r['id'] == $sel['id'] ? 'selected' : '' ?>><?= e($r['nama']) ?></option><?php endforeach; ?></select></div>
  <?php endforeach; ?></form>
<div class="cardx p-3"><div class="table-responsive"><table class="table table-dark cmp text-center mb-0">
  <tr><th></th><?php foreach ([$a, $b] as $x): ?><td style="width:38%"><div class="mx-auto" style="width:110px;height:110px;border-radius:50%;overflow:hidden;border:3px solid var(--gold)"><?= avatar($x, '') ?></div>
    <a href="detail.php?id=<?= $x['id'] ?>" class="fw-bold gold text-decoration-none d-block mt-2"><?= e($x['nama']) ?></a></td><?php endforeach; ?></tr>
  <?php
  $rowsC = [['Klub', 'klub'], ['Statistik', 'statistik'], ['Prestasi', 'prestasi'], ['Peringkat', 'rank'], ['Total suara', 'total'], ['Persentase', 'persen']];
  foreach ($rowsC as [$l, $f]): ?>
  <tr><th class="text-secondary text-start"><?= $l ?></th>
  <?php foreach ([[$a, $b], [$b, $a]] as [$x, $y]):
      $v = $x[$f]; $win = in_array($f, ['total', 'persen']) && $x[$f] > $y[$f]; $win = $win || ($f === 'rank' && $x[$f] < $y[$f]); ?>
    <td class="<?= $win ? 'gold fw-bold' : '' ?>"><?= $win ? '👑 ' : '' ?><?= e($v) ?><?= $f === 'persen' ? '%' : '' ?></td>
  <?php endforeach; ?></tr><?php endforeach; ?>
  <tr><th class="text-secondary text-start">Porsi suara</th><?php foreach ([$a, $b] as $x): ?><td><div class="bar"><i style="width:<?= $x['persen'] ?>%"></i></div></td><?php endforeach; ?></tr>
  <?php if (votingBuka()): ?><tr><th></th><?php foreach ([$a, $b] as $x): ?><td><button class="btn btn-gold btn-sm" data-vote data-id="<?= $x['id'] ?>" data-nama="<?= e($x['nama']) ?>" data-klub="<?= e($x['klub']) ?>">🏆 Pilih <?= e(explode(' ', $x['nama'])[0]) ?></button></td><?php endforeach; ?></tr><?php endif; ?>
</table></div></div>
<?php endif; require 'includes/footer.php'; ?>
