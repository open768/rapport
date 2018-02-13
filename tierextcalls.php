<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2018 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

//####################################################################
//####################################################################
$root=realpath(".");
$phpinc = realpath("$root/../phpinc");
$jsinc = "../jsinc";

require_once("$phpinc/ckinc/debug.php");
require_once("$phpinc/ckinc/session.php");
require_once("$phpinc/ckinc/common.php");
require_once("$phpinc/ckinc/http.php");
require_once("$phpinc/ckinc/header.php");
	
cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();

//####################################################################
require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$phpinc/appdynamics/common.php");
require_once("inc/inc-charts.php");
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");

set_time_limit(200); // huge time limit as this takes a long time

//display the results
$app = cHeader::get(cRender::APP_QS);
$tier = cHeader::get(cRender::TIER_QS);
$aid=cHeader::get(cRender::APP_ID_QS);
$tid = cHeader::get(cRender::TIER_ID_QS);
$gsAppQs=cRender::get_base_app_QS();
$gsTierQs=cRender::get_base_tier_QS();

$SHOW_PROGRESS=true;


//####################################################################
cRender::html_header("External tier calls");
cRender::force_login();

cRender::show_time_options("External calls from $tier in $app"); 
$oCred = cRender::get_appd_credentials();
if ($oCred->restricted_login == null){
	cRenderMenus::show_app_functions();
	cRenderMenus::show_tier_functions();
	cRenderMenus::show_tier_menu("Change Tier to", "tierextcalls.php");
}

//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRender::html_footer();
	exit;
}
//********************************************************************
//********************************************************************
//####################################################################
cCommon::flushprint ("<br>");
$oTimes = cRender::get_times();
$oResponse =cAppdyn::GET_Tier_ext_details($app, $tier, $oTimes);
cRender::render_tier_ext($app, $aid, $tier, $tid, $oResponse);

cRender::html_footer();
?>
