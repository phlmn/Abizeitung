<?php

	class Thumbnails {
		
		public static function create_thumbnail($path, $file) {
			$src = $path . "/" . $file;
			
			if(file_exists($src)) {
				if(filesize($src) <= return_ini_bytes(ini_get("memory_limit"))) {
					
					if(!ini_get("gd.jpeg_ignore_warning")) {
						ini_set("gd.jpeg_ignore_warning", true);
					}
					
					$memory_limit = ini_get("memory_limit");
					ini_set("memory_limit", "1000M");
					
					$size = getimagesize($src);
					
					$info = pathinfo($src);
					$info["width"] 	= $size[0];
					$info["height"] = $size[1];
					
					$height = db_get_option("thumbnails_height", 100);
					$width 	= $info["width"] * ($height / $info["height"]);
					
					$image = NULL;
					switch(strtolower($info["extension"])) {
						case "jpg":
						case "jpeg":
							$image = @imagecreatefromjpeg($src);
							break;
						case "png":
							$image = @imagecreatefrompng($src);
							break;
						default:
							return "format";
					}
					
					$thumbnail = imagecreatetruecolor($width, $height);
					
					imagefill($thumbnail, 0, 0, 0x7fffffff);
					
					imagecopyresampled(
						$thumbnail,
						$image,
						0, 0, 0, 0,
						$width, $height,
						$info["width"], $info["height"]
					);
					
					if(!file_exists($path . "/thumbnails/")) {
						mkdir($path . "/thumbnails/");
					}
					
					switch(strtolower($info["extension"])) {
						case "jpg":
						case "jpeg":
							$qual = abs(db_get_option("thumbnails_quality_jpeg", 50));
							
							if($qual > 100) {
								$qual = 100;
							}
							
							imagejpeg($thumbnail, $path . "/thumbnails/" . $file, $qual);
							break;
						case "png":
							$qual = intval(abs(db_get_option("thumbnails_quality_png", 50) / 10)) + 1;
							
							if($qual > 10) {
								$qual = 10;
							}
							
							imagepng($thumbnail, $path . "/thumbnails/" . $file, 10 - $qual);
							break;
						default:
							return "format";
					}
					
					imagedestroy($image);
					
					ini_set("memory_limit", $memory_limit);
					
					return 0;
				}
				else {
					return "memory-limit";
				}
			}
			else {
				return "file-not-existing";
			}
		}
	}

?>
