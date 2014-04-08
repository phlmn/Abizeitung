<?php
	session_start();
	
	require_once("functions.php");
	
	db_connect();
	check_login();
	check_admin(); 

	$data = UserManager::get_userdata($_SESSION["user"]);	
	
?>

<?php if(isset($_GET["class"])): ?>
<div class="modal-dialog">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h4 class="modal-title"></h4>
		</div>
		<div class="modal-body">
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		</div>
	</div>
</div>
<?php exit; ?>
<?php endif; ?>


<!DOCTYPE html>
<html>
	<head>
		<title>Abizeitung - Kursverwaltung</title>
		<?php head(); ?>
		<script type="text/javascript">
			function showClass(id) {
				$('#classModal').modal();
				$('#classModal').load("classes.php?class=" + id);
			}
		</script>
	</head>
	
	<body>
		<?php require("nav-bar.php") ?>
		<div id="class-management" class="container-fluid">
			<h1>Kursverwaltung</h1>
			<form id="data_form" name="data" action="save.php"></form>
			<h2>Kurse</h2>
			<?php
				global $mysqli;
				$stmt = $mysqli->prepare("
					SELECT classes.id, classes.name, teacher.id, users.id, users.lastname
					FROM classes
					LEFT JOIN teacher ON classes.tutor = teacher.id
					LEFT JOIN users ON teacher.uid = users.id;
				");	
				
				$stmt->execute();
				$stmt->bind_result($class["id"], $class["name"], $class["teacher"]["id"], $class["teacher"]["userid"], $class["teacher"]["lastname"]);
			?>
			<div class="classes">					
				<?php while($stmt->fetch()): ?>
					<div onclick="showClass(<?php echo $class["id"] ?>)">
						<div class="info">
							<div class="name"><?php echo $class["name"] ?></div>
							<div class="teacher"><?php echo $class["teacher"]["lastname"] ?></div>
						</div>
					</div>
				<?php endwhile; ?>
			</div>
						
			<div class="buttons">
				<a class="button" href="add-user.php"><span class="icon-plus-circled"></span> Kurs erstellen</a>
			</div>

		</div>	

		<div class="modal fade" id="classModal" tabindex="-1" role="dialog" aria-hidden="true"></div>
	</body>
</html>

<?php db_close(); ?>