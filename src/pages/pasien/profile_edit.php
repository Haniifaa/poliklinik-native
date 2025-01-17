<?php
session_start();
include('../../config/koneksi.php');
profileEditOrUpdate();

function profileEditOrUpdate() {
    global $pdo;

    if (!isset($_SESSION['pasien'])) {
        header('Location: login.php');
        exit;
    }

    $pasienId = $_SESSION['pasien']['id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nama = trim($_POST['nama'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $no_ktp = trim($_POST['no_ktp'] ?? '');
        $no_hp = trim($_POST['no_hp'] ?? '');

        $errors = [];
        if (empty($nama)) $errors[] = 'Nama harus diisi.';
        if (empty($alamat)) $errors[] = 'Alamat harus diisi.';
        if (empty($no_ktp) || !is_numeric($no_ktp) || strlen($no_ktp) !== 16) $errors[] = 'No KTP harus berupa 16 digit angka.';
        if (empty($no_hp) || !is_numeric($no_hp)) $errors[] = 'No HP harus berupa angka.';

        if (empty($errors)) {
            $queryUpdate = "UPDATE pasien SET nama = ?, alamat = ?, no_ktp = ?, no_hp = ? WHERE id = ?";
            $stmt = $pdo->prepare($queryUpdate);
            $stmt->execute([$nama, $alamat, $no_ktp, $no_hp, $pasienId]);

            $_SESSION['success'] = 'Profil pasien berhasil diperbarui.';
            header('Location: ../pasien/profile.php');
            exit;
        }

        $_SESSION['errors'] = $errors;
        header('Location: ../pasien/profile.php');
        exit;
    }
    header('Location: ../pasien/profile.php');
    exit;
}