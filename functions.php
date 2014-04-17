<?php
	
	function head() {
		require("head.php");
	}
	
	function db_connect() {
		global $mysqli;
		include("config.php");
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		
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
				FROM user_questions
				WHERE user = ? AND question = ?
				LIMIT 1");
			
			$stmt->bind_param("ii", $user, $question);
			$stmt->execute();
			$stmt->store_result();
			$stmt->bind_result($user_questions["id"], $user_questions["text"]);
			
			$stmt->fetch();
			
			if($stmt->num_rows > 0) {
				if(empty($answer)) {
					$stmt2 = $mysqli->prepare("
						DELETE FROM user_questions
						WHERE id = ?
						LIMIT 1");
					
					$stmt2->bind_param("i", $user_questions["id"]);
					
					$stmt2->execute();
					$stmt2->close();
				}
				else {
					$stmt2 = $mysqli->prepare("
						UPDATE user_questions
						SET text = ?
						WHERE id = ?
						LIMIT 1");
						
					$stmt2->bind_param("si", $answer, $user_questions["id"]);
					
					$stmt2->execute();
					$stmt2->close();
				}
			}
			else {
				if(!empty($answer)) {
					$stmt2 = $mysqli->prepare("
						INSERT INTO user_questions (
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
				FROM user_surveys
				WHERE user = ? AND survey = ?
				LIMIT 1");
				
			$stmt->bind_param("ii", $user, $survey);
			$stmt->execute();
			$stmt->store_result();
			$stmt->bind_result($user_survey["id"]);
			
			$stmt->fetch();
			
			if($stmt->num_rows > 0) {
				$stmt2 = $mysqli->prepare("
					UPDATE user_surveys
					SET m = ?, w = ?
					WHERE id = ?
					LIMIT 1");
				
				$stmt2->bind_param("iii", $answer["male"], $answer["female"], $user_survey["id"]);
				$stmt2->execute();
				$stmt2->close();
			}
			else {
				$stmt2 = $mysqli->prepare("
					INSERT INTO user_surveys (
						user, survey, m, w
					) VALUES (
						?, ?, ?, ?
					)");
					
				$stmt2->bind_param("iiii", $user, $survey, $answer["male"], $answer["female"]);
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

			$stmt = $mysqli->prepare("
				SELECT prename, lastname, birthday, nickname, admin, email, female, class
				FROM users
				WHERE users.id = ?
				LIMIT 1
			");
			
			$stmt->bind_param("i", $id);
			$stmt->execute();	
			
			$stmt->bind_result($data["prename"], $data["lastname"], $data["birthday"], $data["nickname"], $data["admin"], $data["email"], $data["female"], $classid);
			
			$stmt->store_result();	
			if($stmt->num_rows > 0) {
			
				$stmt->fetch();
			
				$data["id"] 		= $id;
				$data["isteacher"]	= false;
							
				$stmt2 = $mysqli->prepare("SELECT * FROM teacher WHERE uid = ?");
				$stmt2->bind_param("i", $id);
				$stmt2->execute();
				$stmt2->store_result();
				
				if($stmt2->num_rows > 0)
					$data["isteacher"] = true;
					
				$stmt2->free_result();
				$stmt2->close();
					
				if(!$data["isteacher"]) {
				
					$stmt2 = $mysqli->prepare("
						SELECT classes.name, teacher.uid
						FROM classes
						LEFT JOIN teacher ON classes.tutor = teacher.id
						WHERE classes.id = ?
					");
					$stmt2->bind_param("i", $classid);	
					$stmt2->execute();
					$stmt2->bind_result($classname, $tutorid);
					$stmt2->fetch();				
					$stmt2->close();
					
					$data["class"] = array(
						"id" => $classid,
						"name" => $classname,
						"tutor" => UserManager::get_userdata($tutorid)
					);
				}
				
				$stmt->free_result();
				$stmt->close();	
				return $data;
			}
			
			$stmt->free_result();
			$stmt->close();
			
		}	
		
		function add_user($data) {
			global $mysqli;
				
			
			if(empty($data["email"]) || empty($data["password"])) {
				return -1;
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
			
			$stmt = $mysqli->prepare("
				INSERT INTO users (
					prename, lastname, class, birthday, nickname, female, email, password, admin, updatetime
				) VALUES (
					?, ?, ?, ?, ?, ?, ?, ?, ?, ?
				)
			");
			
			$female = $data["female"] ? true : false;
			$admin =  $data["admin"]  ? true : false;
			
			$stmt->bind_param(
				"sssssissii",
				null_on_empty($data["prename"]),
				null_on_empty($data["lastname"]),
				intval($_POST["class"]),
				null_on_empty($data["birthday"]),
				null_on_empty($data["nickname"]),
				$female,
				null_on_empty($data["email"]),
				encrypt_pw($data["password"]),
				$admin,
				time()
			);
			
			$stmt->execute();
			
			$res = $stmt->affected_rows;
			$stmt->close();
			
			if($res == 0) {
				return 1;
			}
			
			
			$stmt = $mysqli->prepare("
				SELECT id
				FROM users
				WHERE email = ?
				LIMIT 1
			");
			
			
			$stmt->bind_param("s", null_on_empty($data["email"]));
			$stmt->execute();
			$stmt->bind_result($id);
			$stmt->fetch();
			
			$stmt->close();
			
			if(!$id) {
				return 1;
			}
				
			if($data["teacher"]) {
				$stmt = $mysqli->prepare("
					INSERT INTO teacher (
						uid
					) VALUES (
						?
					)
				");
			} 
			else {
				$stmt = $mysqli->prepare("
					INSERT INTO students (
						uid
					) VALUES (
						?
					)
				");
			}
				
			$stmt->bind_param("i", intval($id));
			$stmt->execute();
			
			$res = $stmt->affected_rows;
			$stmt->close();
			
			if($res == 0) {
				return 2;
			}
			
			return 0;
			
		}
		
		function edit_user($data) {
			global $mysqli;
			
			$stmt = $mysqli->prepare("
				UPDATE users SET 
				prename = ?,
				lastname = ?,
				class = ?,
				birthday = ?,
				nickname = ?,
				female = ?,
				admin = ?,
				email = ?,
				updatetime = ?
				WHERE id = ?
				LIMIT 1
			");
			
			$stmt->bind_param("ssissiisii",
				$data["prename"],
				$data["lastname"],
				$data["class"]["id"],
				$data["birthday"],
				$data["nickname"],
				intval($data["female"]),
				intval($data["admin"]),
				$data["email"],
				time(),
				$data["id"]
			);
			
			$stmt->execute();
			
			$res = $stmt->affected_rows;
			$stmt->close();
			
			if($res == 0) {
				return -1;
			}
			
			if($data["teacher"]) {
				$stmt = $mysqli->prepare("
					SELECT id
					FROM students
					WHERE uid = ?
				");
				
				$stmt->bind_param("i", intval($data["id"]));
				$stmt->execute();
				
				$res = $stmt->affected_rows;
				$stmt->close();
				
				if($res) {
					$stmt = $mysqli->prepare("
						DELETE FROM students
						WHERE uid = ?
					");
					
					$stmt->bind_param("i", intval($data["id"]));
					$stmt->execute();
					
					$stmt->close();
				}
				
				$stmt = $mysqli->prepare("
					SELECT id
					FROM teacher
					WHERE uid = ?
				");
				
				$stmt->bind_param("i", intval($data["id"]));
				$stmt->execute();
				
				$res = $stmt->affected_rows;
				$stmt->close();
				
				if($res <= 0) {
					$stmt = $mysqli->prepare("
						INSERT INTO teacher (
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
				$stmt = $mysqli->prepare("
					SELECT id
					FROM teacher
					WHERE uid = ?
				");
				
				$stmt->bind_param("i", intval($data["id"]));
				$stmt->execute();
				
				$res = $stmt->affected_rows;
				$stmt->close();
				
				if($res) {
					$stmt = $mysqli->prepare("
						DELETE FROM teacher
						WHERE uid = ?
					");
					
					$stmt->bind_param("i", intval($data["id"]));
					$stmt->execute();
					
					$stmt->close();
				}
				
				$stmt = $mysqli->prepare("
					SELECT id
					FROM students
					WHERE uid = ?
				");
				
				$stmt->bind_param("i", intval($data["id"]));
				$stmt->execute();
				
				$res = $stmt->affected_rows;
				$stmt->close();
				
				if($res <= 0) {
					$stmt = $mysqli->prepare("
						INSERT INTO students (
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
			
			if(isset($data["password"])) {
				if(!empty($data["password"])) {
					$stmt = $mysqli->prepare("
						UPDATE users SET 
						password = ?
						WHERE id = ?
						LIMIT 1
					");	
					$stmt->bind_param("si", encrypt_pw($data["password"]), $data["id"]);
					
					$stmt->execute();
					$stmt->store_result();
					
					if($stmt->affected_rows == 0) {
						$stmt->free_result();
						$stmt->close();
						return -2;
					}
					
					$stmt->free_result();
					$stmt->close();	
				}
			}
			
			return 0;		
			
		}
		
		function update_userdata($data) {
			global $mysqli;
			
			// TODO: Validate data
			
			$stmt = $mysqli->prepare("
				SELECT lastname
				FROM users
				WHERE id = ?
				LIMIT 1
			");
			
			$stmt->bind_param("i", intval($data["id"]));
			$stmt->execute();
			
			$res = $stmt->num_rows;
			$stmt->close();
			
			if($res == 0) {
				return -2;
			}
			
			$stmt = $mysqli->prepare("
				UPDATE users
				SET
					birthday = ?,
					nickname = ?
				WHERE id = ?
			");
			
			$stmt->bind_param("ssi", null_on_empty($data["birthday"]), null_on_empty($data["nickname"]), intval($data["id"]));
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
		$res = $mysqli->query("SELECT password, id FROM users WHERE email = '".$mysqli->real_escape_string($email)."' LIMIT 1");

		if($mysqli->affected_rows > 0) {
		
			$row = $res->fetch_assoc();
			
			if($row["password"] === encrypt_pw($password))
				return $row["id"];
				
		}
			
		return -1;
	}
	
	
	function null_on_empty($var) {
		
		global $mysqli;
		
		if(empty($var)) {
			return "NULL";	
		}
		else {
			return $mysqli->real_escape_string($var);
		}
	}
	
	function encrypt_pw($pw) {
		return md5($pw);
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
	
?>