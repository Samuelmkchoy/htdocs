<?php

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

    //Does this client has previous failed login attempts?
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
                //Assuming theres 5 failed logins from this IP now check the timestamp to lock them out for 3 minutes
                $checkTime = "SELECT `timeStamp` FROM `failedLogins` WHERE `ip` = ?"; //$ipAddr
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
                    $recordLogin = "INSERT INTO `loginEvents` (`ip`, `timeStamp`, `user_id`, `outcome`) VALUES (?, ?, ?, 'fail')"; //$ipAddr, $time, $uid
                    $stmt = $conn->prepare($recordLogin);
                    $stmt->bind_param("sss", $ipAddr, $time, cleanChars($uid));
                    $stmt->execute();

                    if(!$stmt->execute()) {
                        die("Errory: " . $stmt->error);
                    }
                    //Redirect given lockout is currently enabled
                    header("location: ../index.php");
                    
                } else {

                    //Update lockOutCount
                    $updateLockOutCount = "UPDATE `failedLogins` SET `lockOutCount` = `lockOutCount` + 1 WHERE `ip` = ?"; //$ipAddr
                    $stmt = $conn->prepare($updateLockOutCount);
                    $stmt->bind_param("s", $ipAddr);

                    if(!$stmt->execute()) {
                        die("Errorz: " . $stmt->error);
                    } else {

                        //Otherwise update the lockout counter/timestamp
                        $currTime = date("Y-m-d H:i:s");
                        $updateCount = "UPDATE `failedLogins` SET `failedLoginCount` = '0', `timeStamp` = ? WHERE `ip` = ?"; //$currTime, $ipAddr
                        $stmt = $conn->prepare($updateCount);
                        $stmt->bind_param("ss", $currTime, $ipAddr);

                        if(!$stmt->execute()) {
                            die("Error: " . $stmt->error);
                        }
                        
                        processLogin($conn,$uid,$pwd,$ipAddr); 
                    }
                }
            } else {
                processLogin($conn, $uid, $pwd, $ipAddr);
            }
        }
    }
}

function processLogin($conn, $uid, $pwd, $ipAddr)
{
    // Errors handlers
    // Check if inputs are empty
    if (empty($uid) || empty($pwd)) {
        header("Location: ../index.php?login=empty");
        failedLogin($uid, $ipAddr);
        exit();
    }

    $sql = "SELECT * FROM sapusers WHERE user_uid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $uid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows < 1) {
        failedLogin($uid, $ipAddr);
    } else {
        if ($row = $result->fetch_assoc()) {
            //Check password
            $hashedPwdCheck = $row['user_pwd'];

            if (password_verify($pwd, $hashedPwdCheck)) {
                //Initiate session
                $_SESSION['u_id'] = $row['user_id'];
                $_SESSION['u_uid'] = $row['user_uid'];
                $_SESSION['u_admin'] = $row['user_admin']; //Will be 0 for non admin users

                // Regenerate session ID to prevent session fixation attacks
                session_regenerate_id(true);

                // Redirect to authenticated page
                header("Location: ../auth1.php");
                exit();
            } else {
                failedLogin($uid, $ipAddr);
            }
        }
    }
}

function failedLogin ($uid,$ipAddr) {
    include "dbh.inc.php";
    //When login fails redirect to index and set the failedMsg variable so it can be displayed on index
    $_SESSION['failedMsg'] = "The username " . cleanChars($uid) . " and password could not be authenticated at this moment.";
    
    //Store unsuccessful login attempt, uid, timestamp, IP in log format for viewing at admin.php
    $time = date("Y-m-d H:i:s");
    $recordLogin = "INSERT INTO `loginEvents` (`ip`, `timeStamp`, `user_id`, `outcome`) VALUES (?, ?, ?, 'fail')"; //$ipAddr, $time, $uid
    $stmt = $conn->prepare($recordLogin);
    $stmt->bind_param("sss", $ipAddr, $time, cleanChars($uid));

    if(!$stmt->execute()) {
        die("Error 1: " . $stmt->error);
    } else {
        //Update failed login count for client
        $currTime = date("Y-m-d H:i:s");
        $updateCount = "UPDATE `failedLogins` SET `failedLoginCount` = `failedLoginCount` + 1, `timeStamp` = ? WHERE `ip` = ?"; //$currTime, $ipAddr
        $stmt = $conn->prepare($updateCount);
        $stmt->bind_param("ss", $currTime, $ipAddr);

        if(!$stmt->execute()) {
            die("Error 2: " . $stmt->error);
        } else {
            header("Location: ../index.php");
            exit();
        }
    }
    
}
?>
