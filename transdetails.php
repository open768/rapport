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

const COLUMNS=6;

//####################################################################
cRender::html_header("Transactions");
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

//####################################################
//display the results
$app = cHeader::get(cRender::APP_QS);
$tier = cHeader::get(cRender::TIER_QS);
$trans = cHeader::get(cRender::TRANS_QS);
$trid = cHeader::get(cRender::TRANS_ID_QS);
$node= cHeader::get(cRender::NODE_QS);
$sExtraCaption = ($node?"($node) node":"");

$sAppQS = cRender::get_base_app_QS();
$sTierQS = cRender::get_base_tier_QS();
$sTransQS = cHttp::build_QS($sTierQS, cRender::TRANS_QS,$trans);
$sTransQS = cHttp::build_QS($sTransQS, cRender::TRANS_ID_QS,$trid);

//**************************************************
$aNodes = cAppdyn::GET_TierAppNodes($app,$tier);
cRender::show_time_options("$app&gt;$app&gt;$tier&gt;$trans"); 

$oCred = cRender::get_appd_credentials();
if ($oCred->restricted_login == null){?>
	<select id="showMenu">
		<option selected disabled>Back to...</option>
		<optgroup label="All Transactions">
			<option value="apptrans.php?<?=$sAppQS?>">for <?=($app)?> Application</option>
			<option value="tiertransgraph.php?<?=$sTransQS?>">for <?=($tier)?> tier</option>
			<?php
				if ($node){ 
					$sNodeQs = cHttp::build_url($sTransQS, cRender::NODE_QS, $node);
					?><option value="tiertransgraph.php?<?=$sNodeQs?>">
						for (<?=($node)?>) server
					</option><?php 
				}
			?>
		</optgroup>
		<optgroup label="Servers for (<?=$trans?>) transaction">
		<?php
			if ($node){
				?><option value="transdetails.php?<?=$sTransQS?>">All servers</option><?php
			}
			foreach ($aNodes as $oNode){
				$sDisabled = ($oNode->name==$node?"disabled":"");
				$sUrl = "transdetails.php?$sNodeQs=$oNode->name";
				?>
					<option <?=$sDisabled?> value="<?=$sUrl?>"><?=$oNode->name?></option>
				<?php
			}
		?>
		</optgroup>
	</select>
	<script language="javascript">
	$(  
		function(){
			$("#showMenu").selectmenu({change:common_onListChange});
		}  
	);
	</script><?php
}
$aid = cHeader::get(cRender::APP_ID_QS);
cRender::appdButton(cAppDynControllerUI::transaction($aid,$trid));


//####################################################
?>

<!-- ************************************************** -->
<h2>Overall data for <?=$trans?> in (<?=$tier?>) tier </h2>
<table class='maintable'>
	<tr><td class="<?=cRender::getRowClass()?>">
	<?php
		$sMetricUrl=cAppDynMetric::transResponseTimes($tier, $trans);
		cChart::add("Overall Response Times:  ($trans) Transaction, ($tier) Tier", $sMetricUrl, $app);
	?>
	</td></tr>
	<tr><td class="<?=cRender::getRowClass()?>">
	<?php
		$sMetricUrl=cAppDynMetric::transCallsPerMin($tier, $trans);
		cChart::add("Overall Calls per min: ($trans) Transaction, ($tier) Tier", $sMetricUrl, $app);
	?>
	</td></tr>
	<tr><td class="<?=cRender::getRowClass()?>">
	<?php
		$sMetricUrl=cAppDynMetric::transErrors($tier, $trans);
		cChart::add("Overall Errors per min: ($trans) Transaction, ($tier) Tier", $sMetricUrl, $app);
	?>
	</td></tr>
</table>
<p>

