<?php
	session_start();
	
	require_once("functions.php");
	
	if(isset($_GET["action"])) {
		if($_GET["action"] == "install") {
		
			file_put_contents("config.php", "<?php
	define('DB_HOST', '".mysql_real_escape_string($_POST['db-host'])."');
	define('DB_USER', '".mysql_real_escape_string($_POST['db-user'])."');
	define('DB_NAME', '".mysql_real_escape_string($_POST['db-name'])."');
	define('DB_PASSWORD', '".mysql_real_escape_string($_POST['db-password'])."');
?>");
			
			db_connect();
	
			$res = $mysqli->query("
				CREATE TABLE `classes` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `name` varchar(45) DEFAULT NULL,
				  `tutor` int(11) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
				
				
				
			$res = $mysqli->query("
				CREATE TABLE `questions` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `title` varchar(255) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
				
			$res = $mysqli->query("
				CREATE TABLE `subjects` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `name` varchar(45) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
				
			$res = $mysqli->query("
				CREATE TABLE `surveys` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `title` varchar(255) DEFAULT NULL,
				  `m` tinyint(4) DEFAULT NULL,
				  `w` tinyint(4) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
				
			$res = $mysqli->query("
				CREATE TABLE `user_questions` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `user` int(11) NOT NULL,
				  `text` text,
				  `question` int(11) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
				
			$res = $mysqli->query("
				CREATE TABLE `user_surveys` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `user` int(11) NOT NULL,
				  `survey` int(11) NOT NULL,
				  `m` int(11) DEFAULT NULL,
				  `w` int(11) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
				
			$res = $mysqli->query("
				CREATE TABLE `users` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `prename` varchar(45) DEFAULT NULL,
				  `lastname` varchar(45) DEFAULT NULL,
				  `class` int(11) DEFAULT NULL,
				  `birthday` varchar(45) DEFAULT NULL,
				  `nickname` varchar(45) DEFAULT NULL,
				  `female` tinyint(4) DEFAULT NULL,
				  `admin` tinyint(4) DEFAULT '0',
				  `password` varchar(45) NOT NULL,
				  `email` varchar(45) NOT NULL,
				  `updatetime` int(11) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
				
			$res = $mysqli->query("
				CREATE TABLE `teacher` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `uid` int(11) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
				
			$res = $mysqli->query("
				CREATE TABLE `students` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `uid` int(11) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
				
			$res = $mysqli->query("
				CREATE TABLE `users_classes` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `user` int(11) NOT NULL,
				  `class` int(11) NOT NULL,
				  PRIMARY KEY (`id`)
				  ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
				
			$res = $mysqli->query("
				CREATE TABLE `categories` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `name` varchar(45) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
				
			$res = $mysqli->query("
				CREATE TABLE `images` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `uid` int(11) NOT NULL,
				  `category` int(11) NOT NULL,
				  `file` varchar(255)
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
			
			$stmt = $mysqli->prepare("
				INSERT INTO `users` (
					`prename`, `lastname`, `admin`, `password`, `email`, `updatetime`
				) VALUES (
					?, ?, ?, ?, ?, ?
				)
			");
			
			$stmt->bind_param(
				"ssissi",
				null_on_empty($_POST["admin-prename"]),
				null_on_empty($_POST["admin-name"]),
				intval(1),
				encrypt_pw($_POST["admin-pw"]),
				null_on_empty($_POST["admin-mail"]),
				intval(time())
			);
				
			$stmt->execute();
			$stmt->close();
			
			$stmt = $mysqli->prepare("
				SELECT id
				FROM users
				WHERE email = ?
				LIMIT 1
			");
			
			$stmt->bind_param("s", $mysqli->real_escape_string($_POST["admin-mail"]));
			$stmt->execute();
			
			$stmt->bind_result($id);
			$stmt->fetch();
			
			$stmt->close();
			
			$stmt = $mysqli->prepare("
				INSERT INTO students (
					uid
				) VALUES (
					?
				)
			");
			
			$stmt->bind_param("i", $id);
			$stmt->execute();
			
			$stmt->close();
		}
	}

?>

<!DOCTYPE html>
<html>
	<head>
		<title>Abizeitung - Installation</title>
		<link rel="stylesheet" href="style.css">
		<link rel="stylesheet" href="icons/css/fontello.css">
	    <!--[if IE 7]>
	    <link rel="stylesheet" href="icons/css/fontello-ie7.css">
	    <![endif]-->
		<meta charset="utf-8">
		<script src="jquery.js" type="text/javascript"></script>
	</head>
	
	<body>
		<form action="install.php?action=install" method="post">
			<input name="db-host" type="text" placeholder="DB Host" />
			<input name="db-user" type="text" placeholder="DB Nutzer" />
			<input name="db-password" type="password" placeholder="DB Passwort" />
			<input name="db-name" type="text" placeholder="DB Name" />
			<input name="admin-prename" type="text" placeholder="Admin Vorname" />
			<input name="admin-name" type="text" placeholder="Admin Nachname" />
			<input name="admin-mail" type="text" placeholder="Admin E-Mail" />
			<input name="admin-pw" type="password" placeholder="Admin Passwort" />
			<input type="submit" value="Installieren" />
		</form>
	</body>
	
</html>
