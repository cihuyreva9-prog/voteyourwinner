<?php
require 'includes/pay_boot.php';
if (user()) { header('Location: akun.php'); exit; }
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrfCek();
  $_SESSION['try'] = ($_SESSION['try'] ?? 0) + 1;
  if ($_SESSION['try'] > 8) { $err = 'Terlalu banyak percobaan. Coba lagi nanti.'; }
  else {
    $s = $pdo->prepare('SELECT * FROM pengguna WHERE email=?'); $s->execute([strtolower(trim($_POST['email'] ?? ''))]);
    $u = $s->fetch();
    if ($u && password_verify($_POST['password'] ?? '', $u['password_hash'])) {
      session_regenerate_id(true); $_SESSION['uid'] = (int)$u['id']; $_SESSION['try'] = 0;
      $n = $_SESSION['next'] ?? 'akun.php'; unset($_SESSION['next']);
      header('Location: ' . (preg_match('/^[\w\-]+\.php(\?[\w=&\-]*)?$/', $n) ? $n : 'akun.php')); exit;
    }
    $err = 'Email atau password salah.';
  }
}
$pageTitle = 'Masuk'; require 'includes/header.php'; ?>
<div class="row justify-content-center"><div class="col-md-6 col-lg-4"><div class="cardx p-4">
  <h3 class="gold mb-3">Masuk</h3>
  <?php if ($err): ?><div class="alert alert-danger py-2"><?= e($err) ?></div><?php endif; ?>
  <form method="post"><?= csrfField() ?>
    <label class="form-label">Email</label><input type="email" class="form-control mb-3" name="email" required>
    <label class="form-label">Password</label><input type="password" class="form-control mb-3" name="password" required>
    <button class="btn btn-gold w-100">Masuk</button>
  </form>
  <div class="small text-secondary mt-3 text-center">Belum punya akun? <a href="register.php" class="gold">Daftar</a></div>
</div></div></div>
<?php require 'includes/footer.php'; ?>