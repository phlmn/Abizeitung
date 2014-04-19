<?php
	session_start();
	
	require_once("functions.php");
	
	db_connect();
	check_login();
	check_admin(); 

	$data = UserManager::get_userdata($_SESSION["user"]);
	
	if(isset($_GET["action"])) {
		if($_GET["action"] == "new") {
	?>
        <div class="modal-dialog">
        	<div class="modal-content">
            	<form id="new_tutorial_form" method="post" action="tutorial.php?action=insert"></form>
                <div class="modal-header">
                	<button type="button" class="close" form="new_tutorial_form" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4>Neues Tutorium</h4>
                </div>
                <div class="modal-body">
                    <input type="text" name="name" form="new_tutorial_form" placeholder="Name eingeben..." />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" form="new_tutorial_form" data-dismiss="modal">Schließen</button>
                    <button type="submit" class="btn btn-default" form="new_tutorial_form">Speichern</button>
                </div>
        	</div>
        </div>
	<?php
		
			db_close();
			
			die;
		
		}
		else if($_GET["action"] == "insert") {
			global $mysqli;
			
			$stmt = $mysqli->prepare("
				INSERT INTO tutorial (
					name
				) VALUES (
					?
				)
			");
			
			$stmt->bind_param("s", null_on_empty($_POST["name"]));
			$stmt->execute();
			$stmt->close();
			
			db_close();
			
			header("Location: ./tutorial.php?saved");
			
			die;
		}
		else if($_GET["action"] == "tutor") {
			global $mysqli;
			
			$stmt = $mysqli->prepare("
				SELECT id
				FROM tutorial
			");
			
			$stmt->execute();
			$stmt->bind_result($id);
			$stmt->store_result();
			
			while($stmt->fetch()) {
				if(isset($_POST["tutorial_" . $id])) {
					$stmt2 = $mysqli->prepare("
						UPDATE tutorial
						SET tutor = ?
						WHERE id = ?
					");
					
					$stmt2->bind_param("ii", intval($_POST["tutorial_" . $id]), $id);
					$stmt2->execute();
					$stmt2->close();
				}
			}
			
			$stmt->free_result();
			$stmt->close();
			
			db_close();
			
			header("Location: ./tutorial.php?saved");
			
			die;
		}
		
	}
?>


<!DOCTYPE html>
<html>
	<head>
		<title>Abizeitung - Tutorien</title>
		<?php head(); ?>
        <script type="text/javascript">
			function addTutorial() {
				$('#tutorialModal').modal();
				$('#tutorialModal').load("tutorial.php?action=new");		
			}
		</script>
	</head>
    
    <body>
		<?php require("nav-bar.php") ?>
		<div id="questions-management" class="container">
        	<h1>Tutorien Zuordnung</h1>
        	<div class="box">
	            <h2>Tutorien</h2>
	            <form id="tutorial_form" name="tutorial" method="post" action="tutorial.php?action=tutor" ></form>
	            <table class="table table-striped">
	                <thead>
	                    <tr>
	                        <th>Tutorium</th>
	                        <th>Tutor</th>
	                    </tr>
	                </thead>
	                <tbody>
	            <?php
	                global $mysqli;
	                
	                $stmt = $mysqli->prepare("
	                    SELECT tutorial.id, tutorial.name, tutorial.tutor
	                    FROM tutorial
	                    LEFT JOIN teacher ON tutorial.tutor = teacher.id
	                    LEFT JOIN users ON teacher.uid = users.id
						ORDER BY tutorial.id ASC
	                ");
	                
	                $stmt->execute();
	                $stmt->bind_result($tutorial["id"], $tutorial["name"], $tutorial["tutor"]);
					$stmt->store_result();
	                
	                while($stmt->fetch()):
				?>
	            		<tr>
	                    	<td><?php echo $tutorial["name"] ?></td>
	                    	<td>
	                        	<select name="tutorial_<?php echo  $tutorial["id"] ?>" form="tutorial_form">
	                            	<option value="0">-</option>
	            <?php
					
						$stmt2 = $mysqli->prepare("
							SELECT teacher.id, users.lastname
							FROM teacher
							LEFT JOIN users ON teacher.uid = users.id
						");
						
						$stmt2->execute();
						$stmt2->bind_result($teacher["id"], $teacher["lastname"]);
						$stmt2->store_result();
						
						while($stmt2->fetch()):
	            ?>
	                            	<option value="<?php echo $teacher["id"]; ?>" <?php if($tutorial["tutor"] == $teacher["id"]): ?> selected<?php endif; ?>><?php echo $teacher["lastname"] ?></option>
	            <?php 	endwhile; 
						
						$stmt2->free_result();
						$stmt2->close();
				?>
	                            </select>
	                        </td>
	                    </tr>
	            <?php 
					endwhile; 
					
					$stmt->free_result();
					$stmt->close();
				?>
	                </tbody>
	            </table>
	            
	            <div class="buttons">
	            	<button type="submit" form="tutorial_form">Speichern</button>
					<a class="button" href="javascript:void(addTutorial())"><span class="icon-plus-circled"></span> Tutorium hinzufügen</a>
				</div>
        	</div>
            <div class="modal fade" id="tutorialModal" tabindex="-1" role="dialog" aria-hidden="true">
            </div>
            
        </div>
	</body>
</html>

<?php db_close(); ?>