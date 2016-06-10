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
require_once("$phpinc/ckinc/header.php");
require_once("$phpinc/ckinc/http.php");
	
cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();

//####################################################################
require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$phpinc/appdynamics/common.php");
require_once("inc/inc-charts.php");
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");


//-----------------------------------------------

//####################################################################
cRender::html_header("All Remote Services");
cRender::force_login();
?>
	<script type="text/javascript" src="<?=$jsinc?>/bean/bean.js"></script>
	<script type="text/javascript" src="js/remote.js"></script>
	<script type="text/javascript" src="js/chart.php"></script>

	<script language="javascript">
		function hide_chart(poData){ //override
			var sDivID = poData.oItem.chart;
			$("#"+sDivID).closest("TABLE").empty(); //the whole row
		}
		bean.on(cChartBean,CHART__NODATA_EVENT,hide_chart);
	</script>
	
<?php
cChart::do_header();
cChart::$width=400;
cChart::$json_data_fn = "chart_getUrl";
cChart::$json_callback_fn = "chart_jsonCallBack";
cChart::$csv_url = "rest/getMetric.php";
cChart::$zoom_url = "metriczoom.php";
cChart::$save_fn = "save_fave_chart";

cChart::$compare_url = "compare.php";
cChart::$metric_qs = cRender::METRIC_QS;
cChart::$title_qs = cRender::TITLE_QS;
cChart::$app_qs = cRender::APP_QS;

$title ="All Remote Services";
cRender::show_time_options( $title); 


//####################################################################
$oApps = cAppDyn::GET_Applications();
?>


<h2><?=$title?></h2>
<div class="maintable"><ul><?php
	$aBackends = cAppDyn::GET_allBackends();
	$iBackID = 0;
	foreach ($aBackends as $sBackend=>$aApps){
		$iBackID++;
		?><li><a href="#<?=$iBackID?>"><?=$sBackend?></a><?php
	}
?></ul></div>

<table class="maintable"><?php
	$iBackID = 0;
	foreach ($aBackends as $sBackend=>$aApps){
		$iBackID++;
		$sClass=cRender::getRowClass();
		$sResponseMetric = cAppDynMetric::backendResponseTimes($sBackend);
		$sCallsMetric = cAppDynMetric::backendCallsPerMin($sBackend);
		
		?><tr class="<?=$sClass?>"><td><dl>
			<dt><h3><a name="<?=$iBackID?>"><?=$sBackend?></a></h3>
			<dd><table border="0" width="100%">
			<?php
				foreach ($aApps as $oApp){
					$sGoUrl = cHttp::build_url("backcalls.php", cRender::APP_QS, $oApp->name);
					$sGoUrl = cHttp::build_url($sGoUrl, cRender::APP_ID_QS, $oApp->id);
					$sGoUrl = cHttp::build_url($sGoUrl, cRender::BACKEND_QS, $sBackend);

					?><tr>
						<td colspan="2" align="left"><?=$oApp->name?></td>
					</tr><tr>
						<td><?php
							cChart::add("Calls Per minute: ($sBackend) in ($oApp->name) App", $sCallsMetric, $oApp->name, 100);
						?></td>
						<td><?php
							cChart::add("Response Times: ($sBackend) in ($oApp->name) App", $sResponseMetric, $oApp->name, 100);
						?></td>
						<td align="right"><?=cRender::button("Go", $sGoUrl)?></td>
					</tr><?php
				}
			?>
			</table>
		</dl></td></tr><?php
	}
?></table><?php

cChart::do_footer("chart_getUrl", "chart_jsonCallBack");
cRender::html_footer();
?>
