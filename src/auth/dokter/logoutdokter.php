<?php
session_start();

// Hapus sesi dokter
unset($_SESSION['dokter']);

// Redirect ke halaman login dokter
$_SESSION['success'] = 'Logout berhasil.';
header('Location: dokter-login.php');
exit;
