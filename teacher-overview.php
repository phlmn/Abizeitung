<?php
	session_start();
	
	require_once("functions.php");
	require_once("classes/cUsers.php");
	
	db_connect();
	check_login();

	$data = UserManager::get_userdata($_SESSION["user"]);
	
	if(!($data["isteacher"] || $data["admin"])) {
		db_close();
		
		header("Location: ./");
		
		die;
	}
	
?>


<!DOCTYPE html>
<html>
	<head>
		<title>Abizeitung - Nutzerstatus</title>
		<?php head(); ?>
	</head>
	
	<body>
		<?php require("nav-bar.php") ?>
		<div id="user-management" class="container-fluid">
			<h1>Nutzerverwaltung</h1>
			<div class="users box">
                <?php 
					
					if($data["isteacher"]) {
						
						$stmt = $mysqli->prepare("
							SELECT tutorials.id, tutorials.name
							FROM tutorials
							WHERE tutor = (
								SELECT id
								FROM teachers
								WHERE uid = ?
							)
						");
						
						$stmt->bind_param("i", $data["id"]);
						$stmt->execute();
						
						$stmt->bind_result($tutorial["id"], $tutorial["name"]);
						$stmt->store_result();
						
						while($stmt->fetch()) {
							echo "<h4>" .$tutorial["name"] . "</h4>";
							
							Users::display_state($tutorial["id"]);
						}
						
						$stmt->free_result();
						$stmt->close();
					}
					else {
						Users::display_state(); 
					}
					
				?>
			</div>
		</div>	
	</body>
</html>

<?php db_close(); ?>