<?php 
	session_start();
	
	require_once("functions.php");
	
	db_connect();
	
	check_login();

	$data = UserManager::get_userdata($_SESSION["user"]);
	
	if(isset($_GET["update"])) {
		$userdata["id"] = $data["id"];
		$userdata["nickname"] = $_POST["nickname"];
		$userdata["birthday"] = $_POST["birthday"];
		
		UserManager::update_userdata($userdata);
		
		$data = UserManager::get_userdata($_SESSION["user"]);
		
		header("Location: ./dashboard.php");
		exit;
	}
		

	$students = array();
	
	$res = $mysqli->query("
		SELECT users.id as id, prename, lastname, female
		FROM users 
		LEFT JOIN users_classes ON users.id = users_classes.user
		LEFT JOIN classes ON users_classes.id = classes.id
		ORDER BY users.prename
	");
						
	while($row = $res->fetch_assoc()) {				
		array_push($students, array("id" => $row["id"], "prename" => $row["prename"], "lastname" => $row["lastname"], "gender" => $row["female"] ? "w" : "m"));
	}
	
	
	
	$res = $mysqli->query("SELECT * FROM questions");

	$questions = array();
	
	while($row = $res->fetch_assoc()) {				
		$questions[intval($row['id'])] = array("title" => $row["title"]);
	}
	
	
	$res = $mysqli->query("SELECT * FROM surveys");
	
	$surveys = array();
	
	while($row = $res->fetch_assoc()) {				
		$surveys[intval($row['id'])] = array("title" => $row["title"], "m" => ($row["m"] == '1'), "w" => ($row["w"] == '1'));
	}
	
	$res = $mysqli->query("SELECT * FROM user_surveys WHERE user = 1" );
	
	$survey_answers = array();
	
	while($row = $res->fetch_assoc()) {				
		$survey_answers[intval($row['survey'])] = array("m" => $row["m"], "w" => $row["w"]);
	}
	
	
	/*$survey_answers = array(
		0 => array(
			"m" => 1,
			"w" => 3
		)
	);*/
	
	$question_answers = array(
		0 => "Halli Hallo"
	);
	
?>


<!DOCTYPE html>
<html>
	<head>
		<title>Abizeitung - Dashboard</title>
		<?php head(); ?>
		
		<script type="text/javascript">
			function openImageSelector() {
				$("#photo-upload").click();
			}
			
			function uploadImage() {
				var formData = new FormData($('#image_form')[0]);
			    $.ajax({
			        url: 'upload.php',
			        type: 'POST',
			        xhr: function() {
			            var myXhr = $.ajaxSettings.xhr();
			            if(myXhr.upload){
			                myXhr.upload.addEventListener('progress',function(e) {
			                	if(e.lengthComputable) {
			                	
			                	}	
			                }, false);
			            }
			            return myXhr;
			        },
			        beforeSend: function() {
			        	$("span#photo-upload-state").html("<br>Bild wird<br/>hochgeladen ...");
			        },
			        success: function(data) {
						error1 = parseInt(data[data.length-1]);
						error0 = "";
						
						if(data.indexOf("Length") != -1) {
							error0 = error1;
							error1 = 3;
						}
						
						switch(error1) {
							case 1:
							case 4:
								$("span#photo-upload-state").html(
									'<span class="icon-cancel-circled"></span><br />' +
									'Fehler beim Hochladen:<br />' +
									'<em>Fehlercode 0x' + error1 + error0 + '</em>'
								);
								break;
							case 2:
								$("span#photo-upload-state").html(
									'<span class="icon-cancel-circled"></span><br />' +
									'Fehler beim Hochladen:<br />' +
									'Ungültiges Dateiformat<br />' +
									'Erlaubte Formate: .jpg, .png<br />' +
									'<em>Fehlercode 0x' + error1 + error0 + '</em>'
								);
								break;
							case 3:
								$("span#photo-upload-state").html(
									'<span class="icon-cancel-circled"></span><br />' +
									'Fehler beim Hochladen:<br />' +
									'Die Datei ist zu groß<br />' +
									'<em>Fehlercode 0x' + error1 + error0 + '</em>'
								);
								break;
							default:
								$("span#photo-upload-state").html('<span class="icon-ok-circled"></span><br />Hochladen erfolgreich');			        	
								$("div.photo").css("background-image", "url('" + data + "')");
						}
			        },
			        error: function(a,b) {
			        	alert(b);
			        },
			        data: formData,
			        cache: false,
			        contentType: false,
			        processData: false
			    });
			}
		</script>
	</head>
	
	<body>
		<?php require("nav-bar.php") ?>
		<div id="dashboard" class="container">
			<h1>Hallo <?php echo $data["prename"] ?>!</h1>
			<p>Hier kannst du deine Daten für die Abizeitung angeben bzw. ergänzen. Die Daten werden für deinen Steckbrief verwendet. Die Ergebnisse der Umfragen kommen auch in die Abizeitung, auf Wunsch werden eure Namen geschwärzt.</p>
			<p>Bitte achte auf Rechtschreibung und <b>Speichern nicht vergessen</b> ;)</p>
			<form id="data_form" name="data" action="dashboard.php?update" method="post"></form>
			<div class="common">
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
						<td><?php echo $data["class"]["name"] ?></td>
					</tr>
					<tr>
						<td class="title">Tutor</td>
						<td><?php echo $data["class"]["tutor"]["lastname"] ?></td>
					</tr>
				</table>
				
				<div class="photo">
					<form action="upload.php" id="image_form" enctype="multipart/form-data" ></form>
                    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo return_ini_bytes(ini_get('upload_max_filesize')); ?>" />
					<input id="photo-upload" name="photo" type="file" form="image_form" accept="image/x-png,image/jpeg" onchange="uploadImage()" />
					<div class="upload">
						<a href="javascript: openImageSelector()">
                        	<span id="photo-upload-state">
								<span class="icon-upload"></span>
								<br>Einschulungsfoto
								<br>hochladen...
                            </span>
						</a>
					</div>
				</div>
			</div>
			
			<div class="questions">
				<h2>Fragen</h2>
				<table>
				<?php foreach($questions as $key => $question): ?>
					<tr>
						<td class="title"><?php echo $question["title"] ?></td>
						<td><textarea name="question_<?php echo $key ?>" form="data_form"><?php echo $question_answers[$key] ?></textarea></td>
					</tr>
				<?php endforeach; ?>
				</table>
			
			</div>
			
			<div class="surveys">
				<h2>Umfragen</h2>
				<table>
				<?php foreach($surveys as $key => $survey): ?>
					<tr>
						<td class="title"><?php echo $survey["title"] ?></td>
						<td>
						<?php if($survey["m"] === true): ?>
							<span class="icon-male" />  
							<select name="survey_m_<?php echo $key ?>" form="data_form">
								<option value=""<?php echo ($survey_answers[$key]["m"] == null) ? "" : " selected" ?>>-</option>
								<?php foreach($students as $student) {
									if($student["gender"] == "m") {
										echo "<option";
										if($survey_answers[$key]["m"] == $student["id"])
											echo " selected";
										echo " value=\"".$student["id"]."\">".$student["prename"]." ".$student["lastname"]."</option>";	
									}
								}
								?>
							</select>
						<?php endif; ?>
						</td>
						<td>
						<?php if($survey["w"] === true): ?>
							<span class="icon-female" /> 
							<select name="survey_w_<?php echo $key ?>" form="data_form">
								<option value="" <?php echo ($survey_answers[$key]["w"] == null) ? "" : " selected" ?>>-</option>
								<?php foreach($students as $student) {
									if($student["gender"] == "w") {
										echo "<option";
										if($survey_answers[$key]["w"] == $student["id"])
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