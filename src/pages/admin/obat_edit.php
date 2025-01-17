<?php
include '../../config/koneksi.php';

// Mendapatkan data obat berdasarkan ID (GET)
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $id = isset($_GET["id"]) ? intval($_GET["id"]) : null;

    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'ID obat tidak ditemukan.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM obat WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $obat = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($obat) {
            echo json_encode(['status' => 'success', 'obat' => $obat]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Obat tidak ditemukan.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// Memperbarui data obat berdasarkan ID (POST)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = isset($_POST["id"]) ? intval($_POST["id"]) : null;
    $nama_obat = isset($_POST["nama_obat"]) ? trim($_POST["nama_obat"]) : '';
    $kemasan = isset($_POST["kemasan"]) ? trim($_POST["kemasan"]) : '';
    $harga = isset($_POST["harga"]) ? floatval($_POST["harga"]) : 0;

    if (!$id || !$nama_obat || !$kemasan || $harga <= 0) {
        echo "<script>alert('Semua field harus diisi dengan benar.'); window.location.href='admin/obat.php';</script>";
        exit;
    }

    try {
        $query = "UPDATE obat SET nama_obat = :nama_obat, kemasan = :kemasan, harga = :harga WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':nama_obat', $nama_obat);
        $stmt->bindParam(':kemasan', $kemasan);
        $stmt->bindParam(':harga', $harga, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Jika berhasil, arahkan ke halaman admin/obat.php
            header("Location: ../admin/obat.php?success=1");
            exit;
        } else {
            echo "<script>alert('Gagal mengubah data.'); window.location.href='admin/obat.php';</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Error: " . $e->getMessage() . "'); window.location.href='admin/obat.php';</script>";
    }
    exit;
}
?>
