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
// Fetch products for selection
$sql = "SELECT * FROM products";
$result = $conn->query($sql);
$products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}


// Get user information
$email = $_SESSION['email'];
$sql = "SELECT name FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($name);
$stmt->fetch();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order - Salad Buah</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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

    <!-- Main Content -->
    <div class="container mx-auto my-4 px-4 flex-grow">
        <h1 class="text-2xl font-bold mb-4">Isi data pesanan mu!</h1>
        <form action="process_order.php" method="POST" class="bg-white p-6 rounded-lg shadow-md">
            <div class="mb-4">
                <label for="name" class="block text-gray-700">Nama Lengkap:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required class="w-full px-4 py-2 border rounded-lg">
            </div>
            <div class="mb-4">
                <label for="email" class="block text-gray-700">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required class="w-full px-4 py-2 border rounded-lg">
            </div>
            <div class="mb-4">
                <label for="phone" class="block text-gray-700">Nomor Telepon:</label>
                <input type="number" id="phone" name="phone" placeholder="08123456789" required class="w-full px-4 py-2 border rounded-lg">
            </div>
            <div class="mb-4">
                <label for="address" class="block text-gray-700">Alamat Lengkap:</label>
                <textarea id="address" name="address" placeholder="Contoh: JL. Mujair No 2 Kec.Bojong Kel.Bojongsari Kodepos 12345 (sekitar gunadarma)" required class="w-full px-4 py-2 border rounded-lg"></textarea>
            </div>
            <div class="mb-4">
                <label for="products" class="block text-gray-700">Produk:</label>
                <div id="productSelection">
                    <div class="product-group mb-4">
                        <select name="products[]" required class="w-full px-4 py-2 border rounded-lg">
                            <?php foreach ($products as $product): ?>
                                <option value="<?php echo htmlspecialchars($product['id']); ?>">
                                    <?php echo htmlspecialchars($product['name']); ?> - Rp<?php echo htmlspecialchars($product['price']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="quantities[]" min="1" required class="w-full px-4 py-2 border rounded-lg mt-2" placeholder="Banyak Produk">
                    </div>
                </div>
                <button type="button" id="addProduct" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-4">Tambah Produk</button>
            </div>
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Kirim</button>
        </form>
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
document.getElementById('addProduct').addEventListener('click', function() {
    const productGroup = document.createElement('div');
    productGroup.classList.add('product-group', 'mb-4');
    productGroup.innerHTML = `
        <select name="products[]" required class="w-full px-4 py-2 border rounded-lg">
            <?php foreach ($products as $product): ?>
                <option value="<?php echo htmlspecialchars($product['id']); ?>">
                    <?php echo htmlspecialchars($product['name']); ?> - Rp<?php echo htmlspecialchars($product['price']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="number" name="quantities[]" min="1" required class="w-full px-4 py-2 border rounded-lg mt-2" placeholder="Quantity">
    `;
    document.getElementById('productSelection').appendChild(productGroup);
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
    
</body>
</html>
