<?php
$p = new PDO('mysql:host=127.0.0.1;dbname=laravel;charset=utf8mb4', 'root', '');
$s = $p->query("SELECT login_method, pairing_phone, pairing_code, qr_code IS NOT NULL as has_qr FROM bot_status WHERE id=1")->fetch(PDO::FETCH_ASSOC);
print_r($s);
