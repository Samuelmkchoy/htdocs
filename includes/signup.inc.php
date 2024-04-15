<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'dbh.inc.php';

header("Content-Security-Policy: default-src 'self'; script-src 'self';");

// Check if the CSRF token is set and matches the token stored in the session
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    // If the tokens do not match, set an error message and redirect to the registration page
    $_SESSION['registerError'] = "CSRF token validation failed.";
    header("Location: ../register.php");
    exit();
}

// Check if the form was submitted
if (isset($_POST['submit'])) {
    // Sanitize the username and password input
    $uid = $_POST['uid'];
    $pwd = $_POST['pwd'];

    // IP address handling with consideration to privacy regulations
    $ipAddr = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);

    // Determine the user's IP address
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ipAddr = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipAddr = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ipAddr = $_SERVER['REMOTE_ADDR'];
    }

    // Check if the user is locked out due to failed login attempts
    $checkClient = "SELECT `failedLoginCount` FROM `failedLogins` WHERE `ip` = ?";
    $stmt = $conn->prepare($checkClient);
    $stmt->bind_param("s", $ipAddr);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->fetch_assoc()['failedLoginCount'] >= 5) {
        // Handle the user being locked out
    }

    // Validate that the username and password fields are not empty
    if (empty($uid) || empty($pwd)) {
        $_SESSION['registerError'] = "Cannot submit empty username or password.";
        header("Location: ../register.php");
        exit();
    }

    // Ensure the username contains only alphabetical characters
    if (!preg_match("/^[a-zA-Z]*$/", $uid)) {
        $_SESSION['registerError'] = "Username must only contain alphabetic characters.";
        header("Location: ../register.php");
        exit();
    }

    // Hash the password
    $hashedPwd = password_hash($pwd, PASSWORD_DEFAULT);

    // Check if the username already exists in the database
    $sql = "SELECT * FROM `sapusers` WHERE `user_uid` = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $uid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $_SESSION['registerError'] = "Username already exists.";
        header("Location: ../register.php");
        exit();
    } else {
    // Insert the new user into the database
    $sql = "INSERT INTO `sapusers` (`user_uid`, `user_pwd`) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $uid, $hashedPwd);

    if ($stmt->execute()) {
        // Fetch the user ID of the newly registered user
        $new_user_id = $conn->insert_id;
    
        // Set necessary session variables
        $_SESSION['u_id'] = $new_user_id;
        $_SESSION['u_uid'] = $uid;
        $_SESSION['u_admin'] = 0;  // assuming default admin status is 0
    
        $_SESSION['registerSuccess'] = "You've successfully registered.";
        header("Location: ../auth1.php");
        exit();
    } else {
        $_SESSION['registerError'] = "An unexpected error occurred. Please try again.";
        header("Location: ../register.php");
        exit();
    }
}
} else {
header("Location: ../register.php");
exit();
}