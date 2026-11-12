<?php $pageTitle = 'Pilihanku'; require 'includes/header.php';
$kats = $pdo->query('SELECT id,nama FROM kategori WHERE aktif=1 ORDER BY urutan,id')->fetchAll();
?>
<div class="sec-title mt-2"><h3>⭐ Pilihanku</h3></div>
<p class="text-secondary">Riwayat dukunganmu disimpan di perangkat ini saja (tanpa akun).</p>
<div class="cardx p-3 mb-4"><div class="d-flex justify-content-between"><b>Kategori yang sudah kamu dukung</b><span class="gold"><span id="done">0</span>/<?= count($kats) ?></span></div>
  <div class="bar my-2"><i id="prog" style="width:0"></i></div><div id="sisa" class="small text-secondary"></div></div>
<div id="isi"></div>
<button class="btn btn-sm btn-outline-danger mt-3" id="hapus">Hapus riwayat</button>
<script>
const KATS=<?= json_encode($kats) ?>,esc=s=>String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
let r=[];try{r=JSON.parse(localStorage.getItem('riwayat')||'[]')}catch(e){}
const sudah=new Set(r.map(x=>x.kategori));
done.textContent=KATS.filter(k=>sudah.has(k.nama)).length;prog.style.width=done.textContent/KATS.length*100+'%';
const sisa=KATS.filter(k=>!sudah.has(k.nama));
document.getElementById('sisa').innerHTML=sisa.length?'Belum dipilih: '+sisa.map(k=>`<a class="gold" href="nominasi.php?kategori=${k.id}">${esc(k.nama)}</a>`).join(', '):'🎉 Kamu sudah mendukung di semua kategori!';
isi.innerHTML=r.map(x=>`<a href="detail.php?id=${x.id}" class="cardx lead-card mb-2"><div style="font-size:2rem">🏆</div><div><div class="fw-bold">#${esc(x.nomor)} ${esc(x.nama)}</div><div class="small text-secondary">${esc(x.kategori)} · ${new Date(x.waktu).toLocaleString('id-ID')}</div></div></a>`).join('')||'<p class="text-secondary">Belum ada riwayat. <a class="gold" href="nominasi.php">Mulai memilih</a></p>';
hapus.onclick=()=>{if(confirm('Hapus semua riwayat di perangkat ini?')){localStorage.removeItem('riwayat');location.reload()}};
</script>
<?php require 'includes/footer.php'; ?>
