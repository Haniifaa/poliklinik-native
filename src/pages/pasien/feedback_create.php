<?php
session_start();
include('../../config/koneksi.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_periksa = $_POST['id_periksa'];
    $rating = $_POST['rating'];
    $ulasan = $_POST['ulasan'];

    $query = "INSERT INTO feedback (id_periksa, rating, ulasan) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id_periksa, $rating, $ulasan]);

    echo json_encode(['success' => true, 'message' => 'Feedback berhasil ditambahkan.']);
    exit;
}

// Logika untuk permintaan GET (menampilkan form HTML atau data)
if (isset($_GET['id_periksa'])) {
    $id_periksa = $_GET['id_periksa'];

    // Query untuk mendapatkan data feedback berdasarkan id_periksa
    $query = "SELECT rating, ulasan FROM feedback WHERE id_periksa = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id_periksa]);
    $feedback = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($feedback) {
        // Mengembalikan data feedback dalam format JSON
        echo json_encode($feedback);
    } else {
        echo json_encode(['error' => 'Data feedback tidak ditemukan.']);
    }
    exit;
}
