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
			
			$res = $mysqli->query("SELECT * FROM users WHERE users.email = '".$mysqli->real_escape_string($data["email"])."' LIMIT 1");
			
			if($mysqli->affected_rows > 0) {
				return -2;
			}
			
			$mysqli->query("
				INSERT INTO users (prename, lastname, birthday, nickname, female, email, password, admin) 
				VALUES (".null_on_empty($data["prename"]).", ".null_on_empty($data["lastname"]).", ".null_on_empty($data["birthday"]).", ".null_on_empty($data["nickname"]).", ".($data["female"] ? "true" : "false").", ".null_on_empty($data["email"]).", '".md5($data["password"])."', ".($data["admin"] ? "true" : "false").")");
			
			if($mysqli->affected_rows > 0) {
				return 1;
			}
			
			if(intval($data["tutor"])) {
				$mysqli->query("INSERT INTO teacher ( uid ) VALUES ( '".intval(($data["tutor"]))."')");
				
				if($mysqli->affected_rows > 0) {
					return 2;
				}
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
				email = ?
				WHERE id = ?
			");	
			
			$stmt->bind_param("ssissiisi",
				$data["prename"],
				$data["lastname"],
				$data["class"]["id"],
				$data["birthday"],
				$data["nickname"],
				intval($data["female"]),
				intval($data["admin"]),
				$data["email"],
				$data["id"]
			);
			
			$stmt->execute();
			$stmt->store_result();
			
			if($stmt->affected_rows == 0) {
				$stmt->free_result();
				$stmt->close();
				return -2;
			}
			
			$stmt->free_result();
			$stmt->close();
			
			if(isset($data["password"]) && !empty($data["password"])) {
				$stmt = $mysqli->prepare("
					UPDATE users SET 
					password = ?
					WHERE id = ?
				");	
				$stmt->bind_param("si", encrypt_pw($data["password"]), $id);
				
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
			
			return 0;		
			
		}
		
		function update_userdata($data) {
			global $mysqli;
			
			// TODO: Validate data
			
			$res = $mysqli->query("SELECT * FROM users WHERE users.id ='".intval($data["id"])."'");
			
			if($mysqli->affected_rows == 0) {
				return -2;
			}
			
			$mysqli->query("UPDATE users SET birthday = ".null_on_empty($data["birthday"]).", nickname = ".null_on_empty($data["nickname"])." WHERE id = '".intval($data["id"])."'");
			
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
			return "'".$mysqli->real_escape_string($var)."'";
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