<?php
session_start();
include '../connection.php';

$notification = '';

// Handle POST request for adding or updating products or fruits
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'update') {
            // Update product
            $id = intval($_POST['id']);
            $name = $_POST['name'];
            $price = $_POST['price'];
            $stock = $_POST['stock'];
            $description = $_POST['description'];
            $image = $_POST['current_image'];

            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                $targetDir = "uploads/";
                $targetFile = $targetDir . basename($_FILES["image"]["name"]);
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                    $image = $targetFile;
                }
            }

            $stmt = $conn->prepare("UPDATE products SET name=?, price=?, stock=?, description=?, image=? WHERE id=?");
            $stmt->bind_param("sdsssi", $name, $price, $stock, $description, $image, $id);
            $stmt->execute();
            $stmt->close();

            $notification = 'Product updated successfully!';
        } elseif ($_POST['action'] == 'add') {
            // Add new product
            $name = $_POST['name'];
            $price = $_POST['price'];
            $stock = $_POST['stock'];
            $description = $_POST['description'];

            $image = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                $targetDir = "uploads/";
                $targetFile = $targetDir . basename($_FILES["image"]["name"]);
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                    $image = $targetFile;
                }
            }

            $stmt = $conn->prepare("INSERT INTO products (name, price, stock, description, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sdsss", $name, $price, $stock, $description, $image);
            $stmt->execute();
            $stmt->close();

            $notification = 'Product added successfully!';
        } elseif ($_POST['action'] == 'update_fruit') {
            // Update fruit
            $id = intval($_POST['id']);
            $name = $_POST['name'];
            $description = $_POST['description'];
            $image = $_POST['current_image'];

            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                $targetDir = "uploads/";
                $targetFile = $targetDir . basename($_FILES["image"]["name"]);
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                    $image = $targetFile;
                }
            }

            $stmt = $conn->prepare("UPDATE fruits SET name=?, description=?, image=? WHERE id=?");
            $stmt->bind_param("sssi", $name, $description, $image, $id);
            $stmt->execute();
            $stmt->close();

            $notification = 'Fruit updated successfully!';
        } elseif ($_POST['action'] == 'add_fruit') {
            // Add new fruit
            $name = $_POST['name'];
            $description = $_POST['description'];

            $image = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                $targetDir = "uploads/";
                $targetFile = $targetDir . basename($_FILES["image"]["name"]);
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                    $image = $targetFile;
                }
            }

            $stmt = $conn->prepare("INSERT INTO fruits (name, description, image) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $description, $image);
            $stmt->execute();
            $stmt->close();

            $notification = 'Fruit added successfully!';
        }
    }

    header('Location: ' . $_SERVER['PHP_SELF'] . '?notification=' . urlencode($notification)); // Redirect with notification
    exit;
}

// Handle DELETE request for deleting products or fruits
if (isset($_GET['action'])) {
    if ($_GET['action'] == 'delete') {
        $id = intval($_GET['id']);
        $type = $_GET['type'];
        $table = $type === 'fruit' ? 'fruits' : 'products';
        $sql = "DELETE FROM $table WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['success' => true, 'message' => ucfirst($type) . ' deleted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete ' . $type]);
        }
        exit;
    }
}

// Fetch products and fruits for display
$productsResult = $conn->query("SELECT * FROM products");
$products = [];
if ($productsResult->num_rows > 0) {
    while ($row = $productsResult->fetch_assoc()) {
        $products[] = $row;
    }
}

$fruitsResult = $conn->query("SELECT * FROM fruits");
$fruits = [];
if ($fruitsResult->num_rows > 0) {
    while ($row = $fruitsResult->fetch_assoc()) {
        $fruits[] = $row;
    }
}

