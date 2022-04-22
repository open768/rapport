'use strict';
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.adappcheckup",  $.ck.common, {
	//#################################################################
	//# Definition
	//#################################################################
	consts:{
		REST_API:"/rest/appcheckup.php"
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
		if (!oElement.attr(cRenderQS.APP_ID_QS))		$.error("app ID  missing!");			
		if (!oElement.attr(cRenderQS.HOME_QS))		$.error("home  missing!");			
					
		this.pr_queueMe();
	},
	
	//*******************************************************************
	pr_queueMe: function(){
		var oThis = this;
		var oElement = this.element;

		//set behaviour for widget when it becomes visible
		var oQueue = new cQueueifVisible();
		bean.on(oQueue, "status", 	function(psStatus){oThis.onStatus(psStatus);}	);				
		bean.on(oQueue, "start", 	function(){oThis.onStart();}	);				
		bean.on(oQueue, "result", 	function(poHttp){oThis.onResponse(poHttp);}	);				
		bean.on(oQueue, "error", 	function(poHttp){oThis.onError(poHttp);}	);				
		oQueue.go(oElement, this.get_appcheckup_url());
	},

	
	//*******************************************************************
	onResponse: function(poHttp){
		var oThis = this;
		var oElement = this.element;

		oElement.empty();
		oElement.removeClass();
		
		var aResponse = poHttp.response;
		if (aResponse.length == 0 )
			oElement.append("<i>no errors found</i>");
		else
			this.render_checkup(poHttp.response);
	},

	
	//#################################################################
	//# functions
	//#################################################################`
	get_appcheckup_url: function (){
		var sUrl;
		var oConsts = this.consts;
		var oElement = this.element;
		
		var oParams = {};
		oParams[ cRenderQS.APP_ID_QS ] = oElement.attr(cRenderQS.APP_ID_QS);
		oParams[ cRenderQS.CHECK_ONLY_QS ] = oElement.attr(cRenderQS.CHECK_ONLY_QS);
		
		var sBaseUrl = oElement.attr(cRenderQS.HOME_QS)+this.consts.REST_API;
		sUrl = cBrowser.buildUrl(sBaseUrl, oParams);
		return sUrl;
	},
	
	//*******************************************************************
	pr_output_data: function( psTitle, paData){
		var sHTML = "<tr><th colspan='2'>" + psTitle + "</th></tr>";
		var oMsg, sClass, sHTML;
		for (var i=0; i<paData.length; i++){
			oMsg = paData[i];
			sClass = (oMsg.is_bad?"bad_row":"good_row");
			sHTML += "<tr class='"+sClass+"'><td width='200' style='max-width:200px;overflow-wrap:break-word'>"+oMsg.extra+"</td><td width='*'>"+oMsg.message+"</td></tr>";
		}
		return sHTML;
	},
	
	//*******************************************************************
	render_checkup: function(poData){
		var oThis = this;
		var oElement = this.element;
		var oConsts = this.consts;
		var sClass, i, oMsg, sHome, sAid;
		
		oElement.empty();
		sHome = oElement.attr(cRenderQS.HOME_QS)+"/pages";
		sAid = cRenderQS.APP_ID_QS +"="+oElement.attr(cRenderQS.APP_ID_QS);
		
		var sHTML, sUrl, oButton;
		var sCheckOnly = oElement.attr(cRenderQS.CHECK_ONLY_QS);
		sHTML = "<table border='1' cellspacing='0' width='100%'>";
			sHTML += this.pr_output_data("General", poData.general);
		
			if(!sCheckOnly){
				sUrl=  sHome + "/app/datacollectors.php?"+ sAid
				sHTML += this.pr_output_data("<a href='"+sUrl+"'>Data Collectors</a>", poData.DCs);
			}
			
			sUrl=  sHome + "/trans/apptrans.php?"+ sAid
			sHTML += this.pr_output_data("<a href='"+sUrl+"'>Transactions</a>", poData.BTs);
			
			if(!sCheckOnly){
				sUrl=  sHome + "/app/tiers.php?"+ sAid
				sHTML += this.pr_output_data("<a href='"+sUrl+"'>Tiers</a>", poData.tiers);
			}
			
			if(!sCheckOnly){
				sUrl=  sHome + "/app/appext.php?"+ sAid
				sHTML += this.pr_output_data("<a href='"+sUrl+"'>Backends</a>", poData.backends);
			}
			
			if(!sCheckOnly){
				sUrl=  sHome + "/service/services.php?"+ sAid
				sHTML += this.pr_output_data("<a href='"+sUrl+"'>Service End Points</a>", poData.sendpoints);
			}
		sHTML += "</table>";
		oElement.append(sHTML);
		
		oButton = $("<button>");
		oButton.append("Refresh");
		oButton.click( function(){oElement.empty(); oElement.append("please Wait"); oThis.pr_queueMe()} );
		
		oElement.append(oButton);
	}
});