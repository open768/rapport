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
$oApp = cRenderObjs::get_current_app();
$gsAppQs = cRenderQS::get_base_app_QS($oApp);
$aTiers =$oApp->GET_Tiers();

//####################################################################
cRenderHtml::$load_google_charts = true;
cRenderHtml::header("$oApp->name Overview");
cRender::force_login();
cChart::$show_export_all = "0";
cChart::do_header();

//#####################################################################
//TODO make sections asynchonous, it takes a long time to load

//####################################################################
cRenderCards::card_start("Overview for $oApp->name");
	cRenderCards::body_start();
	?>
	<ul>
		<li><a href="#app">Application Overview</a>
		<li><a href="#backend">Backends</a>
		<li><a href="#tperf">Tier Performance</a>
		<li><a href="#trans">Transactions</a>
	</ul>
	<?php
	cRenderCards::body_end();
	cRenderCards::action_start();
		cRenderMenus::show_app_change_menu("Show Overview for:");
		cADCommon::button(cADControllerUI::application($oApp));
	cRenderCards::action_end();
cRenderCards::card_end();

//####################################################################
cRenderCards::card_start("<a name='app'>Application Overview</a>");
	cRenderCards::body_start();
		$aMetrics  = [
			[cChart::LABEL=>"response time in ms", cChart::METRIC=>cADAppMetricPaths::appResponseTimes()],
			[cChart::LABEL=>"Calls per min", cChart::METRIC=>cADAppMetricPaths::appCallsPerMin()],
			[cChart::LABEL=>"Slow Calls", cChart::METRIC=>cADAppMetricPaths::appSlowCalls()],
			[cChart::LABEL=>"Very Slow Calls", cChart::METRIC=>cADAppMetricPaths::appVerySlowCalls()],
			[cChart::LABEL=>"Stalled", cChart::METRIC=>cADAppMetricPaths::appStalledCount()],
			[cChart::LABEL=>"Errors per min", cChart::METRIC=>cADAppMetricPaths::appErrorsPerMin()],
			[cChart::LABEL=>"Exceptions", cChart::METRIC=>cADAppMetricPaths::appExceptionsPerMin()]
		];
		cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);
		cDebug::flush();
	cRenderCards::body_end();
cRenderCards::card_end();

//####################################################################
cRenderCards::card_start("<a name='tperf'>Tier Performance</a>");
	cRenderCards::body_start();
		//-----------------------------------------------
		$sClass=cRender::getRowClass();
		$iHeight=100;

		foreach ( $aTiers as $oTier){
			$sTier=$oTier->name;
			//------------------------------------------------
			?><hr><?php
			cRenderMenus::show_tier_functions($oTier);
			?><br><?php
			$aMetrics = [];
			$aMetrics[] = [cChart::LABEL=>"Calls Per min ", cChart::METRIC=>cADTierMetricPaths::tierCallsPerMin($sTier)];
			$aMetrics[] = [cChart::LABEL=>"Response Times", cChart::METRIC=>cADTierMetricPaths::tierResponseTimes($sTier)];
			$aMetrics[] = [cChart::LABEL=>"CPU Busy", cChart::METRIC=>cADInfraMetric::InfrastructureCpuBusy($sTier)];
			$aMetrics[] = [cChart::LABEL=>"Java Heap Used", cChart::METRIC=>cADInfraMetric::InfrastructureJavaHeapUsed($sTier)];
			$aMetrics[] = [cChart::LABEL=>".Net Heap Used", cChart::METRIC=>cADInfraMetric::InfrastructureDotnetHeapUsed($sTier)];
			cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);
		}
		cDebug::flush();
	cRenderCards::body_end();
cRenderCards::card_end();
	
//####################################################################
cRenderCards::card_start("<a name='backend'>Backends</a>");
	cRenderCards::body_start();
		$oBackends =$oApp->GET_Backends();
		$sBackendURL = cHttp::build_url("backcalls.php",$gsAppQs );
		
		foreach ( $oBackends as $oBackend){
			$sClass=cRender::getRowClass();
				$aMetrics = [];
				$sMetricUrl=cADMetricPaths::backendCallsPerMin($oBackend->name);
				$aMetrics[] = [cChart::LABEL=>"Calls per min", cChart::METRIC=>$sMetricUrl];
				$sMetricUrl=cADMetricPaths::backendResponseTimes($oBackend->name);
				$aMetrics[] = [cChart::LABEL=>"Response Times", cChart::METRIC=>$sMetricUrl];
				$sMetricUrl=cADMetricPaths::backendErrorsPerMin($oBackend->name);
				$aMetrics[] = [cChart::LABEL=>"Errors Per min", cChart::METRIC=>$sMetricUrl];
				?><hr><?php
				cRender::button($oBackend->name, cHttp::build_url($sBackendURL, cRenderQS::BACKEND_QS, $oBackend->name));
				?><br><?php
				cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);
		}
		cDebug::flush();
	cRenderCards::body_end();
cRenderCards::card_end();


//####################################################################
cRenderCards::card_start("<a name='trans'>Transactions</a>");
	cRenderCards::body_start();
		//********************************************************************
		if (cAD::is_demo()){
			cCommon::errorbox("Transactions not supported for Demo");
			cChart::do_footer();
			cRenderHtml::footer();
			exit;
		}

		$iHeight=100;
		foreach ($aTiers as $oTier){
			$sTier = $oTier->name;
			$sTierQS = cHttp::build_QS($gsAppQs, cRenderQS::TIER_QS, $sTier);
			$sTierQS = cHttp::build_QS($sTierQS, cRenderQS::TIER_ID_QS, $oTier->id);
			
			?><h3><?=$oTier->name?></h3><?php
			$aTransactions = $oTier->GET_all_transaction_names();
			if ($aTransactions==null) {
				cCommon::messagebox("unable to get transaction names");
				continue;
			}
				
				
			foreach ($aTransactions as $oTrans){
				$sTrans = $oTrans->name;
				$sClass=cRender::getRowClass();
				
				?><DIV class="<?=$sClass?>"><?php
					$sUrl = cHttp::build_url("../trans/transdetails.php?$sTierQS",cRenderQS::TRANS_QS, $sTrans);
					cRender::button($oTrans->name, $sUrl);
					
					$aMetrics = [];
						$sMetricUrl=cADMetricPaths::transCallsPerMin($oTrans);
						$aMetrics[] = [cChart::LABEL=>"Calls per min", cChart::METRIC=>$sMetricUrl];
						
						$sMetricUrl=cADMetricPaths::transResponseTimes($oTrans);
						$aMetrics[] = [cChart::LABEL=>"Response times", cChart::METRIC=>$sMetricUrl];

						$sMetricUrl=cADMetricPaths::transErrors($oTrans);
						$aMetrics[] = [cChart::LABEL=>"Error", cChart::METRIC=>$sMetricUrl];
						
						$sMetricUrl=cADMetricPaths::transCpuUsed($oTrans);
						$aMetrics[] = [cChart::LABEL=>"CPU Used", cChart::METRIC=>$sMetricUrl];
						
					cChart::metrics_table($oApp, $aMetrics,4,$sClass);
				?></div><?php
			}
		}
	cRenderCards::body_end();
cRenderCards::card_end();

//####################################################################
cChart::do_footer();
cRenderHtml::footer();
?>
