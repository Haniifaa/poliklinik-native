<?php
session_start();
include('../../config/koneksi.php');
profileEditOrUpdate();

function profileEditOrUpdate() {
    global $pdo;

    if (!isset($_SESSION['dokter'])) {
        header('Location: login.php');
        exit;
    }

    $dokterId = $_SESSION['dokter']['id']; // Ambil ID dokter dari sesi

    // Proses jika formulir dikirimkan
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nama = trim($_POST['nama'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $no_hp = trim($_POST['no_hp'] ?? '');
        $id_poli = trim($_POST['id_poli'] ?? '');

        // Validasi input
        $errors = [];
        if (empty($nama)) $errors[] = 'Nama harus diisi.';
        if (empty($alamat)) $errors[] = 'Alamat harus diisi.';
        if (empty($no_hp)) $errors[] = 'No HP harus diisi.';
        if (empty($id_poli)) $errors[] = 'Poli harus dipilih.';

        // Cek jika poli ada di database
        $queryPoli = "SELECT * FROM poli WHERE id = ?";
        $stmt = $pdo->prepare($queryPoli);
        $stmt->execute([$id_poli]);
        if ($stmt->rowCount() === 0) $errors[] = 'Poli tidak valid.';

        // Jika tidak ada error, perbarui data
        if (empty($errors)) {
            $queryUpdate = "UPDATE dokter SET nama = ?, alamat = ?, no_hp = ?, id_poli = ? WHERE id = ?";
            $stmt = $pdo->prepare($queryUpdate);
            $stmt->execute([$nama, $alamat, $no_hp, $id_poli, $dokterId]);

            $_SESSION['success'] = 'Profil dokter berhasil diperbarui.';
            header('Location: ../dokter/profile.php');
            exit;
        }

        // Simpan pesan error di sesi jika ada
        $_SESSION['errors'] = $errors;
        header('Location: ../dokter/profile.php');
        exit;
    }

    // Jika bukan POST, langsung ke halaman profil
    header('Location: ../dokter/profile.php');
    exit;

}
