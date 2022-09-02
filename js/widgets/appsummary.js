'use strict';
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.adappsummary", $.ck.common, {
	//#################################################################
	//# Definition
	//#################################################################
	consts:{
		REST_API:"/rest/appssummary.php"
	},

	//#################################################################
	//# Constructor
	//#################################################################`
	_create: function(){
		var oThis = this;

		//set basic stuff
		var oElement = this.element;
		oElement.uniqueId();

		//check for necessary attributes
		if (!oElement.attr(cRenderQS.HOME_QS))		$.error("home  missing!");			
		if (!oElement.attr(cRenderQS.APPS_QS))		$.error("list of apps missing!");			
		if (!oElement.attr(cRenderQS.LOGIN_TOKEN_QS))		$.error("login token missing!");			
		if (!oElement.attr(cRenderQS.CONTROLLER_URL_QS))		$.error("controller url missing!");			
		

		//check for necessary classes
		if (!cQueueifVisible)			$.error("Queue on visible class is missing! check includes");
		if (!bean)						$.error("bean class is missing! check includes");


		//set behaviour for widget when it becomes visible
		var oQueue = new cQueueifVisible();
		bean.on(oQueue, "status", 	function(psStatus){oThis.onStatus(psStatus);}	); //inherited
		bean.on(oQueue, "start", 	function(){oThis.onStart();}	); //inherited
		bean.on(oQueue, "result", 	function(poHttp){oThis.onResponse(poHttp);}	);
		bean.on(oQueue, "error", 	function(poHttp){oThis.onError(poHttp);}	); //inherited
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
			this.render(poHttp.response);
		}
	},


	//#################################################################
	//# functions
	//#################################################################`
	get_url: function (){
		var oElement = this.element;

		var sBaseUrl = oElement.attr(cRenderQS.HOME_QS)+this.consts.REST_API;
		var oParams = {};
		oParams[ cRenderQS.APPS_QS ] = oElement.attr(cRenderQS.APPS_QS);
		oParams[ cRenderQS.LOGIN_TOKEN_QS ] = oElement.attr(cRenderQS.LOGIN_TOKEN_QS);
		var sUrl = cBrowser.buildUrl(sBaseUrl, oParams);
		
		return sUrl;
	},

	//*******************************************************************
	render: function(paData){
		var oElement = this.element;
		var sHome = oElement.attr(cRenderQS.HOME_QS);
		var oThis = this;

		oElement.removeClass();
		oElement.addClass("ui-widget");
		
		var sHTML = "<table border='1' cellspacing='0' cellpadding='4'>";
		sHTML += "<tr>" +
			"<th>Name</th>" +
			"<th>Health</th>" +
			"<th>Calls</th>" +
			"<th>Slow Calls</th>" +
			"<th>Very Slow Calls</th>" +
			"<th>Response Time (ms)</th>" +
			"<th>Errors</th>" +
		"</tr>";
			
		var sControllerUrl = oElement.attr(cRenderQS.CONTROLLER_URL_QS)
		for (var i=0; i<paData.length; i++){
			var oDetail = paData[i];
			
			var sImgUrl = sControllerUrl + "/images/health/" + oDetail.severitySummary.performanceState.toLowerCase() + ".svg";
			var sAdUrl = sControllerUrl + "/#/location=APP_DASHBOARD&application=" + oDetail.id;
			
			sHTML += "<tr>" +
				"<td width='200' align='right'><a href='" + sAdUrl +"' target='appd'>" + oDetail.name + "</a></td>" +
				"<td width='50' align='middle'><img src='" + sImgUrl + "' alt='" + oDetail.severitySummary.performanceState + "'></td>" +
				"<td width='100' align='right'>"+ oDetail.numberOfCalls + "</td>" +
				"<td width='100' align='right'>TBD</td>" +
				"<td width='100' align='right'>TBD</td>" +
				"<td width='100' align='right'>" + oDetail.averageResponseTime+ "</td>" +
				"<td width='100' align='right'>" + oDetail.numberOfErrors+ "</td>" +
			"</tr>";
		}	
		sHTML += "</TABLE>";
		oElement.append(sHTML);
		
		//TBD make async calls to get  health
	}
});