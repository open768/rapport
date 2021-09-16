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

require_once("$root/inc/filter.php");

//####################################################################
cRenderHtml::header("Tier Transactions");
cRender::force_login();
cChart::do_header();
cChart::$width=cChart::CHART_WIDTH_LARGE/2;
	
//###################### DATA #############################################################
//display the results
$oTier = cRenderObjs::get_current_tier();
$oApp = $oTier->app;

$node= cHeader::get(cRender::NODE_QS);
$gsAppQs=cRenderQS::get_base_app_QS($oApp);
$gsTierQs=cRenderQS::get_base_tier_QS($oTier);
$gsMetricType = cHeader::get(cRender::METRIC_TYPE_QS);
if ($gsMetricType==null) $gsMetricType = cADMetric::METRIC_TYPE_ACTIVITY;

$gsBaseUrl = cHttp::build_url("tiertransgraph.php", $gsTierQs );
if ($node) $gsBaseUrl = cHttp::build_url($gsBaseUrl, cRender::NODE_QS, $node );

$sExtraCaption = ($node?"($node) node":"");

$title= "$oApp->name&gt;$oTier->name $sExtraCaption&gt;Transactions";

//********************************************************************
if (cAD::is_demo()){
	cRender::errorbox("function not support ed for Demo");
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

	$sMetricpath = cADMetric::transResponseTimes($poTier->name, "*");
	$aStats = $poApp->GET_MetricData( $sMetricpath, $oTimes,"true",false,true);
	uasort($aStats,"sort_by_metricpath" );

	$aMetrics=[];
	$iCount  = 0;
	foreach ($aStats as $oTrans){
		$oStats =  cADUtil::Analyse_Metrics($oTrans->metricValues);
		$sTrName = cADUtil::extract_bt_name($oTrans->metricPath, $poTier->name);
		try{
			$sTrID = cADUtil::extract_bt_id($oTrans->metricName);
		}
		catch (Exception $e){
			$sTrID = null;
		}
		$sLink = null;
		
		if ($oStats->count == 0)	continue;
		$iCount ++;
		$sLink = cHttp::build_url($sBaseUrl,cRender::TRANS_QS, $sTrName);
		$sLink = cHttp::build_url($sLink,cRender::TRANS_ID_QS,$sTrID);
		
		if ($node) $sLink = cHttp::build_url($sLink,cRender::NODE_QS,$node);
		
		$sMetricUrl=cADMetric::transCallsPerMin($poTier->name, $sTrName, $node);
		$aMetrics[] = [
			cChart::LABEL=>"Calls ($sTrName)", cChart::METRIC=>$sMetricUrl, 
			cChart::GO_URL=>$sLink, cChart::GO_HINT=>"Go"
		];
		
		$sMetricUrl=cADMetric::transResponseTimes($poTier->name, $sTrName,$node);
		$aMetrics[] = [
			cChart::LABEL=>"Response ($sTrName)", cChart::METRIC=>$sMetricUrl, 
		];

		$sMetricUrl=cADMetric::transErrors($poTier->name, $sTrName,$node);
		$aMetrics[] = [
			cChart::LABEL=>"Errors ($sTrName)", cChart::METRIC=>$sMetricUrl, 
		];

		$sMetricUrl=cADMetric::transCpuUsed($poTier->name, $sTrName,$node);
		$aMetrics[] = [
			cChart::LABEL=>"CPU ($sTrName)", cChart::METRIC=>$sMetricUrl, 
		];
	}
	
	if ($iCount >0)
		cChart::metrics_table($poApp,$aMetrics,4,cRender::getRowClass(),null,null,["calls per minute", "Response Times (ms)", "Errors per minute"]);
	else{
		cRender::messagebox("No transactions found");
	}
	
	cDebug::leave();
}

