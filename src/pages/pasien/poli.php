<?php
// Include your database connection
session_start();
include_once("../../config/koneksi.php"); // Assuming this includes the PDO connection

// Initialize variables for error and success messages
$error = '';
$success = '';

// Memastikan pasien sudah login
if (isset($_SESSION['pasien'])) {
    $pasien = $_SESSION['pasien'];
    $no_rm = $pasien['no_rm'];  // Ambil no_rm dari data pasien yang ada di sesi
    $id_pasien = $pasien['id'];  // Ambil id_pasien dari sesi
} else {
    echo "Anda harus login terlebih dahulu.";
    exit();
}

// Handle fetching and search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$poliQuery = "SELECT * FROM poli";

// Add search functionality to filter results
if ($search) {
    $poliQuery .= " WHERE nama_poli LIKE :search";
}

$stmt = $pdo->prepare($poliQuery);
if ($search) {
    $stmt->bindValue(':search', '%' . $search . '%');
}
$stmt->execute();
$poliResult = $stmt->fetchAll(PDO::FETCH_ASSOC); // Use $pdo instead of $conn



// Riwayat Pendaftaran Pasien
$riwayatQuery = "
    SELECT 
        dp.id AS id_daftar_poli, 
        jp.id_dokter, 
        jp.hari,  
        jp.jam_mulai, 
        jp.jam_selesai, 
        dp.no_antrian, 
        d.nama AS nama_dokter, 
        p.nama_poli,
        MAX(per.tgl_periksa) AS tgl_periksa, 
        MAX(per.catatan) AS catatan, 
        MAX(per.biaya_periksa) AS biaya_periksa, 
        GROUP_CONCAT(CONCAT(o.nama_obat, ' (', o.kemasan, ')') SEPARATOR ', ') AS daftar_obat, 
        IF(MAX(per.id) IS NOT NULL, 'Sudah Diperiksa', 'Belum Diperiksa') AS status,
        MAX(per.id) AS id_periksa,
        f.id AS id_feedback -- Ambil id_feedback jika ada
    FROM 
        daftar_poli dp
    JOIN 
        jadwal_periksa jp ON dp.id_jadwal = jp.id
    JOIN 
        dokter d ON jp.id_dokter = d.id
    JOIN 
        poli p ON d.id_poli = p.id
    LEFT JOIN 
        periksa per ON per.id_daftar_poli = dp.id
    LEFT JOIN 
        feedback f ON f.id_periksa = per.id -- Relasi dengan feedback
    LEFT JOIN 
        detail_periksa po ON po.id_periksa = per.id
    LEFT JOIN 
        obat o ON po.id_obat = o.id
    WHERE 
        dp.id_pasien = ?
    GROUP BY 
        dp.id, jp.id_dokter, jp.hari, jp.jam_mulai, jp.jam_selesai, dp.no_antrian, d.nama, p.nama_poli, f.id
    ORDER BY 
        MAX(per.tgl_periksa) DESC;
";

// Get Riwayat
$stmt = $pdo->prepare($riwayatQuery);
$stmt->execute([$id_pasien]);
$riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Jadwal Aktif Pasien
$jadwalQuery = "
    SELECT 
        jp.id AS id_jadwal, 
        jp.hari, 
        jp.jam_mulai, 
        jp.jam_selesai, 
        p.nama_poli, 
        d.nama AS nama_dokter
    FROM 
        jadwal_periksa jp
    JOIN 
        dokter d ON jp.id_dokter = d.id
    JOIN 
        poli p ON d.id_poli = p.id
    WHERE 
        jp.status = 'Aktif'
