<?php
session_start();
include_once("../../config/koneksi.php");
ob_start();

// Handle Search Input
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Pagination Setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Query for Dokter based on search
$sql = "SELECT * FROM dokter WHERE nama LIKE :search LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$dokter = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all Poli for select dropdown
$sqlPoli = "SELECT * FROM poli";
$poliStmt = $pdo->prepare($sqlPoli);
$poliStmt->execute();
$poli = $poliStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch total record count for pagination
$sqlCount = "SELECT COUNT(*) FROM dokter WHERE nama LIKE :search";
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmtCount->execute();
$totalRecords = $stmtCount->fetchColumn();
$totalPages = ceil($totalRecords / $limit);

// Generate pagination links
$paginationLinks = '';
for ($i = 1; $i <= $totalPages; $i++) {
    $paginationLinks .= "<a href='?page=$i&search=" . urlencode($search) . "' class='px-3 py-2 border border-gray-300'>Page $i</a>";
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? $_POST['id'] : '';

    if (empty($id)) {
        $_SESSION['error'] = 'ID dokter tidak ditemukan.';
        header("Location: admin/dokter.php");
        exit;
    }

    try {
        // Periksa apakah dokter ada
        $stmt = $pdo->prepare("SELECT * FROM dokter WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $dokter = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dokter) {
            $_SESSION['error'] = 'Data dokter tidak ditemukan.';
            header("Location: admin/dokter.php");
            exit;
        }

        // Hapus data dokter
        $stmt = $pdo->prepare("DELETE FROM dokter WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Data dokter berhasil dihapus.';
        } else {
            $_SESSION['error'] = 'Gagal menghapus data dokter.';
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Terjadi kesalahan: ' . $e->getMessage();
    }

    header("Location: dokter.php");
    exit;
}
?>

<div class="p-4 mt-14">
    <!-- Display Alert Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div id="successAlert" class="mb-4 p-4 bg-green-500 text-white rounded-md">
            <?php echo $_SESSION['success']; ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-4 p-4 bg-red-500 text-white rounded-md">
            <?php echo $_SESSION['error']; ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Judul H1 -->
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Data Dokter</h1>

    <!-- Bar untuk Search dan Tambah Pasien -->
    <div class="flex justify-between items-center mb-4">
        <!-- Input Pencarian -->
        <form method="GET" action="" class="relative">
            <input
                type="text"
                name="search"
                id="search"
                value="<?php echo htmlspecialchars($search); ?>"
                class="block p-2 pl-10 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Cari dokter..."
            />
            <button type="submit" class="absolute top-1/2 left-2 w-5 h-5 text-gray-400 transform -translate-y-1/2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0a7.5 7.5 0 1110.15-10.15 7.5 7.5 0 01-10.15 10.15z" />
                </svg>
            </button>
        </form>

        <!-- Tombol Tambah Pasien -->
        <button
            type="button"
            onclick="openModal('tambahmodal')"
            class="text-white bg-purple-400 hover:bg-purple-500 focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 flex items-center space-x-2"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Dokter</span>
        </button>
    </div>

    <!-- Tabel -->
    <div class="relative w-full overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3">Nama</th>
                    <th scope="col" class="px-6 py-3">Alamat</th>
                    <th scope="col" class="px-6 py-3">No HP</th>
                    <th scope="col" class="px-6 py-3">ID Poli</th>
                    <th scope="col" class="px-6 py-3">Created At</th>
                    <th scope="col" class="px-6 py-3">Updated At</th>
                    <th scope="col" class="px-6 py-3">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($dokter)): ?>
                    <?php foreach ($dokter as $d): ?>
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap"><?php echo htmlspecialchars($d['nama']); ?></th>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($d['alamat']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($d['no_hp']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($d['id_poli']); ?></td>
                            <td style="width: 200px;">
                                <?php echo isset($d['created_at']) ? date('d-m-Y H:i:s', strtotime($d['created_at'])) : 'NULL'; ?>
                            </td>
                            <td style="width: 200px;">
                                <?php echo isset($d['updated_at']) ? date('d-m-Y H:i:s', strtotime($d['updated_at'])) : 'NULL'; ?>
                            </td>
                            <td class="flex items-center px-6 py-4">
                                <button onclick="editDokter(<?php echo $d['id']; ?>)" class="font-medium text-blue-600 hover:underline" aria-label="Edit Dokter">Edit</button>
                                <form action="dokter.php" method="POST" onsubmit="return confirm('Yakin ingin menghapus data?')">
                                    <input type="hidden" name="id" value="<?php echo $d['id']; ?>">
                                    <button type="submit" class="font-medium text-red-600 hover:underline ml-3">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4">Tidak ada data dokter.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        <?php echo $paginationLinks; ?>
    </div>
