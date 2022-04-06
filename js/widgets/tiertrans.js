'use strict';
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.adtiertrans",  $.ck.common, {
	//#################################################################
	//# Definition
	//#################################################################
	consts:{
		REST_API:"/rest/tiertrans.php"
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
		if (!oElement.attr(cRenderQS.TIER_ID_QS))		$.error("tier ID  missing!");			
		if (!oElement.attr(cRenderQS.APP_ID_QS))		$.error("app ID  missing!");			
		if (!oElement.attr(cRenderQS.HOME_QS))		$.error("home  missing!");			
					
	
		//set behaviour for widget when it becomes visible
		var oQueue = new cQueueifVisible();
		bean.on(oQueue, "status", 	function(psStatus){oThis.onStatus(psStatus);}	);				
		bean.on(oQueue, "start", 	function(){oThis.onStart();}	);				
		bean.on(oQueue, "result", 	function(poHttp){oThis.onResponse(poHttp);}	);				
		bean.on(oQueue, "error", 	function(poHttp){oThis.onError(poHttp);}	);				
		oQueue.go(oElement, this.get_tiertrans_url());
	},

	
	//*******************************************************************
	onResponse: function(poHttp){
		var oThis = this;
		var oElement = this.element;

		oElement.empty();
		oElement.removeClass();
		
		var aResponse = poHttp.response;
		if (aResponse.length == 0 )
			oElement.closest(".mdl-card").remove();
		else
			this.render_tiertrans(poHttp.response);
	},

	
	//#################################################################
	//# functions
	//#################################################################`
	get_tiertrans_url: function (){
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
	render_tiertrans: function(poData){
		var oThis = this;
		var oElement = this.element;
		var oConsts = this.consts;
		
		oElement.empty();
		
		var sTransUrlBase = "transdetails.php?"+
			cRenderQS.APP_ID_QS+"="+oElement.attr(cRenderQS.APP_ID_QS) + "&" +
			cRenderQS.TIER_ID_QS+"="+oElement.attr(cRenderQS.TIER_ID_QS) ;
			
		if (poData.active.length>0){
			var oTable = $("<table>", {border:1,cellspacing:0,width:"100%"});
			oTable.append("<tr><th width='40'></th><th width='*'>Name</th><th width='50'>count</th><th width='50'>avg</th><th width='50'>max</th></tr>");
			for (var i=0; i<poData.active.length; i++){
				var oRow = $("<TR>");
				var oItem = poData.active[i];
				var sFragment = 
					"<button " +
						"class='mdl-button mdl-js-button mdl-button--raised mdl-button--colored mdl-js-ripple-effect'" +  
						"onclick='window.stop();window.open(\"" + oItem.url + "\",\"appdynamics\")');return false;'>"+ 
							 "<i class='material-icons-outlined'>north_east</i>" +
					"</button>";
				oRow.append("<td>"+ sFragment+ "</td>");
				
				var sTransUrl = sTransUrlBase + 
					"&" + cRenderQS.TRANS_ID_QS + "=" + oItem.id +
					"&" + cRenderQS.TRANS_QS + "=" + oItem.name;
				oRow.append("<td><a href='"+sTransUrl+"'>"+oItem.name+"</a></td>");
				oRow.append("<td>"+oItem.count+"</td>");
				oRow.append("<td>"+oItem.avg+"</td>");
				oRow.append("<td>"+oItem.max+"</td>");
				oTable.append(oRow);
			}
			oElement.append(oTable);
			oElement.append("<p>");
		}else{
			var sHTML = "<div class='w3-panel w3-blue w3-round-large w3-padding-16 w3-leftbar'>There are no Active BTs in this time period</div>";
			oElement.append(sHTML);
		}
		
		if (poData.inactive.length>0){
			var sHTML = "<div class='w3-panel w3-blue w3-round-large w3-padding-16 w3-leftbar'>";
				sHTML += "There are "+ poData.inactive.length + " inactive BTs:<p>";
				sHTML += "<div  style='column-count:3'>";
					for(var i=0; i<poData.inactive.length; i++)
						sHTML+=poData.inactive[i]+"<br> ";
				sHTML += "</div>";
			sHTML += "</div>";
			oElement.append(sHTML);
		}
	}
});