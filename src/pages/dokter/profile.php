<?php 
session_start();
include_once("../../config/koneksi.php");
ob_start();

if (!isset($_SESSION['dokter'])) {
    header('Location: login.php');
    exit;
}

// Pastikan koneksi database tersedia
if (!isset($pdo) || !$pdo instanceof PDO) {
    die("Koneksi database tidak tersedia.");
}

// Ambil ID dokter dari sesi
$dokterId = $_SESSION['dokter']['id'] ?? null;

if (!$dokterId) {
    $_SESSION['error'] = "Session dokter tidak valid.";
    header('Location: login.php');
    exit;
}

// Query untuk mengambil data dokter
$queryDokter = "SELECT dokter.*, poli.nama_poli FROM dokter 
                LEFT JOIN poli ON dokter.id_poli = poli.id 
                WHERE dokter.id = ?";
$stmt = $pdo->prepare($queryDokter);
$stmt->execute([$dokterId]);
$dokter = $stmt->fetch(PDO::FETCH_ASSOC);

// Jika data dokter tidak ditemukan
if (!$dokter) {
    $_SESSION['error'] = "Data dokter tidak ditemukan.";
    header('Location: login.php');
    exit;
}

// Ambil semua data poli
$queryPoli = "SELECT * FROM poli";
$stmt = $pdo->query($queryPoli);
$poli = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>


<div class="max-w-4xl mx-auto mt-10">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-4">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <div class="flex items-center justify-center mb-6">
        <div class="relative">
            <img src="default-profile.jpg"
                alt="Foto Profil Dokter"
                class="w-32 h-32 rounded-full shadow-md object-cover">
            <button
                class="absolute bottom-0 right-0 bg-blue-500 text-white p-2 rounded-full hover:bg-blue-600 transition duration-300"
                onclick="document.getElementById('photo').click();">
                <i class="fas fa-camera"></i>
            </button>
        </div>
    </div>

    <form id="dokterForm" action="profile_edit.php" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-md">
        <input type="file" id="photo" name="photo" class="hidden" accept="image/*">

        <div class="mb-4">
            <label for="nama" class="block text-gray-700 font-semibold">Nama:</label>
            <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($dokter['nama'] ?? ''); ?>"
                class="w-full mt-2 border-gray-300 rounded-md focus:ring focus:ring-blue-200" disabled>
        </div>

        <div class="mb-4">
            <label for="alamat" class="block text-gray-700 font-semibold">Alamat:</label>
            <input type="text" id="alamat" name="alamat" value="<?= htmlspecialchars($dokter['alamat'] ?? ''); ?>"
                class="w-full mt-2 border-gray-300 rounded-md focus:ring focus:ring-blue-200" disabled>
        </div>

        <div class="mb-4">
            <label for="no_hp" class="block text-gray-700 font-semibold">No HP:</label>
            <input type="text" id="no_hp" name="no_hp" value="<?= htmlspecialchars($dokter['no_hp'] ?? ''); ?>"
                class="w-full mt-2 border-gray-300 rounded-md focus:ring focus:ring-blue-200" disabled>
        </div>

        <div class="mb-4">
            <label for="id_poli" class="block text-gray-700 font-semibold">Poli:</label>
            <select id="id_poli" name="id_poli" class="w-full mt-2 border-gray-300 rounded-md focus:ring focus:ring-blue-200" disabled>
                <option value="" disabled>Pilih Poli</option>
                <?php foreach ($poli as $item): ?>
                    <option value="<?= $item['id']; ?>" <?= isset($dokter['id_poli']) && $dokter['id_poli'] == $item['id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($item['nama_poli']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="button"
            id="editButton"
            class="w-full mb-4 bg-purple-400 text-white py-2 px-4 rounded-md hover:bg-purple-600 transition duration-300"
            onclick="enableFormFields()">
            Edit Profil
        </button>
        <button type="submit"
            id="saveButton"
            class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 transition duration-300 hidden">
            Simpan
        </button>
    </form>
</div>

<script>
    function enableFormFields() {
        document.querySelectorAll('#dokterForm input, #dokterForm select').forEach(field => {
            field.disabled = false;
        });
        document.getElementById('saveButton').classList.remove('hidden');
        document.getElementById('editButton').classList.add('hidden');
    }
</script>

<?php
$content = ob_get_clean();
include('../../components/layout_dokter.php');
?>

