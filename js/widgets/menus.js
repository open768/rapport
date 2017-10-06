//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

//###############################################################################
var cMenus={
	METRIC_FIELD: "cmf.",
	TITLE_FIELD: "ctf.",
	APP_FIELD: "caf.",
	APP_QS:"app",
	APPID_QS:"aid",
	IGNORE_REF_QS: "igr",
	METRIC_TYPE_QS:"mt",
	METRIC_TYPE_ACTIVITY: "mac",
	METRIC_TYPE_RUMCALLS:"mrc",
		
	//*********************************************************
	renderMenus: function(){	
		
		$("DIV[type='appdmenus']").each( 
			function(pIndex, pElement){
				var oElement = $(pElement);
								
				oElement.appdmenu({	MenuType: oElement.attr("menu")	});
					
			}
		);
		
	}
}


//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.appdmenu",{
	//#################################################################
	//# Definition
	//#################################################################
	options:{
		MenuType: null,
		AppName: null,
		AppID: null,
		TierName: null,
		TierID: null
	},
	

	//#################################################################
	//# Constructor
	//#################################################################`
	_create: function(){
		var oThis, oOptions, oElement;
		
		//set basic stuff
		oThis = this;
		oElement = oThis.element;
		oElement.uniqueId();
		
		//check for necessary classes
		if (! oElement.selectmenu ) {		$.error("select Menu type missing! check includes");			}
		
		//check for required options
		var oOptions = this.options;
		if (!oOptions.MenuType)	{		$.error("MenuType  missing!");			}
		
		//load content
		switch(oOptions.MenuType){
			case "appfunctions": 
				this.pr__showAppFunctions();
				break;
			case "applist": 
				this.pr__showAppList();
				break;
			case "logoutmenu":
				this.pr__showLogoutMenu();
				break;
			default:
				$.error("unknown menu type!");
		}
	},	
	
	//#################################################################
	//# Privates
	//#################################################################`
	pr__showAppFunctions: function(){
		var oOptions, oElement;
		var sAppname, sAppid;
		oOptions = this.options;
		oElement = this.element;
		
		//check for required options
		sAppname = oElement.attr("appname");
		if (!sAppname) {	$.error("appname attr missing!");	}
		sAppid = oElement.attr("appid");
		if (!sAppid)	{	$.error("appid attr missing!");		}
		
		//build the params
		var oParams = {};
		oParams[cMenus.APP_QS] = sAppname;
		oParams[cMenus.APPID_QS] = sAppid;
		
		//build the menu
		var oSelect = $("<select>");
			//- - - - - - - - Application group
			var oGroup = $("<optgroup>",{label:"Application"});
				var oOption = $("<option>",{selected:1,disabled:1}).append(sAppname);
				oGroup.append(oOption);		
				
				this.pr__addToGroup(oGroup, "One Pager", cBrowser.buildUrl("appoverview.php", oParams));
				
			oSelect.append(oGroup);
		
			//- - - - - - - - Application Functions group
			oGroup = $("<optgroup>",{label:"Application Functions"});
				this.pr__addToGroup(oGroup, "Activity", cBrowser.buildUrl("tiers.php", oParams));
				this.pr__addToGroup(oGroup, "Agents", cBrowser.buildUrl("appnodes.php", oParams));
				this.pr__addToGroup(oGroup, "Availability", cBrowser.buildUrl("appavail.php", oParams));
				this.pr__addToGroup(oGroup, "Events", cBrowser.buildUrl("events.php", oParams));
				this.pr__addToGroup(oGroup, "External Calls", cBrowser.buildUrl("appext.php", oParams));
				this.pr__addToGroup(oGroup, "Information Points", cBrowser.buildUrl("appinfo.php", oParams));
				this.pr__addToGroup(oGroup, "Remote Services", cBrowser.buildUrl("backends.php", oParams));
				this.pr__addToGroup(oGroup, "Service End Points", cBrowser.buildUrl("appservice.php", oParams));
				this.pr__addToGroup(oGroup, "Transactions", cBrowser.buildUrl("apptrans.php", oParams));
				this.pr__addToGroup(oGroup, "Web Real User Monitoring", cBrowser.buildUrl("apprum.php", oParams));
			oSelect.append(oGroup);
		
		//add and make the menu a selectmenu
		var oThis = this;		
		oElement.append(oSelect);
		oSelect.selectmenu({select:	function(poEvent, poTarget){oThis.onSelectItem(poTarget.item.element)}}	);
	},

	
	//****************************************************************
	pr__showLogoutMenu: function(){
		var oOptions, oElement, oParams, oGroup;
		oOptions = this.options;
		oElement = this.element;
		
		var oSelect = $("<select>");
			var oOption = $("<option>",{selected:1,disabled:1}).append("Go...");
			oSelect.append(oOption);
			
			oParams = {};
			oParams[cMenus.IGNORE_REF_QS] = 1;
			this.pr__addToGroup( oSelect, "Logout", cBrowser.buildUrl("index.php", oParams));
			this.pr__addToGroup( oSelect, "Login Token", "authtoken.php");
			this.pr__addToGroup( oSelect, "Link to this page", "link.php");
			
			//- - - - -All group
			oGroup = $("<optgroup>",{label:"All"});
				this.pr__addToGroup( oGroup, "Agents", "allagents.php");
				
				oParams = {};
				oParams[cMenus.METRIC_TYPE_QS] = cMenus.METRIC_TYPE_ACTIVITY;
				this.pr__addToGroup(oGroup, "Application Activity", cBrowser.buildUrl("all.php", oParams));
				
				oParams = {};
				oParams[cMenus.METRIC_TYPE_QS] = cMenus.METRIC_TYPE_RUMCALLS;
				this.pr__addToGroup(oGroup, "Browser RUM Activity", cBrowser.buildUrl("all.php", oParams));
				
				this.pr__addToGroup( oGroup, "Configuration", "config.php");
				this.pr__addToGroup( oGroup, "Databases", "alldb.php");
				this.pr__addToGroup( oGroup, "License Usage", "usage.php");
				this.pr__addToGroup( oGroup, "Remote Services", "allbackends.php");
				this.pr__addToGroup( oGroup, "Tiers", "alltier.php");
				
			oSelect.append(oGroup);
			
			//- - - - -App Overview group
			oGroup = $("<optgroup>",{label:"Application Overview for ..."});
			
			var sApp, sAppid;
			var iCount = 1;
			
			while (true){
				sApp = oElement.attr("appname."+iCount);
				if (!sApp) break;
				sAppid = oElement.attr("appid."+iCount);
				
				oParams = {};
				oParams[cMenus.APP_QS] = sApp;
				oParams[cMenus.APPID_QS] = sAppid;
				this.pr__addToGroup(oGroup, sApp, cBrowser.buildUrl("tiers.php", oParams));
				iCount++;
			}
			oSelect.append(oGroup);
			
		//add and make the menu a selectmenu
		var oThis = this;		
		oElement.append(oSelect);
		oSelect.selectmenu({select:	function(poEvent, poTarget){oThis.onSelectItem(poTarget.item.element)}}	);
	},
	
	//****************************************************************
	pr__showAppList: function(){
		var oOptions, oElement;
		oOptions = this.options;
		oElement = this.element;
		
		oElement.append(oOptions.MenuType);				
	},
	
	//****************************************************************
	pr__addToGroup: function(poGroup, psLabel, psUrl){
		var oOption = $("<option>",{value:psUrl}).append(psLabel);			
		poGroup.append(oOption);			
	},
	
	//#################################################################
	//# events
	//#################################################################`
	onSelectItem: function(poTarget){
		document.location.href = poTarget.attr("value");
	}
});