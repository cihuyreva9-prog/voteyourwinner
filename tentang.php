<?php $pageTitle = 'Tentang & Aturan'; require 'includes/header.php';
$p = periode(); $perangkat = setting('mode_voting') === 'perangkat';
?>
<div class="sec-title mt-2"><h3>Tentang & Aturan</h3></div>
<div class="row g-3 text-center mb-4">
  <?php foreach ([['1', '🔍', 'Kenali', 'Jelajahi profil, statistik, dan video nominasi.'], ['2', '🏆', 'Beri Suara', 'Tekan tombol suara, konfirmasi, tinggalkan pesan dukungan.'], ['3', '📈', 'Pantau', 'Lihat peringkat berubah secara langsung.']] as [$no, $ik, $jd, $ds]): ?>
  <div class="col-md-4 rv"><div class="cardx p-4 h-100"><div style="font-size:2.6rem"><?= $ik ?></div><h5 class="gold"><?= $no ?>. <?= $jd ?></h5><p class="text-secondary mb-0"><?= $ds ?></p></div></div>
  <?php endforeach; ?>
</div>
<div class="cardx p-4 mb-4"><h5 class="gold">Aturan Voting</h5><ul class="mb-0">
  <li><?= $perangkat ? 'Setiap perangkat dapat memberi <b>satu suara per kategori</b>.' : 'Kamu dapat memberi suara sebanyak yang kamu mau selama voting dibuka.' ?></li>
  <li>Suara hanya dihitung ketika periode voting berstatus aktif.</li>
  <li>Suara yang sudah masuk tidak dapat diubah atau ditarik.</li>
  <li>Pesan dukungan bersifat opsional dan dapat dimoderasi panitia.</li></ul></div>
<?php if ($p): ?><div class="cardx p-4 mb-4"><h5 class="gold">Jadwal</h5><?= e($p['nama']) ?><br><span class="text-secondary"><?= date('d M Y H:i', strtotime($p['mulai'])) ?> sampai <?= date('d M Y H:i', strtotime($p['selesai'])) ?> WIB</span></div><?php endif; ?>
<h5 class="gold mb-3">Pertanyaan Umum</h5>
<div class="accordion" id="faq">
<?php foreach ([['Apakah harus membuat akun?', 'Tidak. Kamu langsung bisa memberi suara tanpa registrasi.'], ['Bagaimana pemenang ditentukan?', 'Nominasi dengan suara terbanyak pada tiap kategori menjadi pemenang.'], ['Kapan pemenang diumumkan?', 'Setelah voting ditutup, panitia membuka halaman Malam Penghargaan.'], ['Di mana riwayat pilihanku?', 'Di menu Pilihanku. Riwayat tersimpan di browser perangkatmu.']] as $i => [$q, $j]): ?>
  <div class="accordion-item" style="background:var(--card);border-color:var(--line)"><h2 class="accordion-header"><button class="accordion-button collapsed" style="background:transparent;color:var(--text)" data-bs-toggle="collapse" data-bs-target="#f<?= $i ?>"><?= e($q) ?></button></h2>
  <div id="f<?= $i ?>" class="accordion-collapse collapse" data-bs-parent="#faq"><div class="accordion-body text-secondary"><?= e($j) ?></div></div></div>
<?php endforeach; ?></div>
<?php require 'includes/footer.php'; ?>
