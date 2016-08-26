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
$app = cHeader::get(cRender::APP_QS);
$aid = cHeader::get(cRender::APP_ID_QS);
$psType = cHeader::get(cRender::RUM_DETAILS_QS);

//####################################################################
switch ($psType) {
	case cRender::RUM_DETAILS_ACTIVITY:
		$sLabel = "RUM Page Requests";
		break;
	case cRender::RUM_DETAILS_RESPONSE:
		$sLabel = "RUM Response Times";
		break;
	default:
		cDebug::error("unknown type");
}

function pagefn__getMetric($psPage){
	global $psType;

	switch ($psType) {
		case cRender::RUM_DETAILS_ACTIVITY:
			$sMetric = cAppDynMetric::webrumPageCallsPerMin($psPage);
			break;
		case cRender::RUM_DETAILS_RESPONSE:
			$sMetric = cAppDynMetric::webrumPageResponseTimes($psPage);
			break;
		default:
			cDebug::error("unknown type");
	}
	
	return $sMetric;
}

//####################################################################
cRender::html_header("Web browser - Real user monitoring");
cRender::force_login();
?>
	<script type="text/javascript" src="js/remote.js"></script>
	<script type="text/javascript" src="js/chart.php"></script>
<?php
cChart::do_header();
cChart::$width=cRender::CHART_WIDTH_LETTERBOX;
cChart::$json_data_fn = "chart_getUrl";
cChart::$json_callback_fn = "chart_jsonCallBack";
cChart::$csv_url = "rest/getMetric.php";
cChart::$zoom_url = "metriczoom.php";
cChart::$save_fn = "save_fave_chart";

cChart::$compare_url = "compare.php";
cChart::$metric_qs = cRender::METRIC_QS;
cChart::$title_qs = cRender::TITLE_QS;
cChart::$app_qs = cRender::APP_QS;

$title ="$app&gtWeb Real User Monitoring Details";
cRender::show_time_options( $title); 

cRender::button("back to $app RUM", "apprum.php?".cRender::APP_QS."=$app&".cRender::APP_ID_QS."=$aid");

if ($psType == cRender::RUM_DETAILS_ACTIVITY ) 
	echo "RUM Page Requests";
else
	cRender::button("RUM Page Requests", "?".cRender::APP_QS."=$app&".cRender::APP_ID_QS."=$aid&".cRender::RUM_DETAILS_QS."=".cRender::RUM_DETAILS_ACTIVITY);

if ($psType == cRender::RUM_DETAILS_RESPONSE ) 
	echo "RUM Page Response";
else
	cRender::button("RUM Page Response", "?".cRender::APP_QS."=$app&".cRender::APP_ID_QS."=$aid&".cRender::RUM_DETAILS_QS."=".cRender::RUM_DETAILS_RESPONSE);

cRender::appdButton(cAppDynControllerUI::webrum($aid));
	

//####################################################################
$class=cRender::getRowClass();
?>
	<table class="maintable">
		<tr><td class="<?=$class?>">
			<?php
				$sMetricUrl=cAppDynMetric::webrumCallsPerMin();
				cChart::add("Page requests per minute", $sMetricUrl, $app);
				$class=cRender::getRowClass();
			?>
		</td></tr>
		<tr><td class="<?=$class?>">
			<?php
				
				$sMetricUrl=cAppDynMetric::webrumResponseTimes();
				cChart::add("Overall Page Response times", $sMetricUrl, $app);
			?>
		</td></tr>
	</table>
	<p>
<?php
	$class=cRender::getRowClass();
	$aPages = $oResponse =cAppdyn::GET_RUM_pages($app);
	cDebug::vardump($aPages,true);
?>
	<table class="maintable">
		<?php
			foreach ($aPages as $oPage){
				echo "<tr><td class='$class'>";
					$class=cRender::getRowClass();
					$sPage = $oPage->name;
					$sMetric = pagefn__getMetric($oPage->name);
					cRender::button("details for: $sPage", "rumpage.php?".cRender::APP_QS."=$app&".cRender::APP_ID_QS."=$aid&".cRender::RUM_PAGE_QS."=$sPage");
					cChart::add("$sLabel: $sPage", $sMetric, $app);	
				echo "</td></tr>";

			}
		?>
	</table>

<?php

	//-----------------------------------------------
?>

<?php
	cChart::do_footer("chart_getUrl", "chart_jsonCallBack");

	cRender::html_footer();
?>
