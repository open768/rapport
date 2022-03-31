function onclicktab(){
	$(".tab").each( function(){
		$(this).hide();
	});
	sTab = $(this).attr("tab");
	$("#"+ sTab).show();
	return false;
}

function init_tabs(){
	$(".tabbut").each(
		function (piIndex){
			$(this).click(onclicktab);
		}
	);
}

$(init_tabs);