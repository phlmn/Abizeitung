<?php 
	session_start();
	
	require_once("functions.php");
	
	db_connect();
	check_login();
	check_admin();
	
	$data = UserManager::get_userdata($_SESSION["user"]);
	
	if(isset($_GET["create"])) {
		$userdata["prename"] 	= $_POST["prename"];
		$userdata["lastname"] 	= $_POST["lastname"];
		$userdata["tutorial"] 	= $_POST["tutorial"];
		$userdata["birthday"] 	= $_POST["birthday"];
		$userdata["nickname"] 	= $_POST["nickname"];
		$userdata["email"] 		= $_POST["email"];
		$userdata["password"] 	= $_POST["password"];
		$userdata["teacher"] 	= isset($_POST["teacher"]);
		$userdata["admin"] 		= isset($_POST["admin"]);
		if($_POST["gender"] == "f") {
			$userdata["female"] = true;
		}
		else {
			$userdata["female"] = false;
		}
		
		$param = UserManager::add_user($userdata);
		
		if($param == 0) {
			header("Location: ./add-user.php?saved");
		}
		else {
			header("Location: ./add-user.php?error=" . $param);
		}
		
		exit;
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
        	<?php if(isset($_GET["saved"])): ?>
				<div class="alert alert-success">Änderungen gespeichert.</div>
            <?php else: if(isset($_GET["error"])): ?>
                <div class="alert alert-danger">
                	Speichern fehlgeschlagen.<br />
                <?php
					switch($_GET["error"]) {
						case "-1":
							echo "Die Emailadresse oder das Passwort wurde(n) nicht eingegeben.";
							break;
						case "-2":
							echo "Die Emailadresse existiert bereits.";
							break;
						case "1":
							echo "Der Benutzer konnte nicht hinzugefügt werden.";
							break;
						case "2":
							echo "Der Benutzer konnte nicht als Lehrer hinzugefügt werden.";
							break;
					}
				?>
                </div>
            <?php endif; endif; ?>
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
                        	<select name="tutorial" form="data_form">
                            	<option value="0">-</option>
                                <?php 
									$stmt = $mysqli->prepare("
										SELECT id, name
										FROM tutorials
									");
									
									$stmt->execute();
									$stmt->bind_result($tutorial["id"], $tutorial["name"]);
									
									while($stmt->fetch()) : ?>
										<option value="<?php echo $tutorial["id"] ?>"><?php echo $tutorial["name"] ?></option>
									<?php endwhile;
									
									$stmt->close();
								?>
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
						<td class="title">Lehrer</td>
						<td><input name="teacher" type="checkbox" form="data_form" /></td>
					</tr>
					<tr>
						<td class="title">Administrator</td>
						<td><input name="admin" type="checkbox" form="data_form" /></td>
					</tr>
				</table>
			</div>
						
			<div class="buttons">
				<input type="submit" value="Erstellen" form="data_form" />
				<a class="button" href="users.php">Zurück</a>
			</div>

		</div>	
	</body>
</html>

<?php db_close(); ?>