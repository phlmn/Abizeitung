<?php
	session_start();
	
	require_once("functions.php");
	
	db_connect();
	check_login();
	check_admin(); 

	$data = UserManager::get_userdata($_SESSION["user"]);
	
	$errors = array();
	
	if(isset($_GET["import"])) {
		if(!file_exists("csv"))
			mkdir("csv");
			
		// check filetype
			
		if(isset($_FILES["file"]["name"])) {
			if(!empty($_FILES["file"]["name"])) {
				if($_FILES["file"]["type"] == "application/vnd.ms-excel" || $_FILES["file"]["type"] == "text/csv") {
					
					// create filepath
					
					$file = realpath(dirname(__FILE__)) . "/csv/" . time() . "_" . $_FILES["file"]["name"];
					
					// upload file in /csv/
					
					if(move_uploaded_file($_FILES["file"]["tmp_name"], $file)) {
						
						// open file
						
						if(($handle = fopen($file, "r")) !== false) {
							
							// get columns order
							
							$cols = array();
							$seperated = array(
								"tutorial" => NULL,
								"tutor" => NULL
							);
								
							if(isset($_GET["columns"])) {
								for($i = 0; $i < $_GET["columns"]; $i++) {
									$field = $_POST["column-field-" . ($i + 1)];
									
									if(!empty($field)) {
										if($field == "tutorial") {
											$seperated["tutorial"] = $i;
										} 
										else if($field == "tutor") {
											$seperated["tutor"] = $i;
										}
										else {
											array_push($cols, array(
												"name" => $mysqli->real_escape_string($field),
												"index" => $i
											));
										}
									}
								}
							} else {
								array_unshift($errors, "Formularfehler<br />Dieser Fehler muss in der Software gelöst werden");
								error_report("1", 'Fehler im Formularfeld. In dem action-link fehlt "&columns=integer"', "csv-import.php", NULL, $data["id"]);
							}
							
							// get content
							
							$columns_fail = false;
							$form_fail = false;
							
							while(($csv = fgetcsv($handle, 999, ";")) !== false) {
								
								$col = "";
								$val = "";
								
								foreach($cols as $c) {
									$col .= $c["name"] . ", ";
									$val .= "'" .$csv[$c["index"]] . "', ";
								}
								
								// 	Required columns:
								// 		prename, lastname, female
								
								$tutorial_fail = false;
								
								if(strpos($col, "prename") >= 0 && strpos($col, "lastname") >= 0 && strpos($col, "female") >= 0) {
									$user["unlock_code"] = get_unlock_code();
									$user["tutorial"]["id"] = NULL;
									
									global $mysqli;
									
									// Insert user
									
									$stmt = $mysqli->prepare("
										INSERT INTO users (
											" . $col . " activated, unlock_key
										) VALUES (
											" . $val . " '0', ?
										)
									");
									
									$stmt->bind_param("s", $user["unlock_code"]);
									
									$stmt->execute();
									
									$stmt->close();
									
									// Get id from user
									
									$stmt = $mysqli->prepare("
										SELECT id
										FROM users
										WHERE unlock_key = ?
										LIMIT 1
									");
									
									$stmt->bind_param("s", $user["unlock_code"]);
									$stmt->execute();
									
									$stmt->bind_result($user["id"]);
									$stmt->fetch();
									
									$stmt->close();
									
									// Search for tutorial
									
									if($seperated["tutorial"] != NULL) {
										$stmt = $mysqli->prepare("
											SELECT id
											FROM tutorials
											WHERE name = ?
											LIMIT 1
										");
										
										$stmt->bind_param("s", null_on_empty($csv[$seperated["tutorial"]]));
										$stmt->execute();
										
										$stmt->bind_result($user["tutorial"]["id"]);
										$res = $stmt->fetch();
										
										$stmt->close();
										
										if(!$res) {
											if(!$tutorial_fail) {
												$tutorial_fail = true;
												array_unshift($errors, "Fehler beim Eintragen des Tutoriums<br />Bitte überprüfen Sie, ob Sie die Tutorien eingetragen haben");
											}
										}
									}
									
									// Insert student
									
									$stmt = $mysqli->prepare("
										INSERT INTO students (
											uid, tutorial
										) VALUES (
											?, ?
										)
									");
									
									$stmt->bind_param("ii", $user["id"], $user["tutorial"]["id"]);
									$stmt->execute();
									
									$stmt->close();
									
								} else {
									if(!$columns_fail) {
										$columns_fail = true;
										array_unshift($errors, 
											"Es fehlen benötigte Spalten.<br />" . 
											"Überprpfen Sie, ob die Spalten <strong>prename</strong>, <strong>lastname</strong> und <strong>female</strong> gesetzt sind"
										);
									}
								}
							}
							
							// close file
							fclose($handle);
						}
					} else {
						array_unshift($errors, "Die Datei konnte nicht hochgeladen werden");
					}
					
					// delete file
					unlink($file);
				} else {
					array_unshift($errors, "Die Datei hat nicht das richtige Format.<br />Bitte wählen Sie eine <strong>*.csv</strong> Datei aus");
				}
			} else {
				array_unshift($errors, "Es wurde keine Datei ausgewählt");
			}
		}
		
		if(!count($errors)) {
			db_close();
			
			header("Location: ./csv-import.php?saved");
			
			die;
		}
	}
	
?>


<!DOCTYPE html>
<html>
	<head>
		<title>Abizeitung - CSV Import</title>
		<?php head(); ?>
        <script type="text/javascript">
			var columns = new Array(<?php
				$columns = array(
					"prename", 
					"lastname", 
					"female", 
					"tutorial", 
					"tutor"
				);
				$i = 0;
				foreach($columns as $column) {
					if($i++) echo ',';
					echo '"' . $column . '"';
				}
			?>);
			
			function reset_field(id) {
				$(id).val("");
				$(id).removeClass("alternate");
			}
			
			$(document).ready(function() {
				for(i = 1; i <= columns.length; i++) {
					div1 = $('<div class="item draggable col-md-2">' + columns[i - 1] + '</div>');
					div2 = $('<input id="column-field-' + i +'" name="column-field-' + i +'" type="text" class="column-field droppable col-md-2" placeholder="Reihe ' + i + '" onfocus="this.blur()" readonly />' + 
								'<label for="column-field-' + i + '" class="reset" onclick="reset_field(\'#column-field-' + i + '\')"><span class="icon-minus-circled"></span></lable>');
					
					$("#items").append(div1);
					$(div1).draggable({
						revert: true
					}).data("name", columns[i - 1]);
					
					$("#column-fields").append(div2);
				}
			});
			
			$(function() {
				$(".column-field").droppable({
					drop: function( event, ui ) {
						$(".column-field").each(function(i, e) {
							if($(this).val() == ui.draggable.data("name"))
								$(this).val("").removeClass("alternate");
						});
						$(this).val(ui.draggable.data("name"))
						$(this).addClass("alternate");
					}
				});
				
				$("form").bind("reset", function() {
					$(".droppable").each(function(index, e) {
						$(e).removeClass("alternate");
					});
				});
			});
		</script>
	</head>
	
	<body>
		<?php require("nav-bar.php") ?>
		<div id="csv-import" class="container">
        	<?php if(count($errors)) : ?>
            <div class="alert alert-danger">
            	<ul>
            	<?php foreach($errors as $error): ?>
					<li><?php echo $error ?></li>
				<?php endforeach; ?>
                </ul>
            </div>
            <?php else: if(isset($_GET["saved"])) : ?>
            <div class="alert alert-success">
            	Importieren erfolgreich abgeschlossen
            </div>
            <?php endif; endif; ?>
			<h1>CSV Nutzer Import</h1>
            
            <form method="post" name="data" action="csv-import.php?import&columns=<?php echo count($columns); ?>" enctype="multipart/form-data">
            <div class="users box">
            	<h4>Vorhandene Spalten</h4>
            	<div id="items" class="row">
                </div>
                <h4>Reihenfolge der .csv Spalten</h4>
                <div id="column-fields" class="row">
                </div>
            	<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo return_ini_bytes(ini_get("upload_max_filesize")); ?>" />
                <input id="file" name="file" type="file" />
                <button type="submit">Importieren</button>
                <button type="reset">Zurücksetzen</button>
            </div>
            </form>
		</div>	
	</body>
</html>

<?php db_close(); ?>