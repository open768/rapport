'use strict';
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
class cMenuItem{
	static TYPE_SEPARATOR = "s"
	static TYPE_ITEM = "i"
	type=null
	label=null
	url=null

	constructor (psType, psLabel, psUrl = null){
		this.type = psType
		this.label = psLabel
		this.url = psUrl
	}
}

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
		//count how many columns needed
		var iCols = 0
		var iBreakAtRow = null
		if (piFixedCols !== null){
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
			var oDropDownContent = $("<div>", { class: "w3-dropdown-content w3-border", style: "z-index:200" })
				var oInnerDropDownDiv = $("<div>", { style: "column-count:" + iCols + ";column-gap:0"})
				var iRow = 0
				paItems.forEach(poItem => {
					var oSpan, oLink
					if (poItem.type === cMenuItem.TYPE_SEPARATOR){
						oSpan = $("<div>", { class: "w3-block w3-light-grey w3-border w3-padding-4",style:"break-before:column;column-width:100px" })
						oSpan.append(poItem.label)
						oInnerDropDownDiv.append(oSpan)
					} else {
						var oDivParams = null
						if (iBreakAtRow && iRow >= iBreakAtRow){
							oDivParams = {"style": "break-before:column"}
							iRow = 0;
						}
						var oItemDiv = $( "<DIV>", oDivParams )
							oLink = $("<a>", { href: poItem.url, class:"no-decoration"} )
								oSpan = $("<div>", { class: "w3-block w3-button w3-padding-4" })
									oSpan.append(poItem.label)
								oLink.append(oSpan)
							oItemDiv.append(oLink)
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

		poElement.append(oMenuDiv);
	}
}

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
		var oThis, oOptions, oElement;

		//set basic stuff
		oThis = this;
		oElement = oThis.element;
		oElement.empty();
		oElement.uniqueId();
		oElement.css({ position: "relative" });

		var sElementName = oElement.get(0).tagName;
		//if (sElementName !== "SELECT")	$.error("element must be a select");		
		//select menu doesnt automatically go to the top - dont forget to modify the CSS

		//check for necessary classes
		if (!oElement.selectmenu) $.error("select Menu type missing! check includes");
		if (!oElement.attr("menu")) $.error("select menu misssing menu attribute");

		//get attributes
		var oOptions = this.options;
		oOptions.MenuType = oElement.attr("menu");
		oOptions.home = oElement.attr("home");

		//load content
		switch (oOptions.MenuType) {
			case "appfunctions":
				this.pr__showAppFunctions();
				break;
			case "appchange":
				this.pr__showAppsChangeMenu();
				break;
			case "appagents":
				this.pr__showAppAgentsMenu();
				break;
			case "tierchangemenu":
				this.pr__showChangeTierMenu();
				break;
			case "tiernodesmenu":
				this.pr__showTierNodesMenu();
				break;
			case "tierfunctions":
				this.pr__showTierFunctions();
				break;
			default:
				$.error("unknown menu type: " + oOptions.MenuType);
		}
	},

	//#################################################################
	//# show menus
	//#################################################################`
	pr__showAppFunctions: function () {
		var oOptions, oElement;
		var sAppname, sAppid;
		oOptions = this.options;
		oElement = this.element;
		var oThis = this;
		oElement.empty();

		var sElementName = oElement.get(0).tagName;
		if (sElementName !== "DIV") $.error("element must be a DIV");

		//check for required options
		sAppname = oElement.attr("appname");
		if (!sAppname) { $.error("appname attr missing!"); }
		sAppid = oElement.attr("appid");
		if (!sAppid) { $.error("appid attr missing!"); }

		//build the params
		var oParams = {};
		oParams[cRenderQS.APP_QS] = sAppname;
		oParams[cRenderQS.APP_ID_QS] = sAppid;

		//build the prefixes
		var sAgentPrefixUrl = oOptions.home + "/pages/agents";
		var sAppPrefixUrl = oOptions.home + "/pages/app";
		var sCheckPrefixUrl = oOptions.home + "/pages/check";
		var sRumPrefixUrl = oOptions.home + "/pages/rum";
		var sSrvPrefixUrl = oOptions.home + "/pages/service";
		var sTransPrefixUrl = oOptions.home + "/pages/trans";

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
		cDropDownMenu.render(oElement, sAppname, aMenuItems)
	},



	//****************************************************************
	pr__showAppAgentsMenu: function () {
		var oElement = this.element;
		var oOptions = this.options;

		//check for required options
		var sAppname = oElement.attr("appname");
		if (!sAppname) { $.error("appname attr missing!"); }
		var sAppid = oElement.attr("appid");
		if (!sAppid) { $.error("appid attr missing!"); }

		//build the params
		var sAppPrefixUrl = oOptions.home + "/pages/app";
		var sAgentPrefixUrl = oOptions.home + "/pages/agent";
		cJquery.setTopZindex(oElement);

		//build the select menu
		var oSelect = oElement;
		var oOption = $("<option>", { selected: 1, disabled: 1 }).append("Show Agent...");
		oSelect.append(oOption);

		var oParams = {};
		oParams[cRenderQS.APP_QS] = sAppname;
		oParams[cRenderQS.APP_ID_QS] = sAppid;
		this.pr__addToGroup(oSelect, "Agent Information", cBrowser.buildUrl(sAgentPrefixUrl + "/appagents.php", oParams));

		oParams[cRenderQS.METRIC_TYPE_QS] = cMenus.METRIC_TYPE_INFR_AVAIL;
		this.pr__addToGroup(oSelect, "Agent Availability", cBrowser.buildUrl(sAgentPrefixUrl + "/appagentdetail.php", oParams));

		//add and make the menu a selectmenu
		var oThis = this;
		oSelect.selectmenu({ select: function (poEvent, poTarget) { oThis.onSelectItem(poTarget.item.element) } });
	},


	//****************************************************************
	pr__showTierNodesMenu: function () {
		var oOptions, oElement;
		oOptions = this.options;
		oElement = this.element;
		var sThisBaseUrl = this.pr__get_base_tier_QS(oElement.attr("url"));

		cJquery.setTopZindex(oElement);

		//
		var oSelect = oElement
		var oOption = $("<option>", { selected: 1, disabled: 1 }).append(oElement.attr("caption"));
		oSelect.append(oOption);

		var iCount = 1;
		while (true) {
			var sNode = oElement.attr("node." + iCount);
			if (!sNode) break;

			var oParams = {};
			oParams[cRenderQS.NODE_QS] = sNode;
			this.pr__addToGroup(oSelect, sNode, cBrowser.buildUrl(sThisBaseUrl, oParams));
			iCount++;
		}

		//add and make the menu a selectmenu
		var oThis = this;
		oSelect.selectmenu({ select: function (poEvent, poTarget) { oThis.onSelectItem(poTarget.item.element) } });
	},

	//****************************************************************
	pr__showAppsChangeMenu: function () {

		var oElement;
		var sApp, sAppid, oParams;

		//check for a DIV
		oElement = this.element;
		var sElementName = oElement.get(0).tagName;
		if (sElementName !== "DIV") $.error("element must be a DIV");

		//build things needed
		var sUrl = oElement.attr("url") + oElement.attr("extra");
		var sCaption = oElement.attr("caption")

		//render menu
		var iCount = 1;
		var aMenuItems = []
		while (true) {
			sApp = oElement.attr("appname." + iCount);
			if (!sApp) break;
			sAppid = oElement.attr("appid." + iCount);

			oParams = {};
			oParams[cRenderQS.APP_QS] = sApp;
			oParams[cRenderQS.APP_ID_QS] = sAppid;
			var sItemUrl = cBrowser.buildUrl(sUrl, oParams)

			aMenuItems.push( new cMenuItem(cMenuItem.TYPE_ITEM, sApp, sItemUrl))
			iCount++;
		}

		//add menu
		cDropDownMenu.render(oElement, sCaption, aMenuItems,5);

	},

	//****************************************************************
	pr__showChangeTierMenu: function () {
		var oElement;
		oOptions = this.options;
		oElement = this.element;

		var sThisTierID = cBrowser.data[cRenderQS.TIER_ID_QS];
		var sUrl = oElement.attr("url") + oElement.attr("extra");
		var sBaseUrl = this.pr__get_base_app_QS(sUrl);
		var sCaption = oElement.attr("caption");

		//build the select
		var aMenuItems = []

		var iCount = 1;
		while (true) {
			var sTier = oElement.attr("tname." + iCount);
			if (!sTier) break;

			var sTid = oElement.attr("tid." + iCount);
			var oParams = {};
			oParams[cRenderQS.TIER_QS] = sTier;
			oParams[cRenderQS.TIER_ID_QS] = sTid;
			var sUrl = cBrowser.buildUrl(sBaseUrl, oParams);
			aMenuItems.push( new cMenuItem(cMenuItem.TYPE_ITEM, sTier,sUrl))

			iCount++;
		}
		cDropDownMenu.render(oElement, sCaption, aMenuItems,5);
	},

	//****************************************************************
	pr__showTierFunctions: function () {
		var oOptions, oElement;
		oOptions = this.options;
		oElement = this.element;

		var sApp = cBrowser.data[cRenderQS.APP_QS];
		var sThisTier = cBrowser.data[cRenderQS.TIER_QS];
		var sTier = oElement.attr("tier");
		if (!sTier) sTier = sThisTier;

		var sTierPrefixUrl = oOptions.home + "/pages/tier";
		var sAppPrefixUrl = oOptions.home + "/pages/app";
		var sSrvPrefixUrl = oOptions.home + "/pages/service";
		var sTransPrefixUrl = oOptions.home + "/pages/trans";

		cJquery.setTopZindex(oElement);

		var oSelect = oElement;
		//--------------------------------------------------------------------
		var oOption = $("<option>", { selected: 1, disabled: 1 }).append(sTier);
		oSelect.append(oOption);

		var oGroup = $("<optgroup>", { label: "Show..." });
		this.pr__addToGroup(oGroup, "Overview", this.pr__get_base_tier_QS(sTierPrefixUrl + "/tier.php"));
		this.pr__addToGroup(oGroup, "Backends", this.pr__get_base_tier_QS(sTierPrefixUrl + "/tierbackends.php"));
		this.pr__addToGroup(oGroup, "Errors", this.pr__get_base_tier_QS(sTierPrefixUrl + "/tiererrors.php"));
		this.pr__addToGroup(oGroup, "External Calls (graph)", this.pr__get_base_tier_QS(sTierPrefixUrl + "/tierextgraph.php"));
		this.pr__addToGroup(oGroup, "External Calls (table)", this.pr__get_base_tier_QS(sTierPrefixUrl + "/tierextcalls.php"));
		this.pr__addToGroup(oGroup, "Infrastructure", this.pr__get_base_tier_QS(sTierPrefixUrl + "/tierinfrstats.php"));
		this.pr__addToGroup(oGroup, "Service End Points", this.pr__get_base_tier_QS(sSrvPrefixUrl + "/services.php"));
		this.pr__addToGroup(oGroup, "Transactions", this.pr__get_base_tier_QS(sTransPrefixUrl + "/apptrans.php"));
		oSelect.append(oGroup);

		//--------------------------------------------------------------------
		oGroup = $("<optgroup>", { label: "Misc..." });
		if (sThisTier)
			this.pr__addToGroup(oGroup, "Back to (" + sApp + ")", this.pr__get_base_app_QS(sAppPrefixUrl + "/tiers.php"));
		var sUrl = this.pr__get_base_tier_QS(sTierPrefixUrl + "/comparestats.php");
		this.pr__addToGroup(oGroup, "Compare", sUrl);
		oSelect.append(oGroup);

		//add and make the menu a selectmenu
		var oThis = this;
		oSelect.selectmenu({ select: function (poEvent, poTarget) { oThis.onSelectItem(poTarget.item.element) } });
	},

	//#################################################################
	//# privates 
	//#################################################################`
	pr__addToGroup: function (poGroup, psLabel, psUrl) {
		var oOption = $("<option>", { value: psUrl }).append(psLabel);
		poGroup.append(oOption);
		return oOption;
	},

	//****************************************************************
	pr__get_base_tier_QS: function (psBaseUrl) {
		var oElement = this.element;
		var oParams = {};

		oParams[cRenderQS.APP_QS] = cBrowser.data[cRenderQS.APP_QS];
		oParams[cRenderQS.APP_ID_QS] = cBrowser.data[cRenderQS.APP_ID_QS];

		var sTier, sTid, sNode;
		sTier = oElement.attr("tier");
		sTid = oElement.attr("tid");
		sNode = oElement.attr("node");
		if (!sNode) sNode = cBrowser.data[cRenderQS.NODE_QS];

		oParams[cRenderQS.TIER_ID_QS] = (sTid ? sTid : cBrowser.data[cRenderQS.TIER_ID_QS]);
		oParams[cRenderQS.TIER_QS] = (sTier ? sTier : cBrowser.data[cRenderQS.TIER_QS]);
		if (sNode) oParams[cRenderQS.NODE_QS] = sNode;

		return cBrowser.buildUrl(psBaseUrl, oParams);
	},

	//****************************************************************
	pr__get_base_app_QS: function (psBaseUrl) {
		var oParams = {};
		oParams[cRenderQS.APP_QS] = cBrowser.data[cRenderQS.APP_QS];
		oParams[cRenderQS.APP_ID_QS] = cBrowser.data[cRenderQS.APP_ID_QS];

		return cBrowser.buildUrl(psBaseUrl, oParams);
	},

	//#################################################################
	//# events
	//#################################################################`
	//todo open a window for some urls
	onSelectItem: function (poTarget) {
		var sUrl = poTarget.attr("value");
		if (sUrl) {
			window.stop();
			document.location.href = sUrl;
		}
	}
});