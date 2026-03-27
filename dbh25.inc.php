<?php
$dsn = "mysql:host=localhost;dbname=no_gpt";
$dbusername = "root";
$dbpassword = "";

try{
    $pdo = new PDO($email,$messages);
    $pdo- >setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    echo "Connection failed: " . $e- >getMessage();
}