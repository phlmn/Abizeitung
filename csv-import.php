<?php
	session_start();
	
	require_once("functions.php");
	
	db_connect();
	check_login();
	check_admin(); 

	$data = UserManager::get_userdata($_SESSION["user"]);
	
	if(isset($_GET["import"])) {
		if(!file_exists("csv"))
			mkdir("csv");
			
		if(isset($_FILES["file"]["name"])) {
			if($_FILES["file"]["type"] == "application/vnd.ms-excel") {
				$file = realpath(dirname(__FILE__)) . "/csv/" . time() . "_" . $_FILES["file"]["name"];
				
				if(move_uploaded_file($_FILES["file"]["tmp_name"], $file)) {
					
					if(($handle = fopen($file, "r")) !== false) {
						
						while(($data = fgetcsv($handle, 999, ";")) !== false) {
							
							// 	Required file format:
							//
							// 	prename, lastname, is female, tutorial, tutor
							//
							// 	check tutorial names
							
							if(count($data) == 5) {
								$user["unlock_code"] = get_unlock_code();
								
								global $mysqli;
								
								// Insert user
								
								$stmt = $mysqli->prepare("
									INSERT INTO users (
										prename, lastname, female, activated, unlock_key
									) VALUES (
										?, ?, ?, 0, ?
									)
								");
								
								$stmt->bind_param("ssis", null_on_empty($data[0]), null_on_empty($data[1]), intval($data[2]), $user["unlock_code"]);
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
								
								$stmt = $mysqli->prepare("
									SELECT id
									FROM tutorials
									WHERE name = ?
									LIMIT 1
								");
								
								$stmt->bind_param("s", null_on_empty($data[3]));
								$stmt->execute();
								
								$stmt->bind_result($user["tutorial"]["id"]);
								$res = $stmt->fetch();
								
								$stmt->close();
								
								if(!$res) {
									die;
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
						}
						
						fclose($handle);
					}
				}
				
				unlink($file);
			}
		}
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
		<div id="user-management" class="container">
			<h1>CSV Nutzer Import</h1>
            
            <form method="post" name="data" action="csv-import.php?import" enctype="multipart/form-data">
            <div class="users box">
            	<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo return_ini_bytes(ini_get("upload_max_filesize")); ?>" />
                <input id="file" name="file" type="file" />
                <button type="submit">Importieren</button>
            </div>
            </form>
		</div>	
	</body>
</html>

<?php db_close(); ?>