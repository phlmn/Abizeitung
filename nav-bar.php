<div id="nav-bar">
	<div class="container<?php if($data["admin"] == 1): ?>-fluid<?php endif; ?> inner">
		<div class="left">
			<ul>
				<li><a href="dashboard.php" class="title">Abizeitung</a></li>
                <?php if($data["isteacher"]): ?>
                <li><a href="teacher-overview.php">Nutzerstatus</a></li>
                <?php endif; ?>
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
<div id="admin-nav" class="hidden-xs">
	<h2>Administration</h2>
	<ul>
		<li>
        	<a href="users.php">Nutzer</a>
        	<ul>
        		<li><a href="users.php?group=students">Schüler</a></li>
        		<li><a href="users.php?group=teachers">Lehrer</a></li>
        		<li><a href="users.php?group=state">Status</a></li>
        		<li><a href="users.php?group=code">Aktivierungscode</a></li>
        		<li><a href="csv-import.php">Nutzer importieren</a>
        	</ul>
        </li>
	    <li><a href="tutorial.php">Tutorien</a></li>
		<li><a href="classes.php">Kurse</a></li>
		<li><a href="questions.php">Fragen</a></li>
		<li><a href="surveys.php">Umfragen</a></li>
		<li><a href="results.php">Auswertung</a></li>
        <li>
        	<a href="options.php">Einstellungen</a>
            <ul>
            	<li><a href="options.php?group=options">Optionen</a></li>
            	<li><a href="options.php?group=images">Bilder Kategorien</a></li>
            </ul>
        </li>
        <?php $count_errors = db_count("error_report", "solved", "0") ?>
		<li><a href="errors.php">Fehlermeldungen<?php if($count_errors > 0): ?><span class="activity"><?php echo $count_errors; ?></span><?php endif; ?></a></li>
	</ul>
</div>
<?php endif; ?>
