<?php 
	
	session_start();
	
	require_once("functions.php");
	
	db_connect();
	check_login();
	check_admin();
	
	if(isset($_GET["delete"]))
		if($_GET["delete"] == "do") {
			$mysqli->query("SELECT * FROM users WHERE id = '".$_GET["user"]."'");
			
			if($mysqli->affected_rows > 0)
				$mysqli->query("DELETE FROM users WHERE id = '".$_GET["user"]."'");
			
			header("Location: ./edit-user.php?user=".($_GET["user"]-1));
		}
	
	$data = get_userdata($_SESSION["user"]);
	
	$edit = get_userdata($_GET["user"]);
	
	if(isset($_GET["edit"])) {
		if(isset($_POST["prename"]) || isset($_POST["lastname"]) || isset($_POST["email"])) {
			$userdata["id"] 		= $_GET["user"];
			$userdata["prename"] 	= $_POST["prename"];
			$userdata["lastname"] 	= $_POST["lastname"];
			$userdata["female"] 	= $_POST["gender"];
			$userdata["class"] 		= $_POST["name"];
			$userdata["birthday"] 	= $_POST["birthday"];
			$userdata["nickname"] 	= $_POST["nickname"];
			$userdata["email"] 		= $_POST["email"];
			$userdata["password"] 	= $_POST["password"];
			$userdata["tutor"] 		= isset($_POST["tutor"]);
			$userdata["admin"] 		= isset($_POST["admin"]);
			
			header("Location: ./edit-user.php?user=".$_GET["user"]);
		}
		
		edit_user($userdata);
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
			<form id="data_form" name="data" action="edit-user.php?user=<?php echo $_GET["user"] ?>&edit" method="post"></form>
			<div class="add-user">
				<h2>Nutzer bearbeiten</h2>
				<table>
					<tr>
						<td class="title">Vorname</td>
						<td><input name="prename" type="text" form="data_form" value="<?php echo $edit["prename"] ?>" /></td>
					</tr>
					<tr>
						<td class="title">Nachname</td>
						<td><input name="lastname" type="text" form="data_form" value="<?php echo $edit["lastname"] ?>" /></td>
					</tr>
					<tr>
						<td class="title">Geschlecht</td>
						<td>
                        	<select name="gender" form="data_form">
                            	<option>-</option>
                                <option <?php if($edit["female"] == 0) echo "selected" ?>>Männlich</option>
                                <option <?php if($edit["female"] == 1) echo "selected" ?>>Weiblich</option>
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
						<td><input name="birthday" type="text" form="data_form" value="<?php echo $edit["birthday"] ?>" /></td>
					</tr>
					<tr>
						<td class="title">Spitzname</td>
						<td><input name="nickname" type="text" form="data_form" value="<?php echo $edit["nickname"] ?>" /></td>
					</tr>
					<tr>
						<td class="title">E-Mail</td>
						<td><input name="email" type="text" form="data_form" value="<?php echo $edit["email"] ?>" /></td>
					</tr>
					<tr>
						<td class="title">Passwort</td>
						<td><input name="password" type="password" form="data_form" placeholder="Unverändert" /></td>
					</tr>
                    <tr>
                    	<td class="title">Tutor</td>
                        <td><input name="tutor" type="checkbox" form="data_form" <?php if($edit["istutor"] == true) echo "checked" ?> /></td>
                    </tr>
					<tr>
						<td class="title">Administrator</td>
						<td><input name="admin" type="checkbox" form="data_form" <?php if($edit["admin"] == true) echo "checked" ?> /></td>
					</tr>
				</table>
			</div>
						
			<div class="buttons">
				<input type="submit" value="Speichern" form="data_form" />
				<a class="button" href="users.php">Zurück</a>
                <a class="button delete" href="edit-user.php?user=<?php echo $_GET["user"] ?>&delete">Löschen</a>
			</div>

		</div>
        <?php
    		if(isset($_GET["delete"]))
				if($_GET["delete"] != "do") {
					?>
					<script type="text/javascript">
						if(confirm("Benutzer <?php echo $edit["prename"] . " " . $edit["lastname"] ?> löschen?"))
							window.location = window.location + "=do";
					</script>
					<?php
				}
		?>
	</body>
</html>

<?php db_close(); ?>