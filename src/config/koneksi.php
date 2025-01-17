<?php
$host = 'localhost';
$username = 'root'; // Ganti dengan username database Anda
$password = ''; // Ganti dengan password database Anda
$dbname = 'poliklinik'; // Ganti dengan nama database Anda

// Membuat koneksi
// $db = new mysqli($host, $username, $password, $dbname);

// // Memeriksa koneksi
// if ($db->connect_error) {
//     die("Koneksi gagal: " . $db->connect_error);
// }

try {
    // Membuat koneksi PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Mengatur error mode menjadi exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

?>
