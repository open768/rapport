"use strict"
/* globals bean,cQueueifVisible,cRenderQS,cBrowser,cRenderW3 */

$.widget( "ck.adhealthdetail",  $.ck.common, {
	//#################################################################
	//# Definition
	//#################################################################
	consts:{
		HEALTH_API:"/rest/healthdetail.php"
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
		if (!oElement.gSpinner) 		$.error("gSpinner is missing! check includes")		
		if (!cHR_Render) 				$.error("cHR_Render is missing! check includes")		
		
		//check for required options
		if (!oElement.attr(cRenderQS.APP_ID_QS))		$.error("appid  (aid)missing!")			
		if (!oElement.attr("hi"))		$.error("ruleID (hi) missing!")			
		if (!oElement.attr(cRenderQS.HOME_QS))		$.error("home  missing!")			
					
		
		//set behaviour for widget when it becomes visible
		var oQueue = new cQueueifVisible()
		bean.on(oQueue, "status", 	function(psStatus){oThis.onStatus(psStatus)}	)				
		bean.on(oQueue, "start", 	function(){oThis.onStart()}	)				
		bean.on(oQueue, "result", 	function(poHttp){oThis.onResponse(poHttp)}	)				
		bean.on(oQueue, "error", 	function(poHttp){oThis.onError(poHttp)}	)				
		oQueue.go(oElement, this.get_healthruledetail_url())
	},

	
	//*******************************************************************
	onResponse: function(poHttp){
		var oElement = this.element

		oElement.empty()
		oElement.removeClass()
		
		var oResponse = poHttp.response
		if (!oResponse.id){
			oElement.append("ID not found in response")
			return
		}
		
		oElement.empty()
		oElement.append("displaying rule...")
		
		this.render_detail(poHttp.response)
	},

	
	//#################################################################
	//# functions
	//#################################################################`
	get_healthruledetail_url: function (){
		var sUrl
		var oElement = this.element
		
		var oParams = {}
		oParams[ cRenderQS.APP_ID_QS ] = oElement.attr(cRenderQS.APP_ID_QS)
		oParams[ cRenderQS.HEALTH_ID_QS ] = oElement.attr(cRenderQS.HEALTH_ID_QS)
		
		
		var sBaseUrl = oElement.attr(cRenderQS.HOME_QS)+this.consts.HEALTH_API
		sUrl = cBrowser.buildUrl(sBaseUrl, oParams)
		return sUrl
	},
	
	//*******************************************************************
	render_detail: function(poResponse){
		var oElement = this.element
		
		cHR_Render.go(oElement, poResponse)
	}
})

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
var cHR_AffectedRenderer = {
	make: function(poObj){
		var aRows = []
		var oRowT

		oRowT = cHR_Render.title_row("affected Entries")
		aRows.push(oRowT)
		
		var oEntities,oScope, sScope
		var sEntityType = poObj.affectedEntityType
		var aBackends, aBTs, aInfoPoints, sTierOrNode, oAffectedTiers, sTierScope, oAffectedNodes, oAffected, aAffectedTiers, sNodeScope, aTiers, aEndPoints
	
		switch (sEntityType){
			//-----------------------------------------------------------------------------------
			case "BACKENDS" :
				oEntities = poObj.affectedBackends
				aRows.push( cHR_Render.row("Health Rule Type", "Databases and Remote Services"))
				sScope = oEntities.backendScope
				switch (sScope){
					case "SPECIFIC_BACKENDS":
						aBackends = oEntities.backends
						aRows.push( cHR_Render.row("Backends", aBackends.join("<br>")))
						break
					
					case "ALL_BACKENDS":
						aRows.push( cHR_Render.row("Backends", "All Backends"))
						break
					case "BACKENDS_MATCHING_PATTERN":
						aRows.push(  cHR_Render.row("Backends", "Backend Matching a Pattern"))
						aRows.push(  cHR_Render.row("Pattern", cHR_Render.pattern(oEntities.patternMatcher)))
						break
					default:
						aRows.push( cHR_Render.unknown_row("Backends", "Unknown Backend Scope: " + sScope))
				}
				break
			
			//-----------------------------------------------------------------------------------
			case "BUSINESS_TRANSACTION_PERFORMANCE":
				oEntities = poObj.affectedBusinessTransactions
				aRows.push(cHR_Render.row("Health Rule Type", "Business Transaction Performance (load, response time, slow calls, etc)"))
				sScope = oEntities.businessTransactionScope
				switch (sScope){
					case "SPECIFIC_BUSINESS_TRANSACTIONS":
						aBTs = oEntities.businessTransactions
						aRows.push(cHR_Render.row("Business Transactions", aBTs.join("<BR>")))
						break
					case "ALL_BUSINESS_TRANSACTIONS":
						aRows.push(cHR_Render.row("Business Transactions", "All Business Transactions"))
						break
					case "BUSINESS_TRANSACTIONS_MATCHING_PATTERN":
						aRows.push( cHR_Render.row("Business Transactions", "Business Transactions Matching a Pattern"))
						aRows.push(cHR_Render.row("Pattern", cHR_Render.pattern(oEntities.patternMatcher)))
						break
					default:
						aRows.push(cHR_Render.unknown_row("Business Transactions", "Unknown Business Transactions Scope: " + sScope))
				}
				break		
				
			//-----------------------------------------------------------------------------------
			case "CUSTOM":
				oScope = poObj.affectedEntityScope
				aRows.push(cHR_Render.row("Health Rule Type", sEntityType))
				aRows.push(cHR_Render.row("Entity type", oScope.entityScope))
				break

			//-----------------------------------------------------------------------------------
			case "ERRORS" :
				aRows.push(cHR_Render.row("Health Rule Type", sEntityType))
				oEntities = poObj.affectedErrors
				sScope = oEntities.errorScope
				switch (sScope){
					case "ALL_ERRORS" :
						aRows.push( cHR_Render.row("Scope", "All Errors"))
						break
					default:
						aRows.push(cHR_Render.unknown_row("Scope", "unknown scope: "+ sScope))
				}
				break
				
			//-----------------------------------------------------------------------------------
			case "INFORMATION_POINTS" :
				oEntities = poObj.affectedInformationPoints
				aRows.push( cHR_Render.row("Health Rule Type", "Information Points"))
				sScope = oEntities.informationPointScope
				switch (sScope){
					case "ALL_INFORMATION_POINTS":
						aRows.push( cHR_Render.row("Scope", "All Information points"))
						break
					case "SPECIFIC_INFORMATION_POINTS":
						aRows.push( cHR_Render.row("Health Rule affects?", "Specific information points"))
						aInfoPoints = oEntities.informationPoints
						aRows.push( cHR_Render.row("Information Points", aInfoPoints.join("<br>")))
						break
					default:
						aRows.push( cHR_Render.unknown_row("Health Rule affects?", "unknown information points scope: " + sScope))
				}
				break
				
			//-----------------------------------------------------------------------------------
			case "OVERALL_APPLICATION_PERFORMANCE":
				aRows.push(cHR_Render.row("Health Rule Type", "Overall Application Performance"))
				break
			//-----------------------------------------------------------------------------------
			case "TIER_NODE_HARDWARE":
				aRows.push( cHR_Render.row("Health Rule Type", "Tier / Node Health - Hardware, JVM, CLR (cpu, heap, disk I/O, etc)"))
				oEntities = poObj.affectedEntities
				sTierOrNode = oEntities.tierOrNode
				
				switch (sTierOrNode){
					case "TIER_AFFECTED_ENTITIES":
						oAffectedTiers = oEntities.affectedTiers
						sTierScope = oAffectedTiers.affectedTierScope
						switch (sTierScope){
							case "ALL_TIERS":
								aRows.push(  cHR_Render.row("Health Rule affects?", "All Tiers"))
								break
							case "SPECIFIC_TIERS":
								aRows.push(  cHR_Render.row("Health Rule affects?", "Specific Tiers"))
								aTiers = oAffectedTiers.tiers
								aRows.push(  cHR_Render.row("Tiers", aTiers.join("<br>")))
								break
							default:
								aRows.push(  cHR_Render.unknown_row("Health Rule affects?", "unknown tier scope: " + sTierScope))
						}
						break
					case "NODE_AFFECTED_ENTITIES" :
						oAffectedNodes = oEntities.affectedNodes
						sNodeScope = oAffectedNodes.affectedNodeScope
						switch (sNodeScope){
							case "ALL_NODES":
								aRows.push( cHR_Render.row("Health Rule affects?", "All Nodes"))
								break
							case "NODES_OF_SPECIFIC_TIERS":
								aTiers = oAffectedNodes.specificTiers
								aRows.push(  cHR_Render.row("Nodes of Specific Tiers", aTiers.join("<br>")))
								break
							default:
								aRows.push( cHR_Render.unknown_row("Health Rule affects?", "unknown Node scope: " + sNodeScope))
						}
						break
					default:
						aRows.push( cHR_Render.unknown_row("Health Rule affects", "Unknown Entity type :" + sTierOrNode))
				}
				break
			
			//-----------------------------------------------------------------------------------
			case "TIER_NODE_TRANSACTION_PERFORMANCE":
				aRows.push( cHR_Render.row("Health Rule Type", "Tier / Node Health - Transaction Performance (load, response time, slow calls, etc)"))
				oEntities = poObj.affectedEntities
				sTierOrNode = oEntities.tierOrNode
				
				switch (sTierOrNode){
					case "TIER_AFFECTED_ENTITIES":
						aAffectedTiers = oEntities.affectedTiers
						sTierScope = aAffectedTiers.affectedTierScope
						switch (sTierScope){
							case "ALL_TIERS":
								aRows.push( cHR_Render.row("Health Rule affects?", "All Tiers"))
								break
							case "SPECIFIC_TIERS":
								aRows.push( cHR_Render.row("Health Rule affects?", "Specific Tiers"))
								aTiers = aAffectedTiers.tiers
								aRows.push( cHR_Render.row("Tiers", aTiers.join("<br>")))
								break
							default:
								aRows.push( cHR_Render.unknown_row("Health Rule affects?", "unknown tier scope: " + sTierScope))
								}
						break
					case "NODE_AFFECTED_ENTITIES":
						oAffectedNodes = oEntities.affectedNodes
						sNodeScope = oAffectedNodes.affectedNodeScope
						switch (sNodeScope){
							case "ALL_NODES":
								aRows.push( cHR_Render.row("affected nodes", "All Nodes"))
								break
							default:
								aRows.push( cHR_Render.unknown_row("affected nodes", "unknown node scope: " + sTierOrNode))
						}
						break
					default:
						aRows.push(cHR_Render.unknown_row("Health Rule affects", "Unknown Entity type: "+ sTierOrNode))
				}
				break
			//-----------------------------------------------------------------------------------
			case "SERVICE_ENDPOINTS":
				oAffected = poObj.affectedServiceEndpoints
				sScope = oAffected.serviceEndpointScope
				aRows.push( cHR_Render.row("Health Rule Type", "Service end points"))
				
				switch(sScope){
					case "SPECIFIC_SERVICE_ENDPOINTS":
						aEndPoints = oAffected.serviceEndpoints
						aRows.push(cHR_Render.row("Service endpoints", aEndPoints.join("<br>")))
						break
					default:
						aRows.push(cHR_Render.unknown_row("scope", "unknown scope: "+sScope))
				}
				
				break
			//-----------------------------------------------------------------------------------
			default:
				aRows.push( cHR_Render.unknown_row("Health rule type", "unknown type: " + sEntityType))
		}
		return aRows
	}
}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
var cHR_CriteriaRenderer = {
	make: function(psCaption, poData){
		var aRows = []
		var oRowT
		
		oRowT = cHR_Render.title_row(psCaption + " Criteria")
		aRows.push(oRowT)
		
		if (poData == null)
			aRows.push(cHR_Render.row("", "No " + psCaption + " criteria configured"))
		else{
			aRows.push(cHR_Render.row("aggregation", "fires when "+poData.conditionAggregationType+ " the conditions are met"))
			
			var aConditions = poData.conditions
			for (var iC = 0; iC<aConditions.length; iC++){
				var oItem = aConditions[iC]
				var sFragment = ""
	
				//-----------------------------------------------------
				if (oItem.evaluateToTrueOnNoData)
					sFragment += " evaluates to "+cRenderW3.tag("true") + " if no data<br>"

				//-----------------------------------------------------
				var iMinTriggers = oItem.minimumTriggers
				if (iMinTriggers > 0)
					sFragment += "minimum Triggers  "+ cRenderW3.tag(iMinTriggers) + " <br>"

				//-----------------------------------------------------
				var oDetail = oItem.evalDetail
				var sDetailType = oDetail.evalDetailType
				var aVars,oVar, iV
				switch (sDetailType){
					case "METRIC_EXPRESSION":
						sFragment += "evaluates the Expression : " + cRenderW3.tag(oDetail.metricExpression) + "<br>"
						aVars = oDetail.metricExpressionVariables
						sFragment += "with the following variables"
						sFragment += "<ul>"
							for(iV=0; iV<aVars.length; iV++){
								oVar = aVars[iV]
								sFragment += "<li>" + oVar.variableName + " = " + cRenderW3.tag( oVar.metricPath )
							}
						sFragment += "</ul>"
						break
					case "SINGLE_METRIC":
						sFragment += "evaluates single metric: " + cRenderW3.tag( oDetail.metricPath) +"<br>"
						break
					default:
						sFragment += "unknown detail type sDetailType<br>"
				}
				
				//-----------------------------------------------------
				var oEvalCompare = oDetail.metricEvalDetail
				sFragment += "<p>condition triggers when evaluation is "
				var sEvalDetailType = oEvalCompare.metricEvalDetailType
				var sCompCondition
				switch (sEvalDetailType){
					case "SPECIFIC_TYPE":
						sCompCondition = oEvalCompare.compareCondition
						switch ( sCompCondition ){
							case "GREATER_THAN_SPECIFIC_VALUE":
								sFragment += "greater than "
								break
							case "LESS_THAN_SPECIFIC_VALUE":
								sFragment += "less than "
						}
						sFragment += cRenderW3.tag(oEvalCompare.compareValue)
						break
					case "BASELINE_TYPE":
						sFragment += oEvalCompare.baselineCondition +
							" of " + cRenderW3.tag(oEvalCompare.baselineName) +
							" by "+ cRenderW3.tag(oEvalCompare.compareValue) +
							" "+ oEvalCompare.baselineUnit
						break
					default:
						sFragment += "unknown type: sEvalDetailType"
				}				
				//-----------------------------------------------------
				aRows.push(cHR_Render.row(oItem.name, sFragment))
			}
		}
		
		return(aRows)
	}
}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
var cHR_Render = {
	go : function(poElement, poResponse){
		poElement.empty()
		
		var oTable = $("<table>",{class:"ui-widget"})
		var oRow

		oRow = this.pr_get_overview(poResponse)
		oTable.append(oRow)
		oRow = cHR_AffectedRenderer.make(poResponse.affects)
		oTable.append(oRow)
		oRow = cHR_CriteriaRenderer.make("warning", poResponse.evalCriterias.warningCriteria)
		oTable.append(oRow)
		oRow = cHR_CriteriaRenderer.make("critical",poResponse.evalCriterias.criticalCriteria)
		oTable.append(oRow)
		
		poElement.append(oTable)
	},
	
	//**********************************************************
	//**********************************************************
	unknown_row: function(psCaption, psContent){
		var oRow = $("<TR>")
		var oCell1 = $("<td>", {valign:"top", align:"right", width:200}).append("<b>"+psCaption+"</b>")
		oRow.append(oCell1)
		var oCell2 = $("<td>",{width:700,class:"ui-widget-content ui-state-error-text ui-state-error"}).append(psContent)
		oRow.append(oCell2)
		
		return oRow
	},

	//**********************************************************
	row: function(psCaption, psContent){
		var oRow = $("<TR>")
		var oCell1 = $("<td>", {valign:"top", width:200,align:"right"}).append("<b>"+psCaption+"</b>")
		oRow.append(oCell1)
		var oCell2 = $("<td>",{width:700,class:"ui-widget-content"}).append(psContent)
		oRow.append(oCell2)
		
		return oRow
	},
	
	//**********************************************************
	title_row: function(psTitle){
		var oRow = $("<TR>",{class:"ui-widget-header"})
		var oCell = $("<td>",{colspan:2})
		oCell.append(psTitle)
		oRow.append(oCell)
		
		return oRow
	},
	
	//**********************************************************
	// eslint-disable-next-line no-unused-vars
	pattern: function(poPattern){
		return "work in progress"
	},
	
	//**********************************************************
	pr_get_overview: function(poData){
		var aRows = []
		var oRowT, oRowC, sFragment
		
		oRowT = this.title_row("Overview")
		aRows.push(oRowT)
		
		oRowC = $("<tr>")
		sFragment ="<td colspan='2'><table width='100%'><tr>"
			sFragment +="<td align='right'>When Enabled</td>"
			sFragment +="<td >"+cRenderW3.tag(poData.scheduleName)+"</td>"
			sFragment +="<td align='right'>Use data from last</td>"
			sFragment +="<td >" + cRenderW3.tag(poData.useDataFromLastNMinutes + " mins")+"</td>"
			sFragment +="<td align='right'>Wait time after violation</td>"
			sFragment +="<td >"+ cRenderW3.tag(poData.waitTimeAfterViolation + " mins")+"</td>"
			sFragment +="<td align='right'>Rule Enabled</td>"
			sFragment +="<td >"+cRenderW3.tag(poData.enabled)+"</td>"
		sFragment +="</tr></table></td>"
		oRowC.append(sFragment)
		aRows.push(oRowC)
		
		return(aRows)
	}
}

