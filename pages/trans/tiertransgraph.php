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


//####################################################################
	
//###################### DATA #############################################################
//display the results
$oTier = cRenderObjs::get_current_tier();
$oApp = $oTier->app;

$node= cHeader::get(cRenderQS::NODE_QS);
$gsAppQs=cRenderQS::get_base_app_QS($oApp);
$gsTierQs=cRenderQS::get_base_tier_QS($oTier);
$gsMetricType = cHeader::get(cRenderQS::METRIC_TYPE_QS);
if ($gsMetricType==null) $gsMetricType = cADMetricPaths::METRIC_TYPE_ACTIVITY;

$gsBaseUrl = cHttp::build_url(cCommon::filename(), $gsTierQs );
if ($node) $gsBaseUrl = cHttp::build_url($gsBaseUrl, cRenderQS::NODE_QS, $node );

$sExtraCaption = ($node?"($node) node":"");
$title= "$oApp->name&gt;$oTier->name $sExtraCaption&gt;Transaction graphs";

cRenderHtml::$load_google_charts = true;
cRenderHtml::header($title);
cRender::force_login();
cChart::do_header();
cChart::$width=cChart::CHART_WIDTH_LARGE/2;

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
function sort_by_metricpath($a,$b){
	return strcasecmp($a->metricPath, $b->metricPath);
}

//********************************************************************
function render_tier_transactions($poApp, $poTier){	
	global $giTotalTrans;
	global $node;
	cDebug::enter();
	$oTimes = cRender::get_times();

	$sTierQS = cRenderQS::get_base_tier_QS($poTier);
	$sBaseUrl = cHttp::build_url("transdetails.php", $sTierQS);
	$iCount = 0;

	$oAllTrans = new cADBT( $poTier, "*", null, true);
	$sMetricpath = cADMetricPaths::transResponseTimes($oAllTrans);
	$aStats = $poApp->GET_MetricData( $sMetricpath, $oTimes,true,false,true);
	uasort($aStats,"sort_by_metricpath" );

	$aMetrics=[];
	$iCount  = 0;
	foreach ($aStats as $oTrans){
		$oStats =  cADAnalysis::analyse_metrics($oTrans->metricValues);
		$sTrName = cADUtil::extract_bt_name($oTrans->metricPath, $poTier->name);
		try{
			$sTrID = cADUtil::extract_bt_id($oTrans->metricName);
		}
		catch (Exception $e){
			$sTrID = null;
			continue;
		}
		$sLink = null;
		$oTrans = new cADBT($poTier, $sTrName, $sTrID);
		
		if ($oStats->count == 0)	continue;
		$iCount ++;
		$sLink = cHttp::build_url($sBaseUrl,cRenderQS::TRANS_QS, $sTrName);
		$sLink = cHttp::build_url($sLink,cRenderQS::TRANS_ID_QS,$sTrID);
		
		if ($node) $sLink = cHttp::build_url($sLink,cRenderQS::NODE_QS,$node);
		
		$sMetricUrl=cADMetricPaths::transCallsPerMin($oTrans, $node);
		$aMetrics[] = [
			cChart::LABEL=>"Calls ($sTrName)", cChart::METRIC=>$sMetricUrl, 
			cChart::GO_URL=>$sLink, cChart::GO_HINT=>"Go"
		];
		
		$sMetricUrl=cADMetricPaths::transResponseTimes($oTrans,$node);
		$aMetrics[] = [
			cChart::LABEL=>"Response ($sTrName)", cChart::METRIC=>$sMetricUrl, 
		];

		$sMetricUrl=cADMetricPaths::transErrors($oTrans,$node);
		$aMetrics[] = [
			cChart::LABEL=>"Errors ($sTrName)", cChart::METRIC=>$sMetricUrl, 
		];

		$sMetricUrl=cADMetricPaths::transCpuUsed($oTrans,$node);
		$aMetrics[] = [
			cChart::LABEL=>"CPU ($sTrName)", cChart::METRIC=>$sMetricUrl, 
		];
	}
	
	if ($iCount >0)
		cChart::metrics_table($poApp,$aMetrics,4,cRender::getRowClass(),null,null,["calls per minute", "Response Times (ms)", "Errors per minute"]);
	else{
		cCommon::messagebox("No transactions found");
	}
	
	cDebug::leave();
}

