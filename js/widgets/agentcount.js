'use strict';
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.adagentcount",{
	//#################################################################
	//# Definition
	//#################################################################
	consts:{
		REST_API:"/rest/appagentcount.php"
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
		if (!oElement.attr(cRender.HOME_QS))	$.error("home param missing!");			
		if (!oElement.attr(cRender.APP_ID_QS))	$.error("app id param  missing!");			
		if (!oElement.attr(cRender.APP_QS))		$.error("app param  missing!");			
					
	
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
		var sTotals = oElement.attr(cRender.TOTALS_QS);
		
		oElement.empty();
		if (sTotals)
			oElement.append("<td colspan='2'>status: " +psMessage + "</td>");
		else
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
		if (aResponse.length == 0 ){
			oElement.append(cRender.messagebox("<i>no agents found</i>"));
			if (!oElement.attr(cRender.DONT_CLOSE_CARD_QS))
				if (oElement.attr(cRender.TOTALS_QS))
					cRender.fade_element(oElement);
				else
					cRenderMDL.fade_element_and_hide_card(oElement);
		}else{
			oElement.empty();
			var sTotals = oElement.attr(cRender.TOTALS_QS);
			if (sTotals)
				this.render_totals(poHttp.response);
			else
				this.render(poHttp.response);
		}
	},

	
	//#################################################################
	//# functions
	//#################################################################`
	get_url: function (){
		var oElement = this.element;
		
		var oParams = {};
		oParams[ cRender.APP_ID_QS ] = oElement.attr(cRender.APP_ID_QS);
		oParams[ cRender.TIER_ID_QS ] = oElement.attr(cRender.TIER_ID_QS);
		oParams[ cRender.TOTALS_QS ] = oElement.attr(cRender.TOTALS_QS);
		var sBaseUrl = oElement.attr(cRender.HOME_QS)+this.consts.REST_API;
		var sUrl = cBrowser.buildUrl(sBaseUrl, oParams);		
		return sUrl;
	},
	

	//*******************************************************************
	render: function(paData){
		var oElement = this.element;
		var oThis = this;
		oElement.empty();
		var sHTML = "<table border=1 cellspacing='0' cellpadding='3' width='100%'>";
			sHTML += "<thead><tr><th width='400'>Tier</th><th width='*'>counts</th></tr></thead>";
			sHTML += "<tbody>";
			paData.forEach(
				function(poItem){
					sHTML += "<TR>";
						var sTier = poItem.tier;
						var sApp = oElement.attr(cRender.APP_QS);
						if (sTier === "Totals"){
							if (oElement.attr(cRender.DONT_SHOW_TOTAL_QS)) return;
							sTier = "<b>Totals for "+ sApp +" application</b>";
						}
						sHTML += "<TD align='right' width='400'>" + sTier + "</TD>";
						sHTML += "<TD width='*'>";
							poItem.counts.forEach(
								function (poCount){
									sHTML += cRenderW3.tag(poCount.type +":"+ poCount.count);
								}
							);
						sHTML += "</TD>";
					sHTML += "</TR>";
				}
			);
		sHTML += "</tbody></table>";
		
		oElement.append(sHTML);
	},

	//*******************************************************************
	render_totals: function(paData){
		var oElement = this.element;
		var oThis = this;
		oElement.empty();
		var sHTML = "";
		paData.forEach(
			function(poItem){
				var sApp = oElement.attr(cRender.APP_QS);
				var oParams = {};
				oParams[ cRender.APP_ID_QS ] = oElement.attr(cRender.APP_ID_QS);
				var sUrl = cBrowser.buildUrl("appagents.php", oParams);
				
				sHTML += "<TD align='right' width='400'><a href='" + sUrl + "'>" + sApp + "</a></TD>";
				sHTML += "<TD width='*'>";
					poItem.counts.forEach(
						function (poCount){
							sHTML += cRenderW3.tag(poCount.type +":"+ poCount.count);
						}
					);
				sHTML += "</TD>";
			}
		);
		
		oElement.append(sHTML);
	}	
});