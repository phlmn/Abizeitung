<?php
	session_start();
	
	require_once("functions.php");
	
	db_connect();
	check_login();
	check_admin(); 

	$data = UserManager::get_userdata($_SESSION["user"]);	
	
?>

<?php 
if(isset($_GET["class"])) {
	global $mysqli;
	
	$classId = intval($_GET["class"]);
	
	$stmt = $mysqli->prepare("
		SELECT name 
		FROM classes
		WHERE id = ?
		LIMIT 1");
		
	$stmt->bind_param("i", intval($_GET["class"]));
	$stmt->execute();
	
	$stmt->bind_result($class["name"]);
	
	$stmt->fetch();
	
	if($classId == -1) $class["name"] = "Alle Nutzer";
	$json = array("name" => $class["name"]);

	$stmt->close();
	
	if($classId == -1) {
		$stmt = $mysqli->prepare("
			SELECT users.id, users.prename, users.lastname, tutorium.name, tutor.lastname 
			FROM users
			LEFT JOIN classes AS tutorium ON users.class = tutorium.id
			LEFT JOIN teacher ON tutorium.tutor = teacher.id
			LEFT JOIN users AS tutor ON teacher.uid = tutor.id
			ORDER BY users.lastname
		");	
	}
	else {
		$stmt = $mysqli->prepare("
			SELECT users.id, users.prename, users.lastname, tutorium.name, tutor.lastname 
			FROM users
			LEFT JOIN users_classes ON users.id = users_classes.user
			LEFT JOIN classes AS tutorium ON users.class = tutorium.id
			LEFT JOIN classes ON users_classes.id = classes.id
			LEFT JOIN teacher ON tutorium.tutor = teacher.id
			LEFT JOIN users AS tutor ON teacher.uid = tutor.id
			WHERE users_classes.class = ?
			ORDER BY users.lastname
		");
	}
	
	
	$stmt->bind_param("i", $classId);
	$stmt->execute();
	
	$stmt->bind_result($user["id"], $user["prename"], $user["lastname"], $user["class"], $user["tutor"]);
	
	$json["users"] = array();
	while($stmt->fetch()) {
		array_push($json["users"], array(
			"id" => $user["id"],
			"prename" => $user["prename"],
			"lastname" => $user["lastname"],
			"class" => $user["class"],
			"tutor" => $user["tutor"]
		));
	}
	
	$stmt->close();
	
	echo json_encode($json);
	exit;
}
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Abizeitung - Kursverwaltung</title>
		<?php head(); ?>
		<script type="text/javascript">
		
			$.expr[":"].contains = $.expr.createPseudo(function(arg) {
				return function( elem ) {
					return $(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
				};
			});
		
			var selectedClass = -1;
			
			function showClass(id) {
				if(selectedClass == id)
					id = -1;
				selectedClass = id;
				
				$.getJSON("classes.php?class=" + id, function(data) {
					$(".sidebar .head .title").text(data["name"]);
					$(".sidebar .head input.filter").val("");
					if(id != -1) $(".sidebar .head").css("background-color", $(".classes > div[data-classid='" + id + "']").css("background-color"));
					else $(".sidebar .head").css("background-color", "");
					
					$(".sidebar .users ul li").each(function(index, e) {
						$(e).css("opacity", 0);
					});
					
					setTimeout(function() {
						$(".sidebar .users ul li").each(function(index, e) {
							$(e).remove();
						});
						
						data["users"].forEach(function(e) {
							var li = $('<li><span class="name">' + e["prename"] + ' ' + e["lastname"] + '</span><span class="class">' + e["class"] + ' - ' + e["tutor"] + '</span></li>');
							
							$(li).hide(0).css("opacity", 0);
							$(".sidebar .users ul").append(li);
							$(li).draggable({
								revert: true,
								helper: "clone",
								appendTo: "#class-management",
								start: function(e, ui) {
									var count = $("#class-management div.sidebar div.users ul > li.selected").length;
									if(count > 1)
										ui.helper.html(count + " Nutzer");	
								}
							});
							$(li).click(function() {
								$(this).toggleClass("selected");	
							});	
						});
						
						$(".sidebar .users ul li").each(function(index, e) {
							$(e).show(0).css("opacity", 1);
						});
						
					}, 100);
				});
			}
			
			function filter() {
				$(".sidebar .users ul li").hide();
				$(".sidebar .users ul li:contains(" + $(".sidebar .head input.filter").val() + ")").show();
			}
			
			$(document).ready(function() {
				showClass(-1);
			});
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
						<div data-classid="<?php echo $class["id"] ?>" onclick="showClass(<?php echo $class["id"] ?>)">
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
						<div class="head row">
							<div class="col-sm-6">
								<h3 class="title">Alle Nutzer</h3>
							</div>
							<div class="col-sm-6">
								<input class="form-control filter" onkeyup="filter()" type="search" placeholder="Suchen..." />
							</div>
						</div>
						<div class="users">
							<ul>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<?php $stmt->close(); ?>

		</div>
	</body>
</html>

<?php db_close(); ?>