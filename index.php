<?php
require 'config.php';

// fetch products
$res = mysqli_query($conn, "SELECT * FROM products");
$products = [];
while ($row = mysqli_fetch_assoc($res)) $products[] = $row;
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Shop Demo</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

    <nav class="bg-white shadow p-4 flex justify-between">
        <div class="font-bold">Shop Demo</div>
        <div class="flex items-center gap-4">
            <?php if (isset($_SESSION['user'])): ?>
            <div>Welcome, <strong><?= e($_SESSION['user']['username']) ?></strong></div>
            <?php if ($_SESSION['user']['is_admin']): ?>
            <a href="admin.php" class="text-sm text-blue-600">Admin</a>
            <?php endif; ?>
            <a href="logout.php" class="text-sm text-red-600">Logout</a>
            <?php else: ?>
            <a href="login.php" class="text-sm text-blue-600">Login</a>
            <a href="register.php" class="text-sm text-blue-600">Register</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto p-6 grid grid-cols-3 gap-6">
        <!-- Products -->
        <div class="col-span-2">
            <h1 class="text-2xl font-semibold mb-4">Products</h1>
            <div class="grid grid-cols-3 gap-4">
                <?php foreach ($products as $p): ?>
                <div class="bg-white p-4 rounded shadow">
                    <img src="<?= e($p['image']) ?>" alt="" class="w-full h-36 object-cover mb-3">
                    <h3 class="font-medium"><?= e($p['title']) ?></h3>
                    <div class="text-green-600 font-bold">₨ <?= e($p['price']) ?></div>
                    <button class="mt-3 bg-blue-600 text-white px-3 py-2 rounded addToCart"
                        data-id="<?= $p['id'] ?>">Add to Cart</button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Cart -->
        <div>
            <h2 class="text-xl font-semibold mb-3">Your Cart</h2>
            <div id="cartBox" class="bg-white p-4 rounded shadow min-h-[200px]">
                <!-- cart content loaded by AJAX -->
                Loading cart...
            </div>

            <div class="mt-4">
                <button id="checkoutBtn" class="w-full bg-green-600 text-white py-2 rounded">Checkout</button>
            </div>
        </div>
    </div>

    <script>
    $(function() {
        // helper to load cart HTML
        function loadCart() {
            $.post('cart.php', {
                action: 'show'
            }, function(html) {
                $('#cartBox').html(html);
            });
        }

        loadCart(); // initial load

        // Add to cart
        $(document).on('click', '.addToCart', function() {
            var id = $(this).data('id');
            $.post('cart.php', {
                action: 'add',
                id: id
            }, function(resp) {
                // cart.php returns simple text messages like OK or LOGIN_REQUIRED
                if (resp === 'OK') {
                    loadCart();
                    alert('Added to cart');
                } else {
                    loadCart();
                    // resp might be 'LOGIN_REQUIRED' or error text
                    if (resp === 'LOGIN_REQUIRED') {
                        if (confirm(
                            'You must login to proceed to checkout. Go to login page?')) {
                            window.location = 'login.php';
                        }
                    } else {
                        alert(resp);
                    }
                }
            });
        });

        // Checkout button
        $('#checkoutBtn').click(function() {
            $.post('cart.php', {
                action: 'checkout'
            }, function(resp) {
                if (resp === 'OK') {
                    alert('Order placed! You will see it in admin panel.');
                    loadCart();
                } else if (resp === 'LOGIN_REQUIRED') {
                    if (confirm('You must login to checkout. Go to login?')) window.location =
                        'login.php';
                } else {
                    alert(resp);
                }
            });
        });

        // delegate remove & qty change
        $(document).on('click', '.removeItem', function() {
            var id = $(this).data('id');
            $.post('cart.php', {
                action: 'remove',
                id: id
            }, function(resp) {
                loadCart();
            });
        });

        $(document).on('change', '.qtyInput', function() {
            var id = $(this).data('id');
            var qty = $(this).val();
            if (qty < 1) {
                $(this).val(1);
                qty = 1;
            }
            $.post('cart.php', {
                action: 'update',
                id: id,
                qty: qty
            }, function(resp) {
                loadCart();
            });
        });
    });
    </script>

</body>

</html>