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
require_once "$root/inc/inc-charts.php";


//choose a default duration


$CHART_IGNORE_ZEROS = false;

//####################################################################
cRenderHtml::header("tier infrastructure");
cRender::force_login();
cChart::do_header();

//get passed in values
$oTier = cRenderObjs::get_current_tier();
$oApp = $oTier->app;

$sMetricType = cHeader::get(cRender::METRIC_TYPE_QS);
$oMetricDetails = cAppDynInfraMetric::getInfrastructureMetric($oApp->name,null,$sMetricType);

$title = "$oApp->name&gt;$oTier->name&gt;Tier Infrastructure&gt;$oMetricDetails->caption";

//stuff for later
$sAppQS = cRenderQS::get_base_app_QS($oApp);
$sTierQS = cRenderQS::get_base_tier_QS($oTier);

// show time options
cRender::show_time_options($title); 
$showlink = cCommon::get_session($LINK_SESS_KEY);

//other buttons
$aMetrics = cAppDynInfraMetric::getInfrastructureMetricDetails($oTier);
$oCred = cRenderObjs::get_appd_credentials();
if (!$oCred->restricted_login) cRenderMenus::show_tier_functions();
$sAllNodeUrl = cHttp::build_url("appagentdetail.php",$sAppQS);
$sAllNodeUrl = cHttp::build_url($sAllNodeUrl, cRender::METRIC_TYPE_QS, $sMetricType);

//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************

?>
<select id="menuDetails">
	<option disabled selected>Infrastructure...</option>
	<?php
		$sDisabled = ($oCred->restricted_login? "disabled": "");
	?>
	<option <?=$sDisabled?> value="<?=$sAllNodeUrl?>">
		All <?=$oMetricDetails->short?> data for <?=cRender::show_name(cRender::NAME_APP,$oApp)?> Application</option>
	<optgroup label="Show details of ..">
	<?php
		$sAllInfraUrl = cHttp::build_url("tierallnodeinfra.php", $sTierQS);
		foreach ( $aMetrics as $oType){
			$sType = $oType->type;
			$sUrl = cHttp::build_url($sAllInfraUrl, cRender::METRIC_TYPE_QS, $sType);
			?><option <?=($sType==$sMetricType?"disabled":"")?> value="<?=$sUrl?>"><?=$oType->metric->short?></option><?php
		}
	?>
	</optgroup>
	<optgroup label="Other Statistics">
		<option value="<?=cHttp::build_url("tierjmx.php?$sTierQS", cRender::METRIC_TYPE_QS, cAppDynMetric::METRIC_TYPE_JMX_DBPOOLS)?>">JMX database pools</option>

	</optgroup>
</select>

<script language="javascript">
$(  
	function(){
		$("#menuDetails").selectmenu({change:common_onListChange});
	}  
);
</script>
<?php

//data for the page
$aNodes = $oTier->GET_Nodes();	
$aMetricTypes = cAppDynInfraMetric::getInfrastructureMetricTypes();


	
//####################################################################
?>
<h2><?=$oMetricDetails->caption?> for all Servers in <?=cRender::show_name(cRender::NAME_TIER,$oTier)?> Tier</h2>
<p>
<?php
	$sNodeUrl = cHttp::build_url("tierinfrstats.php",$sTierQS);
	
	$aMetrics = [];
	$iWidth = cChart::CHART_WIDTH_LETTERBOX /3 ;

	foreach ($aNodes as $oNode){
		$sNode = $oNode->name;
		if (cFilter::isNodeFilteredOut($sNode)) continue;
		
		$oMetric = cAppDynInfraMetric::getInfrastructureMetric($oTier->name,$sNode, $sMetricType);
		$sUrl = cHttp::build_url($sNodeUrl, cRender::NODE_QS, $sNode);
		$aMetrics[]= [cChart::LABEL=>$sNode." - ".$oMetric->caption, cChart::METRIC=>$oMetric->metric, cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"all metrics ($sNode)", cChart::HIDEIFNODATA=>1];
	}
	$sClass = cRender::getRowClass();			
	cChart::render_metrics($oApp, $aMetrics, cChart::CHART_WIDTH_LETTERBOX/3);
?>

<?php
cChart::do_footer();

cRenderHtml::footer();
?>
