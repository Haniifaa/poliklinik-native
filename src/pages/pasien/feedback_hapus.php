<?php
include('../../config/koneksi.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id_feedback = $_GET['id'];

    // Query untuk menghapus feedback
    $query = "DELETE FROM feedback WHERE id = ?";
    $stmt = $pdo->prepare($query);

    if ($stmt->execute([$id_feedback])) {
        // Berhasil dihapus
        header('Location: ../pasien/poli.php?message=Feedback berhasil dihapus.');
        exit;
    } else {
        // Gagal dihapus
        header('Location: ../pasien/poli.php?message=Gagal menghapus feedback.');
        exit;
    }
} else {
    // Arahkan kembali jika akses tidak valid
    header('Location: ../pasien/poli.php');
    exit;
}
