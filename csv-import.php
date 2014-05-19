<?php
	session_start();
	
	require_once("functions.php");
	require_once("classes/cCsvImport.php");
	
	db_connect();
	check_login();
	check_admin(); 

	$data = UserManager::get_userdata($_SESSION["user"]);
	
	$columns = array(
		"prename", 
		"lastname", 
		"female", 
		"tutorial",
		"tutor_lastname",
		"tutor_prename"
	);
	
	if(isset($_GET["import"])) {
		
		$columns = array();
		$disable = array();
		
		for($i = 1; $i <= intval($_GET["columns"]); $i++) {
			if(isset($_POST["column-field-" . $i])) {
				array_push($columns, $_POST["column-field-" . $i]);
			}
		}
		
		$count = count_filerows($_POST["file"]);
		
		for($i = 1; $i <= $count; $i++) {
			$disable[$i] = isset($_POST["disable_" . $i]);
		}
		
		$errorHandler->add_error(CsvImport::import($data["id"], $_POST["file"], isset($_GET["delete_file"]), $columns, $disable));
		
		db_close();
		
		if($errorHandler->is_error()) {
			header("Location: ./csv-import.php?error" . $errorHandler->export_url_param(true));
		}
		else {
			header("Location: ./csv-import.php?saved");
		}
		
		die;
	}
	
?>


