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
$home = "../..";
$root=realpath($home);
$phpinc = realpath("$root/../phpinc");
$jsinc = "$home/../jsinc";

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
require_once("$phpinc/appdynamics/metrics.php");
require_once("$phpinc/appdynamics/common.php");
require_once("$root/inc/inc-charts.php");
require_once("$root/inc/inc-secret.php");
require_once("$root/inc/inc-render.php");

//####################################################################
cRender::html_header("tier disks");
cChart::do_header();
cRender::force_login();

//get passed in values
$oTier = cRenderObjs::get_current_tier();
$oApp = $oTier->app;
$sAppQS = cRender::get_base_app_QS();
$sTierQS = cRender::get_base_tier_QS();

// show time options
$title = "$oApp->name&gt;$oTier->name&gt;Tier Infrastructure&gt;disks";
cRender::show_time_options($title); 
$showlink = cCommon::get_session($LINK_SESS_KEY);

//other buttons
$oCred = cRenderObjs::get_appd_credentials();
if (!$oCred->restricted_login) cRenderMenus::show_tier_functions();

//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRender::html_footer();
	exit;
}
//********************************************************************


//data for the page
	
//####################################################################
?>
<h2>Disks for all Servers in <?=cRender::show_name(cRender::NAME_TIER,$oTier)?> Tier</h2>
<p>
<?php
	$aNodes = $oTier->GET_Nodes();	
	
	foreach ($aNodes as $oNode){
		?><H3>disks for <?=cRender::show_name(cRender::NAME_OTHER,$oNode->name)?></h3><?php
		$aDisks = $oTier->GET_NodeDisks($oNode->name);
		$aMetrics = [];
		foreach ($aDisks as $oDisk){
			$sMetric = cAppDynMetric::InfrastructureNodeDiskFree($oTier->name, $oNode->name, $oDisk->name);
			$aMetrics[]= [
				cChart::LABEL=>$oDisk->name, cChart::METRIC=>$sMetric, cChart::HIDEIFNODATA=>1
			];
			$sMetric = cAppDynMetric::InfrastructureNodeDiskUsed($oTier->name, $oNode->name, $oDisk->name);
			$aMetrics[]= [
				cChart::LABEL=>$oDisk->name, cChart::METRIC=>$sMetric, cChart::HIDEIFNODATA=>1
			];
		}		
		cChart::metrics_table($oTier->app, $aMetrics, 2, cRender::getRowClass());
		cDebug::flush();
	}
?>

<?php
cChart::do_footer();
cRender::html_footer();
?>