$conn->close();
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
                <a href="./admin_pengumuman.php" class="flex items-center space-x-2 text-gray-300 hover:text-white hover:bg-green-700 p-2 rounded-md">
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

        <!-- Main Content -->
        <div class="container mx-auto my-4 px-4 flex-grow">
            <!-- Manage Selection -->
            <div class="mb-6">
                <button onclick="showSection('products')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
                    Kelola Produk
                </button>
                <button onclick="showSection('fruits')" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Kelola Buah
                </button>
            </div>

            <!-- Product Section -->
            <div id="productsSection" class="hidden">
                <div class="mb-6">
                    <button onclick="openAddProductModal()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Tambah Produk
                    </button>
                </div>

                <h2 class="text-2xl font-semibold mb-4">Daftar Produk</h2>
                <div id="productList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($products as $product) : ?>
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden transform transition-transform hover:scale-105">
                            <div class="relative h-80">
                                <img class="w-full h-full object-cover" src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>
                            <div class="p-6">
                                <h3 class="text-xl font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($product['description']); ?></p>
                                <div class="flex items-center justify-between mb-4">
                                    <p class="text-gray-800 font-bold">Harga: Rp<?php echo htmlspecialchars($product['price']); ?></p>
                                    <p class="text-gray-800 font-bold">Stok: <?php echo htmlspecialchars($product['stock']); ?></p>
                                </div>
                                <div class="flex space-x-2">
                                    <button onclick="openEditProductModal(<?php echo htmlspecialchars(json_encode($product)); ?>)" class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-4 rounded transition-colors">
                                        Ubah
                                    </button>
                                    <button onclick="deleteProduct(<?php echo $product['id']; ?>)" class="flex-1 bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded transition-colors">
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Fruit Section -->
            <div id="fruitsSection" class="hidden">
                <div class="mb-6">
                    <button onclick="openAddFruitModal()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Tambah Buah
                    </button>
                </div>

                <h2 class="text-2xl font-semibold mb-4">Daftar Buah</h2>
                <div id="fruitList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($fruits as $fruit) : ?>
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden transform transition-transform hover:scale-105">
                            <div class="relative h-80">
                                <img class="w-full h-full object-cover" src="<?php echo htmlspecialchars($fruit['image']); ?>" alt="<?php echo htmlspecialchars($fruit['name']); ?>">
                            </div>
                            <div class="p-6">
                                <h3 class="text-xl font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($fruit['name']); ?></h3>
                                <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($fruit['description']); ?></p>
                                <div class="flex space-x-2">
                                    <button onclick="openEditFruitModal(<?php echo htmlspecialchars(json_encode($fruit)); ?>)" class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-4 rounded transition-colors">
                                        Ubah
                                    </button>
                                    <button onclick="deleteFruit(<?php echo $fruit['id']; ?>)" class="flex-1 bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded transition-colors">
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- Add Product Modal -->
    <div id="addProductModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white border border-gray-300 p-6 rounded-lg shadow-lg w-full max-w-lg">
            <h2 class="text-2xl font-semibold mb-4">Tambah Produk</h2>
            <form id="addProductForm" action="manage.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="id" id="productId">
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="productName">Nama:</label>
                    <input type="text" name="name" id="productName" class="form-input w-full border border-gray-300 rounded-md p-2" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="productDescription">Deskripsi:</label>
                    <textarea name="description" id="productDescription" class="form-textarea w-full border border-gray-300 rounded-md p-2" required></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="productPrice">Harga:</label>
                    <input type="number" name="price" id="productPrice" step="0.01" class="form-input w-full border border-gray-300 rounded-md p-2" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="productStock">Stok:</label>
                    <input type="number" name="stock" id="productStock" class="form-input w-full border border-gray-300 rounded-md p-2" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="productImage">Foto:</label>
                    <input type="file" name="image" id="productImage" class="form-input w-full border border-gray-300 rounded-md p-2">
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeModal('addProductModal')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-md">
                        Batal
                    </button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md">
                        Tambah
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editProductModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white border border-gray-300 p-6 rounded-lg shadow-lg w-full max-w-lg">
            <h2 class="text-2xl font-semibold mb-4">Ubah Produk</h2>
            <form id="editProductForm" action="manage.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="editProductId">
                <input type="hidden" name="current_image" id="currentProductImage">
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="editProductName">Nama:</label>
                    <input type="text" name="name" id="editProductName" class="form-input w-full border border-gray-300 rounded-md p-2" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="editProductDescription">Deskripsi:</label>
                    <textarea name="description" id="editProductDescription" class="form-textarea w-full border border-gray-300 rounded-md p-2" required></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="editProductPrice">Harga:</label>
                    <input type="number" name="price" id="editProductPrice" step="0.01" class="form-input w-full border border-gray-300 rounded-md p-2" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="editProductStock">Stok:</label>
                    <input type="number" name="stock" id="editProductStock" class="form-input w-full border border-gray-300 rounded-md p-2" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="editProductImage">Foto:</label>
                    <input type="file" name="image" id="editProductImage" class="form-input w-full border border-gray-300 rounded-md p-2">
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeModal('editProductModal')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-md">
                        Batal
                    </button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>


    <!-- Add Fruit Modal -->
    <div id="addFruitModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white border border-gray-300 p-6 rounded-lg shadow-lg w-full max-w-lg">
            <h2 class="text-2xl font-semibold mb-4">Tambah Buah</h2>
            <form id="addFruitForm" action="manage.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_fruit">
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="fruitName">Nama:</label>
                    <input type="text" name="name" id="fruitName" class="form-input w-full border border-gray-300 rounded-md p-2" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="fruitDescription">Deskripsi:</label>
                    <textarea name="description" id="fruitDescription" class="form-textarea w-full border border-gray-300 rounded-md p-2" required></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="fruitImage">Foto:</label>
                    <input type="file" name="image" id="fruitImage" class="form-input w-full border border-gray-300 rounded-md p-2">
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeModal('addFruitModal')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-md">
                        Batal
                    </button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md">
                        Tambah
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Fruit Modal -->
    <div id="editFruitModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white border border-gray-300 p-6 rounded-lg shadow-lg w-full max-w-lg">
            <h2 class="text-2xl font-semibold mb-4">Ubah Buah</h2>
            <form id="editFruitForm" action="manage.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_fruit">
                <input type="hidden" name="id" id="editFruitId">
                <input type="hidden" name="current_image" id="currentFruitImage">
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="editFruitName">Nama:</label>
                    <input type="text" name="name" id="editFruitName" class="form-input w-full border border-gray-300 rounded-md p-2" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="editFruitDescription">Deskripsi:</label>
                    <textarea name="description" id="editFruitDescription" class="form-textarea w-full border border-gray-300 rounded-md p-2" required></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="editFruitImage">Foto:</label>
                    <input type="file" name="image" id="editFruitImage" class="form-input w-full border border-gray-300 rounded-md p-2">
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeModal('editFruitModal')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-md">
                        Batal
                    </button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        function showSection(section) {
            document.getElementById('productsSection').classList.toggle('hidden', section !== 'products');
            document.getElementById('fruitsSection').classList.toggle('hidden', section !== 'fruits');
        }

        function openAddProductModal() {
            document.getElementById('addProductModal').classList.remove('hidden');
        }

        function openEditProductModal(product) {
            document.getElementById('editProductId').value = product.id;
            document.getElementById('editProductName').value = product.name;
            document.getElementById('editProductDescription').value = product.description;
            document.getElementById('editProductPrice').value = product.price;
            document.getElementById('editProductStock').value = product.stock;
            document.getElementById('currentProductImage').value = product.image;
            document.getElementById('editProductModal').classList.remove('hidden');
        }

        function openAddFruitModal() {
            document.getElementById('addFruitModal').classList.remove('hidden');
        }

        function openEditFruitModal(fruit) {
            document.getElementById('editFruitId').value = fruit.id;
            document.getElementById('editFruitName').value = fruit.name;
            document.getElementById('editFruitDescription').value = fruit.description;
            document.getElementById('currentFruitImage').value = fruit.image;
            document.getElementById('editFruitModal').classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        function deleteProduct(id) {
            if (confirm('Are you sure you want to delete this product?')) {
                fetch('manage.php?action=delete&id=' + id + '&type=product')
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) location.reload();
                    });
            }
        }

        function deleteFruit(id) {
            if (confirm('Are you sure you want to delete this fruit?')) {
                fetch('manage.php?action=delete&id=' + id + '&type=fruit')
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) location.reload();
                    });
            }
        }

        // Set default section to 'products' on page load
        document.addEventListener('DOMContentLoaded', function() {
            showSection('products');
        });
    </script>
    <script>
        document.getElementById('settingsButton').addEventListener('click', function() {
            const dropdown = document.getElementById('settingsDropdown');
            dropdown.classList.toggle('hidden');
        });
    </script>

</body>

</html>