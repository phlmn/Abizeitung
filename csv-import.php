<?php
	session_start();
	
	require_once("functions.php");
	
	db_connect();
	check_login();
	check_admin(); 

	$data = UserManager::get_userdata($_SESSION["user"]);
	
	$columns = array(
		"prename", 
		"lastname", 
		"female", 
		"tutorial"
	);
	
	if(isset($_GET["import"])) {
		
		if(file_exists($_POST["file"])) {
			
			$file = $_POST["file"];
			
			if(($handle = fopen($file, "r")) !== false) {
				
				// get columns order
				
				$cols = array();
				$seperated = array(
					"tutorial" => NULL,
					"tutor" => NULL
				);
					
				if(isset($_GET["columns"])) {
					
					for($i = 0; $i < intval($_GET["columns"]); $i++) {
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
					
					error_report("1", 'Fehler im Formularfeld. In dem action-link fehlt "&columns=(integer)"', "csv-import.php", NULL, $data["id"]);
					
					db_close();
					
					header("Location: ./csv-import.php?error=form");
					
					die;
				}
				
				// get content
				
				$columns_fail = false;
				$form_fail = false;
				
				$headline = false;
				
				if(isset($_GET["caption"]))
					$headline = true;
				
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
						if($headline) {
							$headline = false;
						}
						else {
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
										
										$errorHandler->add_error("cannot-add-tutorial");
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
							
						}	
					} else {
						if(!$columns_fail) {
							$columns_fail = true;
							
							$errorHandler->add_error("required-columns");
						}
					}
				}
				
				// close file
				fclose($handle);
			}
		}
		else {
			$errorHandler->add_error("file-access");
		}
		
		// delete file
		if(isset($_GET["delete_file"])) {
			if($_GET["delete_file"] == 1) {
				if(!unlink($file))
					$errorHandler->add_error("cannot-delete-file");
			}
		}
		
		db_close();
		
		if($errorHandler->is_error())	{
			header("Location: ./csv-import.php?error" . $errorHandler->export_url_param());
		}
		else {
			header("Location: ./csv-import.php?saved");
		}
		
		die;
	}
	
?>


<!DOCTYPE html>
<html>
	<head>
		<title>Abizeitung - CSV Import</title>
		<?php head(); ?>
	</head>
	
	<body>
		<?php require("nav-bar.php") ?>
<<<<<<< HEAD
		<div id="csv-import" class="container">
        	<?php 
				if(isset($_GET["error"])): 
					
					$errorHandler->import_url_param($_GET);
			?>
=======
		<div id="csv-import" class="container-fluid admin-wrapper">
        	<?php if(isset($_GET["error"])): ?>
