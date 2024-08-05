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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Salad Buah</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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

    <!-- Welcome Section -->
    <section id="welcome" class="flex items-center justify-center min-h-screen bg-gradient-to-r from-green-400 to-blue-500">
        <div class="text-center p-8 bg-white bg-opacity-80 rounded-3xl shadow-lg max-w-2xl mx-auto backdrop-filter backdrop-blur-lg">
            <h1 class="text-5xl font-extrabold text-gray-900 mb-4">Selamat Datang di Salad Buah Mas Viko!</h1>
            <p class="text-lg text-gray-700 mb-6">Terima kasih telah bergabung dengan kami. Kami menyediakan berbagai macam salad buah segar yang dapat Anda nikmati. Silakan eksplorasi berbagai fitur dan layanan yang kami tawarkan.</p>
            <button class="mt-4 px-6 py-3 bg-green-500 text-white font-semibold rounded-lg shadow-md hover:bg-green-700 transition duration-300">Jelajahi Sekarang</button>
        </div>
    </section>


    <!-- About Me Section -->
    <section id="about" class="flex items-center justify-center min-h-screen bg-green-100">
        <div class="flex flex-wrap items-start justify-center max-w-6xl mx-auto p-8 gap-12">
            <!-- Left Card with Image -->
            <div class="flex-none w-full md:w-1/3 lg:w-1/4">
                <div class="w-full h-80 bg-gray-300 rounded-3xl overflow-hidden shadow-lg">
                    <img src="../assets/wallpaper.png" alt="About Us" class="object-cover w-full h-full">
                </div>
            </div>
            <!-- Right Card with Text -->
            <div class="flex-1 bg-white bg-opacity-80 rounded-3xl shadow-lg p-12 backdrop-filter backdrop-blur-lg max-w-4xl">
                <h2 class="text-4xl font-extrabold text-gray-900 mb-6">Tentang Kami</h2>
                <p class="text-lg text-gray-700">Kami berkomitmen untuk memberikan produk salad buah terbaik dengan kualitas tertinggi. Dapatkan manfaat dari pengalaman dan pengetahuan tentang manfaat buah pada website ini. Diwebsite ini pemesan akan memilih salad yang ideal untuk setiap kebutuhan Anda. Dengan layanan yang ramah dan responsif, kami siap menjawab setiap pertanyaan Anda dan memberikan saran terbaik untuk memastikan Anda mendapatkan salad buah yang sempurna.</p>
            </div>
        </div>
    </section>


    <!-- Our Services Section -->
    <section id="services" class="flex flex-col items-center justify-center min-h-screen bg-gradient-to-r from-green-400 to-blue-500 p-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-extrabold text-white mb-4">Our Services</h2>
            <p class="text-lg text-white">Explore the range of services we offer to enhance your salad experience.</p>
        </div>
        <div class="flex flex-wrap justify-center gap-8 max-w-6xl mx-auto">
            <!-- Service 1 -->
            <div class="flex flex-col items-center w-full lg:w-80">
                <img src="../assets/s1.png" alt="Custom Salad" class="w-full h-64 object-cover mb-4">
                <h3 class="text-xl font-semibold text-white mb-2">Produk yang higienis</h3>
                <p class="text-white text-center">Kebersihan dalam pembuatan produk menjadi keunggulan kami agar salad buah tetap segar.</p>
            </div>
            <!-- Service 2 -->
            <div class="flex flex-col items-center w-full lg:w-80">
                <img src="../assets/s2.png" alt="Delivery Service" class="w-full h-64 object-cover mb-4">
                <h3 class="text-xl font-semibold text-white mb-2">Kecepatan pengiriman</h3>
                <p class="text-white text-center">Kami akan langsung mengirim produk satu hari setelah pemesanan.</p>
            </div>
            <!-- Service 3 -->
            <div class="flex flex-col items-center w-full lg:w-80">
                <img src="../assets/s3.png" alt="Subscription Program" class="w-full h-64 object-cover mb-4">
                <h3 class="text-xl font-semibold text-white mb-2">Produk sampai ke pembeli</h3>
                <p class="text-white text-center">Senyum pembeli merupakan tujuan utama kami dalam pembuatan serta pengiriman produk salad buah ini.</p>
            </div>
        </div>
    </section>



    <!-- Contact Us Section -->
    <section id="contact" class="flex flex-col md:flex-row items-center justify-center min-h-screen bg-green-100 p-8">
        <!-- Google Map -->
        <div class="w-full md:w-1/2 h-76 mb-8 md:mb-0">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.4629857443056!2d106.60119227482883!3d-6.202491793785254!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69feeaebcf9eeb%3A0x366d50225836ca01!2sJl.%20Rama%20Raya%20No.2%2C%20RT.005%2FRW.007%2C%20Cibodas%20Baru%2C%20Kec.%20Cibodas%2C%20Kota%20Tangerang%2C%20Banten%2015138!5e0!3m2!1sid!2sid!4v1722838110936!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
        <!-- Contact Details -->
        <div class="w-full md:w-1/2 p-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">Kontak Kami</h2>
            <p class="text-gray-600 mb-4">Jika Anda memiliki pertanyaan atau ingin mendapatkan informasi lebih lanjut, jangan ragu untuk menghubungi kami melalui detail berikut:</p>
            <ul class="text-gray-600 mb-4">
                <li class="flex items-center mb-2">
                    <i class="fas fa-phone-alt text-green-600 mr-2"></i>+6285710847277
                </li>
                <li class="flex items-center mb-2">
                    <a href="https://wa.me/6285710847277" class="flex items-center text-gray-600 hover:text-gray-800">
                        <i class="fa-brands fa-whatsapp text-green-600 mr-2"></i>WhatsApp
                    </a>
                </li>
                <li class="flex items-center mb-2">
                    <a href="mailto:vikosaputro24@gmail.com" class="flex items-center text-gray-600 hover:text-gray-800">
                        <i class="fas fa-envelope text-green-600 mr-2"></i>vikosaputro24@gmail.com
                    </a>
                </li>
                <li class="flex items-center mb-2">
                    <i class="fas fa-map-marker-alt text-green-600 mr-2"></i>JL. Rama Raya No 2 Perumnas 2 Kota Tangerang
                </li>
            </ul>

            <div class="flex space-x-4 justify-center">
                <!-- Social Media Icons -->
                <a href="https://www.facebook.com/profile.php?id=100084065671347&locale=id_ID" class="text-gray-600 hover:text-gray-800" target="_blank">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://www.tiktok.com/@ordinaryyclown" class="text-gray-600 hover:text-gray-800" target="_blank">
                    <i class="fa-brands fa-tiktok"></i>
                </a>
                <a href="https://www.instagram.com/viko_saputro/" class="text-gray-600 hover:text-gray-800" target="_blank">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="https://www.youtube.com/channel/UCWZTJWj4FeGsiZtcyYsoxBw" class="text-gray-600 hover:text-gray-800" target="_blank">
                    <i class="fa-brands fa-youtube"></i>
                </a>
            </div>
        </div>
    </section>


    <!-- Announcement Modal -->
    <div id="pengumumanModal" class="fixed inset-0 flex items-center justify-center z-50 hidden bg-black bg-opacity-60">
        <div class="bg-white rounded-lg shadow-lg w-11/12 md:w-3/4 lg:w-1/2 transform scale-100">
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
    <footer class="bg-green-600 text-white text-center py-4 mt-auto">
        <p>&copy; 2024 Salad Buah. All rights reserved.</p>
    </footer>

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