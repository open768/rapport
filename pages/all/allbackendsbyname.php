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


//-----------------------------------------------

//####################################################################
cRenderHtml::$load_google_charts = true;
cRenderHtml::header("All Remote Services");
cRender::force_login();
cChart::do_header();

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
# TODO make into a widget
$aBackends = cADController::GET_all_Backends();

//####################################################################
cRenderCards::card_start("All Remote Services");
	cRenderCards::body_start();
		$sLastCh = "";
		cCommon::div_with_cols(cRenderHTML::DIV_COLUMNS);
			echo "<ul>";
				$iBackID = 0;
				foreach ($aBackends as $sBackend=>$aApps){
					$sCh = strtoupper($sBackend[0]);
					if ($sCh !== $sLastCh){
						echo "<h3>$sCh</h3>";
						$sLastCh = $sCh;
					}
					$iBackID++;
					?><li><a href="#<?=$iBackID?>"><?=$sBackend?></a><?php
				}
			echo "</ul></div>";
	cRenderCards::body_end();
cRenderCards::card_end();

############################################################################################
$iBackID = 0;
foreach ($aBackends as $sBackend=>$aApps){
	cRenderCards::card_start("<a name='$iBackID'>$sBackend</a>");
		cRenderCards::body_start();
			$iBackID++;
			$sClass=cRender::getRowClass();
			
			foreach ($aApps as $oApp){
				echo "<h4>$oApp->name</h4>";
				
				$aMetrics = [];
				$sMetricUrl = cADMetricPaths::backendCallsPerMin($sBackend);
				$aMetrics[] = [cChart::LABEL=>"Calls Per minute: ($sBackend) in ($oApp->name) App", cChart::METRIC=>$sMetricUrl];
				$sMetricUrl = cADMetricPaths::backendResponseTimes($sBackend);
				$aMetrics[] = [cChart::LABEL=>"Response Times: ($sBackend) in ($oApp->name) App", cChart::METRIC=>$sMetricUrl];
				cChart::metrics_table($oApp, $aMetrics, 2, cRender::getRowClass());
				
				echo "<hr>";
			}
		cRenderCards::body_end();
		cRenderCards::action_start();
			$sGoUrl = cHttp::build_url("backcalls.php", cRenderQS::APP_QS, $oApp->name);
			$sGoUrl = cHttp::build_url($sGoUrl, cRenderQS::APP_ID_QS, $oApp->id);
			$sGoUrl = cHttp::build_url($sGoUrl, cRenderQS::BACKEND_QS, $sBackend);
			cRender::button("Go", $sGoUrl);
		cRenderCards::action_end();
	cRenderCards::card_end();
}	

cChart::do_footer();
cRenderHtml::footer();
?>
