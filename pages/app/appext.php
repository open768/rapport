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
cRenderHtml::$load_google_charts = true;
cRenderHtml::header("External Calls");
cRender::force_login();
cChart::do_header();

//####################################################################
function show_tier_menu(){
	global $oApp, $gsAppQS;
	
	$aTiers = $oApp->GET_Tiers();

	?><select id="TierMenu">
		<option selected disabled>Show external calls for Tiers...</option>
		<?php
			foreach ($aTiers as $oTier){
				$sUrl = cHttp::build_qs($gsAppQS, cRenderQS::TIER_QS, $oTier->name);
				$sUrl = cHttp::build_qs($sUrl, cRenderQS::TIER_ID_QS, $oTier->id);
				?><option value="../tier/tierextgraph.php?<?="$sUrl"?>"><?=$oTier->name?></option><?php
			}
		?>
	</select>
	<script>
	$(  
		function(){
			$("#TierMenu").selectmenu({change:common_onListChange});  
		}  
	);
	</script><?php
}
//####################################################################

$oCred = cRenderObjs::get_AD_credentials();
cDebug::flush();

//####################################################################


cRendercards::card_start("Overall statistics for $oApp->name");
	cRendercards::body_start();
		$aMetrics = [];
		$aMetrics[] = [cChart::LABEL=>"Calls per min",cChart::METRIC=>cADAppMetricPaths::appCallsPerMin()];
		$aMetrics[] = [cChart::LABEL=>"Response Time",cChart::METRIC=>cADAppMetricPaths::appResponseTimes()];
		cChart::metrics_table($oApp, $aMetrics,2,null);
	cRendercards::body_end();
	cRendercards::action_start();
		if ($oCred->restricted_login == null){ 
			//********************************************************************
			show_tier_menu();
			cRenderMenus::show_app_change_menu("External Calls");
		}
	cRendercards::action_end();
cRendercards::card_end();

//##################################################################
cRendercards::card_start("External calls from $oApp->name");
cRendercards::body_start();
	$aMetrics = [];

	$aResponse = $oApp->GET_ExtTiers();
	if (count($aResponse) == 0)
		cCommon::messagebox("no External calls found");
	else{
		foreach ( $aResponse as $oExtTier){
			$class=cRender::getRowClass();
			$sName = $oExtTier->name;
			
			$sUrl=cHttp::build_qs($gsAppQS,cRenderQS::BACKEND_QS,$oExtTier->name);
			$sUrl=cHttp::build_url("appexttiers.php", $sUrl);

			$aMetrics[] = [cChart::TYPE=>cChart::LABEL,cChart::LABEL=>$sName,cChart::WIDTH=>200];
			$aMetrics[] = [cChart::LABEL=>"Calls per min to ($sName)",cChart::METRIC=>cADMetricPaths::backendCallsPerMin($sName), cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"see all tiers", cChart::HIDEIFNODATA=>1];
			$aMetrics[] = [cChart::LABEL=>"Response time to ($sName)",cChart::METRIC=>cADMetricPaths::backendResponseTimes($sName), cChart::HIDEIFNODATA=>1];
			$aMetrics[] = [cChart::LABEL=>"Errors ($sName)",cChart::METRIC=>cADMetricPaths::backendErrorsPerMin($sName), cChart::HIDEIFNODATA=>1];
		}
		cChart::metrics_table($oApp, $aMetrics,4,cRender::getRowClass());
	}
	cRendercards::body_end();
cRendercards::card_end();

//##################################################################
cChart::do_footer();
cRenderHtml::footer();
?>
