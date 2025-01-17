<?php
include_once("../../config/koneksi.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // Validasi input
    $errors = [];
    if (empty($email)) {
        $errors[] = "Email wajib diisi.";
    }
    if (empty($password)) {
        $errors[] = "Password wajib diisi.";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }
    
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: login.php");
        exit;
    }

    try {
        // Cek kredensial di database menggunakan PDO
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_email'] = $email;
            $_SESSION['success'] = "Login berhasil.";
            header('Location: /poliklinik-native/src/pages/admin/dashboard.php');
            exit;
        } else {
            $_SESSION['errors'] = ["Email atau password salah."];
            header("Location: login.php");
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['errors'] = ["Terjadi kesalahan: " . $e->getMessage()];
        header("Location: login.php");
        exit;
    }
}
?>
