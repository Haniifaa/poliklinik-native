<?php
include '../../config/koneksi.php';

// Pastikan koneksi PDO berhasil
if ($pdo) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Ambil data dari form
        $nama = $_POST["nama"];
        $alamat = $_POST["alamat"];
        $no_hp = $_POST["no_hp"];
        $id_poli = $_POST["id_poli"];

        // Query untuk menambah data dokter
        $query = "INSERT INTO dokter (nama, alamat, no_hp, id_poli) VALUES (:nama, :alamat, :no_hp, :id_poli)";

        try {
            // Persiapkan statement PDO
            $stmt = $pdo->prepare($query);

            // Bind parameter ke statement PDO
            $stmt->bindParam(':nama', $nama);  // Menggunakan variabel yang sudah didefinisikan
            $stmt->bindParam(':alamat', $alamat);  // Menggunakan variabel yang sudah didefinisikan
            $stmt->bindParam(':no_hp', $no_hp);  // Menggunakan variabel yang sudah didefinisikan
            $stmt->bindParam(':id_poli', $id_poli);  // Menggunakan variabel yang sudah didefinisikan

            // Eksekusi query
            if ($stmt->execute()) {
?>
                <script>
                    alert("Data dokter berhasil ditambahkan!");
                    window.location.href = "../admin/dokter.php";
                </script>
<?php
                exit();
            } else {
                echo "Error: Gagal menambahkan data dokter.";
            }
        } catch (PDOException $e) {
            // Tangani exception jika ada kesalahan dalam query
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Failed to include koneksi.php";
    }
} else {
    echo "Gagal terhubung ke database.";
}
?>
