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
$rum_page = cHeader::get(cRender::RUM_PAGE_QS);
$rum_type = cHeader::get(cRender::RUM_TYPE_QS);
$gsAppQS = cRender::get_base_app_QS();

//####################################################################
$title ="$app&gtWeb Real User Monitoring Details&gt;$rum_page";
cRender::html_header("Web browser - Real user monitoring - $rum_page");
cRender::show_time_options( $title); 
cRender::force_login();
?>
	<script type="text/javascript" src="js/remote.js"></script>
	
<?php
cChart::do_header();
cChart::$width=cRender::CHART_WIDTH_LETTERBOX;;

cRender::button("back to $app RUM details", "rumstats.php?$gsAppQS");
//cRender::appdButton(cAppDynControllerUI::webrum($aid));

//####################################################################

?>
	<table class="maintable">
		<tr><td class="<?=cRender::getRowClass()?>">
			<?php
				$sMetricUrl=cAppDynMetric::webrumPageCallsPerMin($rum_type, $rum_page);
				cChart::add("Page requests: $rum_page", $sMetricUrl, $app);
			?>
		</td></tr>
		<tr><td class="<?=cRender::getRowClass()?>">
			<?php
				$sMetricUrl=cAppDynMetric::webrumPageResponseTimes($rum_type, $rum_page);
				cChart::add("Page Response times: $rum_page", $sMetricUrl, $app);
			?>
		</td></tr>
		<tr><td class="<?=cRender::getRowClass()?>">
			<?php
				$sMetricUrl=cAppDynMetric::webrumPageTCPTime($rum_type, $rum_page);
				cChart::add("Page connection time", $sMetricUrl, $app);
			?>
		</td></tr>
		<tr><td class="<?=cRender::getRowClass()?>">
			<?php
				$sMetricUrl=cAppDynMetric::webrumPageServerTime($rum_type, $rum_page);
				cChart::add("Page Server time", $sMetricUrl, $app);
			?>
		</td></tr>
		<tr><td class="<?=cRender::getRowClass()?>">
			<?php
				$sMetricUrl=cAppDynMetric::webrumPageFirstByte($rum_type, $rum_page);
				cChart::add("Page first byte time", $sMetricUrl, $app);
			?>
		</td></tr>
	</table>

<?php

	//-----------------------------------------------
?>

<?php
	cChart::do_footer();

	cRender::html_footer();
?>
