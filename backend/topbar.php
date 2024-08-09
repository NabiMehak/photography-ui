<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
$config = require 'config.php';

$appUrl = $config['app']['url'];
?>


<div class="topbar">
    <div class="logo">
    <img src="./images/logo.png" alt="IAML CON Logo" class="logo-image">
    </div>
    <div class="topbar-right">
        <a  href="<?php echo $appUrl;?>" class="topbar-item ">Home</a>
        <?php if (isset($_SESSION['email'])): ?>
        <div class="dropdown">
            <button class="email-profile"><?php echo $_SESSION['email']; ?></button>
            <div class="dropdown-content">
                <a href="update_profile.php" >Profile</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
        <?php else: ?>
        <div class="dropdown">
            <button class="dropbtn">Login</button>
            <div class="dropdown-content">
                <a href="login.php">Login</a>
                <a href="registration.php">Register</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
