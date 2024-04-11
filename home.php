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
                // Fixed database name to prevent SQL Injection (assuming no user input is involved here)
                $query = "CREATE DATABASE IF NOT EXISTS secureappdev";
                if (mysqli_query($conn, $query)) {
                    echo "Database created successfully or already exists";
                } else {
                    echo "Error creating database: " . htmlspecialchars(mysqli_error($conn));
                }
            }

            mysqli_close($conn);
        ?>
        
    </div>
</section>

<?php
include_once 'footer.php';
?>