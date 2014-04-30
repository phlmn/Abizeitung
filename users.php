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
        <?php if($group == "code"): ?>
        <link rel="stylesheet" href="less/print.css" type="text/css" media="print">
        <?php endif; ?>
	</head>
	
	<body>
		<?php require("nav-bar.php") ?>
		<div id="user-management" class="container">
			<h1>Nutzerverwaltung</h1>
			<div class="users box">
				<h2>Nutzer</h2>
                <ul class="nav nav-tabs">
                	<li<?php if($group == "students"): 	?> class="active"<?php endif; ?>><a href="users.php?group=students">Schüler</a></li>
                    <li<?php if($group == "teachers"): 	?> class="active"<?php endif; ?>><a href="users.php?group=teachers">Lehrer</a></li>
                    <li<?php if($group == "state"): 	?> class="active"<?php endif; ?>><a href="users.php?group=state">Status</a></li>
                    <li<?php if($group == "code"): 		?> class="active"<?php endif; ?>><a href="users.php?group=code">Aktivierungscode</a></li>
                </ul>
                <?php 
					switch($group) {
						case "teachers":
							Users::display_teachers();
							break;
						case "state":
							Users::display_state();
							break;
						case "code":
							Users::display_unlock_code();
							break;
						default:
							Users::display_students();
					}
				?>
			</div>
            
            <?php if($group == "students") : ?>
			<a class="link" href="csv-import.php">Aus *.csv importieren</a>
            <?php endif; ?>
            	
			<div class="buttons">
            	<?php if($group == "students"): ?>
				<a class="button" href="add-student.php"><span class="icon-plus-circled"></span> Schüler erstellen</a>
                <?php else: if($group == "teachers"): ?>
                <a class="button" href="add-teacher.php"><span class="icon-plus-circled"></span> Lehrer erstellen</a>
                <?php endif; endif; ?>
			</div>

		</div>	
	</body>
</html>

<?php db_close(); ?>