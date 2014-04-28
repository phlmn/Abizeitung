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
				INSERT INTO questions (
					title
				) VALUES (
					?
				)");
				
			$stmt->bind_param("s", $mysqli->real_escape_string($_POST["text"]));
			
			$stmt->execute();
			
			$stmt->close();
		}
		else if($_GET["action"] == "update") {
			$stmt = $mysqli->prepare("
				UPDATE questions
				SET title = ?
				WHERE id = ?
				LIMIT 1;
			");
			
			$stmt->bind_param("si", $mysqli->real_escape_string($_POST["text"]), intval($_GET["question"]));
			
			$stmt->execute();
			
			$stmt->close();
		}
		else if($_GET["action"] == "delete" && isset($_GET["question"])) {
			$stmt = $mysqli->prepare("
				DELETE FROM questions
				WHERE id = ?
				LIMIT 1
			");
			
			$stmt->bind_param("i", intval($_GET["question"]));
			
			$stmt->execute();
			
			$stmt->close();
		}
		
		db_close();
		
		header("Location: ./questions.php");
		die;
	}
	else if(isset($_GET["question"])) {
		global $mysqli;
		
		$question = intval($_GET["question"]);
		$title = "";
		
		if($question) {
		
			$stmt = $mysqli->prepare("
				SELECT title
				FROM questions
				WHERE id = ?
			");
			
			$stmt->bind_param("i", $question); 
			
			$stmt->execute();
			
			$stmt->bind_result($title);
			$stmt->fetch();
			
			$stmt->close();
		}
		?>
        <div class="modal-dialog">
        	<div class="modal-content">
            	<form method="post" action="questions.php?question=<?php echo $question; ?>&action=<?php echo ($question) ? "update" : "new"; ?>">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4>Frage</h4>
                    </div>
                    <div class="modal-body">
                        <textarea name="text" placeholder="Frage eingeben..."><?php if($question) echo $title; ?></textarea>
                    </div>
                    <div class="modal-footer">
                    <?php if($question) : ?>
                    	<button type="button" class="btn btn-default delete" onClick="javascript:void(window.location='questions.php?question=<?php echo $question; ?>&action=delete')" data-dismiss="modal">Löschen</button>
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
				UPDATE questions
				SET accepted = 1
				WHERE id = ?
			");
			
			$stmt->bind_param("i", intval($_GET["accept"]));
			$stmt->execute();
			
			$stmt->close();
			
			db_close();
			
			header("Location: ./questions.php?saved");
			
			die;
		}
	}
	
?>


<!DOCTYPE html>
<html>
	<head>
		<title>Abizeitung - Fragen</title>
		<?php head(); ?>
		<script type="text/javascript">
			function showQuestion(id) {
				$('#questionsModal').modal();
				$('#questionsModal').load("questions.php?question=" + id);		
			}
		</script>
	</head>
    
    <body>
		<?php require("nav-bar.php") ?>
		<div id="questions-management" class="container">
        	<h1>Fragenverwaltung</h1>
        	<div class="box questions">
	            <h2>Fragen</h2>
	            <table class="table table-striped">
	            	<thead>
	                	<tr>
	                    	<th>Frage</th>
	                        <th class="edit"></th>
	                    </tr>
	                </thead>
	                <tbody>
	        	<?php
					global $mysqli;
					
					$stmt = $mysqli->prepare("
						SELECT id, title
						FROM questions
						WHERE accepted = 1
					");
					
					$stmt->execute();
					$stmt->bind_result($questions["id"], $questions["title"]);
					
					while($stmt->fetch()):
				?>
	            		<tr>
	                    	<td><?php echo $questions["title"] ?></td>
	                        <td class="edit"><a href="javascript:void(showQuestion(<?php echo $questions["id"] ?>))"><span class="icon-pencil-squared"></span></a></td>
	                    </tr>
	            <?php 
					endwhile; 
					
					$stmt->close();
					
					$stmt = $mysqli->prepare("
						SELECT id, title
						FROM questions
						WHERE accepted = 0
					");
					
					$stmt->execute();
					$stmt->bind_result($questions["id"], $questions["title"]);
					
					while($stmt->fetch()):
				?>
	            		<tr class="inactive">
	                    	<td><?php echo $questions["title"] ?></td>
	                        <td class="edit"><a title="Frage akzeptieren" href="questions.php?accept=<?php echo $questions["id"] ?>"><span class="icon-ok-circled"></span></a></td>
	                    </tr>
	            <?php 
					endwhile; 
					
					$stmt->close();
				?>
	            	</tbody>
	            </table>
	            
	            <div class="buttons">
					<a class="button" href="javascript:void(showQuestion(0))"><span class="icon-plus-circled"></span> Frage hinzufügen</a>
				</div>
        	</div>
            <div class="modal fade" id="questionsModal" tabindex="-1" role="dialog" aria-hidden="true">
            </div> 
        </div>
	</body>
</html>

<?php db_close(); ?>