<?php
if(!file_exists("photos/"))
	mkdir("photos/");
	
if(!isset($_FILES['photo']['name']))
	die("1");

if($_FILES['photo']['size'] > return_ini_bytes(ini_get("upload_max_filesize")))
	die("3");

$file = "photos/photo_".time()."_".$_FILES['photo']['name'];
if(!($_FILES['photo']['type'] == "image/jpeg" || $_FILES['photo']['type'] == "image/png"))
	die("2");

if(move_uploaded_file($_FILES['photo']['tmp_name'], realpath(dirname(__FILE__))."/".$file))
	echo $file;
else
	die("4");

?>