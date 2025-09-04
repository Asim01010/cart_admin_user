<?php
require 'config.php';
if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
    // simple protection
    echo "Access denied. <a href='login.php'>Login</a>";
    exit;
}

// fetch orders with items
$res = mysqli_query($conn, "SELECT o.*, u.username FROM orders o JOIN users u ON u.id=o.user_id ORDER BY o.created_at DESC");
$orders = [];
while ($r = mysqli_fetch_assoc($res)) {
    $orders[] = $r;
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
    <?php if (empty($orders)): ?>
    <div>No orders yet.</div>
    <?php else: ?>
    <?php foreach ($orders as $o): ?>
    <div class="bg-white p-4 rounded shadow mb-4">
        <div class="flex justify-between">
            <div>Order #<?= e($o['id']) ?> by <strong><?= e($o['username']) ?></strong></div>
            <div><?= e($o['created_at']) ?> — ₨ <?= e($o['total']) ?></div>
        </div>
        <div class="mt-3">
            <table class="w-full text-sm">
                <tr class="font-semibold border-b">
                    <th>Product</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                </tr>
                <?php
            $res2 = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id = " . intval($o['id']));
            while ($it = mysqli_fetch_assoc($res2)) {
                echo "<tr class='border-b'><td>".e($it['title'])."</td><td>₨ ".e($it['price'])."</td><td>".e($it['qty'])."</td><td>₨ ".e($it['subtotal'])."</td></tr>";
            }
            ?>
            </table>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</body>

</html>