'use strict';
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.adhistagentstiers", $.ck.common, {
	//#################################################################
	//# Definition
	//#################################################################
	consts:{
		REST_API:"/rest/listtiers.php"
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
		if (!oElement.adagentcount)		$.error("missing adagentcount widget! check includes");	
		
		//check for required options
		if (!oElement.attr(cRenderQS.APP_ID_QS))		$.error("app  missing!");			
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
		
		var  aResponse = poHttp.response;
		this.render_tiers(aResponse);
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
	render_tiers: function(paTiers){
		var oElement = this.element;
		var sHTML= "";
		
		//error handling - no tiers
		if (paTiers.length == 0){
			sHTML = cRenderMDL.card_start("Summary");
				sHTML += cRenderMDL.body_start();
					sHTML += cRender.messagebox("no tiers found");
				sHTML += "</div>";
			sHTML += "</div>";
			oElement.append(sHTML);
			return;
		}
		
		// a card showing summary of agents
		var sID = oElement.attr("id") + "_sum"
		sHTML = cRenderMDL.card_start("Summary");
			sHTML += cRenderMDL.body_start();
				sHTML += "<div "+
					"id='" + sID + "' " +
					cRenderQS.APP_ID_QS+"='"+oElement.attr(cRenderQS.APP_ID_QS)+"' "+
					cRenderQS.APP_QS+"='"+ paTiers[0].app.name +"' "+
					cRenderQS.HOME_QS +"='" + oElement.attr(cRenderQS.HOME_QS) + "' " + 
					", Please wait.." + 
				"</div>";
			sHTML += "</div>";
		sHTML += "</div></div><p>";
		
		oElement.append(sHTML);
		$("#"+sID).adagentcount();

		// a card that lists the tiers
		sHTML = cRenderMDL.card_start("Tiers");
			sHTML += cRenderMDL.body_start();
				sHTML += "<div style='column-count:3'>"
					paTiers.forEach(
						function (poTier){
							sHTML += "<a href='#" + poTier.name + "'>" + poTier.name + "</a><br>";
						}
					);
				sHTML += "</div>";
			sHTML += "</div>";
			sHTML += cRenderMDL.action_start();
				sHTML += "filter WIP";
			sHTML += "</div>";
		sHTML += "</div></div><p>";
		oElement.append(sHTML);
		
		
		// a card for each tier
		sHTML= "";
		paTiers.forEach(
			function (poTier){
				sHTML += cRenderMDL.card_start("<a type='tier' name='" + poTier.name + "' >" + poTier.name + "</a>");
					sHTML += cRenderMDL.body_start();
					if (poTier.name === "Machine Agent"){
						sHTML += cRender.messagebox("skipping machine agent tier");
					}else{
						sHTML += 
							"<div " + 
								"type='histwidget' " +
								cRenderQS.APP_ID_QS + "='" + poTier.app.id +"' " +
								cRenderQS.APP_QS + "='" + poTier.app.name +"' " +
								cRenderQS.HOME_QS + "='" + oElement.attr(cRenderQS.HOME_QS) +"' " +
								cRenderQS.TIER_ID_QS + "='" + poTier.id +"' >" +
									"Please wait" +
							"</div>";
						sHTML += "</div>";
						sHTML += cRenderMDL.action_start();
							sHTML += cMenusCode.tierfunctions(poTier, oElement.attr(cRenderQS.HOME_QS));
						sHTML += "</div>";
					}
				sHTML += "</div></div><p>";
			}
		);
		
		oElement.append(sHTML);
		//now convert widgets TBD
		$("DIV[type=histwidget]").each (
			function (pi,pEl){
				$(pEl).adhistagent();
			}
		);
		cMenus.renderMenus();
	},
	
});

$.widget( "ck.adhistagent",{
	//#################################################################
	//# Definition
	//#################################################################
	consts:{
		REST_API:"/rest/tierhistoricalagents.php",
		MARK_API:"/rest/markhistoricalagents.php"
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
		if (!oElement.attr(cRenderQS.APP_ID_QS))		$.error("app  missing!");			
		if (!oElement.attr(cRenderQS.TIER_ID_QS))		$.error("tier  missing!");			
		if (!oElement.attr(cRenderQS.HOME_QS))		$.error("home  missing!");			
					
		this.init();
	},
	
	init:function(){
		var oElement = this.element;
		var oThis = this;
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
		oParams[ cRenderQS.TIER_ID_QS ] = oElement.attr(cRenderQS.TIER_ID_QS);
		
		var sBaseUrl = oElement.attr(cRenderQS.HOME_QS)+this.consts.REST_API;
		sUrl = cBrowser.buildUrl(sBaseUrl, oParams);
		return sUrl;
	},
	
	//*******************************************************************
	render: function(poData){
		var oElement = this.element;
		var oThis = this;
		
		//display the count of agents for tier
		var sID = oElement.attr("id") + "_Count"
		var sHTML = "<div "+
			"id='" + sID + "' " +
			cRenderQS.APP_ID_QS+"='"+oElement.attr(cRenderQS.APP_ID_QS)+"' "+
			cRenderQS.APP_QS+"='"+oElement.attr(cRenderQS.APP_QS)+"' "+
			cRenderQS.TIER_ID_QS+"='"+oElement.attr(cRenderQS.TIER_ID_QS)+"' "+
			cRenderQS.HOME_QS +"='" + oElement.attr(cRenderQS.HOME_QS) + "' " + 
			cRenderQS.DONT_SHOW_TOTAL_QS + "='1' " +
			cRenderQS.DONT_CLOSE_CARD_QS + "='1'>" +
			"number of nodes counted: " + poData.node_count + ", Please wait.." + 
		"</div>";
		oElement.append(sHTML);
		$("#"+sID).adagentcount();
			
		//display the results
		oElement.append( cRender.messagebox(poData.status));
		sHTML = "";
		var iNodes = poData.nodes.length;
		if (iNodes > 0){
			var sIds = "";
			var sID = oElement.attr("id") + "_Mark"
			sHTML += "<button id='" + sID + "'> Mark these " + iNodes + " nodes as historical</button>";
			oElement.append(sHTML);
			$("#"+sID).click( 
				function(){oThis.onClickMarkNodes(poData.nodes);}
			);
		}
	},
	
	//*******************************************************************
	onClickMarkNodes:function(paNodes){
		var oElement = this.element;
		
		if (confirm("please confirm operation")){
			var aNodes = [];
			paNodes.forEach(
				function(poNode){
					if (poNode.id == null) return;
					aNodes.push(poNode.id) ;
				}
			);
			
			var oParams = {};
			oParams[cRenderQS.NODE_IDS_QS] = JSON.stringify(aNodes);
			var sAPI = oElement.attr(cRenderQS.HOME_QS) + this.consts.MARK_API;
			var sUrl = cBrowser.buildUrl(sAPI, oParams);
			$.get(sUrl);
			
			this.init();
		}
	}
});