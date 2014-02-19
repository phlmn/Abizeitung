<?php
	
	function db_connect() {
		global $mysqli;
		$mysqli = new mysqli("localhost", "root", "root", "abizeitung");
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
			SELECT * 
			FROM users
			LEFT JOIN users_classes ON users.id = users_classes.user
			LEFT JOIN classes ON users_classes.id = classes.id
			WHERE users.id = '".$mysqli->real_escape_string($id)."'
			LIMIT 1
		");

		
		if($mysqli->affected_rows > 0) {
		
			$row = $res->fetch_assoc();
			
			
			$data["id"] = "1";
			$data["class"] = $row["name"];
			$data["tutor"] = $row["tutor"];
			$data["prename"] = $row["prename"];
			$data["lastname"] = $row["lastname"];
			$data["birthday"] = $row["birthday"];
			$data["nickname"] = $row["nickname"];
			$data["admin"] = $row["admin"];
			$data["email"] = $row["email"];
			$data["female"] = $row["female"];	
			
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
?>