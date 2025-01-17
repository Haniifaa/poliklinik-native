<?php
session_start();
include_once("../../config/koneksi.php");

if (!isset($_SESSION['dokter'])) {
    die("Dokter tidak ditemukan dalam session.");
}

// Ambil data dokter dari session
$dokter = $_SESSION['dokter'];

// Ambil riwayat periksa berdasarkan dokter yang login
$query = "
    SELECT p.id AS id_periksa,
       p.tgl_periksa,
       ps.nama AS nama_pasien,
       ps.alamat AS alamat_pasien,
       ps.no_ktp,
       ps.no_hp,
       ps.no_rm,
       dp.id_jadwal,
       dp.id_pasien,
       jp.hari,
       jp.jam_mulai,
       jp.jam_selesai,
       dp.keluhan,
       GROUP_CONCAT(o.nama_obat SEPARATOR ', ') AS nama_obat,
       GROUP_CONCAT(o.kemasan SEPARATOR ', ') AS kemasan,
       d.nama AS nama_dokter,
       p.biaya_periksa,  -- Menggunakan alias p untuk tabel periksa
       p.catatan
FROM periksa AS p
JOIN daftar_poli AS dp ON p.id_daftar_poli = dp.id
JOIN pasien AS ps ON dp.id_pasien = ps.id
JOIN jadwal_periksa AS jp ON dp.id_jadwal = jp.id
LEFT JOIN detail_periksa ON p.id = detail_periksa.id_periksa
LEFT JOIN obat AS o ON detail_periksa.id_obat = o.id
JOIN dokter AS d ON jp.id_dokter = d.id
WHERE jp.id_dokter = ?
GROUP BY p.id
ORDER BY p.tgl_periksa DESC;


";

$stmt = $pdo->prepare($query);
$stmt->execute([$dokter['id']]);  // Menyaring berdasarkan id_dokter
$riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);
ob_start();
?>

<div class="p-4 mt-14">
    <!-- Judul H1 -->
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Riwayat Pasien</h1>

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
    </div>

    <!-- Tabel -->
    <div class="relative w-full overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3">No</th>
                    <th scope="col" class="px-6 py-3">Nama Pasien</th>
                    <th scope="col" class="px-6 py-3">Alamat</th>
                    <th scope="col" class="px-6 py-3">No. KTP</th>
                    <th scope="col" class="px-6 py-3">No. Telepon</th>
                    <th scope="col" class="px-6 py-3">No. RM</th>
                    <th scope="col" class="px-6 py-3">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($riwayat)): ?>
                    <?php foreach ($riwayat as $index => $r): ?>
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap"><?= $index + 1; ?></th>
                            <td class="px-6 py-4"><?= htmlspecialchars($r['nama_pasien']); ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($r['alamat_pasien']); ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($r['no_ktp']); ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($r['no_hp']); ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($r['no_rm']); ?></td>
                            <td class="flex items-center px-6 py-4">
                                <button onclick="showDetail(<?php echo $r['id_periksa']; ?>)" class="font-medium text-blue-600 hover:underline" aria-label="Detail Riwayat Periksa">
                                    Detail Riwayat Periksa
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4">Tidak ada data pasien.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal Detail Riwayat -->
    <div id="detailModal" class="fixed inset-0 z-50 hidden bg-gray-500 bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-4xl w-full">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Detail Riwayat Periksa</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">No</th>
                            <th scope="col" class="px-6 py-3">Tanggal Periksa</th>
                            <th scope="col" class="px-6 py-3">Nama Pasien</th>
                            <th scope="col" class="px-6 py-3">Nama Dokter</th>
                            <th scope="col" class="px-6 py-3">Keluhan</th>
                            <th scope="col" class="px-6 py-3">Obat</th>
                            <th scope="col" class="px-6 py-3">Biaya Periksa</th>
                        </tr>
                    </thead>
                    <tbody id="modal-detail-body">
                        <!-- Isi modal akan dimuat dengan JavaScript -->
                    </tbody>
                </table>
            </div>
            <div class="mt-4 flex justify-end">
                <button onclick="closeModal()" class="bg-purple-500 text-white py-2 px-4 rounded">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showDetail(id) {
        const modal = document.getElementById('detailModal');
        const riwayat = <?= json_encode($riwayat); ?>;
        const riwayatDetail = riwayat.find(item => item.id_periksa == id);

        if (riwayatDetail) {
            const modalBody = document.getElementById('modal-detail-body');

            // Gabungkan nama obat dan kemasan
            const obatKemasanList = [];
            const namaObatArray = riwayatDetail.nama_obat.split(', ');
            const kemasanArray = riwayatDetail.kemasan.split(', ');

            for (let i = 0; i < namaObatArray.length; i++) {
                obatKemasanList.push(namaObatArray[i] + (kemasanArray[i] ? '-' + kemasanArray[i] : ''));
            }

            // Generate nomor urut
            modalBody.innerHTML = `
                <tr>
                    <td class="px-6 py-4">1</td>
                    <td class="px-6 py-4">${riwayatDetail.tgl_periksa}</td>
                    <td class="px-6 py-4">${riwayatDetail.nama_pasien}</td>
                    <td class="px-6 py-4">${riwayatDetail.nama_dokter}</td>
                    <td class="px-6 py-4">${riwayatDetail.keluhan}</td>
                    <td class="px-6 py-4">${obatKemasanList.join(', ')}</td>
                    <td class="px-6 py-4">Rp ${riwayatDetail.biaya_periksa.toLocaleString()}</td>
                </tr>
            `;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        } else {
            console.error("Data tidak ditemukan");
        }
    }

    function closeModal() {
        const modal = document.getElementById('detailModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>


<?php
$content = ob_get_clean();
include('../../components/layout_dokter.php');
?>
