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
		
		if($userID >= 0) {
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
		<?php head(); ?>
	</head>
    <script type="text/javascript">
		$(document).ready(function() {
			$('#login.login-failed').effect("shake");
		});
	</script>
	
	<body class="login">
		<div id="login" <?php if($login_failed): ?>class="login-failed"<?php endif; ?>>
			<div class="col-sm-6">
				<h1>Login</h1>
				<form action="./" method="post">
					<input placeholder="E-Mail" name="email" value="<?php echo isset($_POST["email"]) ? $_POST["email"] : "" ?>" type="email" />
					<input placeholder="Passwort" name="password" type="password" />
					<input type="submit" value="Anmelden" />			
				</form>
			</div>	
			<div class="col-sm-6 registration">
				<div>oder <a href="registration.php">registriere</a> dich mit deinem Aktivierungscode.</div>
			</div>	
		</div>
	</body>
</html>

<?php db_close(); ?>