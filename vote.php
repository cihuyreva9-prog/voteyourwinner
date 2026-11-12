<?php
require 'includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: nominasi.php'); exit; }
csrfCek();
$st = $pdo->prepare('SELECT * FROM nominasi WHERE id=? AND aktif=1');
$st->execute([(int)($_POST['nominasi_id'] ?? 0)]); $n = $st->fetch();
if (!$n) { flash('Nominasi tidak valid.', 'danger'); header('Location: nominasi.php'); exit; }
$back = 'nominasi.php?kategori=' . $n['kategori_id'];

if (!votingBuka()) { flash('Voting sedang ditutup.', 'warning'); header("Location: $back"); exit; }
$p = periode();

// Mode "perangkat": satu suara per kategori per periode, dicatat lewat cookie
$cookie = 'v_' . $p['id'] . '_' . $n['kategori_id'];
if (setting('mode_voting') === 'perangkat' && isset($_COOKIE[$cookie])) {
    flash('Perangkat ini sudah memberi suara pada kategori tersebut.', 'warning');
    header("Location: $back"); exit;
}
$pdo->prepare('INSERT INTO suara (nominasi_id,kategori_id,periode_id) VALUES (?,?,?)')->execute([$n['id'], $n['kategori_id'], $p['id']]);
if (setting('mode_voting') === 'perangkat') setcookie($cookie, '1', time() + 86400 * 365, '/');

$pesan = trim(mb_substr(strip_tags($_POST['pesan'] ?? ''), 0, 140));
if ($pesan !== '') {
    $nama = trim(mb_substr(strip_tags($_POST['nama'] ?? ''), 0, 40));
    $pdo->prepare('INSERT INTO pesan (nominasi_id,nama,isi) VALUES (?,?,?)')->execute([$n['id'], $nama, $pesan]);
}
$_SESSION['last_vote'] = $n['id'];
header('Location: sukses.php'); exit;
