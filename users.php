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
		<div id="user-management" class="container-fluid admin-wrapper">
			<h1>Nutzerverwaltung</h1>
			<div class="users box">
                <ul class="nav nav-tabs">
                	<li<?php if($group == "students"): 	?> class="active"<?php endif; ?>><a href="users.php?group=students">Schüler</a></li>
                    <li<?php if($group == "teachers"): 	?> class="active"<?php endif; ?>><a href="users.php?group=teachers">Lehrer</a></li>
                    <li<?php if($group == "state"): 	?> class="active"<?php endif; ?>><a href="users.php?group=state">Status</a></li>
                    <li<?php if($group == "code"): 		?> class="active"<?php endif; ?>><a href="users.php?group=code">Aktivierungscode</a></li>
                    <li<?php if($group == "admins"): 	?> class="active"<?php endif; ?>><a href="users.php?group=admins">Administratoren</a></li>
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
						case "admins":
							Users::display_admins();
							break;
						default:
							Users::display_students();
					}
				?>
			</div>
            	
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