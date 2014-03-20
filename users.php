<?php
	session_start();
	
	require_once("functions.php");
	
	db_connect();
	check_login();
	check_admin(); 

	$data = get_userdata($_SESSION["user"]);	
	
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
			<form id="data_form" name="data" action="save.php"></form>
			<div class="users">
				<h2>Nutzer</h2>
				<table>
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
							SELECT users.id AS id, prename, lastname, nickname, birthday, female, name, tutor
							FROM users
							LEFT JOIN users_classes ON users.id = users_classes.user
							LEFT JOIN classes ON users_classes.id = classes.id
							ORDER BY lastname
						");
						
						
					?>
					<?php $i = 0; ?>
					<?php while($row = $res->fetch_assoc()): ?>
						<tr <?php if($i % 2 == 0): ?>class="alternate"<?php endif; ?>>
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