<div id="nav-bar">
	<div class="container inner">
		<div class="left">
			<ul>
				<li><a href="dashboard.php" class="title">Abizeitung</a></li>
			</ul>
		</div>
		<div class="right">
			<ul>
				<li class="hidden-xs"><span>Angemeldet als <?php echo $data["prename"] ?> <?php echo $data["lastname"] ?>.</span></li>
				<li><a href="logout.php">Abmelden</a></li>
			</ul>
		</div>
	</div>
</div>

<?php if($data["admin"] == 1): ?>
<div id="admin-nav">
	<h2>Administration</h2>
	<ul>
		<li><a href="users.php">Nutzer</a></li>
	    <li><a href="tutorial.php">Tutorien</a></li>
		<li><a href="classes.php">Kurse</a></li>
		<li><a href="questions.php">Fragen</a></li>
		<li><a href="surveys.php">Umfragen</a></li>
		<li><a href="results.php">Auswertung</a></li>
	</ul>
</div>
<?php endif; ?>
