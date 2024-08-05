<?php
session_start();
include '../connection.php';

// Ensure the user is logged in
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


$email = $_SESSION['email'];
$sql = "SELECT * FROM orders WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
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
        /* Custom scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-thumb {
            background: #ddd;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #bbb;
        }
        /* Ensuring horizontal scrollbar on mobile */
        .scrollable-table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 1rem;
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
    <a href="./invoice.php"></a>
    <div class="container mx-auto p-6 mt-4">
        <div class="scrollable-table-container">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="py-3 px-4 text-left text-sm md:text-base">ID Pesanan</th>
                        <th class="py-3 px-4 text-left text-sm md:text-base">Nama</th>
                        <th class="py-3 px-4 text-left text-sm md:text-base">Telepon</th>
                        <th class="py-3 px-4 text-left text-sm md:text-base">Email</th>
                        <th class="py-3 px-4 text-left text-sm md:text-base">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="py-3 px-4 border-b text-sm md:text-base"><?= htmlspecialchars($row['id']) ?></td>
                                <td class="py-3 px-4 border-b text-sm md:text-base"><?= htmlspecialchars($row['name']) ?></td>
                                <td class="py-3 px-4 border-b text-sm md:text-base"><?= htmlspecialchars($row['phone']) ?></td>
                                <td class="py-3 px-4 border-b text-sm md:text-base"><?= htmlspecialchars($row['email']) ?></td>
                                <td class="py-3 px-4 border-b text-sm md:text-base"><?= htmlspecialchars($row['status'] ?? '') ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="py-3 px-4 text-center text-sm md:text-base">No orders found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.classList.toggle('hidden');
        }

        function toggleDropdown() {
            const dropdown = document.getElementById('dropdown');
            dropdown.classList.toggle('hidden');
        }
    </script>
</body>
</html>

<?php
// Close connection
$conn->close();
?>
