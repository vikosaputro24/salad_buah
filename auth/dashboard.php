<?php
session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// You can use $_SESSION['adminname'] if you stored it
$adminname = isset($_SESSION['adminname']) ? $_SESSION['adminname'] : 'Admin';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Salad Buah</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .sidebar {
            transition: transform 0.3s ease-in-out;
        }
        .sidebar.closed {
            transform: translateX(-100%);
        }
    </style>
</head>
<body class="bg-green-100 flex">

    <!-- Sidebar -->
    <div id="sidebar" class="sidebar bg-green-800 text-white w-64 h-screen p-4 shadow-lg">
        <div class="flex items-center mb-6">
            <i class="fas fa-lemon text-white text-3xl mr-2"></i>
            <h2 class="text-2xl font-bold">Salad Buah</h2>
        </div>
        <ul>
            <li class="mb-4">
                <a href="./dashboard.php" class="flex items-center space-x-2 text-gray-300 hover:text-white hover:bg-green-700 p-2 rounded-md">
                    <i class="fas fa-home"></i>
                    <span>Beranda</span>
                </a>
            </li>
            <li class="mb-4">
                <a href="./admin_pengumuman.php" class="flex items-center space-x-2 text-gray-300 hover:text-white hover:bg-green-700 p-2 rounded-md">
                <i class="fa-solid fa-bullhorn"></i>
                    <span>Pengumuman</span>
                </a>
            </li>
            <li class="mb-4">
                <a href="./admin_dataDiriUser.php" class="flex items-center space-x-2 text-gray-300 hover:text-white hover:bg-green-700 p-2 rounded-md">
                <i class="fas fa-user"></i>
                    <span>Data Diri Pengguna</span>
                </a>
            </li>
            <li class="relative mb-4">
                <button id="settingsButton" class="flex items-center space-x-2 text-gray-300 hover:text-white hover:bg-green-700 p-2 rounded-md w-full">
                    <i class="fa-solid fa-list"></i>
                    <span>Data Pesanan</span>
                    <i class="fas fa-chevron-down ml-auto"></i>
                </button>
                <div id="settingsDropdown" class="absolute left-0 mt-2 w-full bg-green-700 text-gray-300 rounded-md hidden">
                    <a href="./admin_dataOrderUser.php" class="block px-4 py-2 hover:bg-green-600">Data Order Pengguna</a>
                    <a href="./admin_status.php" class="block px-4 py-2 hover:bg-green-600">Data Status Pesanan</a>
                </div>
            </li>
            <li class="mb-4">
                <a href="./manage.php" class="flex items-center space-x-2 text-gray-300 hover:text-white hover:bg-green-700 p-2 rounded-md">
                <i class="fas fa-box"></i>
                    <span>Produk</span>
                </a>
            </li>
            <li class="mb-4">
                <a href="./admin_penilaian.php" class="flex items-center space-x-2 text-gray-300 hover:text-white hover:bg-green-700 p-2 rounded-md">
                <i class="fa-solid fa-magnifying-glass"></i>
                    <span>Penilaian</span>
                </a>
            </li>
        </ul>
    </div>
    <div class="flex-1 flex flex-col">
        <header class="bg-green-700 text-white p-4 flex items-center justify-between shadow-md">
            <button id="menuButton" class="text-white text-2xl md:hidden">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="text-xl font-bold">Hai Admin</h1>
            <div class="flex items-center space-x-4">
                <a href="./admin_logout.php" class="hover:underline">Keluar</a>
            </div>
        </header>

<main class="flex-1 p-8 bg-green-100">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Quick Actions -->
        <div class="bg-white p-6 rounded-lg shadow-md hover:bg-green-50 transition duration-200">
            <div class="flex items-center mb-4">
                <i class="fas fa-user-circle text-green-600 text-3xl mr-4"></i>
                <h3 class="text-xl font-bold text-gray-800">Data Diri Pengguna</h3>
            </div>
            <p class="text-gray-600 mb-4">Lihat detail pengguna untuk mengelola informasi mereka.</p>
            <a href="./admin_dataDiriUser.php" class="flex items-center text-green-600 hover:text-green-800">
                <span class="text-sm font-semibold">Lihat Detail</span>
                <i class="fas fa-chevron-right ml-2"></i>
            </a>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md hover:bg-green-50 transition duration-200">
            <div class="flex items-center mb-4">
                <i class="fas fa-calendar-check text-green-600 text-3xl mr-4"></i>
                <h3 class="text-xl font-bold text-gray-800">Data Pesanan Pengguna</h3>
            </div>
            <p class="text-gray-600 mb-4">Check the status of your orders and manage them as needed.</p>
            <a href="./view_orders.php" class="flex items-center text-green-600 hover:text-green-800">
                <span class="text-sm font-semibold">Lihat Detail</span>
                <i class="fas fa-chevron-right ml-2"></i>
            </a>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md hover:bg-green-50 transition duration-200">
            <div class="flex items-center mb-4">
                <i class="fas fa-heart text-green-600 text-3xl mr-4"></i>
                <h3 class="text-xl font-bold text-gray-800">Data Status Pengguna</h3>
            </div>
            <p class="text-gray-600 mb-4">Manage your favorite fruit salads to easily find them later.</p>
            <a href="./admin_status.php" class="flex items-center text-green-600 hover:text-green-800">
                <span class="text-sm font-semibold">Lihat Detail</span>
                <i class="fas fa-chevron-right ml-2"></i>
            </a>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md hover:bg-green-50 transition duration-200">
            <div class="flex items-center mb-4">
                <i class="fas fa-cog text-green-600 text-3xl mr-4"></i>
                <h3 class="text-xl font-bold text-gray-800">Kelola Produk</h3>
            </div>
            <p class="text-gray-600 mb-4">Update your account information to keep your details current.</p>
            <a href="./manage.php" class="flex items-center text-green-600 hover:text-green-800">
                <span class="text-sm font-semibold">Kunjungi</span>
                <i class="fas fa-chevron-right ml-2"></i>
            </a>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md hover:bg-green-50 transition duration-200">
            <div class="flex items-center mb-4">
                <i class="fas fa-cog text-green-600 text-3xl mr-4"></i>
                <h3 class="text-xl font-bold text-gray-800">Data Penilaian</h3>
            </div>
            <p class="text-gray-600 mb-4">Update your account information to keep your details current.</p>
            <a href="./account_settings.php" class="flex items-center text-green-600 hover:text-green-800">
                <span class="text-sm font-semibold">Lihat Detail</span>
                <i class="fas fa-chevron-right ml-2"></i>
            </a>
        </div>
    </div>
</main>


    </div>

    <!-- Script to toggle sidebar and profile dropdown -->
    <script>
        document.getElementById('menuButton').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('closed');
        });

        document.querySelector('.relative button').addEventListener('click', function() {
            document.querySelector('.relative .hidden').classList.toggle('hidden');
        });


    
    // Close the dropdown if clicked outside of it
    window.addEventListener('click', function(event) {
        var button = document.getElementById('settingsButton');
        var dropdown = document.getElementById('settingsDropdown');
        
        if (!button.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.classList.add('hidden');
        }
    });
    </script>
</body>
</html>
