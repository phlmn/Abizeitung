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
		
		public static function display_state($tutorial = false) {
			global $mysqli;
			
			$userstate = array(
				db_count("users", "activated", "1"), 
				db_count("users", "activated", "0")
			);
			
			$res = get_percent($userstate, 0);
?>
				<div class="summary">
                    <div class="progress">
                    <?php get_progressbar($res["percent"], $res["absolute"], array("Aktiviert:", "Ausstehend:")); ?>
                    </div>
                </div>
                
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
						
						if($tutorial) {
							$stmt = $mysqli->prepare("
								SELECT users.id, users.prename, users.lastname, users.birthday, users.activated, users.email
								FROM users
								INNER JOIN students ON students.uid = users.id
								WHERE students.tutorial = ?
								ORDER BY users.lastname ASC
							");
							
							$stmt->bind_param("i", $tutorial);
							$stmt->execute();
							
							$stmt->bind_result($row["id"], $row["prename"], $row["lastname"], $row["birthday"], $row["activated"], $row["email"]);
							$stmt->store_result();
						}
						else {
							$stmt = $mysqli->prepare("
								SELECT id, prename, lastname, birthday, activated
								FROM users
								ORDER BY lastname ASC
							");
							
							$stmt->execute();
							
							$stmt->bind_result($row["id"], $row["prename"], $row["lastname"], $row["birthday"], $row["activated"]);
							$stmt->store_result();
						}
						
						$count["images"] 	= db_count("categories");
						$count["questions"] = db_count("questions", "accepted", "1");
						$count["surveys"] 	= db_count("surveys", "accepted", "1", "AND m", "! 0") + db_count("surveys", "accepted", "1", "AND w", "! 0");
						
						while($stmt->fetch()): 
						
							$row["images"] 		= db_count("images", "uid", $row["id"], "DISTINCT category");
							$row["questions"] 	= db_count("users_questions", "user", $row["id"]);
							$row["surveys"] 	= db_count("users_surveys", "user", $row["id"], "AND m ", "! 0") + db_count("users_surveys", "user", $row["id"], "AND w ", "! 0");
							
							$missing = (
								$row["birthday"] && 
								$row["activated"] && 
								$row["images"] == $count["images"] && 
								$row["questions"] == $count["questions"] && 
								$row["surveys"] == $count["surveys"]
							);
							
							$percent = array(
								"images" 	=> 100 * $row["images"] 	/ $count["images"],
								"questions" => 100 * $row["questions"] 	/ $count["questions"],
								"surveys" 	=> 100 * $row["surveys"] 	/ $count["surveys"]
							);
							
						?>
						<tr>
							<td class="name <?php echo ($missing)						? "existing" : "missing"?>"><?php echo $row["prename"]; ?></td>
							<td class="name <?php echo ($missing)						? "existing" : "missing"?>"><?php echo $row["lastname"]; ?></td>
							<td class="<?php echo ($row["birthday"]) 					? "existing" : "missing"?>"><?php echo $row["birthday"]; ?></td>
							<td class="<?php echo ($row["activated"]) 					? "existing" : "missing"?>"><?php echo ($row["activated"]) ? "Ja" : "Nein" ?></td>
                            <td class="<?php echo ($row["images"] >= $count["images"]) 	? "existing" : "missing"?>">
                                <div class="bar">
                                	<?php if($row["images"] < $count["images"]): ?>
                                    <div class="filled" style="width: <?php echo $percent["images"]; ?>%"></div>
                                    <div class="missed" style="width: <?php echo 100 - $percent["images"]; ?>%"></div>
                                    <?php endif; ?>
                                    <div class="text">
										<?php echo $row["images"]; ?> / <?php echo $count["images"]; ?> <span>(<?php echo round($percent["images"]); ?>%)</span>
                                    </div>
                                </div>
                            </td>
                            <td class="<?php echo ($row["questions"] == $count["questions"]) ? "existing" : "missing"?>">
                                <div class="bar">
                                	<?php if($row["questions"] < $count["questions"]): ?>
                                    <div class="filled" style="width: <?php echo $percent["questions"]; ?>%"></div>
                                    <div class="missed" style="width: <?php echo 100 - $percent["questions"]; ?>%"></div>
                                    <?php endif; ?>
                                    <div class="text">
										<?php echo $row["questions"]; ?> / <?php echo $count["questions"]; ?> <span>(<?php echo round($percent["questions"]); ?>%)</span>
                                    </div>
                                </div>
                            </td>
                            <td class="<?php echo ($row["surveys"] == $count["surveys"]) ? "existing" : "missing"?>">
                                <div class="bar">
                                	<?php if($row["surveys"] < $count["surveys"]): ?>
                                    <div class="filled" style="width: <?php echo $percent["surveys"]; ?>%"></div>
                                    <div class="missed" style="width: <?php echo 100 - $percent["surveys"]; ?>%"></div>
                                    <?php endif; ?>
                                    <div class="text">
										<?php echo $row["surveys"]; ?> / <?php echo $count["surveys"]; ?> <span>(<?php echo round($percent["surveys"]); ?>%)</span>
                                    </div>
                                </div>
                            </td>
                            <td class="<?php echo ($missing) ? "existing" : "missing"?> edit">
                            <?php if($tutorial): ?>
                            	<?php if(!empty($row["email"])): ?>
                            	<a title="Kontaktieren" href="mailto:<?php echo $row["email"] ?>"><span class="">@</span></a>
                                <?php endif; ?>
                            <?php else: ?>
                            	<a title="Auswertung" href="user-result.php?user=<?php echo $row["id"] ?>"><span class="icon-download"></span></a>
                            <?php endif; ?>
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