";
$stmt = $pdo->prepare($jadwalQuery);
$stmt->execute();
$jadwals = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Feedback Pasien
$feedbackQuery = "
    SELECT 
        f.id AS id_feedback, 
        f.ulasan, 
        f.rating, 
        per.tgl_periksa, 
        d.nama AS nama_dokter, 
        p.nama_poli
    FROM 
        feedback f
    JOIN 
        periksa per ON f.id_periksa = per.id
    JOIN 
        daftar_poli dp ON per.id_daftar_poli = dp.id
    JOIN 
        jadwal_periksa jp ON dp.id_jadwal = jp.id
    JOIN 
        dokter d ON jp.id_dokter = d.id
    JOIN 
        poli p ON d.id_poli = p.id
    WHERE 
        dp.id_pasien = ?  -- memastikan query mengambil feedback berdasarkan id_pasien
    ORDER BY 
        per.tgl_periksa DESC;  -- mengurutkan berdasarkan tanggal periksa terbaru
";

// Get Feedback
$stmt = $pdo->prepare($feedbackQuery);
$stmt->execute([$id_pasien]);
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$isFeedbackGiven = false;
$rating = 0; // Default rating is 0

foreach ($riwayat as $r) {
    if (isset($r['id_periksa'])) {
        // Cek apakah feedback sudah ada untuk id_periksa
        $checkFeedbackQuery = "SELECT rating FROM feedback WHERE id_periksa = ? LIMIT 1";
        $stmt = $pdo->prepare($checkFeedbackQuery);
        $stmt->execute([$r['id_periksa']]);
        $feedback = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($feedback) {
            $isFeedbackGiven = true;
            $rating = $feedback['rating']; // Ambil rating dari feedback
            break;
        }
    }
}


ob_start();
?>



