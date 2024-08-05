<?php
session_start();
include '../connection.php';



// Tangani penghapusan penilaian
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    $sql = "DELETE FROM reviews WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        $message = "Penilaian berhasil dihapus!";
        $message_type = "success";
    } else {
        $message = "Terjadi kesalahan: " . $stmt->error;
        $message_type = "error";
    }

    $stmt->close();
}

// Ambil daftar penilaian
$sql = "SELECT id, name, rating, comments FROM reviews";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kelola Penilaian</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .toast {
            opacity: 0;
            transition: opacity 0.5s ease;
        }
        .toast.show {
            opacity: 1;
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
    </nav>
    <div class="flex flex-grow items-center justify-center">
        <div class="max-w-4xl w-full bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-800">Kelola Penilaian</h2>
            <?php if (isset($message)): ?>
                <div class="toast p-4 mb-4 rounded <?php echo $message_type === 'success' ? 'bg-green-500' : 'bg-red-500'; ?> text-white">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            <table class="min-w-full bg-white border border-gray-300">
                <thead>
                    <tr>
                        <th class="border-b py-2 px-4 text-left">Nama</th>
                        <th class="border-b py-2 px-4 text-left">Penilaian</th>
                        <th class="border-b py-2 px-4 text-left">Komentar</th>
                        <th class="border-b py-2 px-4 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="border-b py-2 px-4"><?php echo htmlspecialchars($row['name']); ?></td>
                            <td class="border-b py-2 px-4"><?php echo htmlspecialchars($row['rating']); ?></td>
                            <td class="border-b py-2 px-4"><?php echo htmlspecialchars($row['comments']); ?></td>
                            <td class="border-b py-2 px-4">
                                <a href="?delete_id=<?php echo htmlspecialchars($row['id']); ?>" class="text-red-600 hover:underline">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div id="toast-container" class="fixed bottom-4 right-4 z-50"></div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            function showToast(message, type) {
                const toastContainer = document.getElementById('toast-container');
                const toast = document.createElement('div');
                toast.className = `toast p-4 mb-4 rounded ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white`;
                toast.textContent = message;
                toastContainer.appendChild(toast);

                setTimeout(() => {
                    toast.classList.add('show');
                }, 100);

                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => toast.remove(), 500);
                }, 5000);
            }

            <?php if (isset($message)): ?>
                showToast("<?php echo addslashes($message); ?>", "<?php echo $message_type; ?>");
            <?php endif; ?>
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
