<?php
session_start();
include '../connection.php';

// Pagination setup
$records_per_page = 5; // Number of records per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $records_per_page;


$stmt = $conn->prepare("SELECT * FROM orders LIMIT ? OFFSET ?");
$stmt->bind_param('ii', $records_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);

// Get total number of records for pagination
$total_result = $conn->query("SELECT COUNT(*) AS total FROM orders");
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $records_per_page);


// Handle Create, Update, Delete, and Fetch actions
$action = $_POST['action'] ?? $_GET['action'] ?? null;
$order_id = $_POST['id'] ?? $_GET['id'] ?? null;

if ($action === 'create') {
    // Handle Add Order
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];

        $stmt = $conn->prepare("INSERT INTO orders (id, name, email, phone, address) VALUES (?, ?, ?, ?, ?)");
        $order_id = uniqid(); // or use a UUID function
        $stmt->bind_param('sssss', $order_id, $name, $email, $phone, $address);
        $stmt->execute();
        $stmt->close();

        header('Location: admin_dataOrderUser.php?status=success');
        exit;
    }
} elseif ($action === 'update') {
    // Handle Edit Order
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];

        $stmt = $conn->prepare("UPDATE orders SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->bind_param('sssss', $name, $email, $phone, $address, $order_id);
        $stmt->execute();
        $stmt->close();

        header('Location: admin_dataOrderUser.php?status=success');
        exit;
    }
} elseif ($action === 'delete') {
    $order_id = $_GET['id'];

    // First, delete related items
    $stmt = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param('s', $order_id);
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }
    $stmt->close();

    // Then, delete the order
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param('s', $order_id);
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }
    $stmt->close();

    // Redirect with success message
    header('Location: admin_dataOrderUser.php?status=success');
    exit;
}