<?php
if ($node){ ?>
	<h2>Data for (<?=$node?>) node</h2>
	<table class='maintable'>
		<tr><td class="<?=cRender::getRowClass()?>">
		<?php
			$sMetricUrl=cAppDynMetric::transResponseTimes($tier, $trans, $node);
			cChart::add("Response Times:  ($trans) Transaction, ($tier) Tier, ($node) Server", $sMetricUrl, $app);
		?>
		</td></tr>
		<tr><td class="<?=cRender::getRowClass()?>">
		<?php
			$sMetricUrl=cAppDynMetric::transCallsPerMin($tier, $trans, $node);
			cChart::add("Overall Calls per min: ($trans) Transaction, ($tier) Tier, ($node) Server", $sMetricUrl, $app);
		?>
		</td></tr>
		<tr><td class="<?=cRender::getRowClass()?>">
		<?php
			$sMetricUrl=cAppDynMetric::transErrors($tier, $trans, $node);
			cChart::add("Errors: ($trans) Transaction, ($tier) Tier, ($node) Server", $sMetricUrl, $app);
		?>
		</td></tr>
		<tr><td class="<?=cRender::getRowClass()?>">
		<?php
			$sMetricUrl=cAppDynMetric::transCpuUsed($tier, $trans, $node);
			cChart::add("CPU Used: ($trans) Transaction, ($tier) Tier, ($node) Server", $sMetricUrl, $app);
		?>
		</td></tr>
		<tr><td class="<?=cRender::getRowClass()?>">
		<?php
			$sMetricUrl=cAppDynMetric::InfrastructureCpuBusy($tier, $node);
			cChart::add("Overall CPU Busy: ($tier) Tier, ($node) Server", $sMetricUrl, $app);
		?>
		</td></tr>
		<tr><td class="<?=cRender::getRowClass()?>">
		<?php
			$sMetricUrl=cAppDynMetric::InfrastructureJavaHeapFree($tier, $node);
			cChart::add("Java Heap free: ($tier) Tier, ($node) Server", $sMetricUrl, $app);
		?>
		</td></tr>
		<tr><td class="<?=cRender::getRowClass()?>">
		<?php
			$sMetricUrl=cAppDynMetric::InfrastructureJavaGCTime($tier, $node);
			cChart::add("Java GC time: ($tier) Tier, ($node) Server", $sMetricUrl, $app);
		?>
		</td></tr>
		<tr><td class="<?=cRender::getRowClass()?>">
		<?php
			$sMetricUrl=cAppDynMetric::InfrastructureDotnetHeapUsed($tier, $node);
			cChart::add("dotnet  Heap used: ($tier) Tier, ($node) Server", $sMetricUrl, $app);
		?>
		</td></tr>
		<tr><td class="<?=cRender::getRowClass()?>">
		<?php
			$sMetricUrl=cAppDynMetric::InfrastructureDotnetGCTime($tier, $node);
			cChart::add("dotnet  GC time : ($tier) Tier, ($node) Server", $sMetricUrl, $app);
		?>
		</td></tr>
	</table>
<p>
<?php } ?>

<!-- ************************************************** -->
<h2>External tiers for - <?=$tier?> - <?=$trans?></h2>
	<?php
		//******get the external tiers used by this transaction
		$oData = cAppdyn::GET_TransExtTiers($app, $tier, $trans);
		cChart::$width = cRender::CHART_WIDTH_LETTERBOX / 3;
		if ($oData){
			?><table class='maintable'>
			<tr>
				<th>Name</th>
				<th>Activity</th>
				<th>Response Times (ms)</th>
			</tr>
			
			<?php
			foreach ( $oData as $oItem){
				$other = $oItem->name;
				$sClass = cRender::getRowClass();
				
				?><tr class="<?=$sClass?>">
					<td><?=$other?></td>
					<td><?php
						$sMetricUrl=cAppDynMetric::transExtCalls($tier, $trans, $other);
						cChart::add("Calls per min to: $other", $sMetricUrl, $app, cRender::CHART_HEIGHT_SMALL);
					?></td>
					<td><?php
						$sMetricUrl=cAppDynMetric::transExtResponseTimes($tier, $trans, $other);
						cChart::add("response times: $other", $sMetricUrl, $app, cRender::CHART_HEIGHT_SMALL);
					?></td>
				</tr><?php
			}
			?></table><?php
		}else
			echo "<h3>This transaction has no external calls</h3>";

		//******** investigate the snapshots not avail in 3.3.3
		//$oSnapshots = cAppdyn::GET_Snapshots($app, $tid, $trid);
	?>
</table>

<?php
cChart::do_footer("chart_getUrl", "chart_jsonCallBack");

cRender::html_footer();
?>