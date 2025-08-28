<?php 
session_start();
include 'includes/config.php';
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name && $email && $message) {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name,email,subject,message,created_at) VALUES (?,?,?,?,NOW())");
        $stmt->bind_param("ssss",$name,$email,$subject,$message);

        if ($stmt->execute()) {
            flash('✅ Message sent successfully. We will contact you soon.','success');
            $_POST=[];
        } else {
            flash('❌ DB Error: '.$stmt->error,'danger');
        }
    } else {
        flash('⚠ Please fill all required fields.','danger');
    }
}
?>

<div class="container mt-5">
    <h3>📩 Contact Us</h3>

    <!-- Flash message -->
    <?php if (isset($_SESSION['flash'])): ?>
    <div class="alert alert-<?php echo htmlspecialchars($_SESSION['flash_type']); ?> alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['flash']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash'], $_SESSION['flash_type']); endif; ?>

    <form method="post" class="row g-3 mt-3">
        <div class="col-md-6">
            <input type="text" class="form-control" name="name" placeholder="Your Name *" 
                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
        </div>
        <div class="col-md-6">
            <input type="email" class="form-control" name="email" placeholder="Your Email *" 
                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
        </div>
        <div class="col-12">
            <input type="text" class="form-control" name="subject" placeholder="Subject (optional)" 
                   value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>">
        </div>
        <div class="col-12">
            <textarea class="form-control" name="message" rows="5" placeholder="Message *" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Send Message</button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
