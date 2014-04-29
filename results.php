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
            	<h4>Geschlecht</h4>
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
                    <div class="progress-bar" style="width: <?php echo $gender["percent"][1] ?>%;">
                    	<?php echo $gender["percent"][1] ?>% Weiblich
                    </div>
                </div>
                <h4>Aufteilung Tutorien</h4>
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
					
					get_progressbar($tutorials["percent"], $tut["names"]);
					
				?>
                </div>
                <h4>Meisten Nicknames bekommen</h4>
                <div class="progress">
                <?php
					
					$students = array(
						"count" => array(),
						"name" => array(),
						"data" => array()
					);
					
					$stmt = $mysqli->prepare("
						SELECT COUNT(*) AS most, users.prename, users.lastname
						FROM nicknames
						INNER JOIN users ON nicknames.`to` = users.id
						GROUP BY `to`
						ORDER BY most DESC
						LIMIT 5
					");
					
					$stmt->execute();
					
					$stmt->bind_result($count, $prename, $lastname);
					
					while($stmt->fetch()) {
						array_push($students["count"], $count);
						array_push($students["name"], ($prename . " " . $lastname));
					}
					
					$stmt->close();
					
					$students["data"] = get_percent($students["count"]);
					
					get_progressbar($students["data"]["percent"], $students["data"]["absolute"], $students["name"]);
				?>
                </div>
                <h4>Meisten Nicknames vergeben</h4>
                <div class="progress">
                <?php
					
					$students = array(
						"count" => array(),
						"name" => array(),
						"data" => array()
					);
					
					$stmt = $mysqli->prepare("
						SELECT COUNT(*) AS most, users.prename, users.lastname
						FROM nicknames
						INNER JOIN users ON nicknames.`from` = users.id
						GROUP BY `from`
						ORDER BY most DESC
						LIMIT 5
					");
					
					$stmt->execute();
					
					$stmt->bind_result($count, $prename, $lastname);
					
					while($stmt->fetch()) {
						array_push($students["count"], $count);
						array_push($students["name"], ($prename . " " . $lastname));
					}
					
					$stmt->close();
					
					$students["data"] = get_percent($students["count"]);
					
					get_progressbar($students["data"]["percent"], $students["data"]["absolute"], $students["name"]);
				?>
                </div>
            </div>
			<div class="box">
				<h2>Umfragen</h2>
                <?php
					$surveys = array();
	
					$stmt = $mysqli->prepare("
						SELECT id, title, m, w
						FROM surveys
						WHERE accepted = 1
					");
					
					$stmt->execute();
					
					$stmt->bind_result($row["id"], $row["title"], $row["m"], $row["w"]);
					
					while($stmt->fetch()) {	
						array_push(
							$surveys, 
							array(
								"id" => $row["id"],
								"title" => $row["title"],
								"m" => $row["m"],
								"w" => $row["w"]
							)
						);	
					}
					
					$stmt->close();
					
					foreach($surveys as $survey):
					
						$res = array(
							"m" => array(
								"count" => array(),
								"id" 	=> array(),
								"name" 	=> array()
							),
							"w" => array(
								"count" => array(),
								"id" 	=> array(),
								"name" 	=> array()
							)
						);
						
						if($survey["m"]) {
					
							$stmt = $mysqli->prepare("
								SELECT COUNT(*) AS most, users.id, users.prename, users.lastname
								FROM users_surveys
								INNER JOIN users ON users_surveys.m = users.id
								WHERE users_surveys.survey = ?
								AND users_surveys.m IS NOT NULL
								GROUP BY users.id
								ORDER BY most DESC
								LIMIT 5;
							");
							echo $mysqli->error;
							$stmt->bind_param("i", $survey["id"]);
							$stmt->execute();
							
							$stmt->bind_result($count, $id, $prename, $lastname);
							
							while($stmt->fetch()) {
								array_push($res["m"]["count"], 	$count);
								array_push($res["m"]["id"], 	$id);
								array_push($res["m"]["name"], 	$prename . " " . $lastname);
							}
							
							$stmt->close();
						}
						
						if($survey["w"]) {
							
							$stmt = $mysqli->prepare("
								SELECT COUNT(*) AS most, users.id, users.prename, users.lastname
								FROM users_surveys
								INNER JOIN users ON users_surveys.w = users.id
								WHERE users_surveys.survey = ?
								AND users_surveys.w IS NOT NULL
								GROUP BY users.id
								ORDER BY most DESC
								LIMIT 5;
							");
							
							$stmt->bind_param("i", $survey["id"]);
							$stmt->execute();
							
							$stmt->bind_result($count, $id, $prename, $lastname);
							
							while($stmt->fetch()) {
								array_push($res["w"]["count"], 	$count);
								array_push($res["w"]["id"], 	$id);
								array_push($res["w"]["name"], 	$prename . " " . $lastname);
							}
							
							$stmt->close();
						}
				?>
                	<h4><?php echo $survey["title"] ?></h4>
                    <?php if($survey["m"]): ?>
                    <div class="progress">
                    <?php
						$percent = get_percent($res["m"]["count"]);
						
						get_progressbar($percent["percent"],$percent["absolute"], $res["m"]["name"]);
					?>
                    </div>
                    <?php 
						endif; 
						if($survey["w"]):
					?>
                    <div class="progress">
                    <?php
						$percent = get_percent($res["w"]["count"]);
						
						get_progressbar($percent["percent"],$percent["absolute"], $res["w"]["name"]);
					?>
                    </div>
                    <?php endif; ?>
                <?php
					endforeach;
				?>
			</div>
		</div>	
	</body>
</html>

<?php db_close(); ?>