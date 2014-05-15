<?php
	session_start();
	
	require_once("functions.php");
	require_once("classes/cOptions.php");
	
	db_connect();
	check_login();
	check_admin();

	$data = UserManager::get_userdata($_SESSION["user"]);
	
	if(isset($_GET["edit"])) {
		switch($_GET["edit"]) {
			case "images":
				Options::edit_images($_GET["id"]);
				break;
			default:
		}
		
		db_close();
		
		die;
	}
	
	if(isset($_GET["affected"])) {
		$action = NULL;
		$param = NULL;
		
		if(isset($_GET["action"])) {
			$action = $_GET["action"];
		}
		
		switch($_GET["affected"]) {
			case "images":
				
				$param = "&group=images";
				
				if(empty($_GET["id"])) {
					$errorHandler->add_error("empty-input");
				}
				else {
					$data["id"] = $_GET["id"];
					
					if($action == "delete") {
						$errorHandler->add_error(Options::delete_images($data));
					}
					else {
						if(empty($_POST["name"])) {
							$errorHandler->add_error("empty-input");
						}
						else {
							$data["name"] = $_POST["name"];
							
							$errorHandler->add_error(Options::update_images($data));
						}
					}
				}
				
				break;
				
			case "options":
				
				$param = "&group=options";
				
				if(empty($_POST["nicknames"]) && empty($_POST["classes"]) && empty($_POST["questions"]) && empty($_POST["surveys"])) {
					$errorHandler->add_error("empty-input");	
				}
				else {
					$data["nicknames"] 	= $_POST["nicknames"];
					$data["classes"] 	= $_POST["classes"];
					$data["questions"] 	= $_POST["questions"];
					$data["surveys"] 	= $_POST["surveys"];
					
					$errorHandler->add_error(Options::update_options($data));
				}
				
				break;
				
			case "files":
				
				$param = "&group=csv";
				
				$data["files"] = array();
				
				if(isset($_POST["file_count"])) {
					if($_POST["file_count"] > 0) {
						
						for($i = 1; $i <= $_POST["file_count"]; $i++) {
							if(isset($_POST["file_" . $i]) && isset($_POST["file_name_" . $i])) {
								array_push($data["files"], $_POST["file_name_" . $i]);
							}
						}
						
						$errorHandler->add_error(Options::delete_csv($data));
					}
					else {
						$errorHandler->add_error("empty-input");
					}
				}
				else {
					$errorHandler->add_error("empty-input");
				}
				
				break;
		}
		
		db_close();
			
		if($errorHandler->is_error()) {
			header("Location: ./options.php?error" . $errorHandler->export_url_param(true) . $param);
		}
		else {
			header("Location: ./options.php?saved" . $param);
		}
		
		die;
	}
	
	if(isset($_GET["group"])) {
		$group = $_GET["group"];
	}
	else {
		$group = "options";
	}
?>


<!DOCTYPE html>
<html>
	<head>
		<title>Abizeitung - Einstellungen</title>
		<?php head(); ?>
        <?php Options::script(true); ?>
	</head>
	
	<body>
		<?php require("nav-bar.php") ?>
		<div id="options" class="container-fluid admin-wrapper">
        	<?php if(isset($_GET["saved"])): ?>
				<div class="alert alert-success">Änderungen gespeichert.</div>
			<?php else: if(isset($_GET["error"])): 
				$errorHandler->import_url_param($_GET);
			?>
				<div class="alert alert-danger">
					Speichern fehlgeschlagen: 
					<ul>
						<?php $errorHandler->get_errors("li"); ?>
					</ul>
				</div>
			<?php endif; endif; ?>
			<h1>Einstellungen</h1>
			<div class="box">
				<ul class="nav nav-tabs">
                	<li<?php if($group == "options"): 	?> class="active"<?php endif; ?>><a href="options.php?group=options">Optionen</a></li>
                	<li<?php if($group == "images"): 	?> class="active"<?php endif; ?>><a href="options.php?group=images">Bilder</a></li>
                    <li<?php if($group == "csv"): 		?> class="active"<?php endif; ?>><a href="options.php?group=csv">CSV Import</a></li>
                </ul>
                <?php
					switch($group) {
						case "images":
							Options::display_images();
							break;
						case "options":
							Options::display_options();
							break;
						case "csv":
							Options::display_csv();
							break;
					}
				?>
			</div>
		</div>	
        
        <div class="modal fade" id="optionsModal" tabindex="-1" role="dialog" aria-hidden="true"></div>
	</body>
</html>

<?php db_close(); ?>