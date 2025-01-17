<?php
session_start();
include('../../config/koneksi.php');
// header('Content-Type: application/json');

$response = ['success' => false];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
        $id = $_GET['id']; // ID feedback
        $query = "
            SELECT f.id, f.ulasan, f.rating, p.id AS id_periksa
            FROM feedback f
            JOIN periksa p ON f.id_periksa = p.id
            WHERE f.id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['id' => $id]); // Gunakan named parameter
        $feedbackData = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($feedbackData) {
            $response = [
                'success' => true,
                'id' => $feedbackData['id'], // ID feedback
                'id_periksa' => $feedbackData['id_periksa'], // ID periksa terkait
                'ulasan' => $feedbackData['ulasan'],
                'rating' => $feedbackData['rating'],
            ];
        } else {
            $response['message'] = 'Feedback tidak ditemukan.';
        }
        echo json_encode($response);
        exit;
    }
    

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'] ?? null; // ID feedback
        $ulasan = $_POST['ulasan'] ?? null;
        $rating = $_POST['rating'] ?? null;
    
        // Validasi data
        if ($id && $ulasan && is_numeric($rating) && $rating >= 1 && $rating <= 5) {
            $query = "UPDATE feedback SET ulasan = ?, rating = ? WHERE id = ?";
            $stmt = $pdo->prepare($query);
    
            if ($stmt->execute([$ulasan, $rating, $id])) {
                // Menampilkan alert menggunakan JavaScript
                echo "<script>
                    alert('Feedback berhasil diperbarui.');
                    window.location.href = '../pasien/poli.php';
                </script>";
                exit;
            } else {
                $response['message'] = 'Gagal memperbarui feedback.';
            }
        } else {
            $response['message'] = 'Data tidak lengkap atau rating tidak valid.';
        }
    
        echo json_encode($response);
        exit;
    }
    
    
    

    $response['message'] = 'Permintaan tidak valid.';
} catch (Exception $e) {
    $response['message'] = 'Terjadi kesalahan: ' . $e->getMessage();
}

?>
