<?php
session_start();
include_once("../../config/koneksi.php");

// Cek apakah pasien sudah login
if (!isset($_SESSION['pasien'])) {
    $_SESSION['error'] = 'Silakan daftar terlebih dahulu.';
    header("Location: login.php"); // Arahkan ke halaman login pasien
    exit;
}

// Mengambil data pasien dari session
$pasien = $_SESSION['pasien'];

// Ambil riwayat pemeriksaan pasien (10 riwayat terakhir)
$query = "
    SELECT dp.*, j.id_dokter, d.nama AS dokter_nama, p.keluhan, dp.tgl_periksa, 
           GROUP_CONCAT(CONCAT(o.nama_obat, '-', o.kemasan) ORDER BY o.nama_obat ASC) AS nama_obat,
           CASE
               WHEN dp.tgl_periksa IS NOT NULL THEN 'Sudah Diperiksa'
               ELSE 'Belum Diperiksa'
           END AS status
    FROM daftar_poli p
    LEFT JOIN periksa dp ON dp.id_daftar_poli = p.id -- Bergabung dengan id_daftar_poli
    JOIN jadwal_periksa j ON p.id_jadwal = j.id
    LEFT JOIN dokter d ON j.id_dokter = d.id
    LEFT JOIN detail_periksa dp_obat ON dp.id = dp_obat.id_periksa
    LEFT JOIN obat o ON dp_obat.id_obat = o.id
    WHERE p.id_pasien = :id_pasien -- Menggunakan id_pasien di daftar_poli
    GROUP BY dp.id, p.id, j.id, d.id
    ORDER BY dp.tgl_periksa DESC
    LIMIT 10
";


$stmt = $pdo->prepare($query);
$stmt->execute(['id_pasien' => $pasien['id']]);
$riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tentukan status pemeriksaan (sudah/perlu diperiksa)
// foreach ($riwayat as &$item) {
//     if (!empty($item['status']) && $item['status'] === 'Sudah Diperiksa') {
//         $item['status'] = 'Sudah Diperiksa';
//     } else {
//         $item['status'] = 'Belum Diperiksa';
//     }
// }

ob_start();
?>

<div class="p-4 mt-14">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Dashboard</h1>

    <div class="relative p-6 rounded-lg shadow-lg mb-8 z-10 bg-gradient-to-r from-white to-white">
        <!-- Circular Gradient Background -->
        <div class="absolute inset-0 rounded-lg bg-gradient-to-r from-white to-white overflow-hidden">
            <div class="absolute -top-20 -left-20 w-96 h-96 bg-gradient-to-r from-white to-pink-500 rounded-full blur-3xl opacity-30"></div>
            <div class="absolute -bottom-10 -right-10 w-72 h-72 bg-gradient-to-r from-blue-400 to-purple-500 rounded-full blur-3xl opacity-30"></div>
        </div>

        <!-- Greeting Content -->
        <div class="relative z-10">
            <h2 class="text-3xl font-semibold text-purple-500">Halo, <?= $pasien['nama'] ?>!</h2>
            <p class="mt-2 text-md text-gray-600">Selamat datang kembali! Siap untuk mendaftar ke dokter dan mendapatkan layanan kesehatan terbaik? Ayo jelajahi dashboard Anda dan daftar untuk konsultasi hari ini.</p>
        </div>
    </div>

    <div class="p-4 mt-14">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Riwayat Terakhir Pasien</h1>

        <!-- Tabel Riwayat -->
        <div class="relative w-full overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">No</th>
                        <th scope="col" class="px-6 py-3">Tanggal Periksa</th>
                        <th scope="col" class="px-6 py-3">Nama Dokter</th>
                        <th scope="col" class="px-6 py-3">Keluhan</th>
                        <th scope="col" class="px-6 py-3">Catatan</th>
                        <th scope="col" class="px-6 py-3">Obat</th>
                        <th scope="col" class="px-6 py-3">Biaya Periksa</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($riwayat)): ?>
                        <?php foreach ($riwayat as $index => $r): ?>
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900"><?= $index + 1 ?></th>
                                <td class="px-6 py-4"><?= $r['tgl_periksa'] ?></td>
                                <td class="px-6 py-4"><?= $r['dokter_nama'] ?></td>
                                <td class="px-6 py-4"><?= $r['keluhan'] ?></td>
                                <td class="px-6 py-4"><?= $r['catatan'] ?></td>
                                <td class="px-6 py-4"><?= $r['nama_obat'] ?? '-' ?></td>
                                <td class="px-6 py-4">Rp <?= number_format($r['biaya_periksa'], 0, ',', '.') ?></td>
                                <td class="px-6 py-4 w-40">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        <?= $r['status'] === 'Sudah Diperiksa' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' ?>">
                                        <?= $r['status'] ?>
                                    </span>
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
    </div>
</div>

<?php
$content = ob_get_clean();
include('../../components/layout_pasien.php');
?>
