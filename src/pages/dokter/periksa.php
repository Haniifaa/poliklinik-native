<?php
session_start();
include_once("../../config/koneksi.php");

// Mendapatkan ID pasien dari URL
$id = $_GET['id'] ?? null;

// Validasi ID pasien
if (!$id) {
    echo "ID pasien tidak ditemukan!";
    exit;
}

try {
    // Ambil data pasien dan daftar poli
    $query = "
        SELECT p.*, dp.id AS id_daftar_poli
        FROM daftar_poli dp
        JOIN pasien p ON dp.id_pasien = p.id
        WHERE dp.id = ?
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);
    $pasien = $stmt->fetch();

    // Pastikan data pasien ditemukan
    if (!$pasien) {
        echo "Data pasien tidak ditemukan!";
        exit;
    }

    // Ambil daftar poli dan obat-obatan
    $daftarPoliStmt = $pdo->query("SELECT * FROM daftar_poli");
    $daftarPoli = $daftarPoliStmt->fetchAll();

    $obatStmt = $pdo->query("SELECT * FROM obat");
    $obatList = $obatStmt->fetchAll();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

$tgl_periksa = $_POST['tgl_periksa'] ?? null;

if ($tgl_periksa) {
    $tgl_periksa = date('Y-m-d H:i:s', strtotime($tgl_periksa));
} else {
    $tgl_periksa = ''; // Atau Anda bisa set default value, misalnya ''
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pdo->beginTransaction();  // Mulai transaksi di sini untuk menghindari kesalahan
    try {
        // Validasi input
        $id_daftar_poli = $_POST['id_daftar_poli'] ?? null;
        $tgl_periksa = $_POST['tgl_periksa'] ?? null;
        $catatan = $_POST['catatan'] ?? null;
        $obatArray = $_POST['id_obat'] ?? [];

        // Validasi data
        if (!$id_daftar_poli || !$tgl_periksa || !is_array($obatArray) || empty($obatArray)) {
            throw new Exception('Data input tidak lengkap atau tidak valid!');
        }

        // Validasi apakah id_daftar_poli ada di database
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM daftar_poli WHERE id = ?");
        $stmt->execute([$id_daftar_poli]);
        if ($stmt->fetchColumn() == 0) {
            throw new Exception('ID Daftar Poli tidak ditemukan!');
        }

        // Validasi apakah setiap obat ada di database
        foreach ($obatArray as $id_obat) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM obat WHERE id = ?");
            $stmt->execute([$id_obat]);
            if ($stmt->fetchColumn() == 0) {
                throw new Exception("Obat dengan ID {$id_obat} tidak ditemukan!");
            }
        }

        // Biaya dokter tetap
        $biayaDokter = 150000;

        // Hitung total biaya obat
        $totalBiayaObat = 0;
        foreach ($obatArray as $id_obat) {
            $stmt = $pdo->prepare("SELECT harga FROM obat WHERE id = ?");
            $stmt->execute([$id_obat]);
            $obat = $stmt->fetch();
            $totalBiayaObat += $obat['harga'];
        }

        // Hitung total biaya pemeriksaan
        $biayaPeriksa = $biayaDokter + $totalBiayaObat;

        // Simpan data ke tabel 'periksa'
        
        $stmt = $pdo->prepare("INSERT INTO periksa (id_daftar_poli, tgl_periksa, catatan, biaya_periksa) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id_daftar_poli, $tgl_periksa, $catatan, $biayaPeriksa]);
        $periksa_id = $pdo->lastInsertId(); // Mendapatkan ID periksa yang baru saja disimpan

        // Simpan data ke tabel 'detail_periksa' untuk setiap obat
        foreach ($obatArray as $id_obat) {
            $stmt = $pdo->prepare("INSERT INTO detail_periksa (id_periksa, id_obat) VALUES (?, ?)");
            $stmt->execute([$periksa_id, $id_obat]);
        }

        // Commit transaksi jika semua proses berhasil
        $pdo->commit();

        // Redirect dengan pesan sukses
        echo "<script>alert('Data berhasil disimpan!'); window.location.href = 'periksa_pasien.php';</script>";
        exit();
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi kesalahan
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        // Log error
        error_log("Terjadi kesalahan: " . $e->getMessage(), 0);

        echo "<script>alert('Terjadi kesalahan: " . $e->getMessage() . "'); window.history.back();</script>";
    }
}

ob_start();
?>



