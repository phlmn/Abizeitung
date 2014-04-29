<?php
	session_start();
	
	require_once("functions.php");
	require_once("classes/cResults.php");
	
	db_connect();
	check_login();
	check_admin();

	$data = UserManager::get_userdata($_SESSION["user"]);
	
	if(isset($_GET["group"])) {
		$group = $_GET["group"];
	}
	else {
		$group = "students";
	}
	
?>


<!DOCTYPE html>
<html>
	<head>
		<title>Abizeitung - Auswertung</title>
		<?php head(); ?>
	</head>
	
	<body>
		<?php require("nav-bar.php") ?>
		<div id="results" class="container">
			<h1>Auswertung</h1>
            <div class="box">
            	<h2>Allgemein</h2>
                <div class="progress">
                <?php
				
					$gender = array(
						db_count("users", "female", "0"),
						db_count("users", "female", "1")
					);
					
					$gender = get_percent($gender);
					
				?>
                	<div class="progress-bar" style="width: <?php echo $gender["percent"][0] ?>%;">
                    	<?php echo $gender["percent"][0] ?> % Männlich
                    </div>
                    <div class="progress-bar progress-bar-success" style="width: <?php echo $gender["percent"][1] ?>%;">
                    	<?php echo $gender["percent"][1] ?>% Weiblich
                    </div>
                </div>
                <div class="progress">
                <?php
					global $mysqli;
					
					$tut = array(
						"id" => array(),
						"names" => array()
					);
					$tutorials = array();
					
					$stmt = $mysqli->prepare("
						SELECT id, name
						FROM tutorials
					");
					
					$stmt->execute();
					
					$stmt->bind_result($id, $name);
					
					while($stmt->fetch()) {
						array_push($tut["id"], $id);
						array_push($tut["names"], $name);
					}
					
					$stmt->close();
					
					foreach($tut["id"] as $id) {
						array_push($tutorials, db_count("students", "tutorial", $id));
					}
					
					$tutorials = get_percent($tutorials);
					
					for($i = 0; $i < count($tutorials["percent"]); $i++):
				?>
                	<div class="progress-bar" style="width: <?php echo $tutorials["percent"][$i]; ?>%;">
                    	<?php echo $tutorials["percent"][$i]; ?>% <?php echo $tut["names"][$i]; ?>
                    </div>
                <?php endfor; ?>
                </div>
            </div>
			<div class="box">
				<h2>Kategorien</h2>
                <ul class="nav nav-tabs">
                	
                </ul>
			</div>
		</div>	
	</body>
</html>

<?php db_close(); ?>