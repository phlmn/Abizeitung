<?php
require_once("functions.php");

if(!file_exists("photos/"))
	mkdir("photos/");
	
if(!isset($_GET["user"]) || !isset($_GET["category"]))
	die("0");

$user 		= intval($_GET["user"]);
$category 	= intval($_GET["category"]);
	
if(!file_exists("photos/cat" . $category))
	mkdir("photos/cat" . $category);
	
if(!isset($_FILES['photo']['name']))
	die("1");

if($_FILES['photo']['size'] > return_ini_bytes(ini_get("upload_max_filesize")))
	die("3");

$file = "photos/cat" . $category . "/photo_" . time(). "_" . $_FILES['photo']['name'];

if(!($_FILES['photo']['type'] == "image/jpeg" || $_FILES['photo']['type'] == "image/png"))
	die("2");

if(move_uploaded_file($_FILES['photo']['tmp_name'], realpath(dirname(__FILE__)) . "/" . $file)) {
	db_connect();
	
	global $mysqli;
	
	$stmt = $mysqli->prepare("
		INSERT INTO images (
			uid, category, file
		) VALUES (
			?, ?, ?
		)
	");
	
	$stmt->bind_param("iis", $user, $category, $file);
	
	$stmt->execute();
	$res = $stmt->affected_rows;
	$stmt->close();
	
	db_close();
		
	if($res == 0)
		die("4");
	
	echo $file;
} else
	die("5");

?>