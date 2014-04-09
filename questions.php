<?php
	session_start();
	
	require_once("functions.php");
	
	db_connect();
	check_login();
	check_admin(); 

	$data = UserManager::get_userdata($_SESSION["user"]);	
	
?>


<!DOCTYPE html>
<html>
	<head>
		<title>Abizeitung - Fragen</title>
		<?php head(); ?>
	</head>
    
    <body>
		<?php require("nav-bar.php") ?>
		<div id="questions-management" class="container">
        	<ul>
        	<?php
				global $mysqli;
				
				$stmt = $mysqli->prepare("
					SELECT id, title
					FROM questions
				");
				
				$stmt->execute();
				$stmt->bind_result($questions["id"], $questions["title"]);
				
				while($stmt->fetch()):
			?>
            	<li><?php echo $questions["title"] ?></li>
            <?php endwhile; ?>
            </ul>
        </div>
	</body>
</html>

<?php db_close(); ?>