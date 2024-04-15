<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

session_start();

include 'dbh.inc.php';

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if form submitted
if (isset($_POST['submit'])) {
    // Validate CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        // CSRF token validation failed
        header("Location: ../index.php?error=csrf");
        exit();
    }

    // Retrieve IP address
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ipAddr = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipAddr = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ipAddr = $_SERVER['REMOTE_ADDR'];
    }

    //Sanitize inputs
    $uid = mysqli_real_escape_string($conn, $_POST['uid']);
    $pwd = mysqli_real_escape_string($conn, $_POST['pwd']);

    //Sanitize UID
    $sanitizedUid = cleanChars($uid);

    //Does this client have previous failed login attempts?
    $checkClient = "SELECT `failedLoginCount`, `timeStamp` FROM `failedLogins` WHERE `ip` = ?";
    $stmt = $conn->prepare($checkClient);
    $stmt->bind_param("s", $ipAddr);
    $stmt->execute();
    $result = $stmt->get_result();
    $time = date("Y-m-d H:i:s");

    //New user, insert into database and login
    //"Initialise" attempts recording their IP, timestamp and setup a failed login count, based off IP and attempted uid
    if ($result->num_rows == 0) {
        $addUser = "INSERT INTO `failedLogins` (`ip`, `timeStamp`, `failedLoginCount`, `lockOutCount`) VALUES (?, ?, '0', '0')";
        $stmt = $conn->prepare($addUser);
        $stmt->bind_param("ss", $ipAddr, $time);
        $stmt->execute();

        processLogin($conn, $uid, $pwd, $ipAddr);
    } else {
        // Handle subsequent visits for each client
        $getCount = "SELECT `failedLoginCount` FROM `failedLogins` WHERE `ip` = ?";
        $stmt = $conn->prepare($getCount);
        $stmt->bind_param("s", $ipAddr);
        $stmt->execute();
        $result = $stmt->get_result();

        if (!$result) {
            die("Error: " . $stmt->error);
        } else {
            $failedLoginCount = ($result->fetch_row()[0]);

            if ($failedLoginCount >= 5) {
                //Assuming there are 5 failed logins from this IP now check the timestamp to lock them out for 3 minutes
                $checkTime = "SELECT `timeStamp` FROM `failedLogins` WHERE `ip` = ?";
                $stmt = $conn->prepare($checkTime);
                $stmt->bind_param("s", $ipAddr);
                $stmt->execute();
                $result = $stmt->get_result();

                if(!$result) {
                    die('Error: ' . $stmt->error);
                } else {
                    $failedLoginTime = ($result->fetch_row()[0]);
                }

                $currTime = date("Y-m-d H:i:s");
                $timeDiff = abs(strtotime($currTime) - strtotime($failedLoginTime));
                $_SESSION['timeLeft'] = 180 - $timeDiff; //Print to inform user of how many seconds remain on the lockout

                if((int)$timeDiff <= 180) {
                    $_SESSION['lockedOut'] = "Due to multiple failed logins you're now locked out, please try again in 3 minutes"; //Should also stop user if they try to register

                    //Store unsuccessful login attempt, uid, timestamp, IP in log format for viewing at admin.php
                    $time = date("Y-m-d H:i:s");
                    $recordLogin = "INSERT INTO `loginEvents` (`ip`, `timeStamp`, `user_id`, `outcome`) VALUES (?, ?, ?, 'fail')"; //$ipAddr, $time, $sanitizedUid
                    $stmt = $conn->prepare($recordLogin);
                    $stmt->bind_param("sss", $ipAddr, $time, $sanitizedUid);
                    $stmt->execute();

                    if(!$stmt->execute()) {
                        die("Error: " . $stmt->error);
                    }
                    //Redirect given lockout is currently enabled
                    header("location: ../index.php");
                    
                } else {

                    //Update lockOutCount
                    $updateLockOutCount = "UPDATE `failedLogins` SET `lockOutCount` = `lockOutCount` + 1 WHERE `ip` = ?"; //$ipAddr
                    $stmt = $conn->prepare($updateLockOutCount);
                    $stmt->bind_param("s", $ipAddr);

                    if(!$stmt->execute()) {
                        die("Error: " . $stmt->error);
                    } else {

                        //Otherwise update the lockout counter/timestamp
                        $currTime = date("Y-m-d H:i:s");
                        $updateCount = "UPDATE `failedLogins` SET `failedLoginCount` = '0', `timeStamp` = ? WHERE `ip` = ?"; //$currTime, $ipAddr
                        $stmt = $conn->prepare($updateCount);
                        $stmt->bind_param("ss", $currTime, $ipAddr);

                        if(!$stmt->execute()) {
                            die("Error: " . $stmt->error);
                        }
                        
                        processLogin($conn, $uid, $pwd, $ipAddr); 
                    }
                }
            } else {
                processLogin($conn, $uid, $pwd, $ipAddr);
            }
        }
    }
}

