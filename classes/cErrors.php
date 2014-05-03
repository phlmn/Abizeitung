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
				SELECT error_report.id, error_report.page, error_report.code, error_report.function, error_report.user, error_report.message, error_report.time, error_report.solved, users.prename, users.lastname
				FROM error_report
				LEFT JOIN users ON error_report.user = users.id
				ORDER BY error_report.solved ASC
			");
			
			$stmt->execute();
			
			$stmt->bind_result($report["id"], $report["page"], $report["code"], $report["function"], $report["user"], $report["message"], $report["time"], $report["solved"], $report["prename"], $report["lastname"]);
			$stmt->store_result();
			
			while($stmt->fetch()):
				$user = 0;
				if($report["user"]) {
					$user = $report["prename"] . " " . $report["lastname"];
				}
		?>
        				<tr<?php if($report["solved"]): ?> class="solved"<?php endif; ?>>
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
							<?php if($report["solved"]): ?>
                            	<a href="./errors.php?action=existing&id=<?php echo $report["id"] ?>" class="icon-cancel-circled" title="Ungelöst"></a>
                            <?php else: ?>
                            	<a href="./errors.php?action=solved&id=<?php echo $report["id"] ?>" class="icon-ok-circled" title="Gelöst"></a>
							<?php endif; ?>
                            </td>
                        </tr>
        <?php
			endwhile;
			
			$stmt->free_result();
			$stmt->close();
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