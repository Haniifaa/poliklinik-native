<?php
session_start();
include_once("../../config/koneksi.php");

// Fungsi untuk mendapatkan nomor antrean terakhir
function getQueQue($pdo, $id_jadwal)
{
    $query = "SELECT MAX(no_antrian) as max_queue FROM daftar_poli WHERE id_jadwal = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id_jadwal]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['max_queue'] ?? 0;
}

// Pastikan sesi pasien tersedia
if (!isset($_SESSION['pasien']) || !isset($_SESSION['pasien']['id'])) {
    echo "Anda harus login terlebih dahulu.";
    exit();
}

// Ambil ID pasien dari sesi
$id_pasien = $_SESSION['pasien']['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validasi input
    if (!isset($_POST["id_jadwal"]) || !isset($_POST["keluhan"])) {
        echo "Data tidak lengkap. Silakan coba lagi.";
        exit();
    }

    $id_jadwal = $_POST["id_jadwal"];
    $keluhan = $_POST["keluhan"];
    $no_antrian = getQueQue($pdo, $id_jadwal) + 1;

    try {
        // Mulai transaksi
        $pdo->beginTransaction();

        // Insert ke daftar_poli
        $sql = "INSERT INTO daftar_poli (id_pasien, id_jadwal, keluhan, no_antrian) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_pasien, $id_jadwal, $keluhan, $no_antrian]);

        $id_daftar_poli = $pdo->lastInsertId();

        // Commit transaksi
        $pdo->commit();
?>
        <script>
            alert("Berhasil Daftar Poli");
        </script>
        <meta http-equiv='refresh' content='0; url=../pasien/poli.php'>
<?php
        exit();
    } catch (Exception $e) {
        // Rollback jika ada error
        $pdo->rollBack();
        echo "Terjadi kesalahan: " . $e->getMessage();
    }
}
?>
