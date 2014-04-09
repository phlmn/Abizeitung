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
        <?php
			global $mysqli;
			
			$stmt = $mysqli->prepare("
				SELECT name 
				FROM classes
				WHERE id = ?
				LIMIT 1");
				
			$stmt->bind_param("i", intval($_GET["class"]));
			$stmt->execute();
			
			$stmt->bind_result($class["name"]);
			
			$stmt->fetch();
		?>
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h4 class="modal-title">Kurs <?php echo $class["name"] ?></h4>
		</div>
		<div class="modal-body">
        	<div class="classes">
        <?php
			$stmt->close();
		
			$stmt = $mysqli->prepare("
				SELECT users.id, prename, lastname, nickname
				FROM users
				LEFT JOIN users_classes ON users.id = user
				WHERE users.class = ? OR users_classes.class = ?
				ORDER BY lastname ASC;
			");
			
			$stmt->bind_param("ii", intval($_GET["class"]), intval($_GET["class"]));
			$stmt->execute();
			
			$stmt->bind_result($user["id"], $user["prename"], $user["lastname"], $user["nickname"]);
			
			while($stmt->fetch()) :
			?>
            <div class="info"><?php echo $user["lastname"] ?></div>
            <?php
			endwhile;
			
			$stmt->close();
		?>
        	</div>
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
			<div class="row">
				<div class="col-sm-8">
					<div class="classes">					
						<div class="addClass"></div>
						<?php while($stmt->fetch()): ?>
						<div onclick="showClass(<?php echo $class["id"] ?>)">
							<div class="info">
								<div class="name"><?php echo $class["name"] ?></div>
								<div class="teacher"><?php echo $class["teacher"]["lastname"] ?></div>
							</div>
						</div>
						<?php endwhile; ?>
					</div>
				</div>
				<div class="col-sm-4">
					<div class="sidebar affix col-sm-4">
						<div class="filter row">
							<div class="col-sm-6">
								<h3>Alle Nutzer</h3>
							</div>
							<div class="col-sm-6">
								<input class="form-control" type="search" placeholder="Suchen..." />
							</div>
						</div>
						<div class="users">
							<ul>
							<?php
								global $mysqli;
								$res = $mysqli->query("
									SELECT users.id AS id, users.prename, users.lastname, classes.name, tutor.lastname AS tutor 
									FROM users
									LEFT JOIN users_classes ON users.id = users_classes.user
									LEFT JOIN classes ON users_classes.id = classes.id OR users.class = classes.id
									LEFT JOIN teacher ON classes.tutor = teacher.id
									LEFT JOIN users tutor ON teacher.uid = tutor.id
									ORDER BY users.lastname
								");						
								
							?>
							<?php while($row = $res->fetch_assoc()): ?>
								<li>
									<span class="name"><?php echo $row["prename"] ?> <?php echo $row["lastname"] ?></span>
									<span class="class"><?php echo $row["name"] ?> - <?php echo $row["tutor"] ?></span>
								</li>
							<?php endwhile; ?>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<?php $stmt->close(); ?>

		</div>	

		<div class="modal fade" id="classModal" tabindex="-1" role="dialog" aria-hidden="true"></div>
	</body>
</html>

<?php db_close(); ?>