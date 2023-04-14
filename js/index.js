'use strict'

function onclicktab(){
	$(".tab").each( function(){
		$(this).hide()
	})
	$(".tabbut").each( function(){
		$(this).removeClass("w3-light-blue").addClass("w3-sand")
	})
	var sTab = $(this).attr("tab")
	$(this).removeClass("w3-sand").addClass("w3-light-blue")
	$("#"+ sTab).show()
	return false
}

function init_tabs(){
	$(".tabbut").each(
		// eslint-disable-next-line no-unused-vars
		function (piIndex){
			$(this).click(onclicktab)
		}
	)
	$("#BUTP").click()
}

$(init_tabs)