<div class="p-4 mt-14">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Poli</h1>

    <div class="max-w-md mx-auto bg-white border border-gray-200 rounded-lg shadow-lg p-6 mb-6">
        <h2 class="text-2xl font-semibold text-center text-gray-900 mb-6">Daftar Poli</h2>

        <form action="daftar_poli.php" method="POST">
            <div class="mb-5">
                <label for="no_rm" class="block mb-2 text-sm font-medium text-gray-900">No Rekam Medis</label>
                <input
                    type="text"
                    id="no_rm"
                    name="no_rm"
                    value="<?= htmlspecialchars($no_rm) ?>"
                    class="bg-gray-200 border border-gray-300 text-gray-500 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                    placeholder="No Rekam Medis"
                    readonly
                    disabled
                    required
                />
            </div>

            <div class="mb-5">
                <label for="poli" class="block mb-2 text-sm font-medium text-gray-900">Pilih Poli</label>
                <select id="poli" name="poli" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                    <option value="">Pilih Poli</option>
                    <?php foreach ($poliResult as $p): ?>
                        <option value="<?= htmlspecialchars($p['id']) ?>"><?= htmlspecialchars($p['nama_poli']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-5">
    <label for="jadwal" class="block mb-2 text-sm font-medium text-gray-900">Pilih Jadwal</label>
    <select id="jadwal" name="id_jadwal" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
        <option value="">Pilih Jadwal</option>
        <?php foreach ($jadwals as $jadwal): ?>
            <option 
                value="<?= htmlspecialchars($jadwal['id_jadwal']) ?>" 
                data-poli="<?= htmlspecialchars($jadwal['nama_poli']) ?>" 
                data-dokter="<?= htmlspecialchars($jadwal['nama_dokter']) ?>">
                Dr. <?= htmlspecialchars($jadwal['nama_dokter']) ?> - 
    <?= htmlspecialchars($jadwal['hari']) ?> 
    (<?= htmlspecialchars($jadwal['jam_mulai']) ?> - <?= htmlspecialchars($jadwal['jam_selesai']) ?>) - 
    <?= htmlspecialchars($jadwal['nama_poli']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

            <div class="mb-5">
                <label for="keluhan" class="block mb-2 text-sm font-medium text-gray-900">Keluhan</label>
                <textarea id="keluhan" name="keluhan" rows="4" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Tulis keluhan Anda" required></textarea>
            </div>

            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center">
                Daftar
            </button>
        </form>
    </div>

    <!-- Riwayat -->
    <div class="max-w-full mx-auto bg-white border border-gray-200 rounded-lg shadow-lg p-6 mt-6">
        <div class="relative w-full overflow-x-auto shadow-md sm:rounded-lg">
            <h2 class="text-2xl font-semibold text-center text-gray-900 mb-6">Riwayat Daftar Poli</h2>

            <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
    <tr>
        <th scope="col" class="px-6 py-3">No</th>
        <th scope="col" class="px-6 py-3">Poli</th>
        <th scope="col" class="px-6 py-3">Dokter</th>
        <th scope="col" class="px-6 py-3">Hari</th>
        <th scope="col" class="px-6 py-3">Mulai</th>
        <th scope="col" class="px-6 py-3">Selesai</th>
        <th scope="col" class="px-6 py-3">Antrian</th>
        <th scope="col" class="px-6 py-3">Status</th>
        <th scope="col" class="px-6 py-3">Action</th>
        <!-- <th scope="col" class="px-24 py-3">Tes</th> Kolom Tes dengan padding lebih besar -->
    </tr>
</thead>
<tbody>
    <?php if (empty($riwayat)): ?>
        <tr>
            <td colspan="10" class="text-center px-6 py-4">Tidak ada data untuk ditampilkan.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($riwayat as $index => $r): ?>
            <tr class="bg-white border-b hover:bg-gray-50">
                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap"><?= $index + 1 ?></th>
                <td class="px-6 py-4"><?= htmlspecialchars($r['nama_poli'] ?? 'Tidak ada data') ?></td>
                <td class="px-6 py-4"><?= htmlspecialchars($r['nama_dokter']) ?></td>
                <td class="px-6 py-4"><?= htmlspecialchars($r['hari'] ?? 'Tidak ada data') ?></td>
                <td class="px-6 py-4"><?= htmlspecialchars($r['jam_mulai'] ?? 'Tidak ada data') ?></td>
                <td class="px-6 py-4"><?= htmlspecialchars($r['jam_selesai'] ?? 'Tidak ada data') ?></td>
                <td class="px-6 py-4"><?= htmlspecialchars($r['no_antrian'] ?? 'Tidak ada data') ?></td>
                <td class="px-6 py-4 w-40">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                        <?= $r['status'] == 'Sudah Diperiksa' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' ?>">
                        <?= htmlspecialchars($r['status']) ?>
                    </span>
                </td>
                <td class="flex items-center px-6 py-4">
                    <button id="openModal" class="font-medium text-blue-600 hover:underline bg-transparent border-none cursor-pointer">Detail Poli dan Riwayat</button>
                </td>
                <td class="px-6 py-4 flex flex-col items-center"> <!-- Kolom Tes Flexbox -->
                    <!-- Tombol Feedback -->
                    <?php if (!$isFeedbackGiven): ?>
                        <button class="feedbackButton font-medium text-blue-600 hover:underline bg-transparent border-none cursor-pointer" data-id-periksa="<?= htmlspecialchars($r['id_periksa'] ?? '') ?>">
                        Feedback
        </button>
    <?php else: ?>
        <!-- Bintang Feedback -->
        <div class="feedbackStars flex space-x-1 items-center">
            <?php for ($i = 0; $i < 5; $i++): ?>
                <span class="star <?= $i < $rating ? 'text-yellow-500' : 'text-gray-300' ?>">&#9733;</span>
            <?php endfor; ?>
            <!-- Ikon Edit -->
            <button class="editFeedbackButton text-gray-500 hover:text-blue-500 ml-2" aria-label="Edit Feedback" data-id="<?= htmlspecialchars($r['id_feedback'] ?? '') ?>">
                <i class="fas fa-edit"></i> <!-- Ikon Edit Font Awesome -->
            </button>
            <!-- Ikon Hapus -->
<a href="feedback_hapus.php?id=<?= htmlspecialchars($r['id_feedback'] ?? '') ?>" 
   class="deleteFeedbackButton text-gray-500 hover:text-red-500 ml-2" 
   aria-label="Hapus Feedback" 
   onclick="return confirm('Apakah Anda yakin ingin menghapus feedback ini?');">
    <i class="fas fa-trash-alt"></i> <!-- Ikon Hapus Font Awesome -->
</a>

        </div>
    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</tbody>

            </table>
        </div>
    </div>
</div>

<!-- Tempatkan bintang feedback yang sudah ada -->



<!-- Modal Structure -->
<div id="modalDetail" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white p-6 rounded-lg w-3/4 md:w-1/2">
        <!-- Modal Header -->
        <div class="flex justify-between items-center">
            <h3 class="text-xl font-semibold">Detail Riwayat</h3>
            <button id="closeModal" class="text-gray-600 hover:text-gray-900">&times;</button>
        </div>

        <!-- Modal Body (Tables and Information) -->
        <div class="mt-4">
            <!-- Tabel Riwayat -->
            <table class="min-w-full table-auto border-collapse">
                <thead>
                    <tr>
                        <th class="px-4 py-2 border">Nama Poli</th>
                        <th class="px-4 py-2 border">Nama Dokter</th>
                        <th class="px-4 py-2 border">Hari</th>
                        <th class="px-4 py-2 border">Mulai</th>
                        <th class="px-4 py-2 border">Selesai</th>
                        <th class="px-4 py-2 border">Nomor Antrian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($riwayat as $r): ?>
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <?= htmlspecialchars($r['nama_poli'] ?? 'Tidak ada data') ?>
                            </td>
                            <td class="px-6 py-4">
                                <?= htmlspecialchars($r['nama_dokter'] ?? 'Tidak ada data') ?>
                            </td>
                            <td class="px-6 py-4">
                                <?= htmlspecialchars($r['hari'] ?? 'Tidak ada data') ?>
                            </td>
                            <td class="px-6 py-4">
                                <?= htmlspecialchars($r['jam_mulai'] ?? 'Tidak ada data') ?>
                            </td>
                            <td class="px-6 py-4">
                                <?= htmlspecialchars($r['jam_selesai'] ?? 'Tidak ada data') ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-green-400 text-white py-1 px-3 rounded-full text-sm">
                                    <?= htmlspecialchars($r['no_antrian'] ?? 'Tidak ada data') ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Additional Details -->
            <div class="mt-6">
    <?php if (!empty($riwayat)): ?>
        <?php foreach ($riwayat as $r): ?>
            <p>
                <strong>Tanggal Periksa:</strong> 
                <?= htmlspecialchars($r['tgl_periksa'] ?? 'Belum ada tanggal periksa') ?>
            </p>
            <p>
                <strong>Catatan:</strong> 
                <?= htmlspecialchars($r['catatan'] ?? 'Tidak ada catatan') ?>
            </p>

            <p><strong>Daftar Obat yang Diresepkan:</strong></p>
            <?php if (!empty($r['daftar_obat'])): ?>
                <p><?= htmlspecialchars($r['daftar_obat']) ?></p>
            <?php else: ?>
                <p>Tidak ada obat yang diresepkan</p>
            <?php endif; ?>

            <p class="mt-4">
                <strong class="text-xl font-semibold">Biaya Periksa:</strong>
                <span class="bg-purple-600 text-white px-4 py-2 rounded-full text-xl font-semibold">
                    <?= isset($r['biaya_periksa']) && is_numeric($r['biaya_periksa']) 
                        ? 'Rp ' . number_format($r['biaya_periksa'], 0, ',', '.') 
                        : 'Belum diperiksa' ?>
                </span>
            </p>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Riwayat periksa tidak ditemukan.</p>
    <?php endif; ?>
</div>
        </div>

        <!-- Modal Footer -->
        <div class="mt-6 text-right">
            <button id="closeModalBtn" class="bg-blue-600 text-white px-4 py-2 rounded">Tutup</button>
        </div>
    </div>
</div>
<div id="feedbackModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden flex items-center justify-center z-50">
    <div class="relative p-6 border w-4/5 max-w-2xl shadow-lg rounded-lg bg-white">
        <div class="text-center">
            <h3 class="text-xl leading-6 font-semibold text-gray-900">Beri Feedback</h3>
            <div class="mt-4 text-gray-700">
                <p id="tanggalPeriksa" class="font-semibold text-lg"></p> <!-- Tanggal Periksa -->
                <p id="namaDokter" class="font-semibold text-lg"></p> <!-- Nama Dokter -->
            </div>
            <form id="feedbackForm" class="mt-4">
                <input type="hidden" id="id_periksa" name="id_periksa">

                <div class="mb-4">
                    <label for="ulasan" class="block text-gray-700">Ulasan:</label>
                    <textarea id="ulasan" name="ulasan" rows="4" class="border border-gray-300 w-full p-2 mt-1" required></textarea>
                </div>

                <div class="mb-4">
                    <label for="rating" class="block text-gray-700">Rating:</label>
                    <div id="Rating" class="flex justify-center space-x-2 mt-1 cursor-pointer">
                        <span class="star text-gray-300">&#9733;</span>
                        <span class="star text-gray-300">&#9733;</span>
                        <span class="star text-gray-300">&#9733;</span>
                        <span class="star text-gray-300">&#9733;</span>
                        <span class="star text-gray-300">&#9733;</span>
                    </div>
                    <input type="hidden" id="rating" name="rating" required>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Kirim Feedback
                    </button>
                </div>
            </form>

            <div class="mt-6 flex justify-end">
                <button id="closeModal" class="px-6 py-2 bg-gray-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>


<!-- Modal Edit Feedback -->
<!-- Modal Edit Feedback -->
<div id="editfeedbackModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white p-6 rounded-lg w-1/3 shadow-lg">
        <h2 class="text-xl font-semibold text-center mb-4">Edit Feedback</h2>
        <form action="feedback_edit.php" method="POST">
        
        <input type="hidden" name="id" id="id_feedback" value=""> <!-- ID feedback -->
        <?php foreach ($riwayat as $p): ?>
            <input type="hidden" name="id_periksa" value="<?php echo $p['id_periksa']; ?>">
<?php endforeach; ?>

            <textarea name="ulasan" id="edit_ulasan" rows="4" class="w-full p-2 border border-gray-300 rounded-md" placeholder="Masukkan feedback"></textarea>

            <!-- Bintang untuk rating -->
            <div id="editFeedbackStars" class="flex justify-center mt-2 space-x-2">
                <!-- Bintang akan ditambahkan secara dinamis melalui JavaScript -->
            </div>
            <!-- Input hidden untuk menyimpan nilai rating -->
            <input type="hidden" name="rating" id="edit_rating" value="">

            <button type="submit" class="mt-4 w-full bg-green-500 text-white py-2 rounded-md hover:bg-green-600">Update</button>
        </form>
    </div>
</div>




<script>
    document.addEventListener('DOMContentLoaded', function () {
    const poliSelect = document.getElementById('poli');
    const jadwalSelect = document.getElementById('jadwal');
    const jadwalOptions = jadwalSelect ? Array.from(jadwalSelect.options) : []; // Simpan semua opsi jadwal jika jadwalSelect ada

    if (poliSelect && jadwalSelect) {
        poliSelect.addEventListener('change', function () {
            const selectedPoli = this.options[this.selectedIndex].text; // Mengambil nama poli dari opsi yang dipilih

            // Reset opsi jadwal
            jadwalSelect.innerHTML = '<option value="">Pilih Jadwal</option>';

            // Filter jadwal berdasarkan poli yang dipilih
            const filteredOptions = jadwalOptions.filter(option => option.dataset.poli === selectedPoli);

            // Tambahkan opsi yang difilter ke dropdown
            filteredOptions.forEach(option => {
                jadwalSelect.appendChild(option.cloneNode(true));
            });

            // Jika tidak ada jadwal yang cocok
            if (filteredOptions.length === 0) {
                const noJadwalOption = document.createElement('option');
                noJadwalOption.value = "";
                noJadwalOption.textContent = "Tidak ada jadwal tersedia untuk poli ini.";
                jadwalSelect.appendChild(noJadwalOption);
            }
        });
    }

    if (jadwalSelect) {
        // Tambahkan event listener untuk menangani perubahan jadwal
        jadwalSelect.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption) {
                const dokterName = selectedOption.dataset.dokter;
                console.log(`Nama Dokter: ${dokterName}`); // Contoh: Menampilkan nama dokter di console
                // Anda bisa menampilkan nama dokter ini di tempat lain jika diperlukan
            }
        });
    }

    const modal = document.getElementById('modalDetail');
    const openModalBtn = document.getElementById('openModal');
    const closeModalBtn = document.getElementById('closeModal');
    const closeModalButton = document.getElementById('closeModalBtn');

    if (modal && openModalBtn && closeModalBtn && closeModalButton) {
        // Open Modal Function
        openModalBtn.addEventListener('click', function(event) {
            event.preventDefault(); // Prevent link from redirecting
            modal.classList.remove('hidden');
        });

        // Close Modal Function
        closeModalBtn.addEventListener('click', function() {
            modal.classList.add('hidden');
        });

        closeModalButton.addEventListener('click', function() {
            modal.classList.add('hidden');
        });

        // Close modal when clicking outside of modal content
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.classList.add('hidden');
            }
        });
    }



        // Feedback Modal Handling
        const feedbackModal = document.getElementById('feedbackModal');
        const feedbackForm = document.getElementById('feedbackForm');
        const stars = document.querySelectorAll('.star');
        const ratingInput = document.getElementById('rating');

        stars.forEach((star, index) => {
            star.addEventListener('click', () => {
                stars.forEach((s, i) => {
                    s.classList.toggle('text-yellow-500', i <= index);
                    s.classList.toggle('text-gray-300', i > index);
                });
                ratingInput.value = index + 1;
            });
        });

        feedbackForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const idPeriksa = document.getElementById('id_periksa').value;
            const ulasan = document.getElementById('ulasan').value;
            const rating = ratingInput.value;

            if (!rating) {
                alert('Silakan pilih rating sebelum mengirim feedback.');
                return;
            }

            fetch('feedback_create.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ id_periksa: idPeriksa, ulasan: ulasan, rating: rating })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Feedback berhasil dikirim!');
                    feedbackModal.classList.add('hidden');
                    feedbackForm.reset();

                    // Reset dan hilangkan bintang yang terpilih sebelumnya
                    stars.forEach(star => star.classList.remove('text-yellow-500'));
                    stars.forEach(star => star.classList.add('text-gray-300'));

                    // Sembunyikan tombol feedback
                    const feedbackButton = document.querySelector(`[data-id-periksa="${idPeriksa}"].feedbackButton`);
                    if (feedbackButton) {
                        feedbackButton.style.display = 'none';  // Sembunyikan tombol Feedback
                    }

                    // Menampilkan rating bintang yang baru
                    const feedbackStars = document.querySelector(`[data-id-periksa="${idPeriksa}"].feedbackStars`);
                    if (feedbackStars) {
                        feedbackStars.style.display = 'block';  // Menampilkan bintang feedback
                        const starsToHighlight = feedbackStars.children;
                        for (let i = 0; i < rating; i++) {
                            starsToHighlight[i].classList.add('text-yellow-500');  // Menandai bintang yang dipilih dengan warna kuning
                        }
                    }
                } else {
                    alert('Gagal mengirim feedback.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Coba lagi nanti.');
            });
        });

        // Show feedback modal on button click
        document.querySelectorAll('.feedbackButton').forEach(button => {
            button.addEventListener('click', function () {
                const idPeriksa = this.dataset.idPeriksa;
                const tglPeriksa = this.dataset.tglPeriksa;
                const namaDokter = this.dataset.namaDokter;

                document.getElementById('id_periksa').value = idPeriksa;
                document.getElementById('tanggalPeriksa').textContent = "Tanggal Periksa: " + tglPeriksa;
                document.getElementById('namaDokter').textContent = "Dokter: " + namaDokter;
                feedbackModal.classList.remove('hidden');
            });
        });

        document.getElementById('closeModal').addEventListener('click', function () {
            feedbackModal.classList.add('hidden');
        });

        // Fungsi untuk menampilkan dan mengedit bintang
        document.querySelectorAll('.editFeedbackButton').forEach(button => {
    button.addEventListener('click', function () {
        const idFeedback = this.getAttribute('data-id'); // ID feedback

        if (idFeedback) {
            // Fetch data untuk ID feedback
            fetch(`feedback_edit.php?id=${idFeedback}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Isi modal dengan data
                        document.getElementById('id_feedback').value = data.id;
                        document.getElementById('id_periksa').value = id_periksa; // Tetap gunakan ID Periksa
                        document.getElementById('edit_ulasan').value = data.ulasan;

                        // Setup bintang
                        setupEditableStars('editFeedbackStars', 'edit_rating', data.rating);

                        // Tampilkan modal
                        document.getElementById('editfeedbackModal').classList.remove('hidden');
                    } else {
                        alert(data.message || 'Feedback tidak ditemukan.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengambil data.');
                });
        } else {
            alert('ID Feedback atau ID Periksa tidak ditemukan.');
        }
    });
});


// Fungsi untuk mengatur bintang interaktif
function setupEditableStars(starContainerId, hiddenInputId, currentRating) {
    const container = document.getElementById(starContainerId);
    const hiddenInput = document.getElementById(hiddenInputId);

    container.innerHTML = ''; // Hapus bintang sebelumnya
    const totalStars = 5; // Jumlah total bintang

    for (let i = 1; i <= totalStars; i++) {
        const star = document.createElement('span');
        star.textContent = '★'; // Gunakan simbol bintang
        star.style.cursor = 'pointer';
        star.style.fontSize = '24px';
        star.style.color = i <= currentRating ? 'gold' : 'gray';

        // Event saat hover atau klik bintang
        star.addEventListener('mouseover', () => highlightStars(container, i));
        star.addEventListener('mouseout', () => highlightStars(container, currentRating));
        star.addEventListener('click', () => {
            currentRating = i;
            hiddenInput.value = i; // Update nilai rating
        });

        container.appendChild(star);
    }

    // Set nilai awal di input hidden
    hiddenInput.value = currentRating;

    function highlightStars(container, rating) {
        [...container.children].forEach((child, index) => {
            child.style.color = index < rating ? 'gold' : 'gray';
        });
    }
}

// Menangani submit form
document.querySelector('form').addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('feedback_edit.php', {
        method: 'POST',
        body: formData,
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'Feedback berhasil diperbarui.');
                document.getElementById('editfeedbackModal').classList.add('hidden');
                location.reload(); // Muat ulang halaman setelah sukses
            } else {
                alert(data.message || 'Gagal memperbarui feedback.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan pada server.');
        });
});

// Close modal jika klik di luar modal
document.getElementById('editfeedbackModal').addEventListener('click', function (e) {
    if (e.target === this) {
        this.classList.add('hidden');
    }
});
});




</script>


<?php
$content = ob_get_clean();
include('../../components/layout_pasien.php');
?>
