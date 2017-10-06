//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

//###############################################################################
var cMenus={
	METRIC_FIELD: "cmf.",
	TITLE_FIELD: "ctf.",
	APP_FIELD: "caf.",
		
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
	
	consts:{
		APP_QS:"app",
		APPID_QS:"aid"
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
		oParams[this.consts.APP_QS] = sAppname;
		oParams[this.consts.APPID_QS] = sAppid;
		
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
	pr__addToGroup: function(poGroup, psLabel, psUrl){
		var oOption = $("<option>",{value:psUrl}).append(psLabel);			
		poGroup.append(oOption);			
	},
	
	//****************************************************************
	pr__showAppList: function(){
		var oOptions, oElement;
		oOptions = this.options;
		oElement = this.element;
		
		oElement.append(oOptions.MenuType);				
	},
	
	//#################################################################
	//# events
	//#################################################################`
	onSelectItem: function(poTarget){
		document.location.href = poTarget.attr("value");
	}
});