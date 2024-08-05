<?php
session_start(); // Start the session to manage user login state
include '../connection.php';

$message = '';
$message_type = '';
$show_reset_form = false; // Initialize the variable
$show_toast = false; // Flag for showing toast

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email']) && !isset($_POST['new_password'])) {
        // Handle the email check
        $email = $_POST['email'];

        // Check if the email exists in the database
        $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            // Email found, proceed to password reset
            $show_reset_form = true;
        } else {
            $message = "No account found with that email address.";
            $message_type = "error";
        }

        // Close connections
        $stmt->close();
    } elseif (isset($_POST['new_password'])) {
        // Handle the password update
        $email = $_POST['email'];
        $new_password = $_POST['new_password'];

        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update the password in the database
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);

        if ($stmt->execute()) {
            if ($stmt->affected_rows == 1) {
                $message = "Kata sandi berhasil diubah.";
                $message_type = "success";
                $show_reset_form = false;
                $show_toast = true; // Set flag to show toast
            } else {
                $message = "Gagal mengubah kata sandi, silahkan coba lagi.";
                $message_type = "error";
            }
        } else {
            $message = "Error executing query: " . $stmt->error;
            $message_type = "error";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salad Buah - Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: rgba(0, 0, 0, 0.75);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            z-index: 1000;
            display: none;
        }
        .toast.show {
            display: block;
        }
    </style>
</head>
<body class="bg-green-100 flex items-center justify-center min-h-screen">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <h2 class="text-2xl font-bold text-center text-green-600 mb-6">
            <?php echo $show_reset_form ? "Reset Kata Sandi" : "Lupa Kata Sandi"; ?>
        </h2>

        <?php if (!$show_reset_form): ?>
            <form action="" method="POST">
                <div class="mb-4 relative">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" name="email" type="email" placeholder="Masukkan email anda ..." required>
                </div>
                <div>
                    <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded w-full focus:outline-none focus:shadow-outline" type="submit">
                        Kirim
                    </button>
                </div>
            </form>
        <?php else: ?>
            <form action="lupa_sandi.php" method="POST">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="new_password">Kata Sandi Baru</label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="new_password" name="new_password" type="password" placeholder="Masukkan kata sandi baru ..." required>
                </div>
                <div>
                    <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded w-full focus:outline-none focus:shadow-outline" type="submit">
                        Reset Kata Sandi
                    </button>
                </div>
            </form>
        <?php endif; ?>

        <?php if (!$show_reset_form): ?>
            <div class="mt-4 text-center">
                <p>Sudah punya akun? <a class="inline-block align-baseline font-bold text-sm text-green-500 hover:text-green-700" href="./login.php">Masuk disini</a></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Toast notification -->
    <div id="toast" class="toast <?php echo $show_toast ? 'show' : ''; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var toast = document.getElementById('toast');
            if (toast.classList.contains('show')) {
                setTimeout(function () {
                    toast.classList.remove('show');
                }, 5000); // Hide toast after 5 seconds
            }
        });
    </script>
</body>
</html>