//********************************************************************
function show_node_menu(){
	global $oTier, $oApp, $gsTierQs, $gsAppQs;
	


	?>
	<select id="nodesMenu">
		<option selected disabled>Show...</option>
		<option value="apptrans.php?<?=$gsAppQs?>">All Transactions for <?=$oApp->name?> application</option>
		
		<optgroup label="Servers">
		<?php
			if ($node){
				?><option value="tiertrans.php?<?=$gsTierQs?>">All servers in tier</option><?php
			}
			$aNodes = $oTier->GET_nodes();
			foreach ($aNodes as $oNode){
				$sDisabled = ($oNode->name==$node?"disabled":"");
				$sUrl = cHttp::build_url(cCommon::filename(),$gsTierQs);
				$sUrl = cHttp::build_url($sUrl, cRenderQS::NODE_QS, $oNode->name);
				
				?>
					<option <?=$sDisabled?> value="<?=$sUrl?>"><?=$oNode->name?></option>
				<?php
			}
		?>
		</optgroup>
	</select>
	<script>
	$(  
		function(){
			$("#nodesMenu").selectmenu({change:common_onListChange});
		}  
	);
	</script><?php
}

//###############################################
cRenderCards::card_start("Overall Stats for $oTier->name");
cRenderCards::body_start();
	$sBaseUrl = cHttp::build_url("alltiertrans.php",$gsTierQs);
	
	$aMetrics=[];
	$sMetricUrl=cADTierMetricPaths::tierCallsPerMin($oTier->name);
	$sUrl = cHttp::build_url($sBaseUrl, cRenderQS::METRIC_QS, cADMetricPaths::CALLS_PER_MIN );
	$aMetrics[] = [
		cChart::LABEL=>"Overall Calls per min ($oTier->name) tier", cChart::METRIC=>$sMetricUrl,
		cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"All Transactions"
	];
	
	$sMetricUrl=cADTierMetricPaths::tierResponseTimes($oTier->name);
	$sUrl = cHttp::build_url($sBaseUrl, cRenderQS::METRIC_QS, cADMetricPaths::RESPONSE_TIME );
	$aMetrics[] = [
		cChart::LABEL=>"Overall response times (ms) ($oTier->name) tier", cChart::METRIC=>$sMetricUrl,
		cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"All Transactions"
	];
	
	$sMetricUrl=cADTierMetricPaths::tierErrorsPerMin($oTier->name);
	$sUrl = cHttp::build_url($sBaseUrl, cRenderQS::METRIC_QS, cADMetricPaths::ERRS_PER_MIN );
	$aMetrics[] = [
		cChart::LABEL=>"Errors($oTier->name) tier", cChart::METRIC=>$sMetricUrl,
		cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"All Transactions"
	];
	
	$sMetricUrl = cADInfraMetric::InfrastructureCpuBusy($oTier->name);
	$aMetrics[] = [
		cChart::LABEL=>"CPU($oTier->name)tier", cChart::METRIC=>$sMetricUrl
	];
	
	cChart::metrics_table($oApp,$aMetrics,4,cRender::getRowClass());
cRenderCards::body_end();
cRenderCards::action_start();
	$oCred = cRenderObjs::get_AD_credentials();
	if ($oCred->restricted_login == null){
		cADCommon::button(cADControllerUI::tier($oApp,$oTier));
		cRenderMenus::show_tier_functions();
		cRenderMenus::show_tier_menu("change tier", cCommon::filename());
		show_node_menu();
	}
cRenderCards::action_end();
cRenderCards::card_end();


if ($node){ 
	cRenderCards::card_start("Stats for ($node) Server");
	cRenderCards::body_start();
		$aMetrics=[];
		$sMetricUrl=cADTierMetricPaths::tierNodeCallsPerMin($oTier->name, $node);
		$aMetrics[] = [cChart::LABEL=>"Overall  Calls per min ($node) server", cChart::METRIC=>$sMetricUrl];
		$sMetricUrl=cADTierMetricPaths::tierNodeResponseTimes($oTier->name, $node);
		$aMetrics[] = [cChart::LABEL=>"Overall  response times (ms) ($node) server", cChart::METRIC=>$sMetricUrl];
		cChart::metrics_table($oApp,$aMetrics,2,cRender::getRowClass());
	cRenderCards::body_end();
	cRenderCards::card_end();
}

//################################################################################################
?><p><h3>Transaction Details</h3><?php
cRenderCards::card_start("Transaction Details");
cRenderCards::body_start();
	render_tier_transactions($oApp, $oTier);
cRenderCards::body_end();
cRenderCards::card_end();

//###############################################
cChart::do_footer();
cRenderHtml::footer();
?>