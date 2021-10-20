<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2021 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

//####################################################################
//####################################################################
$home="../..";
require_once "$home/inc/common.php";
require_once "$root/inc/charts.php";

const COLUMNS=6;
const FLOW_ID = "trflw";
const MIN_TRANS_TIME=150;

//####################################################################
cRenderHtml::header("Transactions");
cRender::force_login();
?>
	<script type="text/javascript" src="<?=$home?>/js/remote.js"></script>	
	<script type="text/javascript" src="<?=$home?>/js/transflow.js"></script>
<?php
cChart::do_header();

//####################################################
//display the results
$oTrans = cRenderObjs::get_current_trans();
$oTier = $oTrans->tier;
$oApp = $oTier->app;

$node= cHeader::get(cRender::NODE_QS);
$sExtraCaption = ($node?"($node) node":"");

$sAppQS = cRenderQS::get_base_app_QS($oApp);
$sTierQS = cRenderQS::get_base_tier_QS($oTier);

$sTransQS = cHttp::build_QS($sTierQS, cRender::TRANS_QS,$oTrans->name);
$sTransQS = cHttp::build_QS($sTransQS, cRender::TRANS_ID_QS,$oTrans->id);


//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************
$aNodes = $oTier->GET_Nodes();
uasort($aNodes , "AD_name_sort_fn");

$oCred = cRenderObjs::get_AD_credentials();

