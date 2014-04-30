<?php
	session_start();
	
	require_once("functions.php");
	
	if(isset($_GET["action"])) {
		if($_GET["action"] == "install") {
			if(!file_exists("config.php")) {
			
				if(isset($_GET["db"])) {
					$db_host 		= mysql_real_escape_string($_GET['db-host']);
					$db_user 		= mysql_real_escape_string($_GET['db-user']);
					$db_name 		= mysql_real_escape_string($_GET['db-name']);
					$db_password 	= mysql_real_escape_string($_GET['db-password']);
					
					$admin_prename 	= null_on_empty($_GET['admin-prename']);
					$admin_name		= null_on_empty($_GET['admin-name']);
					$admin_mail		= null_on_empty($_GET['admin-mail']);
					$admin_pw = $_GET['admin-pw'];
				}
				else {
				
					$db_host 		= mysql_real_escape_string($_POST['db-host']);
					$db_user 		= mysql_real_escape_string($_POST['db-user']);
					$db_name 		= mysql_real_escape_string($_POST['db-name']);
					$db_password 	= mysql_real_escape_string($_POST['db-password']);
					
					$admin_prename 	= null_on_empty($_POST['admin-prename']);
					$admin_name		= null_on_empty($_POST['admin-name']);
					$admin_mail		= null_on_empty($_POST['admin-mail']);
					$admin_pw = $_POST['admin-pw'];
					
					$unlock_key = null_on_0($_POST['unlock-key']);
					
					if($unlock_key < 5) {
						$unlock_key = 5;
					}
					
					file_put_contents(
						"config.php", 
						"<?php
		define('DB_HOST', '" . $db_host ."');
		define('DB_USER', '" . $db_user . "');
		define('DB_NAME', '" . $db_name . "');
		define('DB_PASSWORD', '" 	. $db_password 	. "');
		
		define('UNLOCK_KEY', '" 	. $unlock_key 	. "');
	?>");
					
				}
				
				if(db_connect() == -1) {
					if(mysql_connect($db_host, $db_user, $db_password))
						if(mysql_query("CREATE DATABASE IF NOT EXISTS `" . $db_name . "` DEFAULT CHARACTER SET utf8 ;")) {
							
							$db_host 		= "&db-host=" 		. $db_host;
							$db_user 		= "&db-user=" 		. $db_user;
							$db_name 		= "&db-name=" 		. $db_name;
							$db_password 	= "&db-password=" 	. $db_password;
							
							$admin_prename 	= "&admin-prename=" . $admin_prename;
							$admin_name 	= "&admin-name="	. $admin_name;
							$admin_mail 	= "&admin-mail="	. $admin_mail;
							$admin_pw = "&admin-pw=". $admin_pw;
							
							db_close();
							
							header("Location: ./install.php?action=install&db" . $db_host . $db_user . $db_name . $db_password . $admin_prename . $admin_name . $admin_mail . $admin_pw);
							
							die;
						}
						
					db_close();
						
					header("Location: ./install.php?error=database");
					
					die;
				}
				
				$res = $mysqli->multi_query("
					-- TABLE CATEGORIES --
					
						CREATE TABLE IF NOT EXISTS `categories` (
						  `id` 			INT(11) 	NOT NULL 	AUTO_INCREMENT,
						  `name` 		VARCHAR(45) NULL		DEFAULT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE = InnoDB;
						
					-- TABLE USERS --
						
						CREATE TABLE IF NOT EXISTS `users` (
						  `id` 			INT(11) 	NOT NULL 	AUTO_INCREMENT,
						  `prename` 	VARCHAR(45) NULL		DEFAULT NULL,
						  `lastname` 	VARCHAR(45) NULL		DEFAULT NULL,
						  `birthday` 	VARCHAR(45) NULL 		DEFAULT NULL,
						  `female` 		BOOLEAN 	NULL 		DEFAULT NULL,
						  `admin` 		BOOLEAN 	NULL 		DEFAULT FALSE,
						  `password` 	VARCHAR(128) NULL 		DEFAULT NULL,
						  `email` 		VARCHAR(45) NULL 		DEFAULT NULL,
						  `updatetime` 	TIMESTAMP 	NOT NULL 	DEFAULT CURRENT_TIMESTAMP,
						  `activated` 	BOOLEAN		NOT NULL 	DEFAULT TRUE,
						  `unlock_key` 	VARCHAR(45) NULL,
						  PRIMARY KEY (`id`),
						  UNIQUE INDEX `email_UNIQUE` (`email` ASC),
						  UNIQUE INDEX `unlock_key_UNIQUE` (`unlock_key` ASC)
						) ENGINE = InnoDB;
						
					-- TABLE TEACHERS --
	
						CREATE TABLE IF NOT EXISTS `teachers` (
						  `id` 			INT(11) 	NOT NULL 	AUTO_INCREMENT,
						  `uid` 		INT(11) 	NOT NULL,
						  PRIMARY KEY (`id`),
						  
						  INDEX `fk_teacher_users_idx` (`uid` ASC),
						  CONSTRAINT `fk_teacher_users`
							FOREIGN KEY (`uid`)
							REFERENCES `users` (`id`)
							ON DELETE CASCADE
							ON UPDATE CASCADE
						) ENGINE = InnoDB;
						
					-- TABLE CLASSES --
					
						CREATE TABLE IF NOT EXISTS `classes` (
						  `id` 			INT(11) 	NOT NULL 	AUTO_INCREMENT,
						  `name` 		VARCHAR(45) NULL 		DEFAULT NULL,
						  `teacher` 	INT(11) 	NULL 		DEFAULT NULL,
						  PRIMARY KEY (`id`),
						  
						  INDEX `fk_classes_teacher1_idx` (`teacher` ASC),
						  CONSTRAINT `fk_classes_teacher1`
							FOREIGN KEY (`teacher`)
							REFERENCES `teachers` (`id`)
							ON DELETE SET NULL
							ON UPDATE CASCADE
						) ENGINE = InnoDB;
						
					-- TABLE IMAGES --
	
						CREATE TABLE IF NOT EXISTS `images` (
						  `id` 			INT(11) 	NOT NULL 	AUTO_INCREMENT,
						  `uid` 		INT(11) 	NULL		DEFAULT NULL,
						  `file` 		VARCHAR(255) NOT NULL,
						  `category` 	INT(11) 	NULL 		DEFAULT NULL,
						  `uploadtime` 	TIMESTAMP 	NOT NULL 	DEFAULT CURRENT_TIMESTAMP,
						  PRIMARY KEY (`id`),
						  
						  INDEX `fk_images_categories1_idx` (`category` ASC),
						  INDEX `fk_images_users1_idx` (`uid` ASC),
						  CONSTRAINT `fk_images_categories1`
							FOREIGN KEY (`category`)
							REFERENCES `categories` (`id`)
							ON DELETE SET NULL
							ON UPDATE CASCADE,
						  CONSTRAINT `fk_images_users1`
							FOREIGN KEY (`uid`)
							REFERENCES `users` (`id`)
							ON DELETE SET NULL
							ON UPDATE CASCADE
						) ENGINE = InnoDB;
						
					-- TABLE QUESTION --
					
						CREATE TABLE IF NOT EXISTS `questions` (
						  `id` 			INT(11) 		NOT NULL 	AUTO_INCREMENT,
						  `title` 		VARCHAR(255) 	NOT NULL,
						  `user` 		INT(11) 		NULL 		DEFAULT NULL,
						  `accepted` 	TINYINT(1) 		NOT NULL 	DEFAULT 1,
						  PRIMARY KEY (`id`),
						  
						  INDEX `fk_questions_users1_idx` (`user` ASC),
						  CONSTRAINT `fk_questions_users1`
							FOREIGN KEY (`user`)
							REFERENCES `users` (`id`)
							ON DELETE SET NULL
							ON UPDATE CASCADE
						) ENGINE = InnoDB;
						
					-- TABLE TUTORIALS --
					
						CREATE TABLE IF NOT EXISTS `tutorials` (
						  `id` 			INT(11) 	NOT NULL 	AUTO_INCREMENT,
						  `name` 		VARCHAR(45) NULL 		DEFAULT NULL,
						  `tutor` 		INT(11) 	NULL 		DEFAULT NULL,
						  PRIMARY KEY (`id`),
						  
						  INDEX `fk_tutorial_teacher1_idx` (`tutor` ASC),
						  CONSTRAINT `fk_tutorial_teacher1`
							FOREIGN KEY (`tutor`)
							REFERENCES `teachers` (`id`)
							ON DELETE SET NULL
							ON UPDATE CASCADE
						) ENGINE = InnoDB;
						
					-- TABLE STUDENTS --
					
						CREATE TABLE IF NOT EXISTS `students` (
						  `id` 			INT(11) 	NOT NULL 	AUTO_INCREMENT,
						  `uid` 		INT(11) 	NOT NULL,
						  `tutorial` 	INT(11) 	NULL,
						  PRIMARY KEY (`id`),
						  
						  INDEX `fk_students_users1_idx` (`uid` ASC),
						  INDEX `fk_students_tutorial1_idx` (`tutorial` ASC),
						  CONSTRAINT `fk_students_users1`
							FOREIGN KEY (`uid`)
							REFERENCES `users` (`id`)
							ON DELETE CASCADE
							ON UPDATE CASCADE,
						  CONSTRAINT `fk_students_tutorial1`
							FOREIGN KEY (`tutorial`)
							REFERENCES `tutorials` (`id`)
							ON DELETE SET NULL
							ON UPDATE CASCADE
						) ENGINE = InnoDB;
						
					-- TABLE SURVEYS --
	
						CREATE TABLE IF NOT EXISTS `surveys` (
						  `id` 			INT(11) 	NOT NULL 	AUTO_INCREMENT,
						  `title` 		VARCHAR(255) NOT NULL,
						  `m` 			BOOLEAN 	NULL 		DEFAULT TRUE,
						  `w` 			BOOLEAN 	NULL 		DEFAULT TRUE,
						  `user` 		INT(11) 	NULL		DEFAULT NULL,
						  `accepted` 	TINYINT(1) 	NOT NULL 	DEFAULT 1,
						  PRIMARY KEY (`id`),
						  INDEX `fk_surveys_users1_idx` (`user` ASC),
						  CONSTRAINT `fk_surveys_users1`
							FOREIGN KEY (`user`)
							REFERENCES `users` (`id`)
							ON DELETE SET NULL
							ON UPDATE CASCADE
						) ENGINE = InnoDB;
						
					-- TABLE USERS_QUESTIONS --
	
						CREATE TABLE IF NOT EXISTS `users_questions` (
						  `id` 			INT(11) 	NOT NULL 	AUTO_INCREMENT,
						  `user` 		INT(11) 	NOT NULL,
						  `text` 		TEXT 		NULL 		DEFAULT NULL,
						  `question` 	INT(11) 	NOT NULL,
						  PRIMARY KEY (`id`),
						  
						  INDEX `fk_user_questions_questions1_idx` (`question` ASC),
						  INDEX `fk_user_questions_users1_idx` (`user` ASC),
						  CONSTRAINT `fk_user_questions_questions1`
							FOREIGN KEY (`question`)
							REFERENCES `questions` (`id`)
							ON DELETE CASCADE
							ON UPDATE CASCADE,
						  CONSTRAINT `fk_user_questions_users1`
							FOREIGN KEY (`user`)
							REFERENCES `users` (`id`)
							ON DELETE CASCADE
							ON UPDATE CASCADE
						) ENGINE = InnoDB;
						
					-- TABLE USERS_SURVEYS --
					
						CREATE TABLE IF NOT EXISTS `users_surveys` (
						  `id` 			INT(11) 	NOT NULL 	AUTO_INCREMENT,
						  `user` 		INT(11) 	NOT NULL,
						  `survey` 		INT(11) 	NOT NULL,
						  `m` 			INT(11) 	NULL 		DEFAULT NULL,
						  `w` 			INT(11) 	NULL 		DEFAULT NULL,
						  PRIMARY KEY (`id`),
						  
						  INDEX `fk_user_surveys_surveys1_idx` (`survey` ASC),
						  INDEX `fk_user_surveys_users1_idx` (`user` ASC),
						  INDEX `fk_user_surveys_users2_idx` (`m` ASC),
						  INDEX `fk_user_surveys_users3_idx` (`w` ASC),
						  CONSTRAINT `fk_user_surveys_surveys1`
							FOREIGN KEY (`survey`)
							REFERENCES `surveys` (`id`)
							ON DELETE CASCADE
							ON UPDATE CASCADE,
						  CONSTRAINT `fk_user_surveys_users1`
							FOREIGN KEY (`user`)
							REFERENCES `users` (`id`)
							ON DELETE CASCADE
							ON UPDATE CASCADE,
						  CONSTRAINT `fk_user_surveys_users2`
							FOREIGN KEY (`m`)
							REFERENCES `users` (`id`)
							ON DELETE SET NULL
							ON UPDATE CASCADE,
						  CONSTRAINT `fk_user_surveys_users3`
							FOREIGN KEY (`w`)
							REFERENCES `users` (`id`)
							ON DELETE SET NULL
							ON UPDATE CASCADE
						) ENGINE = InnoDB;
						
					-- TABLE STUDENTS_CLASSES --
					
						CREATE TABLE IF NOT EXISTS `students_classes` (
						  `id` 			INT(11) 	NOT NULL 	AUTO_INCREMENT,
						  `student` 	INT(11) 	NOT NULL,
						  `class` 		INT(11) 	NOT NULL,
						  PRIMARY KEY (`id`),
						  
						  INDEX `fk_students_classes_classes1_idx` (`class` ASC),
						  INDEX `fk_students_classes_students1_idx` (`student` ASC),
						  CONSTRAINT `fk_students_classes_classes1`
							FOREIGN KEY (`class`)
							REFERENCES `classes` (`id`)
							ON DELETE CASCADE
							ON UPDATE CASCADE,
						  CONSTRAINT `fk_students_classes_students1`
							FOREIGN KEY (`student`)
							REFERENCES `students` (`id`)
							ON DELETE CASCADE
							ON UPDATE CASCADE
						) ENGINE = InnoDB;
						
					-- TABLE NICKNAMES --
					
						CREATE TABLE IF NOT EXISTS `nicknames` (
						  `id` 			INT 		NOT NULL 	AUTO_INCREMENT,
						  `nickname` 	VARCHAR(45) NULL 		DEFAULT NULL,
						  `from` 		INT(11) 	NOT NULL,
						  `to` 			INT(11) 	NOT NULL,
						  `accepted` 	BOOLEAN 	NOT NULL 	DEFAULT TRUE,
						  PRIMARY KEY (`id`),
						  
						  INDEX `fk_nicknames_users1_idx` (`from` ASC),
						  INDEX `fk_nicknames_users2_idx` (`to` ASC),
						  CONSTRAINT `fk_nicknames_users1`
							FOREIGN KEY (`from`)
							REFERENCES `users` (`id`)
							ON DELETE CASCADE
							ON UPDATE CASCADE,
						  CONSTRAINT `fk_nicknames_users2`
							FOREIGN KEY (`to`)
							REFERENCES `users` (`id`)
							ON DELETE CASCADE
							ON UPDATE CASCADE
						) ENGINE = InnoDB;
						
					-- TABLE ERROR_REPORT --
					
						CREATE TABLE IF NOT EXISTS `error_report` (
						  `id` 			INT(11) 	NOT NULL 	AUTO_INCREMENT,
						  `code` 		INT(11) 	NULL 		DEFAULT NULL,
						  `message` 	VARCHAR(255) NULL 		DEFAULT NULL,
						  `page`		VARCHAR(45) NOT NULL	
						  `function` 	VARCHAR(100) NULL 		DEFAULT NULL,
						  `user` 		INT(11) 	NULL 		DEFAULT NULL,
						  `time` 		TIMESTAMP 	NOT NULL 	DEFAULT CURRENT_TIMESTAMP,
						  PRIMARY KEY (`id`),
						  
						  INDEX `fk_error_report_users1_idx` (`user` ASC),
						  CONSTRAINT `fk_error_report_users1`
							FOREIGN KEY (`user`)
							REFERENCES `users` (`id`)
							ON DELETE SET NULL
							ON UPDATE CASCADE
						) ENGINE = InnoDB;
				");
				
				while($mysqli->next_result());
				
				$stmt = $mysqli->prepare("
					INSERT INTO `users` (
						prename, lastname, admin, password, email
					) VALUES (
						?, ?, ?, ?, ?
					)
				");
				
				$stmt->bind_param(
					"ssiss",
					$admin_prename,
					$admin_name,
					intval(1),
					encrypt_pw($admin_pw),
					$admin_mail
				);
					
				$stmt->execute();
				$stmt->close();
				
				$stmt = $mysqli->prepare("
					SELECT id
					FROM users
					WHERE email = ?
					LIMIT 1
				");
				
				$stmt->bind_param("s", $admin_mail);
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
				
				db_close();
				
				header("Location: ./install.php?saved");
				
				die;
			}
			else {
				header("Location: ./install.php?saved");
				
				die;
			}
		}
	}

?>

<!DOCTYPE html>
<html>
	<head>
		<title>Abizeitung - Installation</title>
		<?php head() ?>
	</head>
	
	<body>
    	<div id="installer" class="container">
        	<?php if(isset($_GET["saved"])): ?>
            <div class="alert alert-success">
            	Datenbank ist erstellt.
                <ul>
                    <li>Tabellen sind angelegt.</li>
                    <li>Admin ist erstellt.</li>
                </ul>
                <a href="./">Zur Seite</a>
            </div>
            <?php  else: if(isset($_GET["error"])): ?>
                <div class="alert alert-danger">
                	Datenbank konnte nicht erstellt werden.
                    <ul>
                    <?php
					switch($_GET["error"]) {
						case "database":
							echo "<li>Error: database</li>";
							echo "<li>Überprüfen sie den <strong>Host</strong>, den <strong>Nutzer</strong> und das <strong>Passwort</strong></li>";
							break;
					}
				?>
                	</ul>
                </div>
            <?php endif; endif; ?>
            <h1>Datenbanken installieren</h1>
            <div class="common box">
                <form action="install.php?action=install" method="post">
                	<div id="db">
                    	<h4>Datenbank</h4>
                        <input name="db-host" type="text" placeholder="DB Host" />
                        <input name="db-user" type="text" placeholder="DB Nutzer" />
                        <input name="db-password" type="password" placeholder="DB Passwort" />
                        <input name="db-name" type="text" placeholder="DB Name" />
                    </div>
                    <div id="reg">
                    	<h4>Registrierung</h4>
                        <input name="unlock-key" type="text" placeholder="Schlüssellänge" />
                    </div>
                    <div id="admin">
                    	<h4>Admin</h4>
                        <input name="admin-prename" type="text" placeholder="Admin Vorname" />
                        <input name="admin-name" type="text" placeholder="Admin Nachname" />
                        <input name="admin-mail" type="text" placeholder="Admin E-Mail" />
                        <input name="admin-pw" type="password" placeholder="Admin Passwort" />
                    </div>
                    <input type="submit" value="Installieren" />
                </form>
            </div>
        </div>
	</body>
	
</html>
