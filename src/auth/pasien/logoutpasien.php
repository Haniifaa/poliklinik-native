<?php
session_start();
require_once 'db_connection.php'; // Pastikan mengganti dengan file koneksi ke database

function loginpasien()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validasi input
        $nama = $_POST['nama'] ?? '';
        $alamat = $_POST['alamat'] ?? '';
        $errors = [];

        if (empty($nama)) {
            $errors[] = 'Nama harus diisi.';
        }

        if (empty($alamat)) {
            $errors[] = 'Alamat harus diisi.';
        }

        // Jika ada error, tampilkan pesan error
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: login.php'); // Kembali ke halaman login dengan error
            exit();
        }

        // Cek apakah pasien terdaftar di database
        $stmt = $conn->prepare("SELECT * FROM pasien WHERE nama = ? AND alamat = ?");
        $stmt->bind_param("ss", $nama, $alamat);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Ambil data pasien
            $pasien = $result->fetch_assoc();

            // Simpan data pasien ke sesi
            $_SESSION['pasien'] = $pasien;

            $_SESSION['success'] = 'Login berhasil.';
            header('Location: dashboard.php'); // Redirect ke halaman dashboard
            exit();
        } else {
            $_SESSION['errors'] = ['Nama atau alamat salah.'];
            header('Location: login.php'); // Kembali ke halaman login dengan pesan error
            exit();
        }
    }
}
?>
