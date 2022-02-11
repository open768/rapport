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


//choose a default duration


$CHART_IGNORE_ZEROS = false;

//####################################################################
cRenderHtml::header("tier infrastructure");
cRender::force_login();
cChart::do_header();

//get passed in values
$oTier = cRenderObjs::get_current_tier();
$oApp = $oTier->app;

$sMetricType = cHeader::get(cRenderQS::METRIC_TYPE_QS);
$oMetricDetails = cADInfraMetric::getInfrastructureMetric($oApp->name,null,$sMetricType);

$title = "$oApp->name&gt;$oTier->name&gt;Tier Infrastructure&gt;$oMetricDetails->caption";

//stuff for later
$sAppQS = cRenderQS::get_base_app_QS($oApp);
$sTierQS = cRenderQS::get_base_tier_QS($oTier);

// show time options

$showlink = cCommon::get_session($LINK_SESS_KEY);

//other buttons
$aMetrics = cADInfraMetric::getInfrastructureMetricDetails($oTier);
$oCred = cRenderObjs::get_AD_credentials();
if (!$oCred->restricted_login) cRenderMenus::show_tier_functions();
$sAllNodeUrl = cHttp::build_url("appagentdetail.php",$sAppQS);
$sAllNodeUrl = cHttp::build_url($sAllNodeUrl, cRenderQS::METRIC_TYPE_QS, $sMetricType);

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
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
		$sAllInfraUrl = cHttp::build_url(cCommon::filename(), $sTierQS);
		foreach ( $aMetrics as $oType){
			$sType = $oType->type;
			$sUrl = cHttp::build_url($sAllInfraUrl, cRenderQS::METRIC_TYPE_QS, $sType);
			?><option <?=($sType==$sMetricType?"disabled":"")?> value="<?=$sUrl?>"><?=$oType->metric->short?></option><?php
		}
	?>
	</optgroup>
	<optgroup label="Other Statistics">
		<option value="<?=cHttp::build_url("tierjmx.php?$sTierQS", cRenderQS::METRIC_TYPE_QS, cADMetricPaths::METRIC_TYPE_JMX_DBPOOLS)?>">JMX database pools</option>

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
$aMetricTypes = cADInfraMetric::getInfrastructureMetricTypes();


	
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
		
		$oMetric = cADInfraMetric::getInfrastructureMetric($oTier->name,$sNode, $sMetricType);
		$sUrl = cHttp::build_url($sNodeUrl, cRenderQS::NODE_QS, $sNode);
		$aMetrics[]= [cChart::LABEL=>$sNode." - ".$oMetric->caption, cChart::METRIC=>$oMetric->metric, cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"all metrics ($sNode)", cChart::HIDEIFNODATA=>1];
	}
	$sClass = cRender::getRowClass();			
	cChart::render_metrics($oApp, $aMetrics, cChart::CHART_WIDTH_LETTERBOX/3);
?>

<?php
cChart::do_footer();

cRenderHtml::footer();
?>
