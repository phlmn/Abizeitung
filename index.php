<?php 
	session_start();
	
	if(isset($_SESSION["user"])) {
		header("Location: dashboard.php");
		die;
	}
	
	require_once("functions.php");
	
	db_connect();
	
	$login_failed = false;
	
	if(isset($_POST["email"]) && isset($_POST["password"])) {
		$userID = login($_POST["email"], $_POST["password"]);
		if($userID > -1) {
			$_SESSION["user"] = $userID;
			header("Location: dashboard.php");
			die;
		}	
		else {
			$login_failed = true;
		}
	}

?>

<!DOCTYPE html>
<html>
	<head>
		<title>Abizeitung - Login</title>
		<link rel="stylesheet" href="style.css" />
	</head>
	
	<body class="login">
		<div id="login" <?php if($login_failed): ?>class="login-failed"<?php endif; ?>>
			<h1>Login</h1>
			<form action="./" method="post">
				<input placeholder="E-Mail" name="email" value="<?php echo isset($_POST["email"]) ? $_POST["email"] : "" ?>" type="email" />
				<input placeholder="Passwort" name="password" type="password" />
				<input type="submit" value="Anmelden" />			
			</form>		
		</div>	
	</body>
</html>

<?php db_close(); ?>