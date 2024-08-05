<?php
session_start();
include '../connection.php';
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];
$sql = "SELECT name, profile_picture FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($name, $profile_picture);
$stmt->fetch();
$stmt->close();

// Ambil nama pengguna jika sudah login
$name = '';
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];

    $sql = "SELECT name FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($name);
    $stmt->fetch();
    $stmt->close();
}

// Tangani form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $submitted_name = $_POST['name'];
    $rating = $_POST['rating'];
    $comments = $_POST['comments'];

    // Simpan penilaian ke database
    $sql = "INSERT INTO reviews (name, rating, comments) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sis", $submitted_name, $rating, $comments);

    if ($stmt->execute()) {
        $message = "Penilaian berhasil dikirim!";
        $message_type = "success";
    } else {
        $message = "Terjadi kesalahan: " . $stmt->error;
        $message_type = "error";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penilaian - Salad Buah</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .star {
            font-size: 2rem;
            color: #000;
            cursor: pointer;
        }
        .star:hover, .star.active {
            color: #FFA500;
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
<body class="bg-green-100 flex flex-col min-h-screen">
     <!-- Navbar -->
     <nav class="bg-green-600 text-white shadow-md">
        <div class="container mx-auto flex items-center justify-between p-4">
            <a href="#" class="text-2xl font-bold hover:underline">Salad Buah</a>
            <button class="block md:hidden focus:outline-none" onclick="toggleMobileMenu()">
                <i class="fas fa-bars"></i>
            </button>
            <div class="hidden md:flex space-x-4 relative" id="navbar">
                <a href="./beranda.php" class="hover:underline">Beranda</a>
                <a href="#pengumuman" class="hover:underline">Pengumuman</a>
                <a href="./buahKami.php" class="hover:underline">Buah Kami</a>
                <a href="./checkout.php" class="hover:underline">Pesan</a>
                <a href="./penilaian.php" class="hover:underline">Penilaian</a>
                <div class="inline-block relative">
                    <button onclick="toggleDropdown()" class="hover:underline">
                        <img src="uploads/<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="w-6 h-6 rounded-full">
                    </button>
                    <ul id="dropdown" class="absolute hidden text-gray-700 pt-1">
                        <li><a class="bg-white hover:bg-gray-400 py-2 px-4 block whitespace-no-wrap" href="./profile.php">Profil</a></li>
                        <li><a class="bg-white hover:bg-gray-400 py-2 px-4 block whitespace-no-wrap" href="./status.php">Status</a></li>
                        <li><a class="bg-white hover:bg-gray-400 py-2 px-4 block whitespace-no-wrap" href="./logout.php">Keluar</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="md:hidden hidden flex-col space-y-2 p-4" id="mobileMenu">
            <a href="./beranda.php" class="block text-center bg-white text-green-600 py-2 rounded">Beranda</a>
            <a href="#pengumuman" class="block text-center bg-white text-green-600 py-2 rounded">Pengumuman</a>
            <a href="./buahKami.php" class="block text-center bg-white text-green-600 py-2 rounded">Buah Kami</a>
            <a href="./checkout.php" class="block text-center bg-white text-green-600 py-2 rounded">Pesan</a>
            <a href="./penilaian.php" class="block text-center bg-white text-green-600 py-2 rounded">Penilaian</a>
            <a href="./profile.php" class="block text-center bg-white text-green-600 py-2 rounded">Profil</a>
            <a href="./status.php" class="block text-center bg-white text-green-600 py-2 rounded">Status</a>
            <a href="./logout.php" class="block text-center bg-white text-green-600 py-2 rounded">Keluar</a>
        </div>
    </nav>
    <div class="flex flex-grow items-center justify-center">
        <div class="max-w-2xl w-full bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Berikan Penilaian Anda</h2>
            <form action="penilaian.php" method="POST">
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Nama Lengkap</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" placeholder="Masukkan nama Anda" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Penilaian</label>
                    <div class="flex items-center">
                        <span class="star" data-value="1">&#9733;</span>
                        <span class="star" data-value="2">&#9733;</span>
                        <span class="star" data-value="3">&#9733;</span>
                        <span class="star" data-value="4">&#9733;</span>
                        <span class="star" data-value="5">&#9733;</span>
                    </div>
                    <input type="hidden" id="rating" name="rating" value="0">
                </div>
                <div class="mb-4">
                    <label for="comments" class="block text-gray-700 text-sm font-bold mb-2">Komentar</label>
                    <textarea id="comments" name="comments" rows="4" placeholder="Tulis komentar Anda di sini..." class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                </div>
                <div>
                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded w-full focus:outline-none focus:shadow-outline">Kirim Penilaian</button>
                </div>
            </form>
        </div>
    </div>
    <div id="toast-container" class="fixed bottom-4 right-4 z-50"></div>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const stars = document.querySelectorAll('.star');
        const ratingInput = document.getElementById('rating');

        // Handle star clicks
        stars.forEach(star => {
            star.addEventListener('click', () => {
                const value = star.getAttribute('data-value');
                ratingInput.value = value;

                stars.forEach(s => {
                    s.classList.remove('active');
                });
                star.classList.add('active');

                let prev = star.previousElementSibling;
                while (prev) {
                    prev.classList.add('active');
                    prev = prev.previousElementSibling;
                }
            });
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
        <script>
        function toggleMobileMenu() {
            document.getElementById("mobileMenu").classList.toggle("hidden");
        }

        document.addEventListener('DOMContentLoaded', function () {
            const pengumumanButton = document.querySelector('a[href="#pengumuman"]');
            const pengumumanModal = document.getElementById('pengumumanModal');
            const closeModal = document.getElementById('closeModal');

            if (pengumumanButton) {
                pengumumanButton.addEventListener('click', function (event) {
                    event.preventDefault();
                    pengumumanModal.classList.remove('hidden');
                });
            }

            if (closeModal) {
                closeModal.addEventListener('click', function () {
                    pengumumanModal.classList.add('hidden');
                });
            }

            window.addEventListener('click', function (event) {
                if (event.target === pengumumanModal) {
                    pengumumanModal.classList.add('hidden');
                }
            });
        });

        function toggleDropdown() {
            document.getElementById("dropdown").classList.toggle("hidden");
        }
    </script>
</body
