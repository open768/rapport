//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.addashsearch",{
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
		if (!oElement.attr(cRender.DASH_ID_QS))		$.error("dash ID  missing!");			
		if (!oElement.attr(cRender.DASH_NAME_QS))	$.error("dash name missing!");			
		if (!oElement.attr(cRender.DASH_URL_TEMPLATE))	$.error("dash url template missing!");					
		if (!oElement.attr(cRender.SEARCH_QS))		$.error("search missing!");			
		if (!oElement.attr(cRender.HOME_QS))		$.error("home  missing!");			
					
	
		//set behaviour for widget when it becomes visible
		var oQueue = new cQueueifVisible();
		bean.on(oQueue, "status", 	function(psStatus){oThis.onStatus(psStatus);}	);				
		bean.on(oQueue, "start", 	function(){oThis.onStart();}	);				
		bean.on(oQueue, "result", 	function(poHttp){oThis.onResponse(poHttp);}	);				
		bean.on(oQueue, "error", 	function(poHttp){oThis.onError(poHttp);}	);				
		oQueue.go(oElement, this.get_dash_search_url());
	},


	//*******************************************************************
	onStatus: function(psMessage){
		var oElement = this.element;
		oElement.empty();
		oElement.append("status: " +psMessage);
	},
	
	//*******************************************************************
	onError: function(poHttp, psMessage){
		var oThis = this;
		var oElement = this.element;
				
		oElement.empty();
		oElement.addClass("ui-state-error");
			oElement.append("There was an error  getting data  ");
	},

//*******************************************************************
	onStart: function(poItem){
		var oElement = this.element;

		oElement.empty();
		oElement.removeClass();
		
		var oLoader = $("<DIV>");
		oLoader.gSpinner({scale: .25});
		oElement.append(oLoader).append("Loading: ");
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
		oParams[ cRender.DASH_ID_QS ] = oElement.attr(cRender.DASH_ID_QS);
		oParams[ cRender.SEARCH_QS ] = oElement.attr(cRender.SEARCH_QS);
		
		
		var sBaseUrl = oElement.attr(cRender.HOME_QS)+this.consts.REST_API;
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
		var sDash = oElement.attr(cRender.DASH_NAME_QS);
		var iDash = oElement.attr(cRender.DASH_ID_QS);
		
		oElement.empty();
		var sUrl = oElement.attr(cRender.DASH_URL_TEMPLATE);
		sUrl = sUrl.replace("-tmp-", iDash);
		sJS= "window.open('"+sUrl+"','appd');"
		var oButton = $("<button>",{onclick:sJS}).append("->");
		oElement.append(oButton);
		
		oElement.append(" Dashboard: " + sDash + " has " + piCount + " matches " );
		
	}
});