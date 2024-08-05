<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_announcement'])) {
        $announcement = $_POST['announcement'];
        file_put_contents('./pengumuman.txt', $announcement);
        $message = "Announcement updated successfully.";
        $toast = true; // Flag to show toast
    }
}
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
        .toast {
        position: fixed;
        top: 5rem;
        right: 1rem;
        z-index: 1000;
        display: flex;
        align-items: center;
        padding: 1rem;
        border-radius: 0.375rem;
        background-color: #4CAF50; /* Green background */
        color: white;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .toast.hidden {
        display: none;
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

    <!-- Main Content -->
    <main class="flex-1 p-8">
        <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Kelola Pengumuman</h1>

            <form action="admin_pengumuman.php" method="post">
                <label for="announcement" class="block text-gray-700 font-bold mb-2">Pengumuman</label>
                <textarea id="announcement" name="announcement" rows="10" class="w-full p-3 border border-gray-300 rounded" required><?php echo htmlspecialchars(file_get_contents('./pengumuman.txt')); ?></textarea>
                <button type="submit" name="update_announcement" class="mt-4 bg-green-600 text-white py-2 px-4 rounded">Kirim Pengumuman</button>
            </form>
        </div>
    </main>
    <!-- Toast Notification -->
    <?php if (isset($toast)): ?>
        <div id="toast" class="toast bg-green-600 text-white p-4 rounded shadow-lg">
            <p><?php echo htmlspecialchars($message); ?></p>
        </div>
    <?php endif; ?>


    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const toast = document.getElementById('toast');
        if (toast) {
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 3000); // Hide after 3 seconds
        }
    });
    </script>
        <script>
    document.getElementById('settingsButton').addEventListener('click', function() {
        var dropdown = document.getElementById('settingsDropdown');
        dropdown.classList.toggle('hidden');
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
