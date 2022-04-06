'use strict';
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.adrbacgroupusers", $.ck.common, {
	//#################################################################
	//# Definition
	//#################################################################
	consts:{
		REST_API:"/rest/rbacgroupusers.php"
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
		if (!oElement.attr(cRenderQS.GROUP_NAME_QS))		$.error("group name missing!");
		if (!oElement.attr(cRenderQS.GROUP_ID_QS))		$.error("group id missing!");
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
		oParams[ cRenderQS.GROUP_NAME_QS ] = oElement.attr(cRenderQS.GROUP_NAME_QS);
		oParams[ cRenderQS.GROUP_ID_QS ] = oElement.attr(cRenderQS.GROUP_ID_QS);

		var sBaseUrl = oElement.attr(cRenderQS.HOME_QS)+this.consts.REST_API;
		sUrl = cBrowser.buildUrl(sBaseUrl, oParams);
		return sUrl;
	},

	//*******************************************************************
	render: function(poGroup){
		var oElement = this.element;

		if (poGroup.users.length == 0 ){
			oElement.append(cRender.messagebox("<i>no users found</i>"));
			return;
		}
		var oMainDiv  = $("<DIV>", {style:"column-count:4"});
		poGroup.users.forEach(
			function(poUser){
				var oDiv  = $("<DIV>");
				var sDetails = poUser.display_name + " (" + poUser.username + ")";
				if (poUser.email)
					sDetails = "<a href='mailto:" + poUser.email + "'>" + sDetails  + "</a>";
				oDiv.append(sDetails);
				oMainDiv.append(oDiv);
			}
		);
		oElement.append(oMainDiv);
	},

});