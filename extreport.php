<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2016 

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

//choose a default duration
//

$showlink = cCommon::get_session($LINK_SESS_KEY);


// huge time limit as this takes a long time//display the results
set_time_limit(200); 

//get passed in values
$app = cHeader::get(cRender::APP_QS);
$aid = cHeader::get(cRender::APP_ID_QS);

//####################################################################
cRender::html_header("External tier calls");
cRender::force_login();

// show time options
$baseQuery ="?".cRender::APP_QS."=$app&".cRender::APP_QS."=$aid";
cRender::show_time_options("$app&gt;External Calls"); 

cRender::button("Response time", "transreport.php?$baseQuery");
cRender::button("Server stats", "csv/serverstats.php?$baseQuery");
cRender::button("activity", "activity.php?$baseQuery");
cCommon::flushprint( "<br><i>NB - only shows business transactions - not activity on all tiers</i>");


//retrieve tiers
$oResponse =cAppdyn::GET_Tiers($app);

// work through each tier
foreach ( $oResponse as $oItem){
	$tier = $oItem->name;
	$tid= $oItem->id;
	
	echo "<hr>";
	
	$oResponse =cAppdyn::GET_Tier_ext_details($app, $tier);	
	echo "<br>";
	cRender::render_tier_ext($app,  $aid, $tier, $tid, $oResponse);
}
echo "</table>";
cRender::html_footer();
?>
