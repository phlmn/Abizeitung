<?php

	require_once("./classes/cUserManager.php");
	require_once("./classes/cErrorHandler.php");
	
	$errorHandler = new ErrorHandler();
	
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
	
	function db_get_option($name) {
		global $mysqli;
		
		$stmt = $mysqli->prepare("
			SELECT value
			FROM options
			WHERE name = ?
			LIMIT 1
		");
		
		$stmt->bind_param("s", $name);
		$stmt->execute();
		
		$stmt->bind_result($value);
		
		if(!$stmt->fetch()) {
			$stmt->close();
			
			return -1;
		}
		
		return $value;
		
		$stmt->close();
	}
	
	function db_set_option($name, $value) {
		global $mysqli;
		
		if(db_get_option($name) != -1) {
			$stmt = $mysqli->prepare("
				UPDATE options
				SET value = ?
				WHERE name = ?
				LIMIT 1
			");
			
			$stmt->bind_param("ss", $value, $name);
			$stmt->execute();
			
			$stmt->close();
		}
		else {
			$stmt = $mysqli->prepare("
				INSERT INTO options (
					name, value
				) VALUES (
					?, ?
				)
			");
			
			$stmt->bind_param("ss", $name, $value);
			$stmt->execute();
			
			$stmt->close();
		}
	}
	
	function db_count($table) {
		global $mysqli;
		
		$select = "*";
		$where = "";
		
		if(func_num_args() > 1) {
			$where .= " WHERE ";
			$i = 1;
			
			while(func_num_args() > $i + 1) {
				if(strpos(func_get_arg($i + 1), "NULL") !== false) {
					$where .= $mysqli->real_escape_string(func_get_arg($i++)) . " IS " . $mysqli->real_escape_string(func_get_arg($i++)) . " ";
				}
				else {
					if(strpos(func_get_arg($i + 1), "!") !== false) {
						$where .= $mysqli->real_escape_string(func_get_arg($i++)) . " <> " . $mysqli->real_escape_string(str_replace("!", "", func_get_arg($i++))) . " ";
					}
					else {
						$where .= $mysqli->real_escape_string(func_get_arg($i++)) . " = " . $mysqli->real_escape_string(func_get_arg($i++)) . " ";
					}
				}
			}
			if(func_num_args() > $i) {
				$select = $mysqli->real_escape_string(func_get_arg($i));
			}
		}
		
		$stmt = $mysqli->prepare("
			SELECT COUNT(" . $select . ") 
			FROM " . null_on_empty($table) . 
			$where
		);
		
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
	
	function get_percent($args, $precision = 2, $all = NULL) {
		$precision = pow(10, $precision);
		
		$res = array(
			"all" => 0, 
			"percent" => array(),
			"absolute" => array()
		);
		
		if(is_array($args)) {
			foreach($args as $arg) {
				$res["all"] += intval($arg);
				array_push($res["absolute"], intval($arg));
			}
			
			for($i = 0; $i < count($args); $i++) {
				$res["percent"][$i] = floor($res["absolute"][$i] * 100 * $precision / $res["all"]) / $precision;
			}
		}
		else {
			$res["all"] = intval($all);
			$res["absolute"] = intval($args);
			$res["percent"] = floor($res["absolute"] * 100 * $precision / $res["all"]) / $precision;
		}
		
		return $res;
	}
	
	function get_progressbar($percent, $absolute = NULL, $name = NULL) {
		if(is_array($percent)) {
			for($i = 0; $i < count($percent); $i++): ?>
						<div class="progress-bar" style="width: <?php echo $percent[$i]; ?>%;">
							 <?php echo $name[$i]; ?> <span class="percent"><?php echo ($absolute) ? $absolute[$i] : $percent[$i] . "%"; ?></span>
						</div>
			<?php endfor;
		}
		else { ?>
        				<div class="progress-bar" style="width: <?php echo $percent; ?>%;">
							<?php echo $name; ?> <span class="percent"><?php echo ($absolute) ? $absolute : $percent . "%"; ?></span>
						</div>
		<?php }
	}
	
	function count_files($path, $layer = 0, $onlyLastLayer = false) {
		$count = 0;
		
		if(file_exists($path)) {
			foreach(new DirectoryIterator($path) as $file) {
				if(!$file->isDot()) {
					if($file->isDir()) {
						
						if($layer > 0) {
							if($onlyLastLayer && $layer > 1) {
								count_files($path . "/" . $file->getFilename(), $layer - 1, $onlyLastLayer);
							}
							else {
								$count += count_files($path . "/" . $file->getFilename(), $layer - 1, $onlyLastLayer);
							}
						}
					}
					else {
						if($onlyLastLayer) {
							if($layer == 0) {
								$count++;
							}
						}
						else {
							$count++;
						}
					}
				}
			}
		}
		
		return $count;
	}
	
	function count_filerows($file) {
		if(is_file($file)) { 
		
			$data = file($file); 
			
			$rows = count($data);
			
			unset($data);
			
			return $rows; 
		}
		
		return NULL;
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
		return crypt($pw);
	}
	
	function check_pw($pw, $hash) {
		return (crypt($pw, $hash) === $hash);
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
	
	function get_uri_param($get, $attach = false) {
		$params = "";
		
		foreach($get as $key => $value) {
			if(!$attach) {
				$attach = true;
				$params .= "?";
			}
			else {
				$params .= "&";
			}
				
			$params .=  $key;
			
			if(!empty($value)) 
				$params .= "=" . $value;
		}
		
		return $params;
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