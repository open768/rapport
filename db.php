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

$sDB = cHeader::get(cRender::DB_QS);


//####################################################################
cRender::html_header("Database - $sDB");
cRender::force_login();
cChart::do_header();

//####################################################################
cRender::show_time_options( "Summary Database information  - $sDB"); 
cRender::button("back to all databases", "alldb.php",false);
cRender::button("Details for $sDB", "dbdetail.php?".cRender::DB_QS."=$sDB",false);

//####################################################################
$aMetrics=[];

$sMetric = cAppDynMetric::databaseTimeSpent($sDB);
$aMetrics[] = [cChart::LABEL=>"Time spent in Database", cChart::METRIC=>$sMetric];
$sMetric = cAppDynMetric::databaseCalls($sDB);
$aMetrics[] = [cChart::LABEL=>"Database Calls", cChart::METRIC=>$sMetric];
$sMetric = cAppDynMetric::databaseConnections($sDB);
$aMetrics[] = [cChart::LABEL=>"Database Connections", cChart::METRIC=>$sMetric];
cChart::metrics_table(cAppDApp::$db_app,$aMetrics,1,cRender::getRowClass());


cChart::do_footer();

cRender::html_footer();
?>
