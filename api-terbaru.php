<?php
require 'includes/functions.php';
header('Content-Type: application/json; charset=utf-8');
$p = periode();
$st = $pdo->prepare('SELECT n.nama, k.nama AS kategori, s.waktu FROM suara s JOIN nominasi n ON n.id=s.nominasi_id JOIN kategori k ON k.id=s.kategori_id WHERE s.periode_id=? ORDER BY s.id DESC LIMIT 10');
$st->execute([$p['id'] ?? 0]);
$out = [];
foreach ($st->fetchAll() as $r) $out[] = ['nama' => $r['nama'], 'kategori' => $r['kategori'], 'lalu' => waktuLalu($r['waktu'])];
echo json_encode($out);
