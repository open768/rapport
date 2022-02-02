'use strict';
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.adallagentversions",{
	//#################################################################
	//# Definition
	//#################################################################
	consts:{
		REST_API:"/rest/allagentversions.php"
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
		if (!oElement.tablesorter)		$.error("tablesorter widget is missing! check includes");	
		
		//check for required options
		if (!oElement.attr(cRender.HOME_QS))		$.error("home attr missing!");			
		if (!oElement.attr(cRender.TYPE_QS))		$.error("type attr missing!");			
					
	
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
		
		this.render(poHttp.response);
	},

	
	//#################################################################
	//# functions
	//#################################################################`
	get_url: function (){
		var oElement = this.element;
		var oParams = {};
		oParams[ cRender.APP_ID_QS ] = oElement.attr(cRender.APP_ID_QS);
		oParams[ cRender.TYPE_QS ] = oElement.attr(cRender.TYPE_QS);
		oParams[ cRender.TOTALS_QS ] = oElement.attr(cRender.TOTALS_QS);
		var sBaseUrl = oElement.attr(cRender.HOME_QS)+this.consts.REST_API;
		return cBrowser.buildUrl(sBaseUrl, oParams);
	},
	
	//*******************************************************************
	render: function(poData){
		var oElement = this.element;
		var oThis = this;
		
		oElement.empty();
		//-----------------------------------------------------------------------------------
		var aCounts = poData.counts;
		if (aCounts==null || aCounts.length==0){
			oElement.append(cRender.messagebox("<i>no agents found</i>"));
			return;
		}
		
		//-----------------------------------------------------------------------------------
		//add the totals to the page
		var sHTML = "<div style='border-style:solid'>";
			var iTot = 0;
			for (var i=0; i<aCounts.length; i++){
				var oItem = aCounts[i];
				sHTML += cRenderW3.tag(oItem.name +" ("+ oItem.count + ")") + " ";
				iTot += oItem.count;
			}
			sHTML += cRenderW3.tag("<b>Total ("+ iTot + ")</b>") + " ";
		sHTML += "</div>";
		oElement.append( sHTML );
		
		//-----------------------------------------------------------------------------------
		if (oElement.attr(cRender.TOTALS_QS))
			this.render_agent_totals(poData.detail);
		else
			this.render_details(poData.detail);
	},

	//*******************************************************************
	render_agent_totals: function(paDetails){
		var oElement = this.element;
		var oThis = this;
		
		var sHTML = "<p>&nbsp;<p>Agent Counts:<div style='border-style:solid;column-count:3' >";

		//-----------------------------------------------------------------------------------
		for (var i=0; i<paDetails.length; i++){
			var oItem = paDetails[i];
			sHTML += cRenderW3.tag(oItem.name +" ("+ oItem.count + ")") + " ";
		}
		sHTML += "</div>";
		oElement.append( sHTML );
	},

	
	//*******************************************************************
	render_details: function(paDetails){
		var oElement = this.element;
		var oThis = this;
		
		//-----------------------------------------------------------------------------------
		var sTableID = oElement.attr("id") + "T";
		var oNote = $("<div>",{class:"note"});
		oNote.append("click on table headings to sort   ");
		
		var oBtn = $("<button>");
		oBtn.append("copy table to clipboard");
		oBtn.click(
			function(){
				cBrowser.copy_to_clipboard(sTableID);
			}
		);
		oNote.append(oBtn);
		
		oElement.append(oNote);
		
		//-----------------------------------------------------------------------------------
		var sHTML = "<TABLE border='1' class='maintable' cellspacing='0' id='"+ sTableID +"' width='100%'>";
			var iTot = 0;
			sHTML += "<THEAD><TR>" + 
				"<th width='50'>Agent Type</th>" + 
				"<th width='100'>Application</th>" + 
				"<th width='100'>Tier</th>" + 
				"<th width='100'>Node</th>" + 
				"<th width='100'>Hostname</th>" + 
				"<th width='70'>Version</th>" + 
				"<th width='*'  >Runtime</th>" + 
			"</TR></THEAD>";
			sHTML += "<tbody>";
			for (var i=0; i<paDetails.length; i++){
				
				var oItem = paDetails[i];
				
				//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
				var sApp;
				if (!oItem.app)
					sApp = "";
				else if (typeof oItem.app === "string")
					sApp = oItem.app;
				else
					sApp = oItem.app.name;
				
				var sTier = "";
				if (oItem.tier) sTier = oItem.tier;
				
				var sNode = "";
				if (oItem.node) sNode = oItem.node;
				
				//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
				sHTML += "<TR>" +
					"<td><p class='w3-tooltip' style='font-size:10px'>" + 
						oItem.type + 
						"<span class='w3-text w3-tag' style='position:absolute;left:0;bottom:18px'>" + oItem.installDir + "</span>" +
					"</p></td>" + 
					"<td>" + sApp + "</td>" + 
					"<td>" + sTier + "</td>" + 
					"<td>" + sNode + "</td>" + 
					"<td>" + oItem.hostname + "</td>" + 
					"<td><p class='w3-tooltip' style='font-size:10px'>" + 
						oItem.version + 
						"<span class='w3-text w3-tag' style='position:absolute;left:0;bottom:18px'>" + oItem.raw_version + "</span>" +
					"</p></td>" + 
					"<td>" + oItem.runtime + "</td>" + 
				"</tr>";
				
				//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
				iTot += oItem.count;
			}
		sHTML += "</tbody></table>";
		oElement.append( sHTML );
		
	
		$("#"+sTableID).tablesorter();
	},
	
	
});