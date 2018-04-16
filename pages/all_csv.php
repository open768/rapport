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
$home = "..";
$root=realpath($home);
$phpinc = realpath("$root/../phpinc");
$jsinc = "$home/../jsinc";

require_once("$phpinc/ckinc/debug.php");
require_once("$phpinc/ckinc/session.php");
require_once("$phpinc/ckinc/common.php");
require_once("$phpinc/ckinc/http.php");
require_once("$phpinc/ckinc/header.php");
require_once("$root/inc/inc-secret.php");
require_once("$root/inc/inc-render.php");
require_once("$root/inc/inc-charts.php");
require_once("$root/inc/inc-metrics.php");
	
cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();

//####################################################################
require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$phpinc/appdynamics/metrics.php");
require_once("$phpinc/appdynamics/account.php");

//####################################################################
$iCount = cHeader::get(cRender::CHART_COUNT_FIELD);
if ($iCount == null)
	cDebug::error("no Metric count found");

//####################################################################
cRender::force_login();

cDebug::write("there are $iCount metrics");
cDebug::write("Generating CSV - please wait");

//********************************************************************
//* build up the data into an in memory structure
$oMerged = new cMergedMetrics();
for ($i = 1; $i<=$iCount; $i++){
	$sApp = cHeader::get(cRender::CHART_APP_FIELD."_$i");
	$sMetric = cHeader::get(cRender::CHART_METRIC_FIELD."_$i");
	$sTitle  = cHeader::get(cRender::CHART_TITLE_FIELD."_$i");
	
	//get the data
	cDebug::write("app:$sApp");
	$oMetric = cMetric::get_metric($sApp, $sMetric);
	
	//add data from metric to data structure
	$oMerged->add($oMetric);
}

//********************************************************************
cHeader::set_download_filename("MergedMetrics.csv");
$oMerged->write_csv();

?>
