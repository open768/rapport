'use strict';
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
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
				var oElement = $(pElement);
				oElement.admenu(); //see widgets
			}
		);
	},
}
		
//####################################################################################
//#
//####################################################################################
var cMenusCode={
	appfunctions: function(poApp, psHome, psDivID){
		var sHTML = 
			"<div " + 
				" type='admenus' menu='appfunctions' home='"+psHome+"'" +
				" appname='" + poApp.name +"' appid='"+poApp.id+"' id='"+ psDivID + "'"+
				" style='position: relative;'>"+
					poApp.name+" .. please wait" + 
			"</div>";
		return sHTML;
	},
	
	tierfunctions: function(poTier, psHome){
		var sHTML = 
			"<SELECT " + 
				"type='admenus' menu='tierfunctions' " +
				"home='" + psHome + "' " +
				"tier='" + poTier.name + "' " +
				"tid='" + poTier.id + "'> " +
					"<option selected>" + poTier.name + " - please wait" +
			"</SELECT>";	
		return sHTML;
	}
}

//####################################################################################
//#
//####################################################################################
var cTopMenu={
	//*********************************************************
	pr__add_expansion: function(poDiv, psTitle, pbIsOpen){
		var oDetail = $("<details>",{class:"mdl-expansion"});
			if (pbIsOpen) oDetail.attr("open",1);

			var oSummary = $("<summary>", {class:"mdl-expansion__summary"});
				var oHeader = $("<span>", {class:"mdl-expansion__header"}).append(psTitle);
				oSummary.append(oHeader);
			oDetail.append(oSummary);
			
			var oContent = $("<div>", {class:"mdl-expansion__content"});
			oDetail.append(oContent);
		
		poDiv.append(oDetail);
		return oContent;
	},
		
	//*********************************************************
	pr__add_to_expansion: function(poDiv, psCaption, psUrl){
		var sJS;
		if (psUrl.startsWith("script:")){
			var sCmd = psUrl.slice(7);
			sJS = "window.stop();"+sCmd+";";
		}else{
			sJS = "window.stop();document.location='"+psUrl+"';";
		}
		var oLink = $("<span>", {class:"mdl-navigation__link",onclick:sJS}).append(psCaption);
		poDiv.append(oLink);
	},
	
	//*********************************************************
	render: function(poDiv){
		var bRestricted = poDiv.attr("restricted");
		if (bRestricted)
			this.render_restricted(poDiv);
		else
			this.render_full(poDiv);
	},
	
	//*********************************************************
	render_restricted: function (poDiv){
		var sController = poDiv.attr("controller");
		if (!sController) {	$.error("controller attr missing!");	}
		var sHome = poDiv.attr("home");
		if (!sHome) {	$.error("home attr missing!");	}
		
		//add the sections
		var oContentDiv;
			
		oContentDiv = this.pr__add_expansion(poDiv, "General", true);
			var oParams = {};
			oParams[cRenderQS.IGNORE_REF_QS] = 1;
			this.pr__add_to_expansion(oContentDiv, "Logout", cBrowser.buildUrl(sHome +"/index.php", oParams));
			this.pr__add_to_expansion(oContentDiv, "Appdynamics", "https://"+sController + "/controller/");
		
	},
	
	//*********************************************************
	render_full: function (poDiv){
		//check for required options
		var sController = poDiv.attr("controller");
		if (!sController) {	$.error("controller attr missing!");	}
		var sHome = poDiv.attr("home");
		if (!sHome) {	$.error("home attr missing!");	}
		
		var sAppPrefixUrl = sHome+"/pages/app";
		var sAllPrefixUrl = sHome+"/pages/all";
		var sCheckPrefixUrl = sHome+"/pages/check";
		var sUtilPrefixUrl = sHome+"/pages/util";
		var sRumPrefixUrl = sHome+"/pages/rum";
		var sSrvPrefixUrl = sHome+"/pages/server";
		var sAgentPrefix = sHome+"/pages/agents"
		var sLicensePrefix = sHome+"/pages/license"
		var sAnalyticsPrefixUrl = sHome+"/pages/analytics";
		
		
		//add the sections
		var oContentDiv;
		oContentDiv = this.pr__add_expansion(poDiv, "Agents AND licenses");
			this.pr__add_to_expansion(oContentDiv, "Agent Downloads", sAgentPrefix+"/appdversions.php");
			this.pr__add_to_expansion(oContentDiv, "Agent Licenses", sLicensePrefix+"/agentlicense.php");
			this.pr__add_to_expansion(oContentDiv, "Agent Versions", sAgentPrefix + "/allagentversions.php");
			this.pr__add_to_expansion(oContentDiv, "Count Agents by application", sAgentPrefix + "/countappagents.php");
			this.pr__add_to_expansion(oContentDiv, "License Usage Summary", sLicensePrefix+"/licenseusage.php");
			

		oContentDiv = this.pr__add_expansion(poDiv, "All");
			oParams = {};
			oParams[cRenderQS.METRIC_TYPE_QS] = cMenus.METRIC_TYPE_ACTIVITY;
			this.pr__add_to_expansion(oContentDiv, "Analytics", sAnalyticsPrefixUrl+"/analytics.php");
			this.pr__add_to_expansion(oContentDiv, "Application Activity", cBrowser.buildUrl(sAllPrefixUrl+"/all.php", oParams));
			this.pr__add_to_expansion(oContentDiv, "Browser RUM Activity", cBrowser.buildUrl(sAllPrefixUrl+"/all.php", oParams));
			this.pr__add_to_expansion(oContentDiv, "Databases", sHome +"/pages/db/alldb.php");
			this.pr__add_to_expansion(oContentDiv, "Health", sAllPrefixUrl+"/health.php");
			this.pr__add_to_expansion(oContentDiv, "Remote Services", sAllPrefixUrl+"/allbackends.php");
			this.pr__add_to_expansion(oContentDiv, "Synthetics", sAllPrefixUrl+"/allsynth.php");
			this.pr__add_to_expansion(oContentDiv, "Tiers", sAllPrefixUrl+"/alltier.php");
			
		oContentDiv = this.pr__add_expansion(poDiv, "Check");
			this.pr__add_to_expansion(oContentDiv, "Audit Logs", sHome +"/pages/audit/audit.php");
			this.pr__add_to_expansion(oContentDiv, "Configuration", sUtilPrefixUrl+"/config.php");
			this.pr__add_to_expansion(oContentDiv, "One Click Checkup", sCheckPrefixUrl+"/checkup.php");
			
		oContentDiv = this.pr__add_expansion(poDiv, "Dashboards");
			this.pr__add_to_expansion(oContentDiv, "Check", sHome +"/pages/dash/check.php");
			this.pr__add_to_expansion(oContentDiv, "Search", sHome +"/pages/dash/search.php");
			
		oContentDiv = this.pr__add_expansion(poDiv, "General", true);
			var oParams = {};
			oParams[cRenderQS.IGNORE_REF_QS] = 1;
			this.pr__add_to_expansion(oContentDiv, "Logout", cBrowser.buildUrl(sHome +"/index.php", oParams));
			this.pr__add_to_expansion(oContentDiv, "Login Token", sHome +"/pages/authtoken.php");
			this.pr__add_to_expansion(oContentDiv, "API tester", sHome +"/pages/api/api.php");
			this.pr__add_to_expansion(oContentDiv, "Appdynamics", "https://"+sController + "/controller/");
			this.pr__add_to_expansion(oContentDiv, "Link to this page", sHome +"/pages/link.php");
			this.pr__add_to_expansion(oContentDiv, "Remove links on page", "script:cRender.hide_menus_and_links()");
			this.pr__add_to_expansion(oContentDiv, "Widgets", sHome +"/widgets");
		
		oContentDiv = this.pr__add_expansion(poDiv, "Servers");
			this.pr__add_to_expansion(oContentDiv, "Server Visibility", sSrvPrefixUrl+"/servers.php");
			this.pr__add_to_expansion(oContentDiv, "MQ Dashboard", sSrvPrefixUrl+"/mq.php");

		oContentDiv = this.pr__add_expansion(poDiv, "Users and Groups");
			this.pr__add_to_expansion(oContentDiv, "Groups", sAllPrefixUrl+"/allgroups.php");
			this.pr__add_to_expansion(oContentDiv, "Users", sAllPrefixUrl+"/allusers.php");
	}
}
