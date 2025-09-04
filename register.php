<?php
require 'config.php';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username=='' || $password=='') $msg = 'Enter username & password';
    else {
        // check exists
        $res = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username' LIMIT 1");
        if (mysqli_fetch_assoc($res)) $msg = 'Username exists';
        else {
            $pw_hash = hash('sha256', $password);
            mysqli_query($conn, "INSERT INTO users (username, password) VALUES ('$username', '$pw_hash')");
            $_SESSION['user'] = ['id' => mysqli_insert_id($conn), 'username' => $username, 'is_admin' => 0];
            header('Location: index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="max-w-md mx-auto mt-20 bg-white p-6 rounded shadow">
        <h2 class="text-xl font-semibold mb-4">Register</h2>
        <?php if ($msg): ?><div class="p-2 bg-red-100 text-red-700 mb-3"><?= e($msg) ?></div><?php endif; ?>
        <form method="post">
            <label class="block mb-2">Username<input name="username" class="w-full border p-2 rounded"></label>
            <label class="block mb-2">Password<input name="password" type="password"
                    class="w-full border p-2 rounded"></label>
            <div class="flex gap-2">
                <button class="bg-green-600 text-white px-3 py-2 rounded">Register</button>
                <a href="login.php" class="px-3 py-2 border rounded">Login</a>
            </div>
        </form>
    </div>
</body>

</html>