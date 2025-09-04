<?php
require 'config.php';

/**
 * Helper: fetch product by id
 */
function getProduct($conn, $id) {
    $id = intval($id);
    $res = mysqli_query($conn, "SELECT * FROM products WHERE id = $id LIMIT 1");
    return $res ? mysqli_fetch_assoc($res) : null;
}

/**
 * Ensure cart exists. Cart items may be stored as:
 *  - integer qty:   $_SESSION['cart'][5] = 2
 *  - associative:   $_SESSION['cart'][5] = ['qty'=>2, 'name'=>'...']
 */
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

/* ---------- ADD ---------- */
if ($action === 'add') {
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) { echo 'Invalid product'; exit; }

    if (isset($_SESSION['cart'][$id])) {
        // support both styles
        if (is_array($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['qty'] = intval($_SESSION['cart'][$id]['qty'] ?? 0) + 1;
        } else {
            $_SESSION['cart'][$id] = intval($_SESSION['cart'][$id]) + 1;
        }
    } else {
        // simplest form: store qty as integer
        $_SESSION['cart'][$id] = 1;
    }

    echo 'OK';
    exit;
}

/* ---------- REMOVE ---------- */
if ($action === 'remove') {
    $id = intval($_POST['id'] ?? 0);
    if ($id > 0 && isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
    }
    echo 'OK';
    exit;
}

/* ---------- UPDATE QUANTITY ---------- */
if ($action === 'update') {
    $id = intval($_POST['id'] ?? 0);
    $qty = max(1, intval($_POST['qty'] ?? 1));
    if ($id > 0) {
        if (isset($_SESSION['cart'][$id]) && is_array($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['qty'] = $qty;
        } else {
            $_SESSION['cart'][$id] = $qty;
        }
    }
    echo 'OK';
    exit;
}

/* ---------- SHOW CART (renders HTML) ---------- */
if ($action === 'show') {
    if (empty($_SESSION['cart'])) {
        echo "<div class='text-gray-500'>Cart is empty</div>";
        exit;
    }

    $totalQty = 0;
    $totalPrice = 0.0;

    echo "<table class='w-full text-sm'>";
    echo "<tr class='font-semibold border-b'><th class='text-left'>Image</th><th>Name</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr>";

    foreach ($_SESSION['cart'] as $pid => $entry) {
        $pid = intval($pid);

        // figure out qty from entry (supports both styles)
        if (is_array($entry)) {
            $qty = intval($entry['qty'] ?? ($entry[0] ?? 1));
        } else {
            $qty = intval($entry);
        }
        if ($qty <= 0) continue;

        $product = getProduct($conn, $pid);
        if (!$product) continue; // product removed from DB

        $price = floatval($product['price']);          // make sure price is numeric
        $subtotal = $price * $qty;                     // now safe numeric multiplication
        $totalQty += $qty;
        $totalPrice += $subtotal;

        echo "<tr class='border-b'>";
        echo "<td class='py-2'><img src='".e($product['image'])."' class='w-14 h-14 object-cover'></td>";
        echo "<td>" . e($product['title']) . "</td>";
        echo "<td>₨ " . number_format($price, 2) . "</td>";
        echo "<td><input data-id='".e($pid)."' class='qtyInput border px-1 w-16' type='number' value='".e($qty)."' min='1'></td>";
        echo "<td>₨ " . number_format($subtotal, 2) . "</td>";
        echo "<td><button data-id='".e($pid)."' class='removeItem text-red-600'>Remove</button></td>";
        echo "</tr>";
    }

    echo "<tr class='font-semibold'><td colspan='3' class='text-right'>Total:</td><td>".e($totalQty)."</td><td>₨ ".number_format($totalPrice, 2)."</td><td></td></tr>";
    echo "</table>";
    exit;
}

/* ---------- CHECKOUT ---------- */
if ($action === 'checkout') {
    // must be logged in
    if (!isset($_SESSION['user'])) {
        echo 'LOGIN_REQUIRED';
        exit;
    }

    if (empty($_SESSION['cart'])) {
        echo 'Cart empty';
        exit;
    }

    // compute total safely
    $totalPrice = 0.0;
    foreach ($_SESSION['cart'] as $pid => $entry) {
        $pid = intval($pid);
        if (is_array($entry)) $qty = intval($entry['qty'] ?? 1);
        else $qty = intval($entry);
        if ($qty <= 0) continue;
        $product = getProduct($conn, $pid);
        if (!$product) continue;
        $price = floatval($product['price']);
        $totalPrice += $price * $qty;
    }

    // insert order
    $user_id = intval($_SESSION['user']['id']);
    $totalP = floatval($totalPrice);
    $sql = "INSERT INTO orders (user_id, total) VALUES ($user_id, $totalP)";
    if (!mysqli_query($conn, $sql)) {
        echo 'DB error: ' . mysqli_error($conn);
        exit;
    }
    $order_id = mysqli_insert_id($conn);

    // insert items
    foreach ($_SESSION['cart'] as $pid => $entry) {
        $pid = intval($pid);
        if (is_array($entry)) $qty = intval($entry['qty'] ?? 1);
        else $qty = intval($entry);
        if ($qty <= 0) continue;
        $product = getProduct($conn, $pid);
        if (!$product) continue;
        $title = mysqli_real_escape_string($conn, $product['title']);
        $price = floatval($product['price']);
        $subtotal = $price * $qty;
        $sql = "INSERT INTO order_items (order_id, product_id, title, price, qty, subtotal)
                VALUES ($order_id, $pid, '$title', $price, $qty, $subtotal)";
        mysqli_query($conn, $sql);
    }

    // clear cart
    $_SESSION['cart'] = [];
    echo 'OK';
    exit;
}

/* ---------- OPTIONAL: RESET CART (useful if session contains corrupted data) ---------- */
if ($action === 'reset_cart') {
    $_SESSION['cart'] = [];
    echo 'OK';
    exit;
}

echo 'No action';