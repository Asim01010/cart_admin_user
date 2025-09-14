<?php
require 'config.php';
if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
    // simple protection
    echo "Access denied. <a href='login.php'>Login</a>";
    exit;
}

// fetch orders with items
// 1. Ask the database: "Give me all the orders, and also the username of the person who made them."
$query = "SELECT orders.*, users.username
FROM orders
JOIN users ON users.id = orders.user_id
ORDER BY orders.created_at DESC";

$result = mysqli_query($conn, $query);

// 2. Make an empty box (array) to keep all orders inside
$orders = [];

// 3. Take each row from the database result, one by one
while ($row = mysqli_fetch_assoc($result)) {
    // 4. Put that row (order + username) inside our box
    $orders[] = $row;
}

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Admin - Orders</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-6">
    <h1 class="text-2xl font-semibold mb-4">Admin — Orders</h1>
    <a href="index.php" class="text-blue-600 mb-4 inline-block">Back to shop</a>
    <?php
if (empty($orders)) {
    echo "<div>No orders yet.</div>";
} else {
    foreach ($orders as $o) {
        ?>
    <div class="bg-white p-4 rounded shadow mb-4">
        <div class="flex justify-between">
            <div>Order #<?= $o['id'] ?> by <strong><?= $o['username'] ?></strong></div>
            <div><?= $o['created_at'] ?> — ₨ <?= $o['total'] ?></div>
        </div>
        <div class="mt-3">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="font-semibold border-b">
                        <th class="text-left p-2">Product</th>
                        <th class="text-left p-2">Price</th>
                        <th class="text-left p-2">Qty</th>
                        <th class="text-left p-2">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $res2 = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id = " . intval($o['id']));
                        while ($it = mysqli_fetch_assoc($res2)) {
                            echo "<tr class='border-b'>
                                    <td class='p-2'>".$it['title']."</td>
                                    <td class='p-2'>₨ ".$it['price']."</td>
                                    <td class='p-2'>".$it['qty']."</td>
                                    <td class='p-2'>₨ ".$it['subtotal']."</td>
                                  </tr>";
                        }
                        ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    }
}
?>

</body>

</html>