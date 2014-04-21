
$.expr[":"].contains = $.expr.createPseudo(function(arg) {
	return function( elem ) {
		return $(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
	};
});

var selectedClass = -1;
var key = 0;



function showClass(id) {
	if(key == 17) {
		editClass(id);
		return;
	}
	
	if(selectedClass == id)
		id = -1;
	selectedClass = id;
	
	$.getJSON("classes.php?class=" + id, function(data) {
		$(".sidebar .head .title").text(data["name"]);
		$(".sidebar .head input.filter").val("");
		if(id != -1) $(".sidebar .head").css("background-color", $(".classes > div[data-classid='" + id + "']").css("background-color"));
		else $(".sidebar .head").css("background-color", "");
		
		$(".sidebar .users ul li").each(function(index, e) {
			$(e).css("opacity", 0);
		});
		
		setTimeout(function() {
			$(".sidebar .users ul li").each(function(index, e) {
				$(e).remove();
			});
			
			data["users"].forEach(function(e) {
				var li = $('<li><span class="name">' + e["prename"] + ' ' + e["lastname"] + '</span><span class="class">' + e["class"] + ' - ' + e["tutor"] + '</span></li>');
				
				$(li).hide(0).css("opacity", 0);
				$(".sidebar .users ul").append(li);
				$(li).draggable({
					revert: true,
					helper: "clone",
					appendTo: "#class-management",
					start: function(e, ui) {
						var count = $("#class-management div.sidebar div.users ul > li.selected").length;
						if(count > 1)
							ui.helper.html(count + " Nutzer");	
					}
				});
				$(li).click(function() {
					$(this).toggleClass("selected");	
				});	
			});
			
			$(".sidebar .users ul li").each(function(index, e) {
				$(e).show(0).css("opacity", 1);
			});
			
		}, 100);
	});
}

function filter() {
	$(".sidebar .users ul li").hide();
	$(".sidebar .users ul li:contains(" + $(".sidebar .head input.filter").val() + ")").show();
}

function editClass(id) {
	$('#classesModal').modal();
	$('#classesModal').load("classes.php?editClass=" + id);		
}

document.onkeydown = function(event) {
	event = event || window.event;
	key = event.keyCode;
}

document.onkeyup = function() {
	key = 0
};
