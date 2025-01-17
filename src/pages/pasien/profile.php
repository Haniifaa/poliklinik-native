<?php 
session_start();
include_once("../../config/koneksi.php");
ob_start();

// Pastikan pasien sudah login
if (!isset($_SESSION['pasien'])) {
    header('Location: login.php');
    exit;
}

// Pastikan koneksi database tersedia
if (!isset($pdo) || !$pdo instanceof PDO) {
    die("Koneksi database tidak tersedia.");
}

// Ambil ID pasien dari sesi
$pasienId = $_SESSION['pasien']['id'] ?? null;

if (!$pasienId) {
    $_SESSION['error'] = "Session pasien tidak valid.";
    header('Location: login.php');
    exit;
}

// Query untuk mengambil data pasien
$queryPasien = "SELECT * FROM pasien WHERE id = ?";
$stmt = $pdo->prepare($queryPasien);
$stmt->execute([$pasienId]);
$pasien = $stmt->fetch(PDO::FETCH_ASSOC);

// Jika data pasien tidak ditemukan
if (!$pasien) {
    $_SESSION['error'] = "Data pasien tidak ditemukan.";
    header('Location: login.php');
    exit;
}
?>

<div class="max-w-4xl mx-auto mt-10">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-4">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <form id="pasienForm" action="profile_edit.php" method="POST" class="space-y-3 bg-white p-6 rounded-lg shadow-md">
        <div class="mb-4">
            <label for="no_rm" class="block text-gray-700 font-semibold">Nomor Rekam Medis (No RM):</label>
            <input type="text" id="no_rm" name="no_rm" value="<?= htmlspecialchars($pasien['no_rm'] ?? ''); ?>"
                class="w-full mt-2 border-gray-300 rounded-md focus:ring focus:ring-blue-200 bg-gray-100" readonly>
        </div>
        <div>
            <label for="nama" class="block mb-1 text-sm text-gray-900">Nama Lengkap</label>
            <input type="text" name="nama" id="nama" value="<?= htmlspecialchars($pasien['nama'] ?? ''); ?>"
                class="bg-gray-50 border border-gray-300 text-sm rounded-md focus:ring-blue-500 focus:border-blue-500 block w-full px-2 py-1.5" placeholder="Nama lengkap" disabled>
        </div>
        <div>
            <label for="alamat" class="block mb-1 text-sm text-gray-900">Alamat</label>
            <textarea name="alamat" id="alamat" rows="2"
                class="bg-gray-50 border border-gray-300 text-sm rounded-md focus:ring-blue-500 focus:border-blue-500 block w-full px-2 py-1.5" placeholder="Alamat" disabled><?= htmlspecialchars($pasien['alamat'] ?? ''); ?></textarea>
        </div>
        <div>
            <label for="no_ktp" class="block mb-1 text-sm text-gray-900">No KTP</label>
            <input type="text" name="no_ktp" id="no_ktp" value="<?= htmlspecialchars($pasien['no_ktp'] ?? ''); ?>"
                class="bg-gray-50 border border-gray-300 text-sm rounded-md focus:ring-blue-500 focus:border-blue-500 block w-full px-2 py-1.5" placeholder="Nomor KTP" maxlength="16" disabled>
        </div>
        <div>
            <label for="no_hp" class="block mb-1 text-sm text-gray-900">No HP</label>
            <input type="text" name="no_hp" id="no_hp" value="<?= htmlspecialchars($pasien['no_hp'] ?? ''); ?>"
                class="bg-gray-50 border border-gray-300 text-sm rounded-md focus:ring-blue-500 focus:border-blue-500 block w-full px-2 py-1.5" placeholder="Nomor HP" maxlength="15" disabled>
        </div>
        <button type="button" id="editButton" class="w-full text-white bg-purple-500 hover:bg-purple-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-md text-sm px-4 py-2 text-center" onclick="enableFormFields()">
            Edit Profil
        </button>
        <button type="submit" id="saveButton" class="w-full text-white bg-blue-500 hover:bg-blue-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-md text-sm px-4 py-2 text-center hidden">
            Simpan
        </button>
    </form>
</div>

<script>
    function enableFormFields() {
        document.querySelectorAll('#pasienForm input, #pasienForm textarea').forEach(field => {
            field.disabled = false;
        });
        document.getElementById('saveButton').classList.remove('hidden');
        document.getElementById('editButton').classList.add('hidden');
    }
</script>

<?php
$content = ob_get_clean();
include('../../components/layout_pasien.php');
?>