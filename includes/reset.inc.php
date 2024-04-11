<?php

include 'dbh.inc.php';

session_start();

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['resetError'] = "CSRF token validation failed.";
    header("Location: ../change.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldpass = $_POST['old'];
    $newConfirm = $_POST['new_confirm'];
    $newpass = $_POST['new'];

    if (!preg_match("/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/", $newpass)) {
        $_SESSION['resetError'] = "New password does not meet complexity requirements.";
        header("Location: ../change.php");
        exit();
    }

    if (empty($oldpass) || empty($newpass) || empty($newConfirm)) {
        $_SESSION['resetError'] = "Please fill in all fields.";
        header("Location: ../change.php");
        exit();
    }

    if ($newpass !== $newConfirm) {
        $_SESSION['resetError'] = "New passwords do not match.";
        header("Location: ../change.php");
        exit();
    }

    $uid = $_SESSION['u_uid'];
    $stmt = $conn->prepare("SELECT user_pwd FROM sapusers WHERE user_uid = ?");
    $stmt->bind_param("s", $uid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($oldpass, $row['user_pwd'])) {
            $hashedNewPwd = password_hash($newpass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE sapusers SET user_pwd = ? WHERE user_uid = ?");
            $stmt->bind_param("ss", $hashedNewPwd, $uid);
            if ($stmt->execute()) {
                session_unset();
                $_SESSION['resetSuccess'] = "Password updated successfully.";
                header("Location: ./logout.inc.php");
                exit();
            } else {
                $_SESSION['resetError'] = "Failed to update password.";
                header("Location: ../change.php");
                exit();
            }
        } else {
            $_SESSION['resetError'] = "Old password is incorrect.";
            header("Location: ../change.php");
            exit();
        }
    } else {
        $_SESSION['resetError'] = "User not found.";
        header("Location: ../index.php");
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}