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

//##########################################################################################
//#
//##########################################################################################

$.widget( "ck.adhistagent",$.ck.common,{
	//#################################################################
	//# Definition
	//#################################################################
	consts:{
		REST_API:"/rest/tierhistoricalagents.php",
		MAX_IDS: 49
	},
	
	vars:{
		count:0
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
			
			sID = oElement.attr("id") + "_MarkStatus";
			var oDiv = $("<DIV>", {id:sID}).append ("progress....");
			oElement.append(oDiv);
		}
	},
	
	//*******************************************************************
	onClickMarkNodes:function(paNodes){
		var oElement = this.element;
		var oThis = this;
		
		if (confirm("please confirm operation")){
			//needs to be split into batches of 100 for larger numbers of agents
			var aNodeIDs=[];
			paNodes.forEach( function(poItem){
				aNodeIDs.push(poItem.id);
				if (aNodeIDs.length > oThis.consts.MAX_IDS){
					oThis.markAgents(aNodeIDs);
					aNodeIDs = [];
				}
			});
			if (aNodeIDs.length > 0) this.markAgents(aNodeIDs);
		}
	},
	
	//*******************************************************************
	markAgents: function (paNodeIds){
		var oElement = this.element;
		var sStatusID = oElement.attr("id") + "_MarkStatus";
		var oStatusDiv = $("#" + sStatusID);
		
		this.vars.count ++;
		var sMarkerID = sStatusID + this.vars.count;
		var oParams = {id:sMarkerID};
		oParams[ cRenderQS.NODE_IDS_QS ] = JSON.stringify(paNodeIds);
		oParams[ cRenderQS.HOME_QS ] = oElement.attr(cRenderQS.HOME_QS);
		oParams[ cRenderQS.TITLE_QS ] = "batch "+ this.vars.count;
		var oMarkerDiv = $("<SPAN>", oParams).append("Marking Batch: " + this.vars.count);
		oStatusDiv.append(oMarkerDiv);
		$("#"+sMarkerID).adexpireagents();
	}
});

//##########################################################################################
//#
//##########################################################################################

$.widget( "ck.adexpireagents",$.ck.common,{
	consts:{
		MARK_API:"/rest/markhistoricalagents.php",
	},
	
	//*******************************************************************
	_create: function(){
		var oThis = this;
		
		//set basic stuff
		var oElement = this.element;
		oElement.uniqueId();
		
		//check for necessary classes
		if (!cQueueifVisible)			$.error("Queue on visible class is missing! check includes");	
		if (!bean)						$.error("bean class is missing! check includes");	
		
		//check for required options
		if (!oElement.attr(cRenderQS.NODE_IDS_QS))		$.error("nodes  missing!");			
		if (!oElement.attr(cRenderQS.HOME_QS))		$.error("home  missing!");			
		if (!oElement.attr(cRenderQS.TITLE_QS))		$.error("title  missing!");			
					
		this.init();
	},
	
	//*******************************************************************
	init:function(){
		var oElement = this.element;
		var oThis = this;
		//set behaviour for widget when it becomes visible
		var oQueue = new cQueueifVisible();
		bean.on(oQueue, "status", 	function(psStatus){oThis.onStatus(psStatus);}	);	//inherited			
		bean.on(oQueue, "start", 	function(){oThis.onStart();}	);					//inherited			
		bean.on(oQueue, "result", 	function(poHttp){oThis.onResponse(poHttp);}	);				
		bean.on(oQueue, "error", 	function(poHttp){oThis.onError(poHttp);}	);		//inherited			
		var oParams = {};
		oParams[cRenderQS.NODE_IDS_QS] = oElement.attr(cRenderQS.NODE_IDS_QS);
		oQueue.go(oElement, this.get_url(), oParams);
	},
	
	//*******************************************************************
	get_url: function (){
		var oElement = this.element;
		
		var sUrl = oElement.attr(cRenderQS.HOME_QS)+this.consts.MARK_API;
		return sUrl;
	},
	
	//*******************************************************************
	onResponse: function(poHttp){
		var oElement = this.element;
		oElement.empty();
		oElement.append('<i class="material-icons" style="font-size:48px;color:green">done</i>');
	}


});
