<?php
require_once("functions.php");

if(!file_exists("photos/"))
	mkdir("photos/");
	
if(!isset($_GET["user"]) || !isset($_GET["category"]))
	die("0");
	
if(!isset($_FILES['photo']['name']))
	die("1");

if($_FILES['photo']['size'] > return_ini_bytes(ini_get("upload_max_filesize")))
	die("3");

$file = "photos/photo_".time()."_".$_FILES['photo']['name'];
if(!($_FILES['photo']['type'] == "image/jpeg" || $_FILES['photo']['type'] == "image/png"))
	die("2");

if(move_uploaded_file($_FILES['photo']['tmp_name'], realpath(dirname(__FILE__))."/".$file)) {
	db_connect();
	
	global $mysqli;
	
	$mysqli->query("
		INSERT INTO images (uid, category, file) VALUES (
			'".$_GET["user"]."', '".$_GET["category"]."', '".$file."'
		);");
		
	if($mysqli->affected_rows == 0)
		die("4");
		
	db_close();
	
	echo $file;
} else
	die("5");

?>