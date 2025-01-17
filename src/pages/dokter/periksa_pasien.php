<?php
// Assume we have the necessary variables and session data from a backend
// You need to fetch $periksa, session messages (success/error), etc.
session_start();
include_once("../../config/koneksi.php");

// Ambil dokter dari session
if (!isset($_SESSION['dokter'])) {
    header('Location: login_dokter.php');
    exit;
}

$dokter = $_SESSION['dokter'];
$dokterId = $dokter['id'];

// Pencarian
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query daftar riwayat periksa
$sql = "
    SELECT dp.*, p.nama AS nama_pasien, p.alamat AS alamat_pasien, p.no_ktp, p.no_hp, p.no_rm, 
           jp.hari, pr.id AS id_periksa, pr.tgl_periksa, pr.catatan, pr.biaya_periksa
    FROM daftar_poli dp
    LEFT JOIN periksa pr ON dp.id = pr.id_daftar_poli
    JOIN jadwal_periksa jp ON dp.id_jadwal = jp.id
    JOIN pasien p ON dp.id_pasien = p.id
    WHERE jp.id_dokter = :dokterId
    " . ($search ? "AND p.nama LIKE :search " : "") . "
    ORDER BY FIELD(jp.hari, 'Sabtu', 'Jumat', 'Kamis', 'Rabu', 'Selasa', 'Senin'), dp.no_antrian ASC";

