<?php
    include_once 'header.php';

    // The CSRF token generation is done in 'header.php', so no need to repeat it here.
?>

<section class="main-container">
    <div class="main-wrapper">
        <h2>Signup</h2>
        Please note your username must only contain alphabetic characters.
        <br><br>
        Please ensure your password conforms to the complexity rules:
        <br><br>
        • Be at least 8 characters long<br>
        • Contain a mix of uppercase and lowercase letters<br>
        • Contain at least one digit<br>

        <!-- Move the CSRF token hidden input inside the form -->
        <form class="signup-form" action="includes/signup.inc.php" method="POST">
            <input type="text" name="uid" value="" placeholder="Username" required>
            <input type="password" name="pwd" value="" placeholder="Password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" required>
            
            <!-- CSRF token field -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars ($_SESSION['csrf_token']); ?>">

            <button type="submit" name="submit">Register now</button>
        </form>
    </div>
</section>

<?php
    include_once 'footer.php';
?>