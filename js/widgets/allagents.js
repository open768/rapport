'use strict';
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.adallagents",{
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
			var sTotals = oElement.attr(cRender.TOTALS_QS);
			oElement.empty();
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
		
		var sBaseUrl = oElement.attr(cRender.HOME_QS)+this.consts.REST_API;
		return sBaseUrl;
	},
	
	//*******************************************************************
	render: function(paData){
		var oElement = this.element;
		var oThis = this;
		var sHome = oElement.attr(cRender.HOME_QS);
		paData.forEach( function(poApp){
			var sHTML = cRenderMDL.card_start("<a type='app' name='"+poApp.name+"'>"+poApp.name+"</a>");		
				sHTML += cRenderMDL.body_start();
					sHTML += "<div "+
						"id='"+poApp.id+"agents' "+
						cRender.APP_ID_QS+"='"+poApp.id+"' "+
						cRender.APP_QS+"='"+poApp.name+"' "+
						cRender.HOME_QS +"='" + oElement.attr(cRender.HOME_QS) + "' "
						"' >Please wait..</div>";
				sHTML += "</DIV>";
				sHTML += cRenderMDL.action_start();
					sHTML += "<div type='admenus' menu='appfunctions' home='"+sHome+"' appname='" + poApp.name +"' appid='"+poApp.id+"' id='"+poApp.id+"menu' style='position: relative;'>"+poApp.name+" .. please wait</div>";
				sHTML += "</DIV>";				
			sHTML += "</DIV><p>";
			oElement.append(sHTML);
			
			//- - - render the menus
			$("#"+poApp.id+"agents").adagentcount(); 
			$("#"+poApp.id+"menu").admenu(); 

		});
	},
	
	//*******************************************************************
	render_totals: function(paData){
		var oElement = this.element;
		var oThis = this;
		var sHome = oElement.attr(cRender.HOME_QS);
		
		var sHTML = cRenderMDL.card_start("Controller Totals:");		
			sHTML += cRenderMDL.body_start();
				sHTML += "<table border=1 cellspacing='0' cellpadding='3' width='100%'>";
					sHTML += "<thead><tr><th width='400'>Tier</th><th width='*'>counts</th></tr></thead><tbody>";
					paData.forEach( function(poApp){
						sHTML += "<TR "+
							"type='countwidget' " +
							"id='"+poApp.id+"agents' "+
							cRender.APP_ID_QS+"='"+poApp.id+"' "+
							cRender.APP_QS+"='"+poApp.name+"' "+
							cRender.HOME_QS +"='" + oElement.attr(cRender.HOME_QS) + "' " +
							cRender.TOTALS_QS +"='" + oElement.attr(cRender.TOTALS_QS) + "'>" +
							"<td colspan='2'>Please wait..</td>" +
						"</TR>";
					});
				sHTML += "</tbody></table>";
			sHTML += "</DIV>";
		sHTML += "</DIV><p>";
		oElement.append( sHTML);
		
		//- - - make into widgets
		$("TR[type=countwidget]").each( function (pi,po){$(po).adagentcount()})
	}	

});