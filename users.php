<?php
	session_start();
	
	require_once("functions.php");
	
	db_connect();
	check_login();
	check_admin(); 

	$data = UserManager::get_userdata($_SESSION["user"]);	
	
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
			<form id="data_form" name="data" action="save.php"></form>
			<div class="users">
				<h2>Nutzer</h2>
				<table class="table table-striped">
					<thead>
						<th>Vorname</th>
						<th>Nachname</th>
						<th>Spitzname</th>
						<th>Geburtsdatum</th>
						<th>Geschlecht</th>
						<th>Tutorium</th>
						<th>Tutor</th>
						<th class="edit"></th>
					</thead>
					<tbody>
					<?php
						global $mysqli;
						$res = $mysqli->query("
							SELECT users.id AS id, users.prename, users.lastname, users.nickname, users.birthday, users.female, classes.name, tutor.lastname AS tutor 
							FROM users
							LEFT JOIN users_classes ON users.id = users_classes.user
							LEFT JOIN classes ON users_classes.id = classes.id OR users.class = classes.id
							LEFT JOIN teacher ON classes.tutor = teacher.id
							LEFT JOIN users tutor ON teacher.uid = tutor.id
							ORDER BY users.lastname
						");						
						
					?>
					<?php $i = 0; ?>
					<?php while($row = $res->fetch_assoc()): ?>
						<tr>
							<td><?php echo $row["prename"] ?></td>
							<td><?php echo $row["lastname"] ?></td>
							<td><?php echo $row["nickname"] ?></td>
							<td><?php echo $row["birthday"] ?></td>
							<td><?php echo $row["female"] ? "Weiblich" : "Männlich" ?></td>
							<td><?php echo $row["name"] ?></td>
							<td><?php echo $row["tutor"] ?></td>
							<td class="edit"><a href="edit-user.php?user=<?php echo $row["id"] ?>"><span class="icon-pencil-squared"></span></a></td>
						</tr>
					<?php $i++; ?>
					<?php endwhile; ?>
					</tbody>
				</table>
			</div>
						
			<div class="buttons">
				<a class="button" href="add-user.php"><span class="icon-plus-circled"></span> Nutzer erstellen</a>
			</div>

		</div>	
	</body>
</html>

<?php db_close(); ?>