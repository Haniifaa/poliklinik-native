<?php
session_start();
include_once("../../config/koneksi.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $alamat = trim($_POST['alamat']);
    $errors = [];

    if (empty($nama)) {
        $errors[] = 'Nama wajib diisi.';
    }

    if (empty($alamat)) {
        $errors[] = 'Alamat wajib diisi.';
    }

    if (empty($errors)) {
        // Query untuk mencari dokter berdasarkan nama dan alamat
        $stmt = $pdo->prepare("SELECT * FROM dokter WHERE nama = :nama AND alamat = :alamat");
        
        // Menggunakan bindParam() untuk PDO
        $stmt->bindParam(':nama', $nama, PDO::PARAM_STR);
        $stmt->bindParam(':alamat', $alamat, PDO::PARAM_STR);

        // Menjalankan query
        $stmt->execute();

        // Mengambil hasil query
        $dokter = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dokter) {
            // Jika data ditemukan, simpan data dokter di session
            $_SESSION['dokter'] = $dokter; // Menyimpan seluruh data dokter
            $_SESSION['id'] = $dokter['id']; // Menyimpan hanya ID dokter
            $_SESSION['success'] = 'Login berhasil.';

            // Debugging: Cek apakah session tersimpan
            var_dump($_SESSION); // Akan menampilkan seluruh data session

            header('Location: /poliklinik-native/src/pages/dokter/dashboard.php');
            exit;
        } else {
            $errors[] = 'Nama atau alamat salah.';
        }
    }

    // Menyimpan errors di session untuk ditampilkan di halaman
    $_SESSION['errors'] = $errors;
    header('Location: /poliklinik-native/src/pages/dokter/dashboard.php');
    exit;
}
?>
