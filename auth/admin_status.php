<?php
session_start();
include '../connection.php';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = $_POST['order_id'];
    $newStatus = $_POST['status'];

    // Update the status in the database
    $updateSql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param('ss', $newStatus, $orderId);

    if ($stmt->execute()) {
        $message = "Status updated successfully.";
    } else {
        $message = "Error updating status.";
    }
    $stmt->close();
}

// Fetch orders from the database
$limit = 5; // Number of orders per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch orders from the database
$sql = "SELECT * FROM orders LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Fetch total number of orders for pagination
$totalSql = "SELECT COUNT(*) AS total FROM orders";
$totalResult = $conn->query($totalSql);
$totalRow = $totalResult->fetch_assoc();
$totalOrders = $totalRow['total'];
$totalPages = ceil($totalOrders / $limit);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Tailwind CSS utilities are used for styling */
        .toast {
            position: fixed;
            top: 5rem;
            right: 1rem;
            z-index: 50;
            display: none;
        }

        .toast-show {
            display: block;
            animation: fadeInOut 3s forwards;
        }

        @keyframes fadeInOut {
            0% {
                opacity: 0;
            }

            10% {
                opacity: 1;
            }

            90% {
                opacity: 1;
            }

            100% {
                opacity: 0;
            }
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
        <h1 class="text-2xl font-semibold mb-4 mt-4 ml-7">Kelola status pengguna</h1>
        <div class="container mx-auto p-6 rounded-lg">
            <!-- Toast Notification -->
            <div id="toast" class="toast bg-green-500 text-white p-4 rounded">
                <span id="toast-message"></span>
            </div>

            <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="py-3 px-6 text-center">ID Pemesanan</th>
                        <th class="py-3 px-6 text-center">Nama</th>
                        <th class="py-3 px-6 text-center">Telepon</th>
                        <th class="py-3 px-6 text-center">Email</th>
                        <th class="py-3 px-6 text-center">Status</th>
                        <th class="py-3 px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0) : ?>
                        <?php while ($row = $result->fetch_assoc()) : ?>
                            <tr>
                                <td class="py-3 px-6 border-b"><?= htmlspecialchars($row['id']) ?></td>
                                <td class="py-3 px-6 border-b"><?= htmlspecialchars($row['name']) ?></td>
                                <td class="py-3 px-6 border-b"><?= htmlspecialchars($row['phone']) ?></td>
                                <td class="py-3 px-6 border-b"><?= htmlspecialchars($row['email']) ?></td>
                                <td class="py-3 px-6 border-b"><?= htmlspecialchars($row['status'] ?? '') ?></td>
                                <td class="py-3 px-6 border-b">
                                    <form action="" method="post" class="flex items-center">
                                        <input type="hidden" name="order_id" value="<?= htmlspecialchars($row['id']) ?>">
                                        <select name="status" class="border border-gray-300 rounded p-1 mr-2">
                                            <option value="Menunggu Konfirmasi" <?= $row['status'] === 'Menunggu Konfirmasi' ? 'selected' : '' ?>>Menunggu Konfirmasi</option>
                                            <option value="Pesanan Diproses" <?= $row['status'] === 'Pesanan Diproses' ? 'selected' : '' ?>>Pesanan Diproses</option>
                                            <option value="Pesanan Diantar" <?= $row['status'] === 'Pesanan Diantar' ? 'selected' : '' ?>>Pesanan Diantar</option>
                                            <option value="Pesanan Selesai" <?= $row['status'] === 'Pesanan Selesai' ? 'selected' : '' ?>>Pesanan Selesai</option>
                                        </select>
                                        <button type="submit" name="update_status" class="bg-blue-500 text-white px-3 py-1 rounded">Ubah</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6" class="py-3 px-6 text-center">No orders found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="flex justify-center mt-6">
                <nav>
                    <ul class="flex space-x-2">
                        <?php if ($page > 1) : ?>
                            <li>
                                <a href="?page=<?= $page - 1 ?>" class="bg-gray-300 text-gray-800 px-4 py-2 rounded">Previous</a>
                            </li>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                            <li>
                                <a href="?page=<?= $i ?>" class="bg-gray-300 text-gray-800 px-4 py-2 rounded <?= $i === $page ? 'bg-green-500 text-white' : '' ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages) : ?>
                            <li>
                                <a href="?page=<?= $page + 1 ?>" class="bg-gray-300 text-gray-800 px-4 py-2 rounded">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Show toast message if there is a message from PHP
                <?php if (isset($message)) : ?>
                    const toast = document.getElementById('toast');
                    const toastMessage = document.getElementById('toast-message');
                    toastMessage.textContent = <?= json_encode($message) ?>;
                    toast.classList.add('toast-show');
                    setTimeout(() => {
                        toast.classList.remove('toast-show');
                    }, 3000); // Hide toast after 3 seconds
                <?php endif; ?>
            });
        </script>
        <script>
            document.getElementById('settingsButton').addEventListener('click', function() {
                const dropdown = document.getElementById('settingsDropdown');
                dropdown.classList.toggle('hidden');
            });
        </script>
</body>

</html>

<?php
// Close connection
$conn->close();
?>