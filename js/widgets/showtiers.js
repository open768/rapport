'use strict'
/*global cBrowser, cRender,cRenderQS,bean,cQueueifVisible,cCharts,cADMetrics,cChartConsts */
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.adshowtiers", $.ck.common, {
	//#################################################################
	//# Definition
	//#################################################################
	consts:{
		REST_API:"/rest/listtiers.php"
	},
	
	//#################################################################
	//# Constructor
	//#################################################################`
	_create: function(){
		var oThis = this
		
		//set basic stuff
		var oElement = this.element
		oElement.uniqueId()
		
		//check for necessary classes
		if (!cQueueifVisible)			$.error("Queue on visible class is missing! check includes")	
		if (!bean)						$.error("bean class is missing! check includes")	
		
		//check for required options
		if (!oElement.attr(cRenderQS.APP_ID_QS))		$.error("app ID  missing!")			
		if (!oElement.attr(cRenderQS.HOME_QS))		$.error("home  missing!")			
		if (!oElement.adchart)			$.error("charts widget is missing! check includes")						
		if (!cCharts) 					$.error("cCharts is missing! check includes")						
		if (!cADMetrics) 				$.error("ADMetrics  is missing! check includes")						
		
		//- - - - -load google charts
		if (cCharts.isGoogleChartsLoaded())
			this.pr__queue_request()
		else
			cCharts.load_google_charts(function(){oThis.pr__queue_request()})
	},
	
	//*******************************************************************
	pr__queue_request: function (){
		var oThis = this
		var oElement = this.element
	
		//set behaviour for widget when it becomes visible
		var oQueue = new cQueueifVisible()
		bean.on(oQueue, "status", 	function(psStatus){oThis.onStatus(psStatus)}	)				
		bean.on(oQueue, "start", 	function(){oThis.onStart()}	)				
		bean.on(oQueue, "result", 	function(poHttp){oThis.onResponse(poHttp)}	)				
		bean.on(oQueue, "error", 	function(poHttp){oThis.onError(poHttp)}	)				
		oQueue.go(oElement, this.get_url())
	},


	//*******************************************************************
	onResponse: function(poHttp){
		var oElement = this.element

		oElement.empty()
		oElement.removeClass()
		
		var aResponse = poHttp.response
		if (aResponse.length == 0 ){
			oElement.append(cRender.messagebox("<i>no tiers found</i>"))
			return
		}
		
		this.render(poHttp.response)
	},

	
	//#################################################################
	//# functions
	//#################################################################`
	get_url: function (){
		var sUrl
		var oConsts = this.consts
		var oElement = this.element
		
		var oParams = {}
		oParams[ cRenderQS.APP_ID_QS ] = oElement.attr(cRenderQS.APP_ID_QS)
		
		
		var sBaseUrl = oElement.attr(cRenderQS.HOME_QS)+oConsts.REST_API
		sUrl = cBrowser.buildUrl(sBaseUrl, oParams)
		return sUrl
	},
	
	//*******************************************************************
	render: function(paData){
		var oElement = this.element
		
		oElement.empty()
		if (paData.length == 0){
			oElement.append(cRender.messagebox("<i>no tiers found</i>"))
			return
		}
		
		var sBaseUrl = oElement.attr(cRenderQS.HOME_QS) + "/pages/tier/tier.php"
		var oParams = {}
		oParams[ cRenderQS.APP_ID_QS ] = oElement.attr(cRenderQS.APP_ID_QS)
		sBaseUrl = cBrowser.buildUrl(sBaseUrl, oParams)	
			
		if (oElement.attr(cRenderQS.LIST_MODE_QS)){
			//---------- LIST mode ---------------------------------------
			var oDiv = $("<DIV>", {style:"column-count:3"})
			
			paData.forEach( function(poTier){
				var oParams = {}
				oParams[ cRenderQS.TIER_ID_QS ] = poTier.id
				var sUrl = cBrowser.buildUrl(sBaseUrl, oParams)	
				
				oDiv.append("<a href='" + sUrl + "'>" + poTier.name + "</a><br>")
			})
			oElement.append(oDiv)
		}else
			//---------- not LIST mode ---------------------------------------
			//put widget placeholders
			var oTable = $("<table>", {border:1, cellspacing:0, class:"maintable"})
				//- - - - - - table header
				var oRow = $("<TR>")
					oRow.append("<th style='width:200px'>Tier</th><th style='width:350px'>calls</th><th style='width:350px'>Response Time</th>")
				oTable.append(oRow)
			
				//- - - - - - table rows
				paData.forEach( function(poTier){
					var sBaseID=  oElement.attr("id")+"T"+poTier.id
					var oParams = {}
					oParams[ cRenderQS.TIER_ID_QS ] = poTier.id
					var sUrl = cBrowser.buildUrl(sBaseUrl, oParams)	
					
					var oRow = $("<TR>")
						//- - label 
						oRow.append("<td><a href='" + sUrl + "'>" + poTier.name + "</a></td>")
						//- - calls 
						var oCell = $("<TD>")
							var aOptions = {id: sBaseID + "calls", type:"adchart", width: 341, height:125}
							aOptions[cRenderQS.HOME_QS] =  oElement.attr(cRenderQS.HOME_QS) 
							aOptions[cRenderQS.APP_ID_QS] =  oElement.attr(cRenderQS.APP_ID_QS) 
							aOptions[cChartConsts.ATTR_TITLE + "0"] = "calls per minute"
							aOptions[cRenderQS.METRIC_QS + "0" ] = cADMetrics.tierCallsPerMin(poTier)
							aOptions[cChartConsts.ATTR_SHOW_ZOOM] =1
							aOptions[cChartConsts.ATTR_SHOW_COMPARE] = 1
							aOptions[cChartConsts.ATTR_PREVIOUS] = 0
							aOptions[cChartConsts.ATTR_WIDTH] = 341
							aOptions[cChartConsts.ATTR_HEIGHT] = 125
							
							var oDiv = $("<div>", aOptions).append("please wait")
						oCell.append(oDiv)
						oRow.append(oCell)
						
						//- - response 
						oCell = $("<TD>")
							aOptions ["id"] = sBaseID + "resp"
							aOptions[cChartConsts.ATTR_TITLE + "0"] = "response times"
							aOptions[cRenderQS.METRIC_QS + "0" ] = cADMetrics.tierResponseTimes(poTier)
							oDiv = $("<div>", aOptions).append("please wait")
						oCell.append(oDiv)
						oRow.append(oCell)
						// - - add the table
						oTable.append(oRow)
				})
					
			oElement.append(oTable)
			
			//convert placeholders to charts
			oElement.find("DIV[type='adchart']").each(
				function (piIndex, poElement){
					$(poElement).adchart()
				}
			)
	}
})