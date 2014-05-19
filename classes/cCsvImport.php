<?php
	
	class CsvImport {
		
		public static function form_file_upload() { ?>
			<form method="post" name="data" action="csv-import.php?file=upload" enctype="multipart/form-data">
			
				<div class="users box">
				
					<h4><em>*.csv</em> - Datei auswählen:</h4>
					
					<div class="upload">
						<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo return_ini_bytes(ini_get("upload_max_filesize")) ?>">
						<input id="file" name="file" type="file">
					</div><!-- .upload -->
					
					<div class="option">
						<input id="delete_file" name="delete_file" type="checkbox" value="1" checked>
						<label for="delete_file">Datei nach Bearbeitung löschen</label>
					</div><!-- .option -->
					
					<div class="option">
						<input id="caption" name="caption" type="checkbox" value="1">
						<label for="caption">Erste Reihe ist Überschrift</label>
					</div><!-- .option -->
					
					<button type="submit">Hochladen</button>
					
				</div><!-- .users .box -->
			
			</form>
<?php 	}
		
		public static function form_file_list($path) {
			
			$dir = array(
				"dir" 	=> scandir($path),
				"count" => 0
			);
?>
        	<form method="post" name="data" action="csv-import.php?file=list">
			
				<div class="users box">
				
					<h4><em>*.csv</em> - Datei auswählen:</h4>
					
					<div class="upload">
                        <table class="table table-striped">
                            <thead>
                                <th class="edit"></th>
                                <th>Name</th>
                                <th>Datum</th>
                                <th>Zeilen</th>
                                <th>Größe</th>
                            </thead>
                            <tbody>
                                <?php
                                 
                                foreach ($dir["dir"] as $file):
                                    
                                    if($file == "." || $file == "..") {
                                        continue;
                                    }
									
									$dir["count"]++;
                                    
                                    $info = array(
                                        "size" => filesize($path . $file),
                                        "date" => date("Y - m - d", filemtime($path . $file)),
                                        "time" => date("H:i:s", filemtime($path . $file))
                                    );
                                                        
                                ?>
                                <tr>
                                    <td class="edit"><input type="radio" id="file_<?php echo $dir["count"]; ?>" name="file" value="<?php echo $path . $file; ?>" /></td>
                                    <td><label for="file_<?php echo $dir["count"]; ?>"><?php echo $file; ?></label></td>
                                    <td><?php echo $info["date"]; ?> <em>(<?php echo $info["time"]; ?>)</em></td>
                                    <td><?php echo count_filerows($path . $file) ?></td>
                                    <td><?php echo $info["size"] . " B"; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
					</div><!-- .upload -->
					
					<div class="option">
						<input id="delete_file" name="delete_file" type="checkbox" value="1">
						<label for="delete_file">Datei nach Bearbeitung löschen</label>
					</div><!-- .option -->
					
					<div class="option">
						<input id="caption" name="caption" type="checkbox" value="1">
						<label for="caption">Erste Reihe ist Überschrift</label>
					</div><!-- .option -->
					
					<button type="submit">Auswählen</button>
					
				</div><!-- .users .box -->
			
			</form>
<?php	}
		
		public static function import($userid, $file, $delete_file, $columns, $disable) {
			global $mysqli;
			
			if(file_exists($file)) {
				
				if(($handle = fopen($file, "r")) !== false) {
					
					// get columns order
					
					$cols = array();
					$seperated = array(
						"tutorial" => NULL,
						"tutor_prename" => NULL,
						"tutor_lastname" => NULL
					);
					
					$i = 0;
					
					foreach($columns as $field) {
						
						if($field == "tutorial") {
							$seperated["tutorial"] = $i;
						}
						elseif($field == "tutor_prename") {
							$seperated["tutor_prename"] = $i;
						}
						elseif($field == "tutor_lastname") {
							$seperated["tutor_lastname"] = $i;
						}
						else {
							array_push($cols, array(
								"name" => $mysqli->real_escape_string($field),
								"index" => $i
							));
						}
						
						$i++;
					}
					
					// get content
					
					$col = "";
					
					foreach($cols as $c) {
						$col .= $c["name"] . ", ";
					}
						
					// 	Required columns:
					// 		prename, lastname, female
						
					if(substr_count($col, "prename") && substr_count($col, "lastname") && substr_count($col, "female")) {
						
						$index = 0;
					
						while(($csv = fgetcsv($handle, 999, ";")) !== false) {
							
							$index++;
							
							$val = "";
							
							foreach($cols as $c) {
								$val .= "'" .$csv[$c["index"]] . "', ";
							}
							
							if(!$disable[$index]) {
								$user = array(
									"unlock_code",
									"tutorial" => array(
										"id"
									)
								);
								
								$user["unlock_code"] = get_unlock_code();
								$user["tutorial"]["id"] = NULL;
								
								// Insert user
								
								$stmt = $mysqli->prepare("
									INSERT INTO users (
										" . $col . " activated, unlock_key
									) VALUES (
										" . $val . " '0', ?
									)
								");
								
								$stmt->bind_param("s", $user["unlock_code"]);
								
								$stmt->execute();
								
								$stmt->close();
								
								// Get id from user
								
								$stmt = $mysqli->prepare("
									SELECT id
									FROM users
									WHERE unlock_key = ?
									LIMIT 1
								");
								
								$stmt->bind_param("s", $user["unlock_code"]);
								$stmt->execute();
								
								$stmt->bind_result($user["id"]);
								$stmt->fetch();
								
								$stmt->close();
								
								// Search for tutorial
								
								if($seperated["tutorial"] != NULL) {
									$stmt = $mysqli->prepare("
										SELECT id
										FROM tutorials
										WHERE name = ?
										LIMIT 1
									");
									
									$stmt->bind_param("s", null_on_empty($csv[$seperated["tutorial"]]));
									$stmt->execute();
									
									$stmt->bind_result($user["tutorial"]["id"]);
									$res = $stmt->fetch();
									
									$stmt->close();
									
									// tutorial doesnt exists
									
									if(!$res) {
										
										// insert tutorial
										
										$stmt = $mysqli->prepare("
											INSERT INTO tutorials (
												name
											) VALUES (
												?
											)
										");
										
										$stmt->bind_param("s", $csv[$seperated["tutorial"]]);
										$stmt->execute();
										
										$stmt->close();
										
										// get tutorial id
										
										$stmt = $mysqli->prepare("
											SELECT id
											FROM tutorials
											WHERE name = ?
											LIMIT 1
										");
										
										$stmt->bind_param("s", $csv[$seperated["tutorial"]]);
										$stmt->execute();
										
										$stmt->bind_result($user["tutorial"]["id"]);
										$res = $stmt->fetch();
										
										$stmt->close();
										
										if($res) {
											if($seperated["tutor_prename"] != NULL || $seperated["tutor_lastname"] != NULL) {
												
												// search teacher
												
												$teacherid = 0;
												$res = 0;
												
												if($seperated["tutor_prename"] != NULL && $seperated["tutor_lastname"] != NULL) {
													$stmt = $mysqli->prepare("
														SELECT id
														FROM users
														WHERE
															prename = ?
														AND lastname = ?
														LIMIT 1
													");
													
													$stmt->bind_param("ss", $csv[$seperated["tutor_prename"]], $csv[$seperated["tutor_lastname"]]);
													$stmt->execute();
													
													$stmt->bind_result($teacherid);
													$res = $stmt->fetch();
													
													$stmt->close();
												}
												elseif($seperated["tutor_lastname"] != NULL) {
													$stmt = $mysqli->prepare("
														SELECT id
														FROM users
														WHERE lastname = ?
														LIMIT 1
													");
													
													$stmt->bind_param("s", $csv[$seperated["tutor_lastname"]]);
													$stmt->execute();
													
													$stmt->bind_result($teacherid);
													$res = $stmt->fetch();
													
													$stmt->close();
												}
												else {
													$stmt = $mysqli->prepare("
														SELECT id
														FROM users
														WHERE prename = ?
														LIMIT 1
													");
													
													$stmt->bind_param("s", $csv[$seperated["tutor_prename"]]);
													$stmt->execute();
													
													$stmt->bind_result($teacherid);
													$res = $stmt->fetch();
													
													$stmt->close();
												}
												
												// teacher doesnt exists
												
												if(!$res) {
												
													// insert teacher
													
													$tutor["prename"] 		= $csv[$seperated["tutor_prename"]];
													$tutor["lastname"] 		= $csv[$seperated["tutor_lastname"]];
													$tutor["tutorial"] 		= $csv[$seperated["tutorial"]];
													$tutor["unlock_key"] 	= get_unlock_code();
													
													$stmt = $mysqli->prepare("
														INSERT INTO users (
															prename, lastname, activated, unlock_key
														) VALUES (
															?, ?, 0, ?
														)
													");
													
													$stmt->bind_param("sss", null_on_empty($tutor["prename"]), null_on_empty($tutor["lastname"]), $tutor["unlock_key"]);
													$stmt->execute();
													
													$stmt->close();
													
													// get teacher id
													
													$stmt = $mysqli->prepare("
														SELECT id
														FROM users
														WHERE unlock_key = ?
													");
													
													$stmt->bind_param("s", $tutor["unlock_key"]);
													$stmt->execute();
													
													$stmt->bind_result($teacherid);
													$res = $stmt->fetch();
													
													$stmt->close();
													
													if(!$res) {
														return "cannot-add-user";
													}
													
													// insert user as teacher
													
													$stmt = $mysqli->prepare("
														INSERT INTO teachers (
															uid
														) VALUES (
															?
														)
													");
													
													$stmt->bind_param("i", $teacherid);
													$stmt->execute();
													
													$stmt->close();
												}
												
												// refer teacher to tutorial
												
												$stmt = $mysqli->prepare("
													UPDATE tutorials
													SET tutor = (
														SELECT teachers.id 
														FROM teachers
														WHERE teachers.uid = ?
													)
													WHERE tutorials.id = ?
												");
												
												$stmt->bind_param("ii", $teacherid, $user["tutorial"]["id"]);
												
												$stmt->execute();
												
												$stmt->close();
											}
											else {
												error_report(0, "cannot-add-tutorial", "csv-import.php", "cCsvImport::import", $userid);
												
												return "cannot-add-tutorial";
											}
											
										}
										else {
											return "cannot-add-tutorial";
										}
									}
								}
								
								// Insert student
								
								$stmt = $mysqli->prepare("
									INSERT INTO students (
										uid, tutorial
									) VALUES (
										?, ?
									)
								");
								
								$stmt->bind_param("ii", $user["id"], $user["tutorial"]["id"]);
								$stmt->execute();
								
								$stmt->close();
							}
						}
					} 
					else {
						// close file
						fclose($handle);
						
						return "required-columns";
					}
					
					// close file
					fclose($handle);
				}
			}
			else {
				return "file-access";
			}
			
			// delete file
			if($delete_file && !empty($file)) {
				if(!unlink($file))
					return "cannot-delete-file";
			}
			
			return 0;
		}
	}

?>
