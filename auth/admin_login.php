<?php
session_start();

if (isset($_POST['login'])) {
    $adminname = $_POST['adminname'];
    $password = $_POST['password'];

    // Validate login
    if ($adminname === 'admin' && $password === 'admin123') {
        $_SESSION['loggedin'] = true;
        header('Location: dashboard.php'); // Redirect to the admin dashboard
        exit;
    } else {
        $error = 'Username or password is incorrect!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@latest/dist/tailwind.min.css" rel="stylesheet">
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
<body class="flex items-center justify-center min-h-screen bg-green-700">
    <div class="w-full max-w-md p-8 bg-white rounded-lg shadow-lg border border-gray-200">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Login sebagai admin</h2>
        <?php if (isset($error)): ?>
            <div class="mb-4 p-4 text-red-800 bg-red-100 border border-red-300 rounded-md">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <form action="admin_login.php" method="post">
            <div class="mb-4">
                <label for="adminname" class="block text-gray-600 text-sm font-medium mb-2">Nama admin</label>
                <input type="text" id="adminname" name="adminname" placeholder="Masukkan nama admin" class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-600 text-sm font-medium mb-2">Kata Sandi</label>
                <input type="password" id="password" name="password" placeholder="Masukkan kata sandi" class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <button type="submit" name="login" class="w-full px-4 py-2 bg-blue-500 text-white rounded-md shadow-sm hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">Masuk</button>
        </form>
    </div>

    <div id="toast-container" class="fixed bottom-4 right-4 z-50"></div>
    <script>
        // Function to show toast
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

        // Show toast message if set
        <?php if (isset($error)): ?>
            showToast("<?php echo addslashes($error); ?>", "error");
        <?php endif; ?>
    </script>
</body>
</html>
