<?php

	class ErrorHandler {
		private $storage;
		
		public function __construct() {
			$this->storage = array();
		}
		
		public function is_error() {
			return (count($this->storage) > 0);
		}
		
		public function export_url_param($attach = false) {
			$param = "";
			$i = 0;
			
			foreach($this->storage as $error) {
				if($i == 0 && !$attach)
					$param .= "?";
				else
					$param .= "&";
				
				$param .= "errh" . $i++ . "=" . $error;
			}
			
			$param .= "&counterrh=" . $i;
			
			return $param;
		}
		
		public function import_url_param($get) {
			for($i = 0; $i < intval($get["counterrh"]); $i++) {
				array_push($this->storage, intval($get["errh" . $i]));
			}
		}
		
		public function add_error($error, $store = false, $page = NULL, $function = "ErrorHandler::add-error", $user = NULL) {
			$save = 0;
			
			switch($error) {
				case "cannot-save-1": 			$save = 1; break;
				case "cannot-save-n": 			$save = 2; break;
				case "email-password-missing": 	$save = 3; break;
				case "email-existing": 			$save = 4; break;
				case "cannot-add-user": 		$save = 5; break;
				case "cannot-add-teacher": 		$save = 6; break;
				case "cannot-add-student": 		$save = 7; break;
				case "cannot-add-tutorial": 	$save = 8; break;
				case "no-selected-file": 		$save = 9; break;
				case "cannot-delete-file": 		$save = 10; break;
				case "format": 					$save = 11; break;
				case "format-csv": 				$save = 12; break;
				case "cannot-upload": 			$save = 13; break;
				case "file-access": 			$save = 14; break;
				case "too-little-columns": 		$save = 15; break;
				case "required-columns": 		$save = 16; break;
				case "no-nickname": 			$save = 17; break;
				case 0: return false; break;
				default: $save = intval($error);
			}
			
			array_push($this->storage, $save);
				
			if($store) {
				error_report($save, $this->get_error($error), $page, $function, $user);
			}
		}
		
		public static function __get_error($error) {
			$message = "";
			
			switch($error) {
				case 1: 	case "cannot-save-1": 			$message = "1 Anfrage konnte nicht gespeichert werden."; break;
				case 2: 	case "cannot-save-n": 			$message = "# Anfragen konnten nicht gespeichert werden."; break;
				case 3: 	case "email-password-missing": 	$message = "Die Emailadresse oder das Passwort wurde(n) nicht eingegeben"; break;
				case 4: 	case "email-existing": 			$message = "Die Emailadresse existiert bereits."; break;
				case 5: 	case "cannot-add-user": 		$message = "Der Benutzer konnte nicht hinzugefügt werden."; break;
				case 6: 	case "cannot-add-teacher": 		$message = "Der Benutzer konnte nicht als Lehrer hinzugefügt werden"; break;
				case 7: 	case "cannot-add-student": 		$message = "Der Benutzer konnte nicht als Schüler hinzugefügt werden"; break;
				case 8: 	case "cannot-add-tutorial": 	$message = "Fehler beim Eintragen des Tutoriums<br />Bitte überprüfen Sie, ob Sie die Tutorien eingetragen haben."; break;
				case 9: 	case "no-selected-file": 		$message = "Es wurde keine Datei ausgewählt."; break;
				case 10: 	case "cannot-delete-file": 		$message = "Die Datei konnte nicht gelöscht werden."; break;
				case 11: 	case "format": 					$message = "Die Datei hat nicht das richtige Format."; break;
				case 12: 	case "format-csv": 				$message = "Die Datei hat nicht das richtige Format.<br />Bitte wählen Sie eine <strong>*.csv</strong> Datei aus."; break;
				case 13: 	case "cannot-upload": 			$message = "Die Datei konnte nicht hochgeladen werden."; break;
				case 14: 	case "file-access": 			$message = "Fehler beim Dateizugriff.<br />Bitte überprüfen Sie, ob die Datei hochgeladen worden ist."; break;
				case 15: 	case "too-little-columns": 		$message = "Die Datei hat zu wenig spalten.<br />Es werden mindestens # benötigt."; break;
				case 16: 	case "required-columns": 		$message = "Es fehlen benötigte Spalten.<br />Überprüfen Sie, ob die Spalten # gesetzt sind"; break;
				case 17: 	case "no-nickname": 			$message = "Das Feld Spitzname darf nicht leer sein."; break;
			}
			
			for($i = 1; $i < func_num_args() && $i - 1 < substr_count($message, '#'); $i++) {
				$message = preg_replace("/#/", func_get_arg($i), $message, 1);
			}
			
			return $message;
		}
		
		public function __get_errors() {
			$errors = array();
			
			foreach($this->storage as $error) {
				array_push($errors, $error);
			}
			
			return $errors;
		}
		
		public function get_error($error, $tag = "") {
			$begin = $end = "";
			
			if(!empty($tag)) {
				$begin = "<" . $tag . ">";
				$end  = "</" . $tag . ">";
			}
			
			$message = $this->__get_error($error);
			
			for($i = 2; $i < func_num_args() && $i - 2 < substr_count($message, '#'); $i++) {
				$message = preg_replace("/#/", func_get_arg($i), $message, 1);
			}
			
			echo $begin . $message . $end;
		}
		
		public function get_errors($tag = "") {
			$begin = $end = "";
			
			if(!empty($tag)) {
				$begin = "<" . $tag . ">";
				$end  = "</" . $tag . ">";
			}
			
			$param = 1;
			
			foreach($this->storage as $error) {
				$message = $this->__get_error($error);
				
				for($i = 0; $i < substr_count($message, '#'); $i++) {
					if($param < func_num_args()) {
						$message = preg_replace("/#/", func_get_arg($param), $message, 1);
						$param++;
					}
					else {
						$message = preg_replace("/#/", "", $message, 1);
					}
				}
				
				echo $begin . $message . $end;
			}
			
		}
	}
	
?>