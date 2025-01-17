<?php
include '../../config/koneksi.php';

if ($pdo) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nama = $_POST["nama"];
        $alamat = $_POST["alamat"];
        $no_ktp = $_POST["no_ktp"];
        $no_hp = $_POST["no_hp"];

        // Cek apakah semua field yang diperlukan sudah diisi
        if (empty($nama) || empty($alamat) || empty($no_ktp) || empty($no_hp)) {
            echo "<script>
                    alert('Semua field harus diisi!');
                    window.history.back();
                  </script>";
            exit();
        }

        try {
            // Ambil tanggal saat ini dalam format YYYYMM
            $dateNow = date('Ym');
            
            // Ambil jumlah pasien yang terdaftar pada bulan dan tahun ini
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM pasien WHERE YEAR(created_at) = YEAR(CURRENT_DATE()) AND MONTH(created_at) = MONTH(CURRENT_DATE())");
            $stmt->execute();
            $lastQueueNumber = $stmt->fetchColumn(); // Menghitung jumlah pasien yang ada

            // Generate nomor RM
            $newQueueNumber = $lastQueueNumber + 1;
            $no_rm = $dateNow . "-" . str_pad($newQueueNumber, 3, '0', STR_PAD_LEFT);

            // Periksa apakah nomor RM sudah ada di database
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM pasien WHERE no_rm = :no_rm");
            $stmtCheck->bindParam(':no_rm', $no_rm);
            $stmtCheck->execute();
            $isDuplicate = $stmtCheck->fetchColumn();

            // Jika nomor RM sudah ada, increment sampai unik
            while ($isDuplicate) {
                $newQueueNumber++;
                $no_rm = $dateNow . "-" . str_pad($newQueueNumber, 3, '0', STR_PAD_LEFT);
                $stmtCheck->execute(); // Periksa kembali
                $isDuplicate = $stmtCheck->fetchColumn();
            }

            // Query untuk memasukkan data pasien
            $query = "INSERT INTO pasien (nama, alamat, no_ktp, no_hp, no_rm) 
                      VALUES (:nama, :alamat, :no_ktp, :no_hp, :no_rm)";
            
            // Persiapkan query
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':nama', $nama);
            $stmt->bindParam(':alamat', $alamat);
            $stmt->bindParam(':no_ktp', $no_ktp);
            $stmt->bindParam(':no_hp', $no_hp);
            $stmt->bindParam(':no_rm', $no_rm);

            // Eksekusi query
            if ($stmt->execute()) {
                echo "<script>
                        alert('Data pasien berhasil ditambahkan!');
                        window.location.href = '../admin/pasien.php';
                      </script>";
                exit();
            } else {
                echo "Error: " . $stmt->errorInfo()[2];
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Failed to include koneksi.php";
    }
} else {
    echo "Gagal terhubung ke database!";
}
?>
