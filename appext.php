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

set_time_limit(200); // huge time limit as this takes a long time
$SHOW_PROGRESS=true;
$app = cHeader::get(cRender::APP_QS);
$gsAppQS = cRender::get_base_app_QS();
$oApp = cRender::get_current_app();

//####################################################################
cRender::html_header("External Calls");
cRender::force_login();
?>
	<script type="text/javascript" src="js/remote.js"></script>
	
<?php
cChart::do_header();

//####################################################################

cRender::show_time_options("Apps>$app>External Calls"); 
cRenderMenus::show_apps_menu("External Calls", "appext.php");
$oCred = cRender::get_appd_credentials();
if ($oCred->restricted_login == null){ 
	//********************************************************************
	if (cAppdyn::is_demo()){
		cRender::errorbox("function not support ed for Demo");
		exit;
	}
	//********************************************************************
	$aTiers = cAppdyn::GET_Tiers($app);

	?><select id="TierMenu">
		<option selected disabled>Show external calls for Tiers...</option>
		<?php
			foreach ($aTiers as $oTier){
				$sUrl = cHttp::build_qs($gsAppQS, cRender::TIER_QS, $oTier->name);
				$sUrl = cHttp::build_qs($sUrl, cRender::TIER_ID_QS, $oTier->id);
				?><option value="tierextgraph.php?<?="$sUrl"?>"><?=$oTier->name?></option><?php
			}
		?>
	</select>
	<script language="javascript">
	$(  
		function(){
			$("#TierMenu").selectmenu({change:common_onListChange});  
		}  
	);
	</script><?php

}

//####################################################################
$oResponse =cAppdyn::GET_AppExtTiers($app);

?><h2>Overall statistics for <?=$app?></h2><?php
$aMetrics = [];
$aMetrics[] = [cChart::LABEL=>"Calls per min",cChart::METRIC=>cAppDynMetric::appCallsPerMin()];
$aMetrics[] = [cChart::LABEL=>"Response Time",cChart::METRIC=>cAppDynMetric::appResponseTimes()];
cChart::metrics_table($oApp, $aMetrics,2,cRender::getRowClass());

//##################################################################
?><h2>External calls from <?=$app?></h2><?php
$aMetrics = [];

foreach ( $oResponse as $oExtTier){
	$class=cRender::getRowClass();
	$sName = $oExtTier->name;
	$aMetrics[] = [cChart::TYPE=>cChart::LABEL,cChart::LABEL=>$sName];
	$aMetrics[] = [cChart::LABEL=>"Calls per min to ($sName)",cChart::METRIC=>cAppDynMetric::backendCallsPerMin($sName)];
	$aMetrics[] = [cChart::LABEL=>"Response time to ($sName)",cChart::METRIC=>cAppDynMetric::backendResponseTimes($sName)];
}
cChart::metrics_table($oApp, $aMetrics,3,cRender::getRowClass(),null,cRender::CHART_WIDTH_LETTERBOX/3);

//##################################################################
cChart::do_footer();
cRender::html_footer();
?>
