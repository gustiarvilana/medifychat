<?php
$p = new PDO('mysql:host=127.0.0.1;dbname=laravel;charset=utf8mb4', 'root', '');
$p->exec("UPDATE bot_status SET login_method='pairing_code', pairing_phone='6285964397416', qr_code=NULL, pairing_code=NULL WHERE id=1");
echo "OK\n";