<!DOCTYPE html>
<html>
	<head>
		<title>Abizeitung - CSV Import</title>
		<?php head(); ?>
	</head>
	
	<body>
	
		<?php require("nav-bar.php") ?>
		
		<div id="csv-import" class="container-fluid admin-wrapper">
		
			<?php 
				
				if(isset($_GET["error"])): 
				
					$errorHandler->import_url_param($_GET);
			?>
			
				<div class="alert alert-danger">
					<ul>
						<?php 
							$errorHandler->get_errors("li", 
								"#count_cols#", 3, 
								"#require_cols#", "<strong>prename</strong>, <strong>lastname</strong> und <strong>female</strong>"
							); 
						?>
					</ul>
				</div>
			
			<?php elseif(isset($_GET["saved"])) : ?>
			
				<div class="alert alert-success">Importieren erfolgreich abgeschlossen.</div>
			
			<?php endif; ?>
			
			<h1>CSV Nutzer Import</h1>
			
			<?php
			
			if(isset($_GET["file"])) {
				
				$file = "";
			
				if($_GET["file"] == "upload") {
			
					if(!file_exists("csv"))
						mkdir("csv");
						
					if(!isset($_FILES["file"]["name"])) {
						$errorHandler->add_error("no-selected-file");
					}
					else {
						if(empty($_FILES["file"]["name"])) {
							$errorHandler->add_error("no-selected-file");
						}
						else {
							if(!($_FILES["file"]["type"] == "application/vnd.ms-excel" || $_FILES["file"]["type"] == "text/csv")) {
								$errorHandler->add_error("format-csv");
							}
							else {
								// create filepath
								$file = realpath(dirname(__FILE__)) . "/csv/" . time() . "_" . $_FILES["file"]["name"];
								
								// upload file in /csv/
								if(!move_uploaded_file($_FILES["file"]["tmp_name"], $file)) {
									$errorHandler->add_error("cannot-upload");
								}
							}
						}
					}
				}
				else {
					if($_GET["file"] == "list") {
						
						if(file_exists($_POST["file"])) {
							$file = $_POST["file"];
						}
						else {
							$errorHandler->add_error("no-selected-file");
						}
					}
					else {
						$errorHandler->add_error("no-selected-file");
					}
				}
					
					
				if(!$errorHandler->is_error()) {
								
					$first = array();
						
					$count_column = 0;
					
					if(($handle = fopen($file, "r")) !== false) {
						
						if(($first = fgetcsv($handle, 999, ";")) !== false) {
							foreach($first as $f) {
								$count_column++;
							}
						}
						
						fclose($handle);
					}
					
					if($count_column >= 3) {
					
						if(($handle = fopen($file, "r")) !== false) {
							?>
							
							<script type="text/ecmascript" src="js/csv-import.js"></script>
							<script type="text/javascript">
							
								var columns = new Array(<?php
									$i = 0;
									
									foreach($columns as $column) {
										if($i++) echo ',';
										echo '"' . $column . '"';
									}
								?>);
								
								var rows = new Rows();
								
								rows.setArgs(
									columns, 
									<?php echo $count_column; ?>
								);
							
							</script>
							
							<?php
								$param = "&columns=" . $count_column;
								
								if(isset($_POST["caption"]))
									$param .= "&caption=" . $_POST["caption"];
								
								if(isset($_POST["delete_file"]))
									$param .= "&delete_file=" . $_POST["delete_file"];
								
							?>
							
							<form method="post" name="upload" action="csv-import.php?import<?php echo $param; ?>">
							
								<div class="users box">
								
									<input type="hidden" name="file" value="<?php echo $file ?>">
									
									<h4>Datenbankspalten</h4>
									
									<div id="items" class="row"></div>
									
									
									<div class="option">
										Bitte ordnen Sie der <em>*.csv</em> - Datei die entsprechenden Datenbankspalten via 
										<span style="cursor: help" title="ziehen und loslassen">"drag'n'drop"</span> zu.
									</div>
									
									<h4>Die <em>*.csv</em> - Datei hat <?php echo $count_column; ?> Spalten:</h4>
									
									<table class="table table-striped">
									
									<?php
										
										$index = 1;
									
										// display captions
										
										if(isset($_POST["caption"])) :
										
									?>
										<input type="hidden" name="disable_<?php echo $index++; ?>" value="1" />
										<thead>
											<th class="edit"></th>
											<?php for($i = 0; $i < $count_column; $i++): ?>
												<th><?php echo $first[$i]; ?></th>
											<?php endfor; ?>
										</thead>
									<?php endif; ?>
										<tbody>
											
											<tr id="column-fields">
												<td class="edit"><span class="icon-cancel-circled" style="float: left; margin-top: 5px;"></span></td>
											</tr>
											
										<?php 
											
											$first_continue = false;
											
											if(isset($_POST["caption"]))
												$first_continue = true;
											
											while(($csv = fgetcsv($handle, 999, ";")) !== false) {
												
												if(!$first_continue):
										?>
											<tr>
												<td class="edit">
													<input type="checkbox" name="disable_<?php echo $index++; 
													?>" title="Diese zeile nicht importieren" onClick="disable(this)" value="1" />
												</td>
											<?php foreach($csv as $c) : ?>
												<td><?php echo $c; ?></td>
											<?php endforeach; ?>
													</tr>
										<?php 	endif;
												
												$first_continue = false;
											}
										?>
										
										</tbody>
									
									</table>
									
									<button type="submit">Importieren</button>
									<button type="reset">Zurücksetzen</button>
									
								</div><!-- .users .box -->
								
							</form>
							<?php
							
							fclose($handle);
						}
					}
					else {
						$errorHandler->add_error("too-little-columns");
						
						if(isset($_POST["delete_file"])) {
							if($_POST["delete_file"] == 1)
								unlink($file);
						}
					}
				}
				
				if($errorHandler->is_error()) {
					db_close();
					
					header("Location: ./csv-import.php?error" . $errorHandler->export_url_param(true));
					
					die;
				}
				
			}
			elseif(isset($_GET["select"])) {
				// form file select
				
				if($_GET["select"] == "upload") {
					CsvImport::form_file_upload();
				}
				else {
					CsvImport::form_file_list("./csv/");
				}
			}
			else {
			
			?>
            	<div class="box select">
                	<h2>Dateiauswahl</h2>
                	<div class="row">
                        <div class="col-sm-6">
                            <a href="csv-import.php?select=list">
                            	<div class="icon-list"></div>
                            	Gespeicherte Datei auswählen
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a href="csv-import.php?select=upload">
                            	<div class="icon-upload"></div>
                            	Datei Hochladen
                            </a>
                        </div>
                    </div>
                </div>
            <?php } ?>
			
		</div><!-- #csv-report -->
			
	</body>
</html>

<?php db_close(); ?>
