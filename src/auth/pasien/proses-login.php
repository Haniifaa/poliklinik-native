<?php
session_start();
include_once("../../config/koneksi.php"); // Pastikan koneksi.php sudah benar

function loginpasien()
{
    global $pdo; // Gunakan variabel $pdo yang ada di koneksi.php

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validasi input
        $nama = $_POST['nama'] ?? '';
        $alamat = $_POST['alamat'] ?? '';
        $errors = [];

        if (empty($nama)) {
            $errors[] = 'Nama harus diisi.';
        }

        if (empty($alamat)) {
            $errors[] = 'Password harus diisi.';
        }

        // Jika ada error, tampilkan pesan error dan kembali ke halaman login
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: /poliklinik-native/src/pages/pasien/login.php'); // Arahkan ke halaman login
            exit();
        }

        // Cek apakah pasien terdaftar di database
        try {
            $stmt = $pdo->prepare("SELECT * FROM pasien WHERE nama = ? AND alamat = ?");
            $stmt->execute([$nama, $alamat]);

            if ($stmt->rowCount() > 0) {
                // Ambil data pasien
                $pasien = $stmt->fetch(PDO::FETCH_ASSOC);

                $_SESSION['id_pasien'] = $pasien['id']; // Menyimpan ID pasien
                $_SESSION['pasien'] = $pasien; // Menyimpan seluruh data pasien jika perlu
                
                $_SESSION['success'] = 'Login berhasil.';
                header('Location: /poliklinik-native/src/pages/pasien/dashboard.php'); // Arahkan ke dashboard setelah login berhasil
                exit();
            } else {
                $_SESSION['errors'] = ['Nama atau alamat salah.'];
                header('Location: /poliklinik-native/src/pages/pasien/login.php'); // Arahkan ke halaman login jika login gagal
                exit();
            }
        } catch (PDOException $e) {
            // Jika ada error saat query
            $_SESSION['errors'] = ['Terjadi kesalahan saat mengakses database.'];
            header('Location: /poliklinik-native/src/pages/pasien/login.php'); // Arahkan ke halaman login jika ada error
            exit();
        }
    }
}

loginpasien(); // Panggil fungsi loginpasien
?>
