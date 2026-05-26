<?php
// Script temporário para gerar hash e validar login
$pass = "admin";
$hash = password_hash($pass, PASSWORD_DEFAULT);
echo "Password: " . $pass . "\n";
echo "Hash: " . $hash . "\n";
?>