function show_nodes($psNode){
	global $oNodes, $sTransQS, $aNodes, $oCred;
	
	if ($oCred->restricted_login == null){?>
		<select id="showMenu">
			<optgroup label="Nodes">
				<?php
					if ($node){
						?><option value="transdetails.php?<?=$sTransQS?>">All servers for this transaction</option><?php
						$sNodeQs = cHttp::build_QS($sTransQS, cRender::NODE_QS, $psNode);
						?><option value="tiertransgraph.php?<?=$sNodeQs?>">
							(<?=($node)?>) server
						</option><?php 
					}
				?>
			</optgroup>
			<optgroup label="Nodes">
			<?php
				foreach ($aNodes as $oNode){
					$sDisabled = ($oNode->name==$node?"disabled":"");
					$sNodeQs = cHttp::build_QS($sTransQS, cRender::NODE_QS, $oNode->name);
					$sUrl = "transdetails.php?$sNodeQs";
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
}

//#####################################################################################################
cRenderCards::card_start("Contents");
	cRenderCards::body_start();
	?><ul>
		<li><a href="#1">Data for <?=cRender::show_name(cRender::NAME_TRANS,$oTrans->name)?> in <?=cRender::show_name(cRender::NAME_TIER,$oTier)?></a>
		<li><a href="#2">Transaction Map</a>
		<li><a href="#4">Remote Services</a>
		<li><a href="#5">Transaction Snapshots</a>
	</ul><?php
	cRenderCards::body_end();
	cRenderCards::action_start();
		cADCommon::button(cADControllerUI::transaction($oApp,$oTrans->id));
		cRenderMenus::show_tier_functions();
		cRender::button("Transaction details for all nodes", "transallnodes.php?$sTransQS");
		cRender::button("Search Snapshots", "searchsnaps.php?$sTransQS");
		show_nodes($node);
	cRenderCards::action_end();
cRenderCards::card_end();


//#####################################################################################################
cRenderCards::card_start("<a name='1'>Data</a> for $oTrans->name $oTier->name");
	cRenderCards::body_start();
		$aMetrics = [];
		$aMetrics[] = [cChart::LABEL=>"trans Calls:", cChart::METRIC=>cADMetric::transCallsPerMin($oTier->name, $oTrans->name)];
		$aMetrics[] = [cChart::LABEL=>"trans Response:", cChart::METRIC=>cADMetric::transResponseTimes($oTier->name, $oTrans->name)];
		$aMetrics[] = [cChart::LABEL=>"trans errors:", cChart::METRIC=>cADMetric::transErrors($oTier->name, $oTrans->name)];
		$aMetrics[] = [cChart::LABEL=>"trans cpu used:", cChart::METRIC=>cADMetric::transCpuUsed($oTier->name, $oTrans->name)];
		cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);
	cRenderCards::body_end();
cRenderCards::card_end();

//#####################################################################################################
//TODO make this a widget
cRenderCards::card_start("<a name='2'>Transaction map</a>");
	cRenderCards::body_start();
	
	?><div class="transactionflow" id="<?=FLOW_ID?>">
		Please wait...
	</div>
	<script>
		function load_trans_flow(){
			var oLoader = new cTransFlow("<?=FLOW_ID?>");
			oLoader.home="<?=$home?>";
			oLoader.APP_QS="<?=cRender::APP_QS?>";
			oLoader.TIER_QS="<?=cRender::TIER_QS?>";
			oLoader.TRANS_QS="<?=cRender::TRANS_QS?>";
			oLoader.load("<?=$oApp->name?>", "<?=$oTier->name?>", "<?=$oTrans->name?>");
		}
		$(load_trans_flow);	
	</script><?php
	cRenderCards::body_end();
cRenderCards::card_end();

// ################################################################################
if ($node){ 
	cRenderCards::card_start("<a name='3'>Data</a> for Transaction: $oTrans->name for node $node)");
		cRenderCards::body_start();
			$aMetrics = [];
			$aMetrics[] = [cChart::LABEL=>"server trans Calls:", cChart::METRIC=>cADMetric::transCallsPerMin($oTier->name, $oTrans->name, $node)];
			$aMetrics[] = [cChart::LABEL=>"server trans Response:", cChart::METRIC=>cADMetric::transResponseTimes($oTier->name, $oTrans->name, $node)];
			$aMetrics[] = [cChart::LABEL=>"server trans Errors:", cChart::METRIC=>cADMetric::transErrors($oTier->name, $oTrans->name, $node)];
			$aMetrics[] = [cChart::LABEL=>"server trans cpu used:", cChart::METRIC=>cADMetric::transCpuUsed($oTier->name, $oTrans->name, $node)];
			cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);
		cRenderCards::body_end();
	cRenderCards::card_end();

	cRenderCards::card_start("Server Data");
		cRenderCards::body_start();
			$aMetrics = [];
			$aMetrics[] = [cChart::LABEL=>"Overall CPU Busy:", cChart::METRIC=>cADMetric::InfrastructureCpuBusy($oTier->name, $node)];
			$aMetrics[] = [cChart::LABEL=>"Overall Java Heap Used:", cChart::METRIC=>cADMetric::InfrastructureJavaHeapUsed($oTier->name, $node)];
			$aMetrics[] = [cChart::LABEL=>"Overall Java GC Time:", cChart::METRIC=>cADMetric::InfrastructureJavaGCTime($oTier->name, $node)];
			$aMetrics[] = [cChart::LABEL=>"Overall .Net Heap Used:", cChart::METRIC=>cADMetric::InfrastructureDotnetHeapUsed($oTier->name, $node)];
			$aMetrics[] = [cChart::LABEL=>"Overall .Net GC Time:", cChart::METRIC=>cADMetric::InfrastructureDotnetGCTime($oTier->name, $node)];
			cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);
		cRenderCards::body_end();
	cRenderCards::card_end();
}

// ################################################################################
//TODO make this a widget
cRenderCards::card_start("<a name='4'>Remote</a>Services used by transaction:$oTrans->name in Tier: $oTier->name");
	cRenderCards::body_start();
		//******get the external tiers used by this transaction
		$oData = $oTrans->GET_ExtTiers();
		if ($oData){
			$aMetrics = [];
			foreach ( $oData as $oItem){
				$other = $oItem->name;
				$sClass = cRender::getRowClass();
				
					$aMetrics[] = [cChart::TYPE=>cChart::LABEL, cChart::LABEL=>"<DIV style='max-width:200px;overflow-wrap:break-word'>$other</div>"];
					$sMetricUrl=cADMetric::transExtCalls($oTier->name, $oTrans->name, $other);
					$aMetrics[] = [cChart::LABEL=>"Calls per min to: $other", cChart::METRIC=>$sMetricUrl];
					$sMetricUrl=cADMetric::transExtResponseTimes($oTier->name, $oTrans->name, $other);
					$aMetrics[] = [cChart::LABEL=>"response times: $other", cChart::METRIC=>$sMetricUrl];
			}
			cChart::metrics_table($oApp, $aMetrics, 3, $sClass, cChart::CHART_HEIGHT_SMALL);
		}else
			cCommon::messagebox("This transaction has no external calls");
	cRenderCards::body_end();
