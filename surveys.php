<?php
	session_start();
	
	require_once("functions.php");
	
	db_connect();
	check_login();
	check_admin(); 

	$data = UserManager::get_userdata($_SESSION["user"]);
	
	if(isset($_GET["action"])) {
		global $mysqli;
		
		if($_GET["action"] == "new") {
			$stmt = $mysqli->prepare("
				INSERT INTO surveys (
					title, m, w
				) VALUES (
					?, ?, ?
				)");
				
			$stmt->bind_param("sii", $_POST["text"], intval(isset($_POST["m"])), intval(isset($_POST["w"])));
			
			$stmt->execute();
			
			$stmt->close();
		}
		else if($_GET["action"] == "update") {
			$stmt = $mysqli->prepare("
				UPDATE surveys
				SET title = ?, m = ?, w = ?
				WHERE id = ?
				LIMIT 1;
			");
			
			$stmt->bind_param("siii", $_POST["text"], intval(isset($_POST["m"])), intval(isset($_POST["w"])), intval($_GET["survey"]));
			
			$stmt->execute();
			
			$stmt->close();
		}
		else if($_GET["action"] == "delete" && isset($_GET["survey"])) {
			$stmt = $mysqli->prepare("
				DELETE FROM surveys
				WHERE id = ?
				LIMIT 1
			");
			
			$stmt->bind_param("i", intval($_GET["survey"]));
			
			$stmt->execute();
			
			$stmt->close();
		}
		
		db_close();
		
		header("Location: ./surveys.php");
		die;
	}
	else if(isset($_GET["survey"])) {
		global $mysqli;
		
		$survey = intval($_GET["survey"]);
		$title = "";
		$m = 0;
		$w = 0;
		
		if($survey) {
		
			$stmt = $mysqli->prepare("
				SELECT title, m, w
				FROM surveys
				WHERE id = ?
			");
			
			$stmt->bind_param("i", $survey); 
			
			$stmt->execute();
			
			$stmt->bind_result($title, $m, $w);
			$stmt->fetch();
			
			$stmt->close();
		}
		?>
        <div class="modal-dialog">
        	<div class="modal-content">
            	<form method="post" action="surveys.php?survey=<?php echo $survey; ?>&action=<?php echo ($survey) ? "update" : "new"; ?>">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4>Frage</h4>
                    </div>
                    <div class="modal-body">
                        <textarea name="text" placeholder="Frage eingeben..."><?php if($survey) echo $title; ?></textarea>
                    </div>
                    <div class="modal-body">
                    	<h4>Personengruppe</h4>
                        <div>
                        	<input id="male" type="checkbox" name="m" value="1" <?php if($m == 1 || !$survey): ?>checked <?php endif; ?>>
                            <label for="male">Männlich</label>
                        </div>
                        <div>
                        	<input id="female" type="checkbox" name="w" value="1" <?php if($w == 1 || !$survey): ?>checked <?php endif; ?>>
                            <label for="female">Weiblich</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                    <?php if($survey) : ?>
                    	<button type="button" class="btn btn-default delete" onClick="javascript:void(window.location='surveys.php?survey=<?php echo $survey; ?>&action=delete')" data-dismiss="modal">Löschen</button>
                    <?php endif; ?>
                    	<button type="button" class="btn btn-default" data-dismiss="modal">Schließen</button>
                    	<button type="submit" class="btn btn-default">Speichern</button>
                    </div>
                </form>
        	</div>
        </div>
        <?php
		
		
		db_close();
		
		die;
	}
	else if(isset($_GET["accept"])) {
		if(null_on_0($_GET["accept"]) > 0) {
			global $mysqli;
			
			$stmt = $mysqli->prepare("
				UPDATE surveys
				SET accepted = 1
				WHERE id = ?
			");
			
			$stmt->bind_param("i", intval($_GET["accept"]));
			$stmt->execute();
			
			$stmt->close();
			
			db_close();
			
			header("Location: ./surveys.php?saved");
			
			die;
		}
	}
	
?>


<!DOCTYPE html>
<html>
	<head>
		<title>Abizeitung - Umfragen</title>
		<?php head(); ?>
		<script type="text/javascript">
			function showSurvey(id) {
				$('#surveysModal').modal();
				$('#surveysModal').load("surveys.php?survey=" + id);		
			}
		</script>
	</head>
    
    <body>
		<?php require("nav-bar.php") ?>
		<div id="surveys-management" class="container-fluid admin-wrapper">
        	<h1>Umfragenverwaltung</h1>
        	<div class="box surveys">
	            <h2>Umfragen</h2>
	            <table class="table table-striped">
	            	<thead>
	                	<tr>
	                    	<th>Frage</th>
	                        <th class="edit"><span class="icon-male"></span></th>
	                        <th class="edit"><span class="icon-female"></span></th>
	                        <th class="edit"></th>
	                    </tr>
	                </thead>
	                <tbody>
	        	<?php
					global $mysqli;
					
					$stmt = $mysqli->prepare("
						SELECT id, title, m, w
						FROM surveys
						WHERE accepted = 1
					");
					
					$stmt->execute();
					$stmt->bind_result($surveys["id"], $surveys["title"], $surveys["m"], $surveys["w"]);
					
					while($stmt->fetch()):
				?>
	            		<tr>
	                    	<td><?php echo $surveys["title"] ?></td>
	                        <td class="edit"><?php if($surveys["m"] == 1): ?><span class="icon-ok-circled"></span><?php endif; ?></td>
	                        <td class="edit"><?php if($surveys["w"] == 1): ?><span class="icon-ok-circled"></span><?php endif; ?></td>
	                        <td class="edit"><a href="javascript:void(showSurvey(<?php echo $surveys["id"]; ?>))"><span class="icon-pencil-squared"></span></a></td>
	                    </tr>
	            <?php 
					endwhile; 
					
					$stmt->close();
					
					$stmt = $mysqli->prepare("
						SELECT id, title, m, w
						FROM surveys
						WHERE accepted = 0
					");
					
					$stmt->execute();
					$stmt->bind_result($surveys["id"], $surveys["title"], $surveys["m"], $surveys["w"]);
					
					while($stmt->fetch()):
				?>
	            		<tr class="inactive">
	                    	<td><?php echo $surveys["title"] ?></td>
	                        <td class="edit"><?php if($surveys["m"] == 1): ?><span class="icon-ok-circled"></span><?php endif; ?></td>
	                        <td class="edit"><?php if($surveys["w"] == 1): ?><span class="icon-ok-circled"></span><?php endif; ?></td>
	                        <td class="edit"><a title="Frage akzeptieren" href="surveys.php?accept=<?php echo $surveys["id"]; ?>"><span class="icon-ok-circled"></span></a></td>
	                    </tr>
	            <?php 
					endwhile; 
					
					$stmt->close();
				?>
	            	</tbody>
	            </table>
	            
	            <div class="buttons">
					<a class="button" href="javascript:void(showSurvey(0))"><span class="icon-plus-circled"></span> Umfrage hinzufügen</a>
				</div>
        	</div>
            
            <div class="modal fade" id="surveysModal" tabindex="-1" role="dialog" aria-hidden="true">
            </div>
            
        </div>
	</body>
</html>

<?php db_close(); ?>