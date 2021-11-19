'use strict';
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.adappbackend",{
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
		if (!oElement.attr(cRender.APP_ID_QS))		$.error("app  missing!");			
		if (!oElement.attr(cRender.APP_QS))			$.error("appname  missing!");			
		if (!oElement.attr(cRender.HOME_QS))		$.error("home  missing!");			
					
	
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
		oParams[ cRender.APP_ID_QS ] = oElement.attr(cRender.APP_ID_QS);
		
		
		var sBaseUrl = oElement.attr(cRender.HOME_QS)+this.consts.REST_API;
		sUrl = cBrowser.buildUrl(sBaseUrl, oParams);
		return sUrl;
	},
	
	//*******************************************************************
	render: function(paData){
		var oElement = this.element;
		
		oElement.empty();
		if (oElement.attr(cRender.LIST_MODE_QS))
			this.pr_render_list(paData);
		else
			this.pr_render_charts(paData);
	},
	
	//*******************************************************************
	pr_render_charts: function(paData){
		var oElement = this.element;
		var sbaseID, sID, iCount=0;
		
		sbaseID=  oElement.attr("id")+"_chart_";
		paData.forEach( function(poItem){
			sID = sbaseID +iCount;
			oDiv = $("<DIV>", {type:"adchart", id:sID});
			oDiv.append("please wait loading chart");
			oElement.append(oDiv);
			$("#"+sID).adchart({
				home:oElement.attr(cRender.HOME_QS),
				appName:oElement.attr(cRender.APP_QS),
				title:poItem.name,
				metric:poItem.metric,
				width:341,
				height:125,
				showZoom:1,
				showCompare:1,
				previous_period:0
			});
			iCount++;
		} );
	},
	
	//*******************************************************************
	pr_render_list: function(paData){
		var oElement = this.element;
		
		oDiv = $("<DIV>", {style:"column-count:3"});
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