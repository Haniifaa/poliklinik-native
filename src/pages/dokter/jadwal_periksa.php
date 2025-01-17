<?php
session_start(); // Ensure session_start is called at the top

// Include necessary files (header, database connection, etc.)
include_once("../../config/koneksi.php");

// Check if there's any success or error message in the session
$successMessage = isset($_SESSION['success']) ? $_SESSION['success'] : null;
$errorMessage = isset($_SESSION['error']) ? $_SESSION['error'] : null;

$dokter = isset($_SESSION['dokter']) ? $_SESSION['dokter'] : null;

if (!$dokter) {
    die("Dokter tidak ditemukan dalam session.");
}

// Menentukan urutan hari dengan menggunakan urutan manual
$hariUrut = [
    'Senin' => 1,
    'Selasa' => 2,
    'Rabu' => 3,
    'Kamis' => 4,
    'Jumat' => 5,
    'Sabtu' => 6,
    'Minggu' => 7
];

// Handle the 'store' action (adding a new schedule)
// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     $hari = $_POST['hari'] ?? '';
//     $jam_mulai = $_POST['jam_mulai'] ?? '';
//     $jam_selesai = $_POST['jam_selesai'] ?? '';
//     $status = $_POST['status'] ?? '';

//     // Validasi input
//     $errors = []; // Ensure errors array is initialized
//     if (!in_array($hari, ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'])) {
//         $errors[] = 'Hari tidak valid.';
//     }
//     if (!preg_match('/^[0-9]{2}:[0-9]{2}$/', $jam_mulai)) {
//         $errors[] = 'Format jam mulai tidak valid.';
//     }
//     if (!preg_match('/^[0-9]{2}:[0-9]{2}$/', $jam_selesai)) {
//         $errors[] = 'Format jam selesai tidak valid.';
//     }
//     if (!in_array($status, ['Aktif', 'Tidak Aktif'])) {
//         $errors[] = 'Status tidak valid.';
//     }

//     // Jika ada error, tampilkan kembali halaman dengan error
//     if (!empty($errors)) {
//         $_SESSION['errors'] = $errors;
//     } else {
//         // Periksa apakah sudah ada jadwal aktif untuk dokter ini
//         if ($status === 'Aktif') {
//             $queryActive = "SELECT * FROM jadwal_periksa WHERE id_dokter = ? AND status = 'Aktif'";
//             $stmtActive = $pdo->prepare($queryActive);
//             $stmtActive->execute([$dokter['id']]);
//             if ($stmtActive->rowCount() > 0) {
//                 // Ubah semua jadwal aktif menjadi Tidak Aktif
//                 $queryUpdateActive = "UPDATE jadwal_periksa SET status = 'Tidak Aktif' WHERE id_dokter = ? AND status = 'Aktif'";
//                 $pdo->prepare($queryUpdateActive)->execute([$dokter['id']]);
//             }
//         }

//         // Periksa apakah hari sudah ada
//         $queryExist = "SELECT * FROM jadwal_periksa WHERE id_dokter = ? AND hari = ?";
//         $stmtExist = $pdo->prepare($queryExist);
//         $stmtExist->execute([$dokter['id'], $hari]);
//         if ($stmtExist->rowCount() > 0) {
//             $_SESSION['error'] = 'Hari sudah tersedia.';
//         } else {
//             // Insert jadwal baru
//             $queryInsert = "INSERT INTO jadwal_periksa (id_dokter, hari, jam_mulai, jam_selesai, status) VALUES (?, ?, ?, ?, ?)";
//             $stmtInsert = $pdo->prepare($queryInsert);
//             $stmtInsert->execute([$dokter['id'], $hari, $jam_mulai, $jam_selesai, $status]);
//             $_SESSION['success'] = 'Jadwal berhasil ditambahkan.';
//         }
//     }
//     header('Location: ' . $_SERVER['PHP_SELF']);
//     exit();
// }

