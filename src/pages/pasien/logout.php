<?php
session_start(); // Mulai sesi
include('../../config/koneksi.php');


// Hapus semua data sesi
session_unset();
session_destroy();

// Arahkan pengguna ke halaman login atau beranda
header('Location: auth/pasien/login.php');
exit;
?>
