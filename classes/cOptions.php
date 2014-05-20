<?php
	
	class Options {
		
		public static function display_options() {
			global $mysqli;
			
			$state_questions 	= db_get_option("state_questions");
			$state_surveys 		= db_get_option("state_surveys");
			
			$nicknames 	= db_get_option("nicknames");
			$classes 	= db_get_option("classes");
			$questions 	= db_get_option("questions");
			$surveys 	= db_get_option("surveys");
?>
				<form method="post" action="options.php?affected=options">
                <div class="row">
                	<div class="col-xs-6">
                    	
                        <h4>Status</h4>
                        <div class="option">
                        	<label for="state_questions">Ab</label>
                        	<input class="percent" id="state_questions" type="text" name="state_questions" value="<?php echo $state_questions; ?>" placeholder="100" />
                            <label for="state_questions">% Antworten erscheinen die <strong>Fragen</strong> als beantwortet</label>
                        </div>
                        <div class="option">
                        	<label for="state_surveys">Ab</label>
                        	<input class="percent" id="state_surveys" type="text" name="state_surveys" value="<?php echo $state_surveys; ?>" placeholder="100" />
                            <label for="state_surveys">% Antworten erscheinen die <strong>Umfragen</strong> als beantwortet</label>
                        </div>
                        
                    	<h4>Spitznamen</h4>
                        <div class="option">
                            <input id="nicknames_all" type="radio" name="nicknames" value="1" <?php if($nicknames == "1"): ?>checked <?php endif; ?>>
                            <label for="nicknames_all">Die Nutzer dürfen jedem einen Spitznamen geben</label>
                        </div>
                        <div class="option">
                            <input id="nicknames_tutorial" type="radio" name="nicknames" value="2" <?php if($nicknames == "2"): ?>checked <?php endif; ?>>
                            <label for="nicknames_tutorial">Die Nutzer dürfen jedem in <strong>ihrem Tutorium</strong> einen Spitznamen geben</label>
                        </div>
                        <div class="option">
                            <input id="nicknames_class" type="radio" name="nicknames" value="3" <?php if($nicknames == "3"): ?>checked <?php endif; ?>>
                            <label for="nicknames_class">Die Nutzer dürfen jedem in <strong>ihren Kursen</strong> einen Spitznamen geben</label>
                        </div>
                        
                    </div>
               
                	<div class="col-xs-6">
                    
                    	<h4>Fragen</h4>
                        <div class="option">
                            <input id="questions_allow" type="radio" name="questions" value="1" <?php if($questions == "1"): ?>checked <?php endif; ?>>
                            <label for="questions_allow">Die Nutzer dürfen Fragen vorschlagen</label>
                        </div>
                        <div class="option">
                            <input id="questions_denied" type="radio" name="questions" value="2" <?php if($questions == "2"): ?>checked <?php endif; ?>>
                            <label for="questions_denied">Die Nutzer dürfen <strong>keine</strong> Fragen vorschlagen</label>
                        </div>
                        
                        <h4>Umfragen</h4>
                        <div class="option">
                            <input id="surveys_allow" type="radio" name="surveys" value="1" <?php if($surveys == "1"): ?>checked <?php endif; ?>>
                            <label for="surveys_allow">Die Nutzer dürfen Umfragen vorschlagen</label>
                        </div>
                        <div class="option">
                            <input id="surveys_denied" type="radio" name="surveys" value="2" <?php if($surveys == "2"): ?>checked <?php endif; ?>>
                            <label for="surveys_denied">Die Nutzer dürfen <strong>keine</strong> Umfragen vorschlagen</label>
                        </div>
                        
                        <h4>Kurse</h4>
                        <div class="option">
                            <input id="classes_allow" type="radio" name="classes" value="1" <?php if($classes == "1"): ?>checked <?php endif; ?>>
                            <label for="classes_allow">Die Nutzer dürfen Kurse hinzufügen</label>
                        </div>
                        <div class="option">
                            <input id="classes_denied" type="radio" name="classes" value="2" <?php if($classes == "2"): ?>checked <?php endif; ?>>
                            <label for="classes_denied">Die Nutzer dürfen <strong>keine</strong> Kurse hinzufügen</label>
                        </div>
                        
                    </div>
                    
                </div>
                <div class="buttons">
			
                    <input type="submit" value="Speichern" />
                    <input type="reset" value="Änderungen verwerfen" />
            
                </div><!-- .buttons -->
                </form>
<?php
		}
		
		public static function display_images() {
			global $mysqli;
?>
				<table class="table table-striped">
					<thead>
                    	<th>Id</th>
						<th>Kategoriename</th>
						<th>Anzahl der Bilder</th>
						<th class="edit"></th>
                        <th class="edit"></th>
					</thead>
					<tbody>
<?php
			
			$stmt = $mysqli->prepare("
				SELECT id, name
				FROM categories
			");
			
			$stmt->execute();
			
			$stmt->bind_result($category["id"], $category["name"]);
			$stmt->store_result();
			
			while($stmt->fetch()):
			
			?>
            			<tr>
                        	<td><?php echo $category["id"]; ?></td>
                        	<td><?php echo $category["name"]; ?></td>
                            <td><?php echo db_count("images", "category", $category["id"]); ?></td>
                            <td class="edit">
                            	<a title="Bearbeiten" href="#" onclick="edit('image', 'id=<?php echo $category["id"]; ?>')"><span class="icon-pencil-squared"></span></a>
                            </td>
                            <td class="edit">
                            	<a href="options.php?group=images&category=<?php echo $category["id"]; ?>&name=<?php echo $category["name"]; ?>" title='Vorschau Bilder von Kategorie "<?php echo $category["name"]; ?>"'>
                                	<span class="icon-list"></span>
                                </a>
                            </td>
                        </tr>
            <?php
			
			endwhile;
			
			?>
            			<tr>
                        	<td>-</td>
                            <td>Alle Bilder</td>
                            <td><?php echo count_files("./photos/", 1, true); ?></td>
                            <td class="edit"></td>
                            <td class="edit">
                            	<a title="Vorschau von allen Bildern" href="options.php?group=images&category=all"><span class="icon-list"></span></a>
                            </td>
                        </tr>
            <?php
			
			$stmt->free_result();
			$stmt->close();
			
			?>
            
            		</tbody>
                </table>
                
                <div class="buttons">
                	<a class="button" href="options.php?affected=thumbnails">Thumbnails erstellen</a>
                </div>
            <?php
		}
		
		public static function get_images($category, $name = NULL) { ?>
        	<script type="text/javascript">
				$(document).ready(function() {
					$("div.row .thumbnail").tooltip();
				});
			</script>
            
			<div class="row">
            	
                <div class="buttons">
                    <a class="button" href="options.php?group=images"><span class="icon-angle-left"></span> Zurück zur Übersicht</a>
                </div>
                
                <?php if($name): ?>
                <h4>Kategorie "<?php echo $name; ?>"</h4>
                <?php endif; ?>
<?php
			global $mysqli;
			
			if($category == "all") {
				$path = "./photos/";

				foreach(new DirectoryIterator($path) as $dir) {

					if($dir->isDot()) {
						continue;
					}
					
					if($dir->isDir()) {
					?>
                    <h4>Kategorie "<?php echo $dir->getFilename(); ?>"</h4>
                    <span class="category col-xs-12">
                    <?php
						foreach(new DirectoryIterator($path . "/" . $dir->getFilename()) as $image) {
							if($image->isDot() || $image->isDir()) {
								continue;
							}
							
							$src = $path . $dir->getFilename() . "/" . $image->getFilename();
							
							$title = "Dieses Bild ist keinem Benutzer zugeordnet";
							
							$stmt = $mysqli->prepare("
								SELECT images.uploadtime, users.prename, users.lastname
								FROM images
								LEFT JOIN users ON images.uid = users.id
								WHERE images.file = ?
							");
							
							$stmt->bind_param("s", str_replace("./", "", $src));
							$stmt->execute();
							
							$stmt->bind_result($time, $prename, $lastname);
							
							if($stmt->fetch()) {
								$title = "Hochgeladen von " . $prename . " " . $lastname . " am " . $time;
							}
							
							$stmt->close();
							
							if(file_exists($path . $dir->getFilename() . "/thumbnails/" . $image->getFilename())) {
								$src = $path . $dir->getFilename() . "/thumbnails/" . $image->getFilename();
							}
							
							?>
								<a class="thumbnail" href="#" onclick="detail('<?php echo $path . $dir->getFilename() . "/" . $image->getFilename(); ?>', 'all')" title='<?php echo $title; ?>' data-placement="bottom">
									<img src="<?php echo $src; ?>" />
								</a>
							<?php
						}
					?>
                    </span>
                    <?php
					}
				}
			}
			else {
				$stmt = $mysqli->prepare("
					SELECT images.id, images.file, images.uploadtime, users.prename, users.lastname
					FROM images
					LEFT JOIN users ON images.uid = users.id
					WHERE category = ?
					ORDER BY users.lastname ASC, users.prename ASC
				");
				
				$stmt->bind_param("i", $category);
				$stmt->execute();
				
				$stmt->bind_result($image["id"], $image["file"], $image["time"], $user["prename"], $user["lastname"]);
				
				while($stmt->fetch()) {
					$src = $image["file"];
					
					$path = pathinfo($src);
					
					if(file_exists($path["dirname"] . "/thumbnails/" . $path["basename"])) {
						$src = $path["dirname"] . "/thumbnails/" . $path["basename"];
					}
					
					?>
                        <a class="thumbnail" href="#" onclick="detail('<?php echo $image["file"]; ?>', '<?php echo $category; ?>', '<?php echo $image["id"]; ?>', '<?php echo $name; ?>')" title='Hochgeladen von "<?php echo $user["prename"] . " " . $user["lastname"]; ?>" am <?php echo $image["time"]; ?>' data-placement="bottom">
                            <img src="<?php echo $src; ?>" />
                        </a>
                    <?php
				}
				
				$stmt->close();
			}
?>
			</div>
<?php
		}
		
		public static function create_thumbnails() {
			$path = "./photos/";
			
			$error = 0;

			foreach(new DirectoryIterator($path) as $dir) {

				if($dir->isDot()) {
					continue;
				}
				
				if($dir->isDir()) {
					
					if(!file_exists($path . "/" . $dir->getFilename() . "/thumbnails")) {
						mkdir($path . "/" . $dir->getFilename() . "/thumbnails");
					}

					foreach(new DirectoryIterator($path . "/" . $dir->getFilename()) as $image) {
						if($image->isDot() || $image->isDir()) {
							continue;
						}
						
						$error = Thumbnails::create_thumbnail($path . $dir->getFilename(), $image->getFilename());
					}
					
				}
			}
			
			if($error) {
				return $error;
			}
			else {
				return 0;
			}
		}
		
		public static function display_csv() {
			
			$dir["count"] = 0;
			$dir["path"] = "./csv/";
			
?>
				<form method="post" action="options.php?affected=files">
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
                            
                            $dir["dir"] = scandir($dir["path"]);
                             
                            foreach ($dir["dir"] as $file):
                                
                                if ($file == "." || $file == "..") {
                                    continue;
                                }
                                
                                $dir["count"]++;
                                
                                $info = array(
                                    "size" => filesize($dir["path"] . $file),
                                    "date" => date("Y - m - d", filemtime($dir["path"] . $file)),
                                    "time" => date("H:i:s", filemtime($dir["path"] . $file))
                                );
                                                    
                            ?>
                            <tr>
                                <td class="edit"><input type="checkbox" id="file_<?php echo $dir["count"]; ?>" name="file_<?php echo $dir["count"]; ?>" /></td>
                                <td><label for="file_<?php echo $dir["count"]; ?>"><?php echo $file; ?></label></td>
                                <td><?php echo $info["date"]; ?> <em>(<?php echo $info["time"]; ?>)</em></td>
                                <td><?php echo count_filerows($dir["path"] . $file) ?></td>
                                <td><?php echo $info["size"] . " B"; ?></td>
                                <input type="hidden" name="file_name_<?php echo $dir["count"]; ?>" value="<?php echo $dir["path"] . $file; ?>" />
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <input type="hidden" name="file_count" value="<?php echo $dir["count"]; ?>" />
                    
                    <div class="buttons">
                    
                        <input type="submit" value="Ausgewählte Dateien löschen" />
                        <input type="reset" value="Auswahl aufheben" />
                
                    </div><!-- .buttons -->
                </form>
<?php
		}
		
		public static function edit_images($id) {
			if(empty($id))
				return -1;
			
			global $mysqli;

			$stmt = $mysqli->prepare("
				SELECT name
				FROM categories
				WHERE id = ?
			");
			
			$stmt->bind_param("i", $id);
			$stmt->execute();
			
			$stmt->bind_result($name);
			
			if(!$stmt->fetch()) {
				$stmt->close();
				
				return -2;
			}
			
			$stmt->close();
?>
<div class="modal-dialog">
    <div class="modal-content">
        <form method="post" action="options.php?affected=images&id=<?php echo $id; ?>">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4>Kategorie bearbeiten</h4>
            </div>
            <div class="modal-body">
                <input type="text" name="name" placeholder="Kategoriename eingeben..." value="<?php echo $name; ?>" />
            </div>
            <div class="modal-footer">
            	<button type="button" class="btn btn-default delete" onClick="javascript:void(window.location='options.php?affected=images&id=<?php echo $id; ?>&action=delete')" data-dismiss="modal">Löschen</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Schließen</button>
                <button type="submit" class="btn btn-default">Speichern</button>
            </div>
        </form>
    </div>
</div>
<?php
		}
		
		public static function image_detail($data) {
?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <a href="#" class="close" data-dismiss="modal" aria-hidden="true">&times;</a>
            <h4>Detailansicht</h4>
        </div>
        <div class="modal-body">
            <img src="<?php echo str_replace("@space@", " ", $data["file"]); ?>" width="100%" />
        </div>
        <div class="modal-footer">
        <?php if($data["id"] > 0): ?>
            <a class="btn btn-default delete" href="options.php?affected=detail&id=<?php echo $data["id"]; ?>&category=<?php echo $data["category"]; ?>&name=<?php echo $data["name"]; ?>&action=delete" >Löschen</a>
        <?php endif; ?>
            <a class="btn btn-default" href="#" data-dismiss="modal">Schließen</a>
        </div>
    </div>
</div>
<?php
		}
		
		public static function update_options($data) {
			db_set_option("state_questions", $data["state_questions"]);
			db_set_option("state_surveys", $data["state_surveys"]);
			
			db_set_option("nicknames", $data["nicknames"]);
			db_set_option("classes", $data["classes"]);
			db_set_option("questions", $data["questions"]);
			db_set_option("surveys", $data["surveys"]);
			
			return 0;
		}
		
		public static function update_images($data) {
			global $mysqli;
			
			$stmt = $mysqli->prepare("
				UPDATE categories
				SET name = ?
				WHERE id = ?
			");
			
			$stmt->bind_param("si", $data["name"], $data["id"]);
			$stmt->execute();
			
			$stmt->close();
			
			return 0;
		}
		
		public static function delete_images($data) {
			global $mysqli;
			
			$stmt = $mysqli->prepare("
				DELETE FROM categories
				WHERE id = ?
				LIMIT 1
			");
			
			$stmt->bind_param("i", $data["id"]);
			$stmt->execute();
			
			$stmt->close();
			
			if($mysqli->error)
				return "error";
			
			return 0;
		}
		
		public static function delete_image($id) {
			global $mysqli;
			
			$file = NULL;
			
			$stmt = $mysqli->prepare("
				SELECT file
				FROM images
				WHERE id = ?
			");
			
			$stmt->bind_param("i", $id);
			$stmt->execute();
			
			$stmt->bind_result($file);
			
			if(!$stmt->fetch()) {
				$stmt->close();
				
				return "cannot-delete-file";
			}
			
			$stmt->close();
			
			$stmt = $mysqli->prepare("
				DELETE FROM images
				WHERE id = ?
				LIMIT 1
			");
			
			$stmt->bind_param("i", $id);
			$stmt->execute();
			
			$stmt->close();
			
			if($mysqli->error)
				return "error";
				
			if(file_exists($file)) {
				
				$path = pathinfo($file);
				
				if(file_exists($path["dirname"] . "/thumbnails/" . $path["basename"])) {
					unlink($path["dirname"] . "/thumbnails/" . $path["basename"]);
				}
					
				if(!unlink($file)) {
					return "file-not-existing";
				}
			}
			else {
				return "cannot-delete-file";
				
			}
			
			return 0;
		}
		
		public static function delete_csv($data) {
			if(!count($data["files"])) {
				return "empty-input";
			}
			
			$error["exist"] 	= 0;
			$error["delete"] 	= 0;
			
			foreach($data["files"] as $file) {
				if(is_file($file)) {
					if(!unlink($file)) {
						$error["delete"]++;
					}
				}
				else {
					$error["exist"]++;
				}
			}
			
			if($error["exist"] == 1) {
				return "file-not-existing";
			}
			
			if($error["exist"] > 1) {
				return "files-not-existing";
			}
			
			if($error["delete"] == 1) {
				return "cannot-delete-file";
			}
			
			if($error["delete"] > 1) {
				return "cannot-delete-files";
			}
			
			return 0;
		}
		
		public static function script($jstag = false) {
			if($jstag): ?><script type="text/javascript"><?php endif; ?>
			
            function edit(group) {
				var param = "";
				var countParam;
				
				if(arguments.length > 1) {
					for(countParam = 1; countParam < arguments.length; countParam++)
						param += "&" + arguments[countParam];
				}
				
				$('#optionsModal').modal();
				$('#optionsModal').load("options.php?modal=" + group + "&edit" + param.replace(" ", "") + "&countParam=" + countParam, function() {
					$("#optionsModal select").fancySelect();
				});
			}
			
			function detail(file, category) {
				$('#optionsModal').modal();
				
				var id = 0;
				var name = "";
				
				if(arguments.length > 2) {
					id = arguments[2];
					
					if(arguments.length > 3) {
						name = arguments[3];
					}
				}
				
				$('#optionsModal').load("options.php?modal=detail&id=" + id + "&file=" + file.replace(/ /g, "@space@") + "&category=" + category + "&name=" + name, function() {
					$("#optionsModal select").fancySelect();
				});
			}
			
		<?php if($jstag): ?></script><?php endif; ?>
<?php
		}
	}

?>