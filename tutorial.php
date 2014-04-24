<?php
	session_start();
	
	require_once("functions.php");
	
	db_connect();
	check_login();
	check_admin(); 

	$data = UserManager::get_userdata($_SESSION["user"]);	

	if(isset($_GET["edittutorial"])) {
		global $mysqli;
?>
		<div class="modal-dialog">
        	<div class="modal-content">
            	<form id="modal-form" method="post" action="tutorial.php?action=<?php echo ($_GET["edittutorial"]) ? "update&tutorial=" . intval($_GET["edittutorial"]) : "new" ?>"></form>
                    <div class="modal-header">
                        <button type="button" class="close" form="modal-form" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4>Tutorium</h4>
                    </div>
                    <div class="modal-body">
                        <?php
							$select["tutor"] = 0;
							$select["name"] = "";
							
							if($_GET["edittutorial"]) {
								$stmt = $mysqli->prepare("
									SELECT name, tutor
									FROM tutorial
									WHERE id = ?
									LIMIT 1
								");
								
								$stmt->bind_param("i", intval($_GET["edittutorial"]));
								$stmt->execute();
								
								$stmt->bind_result($select["name"], $select["tutor"]);
								$stmt->fetch();
								
								$stmt->close();
							}
						?>
                        <input type="text" name="tutorialname" form="modal-form" value="<?php echo $select["name"]?>" placeholder="Tutoriumsname"/>
                        <select name="tutor" form="modal-form">
                        	<option value="">-</option>
                            <?php
								$stmt = $mysqli->prepare("
									SELECT teachers.id, users.lastname
									FROM teachers
									INNER JOIN users ON teachers.uid = users.id
								");
								
								$stmt->execute();
								$stmt->bind_result($tutor["id"], $tutor["name"]);
								
								while($stmt->fetch()) :
							?>
                            <option value="<?php echo $tutor["id"] ?>"<?php if($tutor["id"] == $select["tutor"]): ?> selected<?php endif; ?>><?php echo $tutor["name"] ?></option>
                            <?php 
								endwhile;
								
								$stmt->close();
							?>
                        </select>
                    </div>
                    <div class="modal-footer">
                    <?php if($_GET["edittutorial"]) : ?>
                    	<button type="button" class="btn btn-default delete" onClick="javascript:void(window.location='tutorial.php?action=delete&tutorial=<?php echo $_GET["edittutorial"]; ?>')" data-dismiss="modal">Löschen</button>
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
			INSERT INTO tutorials (
				name, tutor
			) VALUES (
				?, ?
			)
		");
		
		$stmt->bind_param("si", null_on_empty($_POST["tutorialname"]), null_on_empty($_POST["tutor"]));
		$stmt->execute();
		
		$stmt->close();
		db_close();
		
		header("Location: ./tutorial.php?saved");
		
		die;
	}
	else if($_GET["action"] == "update") {
		global $mysqli;
		
		$stmt = $mysqli->prepare("
			UPDATE tutorial
			SET
				name = ?,
				tutor = ?
			WHERE id = ?
			LIMIT 1
		");
		
		$stmt->bind_param("sii", null_on_empty($_POST["tutorialname"]), intval($_POST["tutor"]), intval($_GET["tutorial"]));
		$stmt->execute();
		
		$stmt->close();
		db_close();
		
		header("Location: ./tutorial.php?saved");
		
		die;
	}
	else if($_GET["action"] == "delete") {
		global $mysqli;
		
		$stmt = $mysqli->prepare("
			DELETE FROM tutorial
			WHERE id = ?
			LIMIT 1
		");
		
		$stmt->bind_param("i", intval($_GET["tutorial"]));
		$stmt->execute();
		
		$stmt->close();
		db_close();
		
		header("Location: ./tutorial.php?saved");
		
		die;
	}
}

if(isset($_GET["tutorial"])) {
	global $mysqli;
	
	$tutorialId = intval($_GET["tutorial"]);
	
	$stmt = $mysqli->prepare("
		SELECT name 
		FROM tutorials
		WHERE id = ?
		LIMIT 1");
		
	$stmt->bind_param("i", $tutorialId);
	$stmt->execute();
	
	$stmt->bind_result($tutorial["name"]);
	
	$stmt->fetch();
	
	if($tutorialId == -1) $tutorial["name"] = "Alle Nutzer";
	$json = array("name" => $tutorial["name"]);

	$stmt->close();
	
	if($tutorialId == -1) {
		$stmt = $mysqli->prepare("
			SELECT users.id, users.prename, users.lastname, tutorium.name, tutor.lastname 
			FROM students
			LEFT JOIN users ON students.uid = users.id
			LEFT JOIN tutorials AS tutorium ON students.tutorial = tutorium.id
			LEFT JOIN teachers ON tutorium.tutor = teachers.id
			LEFT JOIN users AS tutor ON teachers.uid = tutor.id
			ORDER BY users.lastname
		");	
	}
	else {
		$stmt = $mysqli->prepare("
			SELECT users.id, users.prename, users.lastname, tutorium.name, tutor.lastname 
			FROM students
			LEFT JOIN users ON students.uid = users.id
			LEFT JOIN tutorials AS tutorium ON students.tutorial = tutorium.id
			LEFT JOIN teachers ON tutorium.tutor = teachers.id
			LEFT JOIN users AS tutor ON teachers.uid = tutor.id
			WHERE students.tutorial = ?
			ORDER BY users.lastname
		");
		
		$stmt->bind_param("i", $tutorialId);
	}
	
	$stmt->execute();
	
	$stmt->bind_result($user["id"], $user["prename"], $user["lastname"], $user["tutorial"], $user["tutor"]);
	
	$json["users"] = array();
	while($stmt->fetch()) {
		array_push($json["users"], array(
			"id" => $user["id"],
			"prename" => $user["prename"],
			"lastname" => $user["lastname"],
			"tutorial" => $user["tutorial"],
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
		<title>Abizeitung - Tutoriumsverwaltung</title>
		<?php head(); ?>
        <script type="text/javascript" src="js/groups.js"></script>
		<script type="text/javascript">
		
			setArgs("tutorial", "tutorial", "tutorial", "tutorial-management", "data-tutorialid", "tutorialModal");
			
			$(document).ready(function() {
				showGroup(-1);
			});
		</script>
	</head>
	
	<body>
		<?php require("nav-bar.php") ?>
		<div id="tutorial-management" class="container-fluid">
			<h1>Tutorienverwaltung</h1>
			<form id="data_form" name="data" action="save.php"></form>
			<?php
				global $mysqli;
				$stmt = $mysqli->prepare("
					SELECT tutorials.id, tutorials.name, teachers.id, users.id, users.lastname
					FROM tutorials
					LEFT JOIN teachers ON tutorials.tutor = teachers.id
					LEFT JOIN users ON teachers.uid = users.id
					ORDER BY tutorials.name ASC;
				");	
				
				$stmt->execute();
				$stmt->bind_result($tutorial["id"], $tutorial["name"], $tutorial["teacher"]["id"], $tutorial["teacher"]["userid"], $tutorial["teacher"]["lastname"]);
			?>
			<div class="row">
				<div class="col-sm-8">
					<div class="groups">
						<div class="addGroup" onClick="javascript:void(editGroup(0))"></div>
						<?php while($stmt->fetch()): ?>
						<div data-tutorialid="<?php echo $tutorial["id"] ?>" onclick="showGroup(<?php echo $tutorial["id"] ?>)">
							<div class="info">
								<div class="name"><?php echo $tutorial["name"] ?></div>
								<div class="teacher"><?php echo $tutorial["teacher"]["lastname"] ?></div>
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
            
            <div class="modal fade" id="tutorialModal" tabindex="-1" role="dialog" aria-hidden="true">
            </div> 

		</div>
	</body>
</html>

<?php db_close(); ?>