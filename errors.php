<?php
	session_start();
	
	require_once("functions.php");
	require_once("classes/cErrors.php");
	
	db_connect();
	check_login();
	check_admin();

	$data = UserManager::get_userdata($_SESSION["user"]);
	
	if(isset($_GET["action"]) && isset($_GET["id"])) {
		if($_GET["action"] == "solved")
			Errors::error_solved($_GET["id"]);
		if($_GET["action"] == "existing")
			Errors::error_still_existing($_GET["id"]);
		
		db_close();
		
		header("Location: ./errors.php");
		
		die;
	}
	
?>


<!DOCTYPE html>
<html>
	<head>
		<title>Abizeitung - Fehlermeldungen</title>
		<?php head(); ?>
	</head>
	
	<body>
		<?php require("nav-bar.php") ?>
		<div id="error-reports" class="container-fluid admin-wrapper">
			<h1>Fehlermeldungen</h1>
			<div class="errors box">
				<?php Errors::display_errors(); ?>
			</div>
		</div>	
	</body>
</html>

<?php db_close(); ?>