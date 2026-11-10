<?php
$host='localhost'; $db='db_ppdb_smk'; $user='root'; $pass='';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die('Koneksi database gagal.');
}
define('BASE_URL', '/ppdb-smk');
define('TAHUN_AJARAN', '2026/2027');