//********************************************************************
$oCred = cRenderObjs::get_appd_credentials();
if ($oCred->restricted_login == null){
	cRenderMenus::show_tier_functions();
	cRenderMenus::show_tier_menu("change tier", "tiertransgraph.php");
	$sFilterQS = cHttp::build_QS($gsAppQs, cFilter::makeTierFilter($oTier->name));


	?>
	<select id="nodesMenu">
		<option selected disabled>Show...</option>
		<option value="apptrans.php?<?=$gsAppQs?>">All Transactions for <?=cRender::show_name(cRender::NAME_APP,$oApp)?> application</option>
		<option value="apptrans.php?<?=$sFilterQS?>">Transactions table for <?=$oTier->name?></option>
		
		<optgroup label="Servers">
		<?php
			if ($node){
				?><option value="tiertrans.php?<?=$gsTierQs?>">All servers in tier</option><?php
			}
			$aNodes = $oTier->GET_nodes();
			foreach ($aNodes as $oNode){
				$sDisabled = ($oNode->name==$node?"disabled":"");
				$sUrl = cHttp::build_url("tiertransgraph.php",$gsTierQs);
				$sUrl = cHttp::build_url($sUrl, cRender::NODE_QS, $oNode->name);
				
				?>
					<option <?=$sDisabled?> value="<?=$sUrl?>"><?=$oNode->name?></option>
				<?php
			}
		?>
		</optgroup>
	</select>
	<script language="javascript">
	$(  
		function(){
			$("#nodesMenu").selectmenu({change:common_onListChange});
		}  
	);
	</script><?php
}
cRender::appdButton(cADControllerUI::tier($oApp,$oTier));

//###############################################
?>
<h2>Transactions for <?=cRender::show_name(cRender::NAME_TIER,$oTier)?> <?=$sExtraCaption?></h2>

<h3>Overall Stats for <?=cRender::show_name(cRender::NAME_TIER,$oTier)?></h3>
<?php
	$sBaseUrl = cHttp::build_url("alltiertrans.php",$gsTierQs);
	
	$aMetrics=[];
	$sMetricUrl=cADMetric::tierCallsPerMin($oTier->name);
	$sUrl = cHttp::build_url($sBaseUrl, cRender::METRIC_QS, cADMetric::CALLS_PER_MIN );
	$aMetrics[] = [
		cChart::LABEL=>"Overall Calls per min ($oTier->name) tier", cChart::METRIC=>$sMetricUrl,
		cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"All Transactions"
	];
	
	$sMetricUrl=cADMetric::tierResponseTimes($oTier->name);
	$sUrl = cHttp::build_url($sBaseUrl, cRender::METRIC_QS, cADMetric::RESPONSE_TIME );
	$aMetrics[] = [
		cChart::LABEL=>"Overall response times (ms) ($oTier->name) tier", cChart::METRIC=>$sMetricUrl,
		cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"All Transactions"
	];
	
	$sMetricUrl=cADMetric::tierErrorsPerMin($oTier->name);
	$sUrl = cHttp::build_url($sBaseUrl, cRender::METRIC_QS, cADMetric::ERRS_PER_MIN );
	$aMetrics[] = [
		cChart::LABEL=>"Errors($oTier->name) tier", cChart::METRIC=>$sMetricUrl,
		cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"All Transactions"
	];
	
	$sMetricUrl = cADMetric::InfrastructureCpuBusy($oTier->name);
	$aMetrics[] = [
		cChart::LABEL=>"CPU($oTier->name)tier", cChart::METRIC=>$sMetricUrl
	];
	
	cChart::metrics_table($oApp,$aMetrics,4,cRender::getRowClass());


if ($node){ 
	?><h3>Stats for (<?=$node?>) Server</h3><?php
	$aMetrics=[];
	$sMetricUrl=cADMetric::tierNodeCallsPerMin($oTier->name, $node);
	$aMetrics[] = [cChart::LABEL=>"Overall  Calls per min ($node) server", cChart::METRIC=>$sMetricUrl];
	$sMetricUrl=cADMetric::tierNodeResponseTimes($oTier->name, $node);
	$aMetrics[] = [cChart::LABEL=>"Overall  response times (ms) ($node) server", cChart::METRIC=>$sMetricUrl];
	cChart::metrics_table($oApp,$aMetrics,2,cRender::getRowClass());
}

//################################################################################################
?><p><h3>Transaction Details</h3><?php
render_tier_transactions($oApp, $oTier);

//###############################################
cChart::do_footer();
cRenderHtml::footer();
?>