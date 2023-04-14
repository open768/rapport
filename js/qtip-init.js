'use strict'
//for details on qtip see http://qtip2.com/
//documentation is here: https://github.com/qTip2/qtip2/wiki
/* global cBrowser*/

function qtip__instrument_it( piIndex, poThing){
	let oTip = $(poThing)
	let sTip = oTip.html()
	let oTarget = $("#"+oTip.attr("for"))
	
	oTarget.qtip({ content:{text: sTip}})
}

function qtip_init(){
	cBrowser.writeConsole("**** initialising qtip ****")
	$("div[class*='qtip']").each(qtip__instrument_it)
}

$(qtip_init)