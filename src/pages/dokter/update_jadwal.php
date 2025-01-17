<?php
include_once("../../config/koneksi.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $status = $_POST['status'] ?? null;

    if (!$id || !in_array($status, ['Aktif', 'Tidak Aktif'])) {
        echo "<script>
            alert('Input tidak valid.');
            window.location.href = '../admin/jadwal_periksa.php';
        </script>";
        exit();
    }

    try {
        // Cari jadwal berdasarkan ID
        $query = "SELECT * FROM jadwal_periksa WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$id]);
        $jadwal = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$jadwal) {
            echo "<script>
                alert('Jadwal tidak ditemukan.');
                window.location.href = '../admin/jadwal_periksa.php';
            </script>";
            exit();
        }

        // Perbarui status jadwal
        $updateQuery = "UPDATE jadwal_periksa SET status = ? WHERE id = ?";
        $updateStmt = $pdo->prepare($updateQuery);
        $isUpdated = $updateStmt->execute([$status, $id]);

        if ($isUpdated) {
            echo "<script>
                alert('Status berhasil diperbarui.');
                window.location.href = '../dokter/jadwal_periksa.php';
            </script>";
        } else {
            echo "<script>
                alert('Gagal menyimpan status baru.');
                window.location.href = '../dokter/jadwal_periksa.php';
            </script>";
        }
    } catch (Exception $e) {
        echo "<script>
            alert('Terjadi kesalahan: " . addslashes($e->getMessage()) . "');
            window.location.href = '../dokter/jadwal_periksa.php';
        </script>";
    }
} else {
    echo "<script>
        alert('Metode permintaan tidak diizinkan.');
        window.location.href = '../dokter/jadwal_periksa.php';
    </script>";
}
