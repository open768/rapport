'use strict'
/* globals cRenderQS,cBrowser */
//###############################################################################
//#
//###############################################################################
class cMenuItem{
	static TYPE_SEPARATOR = "s"
	static TYPE_ITEM = "i"
	type=null
	label=null
	url=null
	disabled=false

	constructor (psType, psLabel, psUrl = null){
		this.type = psType
		this.label = psLabel
		this.url = psUrl
	}
}

// eslint-disable-next-line no-unused-vars
class cDropDownMenu{
	//****************************************************************
	/**
	 * Description
	 * @param {Element} poElement
	 * @param {string} psCaption
	 * @param {Array} paItems
	 * @param {number} piFixedCols=null
	 */
	static render(poElement, psCaption, paItems, piFixedCols = null) {
		poElement.addClass("ck-menu")
		
		//count how many columns needed
		var iCols = 0
		var iBreakAtRow = null
		if (piFixedCols !== null && (paItems.length > piFixedCols) ){
			iCols = piFixedCols
			iBreakAtRow = Math.trunc( paItems.length / iCols)
		}else
			paItems.forEach(poItem => {
				if (poItem.type === cMenuItem.TYPE_SEPARATOR)
					iCols ++
			})

		//render the menu as a dropdown
		var oMenuDiv = $("<div>", { class: "w3-tag w3-light-blue w3-round" })
			//----- dropdown button
			var oDropDown = $("<div>", { class: "w3-dropdown-hover w3-light-blue" })
				//------- dropdown target
				var oButton = $("<button>", { class: "w3-button w3-circle" })
					oButton.append("<font color='white'><i class='material-icons'>more_vert</i></font>")
				oDropDown.append(oButton)
			//------- dropdown content (unfortunately it doesnt bring itself to top zorder when displaying ) 
			var oDropDownContent = $("<div>", { class: "w3-dropdown-content w3-border ck-z-index-200"})
				var oInnerDropDownDiv = $("<div>", { style: "column-count:" + iCols,class:"ck-no-column-gap"})
				var iRow = 0
				paItems.forEach(poItem => {
					var oSpan, oLink
					if (poItem.type === cMenuItem.TYPE_SEPARATOR){
						//- - - - - - separator 
						oSpan = $("<div>", { class: "w3-block w3-light-grey w3-border ck-padding-4 ck-menu-separator" })
						oSpan.append(poItem.label)
						oInnerDropDownDiv.append(oSpan)
					} else {
						//- - - - - - not a separator 
						var oDivParams = null
						if (iBreakAtRow && iRow >= iBreakAtRow){
							oDivParams = {class: "ck-menu-separator"}
							iRow = 0
						}
						var oItemDiv = $( "<DIV>", oDivParams )
							if (poItem.disabled){
								oSpan = $("<div>", { class: "w3-block w3-text-grey ck-padding-4" })
									oSpan.append(poItem.label)
								oItemDiv.append(oSpan)
							}else{
								oLink = $("<a>", { href: poItem.url, class:"no-decoration"} )
									oSpan = $("<div>", { class: "w3-block w3-button ck-padding-4" })
										oSpan.append(poItem.label)
									oLink.append(oSpan)
								oItemDiv.append(oLink)
							}
						oInnerDropDownDiv.append(oItemDiv)
					}
					iRow ++
				})
				oDropDownContent.append(oInnerDropDownDiv)
		oDropDown.append(oDropDownContent)
		oMenuDiv.append(oDropDown)

		//----- stuff after the dropdown button
		oMenuDiv.append("&nbsp;")
		oMenuDiv.append(psCaption)

		poElement.append(oMenuDiv)
	}
}
//###############################################################################
//#
//###############################################################################
var cMenus={
	METRIC_FIELD: "cmf.",
	TITLE_FIELD: "ctf.",
	APP_FIELD: "caf.",
	METRIC_TYPE_ACTIVITY: "mac",
	METRIC_TYPE_RUMCALLS:"mrc",
	METRIC_TYPE_INFR_AVAIL:"mtia",
		
	//*********************************************************
	renderMenus: function(){	
		
		$("[type='admenus']").each( 
			function(pIndex, pElement){
				var oElement = $(pElement)
				oElement.admenu() //see widgets
			}
		)
	},
}
		
