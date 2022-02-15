'use strict';
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.adcomparestats",{
	//#################################################################
	//# Definition
	//#################################################################
	consts:{
		REST_API:"/rest/appcomparestats.php"
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
		if (!oElement.attr(cRenderQS.LABEL_QS))		$.error("Label missing!");			
		if (!oElement.attr(cRenderQS.METRIC_QS))		$.error("metric missing!");			
		if (!oElement.attr(cRenderQS.TIME_START_QS))	$.error("start time missing!");		
		if (!oElement.attr(cRenderQS.TIME_END_QS))	$.error("end time missing!");		
		if (!oElement.attr(cRenderQS.APP_ID_QS))		$.error("app ID  missing!");			
		if (!oElement.attr(cRenderQS.HOME_QS))		$.error("home  missing!");			
					
	
		//set behaviour for widget when it becomes visible
		var oQueue = new cQueueifVisible();
		bean.on(oQueue, "status", 	function(psStatus){oThis.onStatus(psStatus);}	);				
		bean.on(oQueue, "start", 	function(){oThis.onStart();}	);				
		bean.on(oQueue, "result", 	function(poHttp){oThis.onResponse(poHttp);}	);				
		bean.on(oQueue, "error", 	function(poHttp){oThis.onError(poHttp);}	);				
		oQueue.go(oElement, this.get_widget_url());
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
			oElement.append("<td colspan='7'>There was an error  getting data  </td>");
	},

//*******************************************************************
	onStart: function(poItem){
		var oElement = this.element;

		oElement.empty();
		oElement.removeClass();
		
		var oLoader = $("<DIV>");
		oLoader.gSpinner({scale: .25});
		oElement.append(oLoader).append("<td colspan='7'>Loading: </td>");
	},
	
	//*******************************************************************
	onResponse: function(poHttp){
		var oThis = this;
		var oElement = this.element;

		oElement.empty();
		oElement.removeClass();
		
		var aResponse = poHttp.response;
		if (aResponse.length == 0 )
			oElement.append("<td colspan='7'><i>no data found</i></td>");
		else
			this.render_widget(poHttp.response);
	},

	
	//#################################################################
	//# functions
	//#################################################################`
	get_widget_url: function (){
		var sUrl;
		var oConsts = this.consts;
		var oElement = this.element;
		
		var oParams = {};
		
		oParams[cRenderQS.METRIC_QS] = oElement.attr(cRenderQS.METRIC_QS);
		oParams[cRenderQS.TIME_START_QS] = oElement.attr(cRenderQS.TIME_START_QS);
		oParams[cRenderQS.TIME_END_QS] = oElement.attr(cRenderQS.TIME_END_QS);
		oParams[cRenderQS.APP_ID_QS] = oElement.attr(cRenderQS.APP_ID_QS);

		var sBaseUrl = oElement.attr(cRenderQS.HOME_QS)+oConsts.REST_API;
		sUrl = cBrowser.buildUrl(sBaseUrl, oParams);
		return sUrl;
	},
	
	//*******************************************************************
	render_widget: function(poData){
		var oThis = this;
		var oElement = this.element;
		
		oElement.empty();
		oElement.append("<td align='right'>"+ oElement.attr(cRenderQS.LABEL_QS)+ "</td>");
		if (poData.error !== null){
			var dDate = new Date(  parseInt(oElement.attr(cRenderQS.TIME_START_QS)));
			oElement.append("<td align='right'>"+ dDate.toLocaleString()+ "</td>");
			oElement.append("<td colspan='5'>Error: " + poData.error + "</td>");
		}else{
			oElement.append("<td align='right'>"+ poData.start_date+ "</td>");
			oElement.append("<td align='right'>"+ cRender.format_number(poData.sum_calls)+"</td>");
			oElement.append("<td align='right'>"+ cRender.format_number(poData.calls_per_min)+"</td>");
			oElement.append("<td align='right'>"+ cRender.format_number(poData.avg_response_time)+" ms</td>");
			oElement.append("<td align='right'>"+ cRender.format_number(poData.max_response_time)+" ms</td>");
			oElement.append("<td align='right'>"+ poData.sum_errors +"</td>");
		}
	}
});