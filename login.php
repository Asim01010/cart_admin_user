<?php
require 'config.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $msg = 'Enter username and password';
    } else {
        // passwords were stored as SHA2 in db.sql; use same here
        $pw_hash = hash('sha256', $password);
        $res = mysqli_query($conn, "SELECT id, username, is_admin FROM users WHERE username = '$username' AND password = '$pw_hash' LIMIT 1");
        if ($row = mysqli_fetch_assoc($res)) {
            // login success
            $_SESSION['user'] = $row;
            header('Location: index.php');
            exit;
        } else $msg = 'Invalid credentials';
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="max-w-md mx-auto mt-20 bg-white p-6 rounded shadow">
        <h2 class="text-xl font-semibold mb-4">Login</h2>
        <?php if ($msg): ?><div class="p-2 bg-red-100 text-red-700 mb-3"><?= e($msg) ?></div><?php endif; ?>
        <form method="post">
            <label class="block mb-2">Username<input name="username" class="w-full border p-2 rounded"></label>
            <label class="block mb-2">Password<input name="password" type="password"
                    class="w-full border p-2 rounded"></label>
            <div class="flex gap-2">
                <button class="bg-blue-600 text-white px-3 py-2 rounded">Login</button>
                <a href="register.php" class="px-3 py-2 border rounded">Register</a>
            </div>
        </form>
    </div>
</body>

</html>