$stmt = $pdo->prepare($sql);
if ($search) {
    $searchParam = "%$search%";
    $stmt->bindParam(':dokterId', $dokterId, PDO::PARAM_INT);
    $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
} else {
    $stmt->bindParam(':dokterId', $dokterId, PDO::PARAM_INT);
}
$stmt->execute();
$riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function untuk mendapatkan detail pemeriksaan
function getDetailPemeriksaan($id_periksa, $pdo) {
    $sql = "
        SELECT pr.*, p.nama AS nama_pasien, d.nama AS nama_dokter, 
               obat.nama_obat, obat.kemasan, obat.harga
        FROM periksa pr
        JOIN daftar_poli dp ON pr.id_daftar_poli = dp.id
        JOIN pasien p ON dp.id_pasien = p.id
        JOIN dokter d ON dp.id_dokter = d.id
        LEFT JOIN (
            SELECT dp.id_periksa, GROUP_CONCAT(o.nama_obat SEPARATOR ', ') AS nama_obat,
                   GROUP_CONCAT(o.kemasan SEPARATOR ', ') AS kemasan,
                   SUM(o.harga) AS harga
            FROM detail_periksa dp
            JOIN obat o ON dp.id_obat = o.id
            GROUP BY dp.id_periksa
        ) AS obat ON obat.id_periksa = pr.id
        WHERE pr.id = :id_periksa
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_periksa', $id_periksa, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
try {
    // Atur mode error PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query untuk mendapatkan data obat
    $query = "SELECT id, nama_obat, kemasan, harga FROM obat";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    // Ambil semua hasil sebagai array asosiatif
    $obatList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Tangani error koneksi atau query
    die("Terjadi kesalahan: " . $e->getMessage());
}

ob_start();
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
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Daftar Periksa Pasien</h1>

        <!-- Bar untuk Search dan Tambah Pasien -->
        <div class="flex justify-between items-center mb-4">
            <!-- Input Pencarian -->
            <form method="GET" action="" class="relative">
                <input
                    type="text"
                    name="search"
                    id="search"
                    value="<?= isset($_GET['search']) ? $_GET['search'] : ''; ?>"
                    class="block p-2 pl-10 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Cari pasien..."
                />
                <button type="submit" class="absolute top-1/2 left-2 w-5 h-5 text-gray-400 transform -translate-y-1/2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0a7.5 7.5 0 1110.15-10.15 7.5 7.5 0 01-10.15 10.15z" />
                    </svg>
                </button>
            </form>
        </div>

        <!-- Tabel -->
        <div class="relative w-full overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">No Urut</th>
                        <th scope="col" class="px-6 py-3">Nama Pasien</th>
                        <th scope="col" class="px-6 py-3">Keluhan</th>
                        <th scope="col" class="px-6 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($riwayat)): ?>
                        <?php foreach ($riwayat as $p): ?>
    <tr class="bg-white border-b hover:bg-gray-50">
        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap"><?= $p['no_antrian']; ?></th>
        <td class="px-6 py-4"><?= isset($p['nama_pasien']) ? $p['nama_pasien'] : 'Tidak ada data'; ?></td>
        <td class="px-6 py-4"><?= $p['keluhan']; ?></td>
        <td class="flex items-center px-6 py-4">
            <?php if ($p['id_periksa']): ?>
                <!-- Jika pasien sudah diperiksa -->
                <button onclick="editPeriksa(<?= $p['id_periksa']; ?>)" class="font-medium text-blue-600 hover:underline" aria-label="Edit Periksa">Edit</button>
            <?php else: ?>
                <!-- Jika pasien belum diperiksa -->
                <button onclick="window.location.href='../../pages/dokter/periksa.php?id=<?= $p['id']; ?>'">Periksa</button>
                <?php endif; ?>
        </td>
    </tr>
<?php endforeach; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">Tidak ada data pasien.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- Modal Edit Pasien -->
    <div id="editmodal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl p-5">
            <h2 class="text-lg font-bold mb-3">Edit Pasien</h2>
            <form id="editForm" method="POST" action="periksa_edit.php">
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" id="id_daftar_poli" name="id_daftar_poli" value="">
                    <?php foreach ($riwayat as $p): ?>
                <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
<?php endforeach; ?>
                <div class="sm:col-span-6">
                <label for="nama" class="block text-sm font-medium text-gray-900">Nama Pasien</label>
                <div class="mt-2">
                    <input type="text" name="nama" id="nama" value="<?php echo htmlspecialchars($pasien['nama']); ?>" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400" readonly>
                </div>

                </div>
                <!-- Form fields here -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label for="tgl_periksa" class="block text-sm font-medium text-gray-900">Tanggal Pemeriksaan</label>
                        <input
                            type="datetime-local"
                            id="tgl_periksa"
                            name="tgl_periksa"
                            value=""
                            class="mt-2 block w-full rounded-md bg-white px-3 py-1.5 text-sm text-gray-900 border border-gray-300">
                    </div>

                    <div>
                        <label for="catatan" class="block text-sm font-medium text-gray-900">Catatan Pemeriksaan</label>
                        <textarea
                            id="catatan"
                            name="catatan"
                            rows="3"
                            class="mt-2 block w-full rounded-md bg-white px-3 py-1.5 text-sm text-gray-900 border border-gray-300"></textarea>
                    </div>
                </div>

                <div class="mt-4">
    <label for="id_obat" class="block text-sm font-medium text-gray-900">Pilih Obat</label>
    <div class="flex items-center gap-4 mb-4">
        <select id="id_obat" name="id_obat[]" class="block w-2/3 rounded-md bg-white px-3 py-2 text-base text-gray-900 outline outline-1 outline-gray-300" required>
            <option value="">-- Pilih Obat --</option>
            <?php foreach ($obatList as $obat) : ?>
                <option value="<?= $obat['id']; ?>" data-harga="<?= $obat['harga']; ?>">
                    <?= htmlspecialchars($obat['nama_obat']); ?> - <?= htmlspecialchars($obat['kemasan']); ?> 
                    (Rp<?= number_format($obat['harga'], 0, ',', '.'); ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <button
            type="button"
            id="tambah-obat-btn"
            onclick="addObatToList()"
            class="px-4 py-2 bg-purple-500 text-white text-sm rounded-lg hover:bg-purple-600">
            Tambah Obat
        </button>
    </div>

    <!-- Daftar obat yang dipilih -->
    <div id="selected-obat-list" class="mt-4 space-y-2">
        <?php if (!empty($selectedObatList)) : ?>
            <?php foreach ($selectedObatList as $obat) : ?>
                <div class="flex items-center justify-between bg-gray-100 p-2 rounded-lg shadow-sm" data-id="<?= $obat['id']; ?>" data-harga="<?= $obat['harga']; ?>">
                    <div class="flex items-center gap-2">
                        <span class="text-gray-800 font-medium">
                            <?= htmlspecialchars($obat['nama_obat']); ?> - <?= htmlspecialchars($obat['kemasan']); ?> 
                            (Rp<?= number_format($obat['harga'], 0, ',', '.'); ?>)
                        </span>
                        <input type="hidden" name="id_obat[]" value="<?= $obat['id']; ?>">
                        <span class="text-sm text-gray-500">Rp<?= number_format($obat['harga'], 0, ',', '.'); ?></span>
                    </div>
                    <button type="button" onclick="removeObat(this)" class="px-2 py-1 text-red-500 hover:text-red-700 text-xs">
                        Hapus
                    </button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
                <div class="mt-4">
                    <label for="biaya_periksa" class="block text-sm font-medium text-gray-900">Biaya Pemeriksaan</label>
                    <input
                        type="number"
                        name="biaya_periksa"
                        id="biaya_periksa"
                        class="mt-2 block w-full rounded-md bg-white px-3 py-1.5 text-sm text-gray-900 border border-gray-300"
                        readonly>
                </div>

                <div class="flex justify-end space-x-3 mt-4">
                    <button type="button" onclick="closeModal('editmodal')" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    

    function addObatToList() {
    const obatSelect = document.getElementById('id_obat');
    const selectedValue = obatSelect.value;
    const selectedText = obatSelect.options[obatSelect.selectedIndex].text;
    const selectedHarga = obatSelect.options[obatSelect.selectedIndex].getAttribute('data-harga');

    if (selectedValue === "") {
        alert('Silakan pilih obat terlebih dahulu.');
        return;
    }

    const selectedObatList = document.getElementById('selected-obat-list');

    if (Array.from(selectedObatList.children).some(item => item.dataset.id === selectedValue)) {
        alert('Obat ini sudah ditambahkan.');
        return;
    }

    const obatItem = document.createElement('div');
    obatItem.className = 'flex items-center justify-between bg-gray-100 p-2 rounded-lg shadow-sm';
    obatItem.dataset.id = selectedValue;
    obatItem.dataset.harga = selectedHarga;

    obatItem.innerHTML = `
        <div class="flex items-center gap-2">
            <span class="text-gray-800 font-medium">${selectedText}</span>
            <input type="hidden" name="id_obat[]" value="${selectedValue}">
            <span class="text-sm text-gray-500">Rp${parseFloat(selectedHarga).toLocaleString('id-ID')}</span>
        </div>
        <button type="button" onclick="removeObat(this)" class="px-2 py-1 text-red-500 hover:text-red-700 text-xs">
            Hapus
        </button>
    `;

    selectedObatList.appendChild(obatItem);
    obatSelect.value = "";
    updateBiayaPeriksa();
}

function updateBiayaPeriksa() {
    const biayaDokter = 150000;
    let totalBiayaObat = 0;

    document.querySelectorAll("#selected-obat-list div").forEach(item => {
        const harga = parseFloat(item.dataset.harga) || 0;
        totalBiayaObat += harga;
    });

    const totalBiaya = biayaDokter + totalBiayaObat;
    document.getElementById("biaya_periksa").value = totalBiaya;
}

function removeObat(button) {
    const obatItem = button.parentElement;
    obatItem.remove();
    updateBiayaPeriksa();
}

function editPeriksa(id) {
    console.log('ID yang dikirim:', id);

    fetch(`periksa_edit.php?id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success' && data.data) {
                const periksa = data.data;
                document.getElementById('tgl_periksa').value = periksa.tgl_periksa || '';
                document.getElementById('catatan').value = periksa.catatan || '';
                document.getElementById('biaya_periksa').value = periksa.biaya_periksa || '';
                document.getElementById('id_daftar_poli').value = periksa.id_daftar_poli || '';

                const obatList = document.getElementById('selected-obat-list');
                obatList.innerHTML = '';  // Clear the existing obat list

                if (periksa.obat && Array.isArray(periksa.obat)) {
                    periksa.obat.forEach(obat => {
                        const obatItem = document.createElement('div');
                        obatItem.className = 'flex items-center justify-between bg-gray-100 p-2 rounded-lg shadow-sm';
                        obatItem.dataset.id = obat.id;
                        obatItem.dataset.harga = obat.harga;

                        obatItem.innerHTML = `
                            <div class="flex items-center gap-2">
                                <span class="text-gray-800 font-medium">${obat.nama_obat} - ${obat.kemasan}</span>
                                <input type="hidden" name="id_obat[]" value="${obat.id}">
                                <span class="text-sm text-gray-500">Rp${parseFloat(obat.harga).toLocaleString('id-ID')}</span>
                            </div>
                            <button type="button" onclick="removeObat(this)" class="px-2 py-1 text-red-500 hover:text-red-700 text-xs">
                                Hapus
                            </button>
                        `;

                        obatList.appendChild(obatItem);
                    });
                }

                // Update biaya setelah mengisi obat
                updateBiayaPeriksa();
            
                openModal('editmodal');
            } else {
                alert(data.message || 'Data periksa tidak ditemukan.');
            }
        })
        .catch(error => {
            console.error('Error saat fetch data:', error.message);
            alert('Terjadi kesalahan saat mengambil data.');
        });
}



    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden'); // Menampilkan modal
        } else {
            console.error(`Modal dengan ID "${modalId}" tidak ditemukan.`);
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden'); // Menyembunyikan modal
        } else {
            console.error(`Modal dengan ID "${modalId}" tidak ditemukan.`);
        }
    }
</script>

    

<?php
$content = ob_get_clean();
include('../../components/layout_dokter.php'); ?>
