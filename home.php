<?php
include_once 'header.php';
?>
<section class="main-container">
    <div class="main-wrapper">
        <h2>Homepage</h2>
        Welcome to this Super Secure PHP Application.
        
        <?php
            $conn = mysqli_connect("localhost", "TEST", "");

            if (!$conn) {
                die('Could not connect: ' . htmlspecialchars(mysqli_connect_error()));
            } else {
                mysqli_query($conn,"CREATE DATABASE secureappdev");
                
                }

            mysqli_close($conn);
        ?>
        
    </div>
</section>

<?php
include_once 'footer.php';
?>