<div class="p-4 mt-14">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Memeriksa Pasien</h1>

    <div class="max-w-4xl mx-auto my-8 p-6 bg-white shadow-lg rounded-lg">
        <form method="POST" action="" enctype="multipart/form-data" id="periksa-form">

            <div class="space-y-12">
                <div class="sm:col-span-6">
                    <label for="nama_pasien" class="block text-sm font-medium text-gray-900">Nama Pasien</label>
                    <div class="mt-2">
                        <input type="text" name="nama" id="nama" value="<?php echo $pasien['nama']; ?>" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400" readonly>
                    </div>
                </div>

                <input type="hidden" name="id_daftar_poli" value="<?php echo $pasien['id_daftar_poli']; ?>">

                <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    <div class="sm:col-span-3">
                        <label for="tgl-periksa" class="block text-sm font-medium text-gray-900">Tanggal Pemeriksaan</label>
                        <div class="mt-2">
                            <input type="datetime-local" name="tgl_periksa" id="tgl_periksa" value="<?php echo $tgl_periksa; ?>" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300">
                        </div>
                    </div>
                </div>

                <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    <div class="sm:col-span-6">
                        <label for="catatan" class="block text-sm font-medium text-gray-900">Catatan Pemeriksaan</label>
                        <div class="mt-2">
                            <textarea name="catatan" id="catatan" rows="3" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300"></textarea>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
    <label for="id_obat" class="block text-sm font-medium text-gray-900">Pilih Obat</label>
    <div class="flex items-center gap-4 mb-4">
        <select id="id_obat" name="id_obat[]" class="block w-2/3 rounded-md bg-white px-3 py-2 text-base text-gray-900 outline outline-1 outline-gray-300" required>
            <option value="">-- Pilih Obat --</option>
            <?php foreach ($obatList as $obat) { ?>
                <option value="<?php echo $obat['id']; ?>" data-harga="<?php echo $obat['harga']; ?>">
                    <?php echo $obat['nama_obat']; ?> - <?php echo $obat['kemasan']; ?> (Rp<?php echo number_format($obat['harga'], 0, ',', '.'); ?>)
                </option>
            <?php } ?>
        </select>

        <button
            type="button"
            id="tambah-obat-btn"
            onclick="addObatToList()"
            class="px-4 py-2 bg-purple-500 text-white text-sm rounded-lg hover:bg-purple-600">
            Tambah Obat
        </button>
    </div>

    <div id="selected-obat-list" class="mt-4 space-y-2">
        <!-- Daftar obat yang dipilih akan ditambahkan di sini -->
    </div>
</div>
                


                <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    <div class="sm:col-span-3">
                        <label for="biaya-periksa" class="block text-sm font-medium text-gray-900">Biaya Pemeriksaan</label>
                        <div class="mt-2">
                            <input type="number" name="biaya_periksa" id="biaya-periksa" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300" readonly>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-x-6">
                    <button type="button" class="text-sm font-semibold text-gray-900">Cancel</button>
                    <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
// Fungsi untuk menambah baris obat
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

    // Cek apakah obat sudah ditambahkan sebelumnya
    if (Array.from(selectedObatList.children).some(item => item.dataset.id === selectedValue)) {
        alert('Obat ini sudah ditambahkan.');
        return;
    }

    // Buat elemen baru untuk obat yang dipilih
    const obatItem = document.createElement('div');
    obatItem.className = 'flex items-center justify-between bg-gray-100 p-2 rounded-lg shadow-sm';
    obatItem.dataset.id = selectedValue;
    obatItem.dataset.harga = selectedHarga; // Menyimpan harga obat di data-harga

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

    // Reset dropdown setelah obat ditambahkan
    obatSelect.value = "";

    // Update biaya periksa setelah menambah obat
    updateBiayaPeriksa();
}

// Fungsi untuk menghitung biaya pemeriksaan
function updateBiayaPeriksa() {
    const biayaDokter = 150000; // Biaya tetap dokter
    let totalBiayaObat = 0;

    // Iterasi melalui semua obat yang dipilih
    document.querySelectorAll("#selected-obat-list div").forEach(function(item) {
        const harga = parseFloat(item.dataset.harga) || 0;
        totalBiayaObat += harga; // Tambahkan harga obat ke total
    });

    // Total biaya pemeriksaan
    const totalBiaya = biayaDokter + totalBiayaObat;
    document.getElementById("biaya-periksa").value = totalBiaya;
}

// Fungsi untuk menghapus obat
function removeObat(button) {
    const obatItem = button.parentElement;
    obatItem.remove();
    
    // Update biaya periksa setelah menghapus obat
    updateBiayaPeriksa();
}
</script>

<?php
$content = ob_get_clean();
include('../../components/layout_dokter.php'); ?>