</div>

<!-- Modal Tambah Dokter -->
<div id="tambahmodal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl p-5">
        <h2 class="text-lg font-bold mb-3">Tambah Dokter</h2>
        <form method="POST" action="dokter_tambah.php">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3">
                <!-- Nama -->
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700">Nama</label>
                    <input type="text" name="nama" id="nama" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500" />
                </div>
                <!-- Alamat -->
                <div>
                    <label for="alamat" class="block text-sm font-medium text-gray-700">Alamat</label>
                    <textarea name="alamat" id="alamat" rows="2" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500"></textarea>
                </div>
                <!-- No. HP -->
                <div>
                    <label for="no_hp" class="block text-sm font-medium text-gray-700">No. HP</label>
                    <input type="text" name="no_hp" id="no_hp" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500" />
                </div>
                <!-- Poli -->
                <div>
                    <label for="id_poli" class="block text-sm font-medium text-gray-700">Poli</label>
                    <select name="id_poli" id="id_poli" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                        <option value="" disabled selected>Pilih Poli</option>
                        <?php foreach ($poli as $p): ?>
                            <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nama_poli']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-4">
                <button type="button" onclick="closeModal('tambahmodal')" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Dokter -->
<div id="editmodal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl p-6">
        <h2 class="text-xl font-bold mb-4">Edit Dokter</h2>
        <form id="editForm" method="POST" action="dokter_edit.php">
        <input type="hidden" name="id" id="edit_id" value="">

            <input type="hidden" name="_method" value="PUT">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Nama -->
                <div class="mb-4">
                    <label for="edit_nama" class="block text-sm font-medium text-gray-700">Nama</label>
                    <input type="text" name="nama" id="edit_nama" required
                        class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                </div>
                <!-- Alamat -->
                <div class="mb-4">
                    <label for="edit_alamat" class="block text-sm font-medium text-gray-700">Alamat</label>
                    <textarea name="alamat" id="edit_alamat" rows="3" required
                        class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500"></textarea>
                </div>
                <!-- No. HP -->
                <div class="mb-4">
                    <label for="edit_no_hp" class="block text-sm font-medium text-gray-700">No. HP</label>
                    <input type="text" name="no_hp" id="edit_no_hp" required
                        class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                </div>
                <!-- Poli -->
                <div class="mb-4">
                    <label for="edit_id_poli" class="block text-sm font-medium text-gray-700">Poli</label>
                    <select name="id_poli" id="edit_id_poli" required
                        class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                        <option value="" disabled selected>Pilih Poli</option>
                        <?php foreach ($poli as $item): ?>
                            <option value="<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['nama_poli']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal('editmodal')" class="px-4 py-2 bg-gray-500 text-white rounded-lg">Batal</button>
                <button type="submit" class="px-4 py-2 bg-purple-500 text-white rounded-lg">Simpan</button>
            </div>
        </form>
    </div>
</div>


<script>
    /**
 * Fungsi untuk membuka modal berdasarkan ID
 * @param {string} modalId - ID dari modal yang ingin dibuka
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
    }
}

/**
 * Fungsi untuk menutup modal berdasarkan ID
 * @param {string} modalId - ID dari modal yang ingin ditutup
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
    }
}

/**
 * Fungsi untuk mengedit data dokter
 * @param {number} id - ID dari dokter yang akan diedit
 */
function editDokter(id) {
    // Menyiapkan URL untuk mendapatkan data dokter dari server
    const url = `dokter_edit.php?id=${id}`;  // id adalah ID dokter yang ingin diedit

    // Mengambil data dokter dari server menggunakan fetch
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Gagal mengambil data dari server');
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success' && data.dokter) {
                // Mengisi form modal dengan data dokter
                const dokter = data.dokter;
                document.getElementById('edit_id').value = dokter.id;
                document.getElementById('edit_nama').value = dokter.nama;
                document.getElementById('edit_alamat').value = dokter.alamat;
                document.getElementById('edit_no_hp').value = dokter.no_hp;
                document.getElementById('edit_id_poli').value = dokter.id_poli;

                // Membuka modal edit
                openModal('editmodal');
            } else {
                alert(data.message || 'Terjadi kesalahan saat mengambil data dokter.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengambil data dokter: ' + error.message);
        });
}


    // Optional: Set timeout to close the success alert after 5 seconds
    // setTimeout(function() {
    //     const alert = document.getElementById('successAlert');
    //     if (alert) {
    //         alert.style.display = 'none';
    //     }
    // }, 5000); // 5000 ms = 5 detik
</script>


<?php
$content = ob_get_clean();
include('../../components/layout_admin.php');
?>