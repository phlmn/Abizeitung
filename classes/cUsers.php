<?php

	class Users {
		
		function display_students() {
			global $mysqli;
?>
				<table class="table table-striped">
					<thead>
						<th>Vorname</th>
						<th>Nachname</th>
						<th>Geburtsdatum</th>
						<th>Geschlecht</th>
						<th>Tutorium</th>
						<th>Tutor</th>
						<th class="edit"></th>
					</thead>
					<tbody>
					<?php
						$stmt = $mysqli->prepare("
							SELECT users.id, users.prename, users.lastname, users.birthday, users.female, tutorials.name, tutors.lastname 
							FROM students
							LEFT JOIN users ON students.uid = users.id
							LEFT JOIN tutorials ON students.tutorial = tutorials.id
							LEFT JOIN teachers ON tutorials.tutor = teachers.id
							LEFT JOIN users AS tutors ON teachers.uid = tutors.id
							ORDER BY users.lastname
						");
						
						$stmt->execute();
						$stmt->bind_result($row["id"], $row["prename"], $row["lastname"], $row["birthday"], $row["female"], $row["name"], $row["tutor"]);	
					
						while($stmt->fetch()): ?>
						<tr>
							<td><?php echo $row["prename"] ?></td>
							<td><?php echo $row["lastname"] ?></td>
							<td><?php echo $row["birthday"] ?></td>
							<td><?php echo $row["female"] ? "Weiblich" : "Männlich" ?></td>
							<td><?php echo $row["name"] ?></td>
							<td><?php echo $row["tutor"] ?></td>
							<td class="edit"><a href="edit-user.php?user=<?php echo $row["id"] ?>"><span class="icon-pencil-squared"></span></a></td>
						</tr>
					<?php 
						endwhile; 
						
						$stmt->close();
					?>
					</tbody>
				</table>
<?php
		}
		
		function display_teachers() {
			global $mysqli;
?>
				<table class="table table-striped">
					<thead>
						<th>Vorname</th>
						<th>Nachname</th>
						<th>Geburtsdatum</th>
						<th>Geschlecht</th>
						<th class="edit"></th>
					</thead>
					<tbody>
					<?php
						global $mysqli;
						
						$stmt = $mysqli->prepare("
							SELECT users.id, users.prename, users.lastname, users.birthday, users.female
							FROM teachers
							LEFT JOIN users ON teachers.uid = users.id
							ORDER BY users.lastname
						");
						
						$stmt->execute();
						$stmt->bind_result($row["id"], $row["prename"], $row["lastname"], $row["birthday"], $row["female"]);
						
						while($stmt->fetch()): ?>
						<tr>
							<td><?php echo $row["prename"] ?></td>
							<td><?php echo $row["lastname"] ?></td>
							<td><?php echo $row["birthday"] ?></td>
							<td><?php echo $row["female"] ? "Weiblich" : "Männlich" ?></td>
							<td class="edit"><a href="edit-user.php?user=<?php echo $row["id"] ?>"><span class="icon-pencil-squared"></span></a></td>
						</tr>
					<?php 
						endwhile; 
						
						$stmt->close();
					?>
					</tbody>
				</table>
<?php
		}
		
		function display_state() {
			global $mysqli;
?>
				<table class="table table-striped state">
					<thead>
						<th>Vorname</th>
						<th>Nachname</th>
						<th>Geburtstag</th>
						<th>Aktiviert</th>
						<th>Bilder</th>
                        <th>Fragen</th>
						<th>Umfragen</th>
					</thead>
					<tbody>
					<?php
						global $mysqli;
						
						$stmt = $mysqli->prepare("
							SELECT id, prename, lastname, birthday, activated
							FROM users
							ORDER BY lastname ASC
						");
						
						$stmt->execute();
						
						$stmt->bind_result($row["id"], $row["prename"], $row["lastname"], $row["birthday"], $row["activated"]);
						$stmt->store_result();
						
						while($stmt->fetch()): 
						
							$count["images"] 	= 0;
							$count["questions"] = 0;
							$count["surveys"] 	= 0;
							
							$stmt2 = $mysqli->prepare("
								SELECT COUNT(id)
								FROM images
								WHERE uid = ?
							");
							
							$stmt2->bind_param("i", intval($row["id"]));
							$stmt2->execute();
							
							$stmt2->bind_result($row["images"]);
							$stmt2->store_result();
							
							if($stmt2->fetch()) {
								$count["images"] = $row["images"];
							}
							
							$stmt2->free_result();
							$stmt2->close();
							
							$stmt2 = $mysqli->prepare("
								SELECT COUNT(id)
								FROM users_questions
								WHERE user = ?
							");
							
							$stmt2->bind_param("i", intval($row["id"]));
							$stmt2->execute();
							
							$stmt2->bind_result($row["questions"]);
							$stmt2->store_result();
							
							if($stmt2->fetch()) {
								$count["questions"] = $row["questions"];
							}
							
							$stmt2->free_result();
							$stmt2->close();
							
							$stmt2 = $mysqli->prepare("
								SELECT COUNT(id)
								FROM users_surveys
								WHERE user = ?
							");
							
							$stmt2->bind_param("i", intval($row["id"]));
							$stmt2->execute();
							
							$stmt2->bind_result($row["surveys"]);
							$stmt2->store_result();
							
							if($stmt2->fetch()) {
								$count["surveys"] = $row["surveys"];
							}
							
							$stmt2->free_result();
							$stmt2->close();
							
							$missing = ($row["birthday"] && $row["activated"] && $count["images"] && $count["questions"] && $count["surveys"]);
							
						?>
						<tr>
							<td class="<?php echo ($missing)			? "existing" : "missing"?>"><?php echo $row["prename"] ?></td>
							<td class="<?php echo ($missing)			? "existing" : "missing"?>"><?php echo $row["lastname"] ?></td>
							<td class="<?php echo ($row["birthday"]) 	? "existing" : "missing"?>"><?php echo $row["birthday"] ?></td>
							<td class="<?php echo ($row["activated"]) 	? "existing" : "missing"?>"><?php echo $row["activated"] ?></td>
                            <td class="<?php echo ($count["images"]) 	? "existing" : "missing"?>"><?php echo $count["images"] ?></td>
                            <td class="<?php echo ($count["questions"]) ? "existing" : "missing"?>"><?php echo $count["questions"] ?></td>
                            <td class="<?php echo ($count["surveys"]) 	? "existing" : "missing"?>"><?php echo $count["surveys"] ?></td>
						</tr>
					<?php 
						endwhile; 
						
						$stmt->free_result();
						$stmt->close();
					?>
					</tbody>
				</table>
<?php
		}
	}

?>