<?php
// Dipanggil PALING AWAL di tiap halaman baru (sebelum output / header.php).
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config-pay.php';
// >>> SESUAIKAN: file yang membuat variabel $pdo di proyek Anda <<<
if (!isset($pdo)) require_once __DIR__ . '/config.php';
if (!function_exists('e')) { function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); } }
require_once __DIR__ . '/pay.php';

function csrf(){ return $_SESSION['csrf'] ??= bin2hex(random_bytes(16)); }
function csrfField(){ return '<input type="hidden" name="csrf" value="'.csrf().'">'; }
function csrfCek(){
  if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(419); exit('Sesi kedaluwarsa, muat ulang halaman.'); }
}
function user(){
  global $pdo; static $u = false;
  if ($u === false) {
    $u = null;
    if (!empty($_SESSION['uid'])) { $s = $pdo->prepare('SELECT * FROM pengguna WHERE id=?'); $s->execute([$_SESSION['uid']]); $u = $s->fetch() ?: null; }
  }
  return $u;
}
function wajibLogin(){
  if (!user()) { $_SESSION['next'] = basename($_SERVER['REQUEST_URI']); header('Location: login.php'); exit; }
}
function rupiah($n){ return 'Rp ' . number_format((int)$n, 0, ',', '.'); }

// Pakai 1+ suara dari saldo (atomik). Panggil dari proses voting Anda.
function pakaiSuara($pdo, $uid, $jumlah = 1, $ket = 'Voting', $ref = null){
  $pdo->beginTransaction();
  $s = $pdo->prepare('UPDATE pengguna SET saldo_suara = saldo_suara - ? WHERE id=? AND saldo_suara >= ?');
  $s->execute([$jumlah, $uid, $jumlah]);
  if ($s->rowCount() !== 1) { $pdo->rollBack(); return false; }
  $pdo->prepare('INSERT INTO mutasi_suara (pengguna_id,jumlah,keterangan,ref) VALUES (?,?,?,?)')->execute([$uid, -$jumlah, $ket, $ref]);
  $pdo->commit(); return true;
}