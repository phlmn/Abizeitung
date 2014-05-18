<?php

	class ErrorHandler {
		private $storage;
		
		public function __construct() {
			$this->storage = array();
		}
		
		public function is_error() {
			if(count($this->storage) > 0)
				return true;
			else
				return false;
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
			if(($error == 0 && !is_string($error)) || empty($error)) {
				return;
			}
			
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
				case "cannot-change-data": 		$save = 18; break;
				case "cannot-change-password": 	$save = 19; break;
				case "cannot-insert-nickname": 	$save = 20; break;
				case "cannot-accept-nickname": 	$save = 21; break;
				case "cannot-update-answers": 	$save = 22; break;
				case "cannot-update-surveys": 	$save = 23; break;
				case "cannot-find-user": 		$save = 24; break;
				case "cannot-update-birthday": 	$save = 25; break;
				case "empty-input": 			$save = 26; break;
				case "nickname-already-exists": $save = 27; break;
				case "file-not-existing": 		$save = 28; break;
				case "files-not-existing": 		$save = 29; break;
				case "cannot-delete-file": 		$save = 30; break;
				case "cannot-delete-files": 	$save = 31; break;
				case "cannot-select-file": 		$save = 32; break;
				
				default: $save = intval($error);
			}
			
			if(!array_search($save, $this->storage))
				array_push($this->storage, $save);
				
			if($store) {
				error_report($save, $this->get_error($error), $page, $function, $user);
			}
		}
		
		public static function __get_error($error) {
			$message = "";
			
			switch($error) {
				case 0: 	return false; break;
				case 1: 	case "cannot-save-1": 			$message = "1 Anfrage konnte nicht gespeichert werden."; break;
				case 2: 	case "cannot-save-n": 			$message = "#n# Anfragen konnten nicht gespeichert werden."; break;
				case 3: 	case "email-password-missing": 	$message = "Die Emailadresse oder das Passwort wurde(n) nicht eingegeben."; break;
				case 4: 	case "email-existing": 			$message = "Die Emailadresse existiert bereits."; break;
				case 5: 	case "cannot-add-user": 		$message = "Der Benutzer konnte nicht hinzugefügt werden."; break;
				case 6: 	case "cannot-add-teacher": 		$message = "Der Benutzer konnte nicht als Lehrer hinzugefügt werden."; break;
				case 7: 	case "cannot-add-student": 		$message = "Der Benutzer konnte nicht als Schüler hinzugefügt werden."; break;
				case 8: 	case "cannot-add-tutorial": 	$message = "Fehler beim Eintragen des Tutoriums<br />Bitte überprüfen Sie, ob Sie die Tutorien eingetragen haben."; break;
				case 9: 	case "no-selected-file": 		$message = "Es wurde keine Datei ausgewählt."; break;
				case 10: 	case "cannot-delete-file": 		$message = "Die Datei konnte nicht gelöscht werden."; break;
				case 11: 	case "format": 					$message = "Die Datei hat nicht das richtige Format."; break;
				case 12: 	case "format-csv": 				$message = "Die Datei hat nicht das richtige Format.<br />Bitte wählen Sie eine <strong>*.csv</strong> Datei aus."; break;
				case 13: 	case "cannot-upload": 			$message = "Die Datei konnte nicht hochgeladen werden."; break;
				case 14: 	case "file-access": 			$message = "Fehler beim Dateizugriff.<br />Bitte überprüfen Sie, ob die Datei hochgeladen worden ist."; break;
				case 15: 	case "too-little-columns": 		$message = "Die Datei hat zu wenig spalten.<br />Es werden mindestens #count_cols# benötigt."; break;
				case 16: 	case "required-columns": 		$message = "Es fehlen benötigte Spalten.<br />Überprüfen Sie, ob die Spalten #require_cols# gesetzt sind."; break;
				case 17: 	case "no-nickname": 			$message = "Das Feld Spitzname darf nicht leer sein."; break;
				case 18: 	case "cannot-change-data": 		$message = "Die Daten konnten nicht geändert werden."; break;
				case 19: 	case "cannot-change-password": 	$message = "Das Passwort konnte nicht geändert werden."; break;
				case 20: 	case "cannot-insert-nickname": 	$message = "Der Spitzname konnte nicht eingefügt werden."; break;
				case 21: 	case "cannot-accept-nickname": 	$message = "Der Spitzname konnte nicht akzeptiert werden."; break;
				case 22: 	case "cannot-update-answers": 	$message = "Die Antworten konnten nicht gespeichert werden."; break;
				case 23: 	case "cannot-update-surveys": 	$message = "Die Umfrageergebnisse konnten nicht gespeichert werden."; break;
				case 24: 	case "cannot-find-user": 		$message = "Der Nutzer wurde nicht gefunden."; break;
				case 25: 	case "cannot-update-birthday": 	$message = "Das Geburtsdatum konnte nicht gespeichert werden."; break;
				case 26: 	case "empty-input": 			$message = "Das Textfeld / die Auswahl darf nicht leer sein."; break;
				case 27: 	case "nickname-already-exists": $message = "Dieser Spitzname wurde der Person bereits vorgeschlagen."; break;
				case 28: 	case "file-not-existing": 		$message = "Eine Datei existiert nicht."; break;
				case 29: 	case "files-not-existing": 		$message = "Mehrere Dateien existieren nicht."; break;
				case 30: 	case "cannot-delete-file": 		$message = "Eine Datei konnte nicht gelöscht werden."; break;
				case 31: 	case "cannot-delete-files": 	$message = "Mehrere Dateien konnten nicht gelöscht werden."; break;
				case 32: 	case "cannot-select-file": 		$message = "Die Datei kann nicht ausgewählt werden"; break;
				default: 									$message = "Es ist ein unbekannter Fehler aufgetreten.";
			}
			
			for($i = 1; $i + 1 < func_num_args() && $i - 1 < substr_count($message, '#'); $i++) {
				$message = preg_replace("/" . func_get_arg($i++) . "/", func_get_arg($i), $message, 1);
			}
			
			return $message;
		}
		
		public function __get_errors() {
			$errors = array();
			
			foreach($this->storage as $error) {
				array_push($errors, __get_error($error));
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
			
			for($i = 2; $i + 1 < func_num_args() && $i - 2 < substr_count($message, '#'); $i++) {
				$message = preg_replace("/" . func_get_arg($i++) . "/", func_get_arg($i + 1), $message, 1);
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
					if($param + 1 < func_num_args()) {
						$message = preg_replace("/" . func_get_arg($param++) . "/", func_get_arg($param), $message, 1);
						$param++;
					}
					else {
						while(substr_count($message, '#') > 0) {
							$first = strpos($message, '#');
							$count = strpos($message, '#', $first + 1) - $first;
							
							$search = substr($message, $first, $count + 1);
							
							$message = str_replace($search, "", $message);
						}
					}
				}
				
				echo $begin . $message . $end;
			}
			
		}
	}
	
?>
