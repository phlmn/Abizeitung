<?php
	session_start();
	
	require_once("functions.php");
	
	db_connect();
	check_login();
	check_admin(); 

	$data = UserManager::get_userdata($_SESSION["user"]);	

	if(isset($_GET["editclass"])) {
		global $mysqli;
?>
		<div class="modal-dialog">
        	<div class="modal-content">
            	<form id="modal-form" method="post" action="classes.php?action=<?php echo ($_GET["editclass"]) ? "update&class=" . intval($_GET["editclass"]) : "new" ?>"></form>
                    <div class="modal-header">
                        <button type="button" class="close" form="modal-form" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4>Kurs</h4>
                    </div>
                    <div class="modal-body">
                        <?php
							$select["teacher"] = 0;
							$select["name"] = "";
							
							if($_GET["editclass"]) {
								$stmt = $mysqli->prepare("
									SELECT name, teacher
									FROM classes
									WHERE id = ?
									LIMIT 1
								");
								
								$stmt->bind_param("i", intval($_GET["editclass"]));
								$stmt->execute();
								
								$stmt->bind_result($select["name"], $select["teacher"]);
								$stmt->fetch();
								
								$stmt->close();
							}
						?>
                        <input type="text" name="classname" form="modal-form" value="<?php echo $select["name"]?>" placeholder="Kursname"/>
                        <select name="teacher" form="modal-form">
                        	<option value="0">-</option>
                            <?php
								$stmt = $mysqli->prepare("
									SELECT teachers.id, users.lastname
									FROM teachers
									INNER JOIN users ON teachers.uid = users.id
								");
								
								$stmt->execute();
								$stmt->bind_result($teacher["id"], $teacher["name"]);
								
								while($stmt->fetch()) :
							?>
                            <option value="<?php echo $teacher["id"] ?>"<?php if($teacher["id"] == $select["teacher"]): ?> selected<?php endif; ?>><?php echo $teacher["name"] ?></option>
                            <?php 
								endwhile;
								
								$stmt->close();
							?>
                        </select>
                    </div>
                    <div class="modal-footer">
                    <?php if($_GET["editclass"]) : ?>
                    	<button type="button" class="btn btn-default delete" onClick="javascript:void(window.location='classes.php?action=delete&class=<?php echo $_GET["editclass"]; ?>')" data-dismiss="modal">Löschen</button>
                    <?php endif; ?>
                    	<button type="button" class="btn btn-default" form="modal-form" data-dismiss="modal">Schließen</button>
                    	<button type="submit" class="btn btn-default" form="modal-form">Speichern</button>
                    </div>
                </form>
        	</div>
        </div>
<?php

	die;
}

if(isset($_GET["action"])) {
	if($_GET["action"] == "new") {
		global $mysqli;
		
		$stmt = $mysqli->prepare("
			INSERT INTO classes (
				name, teacher
			) VALUES (
				?, ?
			)
		");
		
		$stmt->bind_param("si", null_on_empty($_POST["classname"]), intval($_POST["teacher"]));
		$stmt->execute();
		
		$stmt->close();
		db_close();
		
		header("Location: ./classes.php?saved");
		
		die;
	}
	else if($_GET["action"] == "update") {
		global $mysqli;
		
		$stmt = $mysqli->prepare("
			UPDATE classes
			SET
				name = ?,
				teacher = ?
			WHERE id = ?
			LIMIT 1
		");
		
		$stmt->bind_param("sii", null_on_empty($_POST["classname"]), intval($_POST["teacher"]), intval($_GET["class"]));
		$stmt->execute();
		
		$stmt->close();
		db_close();
		
		header("Location: ./classes.php?saved");
		
		die;
	}
	else if($_GET["action"] == "delete") {
		global $mysqli;
		
		$stmt = $mysqli->prepare("
			DELETE FROM classes
			WHERE id = ?
			LIMIT 1
		");
		
		$stmt->bind_param("i", intval($_GET["class"]));
		$stmt->execute();
		
		$stmt->close();
		db_close();
		
		header("Location: ./classes.php?saved");
		
		die;
	}
}

if(isset($_GET["class"])) {
	global $mysqli;
	
	$classId = intval($_GET["class"]);
	
	$stmt = $mysqli->prepare("
		SELECT name 
		FROM classes
		WHERE id = ?
		LIMIT 1");
		
	$stmt->bind_param("i", $classId);
	$stmt->execute();
	
	$stmt->bind_result($class["name"]);
	
	$stmt->fetch();
	
	if($classId == -1) $class["name"] = "Alle Nutzer";
	$json = array("name" => $class["name"]);

	$stmt->close();
	
	if($classId == -1) {
		$stmt = $mysqli->prepare("
			SELECT users.id, users.prename, users.lastname, tutorials.name, tutor.lastname 
			FROM students
			LEFT JOIN users ON students.uid = users.id
			LEFT JOIN tutorials ON students.tutorial = tutorials.id
			LEFT JOIN teachers ON tutorials.tutor = teachers.id
			LEFT JOIN users AS tutor ON teachers.uid = tutor.id
			ORDER BY users.lastname
		");	
	}
	else {
		$stmt = $mysqli->prepare("
			SELECT users.id, users.prename, users.lastname, tutorials.name, tutor.lastname 
			FROM students
			LEFT JOIN users ON students.uid = users.id
			LEFT JOIN students_classes ON users.id = students_classes.student
			LEFT JOIN tutorials ON students.tutorial = tutorials.id
			LEFT JOIN classes ON students_classes.id = classes.id
			LEFT JOIN teachers ON tutorials.tutor = teachers.id
			LEFT JOIN users AS tutor ON teachers.uid = tutor.id
			WHERE students_classes.class = ?
			ORDER BY users.lastname
		");
		
		$stmt->bind_param("i", $classId);
	}
	
	$stmt->execute();
	
	$stmt->bind_result($user["id"], $user["prename"], $user["lastname"], $user["class"], $user["tutor"]);
	
	$json["users"] = array();
	while($stmt->fetch()) {
		array_push($json["users"], array(
			"id" => $user["id"],
			"prename" => $user["prename"],
			"lastname" => $user["lastname"],
			"class" => $user["class"],
			"tutor" => $user["tutor"]
		));
	}
	
	$stmt->close();
	
	echo json_encode($json);
	exit;
}
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Abizeitung - Kursverwaltung</title>
		<?php head(); ?>
        <script type="text/javascript" src="js/groups.js"></script>
		<script type="text/javascript">
			
			setArgs("classes", "class", "class", "class-management", "data-classid", "classesModal");
			
			$(document).ready(function() {
				showGroup(-1);
			});
		</script>
	</head>
	
	<body>
		<?php require("nav-bar.php") ?>
		<div id="class-management" class="container-fluid">
			<h1>Kursverwaltung</h1>
			<form id="data_form" name="data" action="save.php"></form>
			<?php
				global $mysqli;
				$stmt = $mysqli->prepare("
					SELECT classes.id, classes.name, teachers.id, users.id, users.lastname
					FROM classes
					LEFT JOIN teachers ON classes.teacher = teachers.id
					LEFT JOIN users ON teachers.uid = users.id
					ORDER BY classes.name ASC;
				");	
				
				$stmt->execute();
				$stmt->bind_result($class["id"], $class["name"], $class["teacher"]["id"], $class["teacher"]["userid"], $class["teacher"]["lastname"]);
			?>
			<div class="row">
				<div class="col-sm-8">
					<div class="groups">					
						<div class="addGroup" onClick="javascript:void(editGroup(0))"></div>
						<?php while($stmt->fetch()): ?>
						<div data-classid="<?php echo $class["id"] ?>" onclick="showGroup(<?php echo $class["id"] ?>)">
							<div class="info">
								<div class="name"><?php echo $class["name"] ?></div>
								<div class="teacher"><?php echo $class["teacher"]["lastname"] ?></div>
							</div>
						</div>
						<?php endwhile; ?>
					</div>
				</div>
				<div class="col-sm-4">
					<div class="sidebar affix col-sm-4">
						<div class="head row">
							<div class="col-sm-6">
								<h3 class="title">Alle Nutzer</h3>
							</div>
							<div class="col-sm-6">
								<input class="form-control filter" onkeyup="filter()" type="search" placeholder="Suchen..." />
							</div>
						</div>
						<div class="users">
							<ul>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<?php $stmt->close(); ?>
            
            <div class="modal fade" id="classesModal" tabindex="-1" role="dialog" aria-hidden="true">
            </div> 

		</div>
	</body>
</html>

<?php db_close(); ?>