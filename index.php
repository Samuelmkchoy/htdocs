<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

      include_once 'header.php';
?>
        <section class="main-container">
            <div class="main-wrapper">
                <h2>Homepage</h2>
				Welcome to this Super Secure PHP Application.
				<form method="post" action="">
        			<input type="submit" name="createDatabase" value="Create / Reset Database & Table">
					<!-- Include CSRF token in the form -->
					<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <br><br><br>
                                
    </form>
     
				<?php
				//DATABASE SETUP
				    $host = "localhost";
					$username = "TEST";
					$password = "";
					
					echo "<br>";
				
					
		if (isset($_POST['createDatabase'])) {
        try {
            // Connect to MySQL server
            $conn = new PDO("mysql:host=$host", $username, $password);

            // Set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			$existingDatabases = $conn->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
            echo "<br>";
                                                
            if (!in_array('secureappdev', $existingDatabases)) {
                // Create a new database
                $sql = "CREATE DATABASE secureappdev";
                $conn->exec($sql);
                echo "Database created successfully<br>";
                $sql = "USE secureappdev";
                $conn->exec($sql);
                
				$makeUsers = "CREATE TABLE `sapusers` 
				(
				`user_id` int(11) NOT NULL AUTO_INCREMENT,
				user_uid varchar(256) NOT NULL,
				user_pwd varchar(256) NOT NULL,
				user_admin int(2) NOT NULL DEFAULT 0,
				primary key (`user_id`))";
				
				$conn->exec($makeUsers);
				echo "Table 'users' created successfully<br>"; 

				$makeAdmin = "INSERT INTO `sapusers` (`user_uid`, `user_pwd`, `user_admin`) VALUES ('admin', 'AdminPass1!', '1')";
				$conn->exec($makeAdmin);
				echo "Admin Added (Username = admin, Password =AdminPass1!<br>";
				
				$makeAdmin = "INSERT INTO `sapusers` (`user_uid`, `user_pwd`, `user_admin`) VALUES ('user1', 'Password1!', '0')";
				$conn->exec($makeAdmin);
				echo "User Added (Username = user1, Password =Password1!<br>";
				
				//Make table to track pre-auth sessions that should be blocked for failed login attempts
				$makeCounter = "CREATE TABLE `failedLogins`
				(
					`event_id` int(11) NOT NULL AUTO_INCREMENT,
					`ip` varchar(128) NOT NULL,
					`timeStamp` datetime NOT NULL,
					`failedLoginCount` int(11) NOT NULL,
					`lockOutCount` int(11) NOT NULL,
					primary key (`event_id`)
				)";
				$conn->exec($makeCounter);
				
				$loginEvents = "CREATE TABLE `loginEvents`
				(
				`event_id` int(11) NOT NULL AUTO_INCREMENT,
				`ip` varchar(128) NOT NULL,
				`timeStamp` datetime NOT NULL,
				`user_id` varchar(50) NOT NULL,
				`outcome` varchar(7) NOT NULL,
				primary key (`event_id`)
				)";
				$conn->exec($loginEvents);
			}

        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }

        $conn = null; // Close the database connection
    }
				
					// Message if login fails 
                    if (isset($_SESSION['failedMsg'])) {
                        echo htmlspecialchars($_SESSION['failedMsg']);
                        unset($_SESSION['failedMsg']);
					}

					// Message if locked out and display the countdown timer
                    if (isset($_SESSION['lockedOut']) && isset($_SESSION['timeLeft']) && $_SESSION['timeLeft'] > 0) {
                        echo htmlspecialchars($_SESSION['lockedOut']);
                        ?>
                        <div id="timer">You are locked out. Please wait <span id="time"><?php echo $_SESSION['timeLeft']; ?></span> seconds.</div>
                        <script>
                            var timeLeft = <?php echo $_SESSION['timeLeft']; ?>;
                            var timerElement = document.getElementById('time');

                            var timer = setInterval(function() {
                                timeLeft--;
                                timerElement.textContent = timeLeft;

                                if (timeLeft <= 0) {
                                    clearInterval(timer);
                                    window.location.reload(); // or redirect to login page
                                }
                            }, 1000);
                        </script>
                        <?php
                        unset($_SESSION['lockedOut']);
                        unset($_SESSION['timeLeft']);
                    }


					// Print messages re: registration
                    if (isset($_SESSION['register'])) {
                        echo htmlspecialchars($_SESSION['register']);
                        unset($_SESSION['register']);
                    }

					// Print messages re: changing password
                    if (isset($_SESSION['resetError'])) {
                        echo htmlspecialchars($_SESSION['resetError']);
                        unset($_SESSION['resetError']);
                    }
      
                ?>
				
            </div>
        </section>

        <?php
        include_once 'footer.php';
        ?>