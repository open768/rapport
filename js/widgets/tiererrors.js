'use strict';
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.adtiererrors",{
	//#################################################################
	//# Definition
	//#################################################################
	consts:{
		REST_API:"/rest/tiererrors.php"
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
		if (!oElement.attr(cRenderQS.TIER_QS))		$.error("tier ID  missing!");			
		if (!oElement.attr(cRenderQS.APP_ID_QS))		$.error("app ID  missing!");			
		if (!oElement.attr(cRenderQS.HOME_QS))		$.error("home  missing!");			
					
	
		//set behaviour for widget when it becomes visible
		var oQueue = new cQueueifVisible();
		bean.on(oQueue, "status", 	function(psStatus){oThis.onStatus(psStatus);}	);				
		bean.on(oQueue, "start", 	function(){oThis.onStart();}	);				
		bean.on(oQueue, "result", 	function(poHttp){oThis.onResponse(poHttp);}	);				
		bean.on(oQueue, "error", 	function(poHttp){oThis.onError(poHttp);}	);				
		oQueue.go(oElement, this.get_tier_error_url());
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
			this.render_errors(poHttp.response);
	},

	
	//#################################################################
	//# functions
	//#################################################################`
	get_tier_error_url: function (){
		var sUrl;
		var oConsts = this.consts;
		var oElement = this.element;
		
		var oParams = {};
		oParams[ cRenderQS.TIER_QS ] = oElement.attr(cRenderQS.TIER_QS);
		oParams[ cRenderQS.APP_ID_QS ] = oElement.attr(cRenderQS.APP_ID_QS);
		
		
		var sBaseUrl = oElement.attr(cRenderQS.HOME_QS)+oConsts.REST_API;
		sUrl = cBrowser.buildUrl(sBaseUrl, oParams);
		return sUrl;
	},
	
	//*******************************************************************
	render_errors: function(paData){
		var oThis = this;
		var oElement = this.element;
		
		oElement.empty();
		if (paData.length == 0){
			oElement.append("<i>no information found</i>");
			return;
		}
		
		var oTable = $("<TABLE>", {width:"100%", border:1, cellspacing:0});
		oTable.append(
			"<thead><tr>" +
				"<th width=\"*\">Name</th>" +
				"<th width=\"50\">Count</th>" + 
				"<th width=\"50\">Average</th>" +
				"</tr></thead>"
		);
		
		var oBody = $("<tbody>");
		for (var i=0; i< paData.length; i++){
			var oItem = paData[i];
			oBody.append("<tr>" +
				"<td>"+ oItem.name +"</td>" +
				"<td>"+ oItem.count+"</td>" +
				"<td>"+ oItem.average+"</td>" +
			"</tr>");
		}
		oTable.append(oBody);
		
		//---------------------------------------------------
		var sID = oElement.attr("id") + "tbl";
		oTable.attr("id", sID );
		oElement.append(oTable);
		$("#"+sID).tablesorter();
	}
});