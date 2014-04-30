
function Group() {
	
	this.selectedGroup = -1;
	this.key = 0;
	
	this.args = new Array();
	
	this.setArgs = function(pagename, arg, classname, management, dataId, modal) {
		this.args["pagename"] = pagename; 		// classes
		this.args["argument"] = arg; 			// class
		
		this.args["class"] = classname;			// class
		this.args["management"] = management; 	// class-management
		this.args["dataId"] = dataId; 			// data-classid
		this.args["modal"] = modal; 			// classesModal	
	}
	
	this.addToGroup;
	
	this.setAddHandler = function(func) {
		this.addToGroup = func;
	}
	
	this.removeFromGroup;
	
	this.setRemoveHandler = function(func) {
		this.removeFromGroup = func;
	}
	
	this.initGroups = function() {
		
		selfpointer = this;
		
		$(".groups > div").each(function(index, e) {
			$(e).droppable({
				drop: function(event, ui) {
					$(ui.helper).remove();
					var dropGroup = $(this).attr(selfpointer.args["dataId"]);
					var actions = new Array();
					if($("#" + selfpointer.args["management"] + " div.sidebar div.users ul > li.selected").length > 0) {
						$("#" + selfpointer.args["management"] + " div.sidebar div.users ul > li.selected").each(function (i, user) {
							actions.push({user: $(user).data("id"), group: dropGroup});		
						});	
					}
					else {
						actions.push({user: ui.draggable.data("id"), group: dropGroup});
					}
					
					selfpointer.addToGroup(actions);
					selfpointer.showGroup(selfpointer.selectedGroup, true);
					$(this).removeClass("hover");
				},
				over: function(event, ui) {
					$(this).addClass("hover");	
				},
				out: function(event, ui) {
					$(this).removeClass("hover");		
				}
			});
		});
		
		
		$(document).keydown(function(event) {
			selfpointer.key = event.which;
		});
		
		$(document).keyup(function() {
			selfpointer.key = 0
		});
	}
	
	$.expr[":"].containsCI = $.expr.createPseudo(function(arg) {
		return function( elem ) {
			return $(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
		};
	});
	
	this.showGroup = function(id, reload) {
		if(this.key == 18) {
			this.editGroup(id);
			return;
		}
		
		if(this.selectedGroup == id && reload != true)
			id = -1;
		this.selectedGroup = id;
		
		var args = this.args;
		
		$.getJSON(this.args["pagename"] + ".php?" + this.args["argument"] + "=" + id, function(data) {
			$(".sidebar .head .title").text(data["name"]);
			$(".sidebar .head input.filter").val("");
			if(id != -1) $(".sidebar .head").css("background-color", $(".groups > div[" + args["dataId"] + "='" + id + "']").css("background-color"));
			else $(".sidebar .head").css("background-color", "");
			
			$(".sidebar .users ul li").each(function(index, e) {
				$(e).css("opacity", 0);
			});
			
			setTimeout(function() {
				$(".sidebar .users ul li").each(function(index, e) {
					$(e).remove();
				});
				
				data["users"].forEach(function(e) {
					var li = $('<li><span class="name">' + e["prename"] + ' ' + e["lastname"] + '</span><span class="info">' + e[args["class"]] + ' - ' + e["tutor"] + '</span></li>');
					
					$(li).hide(0).css("opacity", 0);
					$(".sidebar .users ul").append(li);
					$(li).draggable({
						revert: true,
						helper: "clone",
						appendTo: "#" + args["management"],
						start: function(e, ui) {
							$(".groups .addGroup").addClass("remove");
							var count = $("#" + args["management"] + " div.sidebar div.users ul > li.selected").length;
							if(count > 1)
								ui.helper.html(count + " Nutzer");	
						},
						stop: function(e, ui) {
							$(".groups .addGroup").removeClass("remove");
						}
					}).data("id", e['id']);
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
	
	this.filter = function() {
		$(".sidebar .users ul li").hide();
		$(".sidebar .users ul li:containsCI(" + $(".sidebar .head input.filter").val() + ")").show();
	}
	
	this.editGroup = function(id) {
		$('#' + this.args["modal"]).modal();
		$('#' + this.args["modal"]).load(this.args["pagename"] + ".php?edit" + this.args["argument"] + "=" + id);		
	}
}
