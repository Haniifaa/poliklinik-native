<?php
session_start();
include '../../config/koneksi.php';

if ($pdo) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id_dokter = $_SESSION['id'];
        $hari = $_POST["hari"];
        $jam_mulai = $_POST["jam_mulai"];
        $jam_selesai = $_POST["jam_selesai"];
        $status = $_POST["status"]; // Ambil status

        // Validasi status
        if (!in_array($status, ['Aktif', 'Tidak Aktif'])) {
            echo "Status tidak valid.";
            exit();
        }

        // Cek apakah ada jadwal aktif yang sudah ada untuk dokter
        if ($status == 'Aktif') {
            $check_active_query = "SELECT COUNT(*) as count FROM jadwal_periksa WHERE id_dokter = :id_dokter AND status = 'Aktif'";
            try {
                $check_stmt = $pdo->prepare($check_active_query);
                $check_stmt->bindParam(':id_dokter', $id_dokter);
                $check_stmt->execute();
                $check_stmt->bindColumn('count', $count);
                $check_stmt->fetch();

                // Jika ada jadwal aktif, ubah semua jadwal aktif menjadi Tidak Aktif
                if ($count > 0) {
                    $update_query = "UPDATE jadwal_periksa SET status = 'Tidak Aktif' WHERE id_dokter = :id_dokter AND status = 'Aktif'";
                    $update_stmt = $pdo->prepare($update_query);
                    $update_stmt->bindParam(':id_dokter', $id_dokter);
                    $update_stmt->execute();
                }
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
                exit();
            }
        }

        // Cek apakah jadwal dengan hari yang sama sudah ada
        $existHariQuery = "SELECT COUNT(*) as count FROM jadwal_periksa WHERE id_dokter = :id_dokter AND hari = :hari";
        try {
            $existHariStmt = $pdo->prepare($existHariQuery);
            $existHariStmt->bindParam(':id_dokter', $id_dokter);
            $existHariStmt->bindParam(':hari', $hari);
            $existHariStmt->execute();
            $existHariStmt->bindColumn('count', $existHari);
            $existHariStmt->fetch();

            if ($existHari > 0) {
                echo "<script>
                        alert('Hari sudah tersedia!');
                        window.location.href = '../index.php';
                      </script>";
                exit();
            }

            // Jika tidak ada jadwal pada hari yang sama, tambahkan jadwal
            $query = "INSERT INTO jadwal_periksa (id_dokter, hari, jam_mulai, jam_selesai, status) VALUES (:id_dokter, :hari, :jam_mulai, :jam_selesai, :status)";
            $stmt = $pdo->prepare($query);

            // Bind parameter ke statement PDO
            $stmt->bindParam(':id_dokter', $id_dokter);
            $stmt->bindParam(':hari', $hari);
            $stmt->bindParam(':jam_mulai', $jam_mulai);
            $stmt->bindParam(':jam_selesai', $jam_selesai);
            $stmt->bindParam(':status', $status);

            // Eksekusi query
            if ($stmt->execute()) {
                echo "<script>
                        alert('Jadwal berhasil ditambahkan!');
                        window.location.href = '../dokter/jadwal_periksa.php';
                      </script>";
                exit();
            } else {
                echo "Error: Gagal menambahkan jadwal.";
            }
        } catch (PDOException $e) {
            // Tangani exception jika ada kesalahan dalam query
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Invalid request method";
    }
}
?>
