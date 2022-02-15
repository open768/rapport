'use strict';
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.adaudit",{
	//#################################################################
	//# Definition
	//#################################################################
	consts:{
		REST_API:"/rest/audit.php"
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
		
		//check for required options
		if (!oElement.attr(cRenderQS.HOME_QS))			$.error("home  missing!");			
		if (!oElement.attr(cRenderQS.AUDIT_TYPE_QS))	$.error("audit type missing!");			
					
	
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
		
		var sType = oElement.attr(cRenderQS.AUDIT_TYPE_QS);
		if (sType === cRenderQS.AUDIT_TYPE_LIST_ACTIONS)
			this.render_actions(poHttp.response);
		else
			this.render_action(poHttp.response);
	},

	
	//#################################################################
	//# functions
	//#################################################################`
	get_url: function (){
		var oElement = this.element;
		
		var oParams = {};
		oParams[ cRenderQS.AUDIT_TYPE_QS ] = oElement.attr(cRenderQS.AUDIT_TYPE_QS);
		if (oElement.attr(cRenderQS.AUDIT_TYPE_QS) === cRenderQS.AUDIT_TYPE_ACTION)
			if (!oElement.attr(cRenderQS.AUDIT_FILTER))
				$.error("Filter missing");
		
		oParams[ cRenderQS.AUDIT_FILTER ] = oElement.attr(cRenderQS.AUDIT_FILTER);
		var sBaseUrl = oElement.attr(cRenderQS.HOME_QS)+this.consts.REST_API;
		var sUrl = cBrowser.buildUrl(sBaseUrl, oParams);		
		return sUrl;
	},
	
	//*******************************************************************
	render_actions: function (paList){
		var oElement = this.element;
		if (paList.length == 0)
			oElement.append(cRender.messagebox("no Actions found"));
		else{
			oElement.append("These are the audit activities available<p>");
			paList.forEach(
				function(poAction){
					var oParams = {};
					oParams[cRenderQS.AUDIT_TYPE_QS] = cRenderQS.AUDIT_TYPE_ACTION;
					oParams[cRenderQS.AUDIT_FILTER] = poAction.name;
					var sUrl = cBrowser.buildUrl("audit.php", oParams);	
					
					var sHTML = cRender.button( poAction.name + ": " + poAction.count, sUrl);
					oElement.append(sHTML);
					oElement.append(" ");
				}
			);
		}
	},
	
	//*******************************************************************
	render_action: function(paList){
		var oElement = this.element;			
		
		if (paList.length == 0)
			oElement.append(cRender.messagebox("no details found"));
		else{
			var sFilter = oElement.attr(cRenderQS.AUDIT_FILTER);
			
			var sHTML = "<h3>Audit entries for " + sFilter + "</h3>";
			sHTML += "<DIV style='column-count:3'>";
			sHTML += "<ul>";
			paList.forEach( function(poItem){
				sHTML += "<li>" + poItem.auditDateTime ;
				switch (sFilter){
					case cRenderQS.AUDIT_FILTER_LOGIN:
					case cRenderQS.AUDIT_FILTER_LOGIN_FAILED:
					case cRenderQS.AUDIT_FILTER_LOGOUT:
						sHTML += "<br>User:" + poItem.userName;
						break;
					case cRenderQS.AUDIT_FILTER_OBJECT:
						sHTML += "<br>User:" + poItem.userName;
						sHTML += "<br>objectType:" + poItem.objectType;
						sHTML += "<br>objectName:" + poItem.objectName;
						break;
					
					default:
						sHTML += "?";
				}
			});
			sHTML += "</ul></div>";
			
			oElement.append(sHTML);
		}
	}
	
});