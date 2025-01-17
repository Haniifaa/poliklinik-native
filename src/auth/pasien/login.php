<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="relative bg-gradient-to-r from-[#F3F4FE] via-[#EBF4FF] to-[#E8F7FF] min-h-screen overflow-hidden flex items-center justify-center">
    <?php if (isset($_SESSION['success'])): ?>
    <script>
        alert("<?php echo $_SESSION['success']; ?>");
    </script>
    <?php unset($_SESSION['success']); endif; ?>

    <?php if (isset($_SESSION['errors']) && count($_SESSION['errors']) > 0): ?>
    <script>
        alert("<?php echo implode('\n', $_SESSION['errors']); ?>");
    </script>
    <?php unset($_SESSION['errors']); endif; ?>

    <!-- Background Elements -->
    <div class="absolute inset-0 z-0">
        <!-- Circular Gradients -->
        <div class="absolute -top-20 -left-20 w-96 h-96 bg-gradient-to-r from-purple-400 to-pink-500 rounded-full blur-3xl opacity-30"></div>
        <div class="absolute -bottom-10 -right-10 w-72 h-72 bg-gradient-to-r from-blue-400 to-purple-500 rounded-full blur-3xl opacity-30"></div>

        <!-- Decorative Shapes -->
        <div class="absolute top-1/4 left-1/4 transform rotate-45 w-32 h-32 bg-purple-300/50 rounded-xl blur-xl"></div>
        <div class="absolute bottom-1/4 right-1/4 transform -rotate-45 w-40 h-40 bg-blue-300/50 rounded-full blur-lg"></div>
    </div>

    <!-- Card Section -->
    <section class="relative z-10 w-full max-w-md bg-white bg-opacity-20 rounded-lg shadow-lg p-6 backdrop-blur-sm">
        <div class="text-center mb-6">
            <a href="/" class="flex justify-center items-center mb-6 text-2xl font-semibold text-gray-900">
                    <h5 class="text-xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-purple-500 via-purple-400 to-pink-400">
                        Poliklinik
                    </h5>
            </a>
            <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl">
                Masuk ke akun
            </h1>
        </div>

        <!-- Form Section -->
        <form class="space-y-4 md:space-y-6" action="proses-login.php" method="POST">
            <div>
                <label for="nama" class="block text-sm font-medium text-gray-700">Nama</label>
                <input type="text" name="nama" id="nama" required class="w-full mt-1 px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:ring-primary-300 focus:border-primary-300">
            </div>
            <div>
                <label for="alamat" class="block text-sm font-medium text-gray-700">Password</label>
                <input
                    type="password"
                    name="alamat"
                    id="alamat"
                    required
                    class="w-full mt-1 px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg focus:ring-primary-300 focus:border-primary-300"
                    placeholder="Password"
                >
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-start">
                </div>
                <a href="#" class="text-sm font-medium text-primary-600 hover:underline">Forgot password?</a>
            </div>
            <button type="submit" class="w-full text-white bg-purple-500 hover:bg-purple-600 focus:ring-4 focus:outline-none focus:ring-blue-300/50 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                Masuk
            </button>
            <p class="text-sm font-light text-gray-500">
                Don’t have an account yet? <a href="#" class="font-medium text-primary-600 hover:underline">Sign up</a>
            </p>
        </form>
    </section>
</body>
</html>
