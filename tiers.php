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


//-----------------------------------------------
$app = cHeader::get(cRender::APP_QS);
$aid = cHeader::get(cRender::APP_ID_QS);

//####################################################################
cRender::html_header("Tiers in Application $app");
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

cRender::show_time_options( $app); 


//####################################################################
cRender::show_apps_menu("Tier Activity","tiers.php");
$sAppQS = cRender::get_base_app_QS();

cRender::appdButton(cAppDynControllerUI::application($aid));
cChart::$width=cRender::CHART_WIDTH_LETTERBOX/2;

//####################################################################
?>
	<table class="maintable"><tr>
		<td>
		<?php
			$sMetricUrl=cAppDynMetric::appCallsPerMin();
			cChart::add("Overall Calls per min", $sMetricUrl, $app, cRender::CHART_HEIGHT_LETTERBOX);
		?>
		</td>
		<td>
		<?php
			$sMetricUrl=cAppDynMetric::appResponseTimes();
			cChart::add("Overall response time in ms", $sMetricUrl, $app, cRender::CHART_HEIGHT_LETTERBOX );
		?>
		</td>
	</tr></table>
<?php

	//-----------------------------------------------
	$oResponse =cAppdyn::GET_Tiers($app);
	cChart::$width=940;
?>

<p>
<h2>Tiers Activity in application (<?=$app?>)</h2>
<table class="maintable">
	<?php
		cChart::$width=cRender::CHART_WIDTH_LETTERBOX/2;
		foreach ( $oResponse as $oTier){
			$sTier=$oTier->name;
			if (cFilter::isTierFilteredOut($sTier)) continue;
			
			$class=cRender::getRowClass();
			?>
				<tr class="<?=$class?>" align="left">
					<td colspan=2><?php
						cRender::show_tier_functions($sTier, $oTier->id)
						?> 
						<i><small><?=$oTier->type?></i></small><br>
					</td>
				</tr>
				<tr class="<?=$class?>">
					<td><?php
						$sMetricUrl=cAppDynMetric::tierCallsPerMin($sTier);
						cChart::add("Calls Per minute", $sMetricUrl, $app, cRender::CHART_HEIGHT_LETTERBOX);						
					?></td>
					<td><?php
						$sMetricUrl=cAppDynMetric::tierResponseTimes($sTier);
						cChart::add("Response Times in ms", $sMetricUrl, $app, cRender::CHART_HEIGHT_LETTERBOX);						
					?></td>
				</tr>
			<?php
		}
	?>
</table>
<?php
	cChart::do_footer("chart_getUrl", "chart_jsonCallBack");

	cRender::html_footer();
?>
