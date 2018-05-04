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
require_once("$phpinc/appdynamics/common.php");
require_once("$root/inc/inc-charts.php");
require_once("$root/inc/inc-secret.php");
require_once("$root/inc/inc-render.php");
require_once("$root/inc/inc-filter.php");

//####################################################################
cRender::html_header("Tier Transactions");
cRender::force_login();
cChart::do_header();
cChart::$width=cChart::CHART_WIDTH_LARGE/2;
	
//###################### DATA #############################################################
//display the results
$oApp = cRenderObjs::get_current_app();
$oTier = cRenderObjs::get_current_tier();

$node= cHeader::get(cRender::NODE_QS);
$gsAppQs=cRender::get_base_app_QS();
$gsTierQs=cRender::get_base_tier_QS();
$gsMetricType = cHeader::get(cRender::METRIC_TYPE_QS);
if ($gsMetricType==null) $gsMetricType = cRender::METRIC_TYPE_ACTIVITY;

$gsBaseUrl = cHttp::build_url("tiertransgraph.php", $gsTierQs );
if ($node) $gsBaseUrl = cHttp::build_url($gsBaseUrl, cRender::NODE_QS, $node );

$sExtraCaption = ($node?"($node) node":"");

$title= "$oApp->name&gt;$oTier->name $sExtraCaption&gt;Transactions";
cRender::show_time_options( $title); 

//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRender::html_footer();
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

	$sTierQS = cRender::build_tier_qs($poTier);
	$sBaseUrl = cHttp::build_url("transdetails.php", $sTierQS);
	$iCount = 0;

	$sMetricpath = cAppdynMetric::transResponseTimes($poTier->name, "*");
	$aStats = cAppdynCore::GET_MetricData($poApp, $sMetricpath, $oTimes,"true",false,true);
	uasort($aStats,"sort_by_metricpath" );

	$aMetrics=[];
	$iCount  = 0;
	foreach ($aStats as $oTrans){
		$oStats =  cAppdynUtil::Analyse_Metrics($oTrans->metricValues);
		$sTrName = cAppdynUtil::extract_bt_name($oTrans->metricPath, $poTier->name);
		try{
			$sTrID = cAppdynUtil::extract_bt_id($oTrans->metricName);
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
		
		$sMetricUrl=cAppdynMetric::transCallsPerMin($poTier->name, $sTrName, $node);
		$aMetrics[] = [
			cChart::LABEL=>"Calls ($sTrName)", cChart::METRIC=>$sMetricUrl, 
			cChart::GO_URL=>$sLink, cChart::GO_HINT=>"Go"
		];
		
		$sMetricUrl=cAppDynMetric::transResponseTimes($poTier->name, $sTrName,$node);
		$aMetrics[] = [
			cChart::LABEL=>"Response ($sTrName)", cChart::METRIC=>$sMetricUrl, 
		];

		$sMetricUrl=cAppDynMetric::transErrors($poTier->name, $sTrName,$node);
		$aMetrics[] = [
			cChart::LABEL=>"Errors ($sTrName)", cChart::METRIC=>$sMetricUrl, 
		];
	}
	
	if ($iCount >0)
		cChart::metrics_table($poApp,$aMetrics,3,cRender::getRowClass(),null,null,["calls per minute", "Response Times (ms)", "Errors per minute"]);
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
			$aNodes = cAppdyn::GET_TierAppNodes($oApp->name,$oTier->name);
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
cRender::appdButton(cAppDynControllerUI::tier($oApp,$oTier));

//###############################################
?>
<h2>Transactions for <?=cRender::show_name(cRender::NAME_TIER,$oTier)?> <?=$sExtraCaption?></h2>

<h3>Overall Stats for <?=cRender::show_name(cRender::NAME_TIER,$oTier)?></h3>
<?php
	$aMetrics=[];
	$sMetricUrl=cAppDynMetric::tierCallsPerMin($oTier->name);
	$aMetrics[] = [cChart::LABEL=>"Overall Calls per min ($oTier->name) tier", cChart::METRIC=>$sMetricUrl];
	$sMetricUrl=cAppDynMetric::tierResponseTimes($oTier->name);
	$aMetrics[] = [cChart::LABEL=>"Overall response times (ms) ($oTier->name) tier", cChart::METRIC=>$sMetricUrl];
	$sMetricUrl=cAppDynMetric::tierErrorsPerMin($oTier->name);
	$aMetrics[] = [cChart::LABEL=>"Errors($oTier->name) tier", cChart::METRIC=>$sMetricUrl];
	cChart::metrics_table($oApp,$aMetrics,3,cRender::getRowClass());


if ($node){ 
	?><h3>Stats for (<?=$node?>) Server</h3><?php
	$aMetrics=[];
	$sMetricUrl=cAppDynMetric::tierNodeCallsPerMin($oTier->name, $node);
	$aMetrics[] = [cChart::LABEL=>"Overall  Calls per min ($node) server", cChart::METRIC=>$sMetricUrl];
	$sMetricUrl=cAppDynMetric::tierNodeResponseTimes($oTier->name, $node);
	$aMetrics[] = [cChart::LABEL=>"Overall  response times (ms) ($node) server", cChart::METRIC=>$sMetricUrl];
	cChart::metrics_table($oApp,$aMetrics,2,cRender::getRowClass());
}

//################################################################################################
?><p><h3>Transaction Details</h3><?php
render_tier_transactions($oApp, $oTier);

//###############################################
cChart::do_footer();
cRender::html_footer();
?>