// Handle the 'delete' action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteJadwal'])) {
    $jadwalId = $_POST['id'] ?? null;

    if ($jadwalId) {
        try {
            // Delete the schedule from the database
            $queryDelete = "DELETE FROM jadwal_periksa WHERE id = ?";
            $stmtDelete = $pdo->prepare($queryDelete);
            $stmtDelete->execute([$jadwalId]);

            $_SESSION['success'] = 'Jadwal berhasil dihapus.';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Gagal menghapus jadwal: ' . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = 'Jadwal tidak ditemukan.';
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}


// Ambil data jadwal periksa dengan JOIN untuk mendapatkan nama dokter
$query = "
    SELECT jp.*, d.nama AS nama
    FROM jadwal_periksa jp
    JOIN dokter d ON jp.id_dokter = d.id
    WHERE jp.id_dokter = ?
    ORDER BY FIELD(jp.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')
";
$stmt = $pdo->prepare($query);
$stmt->execute([$dokter['id']]); // Akses ID dokter dengan $dokter['id']
$jadwals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil data dokter (misalnya digunakan untuk menampilkan pilihan dokter)
$queryDokter = "SELECT * FROM dokter";
$stmtDokter = $pdo->query($queryDokter);
$dokters = $stmtDokter->fetchAll(PDO::FETCH_ASSOC);


ob_start();

?>

<div class="p-4 mt-14">
    <!-- Display Alert Messages -->
    <?php if ($successMessage): ?>
        <div id="successAlert" class="mb-4 p-4 bg-green-500 text-white rounded-md">
            <?php echo $successMessage; ?>
        </div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div class="mb-4 p-4 bg-red-500 text-white rounded-md">
            <?php echo $errorMessage; ?>
        </div>
    <?php endif; ?>

    <!-- Display Validation Errors -->
    <?php if (!empty($errors)): ?>
        <div class="mb-4 p-4 bg-red-500 text-white rounded-md" role="alert">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>


    
    <!-- Judul H1 -->
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Jadwal Periksa</h1>

    <!-- Bar untuk Search dan Tambah Jadwal -->
    <div class="flex justify-between items-center mb-4">
        <!-- Input Pencarian -->
        <div class="relative">
            <input
                type="text"
                id="search"
                class="block p-2 pl-10 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Cari jadwal..."
            />
            <svg
                class="absolute top-1/2 left-2 w-5 h-5 text-gray-400 transform -translate-y-1/2"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M21 21l-4.35-4.35m0 0a7.5 7.5 0 1110.15-10.15 7.5 7.5 0 01-10.15 10.15z"
                />
            </svg>
        </div>

        <!-- Tombol Tambah Jadwal -->
        <button
            type="button"
            onclick="openModal('tambahmodal')"
            class="text-white bg-purple-400 hover:bg-purple-500 focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 flex items-center space-x-2"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Jadwal</span>
        </button>
    </div>

    <!-- Tabel -->
    <div class="relative w-full overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3">No</th>
                    <th scope="col" class="px-6 py-3">Nama Dokter</th>
                    <th scope="col" class="px-6 py-3">Hari</th>
                    <th scope="col" class="px-6 py-3">Jam Mulai</th>
                    <th scope="col" class="px-6 py-3">Jam Selesai</th>
                    <th scope="col" class="px-6 py-3">Status</th>
                    <th scope="col" class="px-6 py-3">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jadwals as $index => $jadwal): ?>
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                            <?php echo $index + 1; ?>
                        </th>
                        <td class="px-6 py-4"><?php echo $jadwal['nama']; ?></td> <!-- Ganti object ke array -->
                        <td class="px-6 py-4"><?php echo $jadwal['hari']; ?></td>
                        <td class="px-6 py-4"><?php echo $jadwal['jam_mulai']; ?></td>
                        <td class="px-6 py-4"><?php echo $jadwal['jam_selesai']; ?></td>
                        <td class="px-6 py-4">
    <form action="update_jadwal.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $jadwal['id']; ?>">
        <select name="status" class="rounded border-gray-300" onchange="this.form.submit()">
            <option value="Aktif" <?php echo $jadwal['status'] === 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
            <option value="Tidak Aktif" <?php echo $jadwal['status'] === 'Tidak Aktif' ? 'selected' : ''; ?>>Tidak Aktif</option>
        </select>
        <input type="hidden" name="updateStatus" value="1">
    </form>
</td>


                        <td class="px-6 py-4 text-right">
                            <!-- <button onclick="openModal('editmodal', <?php echo $jadwal['id']; ?>)"
                                    class="text-blue-600 hover:text-blue-900">
                                Edit
                            </button> -->
                            <button onclick="deleteJadwal(<?php echo $jadwal['id']; ?>)"
        class="text-red-600 hover:text-red-900">
    Hapus
</button>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- Modal -->
<div id="addScheduleModal" class="fixed inset-0 bg-gray-500 bg-opacity-50 flex justify-center items-center hidden">
    <div class="bg-white p-6 rounded shadow-md w-96">
        <h2 class="text-2xl font-semibold mb-4">Tambah Jadwal Dokter</h2>
        <form action="jadwal_tambah.php" method="POST">
            <!-- Input Hari -->
            <div class="mb-4">
                <label for="hari" class="block text-sm font-medium text-gray-700">Hari</label>
                <select id="hari" name="hari" class="mt-1 block w-full p-2 border border-gray-300 rounded" required>
                    <option value="Senin">Senin</option>
                    <option value="Selasa">Selasa</option>
                    <option value="Rabu">Rabu</option>
                    <option value="Kamis">Kamis</option>
                    <option value="Jumat">Jumat</option>
                    <option value="Sabtu">Sabtu</option>
                    <option value="Minggu">Minggu</option>
                </select>
            </div>

            <!-- Input Jam Mulai -->
            <div class="mb-4">
                <label for="jam_mulai" class="block text-sm font-medium text-gray-700">Jam Mulai</label>
                <input type="time" id="jam_mulai" name="jam_mulai" class="mt-1 block w-full p-2 border border-gray-300 rounded" required>
            </div>

            <!-- Input Jam Selesai -->
            <div class="mb-4">
                <label for="jam_selesai" class="block text-sm font-medium text-gray-700">Jam Selesai</label>
                <input type="time" id="jam_selesai" name="jam_selesai" class="mt-1 block w-full p-2 border border-gray-300 rounded" required>
            </div>

            <!-- Input Status -->
            <div class="mb-4">
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="status" name="status" class="mt-1 block w-full p-2 border border-gray-300 rounded" required>
                    <option value="Aktif">Aktif</option>
                    <option value="Tidak Aktif">Tidak Aktif</option>
                </select>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-500 text-white p-2 rounded">Simpan Jadwal</button>
            </div>
        </form>

        <!-- Button Close Modal -->
        <button onclick="closeModal()" class="absolute top-0 right-0 p-2 text-gray-700">&times;</button>
    </div>
</div>

<script>
function openModal() {
        document.getElementById('addScheduleModal').classList.remove('hidden');
    }

    // Function to close modal
    function closeModal() {
        document.getElementById('addScheduleModal').classList.add('hidden');
    }
            // Fungsi untuk membuka modal
// function openModal(modalId) {
//     const modal = document.getElementById(modalId);
//     if (modal) {
//         modal.classList.remove('hidden'); // Hapus kelas 'hidden'
//         modal.classList.add('flex');       // Tambahkan kelas 'flex' untuk menampilkan modal
//     } else {
//         console.error(`Modal dengan ID "${modalId}" tidak ditemukan.`);
//     }
// }

// // Fungsi untuk menutup modal
// function closeModal(modalId) {
//     const modal = document.getElementById(modalId);
//     if (modal) {
//         modal.classList.add('hidden');  // Tambahkan kelas 'hidden'
//         modal.classList.remove('flex'); // Hapus kelas 'flex'

//         // Reset data input modal
//         document.getElementById('editJadwalForm').reset();
//     } else {
//         console.error(`Modal dengan ID "${modalId}" tidak ditemukan.`);
//     }
// }



// Fungsi untuk membuka modal edit jadwal dengan data dari server
// Fungsi untuk membuka modal edit jadwal dengan data dari server
// function editJadwal(jadwal_periksa) {
//     // Panggil API untuk mendapatkan data jadwal
//     fetch(`/dokter/jadwal-periksa/${jadwal_periksa}/edit`)
//         .then(response => {
//             if (!response.ok) {
//                 throw new Error('Gagal mengambil data dari server.');
//             }
//             return response.json();
//         })
//         .then(data => {
//             // Isi data dalam modal edit
//             document.getElementById('edit_jam_mulai').value = data.jam_mulai;
//             document.getElementById('edit_jam_selesai').value = data.jam_selesai;
//             document.getElementById('edit_status').value = data.status;
//             document.getElementById('edit_hari').value = data.hari; // Pastikan hari terisi dengan benar

//             // Perbarui action form
//             document.getElementById('editJadwalForm').action = `/dokter/jadwal-periksa/${jadwal_periksa}`;

//             // Tampilkan modal setelah data terisi
//             openModal('editmodal');
//         })
//         .catch(error => {
//             console.error('Terjadi kesalahan:', error);
//             alert('Gagal mengambil data jadwal.');
//         });
// }

// Ambil CSRF Token dari meta tag
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Fungsi untuk memperbarui status dan indikator status
function updateStatus(jadwal_periksa, newStatus) {
    // Pengecekan apakah ada jadwal lain yang aktif
    const allJadwal = document.querySelectorAll('.jadwal-status'); // Ganti dengan selector yang sesuai untuk status jadwal lainnya
    let isAnyActive = false;

    // Loop untuk memeriksa apakah ada jadwal dengan status 'Aktif'
    allJadwal.forEach(jadwal => {
        if (jadwal.textContent.trim() === 'Aktif') {
            isAnyActive = true;
        }
    });

    // Jika ada jadwal yang aktif dan ingin mengubah status menjadi 'Aktif', batalkan
    if (newStatus === 'Aktif' && isAnyActive) {
        alert('Hanya satu jadwal yang bisa aktif pada satu waktu.');
        return; // Jangan lanjutkan jika ada jadwal lain yang aktif
    }

    // Kirim permintaan POST untuk memperbarui status
    fetch(`/dokter/jadwal-periksa/${jadwal_periksa}/update-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken // Gunakan CSRF Token dari meta tag
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Perbarui warna indikator berdasarkan status baru
            const indicator = document.getElementById(`status-indicator-${jadwal_periksa}`);
            if (indicator) {
                // Hapus kelas indikator lama
                indicator.classList.remove('bg-red-500', 'bg-green-500');
                // Tambahkan kelas indikator baru sesuai status
                setTimeout(() => {
                    indicator.classList.add(newStatus === 'Aktif' ? 'bg-green-500' : 'bg-red-500');
                }, 100); // Delay untuk memastikan refresh
            } else {
                console.error(`Indikator dengan ID "status-indicator-${jadwal_periksa}" tidak ditemukan.`);
            }
            alert('Status berhasil diperbarui');
        } else {
            alert('Gagal memperbarui status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan, coba lagi nanti');
    });
}


document.querySelectorAll('.status-dropdown').forEach(dropdown => {
    dropdown.addEventListener('change', function () {
        const id = this.getAttribute('data-id');
        const status = this.value;
        const indicator = this.previousElementSibling; // Elemen buletan

        fetch('update_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id, status }),
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Status berhasil diperbarui!');
                    // Perbarui warna buletan berdasarkan status
                    if (status === 'Aktif') {
                        indicator.classList.remove('bg-red-500');
                        indicator.classList.add('bg-green-500');
                    } else {
                        indicator.classList.remove('bg-green-500');
                        indicator.classList.add('bg-red-500');
                    }
                } else {
                    alert('Gagal memperbarui status: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    });
});

function deleteJadwal(id) {
    if (confirm('Apakah Anda yakin ingin menghapus jadwal ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';

        const inputId = document.createElement('input');
        inputId.type = 'hidden';
        inputId.name = 'id';
        inputId.value = id;

        const inputDelete = document.createElement('input');
        inputDelete.type = 'hidden';
        inputDelete.name = 'deleteJadwal';
        inputDelete.value = '1';

        form.appendChild(inputId);
        form.appendChild(inputDelete);

        document.body.appendChild(form);
        form.submit();
    }
}

        </script>

<?php
$content = ob_get_clean();
include('../../components/layout_dokter.php'); ?>
