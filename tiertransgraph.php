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
require_once("inc/inc-filter.php");

//####################################################################
cRender::html_header("Tier Transactions");
cRender::force_login();
?>
	<script type="text/javascript" src="js/remote.js"></script>
	
<?php
cChart::do_header();
cChart::$width=cRender::CHART_WIDTH_LARGE/2;
	
//###################### DATA #############################################################
//display the results
$app = cHeader::get(cRender::APP_QS);
$tier = cHeader::get(cRender::TIER_QS);
$tid = cHeader::get(cRender::TIER_ID_QS);
$aid = cHeader::get(cRender::APP_ID_QS);
$node= cHeader::get(cRender::NODE_QS);
$gsAppQs=cRender::get_base_app_QS();
$gsTierQs=cRender::get_base_tier_QS();
$gsMetricType = cHeader::get(cRender::METRIC_TYPE_QS);
if ($gsMetricType==null) $gsMetricType = cRender::METRIC_TYPE_ACTIVITY;

$gsBaseUrl = cHttp::build_url("tiertransgraph.php", $gsTierQs );
if ($node) $gsBaseUrl = cHttp::build_url($gsBaseUrl, cRender::NODE_QS, $node );

$sExtraCaption = ($node?"($node) node":"");

$title= "$app&gt;$tier $sExtraCaption&gt;Transactions";
cRender::show_time_options( $title); 

$oCred = cRender::get_appd_credentials();
if ($oCred->restricted_login == null){
	cRenderMenus::show_tier_functions();
	cRenderMenus::show_tier_menu("change tier", "tiertransgraph.php");
	$sFilterQS = cHttp::build_QS($gsAppQs, cFilter::makeTierFilter($tier));
	?>
	<select id="nodesMenu">
		<option selected disabled>Show...</option>
		<option value="apptrans.php?<?=$gsAppQs?>">All Transactions for (<?=$app?>) application</option>
		<option value="apptrans.php?<?=$sFilterQS?>">Transactions table for <?=$tier?></option>
		
		<optgroup label="Servers">
		<?php
			if ($node){
				?><option value="tiertrans.php?<?=$gsTierQs?>">All servers in tier</option><?php
			}
			$aNodes = cAppdyn::GET_TierAppNodes($app,$tier);
			foreach ($aNodes as $oNode){
				$sDisabled = ($oNode->name==$node?"disabled":"");
				$sUrl = cHttp::build_url("tiertransgraph.php",$gsTierQs);
				$sUrl = cHttp::build_url($sUrl, cRender::NODE_QS, $oNode->name);
				
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
			$("#nodesMenu").selectmenu({change:common_onListChange});
		}  
	);
	</script><?php
}
cRender::appdButton(cAppDynControllerUI::tier($aid,$tid));

//###############################################
?>
<h2>Transactions for (<?=$tier?>) tier <?=$sExtraCaption?></h2>

<h3>Overall Stats for (<?=$tier?>) tier</h3>
<table class="maintable">
	<tr class="<?=cRender::getRowClass()?>">
		<td ><?php
			$sMetricUrl=cAppDynMetric::tierCallsPerMin($tier);
			cChart::add("Overall Calls per min ($tier) tier", $sMetricUrl, $app, cRender::CHART_HEIGHT_SMALL);
		?></td>
		<td ><?php
			$sMetricUrl=cAppDynMetric::tierResponseTimes($tier);
			cChart::add("Overall  response times (ms) ($tier) tier", $sMetricUrl, $app, cRender::CHART_HEIGHT_SMALL);
		?></td>
	</tr>
</table>

<?php
if ($node){ ?>
	<h3>Stats for (<?=$node?>) Server</h3>
	<table class="maintable">
		<tr class="<?=cRender::getRowClass()?>">
			<td ><?php
				$sMetricUrl=cAppDynMetric::tierNodeCallsPerMin($tier, $node);
				cChart::add("Overall  response times (ms) ($node) server", $sMetricUrl, $app);
			?></td>
			<td ><?php
				$sMetricUrl=cAppDynMetric::tierNodeResponseTimes($tier, $node);
				cChart::add("Overall  response times (ms) ($node) server", $sMetricUrl, $app);
			?></td>
		</tr>
	</table>
<?php }

//################################################################################################
$aResponse =cAppdyn::GET_tier_transaction_names($app, $tier);
?>
	<p>
	<h3>Transaction Details</h3>
	
	<table class="maintable">
		<tr>
			<th>Calls Per minute</th>
			<th>Response Times in ms</th>
		</tr>
	<?php
		if ($aResponse){
			foreach ($aResponse as $oTrans){
				$sTrans = $oTrans->name;
				$sTrId = $oTrans->id;
				$sLink = cHttp::build_url("transdetails.php?$gsTierQs",cRender::TRANS_QS, $sTrans);
				$sLink = cHttp::build_url($sLink,cRender::TRANS_ID_QS,$sTrId);
				
				if ($node) cHttp::build_url($sLink,cRender::NODE_QS,$node);
				
				?><tr class="<?=cRender::getRowClass()?>">
					<td><?php
						$sMetricUrl=cAppdynMetric::transCallsPerMin($tier, $sTrans, $node);
						cChart::add("Calls ($sTrans)", $sMetricUrl, $app, cRender::CHART_HEIGHT_TINY);
					?></td>
					<td><?php
						$sMetricUrl=cAppdynMetric::transResponseTimes($tier, $sTrans, $node);
						$sDivID = cChart::add("Response ($sTrans)", $sMetricUrl, $app, cRender::CHART_HEIGHT_TINY);
					?></td>
					<td id="<?=$sDivID?>Go"><?php
						cRender::button("Go",$sLink);
					?></td>
				</tr><?php
			}
		}else{
			?><tr><td>No transactions found</td></tr><?php
		}
	?>
	</table>
	<p>
<?php
//###############################################
cChart::do_footer("chart_getUrl", "chart_jsonCallBack");
cRender::html_footer();
?>