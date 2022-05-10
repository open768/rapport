'use strict';
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.addashsearch",  $.ck.common, {
	//#################################################################
	//# Definition
	//#################################################################
	consts:{
		REST_API:"/rest/dashsearch.php"
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
		if (!oElement.attr(cRenderQS.DASH_ID_QS))		$.error("dash ID  missing!");			
		if (!oElement.attr(cRenderQS.DASH_NAME_QS))	$.error("dash name missing!");			
		if (!oElement.attr(cRenderQS.DASH_URL_TEMPLATE))	$.error("dash url template missing!");					
		if (!oElement.attr(cRenderQS.SEARCH_QS))		$.error("search missing!");			
		if (!oElement.attr(cRenderQS.HOME_QS))		$.error("home  missing!");			
					
	
		//set behaviour for widget when it becomes visible
		var oQueue = new cQueueifVisible();
		bean.on(oQueue, "status", 	function(psStatus){oThis.onStatus(psStatus);}	);				
		bean.on(oQueue, "start", 	function(){oThis.onStart();}	);				
		bean.on(oQueue, "result", 	function(poHttp){oThis.onResponse(poHttp);}	);				
		bean.on(oQueue, "error", 	function(poHttp){oThis.onError(poHttp);}	);				
		oQueue.go(oElement, this.get_dash_search_url());
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
	get_dash_search_url: function (){
		var sUrl;
		var oConsts = this.consts;
		var oElement = this.element;
		
		var oParams = {};
		oParams[ cRenderQS.DASH_ID_QS ] = oElement.attr(cRenderQS.DASH_ID_QS);
		oParams[ cRenderQS.SEARCH_QS ] = oElement.attr(cRenderQS.SEARCH_QS);
		
		
		var sBaseUrl = oElement.attr(cRenderQS.HOME_QS)+this.consts.REST_API;
		sUrl = cBrowser.buildUrl(sBaseUrl, oParams);
		return sUrl;
	},
	
	//*******************************************************************
	render: function(piCount){
		var oThis = this;
		var oElement = this.element;
		var oConsts = this.consts;
		var sDash = oElement.attr(cRenderQS.DASH_NAME_QS);
		
		if (piCount == 0){
			oElement.empty();
			oElement.append("nothing found in dashboard: "+sDash);
			setTimeout(	function(){	oElement.hide()}, 500);
			return;
		}
		var iDash = oElement.attr(cRenderQS.DASH_ID_QS);
		
		oElement.empty();
		var sUrl = oElement.attr(cRenderQS.DASH_URL_TEMPLATE);
		sUrl = sUrl.replace("-tmp-", iDash);
		sJS= "window.open('"+sUrl+"','appd');"
		var oButton = $("<button>",{onclick:sJS}).append("->");
		oElement.append(oButton);
		
		oElement.append(" Dashboard: " + sDash + " has " + piCount + " matches " );
	}
	
});