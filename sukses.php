<?php $pageTitle = 'Suara Tersimpan'; require 'includes/header.php';
$id = (int)($_SESSION['last_vote'] ?? 0);
$st = $pdo->prepare('SELECT n.*, k.nama AS kategori FROM nominasi n JOIN kategori k ON k.id=n.kategori_id WHERE n.id=?'); $st->execute([$id]); $n = $st->fetch();
if (!$n) { header('Location: nominasi.php'); exit; }
$d = peringkat($n['kategori_id']); $me = null; foreach ($d['rows'] as $r) if ($r['id'] == $n['id']) $me = $r;
$data = ['id' => (int)$n['id'], 'nama' => $n['nama'], 'klub' => $n['klub'], 'kategori' => $n['kategori'], 'nomor' => $n['nomor'],
  'judul' => setting('judul'), 'foto' => fotoUrl($n['foto']) ?: '', 'hue' => hue($n['id']), 'inisial' => inisial($n['nama']),
  'rank' => $me['rank'] ?? 0, 'url' => baseUrl() . '/index.php'];
$wa = 'https://wa.me/?text=' . rawurlencode('Saya mendukung ' . $n['nama'] . ' di ' . setting('judul') . '! Ayo ikut memilih: ' . baseUrl() . '/index.php');
?>
<div class="text-center">
  <div class="troph reveal">🏆</div>
  <h2 class="gold mt-2">Suaramu Tersimpan!</h2>
  <p class="text-secondary">Terima kasih sudah mendukung <b><?= e($n['nama']) ?></b>. Sekarang dia berada di peringkat <b class="gold"><?= $me['rank'] ?? '-' ?></b> kategori <?= e($n['kategori']) ?>.</p>
  <canvas id="kartu" width="1080" height="1350" style="width:100%;max-width:340px;border-radius:18px;border:1px solid var(--gold);box-shadow:0 0 50px var(--glow)"></canvas>
  <div class="d-flex flex-wrap gap-2 justify-content-center mt-3">
    <a id="unduh" class="btn btn-gold" download="pilihanku.png">⬇ Unduh Kartu</a>
    <button id="bagi" class="btn btn-outline-gold">📤 Bagikan</button>
    <a class="btn btn-outline-gold" target="_blank" href="<?= e($wa) ?>">📲 WhatsApp</a>
  </div>
  <div class="mt-4 d-flex flex-wrap gap-2 justify-content-center">
    <a class="btn btn-outline-gold" href="nominasi.php">Pilih kategori lain</a>
    <a class="btn btn-outline-gold" href="peringkat.php?kategori=<?= $n['kategori_id'] ?>">Lihat peringkat</a>
    <a class="btn btn-outline-gold" href="riwayat.php">⭐ Pilihanku</a>
  </div>
</div>
<script>
const D=<?= json_encode($data, JSON_UNESCAPED_SLASHES) ?>;
// simpan riwayat di perangkat
try{const r=JSON.parse(localStorage.getItem('riwayat')||'[]');r.unshift({id:D.id,nama:D.nama,kategori:D.kategori,nomor:D.nomor,waktu:Date.now()});localStorage.setItem('riwayat',JSON.stringify(r.slice(0,100)))}catch(e){}
function wrap(x,t,cx,y,max,lh){const w=t.split(' ');let l='';const ls=[];w.forEach(k=>{const tt=l+k+' ';if(x.measureText(tt).width>max&&l){ls.push(l);l=k+' '}else l=tt});ls.push(l);ls.forEach((s,i)=>x.fillText(s.trim(),cx,y+i*lh));return y+ls.length*lh}
function gambar(img){
  const c=document.getElementById('kartu'),x=c.getContext('2d');
  let g=x.createLinearGradient(0,0,1080,1350);g.addColorStop(0,'#1c1608');g.addColorStop(.5,'#07070a');g.addColorStop(1,'#241a05');x.fillStyle=g;x.fillRect(0,0,1080,1350);
  let rg=x.createRadialGradient(540,500,20,540,500,600);rg.addColorStop(0,'rgba(247,220,131,.28)');rg.addColorStop(1,'transparent');x.fillStyle=rg;x.fillRect(0,0,1080,1350);
  x.strokeStyle='#d4af37';x.lineWidth=6;x.strokeRect(36,36,1008,1278);x.lineWidth=2;x.strokeRect(54,54,972,1242);
  x.textAlign='center';x.fillStyle='#d4af37';x.font='700 34px Cinzel,Georgia,serif';x.fillText(D.judul.toUpperCase(),540,130);
  x.fillStyle='#f6f1e3';x.font='500 30px Poppins,Arial,sans-serif';x.fillText('SAYA MENDUKUNG',540,215);
  // lingkaran foto
  x.save();x.beginPath();x.arc(540,470,200,0,7);x.clip();
  if(img){x.drawImage(img,340,270,400,400)}else{let a=x.createRadialGradient(480,400,10,540,470,230);a.addColorStop(0,'hsl('+D.hue+',45%,32%)');a.addColorStop(1,'#0c0c10');x.fillStyle=a;x.fillRect(340,270,400,400);
    x.fillStyle='#f7dc83';x.font='900 150px Cinzel,Georgia,serif';x.textBaseline='middle';x.fillText(D.inisial,540,478);x.textBaseline='alphabetic'}
  x.restore();x.beginPath();x.arc(540,470,200,0,7);x.strokeStyle='#f7dc83';x.lineWidth=8;x.stroke();
  x.fillStyle='#d4af37';x.beginPath();x.arc(740,300,52,0,7);x.fill();x.fillStyle='#000';x.font='900 44px Cinzel,serif';x.textBaseline='middle';x.fillText(D.nomor,740,303);x.textBaseline='alphabetic';
  x.fillStyle='#f7dc83';x.font='900 80px Cinzel,Georgia,serif';let y=wrap(x,D.nama,540,770,880,92);
  x.fillStyle='#f6f1e3';x.font='400 36px Poppins,Arial,sans-serif';x.fillText(D.klub,540,y+10);
  x.fillStyle='#d4af37';x.fillRect(340,y+50,400,3);
  x.font='600 38px Poppins,Arial,sans-serif';x.fillText('Kategori: '+D.kategori,540,y+120);
  x.font='400 30px Poppins,Arial,sans-serif';x.fillStyle='#a8a392';x.fillText('Ayo ikut memberi suaramu!',540,1210);
  x.font='500 26px Poppins,Arial,sans-serif';x.fillText(D.url.replace(/^https?:\/\//,''),540,1255);
  document.getElementById('unduh').href=c.toDataURL('image/png');
}
function mulai(){document.fonts.ready.then(()=>{if(D.foto){const i=new Image();i.onload=()=>gambar(i);i.onerror=()=>gambar(null);i.src=D.foto}else gambar(null)})}
mulai();setTimeout(()=>konfeti(90),300);
document.getElementById('bagi').onclick=async()=>{const c=document.getElementById('kartu');
  c.toBlob(async b=>{const f=new File([b],'pilihanku.png',{type:'image/png'});
    try{if(navigator.canShare&&navigator.canShare({files:[f]}))await navigator.share({files:[f],text:'Saya mendukung '+D.nama+'!'});else if(navigator.share)await navigator.share({text:'Saya mendukung '+D.nama+'! '+D.url});else alert('Gunakan tombol Unduh lalu bagikan manual.')}catch(e){}})};
</script>
<?php require 'includes/footer.php'; ?>
