# Register + Payment (Midtrans) untuk Voting

## Pasang
1. Import `database.sql` ke database Anda.
2. Salin semua file ke root proyek (folder `includes/` digabung).
3. Buka `includes/pay_boot.php`: sesuaikan baris `require_once __DIR__.'/config.php'` ke file yang membuat `$pdo`
   (hapus baris itu jika `header.php` sudah membuat `$pdo` lebih dulu). Lakukan hal sama di `notif-midtrans.php`.
4. Tambahkan menu di `header.php`: `daftar` -> register.php, `masuk` -> login.php, `Akun/Beli Suara` -> akun.php / beli.php.

## Uji coba
**A. Simulator (default, tanpa akun):** `PAY_GATEWAY='mock'` di `includes/config-pay.php`.
Daftar -> Beli -> tombol "Simulasikan Berhasil/Gagal/Kedaluwarsa".

**B. Midtrans Sandbox:** daftar di dashboard.sandbox.midtrans.com, ambil Server/Client Key (Settings > Access Keys),
isi di config, set `PAY_GATEWAY='midtrans'`. Kartu tes: 4811 1111 1111 1114, CVV 123, exp bulan/tahun mendatang, OTP 112233.
Di localhost webhook tak terjangkau, tapi `akun.php` otomatis menarik status dari Midtrans.
Untuk server publik, isi Payment Notification URL: `https://domainanda/notif-midtrans.php`.

## Memakai saldo saat voting
Di proses voting (tempat INSERT ke `suara`):
```php
require_once 'includes/pay_boot.php'; wajibLogin();
if (!pakaiSuara($pdo, user()['id'], 1, 'Vote nominasi #'.$nominasiId)) { /* saldo kurang -> arahkan ke beli.php */ }
// lalu INSERT suara (isi kolom pengguna_id = user()['id'])
```
## Production
Set `MIDTRANS_PRODUCTION=true`, ganti key production, gunakan HTTPS, `PAY_GATEWAY='midtrans'`.
