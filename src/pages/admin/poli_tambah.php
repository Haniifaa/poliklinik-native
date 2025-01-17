<?php
include '../../config/koneksi.php';

if ($pdo) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Ambil data dari form
        $nama_poli = $_POST["nama_poli"];
        $keterangan = $_POST["keterangan"];

        // Query untuk menambah data poli
        $query = "INSERT INTO poli (nama_poli, keterangan) VALUES (:nama_poli, :keterangan)";

        try {
            // Persiapkan statement PDO
            $stmt = $pdo->prepare($query);

            // Bind parameter ke statement PDO
            $stmt->bindParam(':nama_poli', $nama_poli);
            $stmt->bindParam(':keterangan', $keterangan);

            // Eksekusi query
            if ($stmt->execute()) {
?>
                <script>
                    alert("Data poli berhasil ditambahkan!");
                    window.location.href = "../admin/poli.php";
                </script>
<?php
                exit();
            } else {
                echo "Error: Gagal menambahkan data poli.";
            }
        } catch (PDOException $e) {
            // Tangani exception jika ada kesalahan dalam query
            echo "Error: " . $e->getMessage();
        }
    }
} else {
    echo "Failed to include koneksi.php";
}
?>
