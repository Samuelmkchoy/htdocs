<?php
    include_once 'header.php';
    if (!isset($_SESSION['u_id'])) {
        header("Location: home.php");
        exit();
    } else {
        $user_id = htmlspecialchars($_SESSION['u_id']);
        $user_uid = htmlspecialchars($_SESSION['u_uid']);
    }
?>
        <section class="main-container">
            <div class="main-wrapper">
                <h2>Auth page 1</h2>
				Only authenticated users should be able to see this Page(1).
            </div>
        </section>
	
<?php	
	echo "<br>";
	//Reflect user's name on the page
	if(isset($_SESSION['u_id'])) {
		$user_uid = $_SESSION['u_uid'];
		echo "You're logged in as " . cleanChars($user_uid);
	}
	
	function cleanChars($val) {
        return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
    }
?>

<?php
	include_once 'footer.php';
?>