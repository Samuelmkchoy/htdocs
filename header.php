<?php
ini_set('session.use_only_cookies', 1);
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

// Generate CSRF token only if it doesn't exist or on login
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Session timeout and activity management
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    session_unset();
    session_destroy();
}
$_SESSION['LAST_ACTIVITY'] = time();

header("Content-Security-Policy: default-src 'self'; script-src 'self'; object-src 'none';");

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Super Secure Site</title>
<link rel="stylesheet" href="css/style.css">
<script>
//Run the function on window.onload
    window.onload = function() {
    inactiveUser(); 
}
var inactiveUser = function () {
var timer;

//Relevant DOM Events in order to reset the time (whenever the user is deemed active)
window.onload = resetTimer;
document.onmousemove = resetTimer;
document.onkeypress = resetTimer;

//Only logout if the user is logged in
function logout() {
    var session='<?php echo $session;?>';
    if(session == 1) {
        location.href = './includes/logout.inc.php'
    } else {
        //No need to logout, they're not logged in
    }
}

function resetTimer() {
    clearTimeout(timer);
    timer = setTimeout(logout, 600000) //600000 10 minutes in milliseconds
}
};
</script>
</head>
<body>

<Meta refresh for automatic logout should be used cautiously, consider removing it if using JavaScript-based timeout -- />
<meta http-equiv="refresh" content="3600;url=includes/logout.inc.php" />



<header>
<nav>
<div class="main-wrapper">
<ul class="nav-bar">
<li><a href="index.php">Home</a></li>
     
<?php
    if (!isset($_SESSION['u_id'])) {
        echo '<li><a href="register.php">Register</a></li>';
    }
    if (isset($_SESSION['u_uid'])) {
        $admin_status = $_SESSION['u_admin'];
        if (isset($_SESSION['u_id']) && $admin_status == 1) {
            echo '<li><a href="admin.php">Admin</a></li>';
            echo '<li><a href="auth1.php">Auth1</a></li>';
            echo '<li><a href="auth2.php?FileToView=yellow.txt">Auth2</a></li>';
            echo '<li><a href="change.php">Change Password</a></li>';
        } else if (isset($_SESSION['u_id'])) {
            echo '<li><a href="auth1.php">Auth1</a></li>';
            echo '<li><a href="auth2.php?FileToView=yellow.txt">Auth2</a></li>';
            echo '<li><a href="change.php">Change Password</a></li>';
        } 
    }
?>
</ul>

<div class="nav-login">
<?php
    if (isset($_SESSION['u_id'])) {
        echo '<form action="includes/logout.inc.php" method="POST">
                  <button type="submit" name="submit">Log out</button>
              </form>';
    } else {
        // Ensure the CSRF token is included in the login form
        echo '<form action="includes/login.inc.php" method="POST">
                  <input type="text" name="uid" placeholder="Username" required>
                  <input type="password" name="pwd" placeholder="Password" required>
                  <input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">
                  <button type="submit" name="submit">Login</button>
              </form>
              <a href="register.php">Sign up</a>';
    }
?>
</div>

</div>
</div>
</nav>
</header>