<?php
include 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        flash('All fields are required.');
    } else {
        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            flash('Invalid email or password.');
        } else {
            $stmt->bind_result($id, $name, $hashed);
            $stmt->fetch();

            // Support both hashed and plain passwords
            if (password_verify($password, $hashed) || $password === $hashed) {
                $_SESSION['user_id'] = $id;
                $_SESSION['user_name'] = $name;

                flash('Login successful!');
                header('Location: index.php');
                exit;
            } else {
                flash('Invalid email or password.');
            }
        }
    }
}
?>

<?php include 'includes/header.php'; ?>
<div class="container my-5" style="max-width:400px;">
    <h2 class="mb-4 text-center">Login</h2>
    <form method="post">
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100">Login</button>
        <p class="mt-3 text-center">Don't have an account? <a href="register.php">Register</a></p>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
