<?php
	session_start();
	
	require_once("functions.php");
	
	db_connect();
	check_login();
	check_admin(); 

	$data = UserManager::get_userdata($_SESSION["user"]);	
	
	if(isset($_GET["delete"])) {
		global $mysqli;
		
		$stmt = $mysqli->prepare("
			UPDATE users SET class = '0' WHERE id = ?;
		");
		
		$stmt->bind_param("i", intval($_GET["user"]));
		$stmt->execute();
		
		$stmt->close();
	}
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Abizeitung - Kursverwaltung</title>
		<?php head(); ?>
	</head>
	
	<body>
		<?php require("nav-bar.php") ?>
		<div id="class-management" class="container">
			<h1>Kursverwaltung</h1>
            <?php 
				global $mysqli;
				
				$stmt = $mysqli->prepare("
					SELECT name 
					FROM classes 
					WHERE id = '" . intval($_GET["class"]) . "';");
				
				$stmt->execute();
				$stmt->bind_result($classname);
				
				$stmt->fetch();
			?>
            <h2>Kurs: <?php echo $classname ?></h2>
            
            <table class="table table-striped">
            	<thead>
                	<th>Vorname</th>
                    <th>Nachname</th>
                    <th class="edit"></th>
                </thead>
                <tbody>
                <?php
					$stmt->close();
					
					$stmt = $mysqli->prepare("
						SELECT id, prename, lastname 
						FROM users
						WHERE class = ?");
					
					if($stmt) {
						$stmt->bind_param("i", intval($_GET["class"]));
						$stmt->execute();
						$stmt->bind_result($class["id"], $class["prename"], $class["lastname"]);
				?>
                <?php
						while($stmt->fetch()):
				?>
                	<th><?php echo $class["prename"] ?></th>
                    <th><?php echo $class["lastname"] ?></th>
                    <th class="edit">
                    	<a href="edit-class.php?class=<?php echo intval($_GET["class"]) ?>&user=<?php echo $class["id"] ?>&delete" title="User aus Kurs entfernen">
                        	<span class="icon-cancel-circled"></span>
                        </a>
                    </th>
                <?php
						endwhile;
					}
				?>
                </tbody>
            </table>
        </div>
	</body>
</html>

<?php db_close(); ?>