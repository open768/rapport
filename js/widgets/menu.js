'use strict'
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
/*global cMenuItem, cBrowser, cRenderQS, cMenus, cDropDownMenu, cJquery */

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget("ck.admenu", {
	//#################################################################
	//# Definition
	//#################################################################
	options: {
		MenuType: null,
		AppName: null,
		AppID: null,
		TierName: null,
		TierID: null,
		home: "."
	},


	//#################################################################
	//# Constructor
	//#################################################################`
	_create: function () {
		var oThis, oOptions, oElement

		//set basic stuff
		oThis = this
		oElement = oThis.element
		oElement.empty()
		oElement.uniqueId()
		oElement.css({ position: "relative" })

		//check for necessary classes
		if (!oElement.selectmenu) $.error("select Menu type missing! check includes")
		if (!cDropDownMenu) $.error("cDropDownMenu misssing check includes")
		if (!oElement.attr("menu")) $.error("select menu misssing menu attribute")

		//get attributes
		oOptions = this.options
		oOptions.MenuType = oElement.attr("menu")
		oOptions.home = oElement.attr("home")

		//load content
		switch (oOptions.MenuType) {
			case "appfunctions":
				this.pr__showAppFunctions()
				break
			case "appchange":
				this.pr__showAppsChangeMenu()
				break
			case "appagents":
				this.pr__showAppAgentsMenu()
				break
			case "tierchangemenu":
				this.pr__showChangeTierMenu()
				break
			case "tiernodesmenu":
				this.pr__showTierNodesMenu()
				break
			case "tierfunctions":
				this.pr__showTierFunctions()
				break
			case "tierinframenu":
				this.pr__showTierInfraMenu()
				break
			default:
				$.error("unknown menu type: " + oOptions.MenuType)
		}
	},

	//#################################################################
	//# show menus
	//#################################################################`
	pr__showAppFunctions: function () {
		var oOptions, oElement
		var sAppname, sAppid
		oOptions = this.options
		oElement = this.element
		oElement.empty()

		var sElementName = oElement.get(0).tagName
		if (sElementName !== "DIV") $.error("element must be a DIV")

		//check for required options
		sAppname = oElement.attr(cRenderQS.APP_QS)
		if (!sAppname) { $.error("appname attr missing!") }
		sAppid = oElement.attr(cRenderQS.APP_ID_QS)
		if (!sAppid) { $.error("appid attr missing!") }

		//build the params
		var oParams = {}
		oParams[cRenderQS.APP_QS] = sAppname
		oParams[cRenderQS.APP_ID_QS] = sAppid

		//build the prefixes
		var sAgentPrefixUrl = oOptions.home + "/pages/agents"
		var sAppPrefixUrl = oOptions.home + "/pages/app"
		var sCheckPrefixUrl = oOptions.home + "/pages/check"
		var sRumPrefixUrl = oOptions.home + "/pages/rum"
		var sSrvPrefixUrl = oOptions.home + "/pages/service"
		var sTransPrefixUrl = oOptions.home + "/pages/trans"

		//build the menu
		var aMenuItems = [
			new cMenuItem(cMenuItem.TYPE_SEPARATOR, "Application"),
			new cMenuItem(cMenuItem.TYPE_ITEM,"Agents",cBrowser.buildUrl(sAgentPrefixUrl + "/appagents.php", oParams) ),
			new cMenuItem(cMenuItem.TYPE_ITEM,"Checkup",cBrowser.buildUrl(sCheckPrefixUrl + "/checkup.php", oParams) ),
			new cMenuItem(cMenuItem.TYPE_ITEM,"Data collectors",cBrowser.buildUrl(sAppPrefixUrl + "/datacollectors.php", oParams) ),
			new cMenuItem(cMenuItem.TYPE_ITEM,"Flow Map",cBrowser.buildUrl(sAppPrefixUrl + "/appflowmap.php", oParams) ),
			new cMenuItem(cMenuItem.TYPE_ITEM,"One Pager",cBrowser.buildUrl(sAppPrefixUrl + "/appoverview.php", oParams) ),

			new cMenuItem(cMenuItem.TYPE_SEPARATOR,"Show..." ),
			new cMenuItem(cMenuItem.TYPE_ITEM,"Activity (tiers)",cBrowser.buildUrl(sAppPrefixUrl + "/tiers.php", oParams) ),
			new cMenuItem(cMenuItem.TYPE_ITEM,"Availability",cBrowser.buildUrl(sAppPrefixUrl + "/appavail.php", oParams) ),
			new cMenuItem(cMenuItem.TYPE_ITEM,"Errors",cBrowser.buildUrl(sAppPrefixUrl + "/apperrors.php", oParams) ),
			new cMenuItem(cMenuItem.TYPE_ITEM,"External Calls",cBrowser.buildUrl(sAppPrefixUrl + "/appext.php", oParams) ),
			new cMenuItem(cMenuItem.TYPE_ITEM,"Health Rules",cBrowser.buildUrl(sAppPrefixUrl + "/healthrules.php", oParams) ),
			new cMenuItem(cMenuItem.TYPE_ITEM,"Infrastructure",cBrowser.buildUrl(sAppPrefixUrl + "/appinfra.php", oParams) ),
			new cMenuItem(cMenuItem.TYPE_ITEM,"Information Points",cBrowser.buildUrl(sAppPrefixUrl + "/appinfo.php", oParams) ),
			new cMenuItem(cMenuItem.TYPE_ITEM,"Service End Points",cBrowser.buildUrl(sSrvPrefixUrl + "/services.php", oParams) ),
			new cMenuItem(cMenuItem.TYPE_ITEM,"Transactions",cBrowser.buildUrl(sTransPrefixUrl + "/apptrans.php", oParams) ),

			new cMenuItem(cMenuItem.TYPE_SEPARATOR,"Synthetics" ),
			new cMenuItem(cMenuItem.TYPE_ITEM,"Overview",cBrowser.buildUrl(sRumPrefixUrl + "/synthetic.php", oParams) ),

			new cMenuItem(cMenuItem.TYPE_SEPARATOR,"Web RUM" ),
			new cMenuItem(cMenuItem.TYPE_ITEM,"Overall stats",cBrowser.buildUrl(sRumPrefixUrl + "/apprum.php", oParams) ),
			new cMenuItem(cMenuItem.TYPE_ITEM,"Page requests",cBrowser.buildUrl(sRumPrefixUrl + "/rumstats.php", oParams) ),
			new cMenuItem(cMenuItem.TYPE_ITEM,"Errors",cBrowser.buildUrl(sRumPrefixUrl + "/rumerrors.php", oParams) )
		]

		//render the menu
		cDropDownMenu.render(oElement, "App: "+ sAppname, aMenuItems)
	},



	//****************************************************************
	pr__showAppAgentsMenu: function () {
		var oElement = this.element
		var oOptions = this.options

		//check for required options
		var sAppname = oElement.attr(cRenderQS.APP_QS)
		if (!sAppname) { $.error("appname attr missing!") }
		var sAppid = oElement.attr(cRenderQS.APP_ID_QS)
		if (!sAppid) { $.error("appid attr missing!") }

		//build the params
		var sAgentPrefixUrl = oOptions.home + "/pages/agents"
		var oParams = {}
		oParams[cRenderQS.APP_QS] = sAppname
		oParams[cRenderQS.APP_ID_QS] = sAppid

		//build the select menu
		var aMenuItems = [
			new cMenuItem(cMenuItem.TYPE_ITEM, "Agent Information", cBrowser.buildUrl(sAgentPrefixUrl + "/appagents.php", oParams))
		]
		oParams[cRenderQS.METRIC_TYPE_QS] = cMenus.METRIC_TYPE_INFR_AVAIL
		aMenuItems.push(
			new cMenuItem(cMenuItem.TYPE_ITEM, "Agent Availability", cBrowser.buildUrl(sAgentPrefixUrl + "/appagentdetail.php", oParams))
		)
		cDropDownMenu.render(oElement, "Show Agent...", aMenuItems)

	},


	//****************************************************************
	pr__showTierNodesMenu: function () {
		var oElement
		oElement = this.element
		var sThisBaseUrl = this.pr__get_base_tier_QS(oElement.attr("url"))

		cJquery.setTopZindex(oElement)

		//
		var oSelect = oElement
		var oOption = $("<option>", { selected: 1, disabled: 1 }).append(oElement.attr("caption"))
		oSelect.append(oOption)

		var iCount = 1
		for (;;) {
			var sNode = oElement.attr("node." + iCount)
			if (!sNode) break

			var oParams = {}
			oParams[cRenderQS.NODE_QS] = sNode
			this.pr__addToGroup(oSelect, sNode, cBrowser.buildUrl(sThisBaseUrl, oParams))
			iCount++
		}

		//add and make the menu a selectmenu
		var oThis = this
		oSelect.selectmenu({ select: function (poEvent, poTarget) { oThis.onSelectItem(poTarget.item.element) } })
	},

	//****************************************************************
	pr__showAppsChangeMenu: function () {

		var oElement
		var sItemAppId, oParams

		//check for a DIV
		oElement = this.element
		var sElementName = oElement.get(0).tagName
		if (sElementName !== "DIV") $.error("element must be a DIV")

		//build things needed
		var sUrl = oElement.attr("url") + oElement.attr("extra")
		var sCaption = oElement.attr("caption")
		var sThisID = cBrowser.data[cRenderQS.APP_ID_QS]

		//render menu
		var iCount = 1
		var aMenuItems = []
		var sItemApp
		for (;;) {
			sItemApp = oElement.attr("appname." + iCount)
			if (!sItemApp) break
			sItemAppId = oElement.attr("appid." + iCount)

			oParams = {}
			oParams[cRenderQS.APP_QS] = sItemApp
			oParams[cRenderQS.APP_ID_QS] = sItemAppId
			var sItemUrl = cBrowser.buildUrl(sUrl, oParams)

			var oItem = new cMenuItem(cMenuItem.TYPE_ITEM, sItemApp, sItemUrl)
			if (sItemAppId == sThisID) oItem.disabled = true

			aMenuItems.push( oItem)
			iCount++
		}

		//add menu
		cDropDownMenu.render(oElement, "Change App: "+sCaption, aMenuItems,5)

	},

	//****************************************************************
	pr__showChangeTierMenu: function () {
		var oElement = this.element

		var sThisTierID = cBrowser.data[cRenderQS.TIER_ID_QS]
		var sUrl = oElement.attr("url") + oElement.attr("extra")
		var sBaseUrl = this.pr__get_base_app_QS(sUrl)
		var sCaption = oElement.attr("caption")

		//build the select
		var aMenuItems = []

		var iCount = 1
		for (;;) {
			var sTier = oElement.attr("tname." + iCount)
			if (!sTier) break

			var sTid = oElement.attr("tid." + iCount)
			var oParams = {}
			oParams[cRenderQS.TIER_QS] = sTier
			oParams[cRenderQS.TIER_ID_QS] = sTid
			sUrl = cBrowser.buildUrl(sBaseUrl, oParams)

			var oMenuItem = new cMenuItem(cMenuItem.TYPE_ITEM, sTier,sUrl)
			if (sThisTierID == sTid) oMenuItem.disabled = true
			aMenuItems.push( oMenuItem )

			iCount++
		}
		cDropDownMenu.render(oElement, "Change Tier: "+ sCaption, aMenuItems,5)
	},

	//****************************************************************
	pr__showTierFunctions: function () {
		var oOptions, oElement
		oOptions = this.options
		oElement = this.element

		var sApp = cBrowser.data[cRenderQS.APP_QS]
		var sThisTier = cBrowser.data[cRenderQS.TIER_QS]
		var sTier = oElement.attr("tier")
		if (!sTier) sTier = sThisTier

		var sTierPrefixUrl = oOptions.home + "/pages/tier"
		var sAppPrefixUrl = oOptions.home + "/pages/app"
		var sSrvPrefixUrl = oOptions.home + "/pages/service"
		var sTransPrefixUrl = oOptions.home + "/pages/trans"

		//--------------------------------------------------------------------
		var aMenuItems = [
			new cMenuItem(cMenuItem.TYPE_SEPARATOR, "Show..."),
			new cMenuItem(cMenuItem.TYPE_ITEM,"Overview", this.pr__get_base_tier_QS(sTierPrefixUrl + "/tier.php")),
			new cMenuItem(cMenuItem.TYPE_ITEM,"Backends", this.pr__get_base_tier_QS(sTierPrefixUrl + "/tierbackends.php")),
			new cMenuItem(cMenuItem.TYPE_ITEM,"Errors", this.pr__get_base_tier_QS(sTierPrefixUrl + "/tiererrors.php")),
			new cMenuItem(cMenuItem.TYPE_ITEM,"External Calls (graph)", this.pr__get_base_tier_QS(sTierPrefixUrl + "/tierextgraph.php")),
			new cMenuItem(cMenuItem.TYPE_ITEM,"External Calls (table)", this.pr__get_base_tier_QS(sTierPrefixUrl + "/tierextcalls.php")),
			new cMenuItem(cMenuItem.TYPE_ITEM,"Infrastructure", this.pr__get_base_tier_QS(sTierPrefixUrl + "/tierinfrstats.php")),
			new cMenuItem(cMenuItem.TYPE_ITEM,"Service End Points", this.pr__get_base_tier_QS(sSrvPrefixUrl + "/services.php")),
			new cMenuItem(cMenuItem.TYPE_ITEM,"Transactions", this.pr__get_base_tier_QS(sTransPrefixUrl + "/apptrans.php")),
		]

		aMenuItems.push(new cMenuItem(cMenuItem.TYPE_SEPARATOR, "Misc..."))
		if (sThisTier)
			aMenuItems.push( new cMenuItem(cMenuItem.TYPE_ITEM, "Back to (" + sApp + ")", this.pr__get_base_app_QS(sAppPrefixUrl + "/tiers.php")))
		aMenuItems.push( new cMenuItem(cMenuItem.TYPE_ITEM, "Compare", this.pr__get_base_tier_QS(sTierPrefixUrl + "/comparestats.php")))

		cDropDownMenu.render(oElement, "Tier: "+sTier, aMenuItems)
	},

	//****************************************************************
	pr__showTierInfraMenu: function(){
		var oElement
		oElement = this.element
		var sApp = oElement.attr(cRenderQS.APP_QS)

		//------------------------------------------------------
		var oParams = {}
		oParams[cRenderQS.APP_QS] = sApp
		oParams[cRenderQS.APP_ID_QS] = oElement.attr(cRenderQS.APP_ID_QS)
		var sAppPrefixUrl = oElement.attr("home") + "/pages/app"
		var sTierPrefixUrl = oElement.attr("home") + "/pages/tier"
		var sAppInfraUrl = cBrowser.buildUrl(sAppPrefixUrl + "/appinfra.php", oParams)

		//------------------------------------------------------
		var aMenuItems = [
			new cMenuItem(cMenuItem.TYPE_SEPARATOR, "App Infra"),
			new cMenuItem(cMenuItem.TYPE_ITEM, sApp, sAppInfraUrl),
			new cMenuItem(cMenuItem.TYPE_SEPARATOR, "Nodes"),
		]

		//------------------------------------------------------
		var sTier = oElement.attr(cRenderQS.TIER_QS)
		oParams[cRenderQS.TIER_QS] = sTier
		oParams[cRenderQS.TIER_ID_QS] = oElement.attr(cRenderQS.TIER_ID_QS)
		var sThisNode = oElement.attr(cRenderQS.NODE_QS)
		var iCount = 1
		for (;;){
			var sNode = oElement.attr(cRenderQS.NODE_QS + "." + iCount)
			if (!sNode) break
			var oNodeProps = Object.assign({}, oParams )
			oNodeProps[ cRenderQS.NODE_QS] = sNode
			var sTierInfraUrl = cBrowser.buildUrl(sTierPrefixUrl + "/tierinfrstats.php", oNodeProps)
			var oMenuItem = new cMenuItem(cMenuItem.TYPE_ITEM, sNode, sTierInfraUrl)
			if (sNode === sThisNode) oMenuItem.disabled = true
			aMenuItems.push(oMenuItem)
			iCount++
		}

		cDropDownMenu.render(oElement, "Tier infrastructure: "+sTier, aMenuItems)
	},

	//#################################################################
	//# privates 
	//#################################################################`
	pr__addToGroup: function (poGroup, psLabel, psUrl) {
		var oOption = $("<option>", { value: psUrl }).append(psLabel)
		poGroup.append(oOption)
		return oOption
	},

	//****************************************************************
	pr__get_base_tier_QS: function (psBaseUrl) {
		var oElement = this.element
		var oParams = {}

		oParams[cRenderQS.APP_QS] = cBrowser.data[cRenderQS.APP_QS]
		oParams[cRenderQS.APP_ID_QS] = cBrowser.data[cRenderQS.APP_ID_QS]

		var sTier, sTid, sNode
		sTier = oElement.attr("tier")
		sTid = oElement.attr("tid")
		sNode = oElement.attr("node")
		if (!sNode) sNode = cBrowser.data[cRenderQS.NODE_QS]

		oParams[cRenderQS.TIER_ID_QS] = (sTid ? sTid : cBrowser.data[cRenderQS.TIER_ID_QS])
		oParams[cRenderQS.TIER_QS] = (sTier ? sTier : cBrowser.data[cRenderQS.TIER_QS])
		if (sNode) oParams[cRenderQS.NODE_QS] = sNode

		return cBrowser.buildUrl(psBaseUrl, oParams)
	},

	//****************************************************************
	pr__get_base_app_QS: function (psBaseUrl) {
		var oParams = {}
		oParams[cRenderQS.APP_QS] = cBrowser.data[cRenderQS.APP_QS]
		oParams[cRenderQS.APP_ID_QS] = cBrowser.data[cRenderQS.APP_ID_QS]

		return cBrowser.buildUrl(psBaseUrl, oParams)
	},

	//#################################################################
	//# events
	//#################################################################`
	//todo open a window for some urls
	onSelectItem: function (poTarget) {
		var sUrl = poTarget.attr("value")
		if (sUrl) {
			window.stop()
			document.location.href = sUrl
		}
	}
})