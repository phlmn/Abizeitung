<?php 
	session_start();
	
	if(isset($_SESSION["user"])) {
		header("Location: dashboard.php");
		die;
	}
	
	require_once("functions.php");
	
	db_connect();
		
	$register_failed = false;
	$activate = false;
	$id = 0;
	
	if(isset($_POST["unlock_key"])) {
		if(empty($_POST["unlock_key"])) {
			$register_failed = true;
		}
		else if(isset($_GET["activate"])) {
			global $mysqli;
			
			$stmt = $mysqli->prepare("
				SELECT id
				FROM users
				WHERE unlock_key = ?
			");
			
			$stmt->bind_param("s", null_on_empty($_POST["unlock_key"]));
			$stmt->execute();
			
			if($stmt->fetch()) {
				$activate = true;
			}
			else {
				$activate = true;
			}
			
			$stmt->close();
		}
		else if(isset($_GET["register"])) {
			if(isset($_POST["email"]) && isset($_POST["password"])) {
				if(!empty($_POST["email"]) && !empty($_POST["password"])) {
					global $mysqli;
					
					$stmt = $mysqli->prepare("
						SELECT id
						FROM users
						WHERE unlock_key = ?
					");
					
					$stmt->bind_param("s", null_on_empty($_POST["unlock_key"]));
					$stmt->execute();
					
					$stmt->bind_result($id);
					$stmt->store_result();
					
					if($stmt->fetch()) {
						if($id > 0) {
							$activate = true;
							
							$stmt2 = $mysqli->prepare("
								UPDATE users SET
									email = ?,
									password = ?,
									activated = ?,
									unlock_key = ?
								WHERE id = ?
								LIMIT 1;
							");
							
							$stmt2->bind_param(
								"ssisi",
								null_on_empty($_POST["email"]),
								encrypt_pw($_POST["password"]),
								intval(1),
								null_on_0(0),
								intval($id)
							);
							
							$stmt2->execute();
							$stmt2->close();
						}
					}
					
					$stmt->free_result();
					$stmt->close();
					
					if($activate) {
						$userID = login($_POST["email"], $_POST["password"]);
						
						if($userID >= 0) {
							$_SESSION["user"] = $userID;
							
							header("Location: ./dashboard.php");
							
							die;
						}
					}
				}
			}
			$register_failed = true;
		}
	}
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Abizeitung - Login</title>
		<?php head(); ?>
	</head>
	
	<body class="login">
		<div id="login" <?php if($register_failed): ?>class="login-failed"<?php endif; ?>>
			<h1>Registrieren</h1>
            <?php if($activate && isset($_POST["unlock_key"])): ?>
			<form action="registration.php?register" method="post">
            	<input name="unlock_key" type="hidden" value="<?php echo $_POST["unlock_key"]; ?>" />
				<input placeholder="E-Mail" name="email" type="email" />
				<input placeholder="Passwort" name="password" type="password" />
				<input type="submit" value="Anmelden" />			
			</form>
            <?php else: ?>
            <form action="registration.php?activate" method="post">
				<input placeholder="Aktivierungscode" name="unlock_key" />
				<input type="submit" value="Anmelden" />			
			</form>	

            <?php endif; ?>	
		</div>	
	</body>
</html>

<?php db_close(); ?>