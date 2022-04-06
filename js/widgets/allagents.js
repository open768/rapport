'use strict';
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.adallagents", $.ck.common, {
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
		
		//check for necessary classes
		//if (!oElement.adagentcount)			$.error("adagentcount widget is missing! check includes");	
		if (!cQueueifVisible)			$.error("Queue on visible class is missing! check includes");	
		if (!bean)						$.error("bean class is missing! check includes");	
		
		//check for required options
		if (!oElement.attr(cRenderQS.HOME_QS))					$.error("home  missing!");			
		if (!oElement.attr(cRenderQS.AGENT_COUNT_TYPE_QS))		$.error("count type missing!");			
					
	
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
		oElement.removeClass();
		
		var aResponse = poHttp.response;
		if (aResponse.length == 0 )
			oElement.append(cRender.messagebox("<i>no applications found</i>"));
		else{
			oElement.empty();
			var sCountType = oElement.attr(cRenderQS.AGENT_COUNT_TYPE_QS);
			switch (sCountType){
				case cRenderQS.COUNT_TYPE_APPD:
					var sTotals = oElement.attr(cRenderQS.TOTALS_QS);
					if (sTotals)
						this.render_appd_totals(poHttp.response);
					else
						this.render_appd(poHttp.response);
					break;
					
				case cRenderQS.COUNT_TYPE_ACTUAL:
					this.render_actual(poHttp.response);
					break;
					
				default:
					oElement.append(cRender.messagebox("<i>unknown count type</i>"));
			}
		}
	},

	
	//#################################################################
	//# functions
	//#################################################################`
	get_url: function (){
		var oElement = this.element;
		
		var oParams = {};
		oParams[ cRenderQS.AGENT_COUNT_TYPE_QS ] = oElement.attr(cRenderQS.AGENT_COUNT_TYPE_QS);
		var sBaseUrl = oElement.attr(cRenderQS.HOME_QS)+this.consts.REST_API;
		var sUrl = cBrowser.buildUrl(sBaseUrl, oParams);		
		return sBaseUrl;
	},
	
	//*******************************************************************
	render_actual: function(paData){
		var oElement = this.element;
		
		//add a summary box that will receive events
		var sHTML = cRenderMDL.card_start("summary");		
			sHTML += cRenderMDL.body_start();
				sHTML += cRender.messagebox("WIP")	;	
			sHTML += "</div>";
		sHTML += "</div><p>";
		oElement.append(sHTML);
		
		//add a Card that will get the agent count for nodes in each app and tier
		var sHTML = cRenderMDL.card_start("details");		
			sHTML += cRenderMDL.body_start();
				sHTML += cRender.messagebox("WIP")	;	
			sHTML += "</div>";
		sHTML += "</div><p>";
		oElement.append(sHTML);
	},
	
	//*******************************************************************
	render_appd: function(paData){
		var oElement = this.element;
		var oThis = this;
		var sHome = oElement.attr(cRenderQS.HOME_QS);
		paData.forEach( function(poApp){
			var sHTML = cRenderMDL.card_start("<a type='app' name='"+poApp.name+"'>"+poApp.name+"</a>");		
				sHTML += cRenderMDL.body_start();
					sHTML += "<div "+
						"id='"+poApp.id+"agents' "+
						cRenderQS.APP_ID_QS+"='"+poApp.id+"' "+
						cRenderQS.APP_QS+"='"+poApp.name+"' "+
						cRenderQS.HOME_QS +"='" + oElement.attr(cRenderQS.HOME_QS) + "'>" + 
							"Please wait.." + 
					"</div>";
				sHTML += "</div>";
				sHTML += cRenderMDL.action_start();
					sHTML += cMenusCode.appfunctions( poApp, sHome, poApp.id+"menu");
					
					var oParams = {};
					oParams[ cRenderQS.APP_ID_QS ] = poApp.id;
					var sUrl = cBrowser.buildUrl("check_historical.php", oParams);
					var sButton = cRender.button("historical agents", sUrl);
					sHTML += sButton;
					
					sUrl = cBrowser.buildUrl("appagents.php", oParams);
					sButton = cRender.button("agent versions", sUrl);
					sHTML += sButton;
				sHTML += "</div>";				
			sHTML += "</div><p>";
			oElement.append(sHTML);
			
			//- - - render the menus
			$("#"+poApp.id+"agents").adagentcount(); 
			$("#"+poApp.id+"menu").admenu(); 

		});
	},
	
	//*******************************************************************
	render_appd_totals: function(paData){
		var oElement = this.element;
		var oThis = this;
		var sHome = oElement.attr(cRenderQS.HOME_QS);
		
		var sHTML = cRenderMDL.card_start("Controller Totals:");		
			sHTML += cRenderMDL.body_start();
				sHTML += "<table border=1 cellspacing='0' cellpadding='3' width='100%'>";
					sHTML += "<thead><tr><th width='400'>App</th><th width='*'>counts</th></tr></thead><tbody>";
					paData.forEach( function(poApp){
						sHTML += "<TR "+
							"type='countwidget' " +
							"id='"+poApp.id+"agents' "+
							cRenderQS.APP_ID_QS+"='"+poApp.id+"' "+
							cRenderQS.APP_QS+"='"+poApp.name+"' "+
							cRenderQS.HOME_QS +"='" + oElement.attr(cRenderQS.HOME_QS) + "' " +
							cRenderQS.TOTALS_QS +"='" + oElement.attr(cRenderQS.TOTALS_QS) + "'>" +
							"<td colspan='2'>Please wait..</td>" +
						"</TR>";
					});
				sHTML += "</tbody></table>";
			sHTML += "</DIV>";
		sHTML += "</DIV><p>";
		oElement.append( sHTML);
		
		//- - - make into widgets
		$("TR[type=countwidget]").each( function (pi,pEl){$(pEl).adagentcount()})
	}	

});