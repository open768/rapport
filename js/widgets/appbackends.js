'use strict';
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.adappbackend", $.ck.common, {
	//#################################################################
	//# Definition
	//#################################################################
	consts:{
		REST_API:"/rest/appbackend.php"
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
		if (!oElement.adchart)			$.error("charts widget is missing! check includes");	
		if (!cQueueifVisible)			$.error("Queue on visible class is missing! check includes");	
		if (!bean)						$.error("bean class is missing! check includes");	
		
		//check for required options
		if (!oElement.attr(cRenderQS.APP_ID_QS))		$.error("app  missing!");			
		if (!oElement.attr(cRenderQS.APP_QS))			$.error("appname  missing!");			
		if (!oElement.attr(cRenderQS.HOME_QS))		$.error("home  missing!");			
					
	
		//set behaviour for widget when it becomes visible
		var oQueue = new cQueueifVisible();
		bean.on(oQueue, "status", 	function(psStatus){oThis.onStatus(psStatus);}	);				
		bean.on(oQueue, "start", 	function(){oThis.onStart();}	);				
		bean.on(oQueue, "result", 	function(poHttp){oThis.onResponse(poHttp);}	);				
		bean.on(oQueue, "error", 	function(poHttp){oThis.onError(poHttp);}	);				
		oQueue.go(oElement, this.get_url());
	},
	
	//*******************************************************************
	onResponse: function(poHttp){
		var oThis = this;
		var oElement = this.element;

		oElement.empty();
		oElement.removeClass();
		
		var aResponse = poHttp.response;
		if (aResponse.length == 0 )
			oElement.append(cRender.messagebox("<i>no BACKENDS found</i>"));
		else
			this.render(poHttp.response);
	},

	
	//#################################################################
	//# functions
	//#################################################################`
	get_url: function (){
		var sUrl;
		var oConsts = this.consts;
		var oElement = this.element;
		
		var oParams = {};
		oParams[ cRenderQS.APP_ID_QS ] = oElement.attr(cRenderQS.APP_ID_QS);
		
		
		var sBaseUrl = oElement.attr(cRenderQS.HOME_QS)+this.consts.REST_API;
		sUrl = cBrowser.buildUrl(sBaseUrl, oParams);
		return sUrl;
	},
	
	//*******************************************************************
	render: function(paData){
		var oElement = this.element;
		
		oElement.empty();
		if (oElement.attr(cRenderQS.LIST_MODE_QS))
			this.pr_render_list(paData);
		else
			this.pr_render_charts(paData);
	},
	
	//*******************************************************************
	pr_render_charts: function(paData){
		var oElement = this.element;
		var sBaseID, sID, iCount=0;
		
		sBaseID=  oElement.attr("id")+"_chart_";
		paData.forEach( function(poItem){
			sID = sBaseID +iCount;
			var oChartParams = {type:"adchart", id:sID};
			
			var oParams = {};
			oParams[cRenderQS.APP_ID_QS] =  oElement.attr(cRenderQS.APP_ID_QS);
			oParams[cRenderQS.BACKEND_QS] =  poItem.name;
			var sBackendUrl = cBrowser.buildUrl(oElement.attr(cRenderQS.HOME_QS) + "/pages/app/appexttiers.php", oParams);	
			
			oChartParams[cRenderQS.HOME_QS] =  oElement.attr(cRenderQS.HOME_QS) ;
			oChartParams[cRenderQS.APP_ID_QS] =  oElement.attr(cRenderQS.APP_ID_QS);
			oChartParams[cChartConsts.ATTR_TITLE + "0"] = poItem.name ;
			oChartParams[cRenderQS.METRIC_QS + "0"] = poItem.metric;
			oChartParams[cChartConsts.ATTR_SHOW_ZOOM] =1;
			oChartParams[cChartConsts.ATTR_SHOW_COMPARE] = 1;
			oChartParams[cChartConsts.ATTR_PREVIOUS] = 0;
			oChartParams[cChartConsts.ATTR_WIDTH] = 341;
			oChartParams[cChartConsts.ATTR_HEIGHT] = 125;
			oChartParams[cChartConsts.ATTR_GO_URL] = sBackendUrl;
			
			var oDiv = $("<DIV>", oChartParams );
			oDiv.append("please wait loading chart");
			oElement.append(oDiv);
			$("#"+sID).adchart();
			iCount++;
		} );
	},
	
	//*******************************************************************
	pr_render_list: function(paData){
		var oElement = this.element;
		
		var oDiv = $("<DIV>", {style:"column-count:3"});
		paData.forEach( function(poItem){		
			var sName = poItem.name;
			if (sName.startsWith("Discovered backend call")) sName = sName.slice(26);
			sName = cRender.put_in_wbrs(sName,20);
			oDiv.append(sName);
			oDiv.append("<BR>");
		} );
		
		oElement.append(oDiv);
	}
});