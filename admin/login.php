<?php
session_start();
require_once("..\includes\config.php");

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM admins WHERE email='$email' LIMIT 1");

    if ($result->num_rows == 1) {
        $admin = $result->fetch_assoc();
        // Check hashed password
        if (password_verify($password, $admin['password']) || $admin['password'] === $password) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Email not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <style>
        body{font-family:Arial; background:#f2f2f2; display:flex; justify-content:center; align-items:center; height:100vh;}
        .login-box{background:white; padding:20px; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.2);}
        input[type=email], input[type=password]{width:100%; padding:10px; margin:5px 0 15px 0; border:1px solid #ccc; border-radius:4px;}
        input[type=submit]{background:#007BFF; color:white; padding:10px; border:none; border-radius:4px; cursor:pointer;}
        .error{color:red; margin-bottom:10px;}
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Admin Login</h2>
        <?php if($error) echo "<div class='error'>$error</div>"; ?>
        <form method="post">
            <label>Email:</label>
            <input type="email" name="email" required>
            
            <label>Password:</label>
            <input type="password" name="password" required>
            
            <input type="submit" value="Login">
        </form>
    </div>
</body>
</html>
