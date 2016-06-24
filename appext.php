<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2016 

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
require_once("$phpinc/appdynamics/common.php");
require_once("inc/inc-charts.php");
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");

set_time_limit(200); // huge time limit as this takes a long time
$SHOW_PROGRESS=true;
$app = cHeader::get(cRender::APP_QS);
$gsAppQS = cRender::get_base_app_QS();

//####################################################################
cRender::html_header("External Calls");
cRender::force_login();
?>
	<script type="text/javascript" src="js/remote.js"></script>
	<script type="text/javascript" src="js/chart.php"></script>
<?php
cChart::do_header();
cChart::$json_data_fn = "chart_getUrl";
cChart::$json_callback_fn = "chart_jsonCallBack";
cChart::$csv_url = "rest/getMetric.php";
cChart::$zoom_url = "metriczoom.php";
cChart::$save_fn = "save_fave_chart";

cChart::$compare_url = "compare.php";
cChart::$metric_qs = cRender::METRIC_QS;
cChart::$title_qs = cRender::TITLE_QS;
cChart::$app_qs = cRender::APP_QS;
cChart::$width=cRender::CHART_WIDTH_LETTERBOX/2;

//####################################################################

cRender::show_time_options("Apps>$app>External Calls"); 
cRender::show_apps_menu("External Calls", "appext.php");
$oCred = cRender::get_appd_credentials();
if ($oCred->restricted_login == null){ 
	$aTiers = cAppdyn::GET_Tiers($app);

	?><select id="TierMenu">
		<option selected disabled>Show external calls for Tiers...</option>
		<?php
			foreach ($aTiers as $oTier){
				$sUrl = cHttp::build_qs($gsAppQS, cRender::TIER_QS, $oTier->name);
				$sUrl = cHttp::build_qs($sUrl, cRender::TIER_ID_QS, $oTier->id);
				?><option value="tierextgraph.php?<?="$sUrl"?>"><?=$oTier->name?></option><?php
			}
		?>
	</select>
	<script language="javascript">
	$(  
		function(){
			$("#TierMenu").selectmenu({change:common_onListChange});  
		}  
	);
	</script><?php

}

//####################################################################
$oResponse =cAppdyn::GET_AppExtTiers($app);

?>
<script language="javascript">
	function hide_chart(poData){
		var sDivID = poData.oItem.chart;
		$("#"+sDivID).parent().parent().hide();
	}
	bean.on(cChartBean,CHART__NODATA_EVENT,hide_chart);
</script>

<h2>Overall statistics for <?=$app?></h2>
<table class="maintable">
	<tr class="<?=cRender::getRowClass()?>">
		<td><?php
			$sMetricUrl = cAppDynMetric::appCallsPerMin();
			cChart::add("Calls per min", $sMetricUrl, $app, cRender::CHART_HEIGHT_LETTERBOX2);
		?></td>
		<td><?php
			$sMetricUrl = cAppDynMetric::appResponseTimes();
			cChart::add("Response time", $sMetricUrl, $app, cRender::CHART_HEIGHT_LETTERBOX2);
		?></td>
	</tr>
</table>

<h2>External calls from <?=$app?></h2>
<table class="maintable"><?php
	foreach ( $oResponse as $oExtTier){
		$class=cRender::getRowClass();
		$sName = $oExtTier->name;
		?><tr class="<?=$class?>">
			<td><?php
				$sMetricUrl = cAppDynMetric::backendCallsPerMin($sName);
				cChart::add("Calls per min to ($sName)", $sMetricUrl, $app, cRender::CHART_HEIGHT_LETTERBOX2);
			?></td>
			<td><?php
				$sMetricUrl = cAppDynMetric::backendResponseTimes($sName);
				cChart::add("Response time to ($sName)", $sMetricUrl, $app, cRender::CHART_HEIGHT_LETTERBOX2);
			?></td>
		</tr><?php
	}
?>
</table>
<?php
	cChart::do_footer("chart_getUrl", "chart_jsonCallBack");

	cRender::html_footer();
?>
