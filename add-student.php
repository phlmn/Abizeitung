<?php 
	session_start();
	
	require_once("functions.php");
	
	db_connect();
	check_login();
	check_admin();
	
	$data = UserManager::get_userdata($_SESSION["user"]);
	
	// handle form data
	
	if(isset($_GET["create"])) {
		// Save posted data in array
		
		$userdata["prename"] 	= $_POST["prename"];
		$userdata["lastname"] 	= $_POST["lastname"];
		$userdata["tutorial"] 	= $_POST["tutorial"];
		$userdata["birthday"] 	= $_POST["birthday"];
		$userdata["nickname"] 	= $_POST["nickname"];
		$userdata["email"]		= $_POST["email"];
		$userdata["password"] 	= $_POST["password"];
		$userdata["teacher"] 	= 0;
		$userdata["admin"] 		= isset($_POST["admin"]);
		if($_POST["gender"] == "f") {
			$userdata["female"] = true;
		}
		else {
			$userdata["female"] = false;
		}
		
		// add data to database
		
		
		
		$errorHandler->add_error(UserManager::add_user($userdata));
		
		if($errorHandler->is_error()) {
			// data failed
			header("Location: ./add-student.php?error" . $errorHandler->export_url_param(true));
		}
		else {
			// data saved
			header("Location: ./add-student.php?saved");
		}
		
		die;
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
		
		<div id="user-management" class="container-fluid admin-wrapper">
		
			<?php
				// handle notifications
				
				if(isset($_GET["saved"])) {
			 		echo '<div class="alert alert-success">Änderungen gespeichert.</div>';	
				}
				
				else if(isset($_GET["error"])) {
			 		echo '<div class="alert alert-danger">Speichern fehlgeschlagen:<ul>';
					
					$errorHandler->import_url_param($_GET);
					
					echo $errorHandler->get_errors("li");
			 		
			 		echo '</ul></div>';	
				}
			?>	 
					 
			<h1>Nutzerverwaltung</h1>
			
			<div class="box">
			
				<h2>Nutzer erstellen</h2>
				
				<form id="data_form" name="data" method="post" action="./add-student.php?create"></form>
				
				<div class="add-user">
				
					<table>
						<tr>
							<td class="title">Vorname</td>
							<td><input name="prename" type="text" form="data_form"></td>
						</tr>
						<tr>
							<td class="title">Nachname</td>
							<td><input name="lastname" type="text" form="data_form"></td>
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
										
										// get all tutorials from database
										
										$stmt = $mysqli->prepare("
											SELECT id, name
											FROM tutorials
										");
										
										$stmt->execute();
										$stmt->bind_result($tutorial["id"], $tutorial["name"]);
										
										while($stmt->fetch()) {
											echo "<option value='{$tutorial['id']}'>{$tutorial['name']}</option>";
										}	
										
										$stmt->close();
									?>
								 </select>
							 </td>
						</tr>
						<tr>
							<td class="title">Geburtsdatum</td>
							<td><input name="birthday" type="text" form="data_form"></td>
						</tr>
						<tr>
							<td class="title">Spitzname</td>
							<td><input name="nickname" type="text" form="data_form"></td>
						</tr>
						<tr>
							<td class="title">E-Mail</td>
							<td><input name="email" type="text" form="data_form"></td>
						</tr>
						<tr>
							<td class="title">Passwort</td>
							<td><input name="password" type="password" form="data_form"></td>
						</tr>
						<tr>
							<td class="title">Administrator</td>
							<td><input name="admin" type="checkbox" form="data_form"></td>
						</tr>
					</table>
					
				</div><!-- .add-user -->
						
				<div class="buttons">
					<input type="submit" value="Erstellen" form="data_form">
					<a class="button" href="./users.php?group=students">Zurück</a>
				</div><!-- .buttons -->
				
			</div><!-- .box -->

		</div><!-- #user-management -->
		
	</body>
</html>

<?php db_close(); ?>