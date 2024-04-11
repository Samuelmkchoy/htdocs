<?php

session_start(); 
include_once 'header.php';

if (!isset($_SESSION['u_id'])) {
    header("Location: home.php");
    exit();
}

$allowedPath = '/Applications/XAMPP/xamppfiles/htdocs';

function sanitizeFilePath($path, $allowedPaths) {
    
    $realBase = realpath($allowedPaths);
    $userPath = realpath($allowedPaths . '/' . $path);

    if ($userPath === false || strpos($userPath, $realBase) !== 0) {
        echo "Path validation failed<br>"; // Debugging output
        return false;
    }
    return $userPath;
}

?>
<section class="main-container">
    <div class="main-wrapper">
        <h2>Auth page 2</h2>
        <?php
        if (isset($_GET['FileToView'])) {
            $filePath = sanitizeFilePath($_GET['FileToView'], $allowedPath);
    
            if (!$filePath) {
                echo "No file found or access denied.";
            } else {
                $fileData = file_get_contents($filePath);
                echo htmlspecialchars($fileData, ENT_QUOTES, 'UTF-8');
            }
        } else {
            echo "No file specified.";
        }
        ?>
    </div>
</section>

<?php
include_once 'footer.php';
?>