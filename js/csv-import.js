
function Rows() {
	
	this.columns = new Array();
	this.countColumns = 0;
	this.items = 0;
	
	selfpointer = this;
	
	this.setArgs = function(cols, countCols) {
		for(i = 0; i < cols.length; i++) {
			this.columns.push(cols[i]);
		}
		
		this.countColumns = countCols;
		
		$(document).ready(function() {
			selfpointer.init();
		});
	}
												
	this.resetField = function(id) {
		$(id).val("");
		$(id).removeClass("alternate");
	}
	
	this.setColSize = function() {
		var width = "14.65%";
		var margin = "0 1%";
		
		if(this.countColumns >= this.items) {
			width = ($("#column-field-1").innerWidth() + 4) + "px";
			margin = "0 8px";
		}
		
		for(i = 1; i <= this.columns.length; i++) {
			$(".item").each(function() {
				$(this).css({
					"width": width,
					"margin": margin
				});
			});
		}
	}
	
	$(window).resize(function() {
		$("#items").css(
			"width", $("table tbody tr").first().css("width")
		);
		
		selfpointer.setColSize();
	});
	
	this.init = function() {
		for(i = 1; i <= this.countColumns; i++) {
			div = $(
				'<td>' + 
					'<input id="column-field-' + i +'" name="column-field-' + i +'" type="text" class="column-field droppable" ' +
						'placeholder="Reihe ' + i + '" onfocus="this.blur()" readonly />' + 
					'<label for="column-field-' + i + 
						'" class="reset" onclick="rows.resetField(\'#column-field-' + i + '\')"><span class="icon-minus-circled"></span>' +
					'</lable>' +
				'</td>'
			);
			
			$("#column-fields").append(div);
		}
		
		for(i = 1; i <= this.columns.length; i++) {
			div = $('<div class="item draggable">' + this.columns[i - 1] + '</div>');
			
			$("#items").append(div);
			$(div).draggable({
				revert: true
			}).data("name", this.columns[i - 1]);
		}
		
		this.items = i - 1;
		
		this.setColSize();
	}
	
}

$(document).ready(function() {
	$(function() {
		$(".column-field").droppable({
			drop: function( event, ui ) {
				$(".column-field").each(function(i, e) {
					if($(this).val() == ui.draggable.data("name"))
						$(this).val("").removeClass("alternate");
				});
				$(this).val(ui.draggable.data("name"))
				$(this).addClass("alternate");
			}
		});
		
		$("form").bind("reset", function() {
			$(".droppable").each(function(index, e) {
				$(e).removeClass("alternate");
			});
		});
	});
});

function disable(element) {
	if($(element).parent().parent().hasClass("disable")) {
		$(element).parent().parent().removeClass("disable");
	} else {
		$(element).parent().parent().addClass("disable");
	}
}
