<nav class="fixed top-0 z-50 w-full bg-white border-b border-gray-200">
    <div class="px-3 py-3 lg:px-5 lg:pl-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center justify-start">
                <button
                    data-drawer-target="logo-sidebar"
                    data-drawer-toggle="logo-sidebar"
                    aria-controls="logo-sidebar"
                    type="button"
                    class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200"
                >
                    <span class="sr-only">Open sidebar</span>
                    <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
                    </svg>
                </button>
                <a href="/" class="flex ms-2 md:me-24">
                    <span class="self-center text-xl font-semibold sm:text-2xl whitespace-nowrap text-transparent bg-clip-text bg-gradient-to-r from-purple-500 to-pink-200">
                        Poliklinik
                    </span>
                </a>
            </div>
            <div class="flex items-center">
                <div class="flex items-center ms-3">
                    <button
                        type="button"
                        class="flex text-sm bg-gray-800 rounded-full focus:ring-4 focus:ring-gray-300"
                        aria-expanded="false"
                        data-dropdown-toggle="dropdown-user"
                    >
                        <span class="sr-only">Open user menu</span>
                        <img class="w-8 h-8 rounded-full" src="https://flowbite.com/docs/images/people/profile-picture-5.jpg" alt="user photo">
                    </button>
                </div>
            </div>
        </div>
    </div>
</nav>

<aside
id="sidebar"
class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0"
aria-label="Sidebar"
>
<div class="h-full px-3 pb-4 overflow-y-auto bg-white">
    <ul class="space-y-2 font-medium">
        <li>
            <a href="dashboard.php" class="flex items-center p-2 text-purple-800 rounded-lg hover:bg-gray-100">
                <svg class="w-5 h-5 text-purple-800 transition duration-75 group-hover:text-purple-900" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 22 21">
                    <path d="M16.975 11H10V4.025a1 1 0 0 0-1.066-.998 8.5 8.5 0 1 0 9.039 9.039.999.999 0 0 0-1-1.066h.002Z"/>
                    <path d="M12.5 0c-.157 0-.311.01-.565.027A1 1 0 0 0 11 1.02V10h8.975a1 1 0 0 0 1-.935c.013-.188.028-.374.028-.565A8.51 8.51 0 0 0 12.5 0Z"/>
                </svg>
                <span class="ms-3">Dashboard</span>
            </a>
        </li>
        <li>
            <button
                type="button"
                class="flex items-center w-full p-2 text-base text-purple-800 transition duration-75 rounded-lg group hover:bg-gray-100"
                aria-controls="dropdown-example"
                data-collapse-toggle="dropdown-example"
                onclick="toggleDropdown('dropdown-example')">
                <!-- Ikon Master Data -->
                <svg class="flex-shrink-0 w-5 h-5 text-purple-800 transition duration-75 group-hover:text-purple-900" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 3a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V3zm2 0v14h12V3H4zm2 3h8v2H6V6zm0 4h8v2H6v-2z"/>
                </svg>
                <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">Master Data</span>
                <svg class="w-3 h-3 transition-transform transform" id="dropdown-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1l4 4 4-4"/>
                </svg>
            </button>
            <ul id="dropdown-example" class="hidden py-2 space-y-2">
                <li>
                    <a href="pasien.php" class="flex items-center w-full p-2 text-purple-800 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100">Pasien</a>
                </li>
                <li>
                    <a href="dokter.php" class="flex items-center w-full p-2 text-purple-800 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100">Dokter</a>
                </li>
                <li>
                    <a href="obat.php" class="flex items-center w-full p-2 text-purple-800 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100">Obat</a>
                </li>
                <li>
                    <a href="poli.php" class="flex items-center w-full p-2 text-purple-800 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100">Poli</a>
                </li>
            </ul>
        </li>
    </ul>
</div>
</aside>

<script>
    function toggleDropdown(id) {
        const dropdown = document.getElementById(id);
        const icon = document.getElementById('dropdown-icon');

        dropdown.classList.toggle('hidden');
        icon.classList.toggle('rotate-180'); // Rotasi untuk panah dropdown
    }

    document.addEventListener('DOMContentLoaded', () => {
    const toggleButton = document.querySelector('[data-drawer-toggle="logo-sidebar"]');
    const sidebar = document.getElementById('sidebar');

    // Tambahkan event listener ke tombol
    toggleButton.addEventListener('click', () => {
        if (sidebar.classList.contains('-translate-x-full')) {
            // Tampilkan sidebar
            sidebar.classList.remove('-translate-x-full');
        } else {
            // Sembunyikan sidebar
            sidebar.classList.add('-translate-x-full');
        }
    });
});
</script>
