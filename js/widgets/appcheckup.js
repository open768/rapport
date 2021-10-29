//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.adappcheckup",{
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
		if (!oElement.attr(cRender.APP_ID_QS))		$.error("app ID  missing!");			
		if (!oElement.attr(cRender.HOME_QS))		$.error("home  missing!");			
					
	
		//set behaviour for widget when it becomes visible
		var oQueue = new cQueueifVisible();
		bean.on(oQueue, "status", 	function(psStatus){oThis.onStatus(psStatus);}	);				
		bean.on(oQueue, "start", 	function(){oThis.onStart();}	);				
		bean.on(oQueue, "result", 	function(poHttp){oThis.onResponse(poHttp);}	);				
		bean.on(oQueue, "error", 	function(poHttp){oThis.onError(poHttp);}	);				
		oQueue.go(oElement, this.get_appcheckup_url());
	},


	//*******************************************************************
	onStatus: function(psMessage){
		var oElement = this.element;
		oElement.empty();
		oElement.append("status: " +psMessage);
		},
	
	//*******************************************************************
	onError: function(poHttp){
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

		if (cCharts.queue.stopping) return;
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
		oParams[ cRender.APP_ID_QS ] = oElement.attr(cRender.APP_ID_QS);
		
		
		var sBaseUrl = oElement.attr(cRender.HOME_QS)+this.consts.REST_API;
		sUrl = cBrowser.buildUrl(sBaseUrl, oParams);
		return sUrl;
	},
	
	//*******************************************************************
	pr_output_data: function( psTitle, paData){
		var sHTML = "<tr><th colspan='2'>" + psTitle + "</th></tr>";
		for (var i=0; i<paData.length; i++){
			oMsg = paData[i];
			sClass = (oMsg.is_bad?"bad_row":"good_row");
			sHTML += "<tr class='"+sClass+"'><td width='200'>"+oMsg.extra+"</td><td width='*'>"+oMsg.message+"</td></tr>";
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
		sHome = oElement.attr(cRender.HOME_QS)+"/pages";
		sAid = cRender.APP_ID_QS +"="+oElement.attr(cRender.APP_ID_QS);
		

		sHTML = "<table border='1' cellspacing='0' width='100%'>";
			sUrl=  sHome + "/app/datacollectors.php?"+ sAid
			sHTML += this.pr_output_data("<a href='"+sUrl+"'>Data Collectors</a>", poData.DCs);
			sUrl=  sHome + "/trans/apptrans.php?"+ sAid
			sHTML += this.pr_output_data("<a href='"+sUrl+"'>Transactions</a>", poData.BTs);
			sUrl=  sHome + "/app/tiers.php?"+ sAid
			sHTML += this.pr_output_data("<a href='"+sUrl+"'>Tiers</a>", poData.tiers);
			sUrl=  sHome + "/app/appext.php?"+ sAid
			sHTML += this.pr_output_data("<a href='"+sUrl+"'>Backends</a>", poData.backends);
		sHTML += "</table>";
		
		oElement.append(sHTML);
	}
});