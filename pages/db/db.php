<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2021 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

//####################################################################
$home="../..";
require_once "$home/inc/common.php";
require_once "$root/inc/charts.php";


$sDB = cHeader::get(cRenderQS::DB_QS);


//####################################################################
cRenderHtml::$load_google_charts = true;
cRenderHtml::header("Database - $sDB");
cRender::force_login();
cChart::do_header();

//####################################################################

cRender::button("back to all databases", "alldb.php",false);
cRender::button("Details for $sDB", "dbdetail.php?".cRenderQS::DB_QS."=$sDB",false);


//####################################################################
$aMetrics=[];

$sMetric = cADMetricPaths::databaseTimeSpent($sDB);
$aMetrics[] = [cChart::LABEL=>"Time spent in Database", cChart::METRIC=>$sMetric];
$sMetric = cADMetricPaths::databaseCalls($sDB);
$aMetrics[] = [cChart::LABEL=>"Database Calls", cChart::METRIC=>$sMetric];
$sMetric = cADMetricPaths::databaseConnections($sDB);
$aMetrics[] = [cChart::LABEL=>"Database Connections", cChart::METRIC=>$sMetric];
cChart::metrics_table(cADDB::$db_app,$aMetrics,1,cRender::getRowClass());


cChart::do_footer();

cRenderHtml::footer();
?>
