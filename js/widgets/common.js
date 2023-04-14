'use strict'
/* globals bean,cQueueifVisible */
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.common",{
	_create: function(){
		//check for necessary classes
		if (typeof bean === 'undefined')	$.error("bean class is missing! check includes")
		if (typeof cHttp2 === 'undefined')	$.error("http2 class is missing! check includes")
		if (!this.element.gSpinner) 		$.error("gSpinner is missing! check includes")
		if (!cQueueifVisible)			$.error("Queue on visible class is missing! check includes")
	},
	
	//*******************************************************************
	onStatus: function(psMessage){
		var oElement = this.element
		oElement.empty()
		oElement.append("status: " +psMessage)
	},

	//*******************************************************************
	// eslint-disable-next-line no-unused-vars
	onError: function(poHttp, psMessage){
		var oElement = this.element

		oElement.empty()
		oElement.addClass("ui-state-error")
			oElement.append("There was an error  getting data  ")
	},

//*******************************************************************
	// eslint-disable-next-line no-unused-vars
	onStart: function(poItem){
		var oElement = this.element

		oElement.empty()
		oElement.removeClass()

		var oLoader = $("<DIV>")
		oLoader.gSpinner({scale: 0.25})
		oElement.append(oLoader).append("Starting Server Request: ")
	},
})