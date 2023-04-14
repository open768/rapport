/* globals bean,cQueueifVisible,cRenderQS,cLocations*/

// eslint-disable-next-line no-unused-vars
var cListDash = {
	Template:null,
	
	//*************************************************************
	onClickSearch:function (){
		window.stop()
		this.getDashList()
	},

	//*************************************************************
	getDashList: function(){
		var oElement = $("#results")
		var oQueue = new cQueueifVisible()
		var self=this

		bean.on(oQueue, "status", 	function(psStatus){self.onListStatus(psStatus)} )
		bean.on(oQueue, "result", 	function(poHttp){self.onListResult(poHttp)} )
		bean.on(oQueue, "error", 	function(poHttp){self.onListError(poHttp)} )
		oQueue.go(oElement, cLocations.rest + "/listdash.php")
	},

	//*************************************************************
	// eslint-disable-next-line no-unused-vars
	onListError: function (poHttp){
		var oElement = $("#results")
		oElement.empty()
		oElement.addClass("ui-state-error")
		oElement.append("There was an error  getting data  ")
	},

	//*************************************************************
	onListStatus:function (psStatus){
		var oElement = $("#results")
		oElement.empty()
		oElement.append("status: " + psStatus)
	},

	//*************************************************************
	onListResult: function (poHttp){
		var oElement = $("#results")
		var aResponse = poHttp.response
		oElement.empty()
		if (aResponse.length == 0){
			oElement.addClass("ui-state-error")
			oElement.append("there are no dashboards configured")
		}else{
			var sSearch = $("#search").val()
			oElement.removeClass()
			for (var i=0 ; i<aResponse.length; i++){
				var oDash = aResponse[i]
				
				var oOptions = {
					style: "border:1px solid black",
					type: "dashsearch",
				}
				oOptions[cRenderQS.DASH_NAME_QS] = oDash.name
				oOptions[cRenderQS.DASH_ID_QS] = oDash.id
				oOptions[cRenderQS.SEARCH_QS] = sSearch
				oOptions[cRenderQS.DASH_URL_TEMPLATE] = this.Template
				oOptions[cRenderQS.HOME_QS] = cLocations.home
				var oDiv = $("<DIV>", oOptions)
				oDiv.append("Searching dashboard " + oDash.name + "...")
				oElement.append(oDiv)
			}
		}
		$("DIV[type=dashsearch]").each( function(piIndex, poEl){ $(poEl).addashsearch()})
	}

}