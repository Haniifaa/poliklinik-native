<?php
include '../../config/koneksi.php';

if ($pdo) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Ambil data dari form
        $nama_obat = $_POST["nama_obat"];
        $kemasan = $_POST["kemasan"];
        $harga = $_POST["harga"];

        // Query untuk menambah data obat
        $query = "INSERT INTO obat (nama_obat, kemasan, harga) VALUES (:nama_obat, :kemasan, :harga)";

        try {
            // Persiapkan statement PDO
            $stmt = $pdo->prepare($query);

            // Bind parameter ke statement PDO
            $stmt->bindParam(':nama_obat', $nama_obat);
            $stmt->bindParam(':kemasan', $kemasan);
            $stmt->bindParam(':harga', $harga);

            // Eksekusi query
            if ($stmt->execute()) {
?>
                <script>
                    alert("Data obat berhasil ditambahkan!");
                    window.location.href = "../admin/obat.php";
                </script>
<?php
                exit();
            } else {
                echo "Error: Gagal menambahkan data obat.";
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
