<?php
	session_start();
	
	require_once("functions.php");
	
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
                	<li<?php if($group == "students"): ?> class="active"<?php endif; ?>><a href="users.php?group=students">Schüler</a></li>
                    <li<?php if($group == "teacher"): ?> class="active"<?php endif; ?>><a href="users.php?group=teacher">Lehrer</a></li>
                </ul>
                <?php if($group == "teacher"): ?>
                <table class="table table-striped">
					<thead>
						<th>Vorname</th>
						<th>Nachname</th>
						<th>Spitzname</th>
						<th>Geburtsdatum</th>
						<th>Geschlecht</th>
						<th class="edit"></th>
					</thead>
					<tbody>
					<?php
						global $mysqli;
						
						$stmt = $mysqli->prepare("
							SELECT users.id AS id, users.prename, users.lastname, users.nickname, users.birthday, users.female
							FROM teacher
							LEFT JOIN users ON teacher.uid = users.id
							ORDER BY users.lastname
						");
						
						$stmt->execute();
						$stmt->bind_result($row["id"], $row["prename"], $row["lastname"], $row["nickname"], $row["birthday"], $row["female"]);
						
						while($stmt->fetch()): ?>
						<tr>
							<td><?php echo $row["prename"] ?></td>
							<td><?php echo $row["lastname"] ?></td>
							<td><?php echo $row["nickname"] ?></td>
							<td><?php echo $row["birthday"] ?></td>
							<td><?php echo $row["female"] ? "Weiblich" : "Männlich" ?></td>
							<td class="edit"><a href="edit-user.php?user=<?php echo $row["id"] ?>"><span class="icon-pencil-squared"></span></a></td>
						</tr>
					<?php endwhile; ?>
					</tbody>
				</table>
                <?php else: ?>
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
						
						$stmt = $mysqli->prepare("
							SELECT users.id AS id, users.prename, users.lastname, users.nickname, users.birthday, users.female, tutorial.name, tutor.lastname AS tutor 
							FROM students
							LEFT JOIN users ON students.uid = users.id
							LEFT JOIN tutorial ON users.class = tutorial.id
							LEFT JOIN teacher ON tutorial.tutor = teacher.id
							LEFT JOIN users tutor ON teacher.uid = tutor.id
							ORDER BY users.lastname
						");
						
						$stmt->execute();
						$stmt->bind_result($row["id"], $row["prename"], $row["lastname"], $row["nickname"], $row["birthday"], $row["female"], $row["name"], $row["tutor"]);	
					
						while($stmt->fetch()): ?>
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
					<?php endwhile; ?>
					</tbody>
				</table>
                <?php endif; ?>
				
			</div>
						
			<div class="buttons">
				<a class="button" href="add-user.php"><span class="icon-plus-circled"></span> Nutzer erstellen</a>
			</div>

		</div>	
	</body>
</html>

<?php db_close(); ?>