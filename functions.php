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
		$res = $mysqli->query("SELECT admin FROM users WHERE id = '".$_SESSION["user"]."' LIMIT 1");

		if($mysqli->affected_rows > 0) {
		
			$row = $res->fetch_assoc();
			
			if($row["admin"] == 1)
				return;
				
		}
		
		header("Location: ./");
		die;
	
	}
	
	function get_userdata($id) {
		global $mysqli;
		$res = $mysqli->query("
			SELECT users.class, tutor, prename, lastname, birthday, nickname, admin, email, female, classes.name, classes.id AS cid, classes.tutor
			FROM users
			LEFT JOIN users_classes ON users.id = users_classes.user
			LEFT JOIN classes ON users_classes.id = classes.id OR users.class = classes.id
			WHERE users.id = '".$mysqli->real_escape_string($id)."'
			LIMIT 1
		");

		
		if($mysqli->affected_rows > 0) {
		
			$row = $res->fetch_assoc();
			
			$data["id"] 		= $id;
			$data["prename"] 	= $row["prename"];
			$data["lastname"] 	= $row["lastname"];
			$data["birthday"] 	= $row["birthday"];
			$data["nickname"] 	= $row["nickname"];
			$data["admin"] 		= $row["admin"];
			$data["email"] 		= $row["email"];
			$data["female"] 	= $row["female"];
			$data["istutor"]	= false;
			
			$res = $mysqli->query("SELECT * FROM teacher WHERE uid = '".$id."'");
		
			if($mysqli->affected_rows > 0)
				$data["istutor"] = true;
				
			if(!$data["istutor"]) {
			
				$res = $mysqli->query("
					SELECT users.id AS id
					FROM users
					LEFT JOIN teacher ON teacher.uid = users.id
					WHERE teacher.id = '".$row["tutor"]."'
				");
				
				
				$tutor = $res->fetch_assoc();
				
				$data["class"] = array(
									"name" => $row["name"],
									"id" => $row["cid"],
									"tutor" => get_userdata($tutor["id"])
								);
			}	
			
			return $data;
		}
		
	}
	
	function login($email, $password) {
		global $mysqli;
		$res = $mysqli->query("SELECT password, id FROM users WHERE email = '".$mysqli->real_escape_string($email)."' LIMIT 1");

		if($mysqli->affected_rows > 0) {
		
			$row = $res->fetch_assoc();
			
			if($row["password"] === md5($password))
				return $row["id"];
				
		}
			
		return -1;
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
		
		$res = $mysqli->query("SELECT * FROM teacher WHERE uid = '".$data["id"]."'");
		
		if($data["tutor"] == 1) {
			if($mysqli->affected_rows == 0) {
				$mysqli->query("INSERT INTO teacher ( uid ) VALUES ( '".intval(($data["id"]))."')");
				
				if($mysqli->affected_rows > 0)
					return -1;
			}
		} else {
			$mysqli->query("DELETE FROM teacher WHERE uid = '".$data["id"]."'");
		}
		
		$class = intval($mysqli->query("SELECT id FROM classes WHERE name = '".$mysqli->real_escape_string($data["class"])."'"));
		
		if($mysqli->affected_rows == 0)
			$class = 0;
		
		$mysqli->query("
			UPDATE users SET 
				prename 	= '".$mysqli->real_escape_string($data["prename"])."',
				lastname 	= '".$mysqli->real_escape_string($data["lastname"])."',
				class 		= '".$class."',
				birthday 	= '".$mysqli->real_escape_string($data["birthday"])."',
				nickname 	= '".$mysqli->real_escape_string($data["nickname"])."',
				female 		= '".intval($data["female"])."',
				admin 		= '".intval($data["admin"])."',
				".change_password($data["password"]).",
				email 		= '".$mysqli->real_escape_string($data["email"])."'
			WHERE id 		= '".intval($data["id"])."'");
			
		if($mysqli->affected_rows > 0) {
			return -2;
		}
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
	
	function null_on_empty($var) {
		
		global $mysqli;
		
		if(empty($var)) {
			return "NULL";	
		}
		else {
			return "'".$mysqli->real_escape_string($var)."'";
		}
	}
	
	function change_password($pw) {
		if(isset($pw))
			return " password = '".md5($pw)."' ";
	}
?>