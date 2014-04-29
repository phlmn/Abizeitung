<?php

	require_once("./classes/cUserManager.php");
	
	function head() {
		require_once("head.php");
	}
	
	function db_connect() {
		global $mysqli;
		include_once("config.php");
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
	
	function db_count($table, $column = NULL, $where = NULL) {
		global $mysqli;
		
		if($column && $where) {
			$stmt = $mysqli->prepare("
				SELECT COUNT(*) 
				FROM " . null_on_empty($table) . "
				WHERE " . null_on_empty($column) . " = ?
			");
			echo $mysqli->error;
			$stmt->bind_param("s", null_on_empty($where));
		}
		else {
			$stmt = $mysqli->prepare("
				SELECT COUNT(*) 
				FROM " . null_on_empty($table) . "
			");
		}
		
		$stmt->execute();
		
		$stmt->store_result();
		$stmt->bind_result($count);
		
		$res = $stmt->fetch();
		
		$stmt->free_result();
		$stmt->close();
		
		if($res) {
			return $count;
		}
		else {
			return NULL;
		}
	}
	
	function get_percent($args, $precision = 2) {
		$res = array(
			"all" => 0, 
			"percent" => array(),
			"absolute" => array()
		);
		
		foreach($args as $arg) {
			$res["all"] += intval($arg);
			array_push($res["absolute"], intval($arg));
		}
		
		for($i = 0; $i < count($args); $i++) {
			$res["percent"][$i] = round($res["absolute"][$i] * 100 / $res["all"], intval($precision));
		}
		
		return $res;
	}
	
	function get_progressbar($percent, $absolute = NULL, $name = NULL) {
		for($i = 0; $i < count($percent); $i++): ?>
                	<div class="progress-bar" style="width: <?php echo $percent[$i]; ?>%;">
                    	<?php echo ($absolute) ? $absolute[$i] : $percent[$i] . "%"; ?> <?php echo $name[$i]; ?>
                    </div>
        <?php endfor;
	}
	
	function null_on_empty($var) {
		if(empty($var)) {
			return NULL;	
		}
		else {
			return $var;
		}
	}
	
	function null_on_0($var) {
		if(!$var) {
			return NULL;
		}
		else {
			return intval($var);
		}
	}
	
	function encrypt_pw($pw) {
		return password_hash($pw, PASSWORD_BCRYPT);
	}
	
	function check_pw($pw, $hash) {
		return password_verify($pw, $hash);
	}
	
	function str_rand($length) {
		if($length < 1) {
			$length = 1;
		}
		else if($length > 32) {
			return md5(rand()) . str_rand($length - 32);
		}
		
		return strtoupper(substr(md5(rand()), (rand() % (33 - $length)), $length));
	}
	
	function get_unlock_code() {
		include_once("config.php");
		
		return str_rand(UNLOCK_KEY);
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
	
	function error_report($code, $message, $page, $function, $user) {
		$connect = false;
		
		if(!DB_HOST) {
			db_connect();
			$connect = true;
		}
		
		global $mysqli;
		
		$stmt = $mysqli->prepare("
			INSERT INTO error_report (
				code, message, page, function, user
			) VALUES (
				?, ?, ?, ?, ?
			)
		");
		
		$stmt->bind_param("isssi", null_on_0($code), null_on_empty($message), null_on_empty($page), null_on_empty($function), null_on_0($user));
		$stmt->execute();
		
		$stmt->close();
		
		if($connect) {
			db_close();
		}
	}
	
?>