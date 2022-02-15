'use strict';
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.adtierothertrans",{
	//#################################################################
	//# Definition
	//#################################################################
	consts:{
		REST_API:"/rest/tierothertrans.php"
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
		if (!cQueueifVisible)	$.error("Queue on visible class is missing! check includes");	
		if (!bean)				$.error("bean class is missing! check includes");	
		if (!cRender)			$.error("cRender class is missing! check includes");	
		
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
		oQueue.go(oElement, this.get_url());
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
			oElement.append(cRender.messagebox("There was an error  getting data"));
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
		if (aResponse.length == 0 )
			oElement.append("nothing found");
		else
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
		var oThis = this;
		var oElement = this.element;
		var oConsts = this.consts;
		
		oElement.empty();
		if (poData.trans.id == null || poData.names.length == 0){
			oElement.append(cRender.messagebox("no overflow transaction found"));
			cRenderMDL.fade_element_and_hide_card(oElement);
		}else{
			oElement.append("<b>The following transactions are in 'All Other Traffic' for this tier</b><br>");
			var sHTML = "<table border='1' cellspacing='0' cellpadding='3'>";
			sHTML += "<tr><th width='500'>name</th><th width='100'>Count</th><th width='100'>Action</th></tr>";
			for ( var i=0; i<poData.names.length; i++){
				var oItem = poData.names[i]
				var sButtonID = oElement.attr("id") + "BUT" + i; 
				sHTML += "<tr>" + 
					"<td align='right'>" + oItem.name + "</td>" + 
					"<td align='middle'>" + oItem.count + "</td>" +
					"<td align='middle'><button type='BTregister' btname='"+ oItem.name +"' id='" + sButtonID + "'>register</button></td>" +
				"</tr>";
			}
			sHTML += "</table>";
			oElement.append(sHTML);
			$("button[type=BTregister]").each(
				function (pi, poEl){
					oThis.init_register_button(poEl);
				}
			);
		}
	},
	
	//*******************************************************************
	init_register_button: function (poButton){
		var oThis = this;
		var oButton = $(poButton);
		oButton.click( 
			function(){
				oThis.onclickregister(oButton.attr("btname"));
			}    
		);
	},
	
	onclickregister: function(psName){
		alert(" tbd: " + psName);
	}
	
	
	
});