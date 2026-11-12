-- Jalankan di database voting Anda (phpMyAdmin > Import / mysql < database.sql)
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS pengguna (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  hp VARCHAR(20) DEFAULT NULL,
  password_hash VARCHAR(255) NOT NULL,
  saldo_suara INT NOT NULL DEFAULT 0,
  dibuat DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS paket_suara (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(80) NOT NULL,
  jumlah_suara INT NOT NULL,
  harga INT NOT NULL,
  aktif TINYINT(1) NOT NULL DEFAULT 1,
  urutan INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pembayaran (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id VARCHAR(50) NOT NULL UNIQUE,
  pengguna_id INT NOT NULL,
  paket_id INT NOT NULL,
  jumlah_suara INT NOT NULL,
  nominal INT NOT NULL,
  gateway VARCHAR(20) NOT NULL,
  status ENUM('pending','paid','failed','expired') NOT NULL DEFAULT 'pending',
  snap_token VARCHAR(100) DEFAULT NULL,
  tipe_bayar VARCHAR(40) DEFAULT NULL,
  raw_notif TEXT,
  dibuat DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  dibayar DATETIME DEFAULT NULL,
  INDEX (pengguna_id), INDEX (status),
  FOREIGN KEY (pengguna_id) REFERENCES pengguna(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Buku besar saldo suara (audit: + beli, - pakai)
CREATE TABLE IF NOT EXISTS mutasi_suara (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pengguna_id INT NOT NULL,
  jumlah INT NOT NULL,
  keterangan VARCHAR(150) NOT NULL,
  ref VARCHAR(50) DEFAULT NULL,
  waktu DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (pengguna_id),
  FOREIGN KEY (pengguna_id) REFERENCES pengguna(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO paket_suara (nama,jumlah_suara,harga,urutan) VALUES
 ('Paket Hemat',5,10000,1),
 ('Paket Fans',25,45000,2),
 ('Paket Sultan',100,160000,3),
 ('Paket Legenda',500,700000,4);

-- Hubungkan suara dengan akun (kolom lama tetap aman)
ALTER TABLE suara ADD COLUMN pengguna_id INT NULL;
