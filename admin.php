<?php

include_once 'header.php';
include_once 'includes/dbh.inc.php';

//Validation here to prevent normal user from accessing directly
if (!isset($_SESSION['u_id']) || $_SESSION['u_admin'] == 0) {
    header("Location: index.php");
    exit();
}
?>

<section class="main-container">
    <div class="main-wrapper">
        <h2>Login Events</h2>
        <div class="admin-entry-count">
            <?php
            $stmt = $conn->prepare("SELECT count(event_id) AS num_rows FROM loginevents");
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_object();
            $total = htmlspecialchars($row->num_rows);
            ?>
            <p><i>Total entry count: <?php echo $total; ?></i></p>
        </div>
        <?php
        $stmt = $conn->prepare("SELECT * FROM loginevents");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            // Escape all outputs to prevent XSS
            $id = htmlspecialchars($row['event_id']);
            $ipAddr = htmlspecialchars($row['ip']);
            $time = htmlspecialchars($row['timeStamp']);
            $user_id = htmlspecialchars($row['user_id']);
            $outcome = htmlspecialchars($row['outcome']);

            echo "<div class='admin-content'>
                      Entry ID: <b>$id</b>
                      <br>
                      IP Address: $ipAddr<br>
                      Timestamp: $time<br>
                      User ID: $user_id<br>
                      Outcome: $outcome<br>
                  </div>";

            ?>
            <!-- CSRF token field -->
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <?php
            echo "<div class='admin-content'>
            Entry ID: <b>$id</b><br>
            <form class='admin-form' method='POST' action='change.php'>
                <input type='hidden' name='csrf_token' value='" . $_SESSION['csrf_token'] . "'>
                <label>IP Address: </label><input type='text' name='IP' value='$ipAddr'><br>
                <label>Timestamp: </label><input type='text' name='eventTimestamp' value='$time'><br>
                <label>User ID: </label><input type='text' name='userId' value='$user_id'><br>
                <label>Outcome: </label><input type='text' name='outcome' value='$outcome'>
            </form>
        </div>";
  
        }
        ?>
    </div>
</section>
      <?php
            include_once 'footer.php';
      ?>