cRenderCards::card_end();

// ################################################################################
//TODO make this a widget
$oTimes = cRender::get_times();
$sAppdUrl = cADControllerUI::transaction_snapshots($oApp,$oTrans->id, $oTimes);
$aSnapshots = $oTrans->GET_snapshots($oTimes);

cRenderCards::card_start("<a name='5'>Transaction Snapshots</a>");
	cRenderCards::body_start();
		echo "Showing snapshots taking over ".MIN_TRANS_TIME."ms<br>";
		if (count($aSnapshots) == 0)
			cCommon::messagebox("No Snapshots found");
		else{
			cRender::button("Analyse top ten slowest transactions", "transanalysis.php?$sTransQS", true);
			cRender::button("Search Snapshots", "searchsnaps.php?$sTransQS");
			?><p><table class="maintable" id="trans">
				<thead><tr class="tableheader">
					<th width="140">start time</th>
					<th width="10"></th>
					<th width="80">Duration</th>
					<th>Server</th>
					<th>URL</th>
					<th>Summary</th>
					<th width="80"></th>
				</tr></thead>
				<tbody><?php
					foreach ($aSnapshots as $oSnapshot){
						if ($oSnapshot->timeTakenInMilliSecs < MIN_TRANS_TIME) continue;

						$sOriginalUrl = $oSnapshot->URL;
						if ($sOriginalUrl === "") $sOriginalUrl = $oTrans->name;
						
						$iEpoch = (int) ($oSnapshot->serverStartTime/1000);
						$sDate = date(cCommon::ENGLISH_DATE_FORMAT, $iEpoch);
						$sAppdUrl = cADControllerUI::snapshot($oApp, $oTrans->id, $oSnapshot->requestGUID, $oTimes);
						$sImgUrl = cRender::get_trans_speed_colour($oSnapshot->timeTakenInMilliSecs);
						$sSnapQS = cHttp::build_QS($sTransQS, cRender::SNAP_GUID_QS, $oSnapshot->requestGUID);
						$sSnapQS = cHttp::build_QS($sSnapQS, cRender::SNAP_URL_QS, $sOriginalUrl);
						$sSnapQS = cHttp::build_QS($sSnapQS, cRender::SNAP_TIME_QS, $oSnapshot->serverStartTime);
						
						?><tr class="<?=cRender::getRowClass()?>">
							<td><?=$sDate?></td>
							<td><img src="<?=$home?>/<?=$sImgUrl?>"></td>
							<td align="middle"><?=$oSnapshot->timeTakenInMilliSecs?></td>
							<td><?=cADUtil::get_node_name($oApp,$oSnapshot->applicationComponentNodeId)?></td>
							<td><a href="snapdetails.php?<?=$sSnapQS?>" target="_blank"><?=cCommon::fixed_width_div(200,$sOriginalUrl)?></div></a></td>
							<td><?=cCommon::fixed_width_div(400, $oSnapshot->summary)?></div></td>
							<td><?=cADCommon::button($sAppdUrl, "Go")?></td>
						</tr><?php 
					}
				?></tbody>
			</table>
			<script language="javascript">
				$( function(){ 
					$("#trans").tablesorter({
						headers:{
							3:{ sorter: 'digit' }
						}
					});
				});

			</script><?php
		}
	cRenderCards::body_end();
	cRenderCards::action_start();
		cADCommon::button($sAppdUrl, "Goto Transaction Snapshots");
		cRender::button("Search Snapshots", "searchsnaps.php?$sTransQS");
	cRenderCards::action_end();
cRenderCards::card_end();



// ################################################################################
// ################################################################################
cChart::do_footer();

cRenderHtml::footer();
?>