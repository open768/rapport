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
cRenderHtml::header("All Remote Services");
cRender::force_login();
?>
	<script type="text/javascript" src="<?=$js_extra?>/bean/bean.js"></script>
<?php
cChart::do_header();

$title ="All Remote Services";

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************


//####################################################################
$oApps = cADController::GET_all_Applications();
?>


<h2><?=$title?></h2>
<div class="maintable"><ul><?php
	$aBackends = cADController::GET_all_Backends();
	$iBackID = 0;
	foreach ($aBackends as $sBackend=>$aApps){
		$iBackID++;
		?><li><a href="#<?=$iBackID?>"><?=$sBackend?></a><?php
	}
?></ul></div>

<!-- ############################################################## -->
<h2>Backend  Details</h2>
<?php
	$iBackID = 0;
	foreach ($aBackends as $sBackend=>$aApps){
		$iBackID++;
		$sClass=cRender::getRowClass();
		
		?><h3><a name="<?=$iBackID?>"><?=$sBackend?></a></h3><?php
			foreach ($aApps as $oApp){
				?><h4><?=$oApp->name?></h4><?php
				
				$aMetrics = [];
				$sMetricUrl = cADMetricPaths::backendCallsPerMin($sBackend);
				$aMetrics[] = [cChart::LABEL=>"Calls Per minute: ($sBackend) in ($oApp->name) App", cChart::METRIC=>$sMetricUrl];
				$sMetricUrl = cADMetricPaths::backendResponseTimes($sBackend);
				$aMetrics[] = [cChart::LABEL=>"Response Times: ($sBackend) in ($oApp->name) App", cChart::METRIC=>$sMetricUrl];
				cChart::metrics_table($oApp, $aMetrics, 2, cRender::getRowClass());

				$sGoUrl = cHttp::build_url("backcalls.php", cRenderQS::APP_QS, $oApp->name);
				$sGoUrl = cHttp::build_url($sGoUrl, cRenderQS::APP_ID_QS, $oApp->id);
				$sGoUrl = cHttp::build_url($sGoUrl, cRenderQS::BACKEND_QS, $sBackend);
				cRender::button("Go", $sGoUrl);
			}
	}

cChart::do_footer();
cRenderHtml::footer();
?>
