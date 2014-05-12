<?php
	session_start();
	
	require_once("functions.php");
	
	db_connect();
	check_login();
	check_admin(); 

	$data = UserManager::get_userdata($_SESSION["user"]);	

	if(isset($_GET["editclass"])) {
	
		// edit class (alt + click)
		
		// get modal dialog
?>
		<div class="modal-dialog">
		
			<div class="modal-content">
			
				<form id="modal-form" method="post" action="classes.php?action=<?php echo ($_GET["editclass"]) ? "update&class=" . intval($_GET["editclass"]) : "new" ?>"></form>
				
				<div class="modal-header">
				
					<button type="button" class="close" form="modal-form" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4>Kurs</h4>
					
				</div><!-- .modal-header -->
				
				<div class="modal-body">
				
					<?php
						$select["teacher"] = 0;
						$select["name"] = "";
						
						// get class data from database
						
						if($_GET["editclass"]) {
							$stmt = $mysqli->prepare("
								SELECT name, teacher
								FROM classes
								WHERE id = ?
								LIMIT 1
							");
							
							$stmt->bind_param("i", $_GET["editclass"]);
							$stmt->execute();
							
							$stmt->bind_result($select["name"], $select["teacher"]);
							$stmt->fetch();
							
							$stmt->close();
						}
					?>
					
					<input type="text" name="classname" form="modal-form" value="<?php echo $select["name"]?>" placeholder="Kursname">
					
					<select name="teacher" form="modal-form">
						<option value="">-</option>
						<?php
							
							// get all teachers
								
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
					
				</div><!-- .modal-body -->
				
				<div class="modal-footer">
				
				<?php if($_GET["editclass"]) : ?>
					<button type="button" class="btn btn-default delete" onClick="void(window.location='classes.php?action=delete&class=<?php echo $_GET["editclass"]; ?>')" data-dismiss="modal">Löschen</button>
				<?php endif; ?>
					<button type="button" class="btn btn-default" form="modal-form" data-dismiss="modal">Schließen</button>
					<button type="submit" class="btn btn-default" form="modal-form">Speichern</button>
				
				</div><!-- .modal-footer -->
			
			</div><!-- .modal-content -->
		
		</div><!-- .modal-dialog -->
<?php

		die;
	}

	// handle actions
	
	if(isset($_GET["action"])) {
	
		if($_GET["action"] == "new") {
		
			// insert new class in database
			
			$stmt = $mysqli->prepare("
				INSERT INTO classes (
					name, teacher
				) VALUES (
					?, ?
				)
			");
			
			$stmt->bind_param("si", null_on_empty($_POST["classname"]), null_on_empty($_POST["teacher"]));
			$stmt->execute();
			
			$stmt->close();
			db_close();
			
			header("Location: ./classes.php?saved");
			
			die;
		}
		
		else if($_GET["action"] == "update") {
			
			// edit class
			
			$stmt = $mysqli->prepare("
				UPDATE classes
				SET
					name = ?,
					teacher = ?
				WHERE id = ?
				LIMIT 1
			");
			
			$stmt->bind_param("sii", null_on_empty($_POST["classname"]), null_on_empty($_POST["teacher"]), $_GET["class"]);
			$stmt->execute();
			
			$stmt->close();
			db_close();
			
			header("Location: ./classes.php?saved");
			
			die;
		}
		
		else if($_GET["action"] == "delete") {
		
			// delete class
			
			$stmt = $mysqli->prepare("
				DELETE FROM classes
				WHERE id = ?
				LIMIT 1
			");
			
			$stmt->bind_param("i", $_GET["class"]);
			$stmt->execute();
			
			$stmt->close();
			db_close();
			
			header("Location: ./classes.php?saved");
			
			die;
		}
		
		else if($_GET["action"] == "addToGroup") {
			
			// add user to class
			
			global $mysqli;
			
			$exists = false;
			
			$stmt = $mysqli->prepare("
				SELECT *
				FROM students_classes
				LEFT JOIN students ON students_classes.student = students.id
				LEFT JOIN users ON students.uid = users.id
				WHERE users.id = ? AND students_classes.class = ?
			");
			
			$stmt->bind_param("ii", $_POST["user"], $_POST["group"]);
			$stmt->execute();
			
			if($stmt->fetch()) $exists = true;
			
			$stmt->close();
			
			if(!$exists) {
				$stmt = $mysqli->prepare("
					INSERT INTO students_classes (student, class)
					SELECT students.id, ?
					FROM students
					LEFT JOIN users ON students.uid = users.id
					WHERE users.id = ?
				");
				
				$stmt->bind_param("ii", $_POST["group"], $_POST["user"]);
				$stmt->execute();
				
				$stmt->close();	
			}		
			
			db_close();
			die;
		}
		
		else if($_GET["action"] == "removeFromGroup") {
			
			// delete user from group
			
			$exists = false;
			
			$stmt = $mysqli->prepare("
				DELETE
				FROM students_classes
				WHERE
					(SELECT students.id FROM students
					LEFT JOIN users ON students.uid = users.id
					WHERE users.id = ?)
				AND students_classes.class = ?
				LIMIT 1
			");
			
			$stmt->bind_param("ii", $_POST["user"], $_POST["group"]);
			$stmt->execute();
					
			db_close();
			die;
		}
		
	}
	
	// handle class requests
	
	if(isset($_GET["class"])) {
	
		// show class
		
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
			
			// no class is selected
			// all users are displayed
			
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
			
			// select users from class
			
			$stmt = $mysqli->prepare("
				SELECT users.id, users.prename, users.lastname, tutorials.name, tutor.lastname 
				FROM students
				LEFT JOIN users ON students.uid = users.id
				LEFT JOIN students_classes ON students.id = students_classes.student
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
		
		// return a json file with selected users
		
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
			
			var classes = new Group();
			classes.setArgs("classes", "class", "class", "class-management", "data-classid", "classesModal");
			
			$(document).ready(function() {
				classes.initGroups();
				// show all users
				classes.showGroup(-1);
				
				classes.setAddHandler(function(actions) {
					actions.forEach(function(e) {
						$.post("classes.php?action=addToGroup", {
							group: e.group,
							user: e.user	
						});
					});
				});
				
				classes.setRemoveHandler(function(actions) {
					actions.forEach(function(e) {
						$.post("classes.php?action=removeFromGroup", {
							group: e.group,
							user: e.user	
						});
					});
				});
			});
			
		</script>		
	</head>
	
	<body>
	
		<?php require("nav-bar.php") ?>
		
		<div id="class-management" class="container-fluid admin-wrapper">
		
			<h1>Kursverwaltung</h1>
			
			<form id="data_form" name="data" action="save.php"></form>
			
			<?php
			
				// display all classes
				
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
			
			<div class="groups">
								
				<div class="circle addGroup" onclick="classes.editGroup(0)"></div>
				
				<?php while($stmt->fetch()): ?>
				
				<div class="circle" data-classid="<?php echo $class["id"] ?>" onclick="classes.showGroup(<?php echo $class["id"] ?>)">
					
					<div class="info">
						<div class="name"><?php echo $class["name"] ?></div>
						<div class="teacher"><?php echo $class["teacher"]["lastname"] ?></div>
					</div><!-- .info -->
					
				</div>
				
				<?php endwhile; ?>
				
			</div><!-- .groups -->
			
			<?php $stmt->close(); ?>
			
			<div class="sidebar affix">
			
				<div class="head clearfix">
				
					<div class="col-sm-6">
						<h3 class="title">Alle Nutzer</h3>
					</div><!-- .col-sm-6 -->
					
					<div class="col-sm-6">
						<input class="form-control filter" onkeyup="classes.filter()" type="search" placeholder="Suchen...">
					</div><!-- .col-sm-6 -->
					
				</div><!-- .head -->
				
				<div class="users">
				
					<ul></ul>
					
				</div><!-- users -->
				
			</div><!-- .sidebar -->
			
			<div class="modal fade" id="classesModal" tabindex="-1" role="dialog" aria-hidden="true"></div> 

		</div><!-- #class-management -->
		
	</body>
</html>

<?php db_close(); ?>
