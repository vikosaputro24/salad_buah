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


// Fetch products for display
$sql = "SELECT * FROM products";
$result = $conn->query($sql);
$products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Fetch fruits for display
$sql_fruits = "SELECT * FROM fruits";
$result_fruits = $conn->query($sql_fruits);
$fruits = [];
if ($result_fruits->num_rows > 0) {
    while ($row_fruit = $result_fruits->fetch_assoc()) {
        $fruits[] = $row_fruit;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buah Kami - Salad Buah</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .selected {
            background-color: #38a169;
            /* Green for selected button */
            color: white;
        }

        .not-selected {
            background-color: #6b7280;
            /* Gray for unselected button */
            color: white;
        }
    </style>
</head>

<body class="bg-gray-100 flex flex-col min-h-screen">

    <!-- Navbar -->
    <nav class="bg-green-600 text-white shadow-md">
        <div class="container mx-auto flex items-center justify-between p-4">
            <a href="#" class="text-2xl font-bold hover:underline">Salad Buah</a>
            <button class="block md:hidden focus:outline-none" onclick="toggleMobileMenu()">
                <i class="fas fa-bars"></i>
            </button>
            <div class="hidden md:flex space-x-4 relative" id="navbar">
                <a href="./index.php" class="hover:underline">Beranda</a>
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

    <!-- Main Content -->
    <div class="container mx-auto my-4 px-4 flex-grow">
        <!-- Button Group -->
        <div class="flex justify-center space-x-4 mb-6">
            <button id="buahKamiBtn" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded non-selected">
                Buah Kami
            </button>
            <button id="produkBtn" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded selected">
                Produk
            </button>
            <a href="checkout.php" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded non-selected">
                Pesan
            </a>
        </div>

        <!-- Cards Container -->
        <div id="buahKamiCards" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 hidden">
            <?php foreach ($fruits as $fruit) : ?>
                <div class="bg-white p-4 rounded-lg shadow-md flex flex-col h-full">
                    <img class="w-full h-72 object-cover rounded-t-lg" src="<?php echo htmlspecialchars($fruit['image']); ?>" alt="<?php echo htmlspecialchars($fruit['name']); ?>">
                    <div class="p-4 flex-grow">
                        <h3 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($fruit['name']); ?></h3>
                        <p class="text-gray-600 mt-2"><?php echo htmlspecialchars($fruit['description']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="produkCards" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($products as $product) : ?>
                <div class="bg-white p-4 rounded-lg shadow-md flex flex-col h-full">
                    <img class="w-full h-72 object-cover rounded-t-lg" src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <div class="p-4 flex-grow">
                        <h3 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="text-gray-600 mt-2"><?php echo htmlspecialchars($product['description']); ?></p>
                        <p class="text-gray-800 mt-2">Harga: Rp<?php echo htmlspecialchars($product['price']); ?></p>
                        <p class="text-gray-800 mt-2">Stok: <?php echo htmlspecialchars($product['stock']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <!-- Announcement Modal -->
    <div id="pengumumanModal" class="fixed inset-0 flex items-center justify-center z-50 hidden bg-black bg-opacity-60 transition-opacity duration-300 ease-in-out">
        <div class="bg-white rounded-lg shadow-lg w-11/12 md:w-3/4 lg:w-1/2 transform transition-transform duration-300 ease-in-out scale-95">
            <div class="border-b px-6 py-3 flex justify-between items-center bg-green-600">
                <h5 class="text-lg font-semibold text-white">Pengumuman</h5>
                <button id="closeModal" class="text-white hover:text-gray-300 focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                <p class="font-serif text-lg leading-relaxed"><?php echo file_get_contents("./pengumuman.txt"); ?></p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-green-600 text-white text-center py-2">
        <p>&copy; 2024 Salad Buah. All rights reserved.</p>
    </footer>

    <script>
        document.getElementById('buahKamiBtn').addEventListener('click', function() {
            document.getElementById('produkCards').classList.add('hidden');
            document.getElementById('buahKamiCards').classList.remove('hidden');
            this.classList.add('selected');
            this.classList.remove('not-selected');
            document.getElementById('produkBtn').classList.add('not-selected');
            document.getElementById('produkBtn').classList.remove('selected');
        });

        document.getElementById('produkBtn').addEventListener('click', function() {
            document.getElementById('produkCards').classList.remove('hidden');
            document.getElementById('buahKamiCards').classList.add('hidden');
            this.classList.add('selected');
            this.classList.remove('not-selected');
            document.getElementById('buahKamiBtn').classList.add('not-selected');
            document.getElementById('buahKamiBtn').classList.remove('selected');
        });
    </script>
    <script>
        function toggleMobileMenu() {
            document.getElementById("mobileMenu").classList.toggle("hidden");
        }

        document.addEventListener('DOMContentLoaded', function() {
            const pengumumanButton = document.querySelector('a[href="#pengumuman"]');
            const pengumumanModal = document.getElementById('pengumumanModal');
            const closeModal = document.getElementById('closeModal');

            if (pengumumanButton) {
                pengumumanButton.addEventListener('click', function(event) {
                    event.preventDefault();
                    pengumumanModal.classList.remove('hidden');
                });
            }

            if (closeModal) {
                closeModal.addEventListener('click', function() {
                    pengumumanModal.classList.add('hidden');
                });
            }

            window.addEventListener('click', function(event) {
                if (event.target === pengumumanModal) {
                    pengumumanModal.classList.add('hidden');
                }
            });
        });

        function toggleDropdown() {
            document.getElementById("dropdown").classList.toggle("hidden");
        }
    </script>
</body>

</html>