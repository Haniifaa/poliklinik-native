<?php
session_start();
include_once("../../config/koneksi.php"); // Pastikan koneksi.php sudah benar

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $nama = $_POST['nama'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $no_ktp = $_POST['no_ktp'] ?? '';
    $no_hp = $_POST['no_hp'] ?? '';

    // Validasi input
    $errors = [];

    if (empty($nama)) {
        $errors[] = 'Nama pasien harus diisi.';
    }

    if (empty($alamat)) {
        $errors[] = 'Alamat pasien harus diisi.';
    }

    if (empty($no_ktp)) {
        $errors[] = 'Nomor KTP harus diisi.';
    } else {
        // Cek apakah nomor KTP sudah terdaftar
        $stmt = $pdo->prepare("SELECT * FROM pasien WHERE no_ktp = ?");
        $stmt->bindValue(1, $no_ktp, PDO::PARAM_STR); // Menggunakan bindValue dengan PDO
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $errors[] = 'Nomor KTP sudah terdaftar.';
        }
    }

    if (empty($no_hp)) {
        $errors[] = 'Nomor HP harus diisi.';
    }

    // Jika ada error, tampilkan pesan error dan kembali ke form
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header('Location: /poliklinik-native/src/auth/pasien/daftar.php'); // Arahkan ke halaman daftar
        exit();
    }

    // Generate nomor RM
    $currentYearMonth = date('Ym'); // TahunBulan saat ini
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total_pasien FROM pasien WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalPasien = $result['total_pasien'];

    // Format no_rm (misalnya 202412-001)
    $noRm = $currentYearMonth . '-' . str_pad($totalPasien + 1, 3, '0', STR_PAD_LEFT);

    // Pastikan no_rm tidak duplikat
    while (true) {
        $stmt = $pdo->prepare("SELECT * FROM pasien WHERE no_rm = ?");
        $stmt->bindValue(1, $noRm, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            break;
        } else {
            $totalPasien++;
            $noRm = $currentYearMonth . '-' . str_pad($totalPasien + 1, 3, '0', STR_PAD_LEFT);
        }
    }

    // Simpan data pasien ke database
    try {
        $stmt = $pdo->prepare("INSERT INTO pasien (nama, alamat, no_ktp, no_hp, no_rm) VALUES (?, ?, ?, ?, ?)");
        $stmt->bindValue(1, $nama, PDO::PARAM_STR);
        $stmt->bindValue(2, $alamat, PDO::PARAM_STR);
        $stmt->bindValue(3, $no_ktp, PDO::PARAM_STR);
        $stmt->bindValue(4, $no_hp, PDO::PARAM_STR);
        $stmt->bindValue(5, $noRm, PDO::PARAM_STR);

        if ($stmt->execute()) {
            // Set session success message
            $_SESSION['success'] = 'Pendaftaran berhasil. Silakan lanjutkan untuk login.';
            header('Location: /poliklinik-native/src/auth/pasien/login.php'); // Arahkan ke halaman login
            exit();
        } else {
            throw new Exception('Gagal melakukan pendaftaran.');
        }
    } catch (Exception $e) {
        $_SESSION['errors'] = ['message' => 'Gagal melakukan pendaftaran: ' . $e->getMessage()];
        header('Location: /poliklinik-native/src/auth/pasien/daftar.php'); // Arahkan ke halaman daftar
        exit();
    }
}
?>
