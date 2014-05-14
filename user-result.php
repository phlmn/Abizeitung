<?php
	session_start();
	
	if(!isset($_GET["user"])) {
		header("Location: ./users.php?group=state");
		
		die;
	}
	
	require_once("functions.php");
	require_once("classes/cResults.php");
	
	db_connect();
	check_login();
	check_admin();

	$data = UserManager::get_userdata($_SESSION["user"]);
	
	$user = UserManager::get_userdata(intval($_GET["user"]));
	
	global $mysqli;
	
	// Alle von anderen Nutzer vergebenen Nicknames speichern
	
	$nicknames = array();
	
	$stmt = $mysqli->prepare("
		SELECT nicknames.id, nicknames.nickname, users.prename, users.lastname, nicknames.accepted
		FROM nicknames
		LEFT JOIN users ON nicknames.`from` = users.id
		WHERE 
			nicknames.`to` <> nicknames.`from`
		AND	nicknames.`to` = ?
	");
	
	$stmt->bind_param("i", $user["id"]);
	$stmt->execute();
	
	$stmt->bind_result($row["id"], $row["nickname"], $row["prename"], $row["lastname"], $row["accepted"]);
	
	while($stmt->fetch()) {
		$nicknames[intval($row["id"])] = array(
			"nickname" 	=> $row["nickname"],
			"from"		=> array(
				"prename" 	=> $row["prename"],
				"lastname" 	=> $row["lastname"]
			),
			"accepted" 	=> $row["accepted"]
		);
	}
	
	$stmt->close();
	
	// Alle Fragen speichern
	
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
	
	// Alle Umfragen speichern
	
	$survey_answers = array();
	
	$stmt = $mysqli->prepare("
		SELECT survey, m, w
		FROM users_surveys
		WHERE user = ?
	");
	
	$stmt->bind_param("i", $user["id"]);
	$stmt->execute();
	$stmt->bind_result($row["survey"], $row["m"], $row["w"]);
	
	while($stmt->fetch()) {		
		$survey_answers[intval($row['survey'])] = array("m" => $row["m"], "w" => $row["w"]);
	}
	
	$stmt->close();
	
?>


<!DOCTYPE html>
<html>
	<head>
		<title>Abizeitung - Auswertung</title>
		<?php head(); ?>
	</head>
	
	<body>
		<?php require("nav-bar.php") ?>
		<div class="admin-wrapper">
			<div id="user-result" class="container">
				<h1>Auswertung</h1>
	            <div class="common box">
	            	<h2>Allgemeines</h2>
					<div class="row">
						<div class="col-sm-4 data">
							<div class="row">
								<div class="col-xs-5 title">Vorname</div>
								<div class="col-xs-7"><?php echo $user["prename"] ?></div>
							</div>
							<div class="row">
								<div class="col-xs-5 title">Nachname</div>
								<div class="col-xs-7"><?php echo $user["lastname"] ?></div>
							</div>
	                        <div class="row">
								<div class="col-xs-5 title">Spitzname</div>
								<div class="col-xs-7"><?php echo $user["nickname"] ?></div>
							</div>
							<div class="row">
								<div class="col-xs-5 title">Geburtsdatum</div>
								<div class="col-xs-7"><?php echo $user["birthday"] ?></div>
							</div>
							<div class="row">
								<div class="col-xs-5 title">Geschlecht</div>
								<div class="col-xs-7"><?php echo $user["female"] ? "Weiblich" : "Männlich" ?></div>
							</div>
							<div class="row">
								<div class="col-xs-5 title">Tutorium</div>
								<div class="col-xs-7"><?php if(isset($user["tutorial"]["name"])) echo $user["tutorial"]["name"] ?></div>
							</div>
							<div class="row">
								<div class="col-xs-5 title">Tutor</div>
								<div class="col-xs-7"><?php if(isset($user["tutorial"]["tutor"]["lastname"])) echo $user["tutorial"]["tutor"]["lastname"] ?></div>
							</div>
						</div>
	                    
	                    <?php
						
						$stmt = $mysqli->prepare("
							SELECT file
							FROM images
							LEFT JOIN categories ON images.category = categories.id
							WHERE 
								images.uid = ? AND
								categories.name = 'enrollment'
							ORDER BY images.uploadtime DESC
							LIMIT 1
						");
						
						$stmt->bind_param("i", $user["id"]);
						$stmt->execute();
						
						$stmt->bind_result($enrollment);
						$stmt->fetch();
						
						$stmt->close();
						
						$stmt = $mysqli->prepare("
							SELECT file
							FROM images
							LEFT JOIN categories ON images.category = categories.id
							WHERE 
								images.uid = ? AND
								categories.name = 'current'
							ORDER BY images.uploadtime DESC
							LIMIT 1
						");
						
						$stmt->bind_param("i", $user["id"]);
						$stmt->execute();
						
						$stmt->bind_result($current);
						$stmt->fetch();
						
						$stmt->close();
						
						?>
						
						<div class="col-sm-4">
							<div id="photo-enrollment" class="photo" title="Einschulungsfoto" style="background-image: url('<?php echo $enrollment; ?>');">
	                        	<div class="upload">
	                            <?php if($enrollment): ?>
	                            	<a class="alternate" href="./<?php echo $enrollment; ?>" target="_blank">
	                                	<span class="icon-download"></span><br />
	                                    Bild Downloaden
	                                </a>
	                            <?php else: ?>
	                            	Es ist noch kein<br />
	                                Bild vorhanden.
	                            <?php endif;?>
			                    </div>
							</div>
						</div>
						<div class="col-sm-4">
			                <div id="photo-current" class="photo" title="Aktuelles Foto" style="background-image: url('<?php echo $current; ?>');">
			                	<div class="upload">
	                            <?php if($current): ?>
	                            	<a class="alternate" href="./<?php echo $current; ?>" target="_blank">
	                                	<span class="icon-download"></span><br />
	                                    Bild Downloaden
	                                </a>
	                            <?php else: ?>
	                            	Es ist noch kein<br />
	                                Bild vorhanden.
	                            <?php endif;?>
			                    </div>
			                </div>
						</div>
					</div>
	                <?php if(!$user["isteacher"]): ?>
	                <h2>Kurse</h2>
	                <div class="classes row">
	                	<table class="table table-striped">
	                    	<thead>
	                        	<th>Kurs</th>
	                            <th>Lehrer</th>
	                        </thead>
	                        <tbody>
	                        <?php 
								
								$stmt = $mysqli->prepare("
									SELECT classes.name, users.lastname
									FROM students_classes
									LEFT JOIN classes ON students_classes.class = classes.id
									LEFT JOIN teachers ON classes.teacher = teachers.id
									LEFT JOIN users ON teachers.uid = users.id
									LEFT JOIN students ON students_classes.student = students.id
									WHERE students.uid = ?
								");
								
								$stmt->bind_param("i", $user["id"]);
								$stmt->execute();
								
								$stmt->bind_result($name, $teacher);
								
								while($stmt->fetch()):
							?>
	                        	<tr>
	                                <td><?php echo $name ?></td>
	                                <td><?php echo $teacher ?></td>
	                            </tr>
	                        <?php 
								endwhile;
								
								$stmt->close();
							?>
	                        </tbody>
	                    </table>
	                </div>
				</div>
	            <?php endif; ?>
	            
	            <div class="nicknames box">
	            	<h2>Spitznamen</h2>
	                <div class="nickname-list row">
	                <?php if(empty($nicknames)) : ?>
	                	<?php echo $user["prename"]; ?> hat noch keinen Spitznamen bekommen.
	                <?php else: ?>
	                	<h4>Akzeptiert</h4>
	                	<table class="table table-striped">
	                        <thead>
	                            <th>Spitzname</th>
	                            <th>Vergeben von</th>
	                        </thead>
	                        <tbody>
	                	<?php foreach($nicknames as $key => $nickname): ?>
	                		<?php if($nickname["accepted"]): ?>	
	                			<tr>
	                            	<td><?php echo $nickname["nickname"] ?></td>
									<td><?php echo $nickname["from"]["prename"] . " " . $nickname["from"]["lastname"]; ?></td>
	                    		</tr>
	                    	<?php endif; ?>
	                	<?php endforeach; ?>
	                		</tbody>
	                 	</table>
	                 	<h4>Nicht akzeptiert</h4>
	                 	<table class="table table-striped">
	                        <thead>
	                            <th>Spitzname</th>
	                            <th>Vergeben von</th>
	                        </thead>
	                        <tbody>
	                	<?php foreach($nicknames as $key => $nickname): ?>
	                		<?php if(!$nickname["accepted"]): ?>
	                			<tr>
	                            	<td><?php echo $nickname["nickname"] ?></td>
									<td><?php echo $nickname["from"]["prename"] . " " . $nickname["from"]["lastname"]; ?></td>
	                    		</tr>
	                    	<?php endif; ?>
	                	<?php endforeach; ?>
	                		</tbody>
	                 	</table>
	                <?php endif; ?>
	                </div>
	            </div>
	            
	            <div class="questions box">
					<h2>Fragen</h2>
					<div class="question-list row">
					<?php foreach($questions as $key => $question): 
						$text = "";
						
						$stmt = $mysqli->prepare("
							SELECT text 
							FROM users_questions
							WHERE user = ? AND question = ?");
							
						$stmt->bind_param("ii", intval($user["id"]), $key);
						$stmt->execute();
						
						
						$stmt->bind_result($text);
						$stmt->fetch();
						
						$stmt->close();					
					?>
						<div class="col-sm-6 question">
							<div class="title"><?php echo $question["title"] ?></div>
							<div class="text"><?php echo $text ?></div>
						</div>
					<?php endforeach; ?>
	                </div>
				</div>
	            
	            <div class="surveys box">
					<h2>Umfragen</h2>
					<div class="survey-list">
					<?php foreach($surveys as $key => $survey): ?>
						<div class="row">
							<div class="col-xs-12 col-sm-4 title"><?php echo $survey["title"] ?></div>
							<div class="col-xs-12 col-sm-4">
							<?php 
								if($survey["m"] === true) {
									if(isset($survey_answers[$key])) {
										$student = UserManager::get_userdata($survey_answers[$key]["m"]);
										
										echo $student["prename"] . " " . $student["lastname"];
									}
								}
							?>
							</div>
							<div class="col-xs-12 col-sm-4">
	                        <?php
								if($survey["w"] === true) {
									if(isset($survey_answers[$key])) {
										$student = UserManager::get_userdata($survey_answers[$key]["w"]);
										
										echo $student["prename"] . " " . $student["lastname"];
									}
								}
							?>
	                        </div>
						</div>
					<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>	
	</body>
</html>

<?php db_close(); ?>