function processLogin($conn, $uid, $pwd, $ipAddr) {
    if (empty($uid) || empty($pwd)) {
        header("Location: ../index.php?login=empty");
        failedLogin($uid, $ipAddr);
        exit();
    } else {

        $sql = "SELECT * FROM sapusers WHERE user_uid = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $uid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows < 1) {
            failedLogin($uid, $ipAddr);
        } else {
            $row = $result->fetch_assoc();

            if (password_verify($pwd, $row['user_pwd'])) {
                $_SESSION['u_id'] = $row['user_id'];
                $_SESSION['u_uid'] = $row['user_uid'];
                $_SESSION['u_admin'] = $row['user_admin']; // Will be 0 for non-admin users

                // Store successful login attempt, uid, timestamp, IP in log format for viewing at admin.php
                $time = date("Y-m-d H:i:s");
                $recordLogin = "INSERT INTO `loginEvents` (`ip`, `timeStamp`, `user_id`, `outcome`) VALUES (?, ?, ?, 'success')";
                $stmt = $conn->prepare($recordLogin);
                $stmt->bind_param("sss", $ipAddr, $time, cleanChars($uid));

                if (!$stmt->execute()) {
                    die("Error: " . $stmt->error);
                } else {
                    header("Location: ../auth1.php");
                    exit();
                }
            } else {
                // Password does not match
                failedLogin($uid, $ipAddr);
            }
        }
    }
}

function failedLogin($uid, $ipAddr) {
    include "dbh.inc.php";
    $sanitizedUid = cleanChars($uid);

    error_log("Login failed for user: $uid.");

    $_SESSION['failedMsg'] = "The username " . $sanitizedUid . " and password could not be authenticated at this moment.";

    $time = date("Y-m-d H:i:s");
    $recordLogin = "INSERT INTO `loginEvents` (`ip`, `timeStamp`, `user_id`, `outcome`) VALUES (?, ?, ?, 'fail')";
    $stmt = $conn->prepare($recordLogin);
    $stmt->bind_param("sss", $ipAddr, $time, $sanitizedUid);

    if (!$stmt->execute()) {
        error_log("Error inserting login event: " . $stmt->error);
    } else {
        error_log("Failed login recorded for $uid at IP: $ipAddr");
        updateFailedLoginCount($ipAddr, $conn);
    }
}

function updateFailedLoginCount($ipAddr, $conn) {
    $currTime = date("Y-m-d H:i:s");
    $updateCount = "UPDATE `failedLogins` SET `failedLoginCount` = `failedLoginCount` + 1, `timeStamp` = ? WHERE `ip` = ?";
    $stmt = $conn->prepare($updateCount);
    $stmt->bind_param("ss", $currTime, $ipAddr);

    if (!$stmt->execute()) {
        error_log("Error updating failed login count: " . $stmt->error);
    } else {
        error_log("Updated failed login count for IP: $ipAddr");
        header("Location: ../index.php");
        exit();
    }
}

function cleanChars($val) {
    $val = str_replace('&', '&amp;', $val);
    $val = str_replace('<', '&lt;', $val);
    $val = str_replace('>', '&gt;', $val);
    $val = str_replace('"', '&quot;', $val);
    $val = str_replace('\'', '&#x27;', $val);
    $val = str_replace('%', '&#x25;', $val);
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}