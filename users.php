<?php
	session_start();
	
	require_once("functions.php");
	require_once("classes/cUsers.php");
	
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
		<title>Abizeitung - Nutzerverwaltung</title>
		<?php head(); ?>
	</head>
	
	<body>
		<?php require("nav-bar.php") ?>
		<div id="user-management" class="container">
			<h1>Nutzerverwaltung</h1>
			<form id="data_form" name="data" action="save.php?group?<?php echo $group; ?>"></form>
			<div class="users box">
				<h2>Nutzer</h2>
                <ul class="nav nav-tabs">
                	<li<?php if($group == "students"): 	?> class="active"<?php endif; ?>><a href="users.php?group=students">Schüler</a></li>
                    <li<?php if($group == "teachers"): 	?> class="active"<?php endif; ?>><a href="users.php?group=teachers">Lehrer</a></li>
                    <li<?php if($group == "state"): 	?> class="active"<?php endif; ?>><a href="users.php?group=state">Status</a></li>
                </ul>
                <?php 
					switch($group) {
						case "teachers":
							Users::display_teachers();
							break;
						case "state":
							Users::display_state();
							break;
						default:
							Users::display_students();
					}
				?>
			</div>
            
			<a class="link" href="csv-import.php">Aus *.csv importieren</a>
            	
			<div class="buttons">
				<a class="button" href="add-user.php"><span class="icon-plus-circled"></span> Nutzer erstellen</a>
			</div>

		</div>	
	</body>
</html>

<?php db_close(); ?>