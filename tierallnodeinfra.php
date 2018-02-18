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
$root=realpath(".");
$phpinc = realpath("$root/../phpinc");
$jsinc = "../jsinc";

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
require_once("$phpinc/appdynamics/metrics.php");
require_once("$phpinc/appdynamics/common.php");
require_once("inc/inc-charts.php");
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");

//choose a default duration


$CHART_IGNORE_ZEROS = false;

//####################################################################
cRender::html_header("tier infrastructure");
?>
	<script type="text/javascript" src="js/remote.js"></script>
	
<?php
cRender::force_login();
cChart::do_header();
cChart::$width=cRender::CHART_WIDTH_LARGE;

//####################################################################
// huge time limit as this takes a long time//display the results
set_time_limit(200); 

//get passed in values
$app = cHeader::get(cRender::APP_QS);
$tier = cHeader::get(cRender::TIER_QS);
$sMetricType = cHeader::get(cRender::METRIC_TYPE_QS);
$oMetricDetails = cAppDynInfraMetric::getInfrastructureMetric($app,null,$sMetricType);

$title = "$app&gt;$tier&gt;Tier Infrastructure&gt;$oMetricDetails->caption";

//stuff for later
$sAppQS = cRender::get_base_app_QS();
$sTierQS = cRender::get_base_tier_QS();
$oApp = cRender::get_current_app();

// show time options
cRender::show_time_options($title); 
$showlink = cCommon::get_session($LINK_SESS_KEY);

//other buttons
$aMetrics = cAppDynInfraMetric::getInfrastructureMetricTypes();
$oCred = cRender::get_appd_credentials();
if (!$oCred->restricted_login) cRenderMenus::show_tier_functions();
$sAllNodeUrl = cHttp::build_url("appagentdetail.php",$sAppQS);
$sAllNodeUrl = cHttp::build_url($sAllNodeUrl, cRender::METRIC_TYPE_QS, $sMetricType);

//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRender::html_footer();
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
		All <?=$oMetricDetails->short?> data for (<?=$app?>) Application</option>
	<optgroup label="Show details of ..">
	<?php
		$sAllInfraUrl = cHttp::build_url("tierallnodeinfra.php", $sTierQS);
		foreach ( $aMetrics as $sType){
			$oMetric = cAppDynInfraMetric::getInfrastructureMetric($tier,null,$sType);
			?>
				<option <?=($sType==$sMetricType?"disabled":"")?> value="<?=cHttp::build_url($sAllInfraUrl, cRender::METRIC_TYPE_QS, $sType)?>"><?=$oMetric->short?></option>
			<?php
		}
	?>
	</optgroup>
	<optgroup label="Other Statistics">
		<option value="<?=cHttp::build_url("tierjmx.php?$sTierQS", cRender::METRIC_TYPE_QS, cRender::METRIC_TYPE_JMX_DBPOOLS)?>">JMX database pools</option>

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
$aNodes = cAppDyn::GET_TierInfraNodes($app,$tier);	
$aMetricTypes = cAppDynInfraMetric::getInfrastructureMetricTypes();

	
//####################################################################
?>
<h2><?=$oMetricDetails->caption?> for all Servers in (<?=$tier?>) Tier</h2>
<p>
<?php
	$sDiskUrl = cHttp::build_url("nodedisks.php", $sTierQS);
	$sNodeUrl = cHttp::build_url("tierinfrstats.php",$sTierQS);
	
	$aMetrics = [];
	$iWidth = cRender::CHART_WIDTH_LETTERBOX /3 ;

	foreach ($aNodes as $oNode){
		$sNode = $oNode->name;
		if (cFilter::isNodeFilteredOut($sNode)) continue;
		
		$oMetric = cAppDynInfraMetric::getInfrastructureMetric($tier,$sNode, $sMetricType);
		$sUrl = cHttp::build_url($sNodeUrl, cRender::NODE_QS, $sNode);
		$aMetrics[]= [cChart::LABEL=>$oMetric->caption, cChart::METRIC=>$oMetric->metric, cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"see all metrics for Tier"];
	}
	$sClass = cRender::getRowClass();			
	cChart::metrics_table($oApp, $aMetrics, 2, $sClass);
?>

<?php
cChart::do_footer();

cRender::html_footer();
?>
