<?php
$file = "photos/photo_".time()."_".$_FILES['photo']['name'];

if(!file_exists("photos/"))
	mkdir("photos/");

if($_FILES['photo']['type'] != "image/jpeg" && $_FILES['photo']['type'] != "image/png")
	die("Kein gültiges Format");

if(move_uploaded_file($_FILES['photo']['tmp_name'], realpath(dirname(__FILE__))."/".$file)) {
	echo $file;
}
else {
	echo "error";
}
?>