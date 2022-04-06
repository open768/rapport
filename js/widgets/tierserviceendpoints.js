'use strict';
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.adserviceendpoints", $.ck.common, {
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
	onResponse: function(poHttp){
		var oThis = this;
		var oElement = this.element;

		oElement.empty();
		oElement.removeClass();

		var aResponse = poHttp.response;
		if (aResponse.length == 0 )
			oElement.append(cRender.messagebox("no service end points found"));
		else{
			this.renderSummary(aResponse);
			this.renderSEPs(aResponse);
		}
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
	//TODO what happens if there are thousands of end points? need to page the list.
	renderSummary: function(paData){
		var oElement = this.element;
		var sLastCh = null;
		var sLast = null;
		var iCommon = 0;
		var sLastCommon = null;

		var sHTML = cRenderMDL.card_start("Service End Points");
			sHTML += cRenderMDL.body_start();
				sHTML += "<div style='column-count:3;overflow-wrap:break-word' >";
					paData.forEach(	function(poSP){
						var sCh = poSP.name[0];
						if (sCh !== sLastCh){
							sHTML += "<hr><h3>" + sCh + "</h3>";
							sLastCh = sCh;
						}
						var sName = poSP.name;
						/* TBD trying to be too clever
						if (sLast){
							iCommon = cString.count_common_chars(sName, sLast);
							if (iCommon >10 ){
								var sCommon = sName.substr(0,iCommon -1);
								if (sCommon === sLastCommon)
									sName = "... " + sName.substr(iCommon );
								sLastCommon = sCommon;
							}
						}*/
						sHTML += "<li><a href='#" + poSP.id + "'>" + sName + "</a><br>";
						sLast = poSP.name;
					});
				sHTML += "</div>";
			sHTML += "</div>";
		sHTML += "</div><p>";

		oElement.append(sHTML);
	},

	//*******************************************************************
	get_SEP_Table: function(poSP,psBaseMetric){
		var oElement = this.element;
		var oTable = $("<table>", {border:0,cellspacing:0,style:"width:100%;overflow-wrap: break-word"});
		oTable.append("<TR><TH>Calls</TH><TH>Response Times</TH><TH>Errors per minute</TH></TR>");

		var sBaseUrl = oElement.attr(cRenderQS.HOME_QS) + "/pages/service/endpoint.php";
		var oParams = {};
		oParams[ cRenderQS.TIER_ID_QS ] = oElement.attr(cRenderQS.TIER_ID_QS);
		oParams[ cRenderQS.APP_ID_QS ] = oElement.attr(cRenderQS.APP_ID_QS);
		oParams[ cRenderQS.SERVICE_ID_QS ] = poSP.id;
		oParams[ cRenderQS.SERVICE_QS ] = poSP.name;
		var sUrl = cBrowser.buildUrl(sBaseUrl, oParams);
		var sSPMetric = psBaseMetric + "|" + poSP.name;

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
			oChartParams[cChartConsts.ATTR_GO_URL] =sUrl;
			
			var oTD = $("<TD>");
			var oChart = $("<div>", oChartParams);
				oChart.append("please wait - chart loading...")
				oTD.append(oChart);
			oRow.append(oTD);
			
			//-----------------------------------------------------------------
			oChartParams[cRenderQS.METRIC_QS + "0"] = sSPMetric + "|Average Response Time (ms)";
			oChartParams[cChartConsts.ATTR_GO_URL] = null;
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
		return oTable;
	},
	
	//*******************************************************************
	renderSEPs: function(paData){
		var oThis = this;
		var oElement = this.element;
		var sBaseMetric = "Service Endpoints|"+oElement.attr(cRenderQS.TIER_QS);

		
		paData.forEach( function(poSP){
			var sHTML = cRenderMDL.card_start("<a name='" + poSP.id + "'>" + poSP.name + "</a>");
				sHTML += cRenderMDL.body_start();
					var oTable = oThis.get_SEP_Table(poSP, sBaseMetric);
					sHTML += oTable[0].outerHTML;
				sHTML += "</div>";
			sHTML += "</div><p>";
			oElement.append(sHTML);
		});

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