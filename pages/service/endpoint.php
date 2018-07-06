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
require_once("../../inc/root.php");
cRoot::set_root("../..");

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
require_once("$root/inc/inc-charts.php");
require_once("$root/inc/inc-secret.php");
require_once("$root/inc/inc-render.php");
require_once("$root/inc/inc-filter.php");


//####################################################################
cRender::html_header("Service End Point");
cRender::force_login();
cChart::do_header();

//####################################################
//display the results
$sService = cHeader::get( cRender::SERVICE_QS);
$oTier = cRenderObjs::get_current_tier();
$oApp = $oTier->app;

$sAppQS = cRenderQS::get_base_app_QS($oApp);
$sTierQS = cRenderQS::get_base_tier_QS($oTier);

cRender::show_time_options("$oApp->name&gt;$oTier->name&gt;$sService"); 
//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRender::html_footer();
	exit;
}

$oCred = cRenderObjs::get_appd_credentials();
cRenderMenus::show_tier_functions();
//cRender::appdButton(cAppDynControllerUI::transaction($oApp,$oTrans->id));
cDebug::flush();

?>
<H2>Service End Point <?=cRender::show_name(cRender::NAME_OTHER,$sService)?></h2>
<?php
	$aMetrics = [];
	$aMetrics[] = [cChart::LABEL=>"Calls", cChart::METRIC=>cAppdynMetric::endPointCallsPerMin($oTier->name, $sService)];
	$aMetrics[] = [cChart::LABEL=>"Response", cChart::METRIC=>cAppdynMetric::endPointResponseTimes($oTier->name, $sService)];
	$aMetrics[] = [cChart::LABEL=>"Errors", cChart::METRIC=>cAppdynMetric::endPointErrorsPerMin($oTier->name, $sService), cChart::HIDEIFNODATA=>true];
	cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX);

	
// ################################################################################
?><h2>Snapshots</h2><?php

// ################################################################################
?><?php

// ################################################################################
cChart::do_footer();

cRender::html_footer();
?>