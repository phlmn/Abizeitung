
function openImageSelector(id) {
	$(id).click();
}

function uploadImage(category, idForm, idState, idPhoto) {
	var formData = new FormData($(idForm)[0]);
	$.ajax({
		url: 'upload.php?user=<?php echo $data["id"] ?>&category=' + category,
		type: 'POST',
		xhr: function() {
			var myXhr = $.ajaxSettings.xhr();
			if(myXhr.upload){
				myXhr.upload.addEventListener('progress',function(e) {
					if(e.lengthComputable) {
					
					}	
				}, false);
			}
			return myXhr;
		},
		beforeSend: function() {
			$(idState).html("<br>Bild wird<br/>hochgeladen ...");
		},
		success: function(data) {
			error1 = parseInt(data[data.length-1]);
			error0 = "";
			
			if(data.indexOf("Length") != -1) {
				error0 = error1;
				error1 = 3;
			}
			
			$(idPhoto).css("background-image", "none");
			
			switch(error1) {
				case 0:
					$(idState).html(
						'<span class="icon-cancel-circled"></span><br />' +
						'Fehler beim Hochladen:<br />' +
						'Fehler bei Identifizierung<br />' +
						'<em>Fehlercode 0x' + error1 + error0 + '</em>'
					);
					break;
				case 1:
					$(idState).html(
						'<span class="icon-cancel-circled"></span><br />' +
						'Fehler beim Hochladen:<br />' +
						'Die Datei wurde nicht korrekt übertragen<br />' +
						'<em>Fehlercode 0x' + error1 + error0 + '</em>'
					);
					break;
				case 2:
					$(idState).html(
						'<span class="icon-cancel-circled"></span><br />' +
						'Fehler beim Hochladen:<br />' +
						'Ungültiges Dateiformat<br />' +
						'Erlaubte Formate: .jpg, .png<br />' +
						'<em>Fehlercode 0x' + error1 + error0 + '</em>'
					);
					break;
				case 3:
					$(idState).html(
						'<span class="icon-cancel-circled"></span><br />' +
						'Fehler beim Hochladen:<br />' +
						'Die Datei ist zu groß<br />' +
						'<em>Fehlercode 0x' + error1 + error0 + '</em>'
					);
					break;
				case 4:
					$("span#photo-upload-state").html(
						'<span class="icon-cancel-circled"></span><br />' +
						'Datenbankfehler:<br />' +
						'Datei konnte nicht hinzugefügt werden<br />' +
						'<em>Fehlercode 0x' + error1 + error0 + '</em>'
					);
					break;
				case 5:
					$(idState).html(
						'<span class="icon-cancel-circled"></span><br />' +
						'Fehler beim Hochladen:<br />' +
						'Datei konnte nicht hochgeladen werden<br />' +
						'<em>Fehlercode 0x' + error1 + error0 + '</em>'
					);
					break;
				default:
					$(idState).html('<span class="icon-ok-circled"></span><br />Hochladen erfolgreich');        	
					$(idPhoto).css("background-image", "url('" + data + "')");
			}
		},
		error: function(a,b) {
			alert(b);
		},
		data: formData,
		cache: false,
		contentType: false,
		processData: false
	});
}
