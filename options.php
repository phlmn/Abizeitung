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
		
		if(isset($_GET["action"])) {
			$action = $_GET["action"];
		}
		
		switch($_GET["affected"]) {
			case "images":
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
		}
		
		db_close();
			
		if($errorHandler->is_error()) {
			header("Location: ./options.php?error" . $errorHandler->export_url_param(true));
		}
		else {
			header("Location: ./options.php?saved");
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
		<title>Abizeitung - Fehlermeldungen</title>
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
                </ul>
                <?php
					switch($group) {
						case "images":
							Options::display_images();
							break;
						case "options":
							Options::display_options();
							break;
					}
				?>
			</div>
		</div>	
        
        <div class="modal fade" id="optionsModal" tabindex="-1" role="dialog" aria-hidden="true"></div>
	</body>
</html>

<?php db_close(); ?>