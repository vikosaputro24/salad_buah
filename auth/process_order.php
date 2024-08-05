<?php
session_start();
include '../connection.php';

require_once '../midtrans-php-master/Midtrans.php';

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

// Initialize an array to keep track of stock updates
$stockUpdates = [];

// Generate UUID for the new order
$order_id = generate_uuid();

// Calculate total amount (assuming you have product prices in $_POST or in the database)
$total_amount = 0;
$orderItems = []; // To store item details for Midtrans

$conn->begin_transaction();

try {
    // Insert order details into orders table
    $insert_order_sql = "INSERT INTO orders (id, name, email, phone, address, status) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_order_sql);
    $stmt->bind_param("ssssss", $order_id, $name, $email, $phone, $address, $status);
    if ($stmt->execute()) {

        // Loop through each selected product and quantity
        for ($i = 0; $i < count($products); $i++) {
            $product_id = $products[$i];
            $quantity = $quantities[$i];

            // Get the selected product's current stock and price
            $sql = "SELECT * FROM products WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();

            if ($product && $product['stock'] >= $quantity) {
                // Calculate total amount
                $total_amount += $product['price'] * $quantity;

                // Reduce stock
                $new_stock = $product['stock'] - $quantity;
                $update_stock_sql = "UPDATE products SET stock = ? WHERE id = ?";
                $stmt = $conn->prepare($update_stock_sql);
                $stmt->bind_param("is", $new_stock, $product_id);
                if ($stmt->execute() === FALSE) {
                    throw new Exception("Error updating stock: " . $conn->error);
                }

                // Check if the order item already exists
                $check_item_sql = "SELECT * FROM order_items WHERE order_id = ? AND product_id = ?";
                $stmt = $conn->prepare($check_item_sql);
                $stmt->bind_param("ss", $order_id, $product_id);
                $stmt->execute();
                $check_result = $stmt->get_result();

                if ($check_result->num_rows > 0) {
                    // Update quantity if it already exists
                    $update_order_item_sql = "UPDATE order_items SET quantity = quantity + ? WHERE order_id = ? AND product_id = ?";
                    $stmt = $conn->prepare($update_order_item_sql);
                    $stmt->bind_param("iss", $quantity, $order_id, $product_id);
                    if ($stmt->execute() === FALSE) {
                        throw new Exception("Error updating order item: " . $conn->error);
                    }
                } else {
                    // Insert new order item
                    $insert_order_item_sql = "INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($insert_order_item_sql);
                    $stmt->bind_param("ssi", $order_id, $product_id, $quantity);
                    if ($stmt->execute() === FALSE) {
                        throw new Exception("Error inserting order item: " . $conn->error);
                    }
                }

                // Prepare item details for Midtrans
                $orderItems[$product['name']] = [
                    'price' => $product['price'],
                    'quantity' => $quantity,
                ];
            } else {
                throw new Exception("Insufficient stock for product ID: $product_id");
            }
        }

        // Update total amount in orders table
        $update_order_sql = "UPDATE orders SET total_amount = ? WHERE id = ?";
        $stmt = $conn->prepare($update_order_sql);
        $stmt->bind_param("ds", $total_amount, $order_id);
        if ($stmt->execute() === FALSE) {
            throw new Exception("Error updating total amount: " . $conn->error);
        }

        // Midtrans configuration
        \Midtrans\Config::$serverKey = 'SB-Mid-server-WTEbvKRslcocz_oQbr5lFnYo';
        \Midtrans\Config::$isProduction = false;
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        // Prepare transaction details
        $transaction_details = array(
            'order_id' => $order_id,
            'gross_amount' => $total_amount,
        );
        $item_details = [];
        foreach ($orderItems as $productName => $item) {
            $item_details[] = array(
                'id' => $productName,
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'name' => $productName,
            );
        }
        $customer_details = array(
            'first_name' => $name,
            'last_name' => '',
            'email' => $email,
            'phone' => $phone,
            'shipping_address' => $address,
        );
        $transaction = array(
            'transaction_details' => $transaction_details,
            'customer_details' => $customer_details,
            'item_details' => $item_details,
        );

        // Generate Snap token and redirect user
        try {
            $snapToken = \Midtrans\Snap::getSnapToken($transaction);
            echo "<script>window.location.href = 'https://app.sandbox.midtrans.com/snap/v2/vtweb/$snapToken';</script>";
            echo "<noscript><meta http-equiv='refresh' content='0;url=https://app.sandbox.midtrans.com/snap/v2/vtweb/$snapToken'></noscript>";
        } catch (Exception $e) {
            echo "Midtrans Error: " . $e->getMessage();
        }

        $conn->commit();
        echo "Order placed successfully!";
    } else {
        throw new Exception("Error inserting order: " . $stmt->error);
    }
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo "Failed to place order: " . $e->getMessage();
}

$conn->close();
?>
