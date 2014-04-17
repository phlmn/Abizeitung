<?php 
	session_start();
	
	require_once("functions.php");
	
	db_connect();
	
	check_login();

	$data = UserManager::get_userdata($_SESSION["user"]);
	
	global $mysqli;
	
	if(isset($_GET["update"])) {
		$userdata["id"] = $data["id"];
		$userdata["nickname"] = $_POST["nickname"];
		$userdata["birthday"] = $_POST["birthday"];
		
		UserManager::update_userdata($userdata);
		
		$data = UserManager::get_userdata($_SESSION["user"]);
		
		$stmt = $mysqli->prepare("
			SELECT id
			FROM questions");
		
		$stmt->execute();
		$stmt->bind_result($q["id"]);
		$stmt->store_result();
		
		$fails = 0;
		
		while($stmt->fetch()) {
			if(isset($_POST["question_" . $q["id"]])) {
				if(Dashboard::update_user_questions($data["id"], $q["id"], $mysqli->real_escape_string($_POST["question_" . $q["id"]])))
					$fails++;
			}
		}
		
		$stmt->free_result();
		$stmt->close();
		
		$stmt = $mysqli->prepare("
			SELECT id
			FROM surveys");
			
		$stmt->execute();
		$stmt->bind_result($s["id"]);
		$stmt->store_result();
		
		while($stmt->fetch()) {
			$answer = array(
				"female" => 0,
				"male" => 0
			);
				
			if(isset($_POST["survey_w_" . $s["id"]]))
				$answer["female"] = intval($_POST["survey_w_" . $s["id"]]);
				
			if(isset($_POST["survey_m_" . $s["id"]]))
				$answer["male"]   = intval($_POST["survey_m_" . $s["id"]]);
			
			if(Dashboard::update_user_surveys($data["id"], $s["id"], $answer))
				$fails++;
		}
		
		$stmt->free_result();
		$stmt->close();
		
		if(!$fails)
			header("Location: ./dashboard.php?saved");
		else
			header("Location: ./dashboard.php?failed=" . $fails);
		exit;
	}

	$students = array();
	
	$stmt = $mysqli->prepare("
		SELECT users.id as id, prename, lastname, female
		FROM users 
		LEFT JOIN users_classes ON users.id = users_classes.user
		LEFT JOIN classes ON users_classes.id = classes.id
		ORDER BY users.prename");
	
	$stmt->execute();
	$stmt->bind_result($row["id"], $row["prename"], $row["lastname"], $row["female"]);
						
	while($stmt->fetch()) {				
		array_push($students, array("id" => $row["id"], "prename" => $row["prename"], "lastname" => $row["lastname"], "gender" => $row["female"] ? "w" : "m"));
	}
	
	$stmt->close();
	
	$questions = array();
	
	$stmt = $mysqli->prepare("
		SELECT id, title
		FROM questions");
		
	$stmt->execute();
	$stmt->bind_result($row["id"], $row["title"]);
	
	while($stmt->fetch()) {				
		$questions[intval($row['id'])] = array("title" => $row["title"]);
	}
	
	$stmt->close();
	
	$surveys = array();
	
	$stmt = $mysqli->prepare("
		SELECT id, title, m, w 
		FROM surveys");
		
	$stmt->execute();
	$stmt->bind_result($row["id"], $row["title"], $row["m"], $row["w"]);
	
	while($stmt->fetch()) {				
		$surveys[intval($row['id'])] = array("title" => $row["title"], "m" => ($row["m"] == '1'), "w" => ($row["w"] == '1'));
	}
	
	$stmt->close();
	
	$survey_answers = array();
	
	$stmt = $mysqli->prepare("
		SELECT survey, m, w
		FROM user_surveys
		WHERE user = ?
	");
	
	$stmt->bind_param("i", $data["id"]);
	$stmt->execute();
	$stmt->bind_result($row["survey"], $row["m"], $row["w"]);
	
	while($stmt->fetch()) {		
		$survey_answers[intval($row['survey'])] = array("m" => $row["m"], "w" => $row["w"]);
	}
	
	$stmt->close();
	
	/*$survey_answers = array(
		0 => array(
			"m" => 1,
			"w" => 3
		)
	);*/
	
?>


<!DOCTYPE html>
<html>
	<head>
		<title>Abizeitung - Dashboard</title>
		<?php head(); ?>
        <script src="js/script.js" type="text/javascript"></script>
	</head>
	
	<body>
		<?php require("nav-bar.php") ?>
		<div id="dashboard" class="container">
			<?php if(isset($_GET["saved"])): ?>
				<div class="alert alert-success">Änderungen gespeichert.</div>
            <?php else: if(isset($_GET["failed"])): ?>
                <div class="alert alert-danger">
                	Speichern fehlgeschlagen.<br />
                    <?php if($_GET["failed"] == 1): ?>
						1 Anfrage konnte nicht gespeichert werden.
                    <?php else: if($_GET["failed"] > 1): ?>
                    	<?php echo $_GET["failed"] ?> Anfragen konnten nicht gespeichert werden.
                    <?php endif; endif; ?>
                </div>
            <?php endif; endif; ?>
			<div class="intro">
				<h1>Hallo <?php echo $data["prename"] ?>!</h1>
				<p class="intro">Hier kannst du deine Daten für die Abizeitung angeben bzw. ergänzen. Die Daten werden für deinen Steckbrief verwendet. Die Ergebnisse der Umfragen kommen auch in die Abizeitung, auf Wunsch wird dein Name geschwärzt.</p>
				<p class="intro">Bitte achte auf Rechtschreibung und <b>vergiss das Speichern nicht</b> ;)</p>
			</div>
			<form id="data_form" name="data" action="dashboard.php?update" method="post"></form>
			<div class="common box">
				<h2>Allgemeines</h2>
				<table>
					<tr>
						<td class="title">Vorname</td>
						<td><?php echo $data["prename"] ?></td>
					</tr>
					<tr>
						<td class="title">Nachname</td>
						<td><?php echo $data["lastname"] ?></td>
					</tr>
					<tr>
						<td class="title">Spitzname</td>
						<td><input name="nickname" type="text" form="data_form" value="<?php echo $data["nickname"] ?>" /></td>
					</tr>
					<tr>
						<td class="title">Geburtsdatum</td>
						<td><input name="birthday" type="text" form="data_form" value="<?php echo $data["birthday"] ?>" /></td>
					</tr>
					<tr>
						<td class="title">Geschlecht</td>
						<td><?php echo $data["female"] ? "Weiblich" : "Männlich" ?></td>
					</tr>
					
					<tr>
						<td class="title">Tutorium</td>
						<td><?php if(isset($data["class"]["name"])) echo $data["class"]["name"] ?></td>
					</tr>
					<tr>
						<td class="title">Tutor</td>
						<td><?php if(isset($data["class"]["tutor"]["lastname"])) echo $data["class"]["tutor"]["lastname"] ?></td>
					</tr>
				</table>
				
				<div id="photo-enrollment" class="photo">
					<form action="upload.php" id="image-form-enrollment" enctype="multipart/form-data" ></form>
                    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo return_ini_bytes(ini_get('upload_max_filesize')); ?>" />
					<input id="photo-upload-enrollment" class="photo-upload" name="photo" type="file" form="image-form-enrollment" accept="image/x-png,image/jpeg" onchange="uploadImage(1, '#image-form-enrollment', '#photo-upload-state_enrollment', '#photo-enrollment')" />
					<div class="upload">
						<a href="javascript: openImageSelector('#photo-upload-enrollment')">
                        	<span id="photo-upload-state-enrollment">
								<span class="icon-upload"></span>
								<br>Einschulungsfoto
								<br>hochladen...
                            </span>
						</a>
					</div>
				</div>
                <div id="photo-current" class="photo photo-alternate">
                	<form action="upload.php" id="image-form-current" enctype="multipart/form-data" ></form>
                    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo return_ini_bytes(ini_get('upload_max_filesize')); ?>" />
					<input id="photo-upload-current" class="photo-upload" name="photo" type="file" form="image-form-current" accept="image/x-png,image/jpeg" onchange="uploadImage(2, '#image-form-current', '#photo-upload-state-current', '#photo-current')" />
                	<div class="upload">
                    	<a href="javascript: openImageSelector('#photo-upload-current')">
                        	<span id="photo-upload-state-current">
                            	<span id="photo-upload-state-current">
								<span class="icon-upload"></span>
								<br>Aktuelles Foto
								<br>hochladen...
                            </span>
                            </span>
                        </a>
                    </div>
                </div>
			</div>
			
			<div class="questions box">
				<h2>Fragen</h2>
				<table>
				<?php foreach($questions as $key => $question): 
					$text = "";
					
					$stmt = $mysqli->prepare("
						SELECT text 
						FROM user_questions
						WHERE user = ? AND question = ?");
						
					$stmt->bind_param("ii", intval($data["id"]), $key);
					$stmt->execute();
					
					
					$stmt->bind_result($text);
					$stmt->fetch();
					
					$stmt->close();					
				?>
					<tr>
						<td class="title"><?php echo $question["title"] ?></td>
						<td><textarea name="question_<?php echo $key ?>" form="data_form"><?php echo $text ?></textarea></td>
					</tr>
				<?php endforeach; ?>
				</table>
			
			</div>
			
			<div class="surveys box">
				<h2>Umfragen</h2>
				<table>
				<?php foreach($surveys as $key => $survey): ?>
					<tr>
						<td class="title"><?php echo $survey["title"] ?></td>
						<td>
						<?php if($survey["m"] === true):
							$answer = 0;
							if(isset($survey_answers[$key]))
								$answer = $survey_answers[$key]["m"];		
						?>
							<span class="icon-male" />  
							<select name="survey_m_<?php echo $key ?>" form="data_form">
								<option value=""<?php echo ($answer) ? "" : " selected" ?>>-</option>
								<?php foreach($students as $student) {
									if($student["gender"] == "m") {
										echo "<option";
										if($answer == $student["id"])
											echo " selected";
										echo " value=\"".$student["id"]."\">".$student["prename"]." ".$student["lastname"]."</option>";	
									}
								}
								?>
							</select>
						<?php endif; ?>
						</td>
						<td>
						<?php if($survey["w"] === true): 
							$answer = 0;
							if(isset($survey_answers[$key]))
								$answer = $survey_answers[$key]["w"];
						?>
							<span class="icon-female" /> 
							<select name="survey_w_<?php echo $key ?>" form="data_form">
								<option value="" <?php echo ($answer) ? "" : " selected" ?>>-</option>
								<?php foreach($students as $student) {
									if($student["gender"] == "w") {
										echo "<option";
										if($answer == $student["id"])
											echo " selected";
										echo " value=\"".$student["id"]."\">".$student["prename"]." ".$student["lastname"]."</option>";	
									}
								}
								?>
							</select>
						</td>
						<?php endif; ?>
					</tr>
				<?php endforeach; ?>
				</table>
			</div>
			
			<div class="buttons">
				<input type="submit" value="Speichern" form="data_form" />
				<input type="reset" value="Änderungen verwerfen" form="data_form" />
			</div>

		</div>	
	</body>
</html>

<?php db_close(); ?>