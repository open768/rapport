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
		if (aCounts==null){
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
		if (!oElement.attr(cRender.TOTALS_QS)){
			oElement.append($("<div>",{class:"note"}).append("click on table headings to sort"));
			
			//-----------------------------------------------------------------------------------
			var sID = oElement.attr("id") + "T";
			sHTML = "<table border='1' class='maintable' cellspacing='0' id='"+ sID +"' width='100%'>";
				iTot = 0;
				sHTML += "<thead><TR><th width='200'>Application</th><th width='100'>Node</th><th width='100'>hostname</th><th width='100'>Version</th><th width='*'>Runtime</th></tr></thead>";
				sHTML += "<tbody>";
				var aItems = poData.detail;
				for (var i=0; i<aItems.length; i++){
					var oItem = aItems[i];
					var sApp ;
					if (!oItem.app)
						sApp = "no Application";
					else if (typeof oItem.app === "string")
						sApp = oItem.app;
					else
						sApp = oItem.app.name;
					
					sHTML += "<TR>" +
						"<td>" + sApp + "</td>" + 
						"<td>" + oItem.node + "</td>" + 
						"<td>" + oItem.hostname + "</td>" + 
						"<td>" + oItem.version + "</td>" + 
						"<td>" + oItem.runtime + "</td>" + 
					"</tr>";
					iTot += oItem.count;
				}
			sHTML += "</tbody></table>";
			oElement.append( sHTML );
			
		
			$("#"+sID).tablesorter();
		}
		
	}
});