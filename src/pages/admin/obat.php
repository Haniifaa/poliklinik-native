<?php

session_start();
include_once("../../config/koneksi.php");
ob_start();

// Ambil parameter search jika ada
$search = isset($_GET['search']) ? $_GET['search'] : '';

if (isset($_POST['method']) && $_POST['method'] == 'DELETE' && isset($_GET['id'])) {
    $id = $_GET['id']; // Ambil ID yang akan dihapus

    // Query untuk menghapus data obat berdasarkan ID
    $queryDelete = "DELETE FROM obat WHERE id = :id";
    $stmtDelete = $pdo->prepare($queryDelete);
    $stmtDelete->bindValue(':id', $id);
    
    // Eksekusi query
    if ($stmtDelete->execute()) {
        $_SESSION['success'] = 'Obat berhasil dihapus.';
    } else {
        $_SESSION['error'] = 'Gagal menghapus obat.';
    }

    // Redirect setelah penghapusan
    header("Location: obat.php");
    exit;
}

// Query untuk mengambil data obat berdasarkan pencarian
$query = "SELECT * FROM obat WHERE nama_obat LIKE :search LIMIT 10";  // Sesuaikan dengan kolom dan tabel di database
$stmt = $pdo->prepare($query);
$stmt->bindValue(':search', '%' . $search . '%');
$stmt->execute();

// Ambil hasil query
$obat = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="p-4 mt-14">
    <!-- Display Alert Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div id="successAlert" class="mb-4 p-4 bg-green-500 text-white rounded-md">
            <?= $_SESSION['success']; ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-4 p-4 bg-red-500 text-white rounded-md">
            <?= $_SESSION['error']; ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Judul H1 -->
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Data Obat</h1>

    <!-- Bar untuk Search dan Tambah Obat -->
    <div class="flex justify-between items-center mb-4">
        <!-- Input Pencarian -->
        <form method="GET" action="admin-masterdata-obat.php" class="relative">
            <input
                type="text"
                name="search"
                id="search"
                value="<?= htmlspecialchars($search); ?>"
                class="block p-2 pl-10 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Cari obat..."
            />
            <button type="submit" class="absolute top-1/2 left-2 w-5 h-5 text-gray-400 transform -translate-y-1/2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0a7.5 7.5 0 1110.15-10.15 7.5 7.5 0 01-10.15 10.15z" />
                </svg>
            </button>
        </form>

        <!-- Tombol Tambah Obat -->
        <button
            type="button"
            onclick="openModal('tambahmodal')"
            class="text-white bg-purple-400 hover:bg-purple-500 focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 flex items-center space-x-2"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Obat</span>
        </button>
    </div>

    <!-- Tabel -->
    <div class="relative w-full overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3">Nama</th>
                    <th scope="col" class="px-6 py-3">Kemasan</th>
                    <th scope="col" class="px-6 py-3">Harga</th>
                    <th scope="col" class="px-6 py-3">Created At</th>
                    <th scope="col" class="px-6 py-3">Updated At</th>
                    <th scope="col" class="px-6 py-3">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($obat as $b): ?>
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap"><?= htmlspecialchars($b['nama_obat']); ?></th>
                        <td class="px-6 py-4"><?= htmlspecialchars($b['kemasan']); ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($b['harga']); ?></td>
                        <td style="width: 200px;">
                            <?= $b['created_at'] ? date('d-m-Y H:i:s', strtotime($b['created_at'])) : 'NULL'; ?>
                        </td>
                        <td style="width: 200px;">
                            <?= $b['updated_at'] ? date('d-m-Y H:i:s', strtotime($b['updated_at'])) : 'NULL'; ?>
                        </td>
                        <td class="flex items-center px-6 py-4">
                            <button onclick="editObat(<?= $b['id']; ?>)" class="font-medium text-blue-600 hover:underline" aria-label="Edit Obat">Edit</button>
                            <form action="obat.php?id=<?= $b['id']; ?>" method="POST" onsubmit="return confirm('Yakin ingin menghapus data?')">
                                <input type="hidden" name="method" value="DELETE">
                                <button type="submit" class="font-medium text-red-600 hover:underline ml-3">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($obat)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4">Tidak ada data obat.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        <!-- Implement pagination logic here -->
    </div>
</div>

<!-- Modal Tambah Obat -->
<div id="tambahmodal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl p-5">
        <h2 class="text-lg font-bold mb-3">Tambah Obat</h2>
        <form method="POST" action="obat_tambah.php">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3">
                <div>
                    <label for="nama_obat" class="block text-sm font-medium text-gray-700">Nama</label>
                    <input type="text" name="nama_obat" id="nama_obat" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label for="kemasan" class="block text-sm font-medium text-gray-700">Kemasan</label>
                    <input type="text" name="kemasan" id="kemasan" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label for="harga" class="block text-sm font-medium text-gray-700">Harga</label>
                    <input type="number" name="harga" id="harga" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-4">
                <button type="button" onclick="closeModal('tambahmodal')" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">Batal</button>
                <button type="submit" class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Obat -->
<div id="editmodal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl p-5">
        <h2 class="text-lg font-bold mb-3">Edit Obat</h2>
        <form id="editForm" method="POST" action="obat_edit.php">
    <input type="hidden" name="id" id="edit_id"> <!-- Field hidden untuk ID -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3">
        <div>
            <label for="edit_nama_obat" class="block text-sm font-medium text-gray-700">Nama</label>
            <input type="text" name="nama_obat" id="edit_nama_obat" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
        </div>
        <div>
            <label for="edit_kemasan" class="block text-sm font-medium text-gray-700">Kemasan</label>
            <input type="text" name="kemasan" id="edit_kemasan" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
        </div>
        <div>
            <label for="edit_harga" class="block text-sm font-medium text-gray-700">Harga</label>
            <input type="number" name="harga" id="edit_harga" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
        </div>
    </div>
    <div class="flex justify-end space-x-3 mt-4">
        <button type="button" onclick="closeModal('editmodal')" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">Batal</button>
        <button type="submit" class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600">Simpan</button>
    </div>
</form>

    </div>
</div>

<script>
    // Fungsi untuk membuka modal berdasarkan ID
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    // Fungsi untuk menutup modal berdasarkan ID
    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    function editObat(id) {
    const url = `obat_edit.php?id=${id}`;
    const editForm = document.getElementById('editForm');
    const editNamaObat = document.getElementById('edit_nama_obat');
    const editKemasan = document.getElementById('edit_kemasan');
    const editHarga = document.getElementById('edit_harga');
    const editId = document.getElementById('edit_id');

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const obat = data.obat;
                editId.value = id; // Masukkan ID obat ke field hidden
                editNamaObat.value = obat.nama_obat;
                editKemasan.value = obat.kemasan;
                editHarga.value = obat.harga;
                openModal('editmodal');
            } else {
                alert(data.message || 'Terjadi kesalahan saat mengambil data obat.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal mengambil data obat.');
        });
}

    setTimeout(function () {
        const alert = document.getElementById('successAlert');
        if (alert) {
            alert.style.display = 'none';
        }
    }, 5000); // 5000 ms = 5 detik
</script>


<?php
$content = ob_get_clean();
include('../../components/layout_admin.php');
?>
