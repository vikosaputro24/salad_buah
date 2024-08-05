<?php
session_start();
include '../connection.php';

// Handle Create, Update, Delete
$action = $_POST['action'] ?? $_GET['action'] ?? null;
$user_id = $_POST['user_id'] ?? $_GET['id'] ?? null;

if ($action === 'create') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        
        // Handle file upload
        $profile_picture = null;
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
            $fileName = $_FILES['profile_picture']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            // Define allowed file extensions and directory
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $uploadDir = 'uploads/';
            $filePath = $uploadDir . uniqid() . '.' . $fileExtension;
            
            if (in_array($fileExtension, $allowedExtensions)) {
                move_uploaded_file($fileTmpPath, $filePath);
                $profile_picture = $filePath;
            }
        }

        $stmt = $conn->prepare("INSERT INTO users (user_id, name, email, password, profile_picture) VALUES (?, ?, ?, ?, ?)");
        $user_id = uniqid();
        $stmt->bind_param('sssss', $user_id, $name, $email, $password, $profile_picture);
        $stmt->execute();
        $stmt->close();

        header('Location: admin_dataDiriUser.php?action=create&status=success&message=Pengguna%20berhasil%20ditambahkan');
        exit;
    }
} elseif ($action === 'update') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : $_POST['current_password'];
        
        // Handle file upload
        $profile_picture = $_POST['old_profile_picture'];
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
            $fileName = $_FILES['profile_picture']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            // Define allowed file extensions and directory
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $uploadDir = 'uploads/';
            $filePath = $uploadDir . uniqid() . '.' . $fileExtension;
            
            if (in_array($fileExtension, $allowedExtensions)) {
                move_uploaded_file($fileTmpPath, $filePath);
                $profile_picture = $filePath;
            }
        }

        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ?, profile_picture = ? WHERE user_id = ?");
        $stmt->bind_param('sssss', $name, $email, $password, $profile_picture, $user_id);
        $stmt->execute();
        $stmt->close();

        header('Location: admin_dataDiriUser.php?action=update&status=success&message=Pengguna%20berhasil%20diubah');
        exit;
    }
} elseif ($action === 'delete') {
    if ($user_id) {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param('s', $user_id);
        $stmt->execute();
        $stmt->close();

        // Redirect setelah penghapusan
        header('Location: admin_dataDiriUser.php?action=delete&status=success&message=Pengguna%20berhasil%20dihapus');
        exit;
    }
} elseif ($action === 'fetch_user') {
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param('s', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    echo json_encode($user);
    exit;
}

// Fetch users for display
$result = $conn->query("SELECT * FROM users");
$users = $result->fetch_all(MYSQLI_ASSOC);
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
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-semibold mb-4">Kelola Data Diri Pengguna</h1>
        <a href="#" onclick="openModal('createModal')" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Tambah Pengguna</a>

        <!-- Table -->
        <div class="bg-white p-6 rounded-lg shadow-md mt-4">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-center text-xs font-medium text-gray-500 uppercase tracking-wider">ID Pengguna</th>
                        <th class="px-6 py-3 text-left text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Foto Profil</th>
                        <th class="px-6 py-3 text-left text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($user['user_id']) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($user['name']) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($user['email']) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($user['profile_picture']??'') ?></td>
                        <td class="px-6 py-4 text-sm font-medium">
                            <a href="#" onclick="openModal('editModal', '<?= htmlspecialchars($user['user_id']) ?>')" class="text-blue-600 hover:text-blue-900">Ubah</a>
                            <a href="?action=delete&id=<?= urlencode($user['user_id']) ?>" class="text-red-600 hover:text-red-900 ml-2" onclick="return confirm('Are you sure you want to delete this user?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
                    </div>
    <!-- Toast Notifications -->
    <div id="toast-container" class="fixed top-4 right-4 space-y-2 z-50">
        <!-- Success Toast -->
        <div id="success-toast" class="toast hidden bg-green-500 text-white p-3 rounded-md shadow-md">
            <p id="success-message"></p>
        </div>
        <!-- Error Toast -->
        <div id="error-toast" class="toast hidden bg-red-500 text-white p-3 rounded-md shadow-md">
            <p id="error-message"></p>
        </div>
    </div>

    <!-- Create User Modal -->
    <div id="createModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-2xl">
            <h2 class="text-xl font-semibold mb-4">Tambah Pengguna</h2>
            <form method="POST" action="admin_dataDiriUser.php" enctype="multipart/form-data">
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
                    <label for="password" class="block text-sm font-medium text-gray-700">Kata Sandi</label>
                    <input type="password" id="password" name="password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-lg" required>
                </div>
                <div class="mb-4">
                    <label for="profile_picture" class="block text-sm font-medium text-gray-700">Foto Profil</label>
                    <input type="file" id="profile_picture" name="profile_picture" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm sm:text-lg">
                </div>
                <div class="flex items-center justify-end">
                    <button type="button" onclick="closeModal('createModal')" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Batal</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 ml-3">Tambah</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-2xl">
            <h2 class="text-xl font-semibold mb-4">Ubah Pengguna</h2>
            <form method="POST" action="admin_dataDiriUser.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update">
                <input type="hidden" id="edit_user_id" name="user_id">
                <input type="hidden" id="edit_old_profile_picture" name="old_profile_picture">
                <input type="hidden" id="edit_current_password" name="current_password">
                <div class="mb-4">
                    <label for="edit_name" class="block text-sm font-medium text-gray-700">Nama</label>
                    <input type="text" id="edit_name" name="name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-lg" required>
                </div>
                <div class="mb-4">
                    <label for="edit_email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="edit_email" name="email" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-lg" required>
                </div>
                <div class="mb-4">
                    <label for="edit_profile_picture" class="block text-sm font-medium text-gray-700">Foto Profil</label>
                    <input type="file" id="edit_profile_picture" name="profile_picture" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm sm:text-lg">
                </div>
                <div class="flex items-center justify-end">
                    <button type="button" onclick="closeModal('editModal')" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Batal</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 ml-3">Perbaharui</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showToast(type, message) {
            const toast = document.getElementById(`${type}-toast`);
            const messageElem = document.getElementById(`${type}-message`);
            
            messageElem.textContent = message;
            toast.classList.remove('hidden');
            
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 3000); // Toast will disappear after 3 seconds
        }

        function openModal(modalId, userId = null) {
            document.getElementById(modalId).style.display = 'flex';
            if (modalId === 'editModal' && userId) {
                fetch(`admin_dataDiriUser.php?action=fetch_user&id=${userId}`)
                    .then(response => response.json())
                    .then(user => {
                        document.getElementById('edit_user_id').value = user.user_id;
                        document.getElementById('edit_name').value = user.name;
                        document.getElementById('edit_email').value = user.email;
                        document.getElementById('edit_old_profile_picture').value = user.profile_picture;
                        document.getElementById('edit_current_password').value = user.password; // Keep current password
                    });
            }
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Show toast messages based on URL parameters
        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const action = urlParams.get('action');
            const status = urlParams.get('status');
            const message = urlParams.get('message');

            if (action && status && message) {
                showToast(status, message);
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
