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
require_once("inc/inc-charts.php");
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");


//####################################################################
cRender::html_header("All Tiers");
cRender::force_login();
?>
	<script type="text/javascript" src="js/remote.js"></script>
	
<?php
cChart::do_header();
cChart::$width=cRender::CHART_WIDTH_LARGE/3;

//####################################################################
cRender::show_time_options( "All Tiers"); 
		

$aApps = cAppDyn::GET_Applications();
if (count($aApps) == 0) cRender::errorbox("No Applications found");

//####################################################################
foreach ( $aApps as $oApp){
	if (cFilter::isAppFilteredOut($oApp->name)) continue;
	$sAppQS = cRender::build_app_qs($oApp->name, $oApp->id);
	$sClass = cRender::getRowClass();
	?>
	<table class="maintable">
		<tr class="<?=$sClass?>"><td colspan=4>
			<?=cRender::show_app_functions($oApp->name, $oApp->id)?>
		</td></tr>
		<?php
			$aTiers =cAppdyn::GET_Tiers($oApp->name);
			foreach ($aTiers as $oTier){ 
				if (cFilter::isTierFilteredOut($oTier->name)) continue;
				?>	
				<tr class="<?=$sClass?>">
					<td width="50"><?=$oTier->name?></td>
					<td><?php
						$sMetric=cAppDynMetric::tierCallsPerMin($oTier->name);
						cChart::add("calls: $oTier->name", $sMetric, $oApp->name, cRender::CHART_HEIGHT_SMALL);	
					?></td>
					<td><?php
						$sMetricUrl=cAppDynMetric::tierResponseTimes($oTier->name);
						cChart::add("Response: $oTier->name", $sMetric, $oApp->name, cRender::CHART_HEIGHT_SMALL);
					?></td>
					<td width="30"><?php
						$sTierQs = cRender::build_tier_qs($sAppQS, $oTier->name, $oTier->id );
						cRender::button("Go", "tier.php?$sTierQs")
					?></td>
				</tr>
			<?php }
		}
	?>
	</table>
	
	<script language="javascript">
		function hide_row(poData){ //override
			var sDivID = poData.oItem.chart;
			$("#"+sDivID).closest("TABLE").closest("TR").hide(); //the whole row
		}
		bean.on(cChartBean,CHART__NODATA_EVENT,hide_row);
	</script>
<?php
	cChart::do_footer("chart_getUrl", "chart_jsonCallBack");

	cRender::html_footer();
?>
