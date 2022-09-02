<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2021 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

//#######################################################################
//#######################################################################
class cRenderQS{
	
	const APP_QS = "app";
	const APP_ID_QS = "aid";
	const APPS_QS = "apps";
	
	const CONTROLLER_URL_QS = "cur";
	
	const DB_QS = "db";
	
	const TIER_QS = "tier";
	const FROM_TIER_QS = "from";
	const TO_TIER_QS = "to";
	const TIER_ID_QS = "tid";
	const SERVICE_QS = "srv";
	const SERVICE_ID_QS= "sid";
	
	const TRANS_QS = "trans";
	const TRANS_ID_QS = "trid";
	const SNAP_GUID_QS = "snpg";
	const SNAP_URL_QS = "snpu";
	const SNAP_TIME_QS = "snpt";
	
	const NODE_QS = "nd";
	const FILTER_NODE_QS = "fnqs";
	const NODE_ID_QS = "ndid";
	const NODE_IDS_QS = "ndids";
	
	const TITLE_QS = "tit";
	const METRIC_QS = "met";
	const METRIC_HEIRARCHY_QS = "meh";
	const BACKEND_QS = "back";
	const LICENSE_QS = "lc";
	
	const USAGE_QS = "us";
	const CSV_QS = "csv";
	const DIV_QS = "div";
	const METRIC_TYPE_QS ="mt";

	const PREVIOUS_QS="prv";
	const LOGIN_TOKEN_QS="lt";

	//************************************************************
	const AUDIT_TYPE_QS = "aut";
	const AUDIT_TYPE_LIST_ACTIONS = "als";
	const AUDIT_TYPE_ACTION = "ala";
	const AUDIT_FILTER = "auf";
	const AUDIT_FILTER_LOGIN = "LOGIN";
	const AUDIT_FILTER_LOGIN_FAILED = "LOGIN_FAILED";
	const AUDIT_FILTER_LOGOUT = "LOGOUT";
	const AUDIT_FILTER_OBJECT = "OBJECT_UPDATED";

	//************************************************************
	const AGENT_COUNT_TYPE_QS = "act";
	const COUNT_TYPE_APPD = "ctap";
	const COUNT_TYPE_ACTUAL = "ctac";
	
	//************************************************************
	const AUDIT_HOST_QS = "auh";
	const AUDIT_ACCOUNT_QS = "aah";
	const AUDIT_USER_QS = "auh";
	
	//************************************************************
	const CHECK_ONLY_QS = "cho";
	const CHECK_ONLY_BT = "cob";
	
	//************************************************************
	const IGNORE_REF_QS = "igr";
	const LIST_MODE_QS = "list";
	const TOTALS_QS = "totl";
	const DONT_SHOW_TOTAL_QS = "dstot";
	const DONT_CLOSE_CARD_QS = "dclcr"; 
	
	//************************************************************
	const LOCATION_QS="loc";
	
	//************************************************************
	const SOURCE_TYPE_QS ="das";
	const SOURCE_TYPE_LDAP ="stl";
	const SOURCE_TYPE_APPD ="sta";
	
	//************************************************************
	const DASH_ID_QS ="dai";
	const DASH_NAME_QS ="dan";
	const DASH_URL_TEMPLATE = "dut";
	
	//************************************************************
	const GROUP_NAME_QS ="grn";
	const GROUP_ID_QS ="gri";
	const GROUP_TYPE_QS ="gtq";
	const GROUP_TYPE_NODE ="n";
	const GROUP_TYPE_TIER ="t";
	const GROUP_TYPE_IP ="i";

	//************************************************************
	const LOG_ID_QS ="loi";
	const LOG_VERSION_QS = "lov";
	const LOG_CREATED_QS = "loc";
	
	//************************************************************
	const HELP_QS ="help";
	const HEALTH_ID_QS ="hi";
	const HOME_QS = "home";
	const LABEL_QS = "lbl";
	
	//**************************************************************************
	const RUM_DETAILS_QS ="rmd";
	const RUM_PAGE_QS = "rpg";
	const RUM_PAGE_ID_QS = "rpid";
	const RUM_TYPE_QS = "rty";
	const RUM_DETAILS_ACTIVITY ="rmda";
	const RUM_DETAILS_RESPONSE ="rmdr";
	const SYNTH_DETAILS_QS = "syd";
		
	//**************************************************************************
	const CHART_METRIC_FIELD = "cmf";
	const CHART_TITLE_FIELD = "ctf";
	const CHART_COUNT_FIELD = "ccf";
	const CHART_APP_FIELD = "caf";
	
	//**************************************************************************
	const SEARCH_QS = "srch";
	const SERVER_MQ_MANAGER_QS = "mqm";
	
	//**************************************************************************
	const NAME_APP = 1;
	const NAME_TIER = 2;
	const NAME_EXT = 3;
	const NAME_TRANS = 4;
	const NAME_OTHER = 99;
	
	//**************************************************************************
	const TIME_START_QS = "tsta";
	const TIME_END_QS = "tend";
	const TIME_DURATION_QS = "tdur";
	const LAST_YEAR_QS = "lyq";
	
	//**************************************************************************
	const TYPE_QS  = "typ";
	
	//**************************************************************************
	const WIDGET_NO_DETAIL_QS  = "nd";
	
	//**************************************************************************
	public static function get_base_app_QS( $poApp){
		//cDebug::enter();
		$appQS = cHttp::build_qs(null, cRenderQS::APP_QS, $poApp->name);
		$appQS = cHttp::build_qs($appQS, cRenderQS::APP_ID_QS, $poApp->id);
		//cDebug::leave();
		return $appQS;
	}

	//******************************************************************************************
	public static function get_base_tier_QS( $poTier){
		//cDebug::enter();
		$sAppQs = self::get_base_app_QS($poTier->app);
		$sTierQs = cHttp::build_qs($sAppQs, cRenderQS::TIER_QS, $poTier->name);
		$sTierQs = cHttp::build_qs($sTierQs, cRenderQS::TIER_ID_QS, $poTier->id);
		//cDebug::leave();
		return $sTierQs;
	}
	
	//******************************************************************************************
	public static function get_base_node_QS( $poTier, $piNode, $psNode){
		//cDebug::enter();
		$sTierQS = self::get_base_tier_QS($poTier);
		$sNodeQs = cHttp::build_qs($sTierQS, cRenderQS::NODE_QS, $psNode);
		$sNodeQs = cHttp::build_qs($sNodeQs, cRenderQS::NODE_ID_QS, $piNode);
		//cDebug::leave();
		return $sNodeQs;
	}

}
?>
