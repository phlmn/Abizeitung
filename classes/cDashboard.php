<?php

	class Dashboard {
		function update_user_nicknames($user, $nicknameId, $accept) {
			global $mysqli;
			
			$stmt = $mysqli->prepare("
				UPDATE nicknames
				SET accepted = ?
				WHERE id = ?
				AND `to` = ?
			");
			
			$stmt->bind_param("iii", intval($accept), intval($nicknameId), intval($user));
			$stmt->execute();
			
			$res = $stmt->affected_rows;
			$stmt->close;
			
			if($mysqli->error || $res < 0)
				return true;
			else
				return false;
		}
		
		function update_user_questions($user, $question, $answer) {
			global $mysqli;
			
			$stmt = $mysqli->prepare("
				SELECT id, text
				FROM users_questions
				WHERE user = ? AND question = ?
				LIMIT 1");
			
			$stmt->bind_param("ii", $user, $question);
			$stmt->execute();
			$stmt->store_result();
			$stmt->bind_result($users_questions["id"], $users_questions["text"]);
			
			$stmt->fetch();
			
			if($stmt->num_rows > 0) {
				if(empty($answer)) {
					$stmt2 = $mysqli->prepare("
						DELETE FROM users_questions
						WHERE id = ?
						LIMIT 1");
					
					$stmt2->bind_param("i", $users_questions["id"]);
					
					$stmt2->execute();
					$stmt2->close();
				}
				else {
					$stmt2 = $mysqli->prepare("
						UPDATE users_questions
						SET text = ?
						WHERE id = ?
						LIMIT 1");
						
					$stmt2->bind_param("si", $answer, $users_questions["id"]);
					
					$stmt2->execute();
					$stmt2->close();
				}
			}
			else {
				if(!empty($answer)) {
					$stmt2 = $mysqli->prepare("
						INSERT INTO users_questions (
							user, text, question
						) VALUES (
							?, ?, ?
						)");
												
					$stmt2->bind_param("isi", $user, $answer, $question);
					$stmt2->execute();
					
					$stmt2->close();
				}
			}
			
			$stmt->free_result();
			$stmt->close();
			
			if($mysqli->error)
				return true;
			else
				return false;
		}
		
		function update_user_surveys($user, $survey, $answer) {
			global $mysqli;
			
			$stmt = $mysqli->prepare("
				SELECT id
				FROM users_surveys
				WHERE user = ? AND survey = ?
				LIMIT 1");
				
			$stmt->bind_param("ii", $user, $survey);
			$stmt->execute();
			$stmt->bind_result($user_survey["id"]);
			$stmt->store_result();
			
			if($stmt->fetch()) {
				$stmt2 = $mysqli->prepare("
					UPDATE users_surveys
					SET m = ?, w = ?
					WHERE id = ?
					LIMIT 1");
				
				$stmt2->bind_param("iii", null_on_empty($answer["male"]), null_on_empty($answer["female"]), $user_survey["id"]);
				$stmt2->execute();
				$stmt2->close();
			}
			else {
				$stmt2 = $mysqli->prepare("
					INSERT INTO users_surveys (
						user, survey, m, w
					) VALUES (
						?, ?, ?, ?
					)");
					
				$stmt2->bind_param("iiii", $user, $survey, null_on_empty($answer["male"]), null_on_empty($answer["female"]));
				$stmt2->execute();
				$stmt2->close();
			}
			
			$stmt->free_result();
			$stmt->close();
			
			if($mysqli->error)
				return true;
			else
				return false;
		}
		
		function get_modal_nicknames($data) {
			
			global $mysqli;
?>
	<div class="modal-dialog">
        	<div class="modal-content">
            	<form method="post" action="dashboard.php?nickname=new">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4>Spitzname vergeben</h4>
                    </div>
                    <div class="modal-body">
                        <input type="text" name="nickname" placeholder="Spitzname"/>
                        <select name="user">
                        <?php
							$stmt = $mysqli->prepare("
								SELECT users.id, users.prename, users.lastname
								FROM students
								LEFT JOIN users ON students.uid = users.id
								WHERE 
									NOT users.id = ?
									AND students.tutorial = ?
								ORDER BY users.lastname ASC
							");
							
							$stmt->bind_param("ii", intval($data["id"]), intval($data["tutorial"]["id"]));
							$stmt->execute();
							
							$stmt->bind_result($user["id"], $user["prename"], $user["lastname"]);
							
							while($stmt->fetch()):
                        ?>
                        	<option value="<?php echo $user["id"]; ?>"><?php echo $user["prename"] . " " . $user["lastname"]; ?></option>
                        <?php
							endwhile;
							
							$stmt->close();
						?>
                        </select>
                    </div>
                    <div class="modal-footer">
                    	<button type="button" class="btn btn-default" data-dismiss="modal">Schließen</button>
                    	<button type="submit" class="btn btn-default">Speichern</button>
                    </div>
                </form>
        	</div>
        </div>
<?php
		}
	}
	
?>
