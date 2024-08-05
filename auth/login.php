<?php
session_start(); // Start the session to manage user login state
include '../connection.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and bind
    $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);

    // Execute the statement
    $stmt->execute();
    $stmt->store_result();

    // Check if email exists
    if ($stmt->num_rows == 1) {
        $stmt->bind_result($hashed_password);
        $stmt->fetch();

        // Verify the password
        if (password_verify($password, $hashed_password)) {
            $_SESSION['email'] = $email; // Store user email in session
            $message = "Login successful";
            $message_type = "success";
            header("Location: beranda.php"); // Redirect to a welcome page or dashboard
            exit();
        } else {
            $message = "Email atau kata sandi salah.";
            $message_type = "error";
        }
    } else {
        $message = "Email atau kata sandi salah.";
        $message_type = "error";
    }

    // Close connections
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salad Buah - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .slide-out {
            animation: slideOut 0.5s forwards;
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
            }
            to {
                transform: translateX(-100%);
                opacity: 0;
            }
        }

        .slide-in {
            animation: slideIn 0.5s forwards;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .toast {
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .toast.show {
            opacity: 1;
        }
    </style>
</head>
<body class="bg-green-100 flex items-center justify-center min-h-screen">
    <div class="max-w-4xl w-full bg-white rounded-lg shadow-md flex flex-col sm:flex-row overflow-hidden">
        <!-- Image Section -->
        <div class="hidden sm:flex sm:w-1/2 items-center justify-center bg-green-700">
            <img src="../assets/logohome.png" alt="Salad Buah" class="object-contain max-w-[80%] max-h-[80%]">
        </div>
        <!-- Form Section -->
        <div class="w-full sm:w-1/2 px-8 py-12 flex flex-col justify-center">
            <h2 class="text-2xl font-bold text-center text-green-600 mb-6">Ayo Masuk</h2>
            <form action="login.php" method="POST">
                <div class="mb-4 relative">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
                    <i class="fas fa-envelope absolute left-3 top-3/4 transform -translate-y-1/2 text-gray-500"></i>
                    <input class="shadow appearance-none border rounded w-full py-2 pl-10 pr-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" name="email" type="email" placeholder="Masukkan email anda ..." required>
                </div>
                <div class="mb-6 relative">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-gray-700 text-sm font-bold" for="password">Kata Sandi</label>
                        <a class="inline-block align-baseline font-bold text-sm text-green-500 hover:text-green-800" href="./lupa_sandi.php">
                            Lupa Kata Sandi ?
                        </a>
                    </div>
                    <i class="fas fa-lock absolute left-3 top-12 transform -translate-y-1/2 text-gray-500"></i>
                    <input class="shadow appearance-none border rounded w-full py-2 pl-10 pr-12 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" id="password" name="password" type="password" placeholder="********" required>
                    <i id="togglePassword" class="fas fa-eye absolute right-3 top-12 transform -translate-y-1/2 text-gray-500 cursor-pointer"></i>
                </div>
                <div>
                    <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded w-full focus:outline-none focus:shadow-outline" type="submit">
                        Login
                    </button>
                </div>

                <div class="mt-4 text-center">
                    <p>Belum punya akun ? <a class="inline-block align-baseline font-bold text-sm text-green-500 hover:text-green-700" href="./register.php"> Daftar disini</a></p>
                </div>
            </form>
        </div>
    </div>

    <div id="toast-container" class="fixed bottom-4 right-4 z-50"></div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const link = document.querySelector('a[href="./register.php"]');
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');

            link.addEventListener('click', (event) => {
                event.preventDefault(); // Prevent the default anchor behavior

                // Add slide-out class to the container
                const container = document.querySelector('.max-w-4xl');
                container.classList.add('slide-out');

                // Wait for the animation to complete before navigating
                setTimeout(() => {
                    window.location.href = './register.php';
                }, 500); // Match the duration of the animation
            });

            togglePassword.addEventListener('click', () => {
                // Toggle the type attribute
                const type = passwordInput.type === 'password' ? 'text' : 'password';
                passwordInput.type = type;
                // Toggle the eye icon
                togglePassword.classList.toggle('fa-eye');
                togglePassword.classList.toggle('fa-eye-slash');
            });

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
            <?php if (isset($message)): ?>
                showToast("<?php echo addslashes($message); ?>", "<?php echo $message_type; ?>");
            <?php endif; ?>
        });
    </script>
</body>
</html>
