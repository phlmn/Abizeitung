<?php 
	
	session_start();
	
	require_once("functions.php");
	
	db_connect();
	check_login();
	check_admin();
	
	if(isset($_GET["delete"])) {
		$stmt->prepare("
			SELECT id
			FROM users
			WHERE id = ?
		");
		
		$stmt->bind_param("i", intval($_GET["user"]));
		$stmt->execute();
		
		$res = $stmt->affected_rows;
		$stmt->close();
		
		if($res > 0) {
			$stmt = $mysqli->prepare("
				DELETE FROM users
				WHERE id = ?
				LIMIT 1
			");
			
			$stmt->bind_param("i", intval($_GET["user"]));
			$stmt->execute();
		}
		
		header("Location: ./users.php");
		exit;	
	}
	
	$data = UserManager::get_userdata($_SESSION["user"]);
	
	$edit = UserManager::get_userdata($_GET["user"]);
	
	if(isset($_GET["edit"])) {
		if(isset($_POST["prename"]) || isset($_POST["lastname"]) || isset($_POST["email"])) {
			$userdata["id"] 		= $_GET["user"];
			$userdata["prename"] 	= $_POST["prename"];
			$userdata["lastname"] 	= $_POST["lastname"];
			$userdata["class"]["id"]= $_POST["class"];
			$userdata["birthday"] 	= $_POST["birthday"];
			$userdata["nickname"] 	= $_POST["nickname"];
			$userdata["email"] 		= $_POST["email"];
			$userdata["password"] 	= $_POST["password"];
			$userdata["admin"] 		= isset($_POST["admin"]);
			if($_POST["gender"] == "f") {
				$userdata["female"] = true;
			}
			else {
				$userdata["female"] = false;
			}
			
			$param = UserManager::edit_user($userdata);
			
			if($param == 0) {
				header("Location: ./edit-user.php?user=" . $_GET["user"] . "&saved");
			}
			else {
				header("Location: ./edit-user.php?user=" . $_GET["user"] . "&error=" . $param);
			}
			
			exit;
		}
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
							echo "Die Daten konnten nicht geändert werden";
							break;
						case "-2":
							echo "Das Passwort konnte nicht geändert werden.";
							break;
					}
				?>
                </div>
            <?php endif; endif; ?>
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
                                <option value="m" <?php if($edit["female"] == 0) echo "selected" ?>>Männlich</option>
                                <option value="f" <?php if($edit["female"] == 1) echo "selected" ?>>Weiblich</option>
                            </select>
                        </td>
					</tr>

					<tr>
						<td class="title">Tutorium</td>
						<td>
                        	<select name="class" form="data_form">
                        		<option>-</option>
                                <?php 
								
									$stmt = $mysqli->prepare("
										SELECT id, name
										FROM classes
									");
									
									$stmt->execute();
									$stmt->bind_result($class["id"], $class["name"]);
									
									$select = 0;
									if(isset($edit["class"]["id"]))
										$select = $edit["class"]["id"];
									
									while($stmt->fetch()) {
										if($select == $class["id"])
											echo '<option value="' . $class["id"] . '" selected>' . $class["name"] . "</option>";
										else
											echo '<option value="' . $class["id"] . '">' . $class["name"] . "</option>";
									}
									
									$stmt->close();
								?>
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
        <?php if(isset($_GET["delete"])): ?>	
			<script type="text/javascript">
				if(confirm("Benutzer <?php echo $edit["prename"] . " " . $edit["lastname"] ?> löschen?"))
					window.location = window.location + "=do";
			</script>
		<?php endif; ?>
	</body>
</html>

<?php db_close(); ?>