// Fetch order details for editing
if ($action === 'fetch_order') {
    $order_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param('s', $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    echo json_encode($order);
    exit;
}

// Fetch order items for viewing
if ($action === 'fetch_order_items') {
    $order_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt->bind_param('s', $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($items);
    exit;
}


// Fetch orders for display with pagination
$stmt = $conn->prepare("SELECT * FROM orders LIMIT ? OFFSET ?");
$stmt->bind_param('ii', $records_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);

// Get total number of records for pagination
$total_result = $conn->query("SELECT COUNT(*) AS total FROM orders");
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $records_per_page);
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
            visibility: hidden;
            min-width: 250px;
            margin-left: -125px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 2px;
            padding: 16px;
            position: fixed;
            z-index: 1;
            left: 50%;
            bottom: 30px;
        }

        .toast.show {
            visibility: visible;
        }

        .toast.success {
            background-color: #4CAF50;
        }

        .toast.error {
            background-color: #f44336;
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
        <div class="toast" id="toast"></div>
        <div class="container mx-auto p-6">
            <h1 class="text-2xl font-semibold mb-4">Kelola pesanan pengguna</h1>
            <a href="#" onclick="openModal('createModal')" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Tambah</a>

            <!-- Table -->
            <div class="bg-white p-6 rounded-lg shadow-md mt-4">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs text-center font-medium text-gray-500 uppercase tracking-wider">ID Pemesanan</th>
                            <th class="px-6 py-3 text-left text-xs text-center font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs text-center font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs text-center font-medium text-gray-500 uppercase tracking-wider">Telepon</th>
                            <th class="px-6 py-3 text-left text-xs text-center font-medium text-gray-500 uppercase tracking-wider">Alamat</th>
                            <th class="px-6 py-3 text-left text-xs text-center font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($orders as $order) : ?>
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($order['id']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($order['name']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($order['email']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($order['phone']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($order['address']) ?></td>
                                <td class="px-6 py-4 text-sm font-medium">
                                    <a href="#" onclick="openModal('editModal', '<?= htmlspecialchars($order['id']) ?>')" class="text-blue-600 hover:text-blue-900">Edit</a>
                                    <a href="#" onclick="openModal('viewModal', '<?= htmlspecialchars($order['id']) ?>')" class="text-green-600 hover:text-green-900 ml-2">View</a>
                                    <a href="?action=delete&id=<?= urlencode($order['id']) ?>" class="text-red-600 hover:text-red-900 ml-2" onclick="return confirm('Are you sure you want to delete this order?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="flex justify-between mt-4">
            <div>
                <?php if ($page > 1): ?>
                <a href="admin_dataOrderUser.php?page=<?= $page - 1 ?>" class="text-blue-500 hover:text-blue-700">&laquo; Previous</a>
                <?php endif; ?>
            </div>
            <div>
                <?php if ($page < $total_pages): ?>
                <a href="admin_dataOrderUser.php?page=<?= $page + 1 ?>" class="text-blue-500 hover:text-blue-700">Next &raquo;</a>
                <?php endif; ?>
            </div>
        </div>
        </div>
    </div>
    <!-- Create Order Modal -->
    <div id="createModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-2xl">
            <h2 class="text-xl font-semibold mb-4">Tambah Pesanan</h2>
            <form method="POST" action="admin_dataOrderUser.php">
                <input type="hidden" name="action" value="create">
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama</label>
                    <input type="text" id="name" name="name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-lg" required>
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" name="email" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-lg" required>
                </div>
                <div class="mb-4">
                    <label for="phone" class="block text-sm font-medium text-gray-700">Telepon</label>
                    <input type="text" id="phone" name="phone" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-lg" required>
                </div>
                <div class="mb-4">
                    <label for="address" class="block text-sm font-medium text-gray-700">Alamat</label>
                    <textarea id="address" name="address" rows="4" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-lg" required></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="closeModal('createModal')" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Batal</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 ml-2">Tambah</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Order Modal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-2xl">
            <h2 class="text-xl font-semibold mb-4">Ubah Pesanan</h2>
            <form method="POST" action="admin_dataOrderUser.php" id="editForm">
                <input type="hidden" name="action" value="update">
                <input type="hidden" id="editId" name="id">
                <div class="mb-4">
                    <label for="editName" class="block text-sm font-medium text-gray-700">Nama</label>
                    <input type="text" id="editName" name="name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-lg" required>
                </div>
                <div class="mb-4">
                    <label for="editEmail" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="editEmail" name="email" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-lg" required>
                </div>
                <div class="mb-4">
                    <label for="editPhone" class="block text-sm font-medium text-gray-700">Telepon</label>
                    <input type="text" id="editPhone" name="phone" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-lg" required>
                </div>
                <div class="mb-4">
                    <label for="editAddress" class="block text-sm font-medium text-gray-700">Alamat</label>
                    <textarea id="editAddress" name="address" rows="4" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-lg" required></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="closeModal('editModal')" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Batal</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 ml-2">Ubah</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Order Modal -->
    <div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-2xl">
            <h2 class="text-xl font-semibold mb-4">Lihat pesanan produk</h2>
            <div id="orderDetails">
                <!-- Order details will be loaded here via JavaScript -->
            </div>
            <div class="flex justify-end mt-4">
                <button type="button" onclick="closeModal('viewModal')" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        function openModal(modalId, orderId = null) {
            document.getElementById(modalId).classList.remove('hidden');

            if (modalId === 'editModal' && orderId) {
                fetch(`admin_dataOrderUser.php?action=fetch_order&id=${orderId}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('editId').value = data.id;
                        document.getElementById('editName').value = data.name;
                        document.getElementById('editEmail').value = data.email;
                        document.getElementById('editPhone').value = data.phone;
                        document.getElementById('editAddress').value = data.address;
                    });
            }

            if (modalId === 'viewModal' && orderId) {
                fetch(`admin_dataOrderUser.php?action=fetch_order_items&id=${orderId}`)
                    .then(response => response.json())
                    .then(items => {
                        let detailsHtml = '<h3 class="text-lg font-semibold mb-2">Order Items</h3><table class="min-w-full divide-y divide-gray-200"><thead><tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product ID</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th></tr></thead><tbody class="bg-white divide-y divide-gray-200">';
                        items.forEach(item => {
                            detailsHtml += `<tr><td class="px-6 py-4 text-sm text-gray-900">${item.product_id}</td><td class="px-6 py-4 text-sm text-gray-500">${item.quantity}</td></tr>`;
                        });
                        detailsHtml += '</tbody></table>';
                        document.getElementById('orderDetails').innerHTML = detailsHtml;
                    });
            }
        }

        function showToast(type, message) {
            const toast = document.getElementById('toast');
            toast.className = `toast ${type} show`;
            toast.textContent = message;
            setTimeout(() => {
                toast.className = toast.className.replace(' show', '');
            }, 3000);
        }

        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('status')) {
            const status = urlParams.get('status');
            const message = urlParams.get('message') || '';
            if (status === 'success') {
                showToast('success', 'Operation successful!');
            } else if (status === 'error') {
                showToast('error', message);
            }
        }

        document.getElementById('settingsButton').addEventListener('click', function() {
            const dropdown = document.getElementById('settingsDropdown');
            dropdown.classList.toggle('hidden');
        });
    </script>
    <script>
        function openModal(modalId, orderId = null) {
            document.getElementById(modalId).classList.remove('hidden');

            if (modalId === 'editModal' && orderId) {
                fetch(`admin_dataOrderUser.php?action=fetch_order&id=${orderId}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('editId').value = data.id;
                        document.getElementById('editName').value = data.name;
                        document.getElementById('editEmail').value = data.email;
                        document.getElementById('editPhone').value = data.phone;
                        document.getElementById('editAddress').value = data.address;
                    });
            }

            if (modalId === 'viewModal' && orderId) {
                fetch(`admin_dataOrderUser.php?action=fetch_order_items&id=${orderId}`)
                    .then(response => response.json())
                    .then(items => {
                        let detailsHtml = '<h3 class="text-lg font-semibold mb-2">Order Items</h3><table class="min-w-full divide-y divide-gray-200"><thead><tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product ID</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th></tr></thead><tbody class="bg-white divide-y divide-gray-200">';
                        items.forEach(item => {
                            detailsHtml += `<tr><td class="px-6 py-4 text-sm text-gray-900">${item.product_id}</td><td class="px-6 py-4 text-sm text-gray-500">${item.quantity}</td></tr>`;
                        });
                        detailsHtml += '</tbody></table>';
                        document.getElementById('orderDetails').innerHTML = detailsHtml;
                    });
            }
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }
    </script>
</body>

</html>