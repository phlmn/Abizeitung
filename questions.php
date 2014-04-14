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
					'?'
				)");
				
			$stmt->bind_param("s", $_POST["text"]);
			
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
			
			$stmt->bind_param("si", $_POST["text"], $_GET["question"]);
			
			$stmt->execute();
			
			$stmt->close();
		}
		else if($_GET["action"] == "delete") {
			$stmt = $mysqli->prepare("
				DELETE FROM questions
				WHERE id = ?
				LIMIT 1
			");
			
			$stmt->bind_param($_GET["question"]);
			
			$stmt->execute();
			
			$stmt->close();
		}
		
		db_close();
		
		header("Location: ./questions.php");
		die;
	}
	else if(isset($_GET["question"])) {
		global $mysqli;
		
		$question = $_GET["question"];
		
		$stmt = $mysqli->prepare("
			SELECT title
			FROM questions
			WHERE id = ?
		");
		
		$stmt->bind_param("i", $_GET["question"]);
		
		$stmt->execute();
		$stmt->fetch();
		
		$stmt->bind_result($title);
		
		?>
        <form method="post" action="questions.php?question=<?php echo $question; ?>&action=<?php echo ($question) ? "new" : "update"; ?>">
            <textarea name="question" onClick="this.select()"><?php echo ($question) ?  $title : "Frage eingeben ..."; ?></textarea>
            <input type="submit" value="Speichern">
        </form>
        <?php
		
		$stmt->close();
		
		db_close();
		
		die;
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
				if(id > 0)
					$('#questionsModal').load("questions.php?question=" + id);		
			}
		</script>
	</head>
    
    <body>
		<?php require("nav-bar.php") ?>
		<div id="questions-management" class="container">
        	<h1>Fragenverwaltung</h1>
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
				");
				
				$stmt->execute();
				$stmt->bind_result($questions["id"], $questions["title"]);
				
				while($stmt->fetch()):
			?>
            		<tr>
                    	<td><?php echo $questions["title"] ?></td>
                        <td class="edit"><a href="javascript:void(showQuestion(<?php echo $questions["id"] ?>))"><span class="icon-pencil-squared"></span></a></td>
                    </tr>
            <?php endwhile; ?>
            	</tbody>
            </table>
            
            <div class="buttons">
				<a class="button" href="javascript:void(showQuestion(0))"><span class="icon-plus-circled"></span> Frage hinzufügen</a>
			</div>
            
            <div class="modal fade" id="questionsModal" tabindex="-1" role="dialog" aria-hidden="true">
            </div>
            
            
        </div>
	</body>
</html>

<?php db_close(); ?>