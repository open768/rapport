'use strict'
/*globals cQueueifVisible,cRenderQS,bean,cBrowser */
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.adlogdetail", $.ck.common, {
	//#################################################################
	//# Definition
	//#################################################################
	consts:{
		DETAIL_API:"/rest/logdetail.php"
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
		if (!oElement.attr(cRenderQS.LOG_ID_QS))		$.error("log rule ID  missing!")			
		if (!oElement.attr(cRenderQS.HOME_QS))		$.error("home  missing!")			
					
	
		//set behaviour for widget when it becomes visible
		var oQueue = new cQueueifVisible()
		bean.on(oQueue, "status", 	function(psStatus){oThis.onStatus(psStatus)}	)				
		bean.on(oQueue, "start", 	function(){oThis.onStart()}	)				
		bean.on(oQueue, "result", 	function(poHttp){oThis.onResponse(poHttp)}	)				
		bean.on(oQueue, "error", 	function(poHttp){oThis.onError(poHttp)}	)				
		oQueue.go(oElement, this.get_logdetail_url())
	},


	//*******************************************************************
	onResponse: function(poHttp){
		var oElement = this.element

		oElement.empty()
		oElement.removeClass()
		
		var aResponse = poHttp.response
		if (aResponse.length == 0 )
			oElement.append("<i>no Field extractions found</i>")
		else
			this.render_detail(poHttp.response)
	},

	
	//#################################################################
	//# functions
	//#################################################################`
	get_logdetail_url: function (){
		var sUrl
		var oElement = this.element
		
		var oParams = {}
		oParams[ cRenderQS.LOG_ID_QS ] = oElement.attr(cRenderQS.LOG_ID_QS)
		
		
		var sBaseUrl = oElement.attr(cRenderQS.HOME_QS)+this.consts.DETAIL_API
		sUrl = cBrowser.buildUrl(sBaseUrl, oParams)
		return sUrl
	},
	
	//*******************************************************************
	render_detail: function(paResponse){
		var oElement = this.element
		
		oElement.empty()
		for (var i=0; i<paResponse.length; i++){
			var oEntry = paResponse[i]
			oElement.append("<li>name:"+ oEntry.fieldName + ", Type="+oEntry.type )
		}
	}
})