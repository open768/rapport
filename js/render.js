'use strict';
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//#
//###############################################################################
var cRender={
	//************************************************************
	APP_QS : "app",
	APP_ID_QS : "aid",
	
	DB_QS : "db",
	
	TIER_QS : "tier",
	FROM_TIER_QS : "from",
	TO_TIER_QS : "to",
	TIER_ID_QS : "tid",
	SERVICE_QS : "srv",
	SERVICE_ID_QS: "sid",
	
	TRANS_QS : "trans",
	TRANS_ID_QS : "trid",
	SNAP_GUID_QS : "snpg",
	SNAP_URL_QS : "snpu",
	SNAP_TIME_QS : "snpt",
	
	NODE_QS : "nd",
	FILTER_NODE_QS : "fnqs",
	NODE_ID_QS : "ndid",
	
	TITLE_QS : "tit",
	METRIC_QS : "met",
	METRIC_HEIRARCHY_QS : "meh",
	BACKEND_QS : "back",
	LICENSE_QS : "lc",
	
	USAGE_QS : "us",
	CSV_QS : "csv",
	DIV_QS : "div",
	METRIC_TYPE_QS :"mt",

	PREVIOUS_QS:"prv",
	LOGIN_TOKEN_QS:"lt",

	//************************************************************
	IGNORE_REF_QS : "igr",
	LIST_MODE_QS : "list",
	
	//************************************************************
	LOCATION_QS:"loc",
	
	//************************************************************
	DASH_ID_QS :"dai",
	DASH_NAME_QS :"dan",
	DASH_URL_TEMPLATE : "dut",
	//************************************************************
	GROUP_TYPE_QS :"gtq",
	GROUP_TYPE_NODE :"n",
	GROUP_TYPE_TIER :"t",
	GROUP_TYPE_IP :"i",

	//************************************************************
	LOG_ID_QS :"loi",
	LOG_VERSION_QS : "lov",
	LOG_CREATED_QS : "loc",
	
	//************************************************************
	HEALTH_ID_QS :"hi",
	
	HOME_QS : "home",
	LABEL_QS : "lbl",
	
	//**************************************************************************
	RUM_DETAILS_QS :"rmd",
	RUM_PAGE_QS : "rpg",
	RUM_PAGE_ID_QS : "rpid",
	RUM_TYPE_QS : "rty",
	RUM_DETAILS_ACTIVITY :"rmda",
	RUM_DETAILS_RESPONSE :"rmdr",
	SYNTH_DETAILS_QS : "syd",
		
	//**************************************************************************
	CHART_METRIC_FIELD : "cmf",
	CHART_TITLE_FIELD : "ctf",
	CHART_COUNT_FIELD : "ccf",
	CHART_APP_FIELD : "caf",
	
	//**************************************************************************
	SEARCH_QS : "srch",
	SERVER_MQ_MANAGER_QS : "mqm",
	
	//**************************************************************************
	NAME_APP : 1,
	NAME_TIER : 2,
	NAME_EXT : 3,
	NAME_TRANS : 4,
	NAME_OTHER : 99,
	
	//**************************************************************************
	TIME_START_QS : "tsta",
	TIME_END_QS : "tend",
	TIME_DURATION_QS : "tdur",


	//**************************************************************************
	 messagebox: function(psMsg){
		return "<div class='w3-panel w3-blue w3-round-large w3-padding-16 w3-leftbar'>"+psMsg+"</div>";
	 },
	 
	//**************************************************************************
	 put_in_wbrs:function(psInput, piInterval=20){
		if (psInput.indexOf(" ") > 0)
			return psInput;
		else{
			var aSplit = psInput.split(piInterval);
			var sJoined = aSplit.join("<wbr>");
			return sJoined;
		}
	},
	
	format_number: function (piNum){
		var sLocale = navigator.language;
		var oFormatter = new Intl.NumberFormat(sLocale);
		return oFormatter.format(piNum);
	}
};

var cRenderW3={
	
	//**********************************************************
	tag:function(psTag, psColour="w3-light-grey"){
		return  "<span class='w3-tag "+ psColour + " w3-round w3-border ' style='text-align:left'>"+psTag+"</span> ";
	}
};

var cRenderMDL={
	cardID:0,
	
	//**********************************************************
	title:function(psTitle){
		return "<div class='mdl-card__title'><font class='card_title'>"+psTitle+"</font></div>";
	},
	
	//**********************************************************
	card_start:function(psTitle=null){
		this.cardID++;
		var sClass = "class='mdl-card mdl-shadow--2dp rapport-card'";
		
		var sHTML = "<div "+ sClass + " id='CARDID_" + this.cardID + "'>";
		if (psTitle !== null)	sHTML += this.title(psTitle);
		return sHTML;	
	},
	
	//**************************************************************************
	action_start:function(){
		return "<div class='mdl-card__actions mdl-card--border'>";
	},

	//**************************************************************************
	body_start:function(){
		return "<div class='mdl-card__supporting-text'>";
	},

};