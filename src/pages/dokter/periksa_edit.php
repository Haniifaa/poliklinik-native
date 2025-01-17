<?php
session_start();
include('../../config/koneksi.php');


if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $id = isset($_GET["id"]) ? intval($_GET["id"]) : null;

    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'ID periksa tidak ditemukan.']);
        exit;
    }

    try {
        // Query untuk mendapatkan data periksa
        $stmt = $pdo->prepare("
            SELECT * 
            FROM periksa 
            WHERE id = :id
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $periksa = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($periksa) {
            // Query untuk mendapatkan obat yang terkait dengan periksa ini
            $stmtObat = $pdo->prepare("
                SELECT obat.id, obat.nama_obat, obat.kemasan, obat.harga 
                FROM detail_periksa
                INNER JOIN obat ON detail_periksa.id_obat = obat.id
                WHERE detail_periksa.id_periksa = :id_periksa
            ");
            $stmtObat->bindParam(':id_periksa', $id, PDO::PARAM_INT);
            $stmtObat->execute();
            $obatList = $stmtObat->fetchAll(PDO::FETCH_ASSOC);

            // Gabungkan data periksa dan obat
            $periksa['obat'] = $obatList;

            echo json_encode(['status' => 'success', 'data' => $periksa]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Periksa tidak ditemukan.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}



// Proses untuk memperbarui data periksa dan detail_periksa
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pdo->beginTransaction();  // Mulai transaksi di sini untuk menghindari kesalahan
    try {
        // Ambil data dari form
        $id = $_POST['id'] ?? null; // Kolom id di tabel periksa
        $id_daftar_poli = $_POST['id_daftar_poli'] ?? null;
        $tgl_periksa = $_POST['tgl_periksa'] ?? null;
        $catatan = $_POST['catatan'] ?? null;
        $obatArray = $_POST['id_obat'] ?? [];

        // Validasi data
        if (!$id || !$id_daftar_poli || !$tgl_periksa || !is_array($obatArray) || empty($obatArray)) {
            throw new Exception('Data input tidak lengkap atau tidak valid!');
        }

        // Validasi apakah id ada di tabel periksa
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM periksa WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() == 0) {
            throw new Exception('ID Pemeriksaan tidak ditemukan!');
        }

        // Validasi apakah id_daftar_poli ada di database
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM daftar_poli WHERE id = ?");
        $stmt->execute([$id_daftar_poli]);
        if ($stmt->fetchColumn() == 0) {
            throw new Exception('ID Daftar Poli tidak ditemukan!');
        }

        // Validasi apakah setiap obat ada di database
        foreach ($obatArray as $id_obat) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM obat WHERE id = ?");
            $stmt->execute([$id_obat]);
            if ($stmt->fetchColumn() == 0) {
                throw new Exception("Obat dengan ID {$id_obat} tidak ditemukan!");
            }
        }

        // Biaya dokter tetap
        $biayaDokter = 150000;

        // Hitung total biaya obat
        $totalBiayaObat = 0;
        foreach ($obatArray as $id_obat) {
            $stmt = $pdo->prepare("SELECT harga FROM obat WHERE id = ?");
            $stmt->execute([$id_obat]);
            $obat = $stmt->fetch();
            $totalBiayaObat += $obat['harga'];
        }

        // Hitung total biaya pemeriksaan
        $biayaPeriksa = $biayaDokter + $totalBiayaObat;

        // Update data pemeriksaan
        $stmt = $pdo->prepare("UPDATE periksa SET id_daftar_poli = ?, tgl_periksa = ?, catatan = ?, biaya_periksa = ? WHERE id = ?");
        $stmt->execute([$id_daftar_poli, $tgl_periksa, $catatan, $biayaPeriksa, $id]);

        // Hapus detail_periksa lama sebelum menyimpan yang baru
        $stmt = $pdo->prepare("DELETE FROM detail_periksa WHERE id_periksa = ?");
        $stmt->execute([$id]);

        // Simpan data ke tabel 'detail_periksa' untuk setiap obat
        foreach ($obatArray as $id_obat) {
            $stmt = $pdo->prepare("UPDATE detail_periksa (id_periksa, id_obat) VALUES (?, ?)");
            $stmt->execute([$id, $id_obat]);
        }

        // Commit transaksi jika semua proses berhasil
        $pdo->commit();

        // Redirect dengan pesan sukses
        echo "<script>alert('Data berhasil diperbarui!'); window.location.href = 'periksa_pasien.php';</script>";
        exit();
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi kesalahan
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        // Log error
        error_log("Terjadi kesalahan: " . $e->getMessage(), 0);

        echo "<script>alert('Terjadi kesalahan: " . $e->getMessage() . "'); window.history.back();</script>";
    }
}


?>
