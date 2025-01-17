<?php
include '../../config/koneksi.php';


if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $id = isset($_GET["id"]) ? intval($_GET["id"]) : null;

    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'ID poli tidak ditemukan.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM poli WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $poli = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($poli) {
            echo json_encode(['status' => 'success', 'poli' => $poli]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Poli tidak ditemukan.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST["id"]) ? intval($_POST["id"]) : null;
    $nama_poli = isset($_POST["nama_poli"]) ? trim($_POST["nama_poli"]) : '';
    $keterangan = isset($_POST["keterangan"]) ? trim($_POST["keterangan"]) : '';

    if (!$id || !$nama_poli || !$keterangan) {
        ?>
        <script>
            alert("Semua field harus diisi!")
            window.location.href = "../index.php"
        </script>
        <?php
        exit();
    }

    try {
        $query = "UPDATE poli SET nama_poli = :nama_poli, keterangan = :keterangan WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':nama_poli', $nama_poli, PDO::PARAM_STR);
        $stmt->bindParam(':keterangan', $keterangan, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            ?>
            <script>
                alert("Data poli berhasil diubah!")
                window.location.href = "../admin/poli.php"
            </script>
            <?php
            exit();
        } else {
            ?>
            <script>
                alert("Gagal mengubah data poli.")
                window.location.href = "../admin/poli.php"
            </script>
            <?php
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
