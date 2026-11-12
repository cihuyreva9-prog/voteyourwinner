<?php
require 'includes/functions.php';
header('Content-Type: application/json; charset=utf-8');
$d = peringkat((int)($_GET['kategori'] ?? 0));
foreach ($d['rows'] as &$r) { $r['foto_url'] = fotoUrl($r['foto']); $r['inisial'] = inisial($r['nama']); $r['hue'] = hue($r['id']); }
$d['waktu'] = date('d M Y H:i:s') . ' WIB';
echo json_encode($d);
