
var selectedGroup = -1;
var key = 0;

var args = new Array();

function setArgs(pagename, arg, classname, management, dataId, modal) {
	args["pagename"] = pagename; 		// classes
	args["argument"] = arg; 			// class
	
	args["class"] = classname;			// class
	args["management"] = management; 	// class-management
	args["dataId"] = dataId; 			// data-classid
	args["modal"] = modal; 				// classesModal
}

$.expr[":"].contains = $.expr.createPseudo(function(arg) {
	return function( elem ) {
		return $(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
	};
});

function showGroup(id) {
	if(key == 18) {
		editGroup(id);
		return;
	}
	
	if(selectedGroup == id)
		id = -1;
	selectedGroup = id;
	
	$.getJSON(args["pagename"] + ".php?" + args["argument"] + "=" + id, function(data) {
		$(".sidebar .head .title").text(data["name"]);
		$(".sidebar .head input.filter").val("");
		if(id != -1) $(".sidebar .head").css("background-color", $("." + args["class"] + " > div[" + args["dataId"] + "='" + id + "']").css("background-color"));
		else $(".sidebar .head").css("background-color", "");
		
		$(".sidebar .users ul li").each(function(index, e) {
			$(e).css("opacity", 0);
		});
		
		setTimeout(function() {
			$(".sidebar .users ul li").each(function(index, e) {
				$(e).remove();
			});
			
			data["users"].forEach(function(e) {
				var li = $('<li><span class="name">' + e["prename"] + ' ' + e["lastname"] + '</span><span class="' + args["class"] + '">' + e[args["class"]] + ' - ' + e["tutor"] + '</span></li>');
				
				$(li).hide(0).css("opacity", 0);
				$(".sidebar .users ul").append(li);
				$(li).draggable({
					revert: true,
					helper: "clone",
					appendTo: "#" + args["management"],
					start: function(e, ui) {
						var count = $("#" + args["management"] + " div.sidebar div.users ul > li.selected").length;
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

function editGroup(id) {
	$('#' + args["modal"]).modal();
	$('#' + args["modal"]).load(args["pagename"] + ".php?edit" + args["argument"] + "=" + id);		
}

document.onkeydown = function(event) {
	event = event || window.event;
	key = event.keyCode;
}

document.onkeyup = function() {
	key = 0
};
