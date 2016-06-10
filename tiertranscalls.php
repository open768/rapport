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


$duration = get_duration();

set_time_limit(200); // huge time limit as this takes a long time
//####################################################################
cRender::html_header("External tier calls");
cRender::force_login();

//display the results
$app = cHeader::get(cRender::APP_QS);
$tier = cHeader::get(cRender::TIER_QS);
$gsTierQS = cRender::get_base_tier_qs();

$title =  "Graphs for transaction calls per minute for transactions for $tier in $app";
cRender::show_time_options($title); 


//####################################################################
$oResponse =cAppdyn::GET_Tier_transactions($app, $tier);

$sBaseUrl = cHttp::build_url("transdetails.php", $gsTierQS;) 
foreach ($oResponse as $oDetail){
    $trans = $oDetail->name;
	$trid = $oDetail->id;
	$sLink = cHttp::build_url($sBaseUrl, cRender::TRANS_QS, $trans);
	$sLink = cHttp::build_url($sLink, cRender::TRANS_ID_QS, $trid);
	
	cCommon::flushprint ("<h2><a href='$link'>$trans</a></h2>");   
	$oDetResponse = cAppdyn::GET_TransCalls($app, $tier, $trans, false);
	$iTotalRows = count($oDetResponse);
    $charturl = generate_chart("ttc", "call per min $trans",  $oDetResponse);
	echo "<img src='$charturl'>";
}


//---------------------------------------------------------------
echo "<p>";
cRender::button("see External Calls", cHttp::build_url("tierextcalls.php", $gsTierQS));
cRender::button("just trans stats", cHttp::build_url("tiertrans.php", $gsTierQS));
cRender::button("trans graph", cHttp::build_url("tiertransgraph.php", $gsTierQS));

cRender::html_footer();
?>