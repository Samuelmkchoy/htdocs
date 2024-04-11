<?php
include_once 'header.php';
?>

<section class="main-container">
    <div class="main-wrapper">
        <h2>Change Password</h2>
        <br>
        <p>Please ensure your new password conforms to the complexity rules:</p>
        <ul>
            <li>Be at least 8 characters long</li>
            <li>Contain a mix of uppercase and lowercase letters</li>
            <li>Contain at least one digit</li>
        </ul>
        <form class="signup-form" action="includes/reset.inc.php" method="POST">
            <input type="password" name="old" placeholder="Old Password" required>
            <input type="password" name="new" placeholder="New Password" required>
            <input type="password" name="new_confirm" placeholder="Confirm New Password" required>
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <button type="submit" name="reset" value="yes">Reset</button>
        </form>
    </div>
</section>

<?php
include_once 'footer.php';
?>