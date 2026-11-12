<?php $pageTitle = 'Peringkat Live'; require 'includes/header.php';
$kats = $pdo->query('SELECT * FROM kategori WHERE aktif=1 ORDER BY urutan,id')->fetchAll();
$kid = (int)($_GET['kategori'] ?? ($kats[0]['id'] ?? 0));
$buka = votingBuka();
?>
<div class="sec-title mt-2"><h3>Peringkat Live</h3><span class="live"><i></i> LIVE</span></div>
<ul class="nav nav-pills mb-2">
<?php foreach ($kats as $i => $k): ?>
  <li class="nav-item"><a class="nav-link <?= $k['id'] == $kid ? 'active' : '' ?>" href="?kategori=<?= $k['id'] ?>"><?= ikonKategori($i) ?> <?= e($k['nama']) ?></a></li>
<?php endforeach; ?>
</ul>
<div id="podium" class="podium"></div>
<div class="d-flex justify-content-between small text-secondary mb-2">
  <span>Total suara: <b class="gold" id="total">0</b></span><span>Diperbarui: <span id="waktu">-</span></span></div>
<div id="list"></div>
<script>
const KID=<?= $kid ?>,BUKA=<?= $buka ? 'true' : 'false' ?>,prev={},esc=s=>String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const pic=(r,cls)=>r.foto_url?`<img class="${cls||''}" src="${esc(r.foto_url)}" alt="">`:`<div class="avatar ${cls||''}" style="--h:${r.hue}"><span>${esc(r.inisial)}</span></div>`;
async function muat(){
  let d;try{d=await (await fetch('api-peringkat.php?kategori='+KID)).json()}catch(e){return}
  total.textContent=d.total.toLocaleString('id-ID');waktu.textContent=d.waktu;
  const t=d.rows.slice(0,3),urut=[t[1],t[0],t[2]].filter(Boolean);
  podium.innerHTML=urut.map(r=>`<div class="p p${r.rank}"><div class="pf">${pic(r)}</div><div class="small fw-bold">${esc(r.nama)}</div><div class="small text-secondary">${r.total} suara</div><div class="blok">${r.rank}</div></div>`).join('');
  list.innerHTML=d.rows.map(r=>{let tren='';const b=prev[r.id];
    if(b!==undefined)tren=r.rank<b?'<span class="text-success">▲ naik</span>':r.rank>b?'<span class="text-danger">▼ turun</span>':'';prev[r.id]=r.rank;
    return `<div class="cardx rrow r${r.rank}"><div class="rn">${r.rank}</div><a href="detail.php?id=${r.id}" class="th">${pic(r)}</a>
      <div class="flex-grow-1" style="min-width:0"><a href="detail.php?id=${r.id}" class="fw-bold text-decoration-none">#${esc(r.nomor)} ${esc(r.nama)}</a> <small>${tren}</small>
        <div class="small text-secondary text-truncate">${esc(r.klub||'')}</div><div class="bar mt-1"><i style="width:${r.persen}%"></i></div></div>
      <div class="text-end"><div class="fw-bold gold">${r.total}</div><div class="small text-secondary">${r.persen}%</div></div>
      ${BUKA?`<button class="btn btn-sm btn-gold d-none d-sm-block" data-vote data-id="${r.id}" data-nama="${esc(r.nama)}" data-klub="${esc(r.klub||'')}">Beri Suara</button>`:''}</div>`}).join('')||'<p class="text-secondary">Belum ada nominasi.</p>';
}
muat();setInterval(muat,5000);
</script>
<?php require 'includes/footer.php'; ?>
