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
		var sJS = "window.stop();document.location='"+psUrl+"';";
		var oLink = $("<span>", {class:"mdl-navigation__link",onclick:sJS}).append(psCaption);
		poDiv.append(oLink);
	},
	
	render: function(poDiv){
		//check for required options
		var sController = poDiv.attr("controller");
		if (!sController) {	$.error("controller attr missing!");	}
		var sHome = poDiv.attr("home");
		if (!sHome) {	$.error("home attr missing!");	}
		
		var sAppPrefixUrl = sHome+"/pages/app";
		var sAllPrefixUrl = sHome+"/pages/all";
		var sUtilPrefixUrl = sHome+"/pages/util";
		var sRumPrefixUrl = sHome+"/pages/rum";
		var sSrvPrefixUrl = sHome+"/pages/server";
		var sAnalyticsPrefixUrl = sHome+"/pages/analytics";
		
		
		//add the sections
		var oContentDiv;
		oContentDiv = this.pr__add_expansion(poDiv, "General", true);
			var oParams = {};
			oParams[cRender.IGNORE_REF_QS] = 1;
			this.pr__add_to_expansion(oContentDiv, "Logout", cBrowser.buildUrl(sHome +"/index.php", oParams));
			this.pr__add_to_expansion(oContentDiv, "Login Token", sHome +"/pages/authtoken.php");
			this.pr__add_to_expansion(oContentDiv, "Link to this page", sHome +"/pages/link.php");
			this.pr__add_to_expansion(oContentDiv, "Appdynamics", "https://"+sController + "/controller/");
			this.pr__add_to_expansion(oContentDiv, "API tester", sHome +"/pages/api/api.php");
		
		oContentDiv = this.pr__add_expansion(poDiv, "Check");
			this.pr__add_to_expansion(oContentDiv, "Configuration", sUtilPrefixUrl+"/config.php");
			this.pr__add_to_expansion(oContentDiv, "License Usage", sUtilPrefixUrl+"/licenseusage.php");
			this.pr__add_to_expansion(oContentDiv, "Agent Licenses", sUtilPrefixUrl+"/agentlicense.php");
			this.pr__add_to_expansion(oContentDiv, "One Click Checkup", sUtilPrefixUrl+"/checkup.php");
			
		oContentDiv = this.pr__add_expansion(poDiv, "Dashboards");
			this.pr__add_to_expansion(oContentDiv, "Check", sHome +"/pages/dash/check.php");
			this.pr__add_to_expansion(oContentDiv, "Search", sHome +"/pages/dash/search.php");
			
		oContentDiv = this.pr__add_expansion(poDiv, "Agents");
			this.pr__add_to_expansion(oContentDiv, "Installed", sAllPrefixUrl+"/allagentversions.php");
			this.pr__add_to_expansion(oContentDiv, "Downloads", sUtilPrefixUrl+"/appdversions.php");

		oContentDiv = this.pr__add_expansion(poDiv, "Servers");
			this.pr__add_to_expansion(oContentDiv, "Server Visibility", sSrvPrefixUrl+"/servers.php");
			this.pr__add_to_expansion(oContentDiv, "MQ Dashboard", sSrvPrefixUrl+"/mq.php");

		oContentDiv = this.pr__add_expansion(poDiv, "All");
			oParams = {};
			oParams[cRender.METRIC_TYPE_QS] = cMenus.METRIC_TYPE_ACTIVITY;
			this.pr__add_to_expansion(oContentDiv, "Analytics", sAnalyticsPrefixUrl+"/analytics.php");
			this.pr__add_to_expansion(oContentDiv, "Application Activity", cBrowser.buildUrl(sAllPrefixUrl+"/all.php", oParams));
			this.pr__add_to_expansion(oContentDiv, "Browser RUM Activity", cBrowser.buildUrl(sAllPrefixUrl+"/all.php", oParams));
			this.pr__add_to_expansion(oContentDiv, "Databases", sHome +"/pages/db/alldb.php");
			this.pr__add_to_expansion(oContentDiv, "Health", sAllPrefixUrl+"/health.php");
			this.pr__add_to_expansion(oContentDiv, "Remote Services", sAllPrefixUrl+"/allbackends.php");
			this.pr__add_to_expansion(oContentDiv, "Synthetics", sAllPrefixUrl+"/allsynth.php");
			this.pr__add_to_expansion(oContentDiv, "Tiers", sAllPrefixUrl+"/alltier.php");
			
		oContentDiv = this.pr__add_expansion(poDiv, "Overviews");
			var sApp, sAppid;
			var iCount = 1;
			while (true){
				sApp = poDiv.attr("appname."+iCount);
				if (!sApp) break;
				sAppid = poDiv.attr("appid."+iCount);
				
				oParams = {};
				oParams[cRender.APP_QS] = sApp;
				oParams[cRender.APP_ID_QS] = sAppid;
				this.pr__add_to_expansion(oContentDiv, sApp, cBrowser.buildUrl(sAppPrefixUrl+"/tiers.php", oParams));
				iCount++;
			}
	}
}
