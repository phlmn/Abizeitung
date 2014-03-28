<?php 
	session_start();
	
	require_once("functions.php");
	
	db_connect();
	check_login();
	check_admin();
	
	$data = get_userdata($_SESSION["user"]);
	
	if(isset($_GET["create"])) {
	
		$userdata["prename"] = $_POST["prename"];
		$userdata["lastname"] = $_POST["lastname"];
		$userdata["birthday"] = $_POST["birthday"];
		$userdata["nickname"] = $_POST["nickname"];
		$userdata["email"] = $_POST["email"];
		$userdata["password"] = $_POST["password"];
		$userdata["tutor"] = isset($_POST["tutor"]);
		$userdata["admin"] = isset($_POST["bla"]);
		if($_POST["gender"] == "f") {
			$userdata["female"] = true;
		}
		else {
			$userdata["female"] = false;
		}
		
		add_user($userdata);
	}
	
?>


<!DOCTYPE html>
<html>
	<head>
		<title>Abizeitung - Nutzerverwaltung</title>
		<link rel="stylesheet" href="style.css">
		<link rel="stylesheet" href="icons/css/fontello.css">
	    <!--[if IE 7]>
	    <link rel="stylesheet" href="icons/css/fontello-ie7.css">
	    <![endif]-->
		<meta charset="utf-8">
		<script src="jquery.js" type="text/javascript"></script>
	</head>
	
	<body>
		<?php require("nav-bar.php") ?>
		<div id="user-management">
			<h1>Nutzerverwaltung</h1>
			<form id="data_form" name="data" method="post" action="add-user.php?create"></form>
			<div class="add-user">
				<h2>Nutzer erstellen</h2>
				<table>
					<tr>
						<td class="title">Vorname</td>
						<td><input name="prename" type="text" form="data_form" /></td>
					</tr>
					<tr>
						<td class="title">Nachname</td>
						<td><input name="lastname" type="text" form="data_form" /></td>
					</tr>
					<tr>
						<td class="title">Geschlecht</td>
						<td>
                        	<select name="gender" form="data_form">
                            	<option value="m">Männlich</option>
                                <option value="f">Weiblich</option>
                            </select>
                        </td>
					</tr>

					<tr>
						<td class="title">Tutorium</td>
						<td>
                        	<select name="class" form="data_form">
                            	<option>-</option>
                                <option>DV1</option>
                                <option>DV2</option>
                                <option>MB1</option>
                            </select>
                        </td>
					</tr>
					<tr>
						<td class="title">Geburtsdatum</td>
						<td><input name="birthday" type="text" form="data_form" /></td>
					</tr>
					<tr>
						<td class="title">Spitzname</td>
						<td><input name="nickname" type="text" form="data_form" /></td>
					</tr>
					<tr>
						<td class="title">E-Mail</td>
						<td><input name="email" type="text" form="data_form" /></td>
					</tr>
					<tr>
						<td class="title">Passwort</td>
						<td><input name="password" type="password" form="data_form" /></td>
					</tr>
                    <tr>
						<td class="title">Tutor</td>
						<td><input name="tutor" type="checkbox" form="data_form" /></td>
					</tr>
					<tr>
						<td class="title">Administrator</td>
						<td><input name="admin" type="checkbox" form="data_form" /></td>
					</tr>
				</table>
			</div>
						
			<div class="buttons">
				<input type="submit" value="Erstellen" form="data_form" />
				<a class="button" href="users.php">Zurück<a/>
			</div>

		</div>	
	</body>
</html>

<?php db_close(); ?>