<?php
	
	function head() {
		require("head.php");
	}
	
	function db_connect() {
		global $mysqli;
		include("config.php");
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		
		if($mysqli->connect_errno)
			return -1;
		
		$mysqli->set_charset("utf8");
	}
	
	function db_close() {
		global $mysqli;
		$mysqli->close();
	}
	
	function check_login() {
		if(!isset($_SESSION["user"])) {
			header("Location: ./");
			die;
		}
	}
	
	function check_admin() {
		global $mysqli;
		
		$stmt = $mysqli->prepare("SELECT admin FROM users WHERE id = ? LIMIT 1");	
		$stmt->bind_param("i", $_SESSION["user"]);
		$stmt->execute();
		$stmt->bind_result($admin);
		$stmt->fetch();

		if($admin == 1)
			return;
		
		header("Location: ./");
		die;
	
	}
	
	class Dashboard {
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
	}
	
	class UserManager {
		function get_userdata($id) {
			global $mysqli;
			
			// Überprüfen, ob Nutzer ein Schüler ist
			
			$stmt = $mysqli->prepare("
				SELECT id
				FROM students
				WHERE uid = ?
			");
			
			$stmt->bind_param("i", $id);
			$stmt->execute();
			
			$res = $stmt->fetch();
			$stmt->close();
			
			if($res) {
				// Nutzer ist ein Schüler
				
				$stmt = $mysqli->prepare("
					SELECT users.prename, users.lastname, users.birthday, users.admin, users.email, users.female, students.tutorial
					FROM users
					INNER JOIN students ON users.id = students.uid
					WHERE users.id = ?
					LIMIT 1
				");
				
				$stmt->bind_param("i", $id);
				$stmt->execute();
				
				$stmt->bind_result($data["prename"], $data["lastname"], $data["birthday"], $data["admin"], $data["email"], $data["female"], $tutorial["id"]);
				$stmt->store_result();
				
				if($stmt->num_rows > 0) {
					$stmt->fetch();
					
					$data["id"] = $id;
					$data["isteacher"] = false;
					
					// Schüler ein Tutorium zuordnen
					
					$stmt2 = $mysqli->prepare("
						SELECT tutorials.name, teachers.uid
						FROM tutorials
						LEFT JOIN teachers ON tutorials.tutor = teachers.id
						WHERE tutorials.id = ?
						LIMIT 1
					");
					
					$stmt2->bind_param("i", $tutorial["id"]);
					$stmt2->execute();
					
					$stmt2->bind_result($tutorial["name"], $tutor["uid"]);
					$stmt2->store_result();
					
					if($stmt2->num_rows > 0) {
						$stmt2->fetch();
						
						$data["tutorial"] = array(
							"id"	=> $tutorial["id"],
							"name"	=> $tutorial["name"],
							"tutor" => UserManager::get_userdata($tutor["id"])
						);
					}
					
					$stmt2->free_result();
					$stmt2->close();
					
					$stmt->free_result();
					$stmt->close();
					
					return $data;
					
				}
				
				$stmt->free_result();
				$stmt->close();
			}
			else {
				// Überprüfen, ob Nutzer ein Lehrer ist
				
				$stmt = $mysqli->prepare("
					SELECT id
					FROM teachers
					WHERE uid = ?
				");
				
				$stmt->bind_param("i", $id);
				$stmt->execute();
				
				$res = $stmt->fetch();
				$stmt->close();
				
				if($res) {
					// Nutzer ist ein Lehrer
					
					$stmt = $mysqli->prepare("
						SELECT prename, lastname, birthday, admin, email, female
						FROM users
						WHERE id = ?
						LIMIT 1
					");
					
					$stmt->bind_param("i", $id);
					$stmt->execute();
					
					$stmt->bind_result($data["prename"], $data["lastname"], $data["birthday"], $data["admin"], $data["email"], $data["female"]);
					
					if($stmt->fetch()) {
						$data["id"] = $id;
						$data["isteacher"] = true;
						
						$stmt->close();
						
						return $data;
					}
					
					$stmt->close();
				}
			}
		}	
		
		function add_user($data) {
			global $mysqli;
			
			// Überprüfen, ob Vor- und Nachname angegeben sind
			// Falls mindestens einer leer ist, wird die Funktion verlassen
			// Vor- und Nachname dienen zur eindeutigen identifikation des Nutzers
			
			if(empty($data["prename"]) || empty($data["lastname"])) {
				return -1;
			}
			
			// Überprüfen, ob Email oder Passwort leer sind
			// Falls mindestens einer leer ist, kann sich der Nutzer nicht anmelden und wird deaktiviert
			
			$activated = true;
			
			if(empty($data["email"]) || empty($data["password"])) {
				$activated = false;
			}
			
			$stmt = $mysqli->prepare("
				SELECT id
				FROM users
				WHERE email = ?
			");
			
			$stmt->bind_param("s", null_on_empty($data["email"]));
			$stmt->execute();
			
			$stmt->fetch();
			$res = $stmt->affected_rows;
			$stmt->close();
			
			if($res > 0) {
				return -2;
			}
			
			$female = $data["female"] ? true : false;
			$admin =  $data["admin"]  ? true : false;
			
			$stmt = $mysqli->prepare("
				INSERT INTO users (
					prename, lastname, birthday, female, admin, password, email, activated
				) VALUES (
					?, ?, ?, ?, ?, ?, ?, ?
				)
			");
			
			$stmt->bind_param(
				"sssiissi",
				null_on_empty($data["prename"]),
				null_on_empty($data["lastname"]),
				null_on_empty($data["birthday"]),
				$female,
				$admin,
				encrypt_pw($data["password"]),
				null_on_empty($data["email"]),
				$activated
			);
			
			$stmt->execute();
			
			$res = $stmt->affected_rows;
			$stmt->close();
			
			if($res <= 0) {
				return 1;
			}
			
			
			$stmt = $mysqli->prepare("
				SELECT id
				FROM users
				WHERE 
					prename = ? 
				AND lastname = ?
				LIMIT 1
			");
			
			
			$stmt->bind_param("ss", null_on_empty($data["prename"]), null_on_empty($data["lastname"]));
			$stmt->execute();
			$stmt->bind_result($id);
			
			$res = $stmt->fetch();
			
			$stmt->close();
			
			if($res <= 0) {
				return 1;
			}
			
			if($data["teacher"]) {
				$stmt = $mysqli->prepare("
					INSERT INTO teachers (
						uid
					) VALUES (
						?
					)
				");
				
				$stmt->bind_param("i", intval($id));
				$stmt->execute();
				
				$res = $stmt->affected_rows;
				$stmt->close();
			} 
			else {
				$stmt = $mysqli->prepare("
					SELECT name
					FROM tutorials
					WHERE id = ?
				");
				
				$stmt->bind_param("i", intval($data["tutorial"]));
				$stmt->execute();
				
				$res = $stmt->fetch();
				$stmt->close();
				
				if(!$res) {
					$stmt = $mysqli->prepare("
						INSERT INTO students (
							uid
						) VALUES (
							?
						)
					");
					
					$stmt->bind_param("i", intval($id));
					
					$stmt->execute();
					
					$res = $stmt->affected_rows;
					$stmt->close();
				}
				else {
					$stmt = $mysqli->prepare("
						INSERT INTO students (
							uid, tutorial
						) VALUES (
							?, ?
						)
					");
					
					$stmt->bind_param("ii", intval($id), intval($data["tutorial"]));
					$stmt->execute();
					
					$res = $stmt->affected_rows;
					$stmt->close();
				}	
			}
			
			if($res == 0) {
				return 2;
			}
			
			return 0;
		}
		
		function edit_user($data) {
			global $mysqli;
			
			// Überprüfen, ob Vor- und Nachname angegeben sind
			// Falls mindestens einer leer ist, wird die Funktion verlassen
			
			if(empty($data["prename"]) || empty($data["lastname"])) {
				return -1;
			}
			
			$stmt = $mysqli->prepare("
				UPDATE users SET 
				prename = ?,
				lastname = ?,
				birthday = ?,
				female = ?,
				admin = ?,
				email = ?,
				updatetime = ?
				WHERE id = ?
				LIMIT 1
			");
			
			$stmt->bind_param("sssiissi",
				null_on_empty($data["prename"]),
				null_on_empty($data["lastname"]),
				null_on_empty($data["birthday"]),
				intval($data["female"]),
				intval($data["admin"]),
				null_on_empty($data["email"]),
				date('Y-m-d H:i:s', time()),
				intval($data["id"])
			);
			
			$stmt->execute();
			
			$res = $stmt->affected_rows;
			$stmt->close();
			
			if($res == 0) {
				return -1;
			}
			
			if($data["teacher"]) {
				// Überprüfen, ob Nutzer ein Schüler ist
				// Falls ja, wird er aus der Tabelle Schüler gelöscht
				
				$stmt = $mysqli->prepare("
					SELECT id
					FROM students
					WHERE uid = ?
				");
				
				$stmt->bind_param("i", intval($data["id"]));
				$stmt->execute();
				
				$res = $stmt->fetch();
				$stmt->close();
				
				if($res) {
					// Nutzer ist Schüler
					// Nutzer wird aus der Tabelle Schüler gelöscht
					
					$stmt = $mysqli->prepare("
						DELETE FROM students
						WHERE uid = ?
					");
					
					$stmt->bind_param("i", intval($data["id"]));
					$stmt->execute();
					
					$stmt->close();
				}
				
				// Überprüfen, ob Nutzer bereits ein Lehrer ist
				
				$stmt = $mysqli->prepare("
					SELECT id
					FROM teachers
					WHERE uid = ?
				");
				
				$stmt->bind_param("i", intval($data["id"]));
				$stmt->execute();
				
				$res = $stmt->fetch();
				$stmt->close();
				
				if(!$res) {
					// Nutzer ist kein Lehrer
					// Nutzer wird in die Tabelle Lehrer eingefügt
					
					$stmt = $mysqli->prepare("
						INSERT INTO teachers (
							uid
						) VALUES (
							?
						)
					");
					
					$stmt->bind_param("i", intval($data["id"]));
					$stmt->execute();
					
					$stmt->close();
				}
			}
			else {
				// Überprüfen, ob Nutzer ein Lehrer ist
				// Falls ja, wird er aus der Tabelle Lehrer gelöscht
				
				$stmt = $mysqli->prepare("
					SELECT id
					FROM teachers
					WHERE uid = ?
				");
				
				$stmt->bind_param("i", intval($data["id"]));
				$stmt->execute();
				
				$res = $stmt->fetch();
				$stmt->close();
				
				if($res) {
					
					// Nutzer ist Lehrer
					// Nutzer wird aus der Tabelle Lehrer gelöscht
					
					$stmt = $mysqli->prepare("
						DELETE FROM teachers
						WHERE uid = ?
					");
					
					$stmt->bind_param("i", intval($data["id"]));
					$stmt->execute();
					
					$stmt->close();
				}
				
				// Überprüfen, ob Nutzer bereits Schüler ist
				
				$stmt = $mysqli->prepare("
					SELECT id
					FROM students
					WHERE uid = ?
				");
				
				$stmt->bind_param("i", intval($data["id"]));
				$stmt->execute();
				
				$res = $stmt->fetch();
				$stmt->close();
				
				if(!$res) {
					// Nutzer ist kein Schüler
					// Nutzer wird in die Tabelle Nutzer eingefügt
					
					$stmt = $mysqli->prepare("
						SELECT name
						FROM tutorials
						WHERE id = ?
					");
					
					$stmt->bind_param("i", intval($data["tutorial"]));
					$stmt->execute();
					
					$res = $stmt->fetch();
					$stmt->close();
					
					if(!$res) {
						$stmt = $mysqli->prepare("
							INSERT INTO students (
								uid
							) VALUES (
								?
							)
						");
						
						$stmt->bind_param("i", intval($data["id"]));
						
						$stmt->execute();
						
						$res = $stmt->affected_rows;
						$stmt->close();
					}
					else {
						$stmt = $mysqli->prepare("
							INSERT INTO students (
								uid, tutorial
							) VALUES (
								?, ?
							)
						");
						
						$stmt->bind_param("ii", intval($data["id"]), intval($data["tutorial"]));
						$stmt->execute();
						
						$res = $stmt->affected_rows;
						$stmt->close();
					}
				}
			}
			
			// Überprüfen, ob das Passwort geändert werden soll
			
			if(isset($data["password"])) {
				// Überprüfen, ob ein Passwort eingegeben wurde
				
				if(!empty($data["password"])) {
					// Passwort wurde eingegeben
					// Passwort wird geändert
					
					$stmt = $mysqli->prepare("
						UPDATE users SET 
						password = ?
						WHERE id = ?
						LIMIT 1
					");
					
					$stmt->bind_param("si", encrypt_pw($data["password"]), $data["id"]);
					
					$stmt->execute();
					$res = $stmt->affected_rows;
					
					$stmt->close();
					
					// Überprüfen, ob das Passwort geändert wurde
					
					if($res == 0) {
						// Passwort wurde nicht geändert
						// Überprüfen, ob das Passwort dasselbe war
						
						$stmt = $mysqli->prepare("
							SELECT password
							FROM users
							WHERE id = ?
							LIMIT 1
						");
						
						$stmt->bind_param("i", $data["id"]);
						$stmt->execute();
						$stmt->bind_result($password);
						
						$stmt->fetch();
						$stmt->close();
						
						if(check_pw($password, $data["password"])) {
							// Die Passwörter sind nicht identisch
							// Passwort konnte nicht geändert werden
							
							return -2;
						}
					}
				}
			}
			
			return 0;		
			
		}
		
		function update_userdata($data) {
			global $mysqli;
			
			// Überprüen, ob der Nutzer vorhanden ist
			
			$stmt = $mysqli->prepare("
				SELECT id
				FROM users
				WHERE id = ?
				LIMIT 1
			");
			
			$stmt->bind_param("i", intval($data["id"]));
			$stmt->execute();
			
			$res = $stmt->fetch();
			$stmt->close();
			
			if(!$res) {
				// Nutzer ist nicht vorhanden
				
				return -2;
			}
			
			$stmt = $mysqli->prepare("
				UPDATE users
				SET
					birthday = ?
				WHERE id = ?
			");
			
			$stmt->bind_param("si", null_on_empty($data["birthday"]), intval($data["id"]));
			$stmt->execute();
			
			$res = $stmt->num_rows;
			$stmt->close();
			
			if($res > 0)
				return -1;
			else
				return 0;
		}
			
	}
	
	function login($email, $password) {
		global $mysqli;
		
		$stmt = $mysqli->prepare("
			SELECT id, password, activated
			FROM users
			WHERE email = ?
			LIMIT 1
		");
		
		$stmt->bind_param("s", $email);
		
		$stmt->execute();
		$stmt->bind_result($user["id"], $user["password"], $user["activated"]);
		
		if($stmt->fetch()) {	
			
			if(!$user["activated"]) {
				$stmt->close();
				return -2;
			}
			
			if(check_pw($password, $user["password"])) {
				$stmt->close();
				return $user["id"];
			}
		}
		
		$stmt->close();
		
		return -1;
	}
	
	
	function null_on_empty($var) {
		if(empty($var)) {
			return NULL;	
		}
		else {
			return mysql_real_escape_string($var);
		}
	}
	
	function encrypt_pw($pw) {
		return password_hash($pw, PASSWORD_BCRYPT);
	}
	
	function check_pw($pw, $hash) {
		return password_verify($pw, $hash);
	}
	
	// converting php.ini file sizes to bytes (32M)
	
	function return_ini_bytes($val) {
	    $val = trim($val);
	    $last = strtolower($val[strlen($val)-1]);
	    switch($last) {
	        case 'g':
	            $val *= 1024;
	        case 'm':
	            $val *= 1024;
	        case 'k':
	            $val *= 1024;
	    }
	
	    return $val;
	}
	
	function get_category_id($name) {
		global $mysqli;
		
		$stmt = $mysqli->prepare("
			SELECT id
			FROM categories
			WHERE name = ?
		");
		
		$stmt->bind_param("s", $name);
		$stmt->execute();
		$stmt->bind_result($id);
		
		$res = $stmt->fetch();
		$stmt->close();
		
		if(!$res)
			return -1;
			
		return $id;
	}
	
?>