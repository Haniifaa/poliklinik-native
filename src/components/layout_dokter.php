<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <title>Dokter</title>
</head>
<body class="h-full bg-white">
    <div class="min-h-full">
    <?php include('../../components/sidebar_dokter.php'); ?> 
        <main class="p-4 sm:ml-64 bg-white">
        <?php echo isset($content) ? $content : '<p>Konten belum diatur</p>'; ?>
        </main>
        <?php include('../../components/footer.php'); ?>

      </div>
</body>
</html>

