<?php
session_start();
require_once 'includes/config.php';

function flash($message) {
    $_SESSION['flash'] = $message;
}

function show_flash() {
    if (!empty($_SESSION['flash'])) {
        echo '<div class="alert alert-info">' . $_SESSION['flash'] . '</div>';
        unset($_SESSION['flash']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (!$name || !$email || !$password) {
        flash('All required fields are required.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('Invalid email address.');
    } elseif ($password !== $confirm_password) {
        flash('Passwords do not match.');
    } elseif (strlen($password) < 6) {
        flash('Password must be at least 6 characters.');
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            flash('Email already registered.');
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name,email,password,phone,address) VALUES (?,?,?,?,?)");
            $stmt->bind_param("sssss", $name, $email, $hashed, $phone, $address);
            if ($stmt->execute()) {
                flash('Registration successful! You can now login.');
                header('Location: login.php');
                exit;
            } else {
                flash('Something went wrong, try again.');
            }
            $stmt->close();
        }
    }
}

include_once 'includes/header.php';
?>

<div class="container my-5" style="max-width:500px;">
    <h2 class="mb-4 text-center">Register</h2>
    <?php show_flash(); ?>
    <form method="post">
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label>Address</label>
            <textarea name="address" class="form-control"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100">Register</button>
        <p class="mt-3 text-center">Already have an account? <a href="login.php">Login</a></p>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
