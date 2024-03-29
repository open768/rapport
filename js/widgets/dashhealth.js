'use strict'
/* globals bean,cQueueifVisible,cRenderQS,cBrowser */
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.addashhealth",  $.ck.common, {
	//#################################################################
	//# Definition
	//#################################################################
	consts:{
		REST_API:"/rest/dashhealth.php"
	},

	//#################################################################
	//# Constructor
	//#################################################################`
	_create: function(){
		var oThis = this
		
		//set basic stuff
		var oElement = this.element
		oElement.uniqueId()
		
		//check for necessary classes
		if (!cQueueifVisible)			$.error("Queue on visible class is missing! check includes")	
		if (!bean)						$.error("bean class is missing! check includes")	
		
		//check for required options
		if (!oElement.attr(cRenderQS.DASH_ID_QS))		$.error("dash ID  missing!")			
		if (!oElement.attr(cRenderQS.HOME_QS))		$.error("home  missing!")			
					
	
		//set behaviour for widget when it becomes visible
		var oQueue = new cQueueifVisible()
		bean.on(oQueue, "status", 	function(psStatus){oThis.onStatus(psStatus)}	)				
		bean.on(oQueue, "start", 	function(){oThis.onStart()}	)				
		bean.on(oQueue, "result", 	function(poHttp){oThis.onResponse(poHttp)}	)				
		bean.on(oQueue, "error", 	function(poHttp){oThis.onError(poHttp)}	)				
		oQueue.go(oElement, this.get_dash_health_url())
	},


	//*******************************************************************
	onResponse: function(poHttp){
		var oElement = this.element

		oElement.empty()
		oElement.removeClass()
		
		var aResponse = poHttp.response
		if (aResponse.length == 0 )
			oElement.append("<i>no errors found</i>")
		else
			this.render_health(poHttp.response)
	},

	
	//#################################################################
	//# functions
	//#################################################################`
	get_dash_health_url: function (){
		var sUrl
		var oElement = this.element
		
		var oParams = {}
		oParams[ cRenderQS.DASH_ID_QS ] = oElement.attr(cRenderQS.DASH_ID_QS)
		
		
		var sBaseUrl = oElement.attr(cRenderQS.HOME_QS)+this.consts.REST_API
		sUrl = cBrowser.buildUrl(sBaseUrl, oParams)
		return sUrl
	},
	
	//*******************************************************************
	// eslint-disable-next-line no-unused-vars
	render_health: function(paData){
		var oElement = this.element
		
		oElement.empty()
		oElement.append("WIP")
	}
})