'use strict';
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//# rendering delay caused by loading google charts
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.adallapps", $.ck.common, {
	//#################################################################
	//# Definition
	//#################################################################
	consts:{
		REST_API:"/rest/listapps.php"
	},

	//#################################################################
	//# Constructor
	//#################################################################`
	_create: function(){
		var oThis = this;

		//set basic stuff
		var oElement = this.element;
		oElement.uniqueId();

		//check for necessary attributes
		if (!oElement.attr("metric1"))	$.error("metric1 attr is missing!");
		if (!oElement.attr("metric2"))	$.error("metric2 attr is missing!");
		if (!oElement.attr("metric3"))	$.error("metric3 attr is missing!");
		if (!oElement.attr("title1"))	$.error("title1 attr is missing!");
		if (!oElement.attr("title2"))	$.error("title2 attr is missing!");
		if (!oElement.attr("title3"))	$.error("title3 attr is missing!");
		if (!oElement.attr("baseurl"))	$.error("baseurl attr is missing!");

		//check for necessary classes
		if (!oElement.admenu)			$.error("admenu widget is missing! check includes");
		if (!oElement.attr(cRenderQS.LIST_MODE_QS) && !oElement.adchart)
			$.error("charts widget is missing! check includes");
		if (!cQueueifVisible)			$.error("Queue on visible class is missing! check includes");
		if (!bean)						$.error("bean class is missing! check includes");

		//check for required options
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
		var oElement = this.element;

		oElement.empty();
		oElement.append("got response");
		oElement.removeClass();

		var aResponse = poHttp.response;
		if (aResponse.length == 0 )
			oElement.append(cRender.messagebox("<i>no applications found</i>"));
		else{
			oElement.empty();
			if (oElement.attr(cRenderQS.LIST_MODE_QS))
				this.render_list(poHttp.response);
			else
				this.render_charts(poHttp.response);
		}
	},


	//#################################################################
	//# functions
	//#################################################################`
	get_url: function (){
		var oElement = this.element;

		var sBaseUrl = oElement.attr(cRenderQS.HOME_QS)+this.consts.REST_API;
		return sBaseUrl;
	},

	//*******************************************************************
	render_charts: function(paData){
		var oElement = this.element;
		var sHome = oElement.attr(cRenderQS.HOME_QS);
		var oThis = this;

		//iterate  each application
		paData.forEach( function(poApp){
			var oUrlParams = {};
			
			if (i==1){
				oGoParams[ cRenderQS.APP_QS ] = poApp.name;
				oGoParams[ cRenderQS.APP_ID_QS ] = poApp.id;
				var sGoUrl = cBrowser.buildUrl(oElement.attr("baseurl"),oGoParams);
			}

			var sHTML = cRenderMDL.card_start();
				sHTML += cRenderMDL.body_start();

					for (var i=1; i<=3; i++){ //3 different charts for each application
						//----------------------------------------------------------------------
						var oChartParams = {
							type:"adchart",
							style:"position: relative; max-width: 341px; width: 341px; height: 125px;",
							class:"chart_widget",
							id:"a"+poApp.id+"i"+i ,
						};
						oChartParams[cRenderQS.HOME_QS] =  sHome ;
						oChartParams[cRenderQS.APP_ID_QS] =  poApp.id;
						oChartParams[cChartConsts.ATTR_TITLE + "0"] = oElement.attr("title"+i) ;
						oChartParams[cRenderQS.METRIC_QS + "0"] = oElement.attr("metric"+i);
						oChartParams[cChartConsts.ATTR_SHOW_ZOOM] =1;
						oChartParams[cChartConsts.ATTR_SHOW_COMPARE] = 1;
						oChartParams[cChartConsts.ATTR_PREVIOUS] = 0;
						oChartParams[cChartConsts.ATTR_WIDTH] = cChartConsts.WIDTH_3ACROSS;
						oChartParams[cChartConsts.ATTR_HEIGHT] = cChartConsts.LETTERBOX_HEIGHT;

						//----------------------------------------------------------------------
						if (i==1) oChartParams[cChartConsts.ATTR_GO_URL] =  sGoUrl ;
						var oDiv = $("<DIV>", oChartParams).append("please Wait");

						sHTML += oDiv[0].outerHTML;
					}
				sHTML += "</DIV>";
				sHTML += cRenderMDL.action_start();
					sHTML += cMenusCode.appfunctions( poApp, sHome, poApp.id+"menu");
				sHTML += "</DIV>";
			sHTML += "</DIV>";
			oElement.append(sHTML);


			//- - - - -convert chart to Widgets
			if (cCharts.isGoogleChartsLoaded())
				oThis.convert_charts_to_widgets(poApp)
			else
				cCharts.load_google_charts( 
					function(){
						oThis.convert_charts_to_widgets(poApp);
					}
				);

			//- - - render the menus
			$("#"+poApp.id+"menu").admenu();

		});
	},

	//*******************************************************************
	convert_charts_to_widgets: function(poApp){
		for (var i=1; i<=3; i++)
			$("#a"+poApp.id+"i"+i).adchart();
	},

	//*******************************************************************
	render_list: function(paData){
		var oElement = this.element;

		var sHTML = cRenderMDL.card_start();
		sHTML += cRenderMDL.body_start();
		sHTML += "<DIV style='column-count:3'>";
			sHTML += "There are "+ paData.length + " Applications";
			var sLastCh = null;
			paData.forEach( function(poApp){
				var oParams = {};
				oParams[ cRenderQS.APP_ID_QS ] = poApp.name;
				oParams[ cRenderQS.APP_QS ] = poApp.id;

				var sUrl = cBrowser.buildUrl(oElement.attr("baseurl"),oParams);
				var sCh = poApp.name[0].toUpperCase();
				if (sCh !== sLastCh){
					sHTML += "<h3>"+sCh + "</h3>";
					sLastCh = sCh;
				}
				sHTML += "<a href='"+sUrl+"'>"+poApp.name + "</a><br>";
				});
		sHTML += "</div></div></div>";
		oElement.append(sHTML);
		},


});