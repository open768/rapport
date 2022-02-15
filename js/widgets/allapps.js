'use strict';
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.adallapps",{
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
	onStatus: function(psMessage){
		var oElement = this.element;
		oElement.empty();
		oElement.append("status: " +psMessage);
	},
	
	//*******************************************************************
	onError: function(poHttp, psMessage){
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
		oLoader.gSpinner({scale: 0.25});
		oElement.append(oLoader).append("Loading: ");
	},
	
	//*******************************************************************
	onResponse: function(poHttp){
		var oElement = this.element;
		
		oElement.empty();
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
		var sStyle = 'type="adchart" style="position: relative; max-width: 341px; width: 341px; height: 125px;" class="chart_widget"';
		var sHome = oElement.attr(cRenderQS.HOME_QS);
		var oThis = this;
		
		paData.forEach( function(poApp){
			var oParams = {};
			oParams[ cRenderQS.APP_ID_QS ] = poApp.name;
			oParams[ cRenderQS.APP_QS ] = poApp.id;
			
			var sHTML = cRenderMDL.card_start();		
				sHTML += cRenderMDL.body_start();
					sHTML += "<div id='"+poApp.id+"1' "+sStyle+">Please wait..</div>";
					sHTML += "<div id='"+poApp.id+"2' "+sStyle+">Please wait..</div>";					sHTML += "<div id='"+poApp.id+"3' "+sStyle+">Please wait..</div>";
				sHTML += "</DIV>";
				sHTML += cRenderMDL.action_start();
					sHTML += "<div type='admenus' menu='appfunctions' home='"+sHome+"' appname='" + poApp.name +"' appid='"+poApp.id+"' id='"+poApp.id+"menu' style='position: relative;'>"+poApp.name+" .. please wait</div>";
				sHTML += "</DIV>";				
			sHTML += "</DIV><p>";
			oElement.append(sHTML);
			
			
			//- - - - -convert chart to Widgets
			if (cCharts.isGoogleChartsLoaded())
				convert_charts_to_widgets(poApp)
			else
				cCharts.load_google_charts(function(){oThis.convert_charts_to_widgets(poApp);});
			
			//- - - render the menus
			$("#"+poApp.id+"menu").admenu(); 

		});
	},
	
	//*******************************************************************
	convert_charts_to_widgets: function(poApp){
		var oElement = this.element;
		var oParams = {};
		oParams[ cRenderQS.APP_ID_QS ] = poApp.id;
		oParams[ cRenderQS.APP_QS ] = poApp.name;
		var sUrl = cBrowser.buildUrl(oElement.attr("baseurl"),oParams);
		var sHome = oElement.attr(cRenderQS.HOME_QS);
		
		$("#"+poApp.id+"1").adchart({
			home:sHome,
			appName:poApp.name,
			title:oElement.attr("title1"),	metric:oElement.attr("metric1"), goUrl:sUrl,
			width:cChartConsts.WIDTH_3ACROSS,height:cChartConsts.LETTERBOX_HEIGHT,showZoom:1,showCompare:1,previous_period:0
		});
		$("#"+poApp.id+"2").adchart({
			home:sHome,
			appName:poApp.name,
			title:oElement.attr("title2"),
			metric:oElement.attr("metric2"),
			width:cChartConsts.WIDTH_3ACROSS,height:cChartConsts.LETTERBOX_HEIGHT,showZoom:1,showCompare:1,previous_period:0
		});
		$("#"+poApp.id+"3").adchart({
			home:sHome,
			appName:poApp.name,
			title:oElement.attr("title3"),
			metric:oElement.attr("metric3"),
			width:cChartConsts.WIDTH_3ACROSS,height:cChartConsts.LETTERBOX_HEIGHT,showZoom:1,showCompare:1,previous_period:0
		});
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
				var sCh = poApp.name[0];
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