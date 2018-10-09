//for details on qtip see http://qtip2.com/
//documentation is here: https://github.com/qTip2/qtip2/wiki

function dialog__instrument_it( piIndex, poThing){
	let oDialog = $(poThing);
	let oTarget = $("#"+oDialog.attr("for"));
	
	oTarget.click(
		function(){
			showDialog({
				title: oDialog.attr("title"),
				text: oDialog.html()
			})
		}
	);
}

function dialog_init(){
	cBrowser.writeConsole("**** initialising dialog ****");
	$("div[class*='dialog']").each(dialog__instrument_it);
}

$(dialog_init);