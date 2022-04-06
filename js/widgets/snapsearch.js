'use strict';
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.adsnapsearch",$.ck.common, {
	//#################################################################
	//# Definition
	//#################################################################
	consts:{
		REST_API:"/rest/snapsearch.php"
	},

	//#################################################################
	//# Constructor
	//#################################################################`
	_create: function(){
		var oThis = this;
		
		//set basic stuff
		var oElement = this.element;
		oElement.uniqueId();
		
		//check for necessary classes
		if (!cQueueifVisible)			$.error("Queue on visible class is missing! check includes");	
		if (!bean)						$.error("bean class is missing! check includes");	
		
		//check for required options
		if (!oElement.attr(cRenderQS.TIER_ID_QS))		$.error("tier ID  missing!");			
		if (!oElement.attr(cRenderQS.APP_ID_QS))		$.error("appid  missing!");			
		if (!oElement.attr(cRenderQS.TRANS_QS))		$.error("trans  missing!");			
		if (!oElement.attr(cRenderQS.TRANS_ID_QS))	$.error("transid  missing!");			
		if (!oElement.attr(cRenderQS.SNAP_GUID_QS))	$.error("snapguuid  missing!");			
		if (!oElement.attr(cRenderQS.SNAP_TIME_QS))	$.error("appid  missing!");			
		if (!oElement.attr(cRenderQS.SEARCH_QS))		$.error("search missing!");			
		if (!oElement.attr(cRenderQS.HOME_QS))		$.error("home  missing!");			
					
	
		//set behaviour for widget when it becomes visible
		var oQueue = new cQueueifVisible();
		bean.on(oQueue, "status", 	function(psStatus){oThis.onStatus(psStatus);}	);				
		bean.on(oQueue, "start", 	function(){oThis.onStart();}	);				
		bean.on(oQueue, "result", 	function(poHttp){oThis.onResponse(poHttp);}	);				
		bean.on(oQueue, "error", 	function(poHttp){oThis.onError(poHttp);}	);				
		oQueue.go(oElement, this.get_snap_search_url());
	},


	//*******************************************************************
	onResponse: function(poHttp){
		var oThis = this;
		var oElement = this.element;

		oElement.empty();
		oElement.removeClass();
		
		var aResponse = poHttp.response;
		if (aResponse.length == 0 )
			oElement.append("<i>no errors found</i>");
		else
			this.render(poHttp.response);
	},

	
	//#################################################################
	//# functions
	//#################################################################`
	get_snap_search_url: function (){
		var sUrl;
		var oConsts = this.consts;
		var oElement = this.element;
		
		var oParams = {};
		oParams[ cRenderQS.APP_ID_QS ] = oElement.attr(cRenderQS.APP_ID_QS);
		oParams[ cRenderQS.TIER_ID_QS ] = oElement.attr(cRenderQS.TIER_ID_QS);
		oParams[ cRenderQS.TRANS_QS ] = oElement.attr(cRenderQS.TRANS_QS);
		oParams[ cRenderQS.TRANS_ID_QS ] = oElement.attr(cRenderQS.TRANS_ID_QS);
		oParams[ cRenderQS.SNAP_GUID_QS ] = oElement.attr(cRenderQS.SNAP_GUID_QS);
		oParams[ cRenderQS.SNAP_TIME_QS ] = oElement.attr(cRenderQS.SNAP_TIME_QS);
		oParams[ cRenderQS.SEARCH_QS ] = oElement.attr(cRenderQS.SEARCH_QS);
		
		
		var sBaseUrl = oElement.attr(cRenderQS.HOME_QS)+oConsts.REST_API;
		sUrl = cBrowser.buildUrl(sBaseUrl, oParams);
		return sUrl;
	},
	
	//*******************************************************************
	render: function(piCount){
		var oThis = this;
		var oElement = this.element;
		var oConsts = this.consts;
		if (piCount == 0){
			oElement.hide();
			return;
		}
		
		oElement.empty();
		oElement.append(" Snapshot: has " + piCount + " matches " );
		
	}
});