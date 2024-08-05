<?php
session_start();
include '../connection.php';

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Get user information
$email = $_SESSION['email'];
$sql = "SELECT name, profile_picture FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($name, $profile_picture);
$stmt->fetch();
$stmt->close();

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $newName = $_POST['name'];
    $profilePicture = $_FILES['profile_picture']['name'];
    $uploadOk = 1;

    if (!empty($profilePicture)) {
        // Validate and handle file upload
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($profilePicture);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if file is an image
        $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
        if ($check === false) {
            $uploadOk = 0;
            $error_message = "File is not an image.";
        }

        // Check file size
        if ($_FILES["profile_picture"]["size"] > 500000) {
            $uploadOk = 0;
            $error_message = "File is too large.";
        }

        // Allow certain file formats
        if (!in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
            $uploadOk = 0;
            $error_message = "Only JPG, JPEG, PNG & GIF files are allowed.";
        }

        if ($uploadOk == 0) {
            $error_message = "Your file was not uploaded.";
        } else {
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                // Update profile picture path in the database
                $sql = "UPDATE users SET name = ?, profile_picture = ? WHERE email = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $newName, $profilePicture, $email);
                $stmt->execute();
                $stmt->close();
            } else {
                $error_message = "There was an error uploading your file.";
            }
        }
    } else {
        // Update only name if no file is uploaded
        $sql = "UPDATE users SET name = ? WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $newName, $email);
        $stmt->execute();
        $stmt->close();
    }

    if (isset($error_message)) {
        $_SESSION['message'] = $error_message;
        $_SESSION['message_type'] = 'error';
    } else {
        $_SESSION['message'] = 'Profile updated successfully!';
        $_SESSION['message_type'] = 'success';
    }

    // Redirect to the correct page
    header("Location: profile.php"); // Ensure this is the correct page
    exit();
}

// Handle account deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_account'])) {
    $sql = "DELETE FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->close();

    // Destroy session and redirect to login page
    session_destroy();
    header("Location: login.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - Salad Buah</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">

    <!-- Main Content -->
    <div class="flex flex-col items-center justify-center flex-grow bg-green-100 py-8">
        <div class="bg-white p-6 rounded-lg shadow-md max-w-lg w-full relative">
            <a href="./beranda.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="flex flex-col items-center">
                <img class="w-24 h-24 rounded-full border-2 border-green-500" src="uploads/<?php echo htmlspecialchars($profile_picture); ?>" alt="User Profile">
                <h2 class="text-2xl font-semibold text-gray-800 mt-4"><?php echo htmlspecialchars($name); ?></h2>
                <p class="text-gray-600"><?php echo htmlspecialchars($email); ?></p>
            </div>
            <div class="mt-6 w-full">
                <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded w-full" onclick="document.getElementById('editProfileModal').style.display='flex'">
                    Edit Profile
                </button>
                <form action="logout.php" method="POST" class="mt-4">
                    <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded w-full">
                        Keluar
                    </button>
                </form>
                <form action="profile.php" method="POST" class="mt-4">
                    <input type="hidden" name="delete_account" value="1">
                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded w-full">
                        Hapus akun
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div id="editProfileModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">Edit Profile</h2>
            <?php if (isset($_SESSION['message'])) : ?>
                <div class="mb-4 text-center <?php echo $_SESSION['message_type'] == 'error' ? 'text-red-500' : 'text-green-500'; ?>">
                    <?php echo htmlspecialchars($_SESSION['message']); ?>
                </div>
                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <?php endif; ?>
            <form action="profile.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="update_profile" value="1">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="name">Name</label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="name" name="name" type="text" value="<?php echo htmlspecialchars($name); ?>" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="profile_picture">Foto Profil</label>
                    <input class="block w-full text-sm text-gray-500 border rounded py-2 px-3" id="profile_picture" name="profile_picture" type="file">
                </div>
                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Simpan Perubahan
                    </button>
                    <button type="button" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" onclick="document.getElementById('editProfileModal').style.display='none'">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const editProfileModal = document.getElementById('editProfileModal');

            // Close the modal when clicking outside of it
            window.addEventListener('click', (event) => {
                if (event.target === editProfileModal) {
                    editProfileModal.classList.add('hidden');
                }
            });
        });
    </script>

</body>
</html>
