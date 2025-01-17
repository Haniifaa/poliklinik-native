<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="relative bg-gradient-to-r from-[#F3F4FE] via-[#EBF4FF] to-[#E8F7FF] min-h-screen overflow-hidden flex items-center justify-center">
    <?php if (isset($_SESSION['success'])): ?>
        <script>
            alert("<?= $_SESSION['success']; ?>");
        </script>
    <?php unset($_SESSION['success']); endif; ?>

    <?php if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
        <script>
            alert("<?= implode('\n', $_SESSION['errors']); ?>");
        </script>
    <?php unset($_SESSION['errors']); endif; ?>

    <!-- Background Elements -->
    <div class="absolute inset-0 z-0">
        <!-- Circular Gradients -->
        <div class="absolute -top-20 -left-20 w-72 h-72 bg-gradient-to-r from-purple-400 to-pink-500 rounded-full blur-3xl opacity-30"></div>
        <div class="absolute -bottom-10 -right-10 w-64 h-64 bg-gradient-to-r from-blue-400 to-purple-500 rounded-full blur-3xl opacity-30"></div>

        <!-- Decorative Shapes -->
        <div class="absolute top-1/4 left-1/4 transform rotate-45 w-32 h-32 bg-purple-300/50 rounded-xl blur-xl"></div>
        <div class="absolute bottom-1/4 right-1/4 transform -rotate-45 w-40 h-40 bg-blue-300/50 rounded-full blur-lg"></div>
    </div>

    <!-- Card Section -->
    <section class="relative z-10 w-full max-w-sm bg-white bg-opacity-20 rounded-lg shadow-lg p-4 backdrop-blur-sm">
        <div class="text-center mb-4">
            <a href="/" class="flex justify-center items-center mb-6 text-2xl font-semibold text-gray-900">
                <h5 class="text-xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-purple-500 via-purple-400 to-pink-400">
                    Poliklinik
                </h5>
            </a>
            <h1 class="text-xl font-bold leading-tight tracking-tight text-grey-800 md:text-2xl">
                Daftar
            </h1>
        </div>

        <!-- Form -->
        <form class="space-y-3" action="proses_register.php" method="POST">
            <div>
                <label for="nama" class="block mb-1 text-sm text-gray-900">Nama Lengkap</label>
                <input type="text" name="nama" id="nama" class="bg-gray-50 border border-gray-300 text-sm rounded-md focus:ring-blue-500 focus:border-blue-500 block w-full px-2 py-1.5" placeholder="Nama lengkap" required>
            </div>
            <div>
                <label for="alamat" class="block mb-1 text-sm text-gray-900">Alamat</label>
                <textarea name="alamat" id="alamat" rows="2" class="bg-gray-50 border border-gray-300 text-sm rounded-md focus:ring-blue-500 focus:border-blue-500 block w-full px-2 py-1.5" placeholder="Alamat" required></textarea>
            </div>
            <div>
                <label for="no_ktp" class="block mb-1 text-sm text-gray-900">No KTP</label>
                <input type="text" name="no_ktp" id="no_ktp" class="bg-gray-50 border border-gray-300 text-sm rounded-md focus:ring-blue-500 focus:border-blue-500 block w-full px-2 py-1.5" placeholder="Nomor KTP" maxlength="16" required>
            </div>
            <div>
                <label for="no_hp" class="block mb-1 text-sm text-gray-900">No HP</label>
                <input type="text" name="no_hp" id="no_hp" class="bg-gray-50 border border-gray-300 text-sm rounded-md focus:ring-blue-500 focus:border-blue-500 block w-full px-2 py-1.5" placeholder="Nomor HP" maxlength="15" required>
            </div>
            <button type="submit" class="w-full text-white bg-purple-500 hover:bg-purple-600 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-md text-sm px-4 py-2 text-center">
                Daftar
            </button>
            <p class="text-xs text-gray-500 text-center">
                Sudah punya akun? <a href="login.php" class="font-medium text-purple-600 hover:underline">Masuk di sini</a>
            </p>
        </form>
    </section>
</body>
</html>