//####################################################################################
//#
//####################################################################################
// eslint-disable-next-line no-unused-vars
var cMenusCode={
	appfunctions: function(poApp, psHome, psDivID){
		var sHTML = 
			"<div " + 
				" type='admenus' menu='appfunctions' "+
				cRenderQS.HOME_QS+"='"+psHome+"' " +
				cRenderQS.APP_QS+"='" + poApp.name +"' " +
				cRenderQS.APP_ID_QS+"='"+poApp.id+"' id='"+ psDivID + "'"+
				">"+
					poApp.name+" .. please wait" + 
			"</div>"
		return sHTML
	},
	
	tierfunctions: function(poTier, psHome){
		var sHTML = 
			"<DIV " + 
				"type='admenus' menu='tierfunctions' " +
				cRenderQS.HOME_QS+"='" + psHome + "' " +
				cRenderQS.TIER_QS+"='" + poTier.name + "' " +
				cRenderQS.TIER_ID_QS+"='" + poTier.id + "'> " +
					poTier.name + " - please wait" +
			"</DIV>"	
		return sHTML
	}
}

//####################################################################################
//#
//####################################################################################
// eslint-disable-next-line no-unused-vars
var cTopMenu={
	//*********************************************************
	pr__add_expansion: function(poDiv, psTitle, pbIsOpen){
		var oDetail = $("<details>",{class:"mdl-expansion"})
			if (pbIsOpen) oDetail.attr("open",1)

			var oSummary = $("<summary>", {class:"mdl-expansion__summary"})
				var oHeader = $("<span>", {class:"mdl-expansion__header"}).append(psTitle)
				oSummary.append(oHeader)
			oDetail.append(oSummary)
			
			var oContent = $("<div>", {class:"mdl-expansion__content"})
			oDetail.append(oContent)
		
		poDiv.append(oDetail)
		return oContent
	},
		
	//*********************************************************
	pr__add_to_expansion: function(poDiv, psCaption, psUrl){
		var sJS
		if (psUrl.startsWith("script:")){
			var sCmd = psUrl.slice(7)
			sJS = "window.stop();"+sCmd+";"
		}else{
			sJS = "window.stop();document.location='"+psUrl+"';"
		}
		var oLink = $("<span>", {class:"mdl-navigation__link",onclick:sJS}).append(psCaption)
		poDiv.append(oLink)
	},
	
	//*********************************************************
	render: function(poDiv){
		var bRestricted = poDiv.attr("restricted")
		if (bRestricted)
			this.render_restricted(poDiv)
		else
			this.render_full(poDiv)
	},
	
	//*********************************************************
	render_restricted: function (poDiv){
		var sController = poDiv.attr("controller")
		if (!sController) {	$.error("controller attr missing!")	}
		var sHome = poDiv.attr("home")
		if (!sHome) {	$.error("home attr missing!")	}
		
		//add the sections
		var oContentDiv
			
		oContentDiv = this.pr__add_expansion(poDiv, "General", true)
			var oParams = {}
			oParams[cRenderQS.IGNORE_REF_QS] = 1
			this.pr__add_to_expansion(oContentDiv, "Logout", cBrowser.buildUrl(sHome +"/index.php", oParams))
			this.pr__add_to_expansion(oContentDiv, "Appdynamics", "https://"+sController + "/controller/")
		
	},
	
	//*********************************************************
	render_full: function (poDiv){
		//check for required options
		var sController = poDiv.attr("controller")
		if (!sController) {	$.error("controller attr missing!")	}
		var sHome = poDiv.attr("home")
		if (!sHome) {	$.error("home attr missing!")	}
		
		var sAllPrefixUrl = sHome+"/pages/all"
		var sCheckPrefixUrl = sHome+"/pages/check"
		var sUtilPrefixUrl = sHome+"/pages/util"
		var sSrvPrefixUrl = sHome+"/pages/server"
		var sAgentPrefix = sHome+"/pages/agents"
		var sLicensePrefix = sHome+"/pages/license"
		var sAnalyticsPrefixUrl = sHome+"/pages/analytics"
		
		
		//add the sections
		var oContentDiv
		oContentDiv = this.pr__add_expansion(poDiv, "Agents AND licenses")
			this.pr__add_to_expansion(oContentDiv, "Agent Downloads", sAgentPrefix+"/appdversions.php")
			this.pr__add_to_expansion(oContentDiv, "Agent Licenses", sLicensePrefix+"/agentlicense.php")
			this.pr__add_to_expansion(oContentDiv, "Agent Versions", sAgentPrefix + "/allagentversions.php")
			this.pr__add_to_expansion(oContentDiv, "Count Agents by application", sAgentPrefix + "/countappagents.php")
			this.pr__add_to_expansion(oContentDiv, "License Usage Summary", sLicensePrefix+"/licenseusage.php")
			

		oContentDiv = this.pr__add_expansion(poDiv, "All")
			oParams = {}
			oParams[cRenderQS.METRIC_TYPE_QS] = cMenus.METRIC_TYPE_ACTIVITY
			this.pr__add_to_expansion(oContentDiv, "Analytics", sAnalyticsPrefixUrl+"/analytics.php")
			this.pr__add_to_expansion(oContentDiv, "Application Activity", cBrowser.buildUrl(sAllPrefixUrl+"/all.php", oParams))
			this.pr__add_to_expansion(oContentDiv, "Browser RUM Activity", cBrowser.buildUrl(sAllPrefixUrl+"/all.php", oParams))
			this.pr__add_to_expansion(oContentDiv, "Databases", sHome +"/pages/db/alldb.php")
			this.pr__add_to_expansion(oContentDiv, "Health", sAllPrefixUrl+"/health.php")
			this.pr__add_to_expansion(oContentDiv, "Remote Services", sAllPrefixUrl+"/allbackends.php")
			this.pr__add_to_expansion(oContentDiv, "Synthetics", sAllPrefixUrl+"/allsynth.php")
			this.pr__add_to_expansion(oContentDiv, "Tiers", sAllPrefixUrl+"/alltier.php")
			
		oContentDiv = this.pr__add_expansion(poDiv, "Check")
			this.pr__add_to_expansion(oContentDiv, "Audit Logs", sHome +"/pages/audit/audit.php")
			this.pr__add_to_expansion(oContentDiv, "Configuration", sUtilPrefixUrl+"/config.php")
			this.pr__add_to_expansion(oContentDiv, "One Click Checkup", sCheckPrefixUrl+"/checkup.php")
			
		oContentDiv = this.pr__add_expansion(poDiv, "Dashboards")
			this.pr__add_to_expansion(oContentDiv, "Check", sHome +"/pages/dash/check.php")
			this.pr__add_to_expansion(oContentDiv, "Search", sHome +"/pages/dash/search.php")
			
		oContentDiv = this.pr__add_expansion(poDiv, "General", true)
			var oParams = {}
			oParams[cRenderQS.IGNORE_REF_QS] = 1
			this.pr__add_to_expansion(oContentDiv, "Logout", cBrowser.buildUrl(sHome +"/index.php", oParams))
			this.pr__add_to_expansion(oContentDiv, "Login Token", sHome +"/pages/authtoken.php")
			this.pr__add_to_expansion(oContentDiv, "API tester", sHome +"/pages/api/api.php")
			this.pr__add_to_expansion(oContentDiv, "Appdynamics", "https://"+sController + "/controller/")
			this.pr__add_to_expansion(oContentDiv, "Link to this page", sHome +"/pages/link.php")
			this.pr__add_to_expansion(oContentDiv, "Remove links on page", "script:cRender.hide_menus_and_links()")
			this.pr__add_to_expansion(oContentDiv, "Widgets", sHome +"/widgets")
		
		oContentDiv = this.pr__add_expansion(poDiv, "Servers")
			this.pr__add_to_expansion(oContentDiv, "Server Visibility", sSrvPrefixUrl+"/servers.php")
			this.pr__add_to_expansion(oContentDiv, "MQ Dashboard", sSrvPrefixUrl+"/mq.php")

		oContentDiv = this.pr__add_expansion(poDiv, "Users and Groups")
			this.pr__add_to_expansion(oContentDiv, "Groups", sAllPrefixUrl+"/allgroups.php")
			this.pr__add_to_expansion(oContentDiv, "Users", sAllPrefixUrl+"/allusers.php")
	}
}