>>>>>>> 7240935f4ed51d8f81c860a9ea15c12b6edcfb1f
            <div class="alert alert-danger">
            	<ul>
					<?php $errorHandler->get_errors("li", 3); ?>
                </ul>
            </div>
            <?php else: if(isset($_GET["saved"])) : ?>
            <div class="alert alert-success">
            	Importieren erfolgreich abgeschlossen
            </div>
            <?php endif; endif; ?>
			<h1>CSV Nutzer Import</h1>
            
            <?php
			
            if(isset($_GET["upload"])) {
				if(!file_exists("csv"))
					mkdir("csv");
				
				if(isset($_FILES["file"]["name"])) {
					if(!empty($_FILES["file"]["name"])) {
						if($_FILES["file"]["type"] == "application/vnd.ms-excel" || $_FILES["file"]["type"] == "text/csv") {
							
							// create filepath
							
							$file = realpath(dirname(__FILE__)) . "/csv/" . time() . "_" . $_FILES["file"]["name"];
							
							// upload file in /csv/
							
							if(move_uploaded_file($_FILES["file"]["tmp_name"], $file)) {
								
								$first = array();
									
								$count_column = 0;
								
								if(($handle = fopen($file, "r")) !== false) {
									
									if(($first = fgetcsv($handle, 999, ";")) !== false) {
										foreach($first as $f) {
											$count_column++;
										}
									}
									
									fclose($handle);
								}
								
								if($count_column >= 3) {
								
									if(($handle = fopen($file, "r")) !== false) {
			?>
            <script type="text/javascript">
				var columns = new Array(<?php
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
						
						$("#items").append(div1);
						$(div1).draggable({
							revert: true
						}).data("name", columns[i - 1]);
					}
					
					for(i = 1; i <= <?php echo $count_column; ?>; i++) {
						div2 = 	$('<td><input id="column-field-' + i +'" name="column-field-' + i +'" type="text" class="column-field droppable col-md-2" placeholder="Reihe ' + i + 
									'" onfocus="this.blur()" readonly />' + '<label for="column-field-' + i + 
									'" class="reset" onclick="reset_field(\'#column-field-' + i + '\')"><span class="icon-minus-circled"></span></lable></td>'
								);
						
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
            <?php
				$param = "&columns=" . $count_column;
				
				if(isset($_POST["caption"]))
					$param .= "&caption=" . $_POST["caption"];
				
				if(isset($_POST["delete_file"]))
					$param .= "&delete_file=" . $_POST["delete_file"];
				
			?>
            <form method="post" name="upload" action="csv-import.php?import<?php echo $param; ?>">
            <div class="users box">
            	<input type="hidden" name="file" value="<?php echo $file ?>">
            	<h4>Datenbankspalten:</h4>
                <div id="items" class="row">
                </div>
                <h4>Die <em>*.csv</em> - Datei hat <?php echo $count_column; ?> Spalten:</h4>
                <div class="option">Bitte ordnen Sie der <em>*.csv</em> - Datei die entsprechenden Datenbankspalten via <span style="cursor: help" title="ziehen und loslassen">"drag'n'drop"</span> zu</div>
                <h4><em>*.csv</em> - Tabelle:</h4>
                <table class="table table-striped">
                <?php if(isset($_POST["caption"])): ?>
                	<thead>
                    <?php for($i = 0; $i < $count_column; $i++): ?>
                    	<th><?php echo $first[$i]; ?></th>
                    <?php endfor; ?>
                    </thead>
                <?php endif; ?>
                    <tbody>
                    	<tr id="column-fields">
                        </tr>
					<?php 
						$first_continue = false;
						if(isset($_POST["caption"]))
							$first_continue = true;
						
						while(($csv = fgetcsv($handle, 999, ";")) !== false):
							if($first_continue) {
								$first_continue = false;
							} else {
					?>
            			<tr>
            			<?php foreach($csv as $c) : ?>
            				<td><?php echo $c ?></td>
           				<?php endforeach; ?>
                        </tr>
            		<?php } endwhile ?>
            		</tbody>
            	</table>
            	<button type="submit">Importieren</button>
                <button type="reset">Zurücksetzen</button>
            </div>
            </form>
            <?php
										fclose($handle);
									}
								}
								else {
									$errorHandler->add_error("too-little-columns");
									
									if(isset($_POST["delete_file"])) {
										if($_POST["delete_file"] == 1)
											unlink($file);
									}
								}
							}
							else {
								$errorHandler->add_error("cannot-upload");
							}
						}
						else {
							$errorHandler->add_error("format-csv");
							
							
						}
					}
				}
				else {
					$errorHandler->add_error("no-selected-file");
				}
				
				if($errorHandler->is_error()) {
					db_close();
					
					header("Location: ./csv-import.php?error" . $errorHandler->export_url_param(true));
					
					die;
				}
			}
			else {
    		?>
            <form method="post" name="data" action="csv-import.php?upload" enctype="multipart/form-data">
            <div class="users box">
            	<h4><em>*.csv</em> - Datei auswählen:</h4>
                <div class="upload">
                    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo return_ini_bytes(ini_get("upload_max_filesize")); ?>" />
                    <input id="file" name="file" type="file" />
                </div>
                <div class="option">
                    <input id="delete_file" name="delete_file" type="checkbox" value="1" checked>
                    <label for="delete_file">Datei nach Bearbeitung löschen</label>
                </div>
                <div class="option">
                    <input id="caption" name="caption" type="checkbox" value="1" />
                    <label for="caption">Erste Reihe ist Überschrift</label>
                </div>
                <button type="submit">Hochladen</button>
            </div>
            </form>
            <?php } ?>
		</div>	
	</body>
</html>

<?php db_close(); ?>