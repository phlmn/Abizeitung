<?php 
	class Errors {
		
		public static function display_errors() {
			global $mysqli;
		?>
        		<table class="table table-striped errors">
					<thead>
						<th>Seite</th>
						<th>Code</th>
						<th>Funktion</th>
                        <th>Nutzer</th>
                        <th>Nachricht</th>
                        <th>Datum</th>
                        <th class="edit"></th>
					</thead>
                    <tbody>
        <?php
			$stmt = $mysqli->prepare("
				SELECT error_report.id, error_report.page, error_report.code, error_report.function, error_report.user, error_report.message, error_report.time, users.prename, users.lastname
				FROM error_report
				LEFT JOIN users ON error_report.user = users.id
				WHERE error_report.solved = 0
			");
			
			$stmt->execute();
			$stmt->bind_result($report["id"], $report["page"], $report["code"], $report["function"], $report["user"], $report["message"], $report["time"], $report["prename"], $report["lastname"]);
			
			$res = false;
			
			while($stmt->fetch()):
				$res = true;
				
				$user = 0;
				if($report["user"]) {
					$user = $report["prename"] . " " . $report["lastname"];
				}
			?>
        				<tr>
                        	<td><a href="<?php echo $report["page"]; ?>" target="_blank"><?php echo $report["page"]; ?></a></td>
                            <td><?php echo $report["code"]; ?></td>
                            <td><?php echo $report["function"]; ?></td>
                            <td><?php if($user): ?>
                            	<a href="./edit-user.php?user=<?php echo $report["user"]; ?>" target="_blank"><?php echo $user ?></a>
                                <?php else: ?>-<?php endif; ?>
                            </td>
                            <td><?php echo $report["message"]; ?></td>
                            <td><?php echo $report["time"]; ?></td>
                            <td class="edit">
                            	<a href="./error.php?action=solved&id=<?php echo $report["id"] ?>" class="icon-ok-circled" title="Gelöst"></a>
                            </td>
                        </tr>
        <?php
			endwhile;
			
			$stmt->close();
			
			if(!isset($_GET["solved"])):
				if(!$res):
		?>
        				<tr class="solved">
                        	<td colspan="7"><strong>Es liegen aktuell keine Probleme vor</strong> - Gelöste Probleme <a href="./users.php?<?php echo get_uri_param() ?>&solved">anzeigen</a></td>
                        </tr>
        <?php
				else:
		?>
        				<tr>
                        	<td colspan="7">Gelöste Probleme <a href="./errors.php?solved">einblenden</a></td>
                        </tr>
        <?php
				endif;
			endif;
			
			if(isset($_GET["solved"])):
				
				$stmt = $mysqli->prepare("
					SELECT error_report.id, error_report.page, error_report.code, error_report.function, error_report.user, error_report.message, error_report.time, users.prename, users.lastname
					FROM error_report
					LEFT JOIN users ON error_report.user = users.id
					WHERE error_report.solved = 1
				");
				
				$stmt->execute();
				$stmt->bind_result($report["id"], $report["page"], $report["code"], $report["function"], $report["user"], $report["message"], $report["time"], $report["prename"], $report["lastname"]);
				
				while($stmt->fetch()):
					$user = 0;
						if($report["user"]) {
							$user = $report["prename"] . " " . $report["lastname"];
						}
		?>
        				<tr class="solved">
                        	<td><a href="<?php echo $report["page"]; ?>" target="_blank"><?php echo $report["page"]; ?></a></td>
                            <td><?php echo $report["code"]; ?></td>
                            <td><?php echo $report["function"]; ?></td>
                            <td><?php if($user): ?>
                            	<a href="./edit-user.php?user=<?php echo $report["user"]; ?>" target="_blank"><?php echo $user ?></a>
                                <?php else: ?>-<?php endif; ?>
                            </td>
                            <td><?php echo $report["message"]; ?></td>
                            <td><?php echo $report["time"]; ?></td>
                            <td class="edit">
                            	<a href="./errors.php?action=existing&id=<?php echo $report["id"] ?>" class="icon-cancel-circled" title="Ungelöst"></a>
                            </td>
                        </tr>
        <?php
				endwhile;
				
				$stmt->close();
				
			endif;
		?>
        			</tbody>
                </table>
        <?php
		}
		
		public static function error_solved($id) {
			global $mysqli;
			
			$stmt = $mysqli->prepare("
				UPDATE error_report
				SET solved = 1
				WHERE id = ?
			");
			
			$stmt->bind_param("i", $id);
			$stmt->execute();
			
			$stmt->close();
		}
		
		public static function error_still_existing($id) {
			global $mysqli;
			
			$stmt = $mysqli->prepare("
				UPDATE error_report
				SET solved = 0
				WHERE id = ?
			");
			
			$stmt->bind_param("i", $id);
			$stmt->execute();
			
			$stmt->close();
		}
	}
	
?>