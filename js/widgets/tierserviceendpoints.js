'use strict';
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.adserviceendpoints",{
	//#################################################################
	//# Definition
	//#################################################################
	consts:{
		REST_API:"/rest/tierserviceendpoints.php"
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

		//check for required options
		if (!oElement.attr(cRenderQS.TIER_QS))		$.error("tier Name missing!");
		if (!oElement.attr(cRenderQS.TIER_ID_QS))	$.error("tier ID  missing!");
		if (!oElement.attr(cRenderQS.APP_ID_QS))	$.error("app ID  missing!");
		if (!oElement.attr(cRenderQS.HOME_QS))		$.error("home  missing!");
		if (!oElement.attr(cRenderQS.LIST_MODE_QS) && !oElement.adchart)
			$.error("charts widget is missing! check includes");
		if (!bean)									$.error("bean class is missing! check includes");


		//set behaviour for widget when it becomes visible
		var oQueue = new cQueueifVisible();
		bean.on(oQueue, "status", 	function(psStatus){oThis.onStatus(psStatus);}	);
		bean.on(oQueue, "start", 	function(){oThis.onStart();}	);
		bean.on(oQueue, "result", 	function(poHttp){oThis.onResponse(poHttp);}	);
		bean.on(oQueue, "error", 	function(poHttp){oThis.onError(poHttp);}	);
		oQueue.go(oElement, this.get_url());
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
			oElement.append(cRender.messagebox("no service end points found"));
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
		oParams[ cRenderQS.TIER_ID_QS ] = oElement.attr(cRenderQS.TIER_ID_QS);
		oParams[ cRenderQS.APP_ID_QS ] = oElement.attr(cRenderQS.APP_ID_QS);

		var sBaseUrl = oElement.attr(cRenderQS.HOME_QS)+oConsts.REST_API;
		sUrl = cBrowser.buildUrl(sBaseUrl, oParams);
		return sUrl;
		},

	//*******************************************************************
	render: function(paData){
		var oThis = this;
		var oElement = this.element;
		var sBaseMetric = "Service Endpoints|"+oElement.attr(cRenderQS.TIER_QS);

		oElement.empty();
		var oTable = $("<table>", {border:1,cellspacing:0,style:"width:100%;overflow-wrap: break-word"});
			oTable.append("<TR><TH>Calls</TH><TH>Response Times</TH><TH>Errors per minute</TH></TR>");
			
			paData.forEach( function(poSP){
				var sBaseUrl = oElement.attr(cRenderQS.HOME_QS) + "/pages/service/endpoint.php";
				var oParams = {};
				oParams[ cRenderQS.TIER_ID_QS ] = oElement.attr(cRenderQS.TIER_ID_QS);
				oParams[ cRenderQS.APP_ID_QS ] = oElement.attr(cRenderQS.APP_ID_QS);
				oParams[ cRenderQS.SERVICE_ID_QS ] = poSP.id;
				oParams[ cRenderQS.SERVICE_QS ] = poSP.name;
				var sUrl = cBrowser.buildUrl(sBaseUrl, oParams);
				var sSPMetric = sBaseMetric + "|" + poSP.name;
				
				//-----------------------------------------------------------------------
				var oRow = $("<TR>");
					oRow.append("<td colspan='3'><a target='SP' href='" + sUrl + "'>" + poSP.name + "</a></td>");
				oTable.append(oRow);
				
				//-----------------------------------------------------------------
				var oChartParams = {};
					oChartParams[cChartConsts.ATTR_TITLE + "0"] = poSP.name ;
					oChartParams[cRenderQS.APP_ID_QS] = oElement.attr(cRenderQS.APP_ID_QS);
					oChartParams["type"] = "spwidget";
					oChartParams["style"] = "position: relative; max-width: 341px; width: 341px; height: 125px;";
					oChartParams["class"] = "chart_widget";
					oChartParams[cRenderQS.HOME_QS] =  oElement.attr(cRenderQS.HOME_QS) ;
					oChartParams[cChartConsts.ATTR_SHOW_ZOOM] =1;
					oChartParams[cChartConsts.ATTR_SHOW_COMPARE] = 1;
					oChartParams[cChartConsts.ATTR_PREVIOUS] = 0;
					oChartParams[cChartConsts.ATTR_WIDTH] = cChartConsts.WIDTH_3ACROSS;
					oChartParams[cChartConsts.ATTR_HEIGHT] = cChartConsts.LETTERBOX_HEIGHT;

				//-----------------------------------------------------------------
				var oRow = $("<TR>");
					oChartParams[cRenderQS.METRIC_QS + "0"] = sSPMetric + "|Calls per Minute"; 
					var oTD = $("<TD>");
					var oChart = $("<div>", oChartParams);
						oChart.append("please wait - chart loading...")
						oTD.append(oChart);
					oRow.append(oTD);
					//-----------------------------------------------------------------
					oChartParams[cRenderQS.METRIC_QS + "0"] = sSPMetric + "|Average Response Time (ms)"; 
					oTD = $("<TD>");
					oChart = $("<div>", oChartParams);
						oChart.append("please wait - chart loading...")
						oTD.append(oChart);
					oRow.append(oTD);
					//-----------------------------------------------------------------
					oChartParams[cRenderQS.METRIC_QS + "0"] = sSPMetric + "|Errors Per Minute"; 
					oTD = $("<TD>");
					oChart = $("<div>", oChartParams);
						oChart.append("please wait - chart loading...")
						oTD.append(oChart);
					oRow.append(oTD);
					
					//-----------------------------------------------------------------
					
				oTable.append(oRow);
			});
		
		oElement.append(oTable);
		
		//- - - - -convert chart to Widgets
		if (cCharts.isGoogleChartsLoaded())
			this.convert_to_widgets()
		else
			cCharts.load_google_charts(function(){this.convert_to_widgets();});
	},
	
	//*******************************************************************
	convert_to_widgets: function(){
		$("DIV[type=spwidget]").each(
			function(piIndex, poElement){
				$(poElement).adchart();
			}
		);
	}
});