<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "poliklinik";

// Membuat koneksi
$conn = new mysqli($host, $user, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Fungsi untuk menjalankan query
function runQuery($conn, $query) {
    if ($conn->query($query) === TRUE) {
        echo "Query berhasil dijalankan: " . $query . "\n";
    } else {
        echo "Error: " . $query . "\n" . $conn->error . "\n";
    }
}

// Tabel poli
runQuery($conn, "
CREATE TABLE IF NOT EXISTS poli (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_poli VARCHAR(25) NOT NULL,
    keterangan TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)
");

// Tabel dokter
runQuery($conn, "
CREATE TABLE IF NOT EXISTS dokter (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    alamat VARCHAR(255) NOT NULL,
    no_hp VARCHAR(50) NOT NULL,
    id_poli INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_poli) REFERENCES poli(id) ON DELETE CASCADE
)
");

// Tabel jadwal_periksa
runQuery($conn, "
CREATE TABLE IF NOT EXISTS jadwal_periksa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_dokter INT NOT NULL,
    hari ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu') NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    status ENUM('Aktif', 'Tidak Aktif') DEFAULT 'Aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_dokter) REFERENCES dokter(id) ON DELETE CASCADE
)
");

// Tabel pasien
runQuery($conn, "
CREATE TABLE IF NOT EXISTS pasien (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    alamat VARCHAR(255) NOT NULL,
    no_ktp VARCHAR(16) UNIQUE NOT NULL,
    no_hp VARCHAR(15) NOT NULL,
    no_rm VARCHAR(20) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)
");

// Tabel daftar_poli
runQuery($conn, "
CREATE TABLE IF NOT EXISTS daftar_poli (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pasien INT NOT NULL,
    id_jadwal INT NOT NULL,
    keluhan TEXT NULL,
    no_antrian INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pasien) REFERENCES pasien(id) ON DELETE CASCADE,
    FOREIGN KEY (id_jadwal) REFERENCES jadwal_periksa(id) ON DELETE CASCADE
)
");

// Tabel periksa
runQuery($conn, "
CREATE TABLE IF NOT EXISTS periksa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_daftar_poli INT NOT NULL,
    tgl_periksa DATETIME NOT NULL,
    catatan TEXT NULL,
    biaya_periksa INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_daftar_poli) REFERENCES daftar_poli(id) ON DELETE CASCADE
)
");

// Tabel obat
runQuery($conn, "
CREATE TABLE IF NOT EXISTS obat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_obat VARCHAR(50) NOT NULL,
    kemasan VARCHAR(35) NOT NULL,
    harga INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)
");

// Tabel detail_periksa
runQuery($conn, "
CREATE TABLE IF NOT EXISTS detail_periksa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_periksa INT NOT NULL,
    id_obat INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_periksa) REFERENCES periksa(id) ON DELETE CASCADE,
    FOREIGN KEY (id_obat) REFERENCES obat(id) ON DELETE CASCADE
)
");

// Tabel admin
runQuery($conn, "
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)
");

// Menutup koneksi
$conn->close();
?>
