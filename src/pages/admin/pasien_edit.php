<?php
include '../../config/koneksi.php';

$response = array();

// Mendapatkan data pasien (GET)
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $id = isset($_GET["id"]) ? $_GET["id"] : '';

    if (empty($id)) {
        $response['status'] = 'error';
        $response['message'] = 'ID pasien tidak ditemukan.';
        echo json_encode($response);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM pasien WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $pasien = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pasien) {
            $response['status'] = 'error';
            $response['message'] = 'Pasien tidak ditemukan.';
            echo json_encode($response);
            exit;
        }

        $response['status'] = 'success';
        $response['pasien'] = $pasien;
        echo json_encode($response);

    } catch (PDOException $e) {
        $response['status'] = 'error';
        $response['message'] = 'Error: ' . $e->getMessage();
        echo json_encode($response);
    }
}

// Menangani permintaan POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mendapatkan data yang dikirimkan melalui POST
    $id = $_POST['id'] ?? null;
    $id_periksa = $_POST['id_periksa'] ?? null;
    $ulasan = $_POST['ulasan'] ?? null;
    $rating = $_POST['rating'] ?? null;

    // Menyiapkan array untuk melacak data yang tidak lengkap
    $missingData = [];

    // Memeriksa setiap field dan menambahkan yang kosong ke dalam array $missingData
    if (!$id) {
        $missingData[] = 'id';
    }
    if (!$id_periksa) {
        $missingData[] = 'id_periksa';
    }
    if (!$ulasan) {
        $missingData[] = 'ulasan';
    }
    if (!$rating) {
        $missingData[] = 'rating';
    }

    // Jika ada data yang tidak lengkap
    if (count($missingData) > 0) {
        $response['message'] = 'Data tidak lengkap.';
        $response['missing_data'] = $missingData;
    } else {
        // Jika semua data lengkap, melakukan update
        $query = "UPDATE feedback SET ulasan = ?, rating = ?, updated_at = NOW() WHERE id = ? AND id_periksa = ?";
        $stmt = $pdo->prepare($query);

        if ($stmt->execute([$ulasan, $rating, $id, $id_periksa])) {
            $response['success'] = true;
            $response['message'] = 'Feedback berhasil diperbarui.';
        } else {
            $response['message'] = 'Gagal memperbarui feedback.';
        }
    }

    // Mengembalikan respons dalam format JSON
    echo json_encode($response);
    exit;
}

// Jika request bukan GET atau POST
$response['message'] = 'Permintaan tidak valid.';
echo json_encode($response);
exit;
?>
