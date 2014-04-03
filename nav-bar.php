<div id="nav-bar">
	<div class="container inner">
		<div class="left">
			<ul>
				<li><a href="dashboard.php" class="title">Abizeitung</a></li>
				<?php if($data["admin"] == 1): ?>
				<li><a href="users.php">Nutzer</a></li>
				<li><a href="classes.php">Tutorien</a></li>
				<li><a href="questions.php">Fragen</a></li>
				<li><a href="surveys.php">Umfragen</a></li>
				<?php endif; ?>
			</ul>
		</div>
		<div class="right">
			<ul>
				<li><span>Angemeldet als <?php echo $data["prename"] ?> <?php echo $data["lastname"] ?>.</span></li>
				<li><a href="logout.php">Abmelden</a></li>
			</ul>
		</div>
	</div>
</div>