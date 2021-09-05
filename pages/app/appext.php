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


set_time_limit(200); // huge time limit as this takes a long time
$oApp = cRenderObjs::get_current_app();
$gsAppQS = cRenderQS::get_base_app_QS($oApp);
$oApp = cRenderObjs::get_current_app();

//####################################################################
cRenderHtml::header("External Calls");
cRender::force_login();
cChart::do_header();

//####################################################################

cRenderMenus::show_apps_menu("External Calls", "appext.php");
$oCred = cRenderObjs::get_appd_credentials();
if ($oCred->restricted_login == null){ 
	//********************************************************************
	$aTiers = $oApp->GET_Tiers();

	?><select id="TierMenu">
		<option selected disabled>Show external calls for Tiers...</option>
		<?php
			foreach ($aTiers as $oTier){
				$sUrl = cHttp::build_qs($gsAppQS, cRender::TIER_QS, $oTier->name);
				$sUrl = cHttp::build_qs($sUrl, cRender::TIER_ID_QS, $oTier->id);
				?><option value="../tier/tierextgraph.php?<?="$sUrl"?>"><?=cRender::show_name(cRender::NAME_TIER,$oTier)?></option><?php
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
cDebug::flush();

//####################################################################

?><h2>Overall statistics for <?=cRender::show_name(cRender::NAME_APP,$oApp)?></h2><?php
$aMetrics = [];
$aMetrics[] = [cChart::LABEL=>"Calls per min",cChart::METRIC=>cAppDynMetric::appCallsPerMin()];
$aMetrics[] = [cChart::LABEL=>"Response Time",cChart::METRIC=>cAppDynMetric::appResponseTimes()];
cChart::metrics_table($oApp, $aMetrics,2,cRender::getRowClass());

//##################################################################
?><h2>External calls from <?=cRender::show_name(cRender::NAME_APP,$oApp)?></h2><?php
$aMetrics = [];

$oResponse = $oApp->GET_ExtTiers();
foreach ( $oResponse as $oExtTier){
	$class=cRender::getRowClass();
	$sName = $oExtTier->name;
	
	$sUrl=cHttp::build_qs($gsAppQS,cRender::BACKEND_QS,$oExtTier->name);
	$sUrl=cHttp::build_url("appexttiers.php", $sUrl);

	$aMetrics[] = [cChart::TYPE=>cChart::LABEL,cChart::LABEL=>$sName,cChart::WIDTH=>200];
	$aMetrics[] = [cChart::LABEL=>"Calls per min to ($sName)",cChart::METRIC=>cAppDynMetric::backendCallsPerMin($sName), cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"see all tiers", cChart::HIDEIFNODATA=>1];
	$aMetrics[] = [cChart::LABEL=>"Response time to ($sName)",cChart::METRIC=>cAppDynMetric::backendResponseTimes($sName), cChart::HIDEIFNODATA=>1];
	$aMetrics[] = [cChart::LABEL=>"Errors ($sName)",cChart::METRIC=>cAppDynMetric::backendErrorsPerMin($sName), cChart::HIDEIFNODATA=>1];
}
cChart::metrics_table($oApp, $aMetrics,4,cRender::getRowClass());

//##################################################################
cChart::do_footer();
cRenderHtml::footer();
?>
