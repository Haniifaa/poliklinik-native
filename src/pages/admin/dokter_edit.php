<?php
include '../../config/koneksi.php';

$response = array();

// Mendapatkan data dokter (GET)
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $id = isset($_GET["id"]) ? $_GET["id"] : '';

    if (empty($id)) {
        $response['status'] = 'error';
        $response['message'] = 'ID dokter tidak ditemukan.';
        echo json_encode($response);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM dokter WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $dokter = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dokter) {
            $response['status'] = 'error';
            $response['message'] = 'Dokter tidak ditemukan.';
            echo json_encode($response);
            exit;
        }

        $response['status'] = 'success';
        $response['dokter'] = $dokter;
        echo json_encode($response);
    } catch (PDOException $e) {
        $response['status'] = 'error';
        $response['message'] = 'Error: ' . $e->getMessage();
        echo json_encode($response);
    }
}

// Proses Update Data Dokter (POST atau PUT)
if ($_SERVER["REQUEST_METHOD"] == "POST" || $_SERVER["REQUEST_METHOD"] == "PUT") {
    // Ambil data dari POST
    $id = isset($_POST["id"]) ? $_POST["id"] : '';
    $nama = isset($_POST["nama"]) ? $_POST["nama"] : '';
    $alamat = isset($_POST["alamat"]) ? $_POST["alamat"] : '';
    $no_hp = isset($_POST["no_hp"]) ? $_POST["no_hp"] : '';
    $id_poli = isset($_POST["id_poli"]) ? $_POST["id_poli"] : '';

    // Validasi input, pastikan ID dan data dokter lainnya tidak kosong
    if (empty($id) || empty($nama) || empty($alamat) || empty($no_hp) || empty($id_poli)) {
        $response['status'] = 'error';
        $response['message'] = 'Semua data harus diisi.';
        echo json_encode($response);
        exit;
    }

    try {
        // Cari dokter berdasarkan ID
        $stmt = $pdo->prepare("SELECT * FROM dokter WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $dokter = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dokter) {
            $response['status'] = 'error';
            $response['message'] = 'Dokter tidak ditemukan.';
            echo json_encode($response);
            exit;
        }

        // Mengupdate data dokter
        $query = "UPDATE dokter SET
                  nama = :nama,
                  alamat = :alamat,
                  no_hp = :no_hp,
                  id_poli = :id_poli
                  WHERE id = :id";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':alamat', $alamat);
        $stmt->bindParam(':no_hp', $no_hp);
        $stmt->bindParam(':id_poli', $id_poli);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'Data dokter berhasil diubah!';
            header("Location: dokter.php?status=success&message=Data pasien berhasil diubah!");
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Gagal mengubah data dokter.';
            echo json_encode($response);
        }
    } catch (PDOException $e) {
        $response['status'] = 'error';
        $response['message'] = 'Error: ' . $e->getMessage();
        echo json_encode($response);
    }
}
?>
