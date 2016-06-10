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
require_once("inc/inc-charts.php");
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");


cRender::html_header("Node Disks");
cRender::force_login();
//####################################################################
cChart::do_header();
?>
	<script type="text/javascript" src="js/remote.js"></script>
	<script type="text/javascript" src="js/chart.php"></script>
<?php

cChart::$width=940;
cChart::$json_data_fn = "chart_getUrl";
cChart::$json_callback_fn = "chart_jsonCallBack";
cChart::$csv_url = "rest/getMetric.php";
cChart::$zoom_url = "metriczoom.php";
cChart::$save_fn = "save_fave_chart";
cChart::$compare_url = "compare.php";

cChart::$metric_qs = cRender::METRIC_QS;
cChart::$title_qs = cRender::TITLE_QS;
cChart::$app_qs = cRender::APP_QS;

//####################################################################
$app = cHeader::get(cRender::APP_QS);
$tier = cHeader::get(cRender::TIER_QS);
$node = cHeader::get(cRender::NODE_QS);

$sAppQs = cRender::get_base_app_QS();
$sTierQs = cRender::get_base_tier_QS();
$sNodeUrl = "tierinfrstats.php?$sTierQs&".cRender::NODE_QS."=$node";

cRender::show_time_options("Node disks: $node"); 

//####################################################################
$oCred = cRender::get_appd_credentials();
if ($oCred->restricted_login == null){ 
	?><select id="menuBackTo">
		<option selected disabled>Back to...</option>
		<option value="tier.php?<?=$sTierQs?>"><?=$tier?> Tier</option>
		<option value="<?=$sNodeUrl?>">Overall Infrastructure Statistics for <?=$node?> Server</option>
	</select>
	<script language="javascript">
	$(  
		function(){
			$("#menuBackTo").selectmenu({change:common_onListChange});
		}  
	);
	</script><?php 
} 
?>
	<script language="javascript">
		function hide_chart(poData){
			var sDivID = poData.oItem.chart;
			$("#"+sDivID).parent().parent().hide();
		}
		bean.on(cChartBean,CHART__NODATA_EVENT,hide_chart);
	</script>
<?php
$aDisks = cAppdyn::GET_NodeDisks($app,$tier,$node);
?>

<h2>Overall Disk Space free in <?=$node?> Server</h3>
<table class="maintable"><tr><td><?php
	$sMetricUrl = cAppDynMetric::InfrastructureDiskFree($tier,$node);
	cChart::add("Overall Disk space Free", $sMetricUrl, $app);
?></td></tr></table>
<p>
<h2>Individual Disks in <?=$node?> Server</h3>
<table class="maintable"><?php
	foreach ($aDisks as $oDisk){
		if ($oDisk->type !== "folder") continue;
		$sMetricUrl = cAppDynMetric::InfrastructureNodeDiskFree($tier,$node,$oDisk->name);
		?>
			<tr class="<?=cRender::getRowClass()?>"><td>
				<?php cChart::add("Disk Available $oDisk->name", $sMetricUrl, $app); ?>
			</td></tr>
		<?php
	}
?></table>

<?php

//####################################################################
cChart::do_footer("chart_getUrl", "chart_jsonCallBack");
cRender::html_footer();
?>
