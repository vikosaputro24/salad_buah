<?php
session_start();
include '../connection.php';

// Function to generate UUID (Version 4)
function generate_uuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

// Retrieve form data
$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$address = $_POST['address'];
$products = $_POST['products'];
$quantities = $_POST['quantities'];
$status = isset($_POST['status']) ? $_POST['status'] : NULL;

// Debugging: Print form data
// echo '<pre>';
// print_r($_POST);
// echo '</pre>';

// Initialize an array to keep track of stock updates
$stockUpdates = [];

// Generate UUID for the new order
$order_id = generate_uuid();

// Prepare SQL statements
$conn->begin_transaction();

try {
    // Insert order details into orders table
    $insert_order_sql = "INSERT INTO orders (id, name, email, phone, address, status) VALUES ('$order_id', '$name', '$email', '$phone', '$address', " . ($status !== NULL ? "'$status'" : "NULL") . ")";

    if ($conn->query($insert_order_sql) === TRUE) {

        // Loop through each selected product and quantity
        for ($i = 0; $i < count($products); $i++) {
            $product_id = $products[$i];
            $quantity = $quantities[$i];

            // // Debugging: Print each product and quantity
            // echo "Product ID: $product_id, Quantity: $quantity<br>";

            // Get the selected product's current stock
            $sql = "SELECT * FROM products WHERE id = '$product_id'";
            $result = $conn->query($sql);
            $product = $result->fetch_assoc();

            if ($product && $product['stock'] >= $quantity) {
                // Reduce stock
                $new_stock = $product['stock'] - $quantity;
                $update_stock_sql = "UPDATE products SET stock = $new_stock WHERE id = '$product_id'";

                // Check if the order item already exists
                $check_item_sql = "SELECT * FROM order_items WHERE order_id = '$order_id' AND product_id = '$product_id'";
                $check_result = $conn->query($check_item_sql);

                if ($check_result->num_rows > 0) {
                    // Update quantity if it already exists
                    $update_order_item_sql = "UPDATE order_items SET quantity = quantity + $quantity WHERE order_id = '$order_id' AND product_id = '$product_id'";
                    if ($conn->query($update_order_item_sql) === FALSE) {
                        throw new Exception("Error updating order item: " . $conn->error);
                    }
                } else {
                    // Insert new order item
                    $insert_order_item_sql = "INSERT INTO order_items (order_id, product_id, quantity) VALUES ('$order_id', '$product_id', $quantity)";
                    if ($conn->query($insert_order_item_sql) === FALSE) {
                        throw new Exception("Error inserting order item: " . $conn->error);
                    }
                }

                // Update stock
                if ($conn->query($update_stock_sql) === FALSE) {
                    throw new Exception("Error updating stock: " . $conn->error);
                }
            } else {
                throw new Exception("Insufficient stock for product ID: $product_id");
            }
        }

        // Commit transaction if everything is successful
        $conn->commit();
        echo "Order placed successfully!";
    } else {
        throw new Exception("Error inserting order: " . $conn->error);
    }
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo "Failed to place order: " . $e->getMessage();
}

$conn->close();
?>
