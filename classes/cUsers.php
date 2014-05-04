<?php

	class Users {
		
		public static function display_students() {
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
							<td class="edit"><a title="Bearbeiten" href="edit-user.php?user=<?php echo $row["id"] ?>"><span class="icon-pencil-squared"></span></a></td>
						</tr>
					<?php 
						endwhile; 
						
						$stmt->close();
					?>
					</tbody>
				</table>
<?php
		}
		
		public static function display_teachers() {
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
		
		public static function display_state() {
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
                        <th class="edit"></th>
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
						
						$count["images"] 	= db_count("categories");
						$count["questions"] = db_count("questions", "accepted", "1");
						$count["surveys"] 	= db_count("surveys", "accepted", "1", "AND m", "! 0") + db_count("surveys", "accepted", "1", "AND w", "! 0");
						
						while($stmt->fetch()): 
						
							$row["images"] 		= db_count("images", "uid", $row["id"]);
							$row["questions"] 	= db_count("users_questions", "user", $row["id"]);
							$row["surveys"] 	= db_count("users_surveys", "user", $row["id"], "AND m ", "! 0") + db_count("users_surveys", "user", $row["id"], "AND w ", "! 0");
							
							$missing = (
								$row["birthday"] && 
								$row["activated"] && 
								$row["images"] == $count["images"] && 
								$row["questions"] == $count["questions"] && 
								$row["surveys"] == $count["surveys"]
							);
							
						?>
						<tr>
							<td class="name <?php echo ($missing)									? "existing" : "missing"?>"><?php echo $row["prename"] ?></td>
							<td class="name <?php echo ($missing)									? "existing" : "missing"?>"><?php echo $row["lastname"] ?></td>
							<td class="<?php echo ($row["birthday"]) 							? "existing" : "missing"?>"><?php echo $row["birthday"] ?></td>
							<td class="<?php echo ($row["activated"]) 							? "existing" : "missing"?>"><?php echo $row["activated"] ?></td>
                            <td class="<?php echo ($row["images"] 		== $count["images"]) 	? "existing" : "missing"?>"><?php echo $row["images"] ?></td>
                            <td class="<?php echo ($row["questions"] 	== $count["questions"]) ? "existing" : "missing"?>"><?php echo $row["questions"] ?></td>
                            <td class="<?php echo ($row["surveys"] 		== $count["surveys"]) 	? "existing" : "missing"?>"><?php echo $row["surveys"] ?></td>
                            <td class="<?php echo ($missing) ? "existing" : "missing"?> edit">
                            	<a title="Auswertung" href="user-result.php?user=<?php echo $row["id"] ?>"><span class="icon-download"></span></a>
                            </td>
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
		
		public static function display_unlock_code() {
			global $mysqli;
		?>
        		<table class="table table-striped code">
					<thead>
						<th>Vorname</th>
						<th>Nachname</th>
						<th>Aktivierungscode</th>
						<th class="noprint">Tutorium</th>
					</thead>
                    <tbody>
        <?php
			
			$stmt = $mysqli->prepare("
				SELECT users.id, users.prename, users.lastname, users.unlock_key, tutorials.name
				FROM users
				LEFT JOIN students ON users.id = students.uid
				LEFT JOIN tutorials ON students.tutorial = tutorials.id
				WHERE users.activated = 0
				ORDER BY students.tutorial ASC, users.lastname ASC
			");
			
			$stmt->execute();
			
			$stmt->bind_result($row["id"], $row["prename"], $row["lastname"], $row["key"], $row["tutorial"]);
			
				while($stmt->fetch()):
			?>
            			<tr>
                        	<td><?php echo $row["prename"] ?><a class="printonly"><br />http://<?php echo $_SERVER["SERVER_NAME"] ?></a></td>
                            <td><?php echo $row["lastname"] ?></td>
                            <td>
                            	<label class="printonly" for="key_<?php echo $row["key"] ?>">Aktivierungscode: </label>
                            	<input id="key_<?php echo $row["key"] ?>" class="key" type="text" value="<?php echo $row["key"] ?>" onclick="this.select()" readonly="readonly"/>
                            </td>
                            <td class="noprint"><?php echo $row["tutorial"] ?></td>
                        </tr>
            <?php
				endwhile;
				
			$stmt->close();
			
			?>
					</tbody>
                </table>
            <?php
		}
		
	}

?>