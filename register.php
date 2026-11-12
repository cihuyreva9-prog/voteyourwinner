<?php
require 'includes/pay_boot.php';
if (user()) { header('Location: akun.php'); exit; }
$err = []; $v = ['nama' => '', 'email' => '', 'hp' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrfCek();
  $v = ['nama' => trim($_POST['nama'] ?? ''), 'email' => strtolower(trim($_POST['email'] ?? '')), 'hp' => preg_replace('/\D/', '', $_POST['hp'] ?? '')];
  $pw = $_POST['password'] ?? '';
  if (mb_strlen($v['nama']) < 3) $err[] = 'Nama minimal 3 karakter.';
  if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $err[] = 'Email tidak valid.';
  if ($v['hp'] !== '' && !preg_match('/^(08|628)\d{8,12}$/', $v['hp'])) $err[] = 'Nomor HP tidak valid.';
  if (strlen($pw) < 8) $err[] = 'Password minimal 8 karakter.';
  if ($pw !== ($_POST['password2'] ?? '')) $err[] = 'Konfirmasi password tidak sama.';
  if (!$err) {
    $c = $pdo->prepare('SELECT 1 FROM pengguna WHERE email=?'); $c->execute([$v['email']]);
    if ($c->fetch()) $err[] = 'Email sudah terdaftar.';
  }
  if (!$err) {
    $pdo->prepare('INSERT INTO pengguna (nama,email,hp,password_hash) VALUES (?,?,?,?)')
        ->execute([$v['nama'], $v['email'], $v['hp'] ?: null, password_hash($pw, PASSWORD_DEFAULT)]);
    session_regenerate_id(true); $_SESSION['uid'] = (int)$pdo->lastInsertId();
    header('Location: beli.php'); exit;
  }
}
$pageTitle = 'Daftar'; require 'includes/header.php'; ?>
<div class="row justify-content-center"><div class="col-md-6 col-lg-5"><div class="cardx p-4">
  <h3 class="gold mb-3">Buat Akun</h3>
  <?php foreach ($err as $x): ?><div class="alert alert-danger py-2 mb-2"><?= e($x) ?></div><?php endforeach; ?>
  <form method="post" autocomplete="on"><?= csrfField() ?>
    <label class="form-label">Nama</label><input class="form-control mb-3" name="nama" value="<?= e($v['nama']) ?>" required>
    <label class="form-label">Email</label><input type="email" class="form-control mb-3" name="email" value="<?= e($v['email']) ?>" required>
    <label class="form-label">No. HP (opsional)</label><input class="form-control mb-3" name="hp" value="<?= e($v['hp']) ?>" placeholder="08xxxxxxxxxx">
    <label class="form-label">Password</label><input type="password" class="form-control mb-3" name="password" minlength="8" required>
    <label class="form-label">Ulangi Password</label><input type="password" class="form-control mb-3" name="password2" required>
    <button class="btn btn-gold w-100">Daftar</button>
  </form>
  <div class="small text-secondary mt-3 text-center">Sudah punya akun? <a href="login.php" class="gold">Masuk</a></div>
</div></div></div>
<?php require 'includes/footer.php'; ?>