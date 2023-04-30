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
cRenderHtml::$load_google_charts = true;
cRenderHtml::header("Transactions");
cRender::force_login();
?>
	<script src="<?=$home?>/js/remote.js"></script>	
	<script src="<?=$home?>/js/transflow.js"></script>
<?php
cChart::do_header();

//####################################################
//display the results
/** @var cADTrans */	$oTrans = cRenderObjs::get_current_trans();
/** @var cADTier */	$oTier = $oTrans->tier;
/** @var cADApp */	$oApp = $oTier->app;

$node= cHeader::get(cRenderQS::NODE_QS);
$sExtraCaption = ($node?"($node) node":"");

$sAppQS = cRenderQS::get_base_app_QS($oApp);
$sTierQS = cRenderQS::get_base_tier_QS($oTier);

$sTransQS = cHttp::build_QS($sTierQS, cRenderQS::TRANS_QS,$oTrans->name);
$sTransQS = cHttp::build_QS($sTransQS, cRenderQS::TRANS_ID_QS,$oTrans->id);


//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************
$oCred = cRenderObjs::get_AD_credentials();
$aNodes = $oTier->GET_Nodes();

function show_nodes($psNode){
	global $oNodes, $sTransQS, $aNodes, $oCred;
	
	if ($oCred->restricted_login == null){?>
		<select id="showMenu">
			<optgroup label="Nodes">
				<?php
					if ($psNode){
						?><option value="transdetails.php?<?=$sTransQS?>">All servers for this transaction</option><?php
						$sNodeQs = cHttp::build_QS($sTransQS, cRenderQS::NODE_QS, $psNode);
						?><option value="tiertransgraph.php?<?=$sNodeQs?>">
							(<?=($psNode)?>) server
						</option><?php 
					}
				?>
			</optgroup>
			<optgroup label="Nodes">
			<?php
				foreach ($aNodes as $oNode){
					$sDisabled = ($oNode->name==$psNode?"disabled":"");
					$sNodeQs = cHttp::build_QS($sTransQS, cRenderQS::NODE_QS, $oNode->name);
					$sUrl = "transdetails.php?$sNodeQs";
					?>
						<option <?=$sDisabled?> value="<?=$sUrl?>"><?=$oNode->name?></option>
					<?php
				}
			?>
			</optgroup>
		</select>
		<script>
		$(  
			function(){
				$("#showMenu").selectmenu({change:common_onListChange});
			}  
		);
		</script><?php
	}
}

//#####################################################################################################
cRenderCards::card_start("Transaction details for $oTrans->name");
	cRenderCards::body_start();
	?><ul>
		<li><a href="#1">Data for <?=$oTrans->name?> in <?=$oTier->name?></a>
		<li><a href="#2">Transaction Map</a>
		<?php 
			if ($node){ ?>
				<li><a href="#3">Details for node: <?=$node?></a>
			<?php }
		?>
		<li><a href="#4">Remote Services</a>
		<li><a href="#5">Transaction Snapshots</a>
	</ul><?php
	cRenderCards::body_end();
	cRenderCards::action_start();
		cADCommon::button(cADControllerUI::transaction($oTrans));
		cRenderMenus::show_tier_functions();
		cRender::button("Transaction details for all nodes", "transallnodes.php?$sTransQS");
		cRender::button("Search Snapshots", "searchsnaps.php?$sTransQS");
		$sBaseMetric = cADMetricPaths::Transaction($oTier->name, $oTrans->name);
		$sUrl = cHttp::build_url("../util/comparestats.php",$sAppQS);
		$sUrl = cHttp::build_url($sUrl,cRenderQS::METRIC_QS, $sBaseMetric );
		$sUrl = cHttp::build_url($sUrl,cRenderQS::TITLE_QS, "Transaction: $oApp->name - $oTier->name - $oTrans->name" );
		cRender::button("compare statistics", $sUrl,true);
		echo "<hr>";
		show_nodes($node);
	cRenderCards::action_end();
cRenderCards::card_end();


//#####################################################################################################
cRenderCards::card_start("<a name='1'>Transation: </a> '$oTrans->name',  Tier: '$oTier->name'");
	cRenderCards::body_start();
		$aMetrics = [];
		$aMetrics[] = [cChart::LABEL=>"trans Calls:", cChart::METRIC=>cADMetricPaths::transCallsPerMin($oTrans)];
		$aMetrics[] = [cChart::LABEL=>"trans Response:", cChart::METRIC=>cADMetricPaths::transResponseTimes($oTrans)];
		$aMetrics[] = [cChart::LABEL=>"trans errors:", cChart::METRIC=>cADMetricPaths::transErrors($oTrans)];
		$aMetrics[] = [cChart::LABEL=>"trans cpu used:", cChart::METRIC=>cADMetricPaths::transCpuUsed($oTrans)];
		cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);
	cRenderCards::body_end();
cRenderCards::card_end();

//#####################################################################################################
//TBD make this a widget
cRenderCards::card_start("<a name='2'>map for transaction: '$oTrans->name'</a>");
	cRenderCards::body_start();
	
	?><div class="transactionflow" id="<?=FLOW_ID?>">
		Please wait...
	</div>
	<script>
		function load_trans_flow(){
			var oLoader = new cTransFlow("<?=FLOW_ID?>");
			oLoader.home="<?=$home?>";
			oLoader.APP_QS="<?=cRenderQS::APP_QS?>";
			oLoader.TIER_QS="<?=cRenderQS::TIER_QS?>";
			oLoader.TRANS_QS="<?=cRenderQS::TRANS_QS?>";
			oLoader.load("<?=$oApp->name?>", "<?=$oTier->name?>", "<?=$oTrans->name?>");
		}
		$(load_trans_flow);	
	</script><?php
	cRenderCards::body_end();
cRenderCards::card_end();

// ################################################################################
if ($node){ 
	cRenderCards::card_start("<a name='3'>Transaction: '$oTrans->name'</a>, node: '$node'");
		cRenderCards::body_start();
			$aMetrics = [];
			$aMetrics[] = [cChart::LABEL=>"server trans Calls:", cChart::METRIC=>cADMetricPaths::transCallsPerMin($oTrans, $node)];
			$aMetrics[] = [cChart::LABEL=>"server trans Response:", cChart::METRIC=>cADMetricPaths::transResponseTimes($oTrans, $node)];
			$aMetrics[] = [cChart::LABEL=>"server trans Errors:", cChart::METRIC=>cADMetricPaths::transErrors($oTrans, $node)];
			$aMetrics[] = [cChart::LABEL=>"server trans cpu used:", cChart::METRIC=>cADMetricPaths::transCpuUsed($oTrans, $node)];
			cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);
		cRenderCards::body_end();
	cRenderCards::card_end();

	cRenderCards::card_start("Server Data");
		cRenderCards::body_start();
			$aMetrics = [];
			$aMetrics[] = [cChart::LABEL=>"Overall CPU Busy:", cChart::METRIC=>cADInfraMetric::InfrastructureCpuBusy($oTier->name, $node)];
			$aMetrics[] = [cChart::LABEL=>"Overall Java Heap Used:", cChart::METRIC=>cADInfraMetric::InfrastructureJavaHeapUsed($oTier->name, $node)];
			$aMetrics[] = [cChart::LABEL=>"Overall Java GC Time:", cChart::METRIC=>cADInfraMetric::InfrastructureJavaGCTime($oTier->name, $node)];
			$aMetrics[] = [cChart::LABEL=>"Overall .Net Heap Used:", cChart::METRIC=>cADInfraMetric::InfrastructureDotnetHeapUsed($oTier->name, $node)];
			$aMetrics[] = [cChart::LABEL=>"Overall .Net GC Time:", cChart::METRIC=>cADInfraMetric::InfrastructureDotnetGCTime($oTier->name, $node)];
			cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);
		cRenderCards::body_end();
	cRenderCards::card_end();
}

// ################################################################################
//TBD make this a widget
cRenderCards::card_start("<a name='4'>Remote Services</a> used by transaction:'$oTrans->name' in Tier: '$oTier->name'");
	cRenderCards::body_start();
		//******get the external tiers used by this transaction
		$oData = $oTrans->GET_ExtTiers();
		if ($oData){
			$aMetrics = [];
			foreach ( $oData as $oItem){
				$sExt = $oItem->name;
				$sClass = cRender::getRowClass();
				
					$aMetrics[] = [cChart::TYPE=>cChart::LABEL, cChart::LABEL=>"<DIV style='max-width:200px;overflow-wrap:break-word'>$sExt</div>"];
					$sMetricUrl=cADMetricPaths::transExtCalls($oTrans, $sExt);
					$aMetrics[] = [cChart::LABEL=>"Calls per min to: $sExt", cChart::METRIC=>$sMetricUrl];
					$sMetricUrl=cADMetricPaths::transExtResponseTimes($oTrans, $sExt);
					$aMetrics[] = [cChart::LABEL=>"response times: $sExt", cChart::METRIC=>$sMetricUrl];
			}
			cChart::metrics_table($oApp, $aMetrics, 3, $sClass, cChart::CHART_HEIGHT_SMALL);
		}else
			cCommon::messagebox("This transaction has no external calls");
	cRenderCards::body_end();
cRenderCards::card_end();

// ################################################################################
//TBD make this a widget
$oTimes = cRender::get_times();
$sAppdUrl = cADControllerUI::transaction_snapshots($oTrans, $oTimes);
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

						$sOriginalUrl = $oSnapshot->url;
						if ($sOriginalUrl === "") $sOriginalUrl = $oTrans->name;
						
						$iEpoch = (int) ($oSnapshot->starttime/1000);
						$sDate = date(cCommon::ENGLISH_DATE_FORMAT, $iEpoch);
						$sAppdUrl = cADControllerUI::snapshot( $oSnapshot);
						$sImgUrl = cRender::get_trans_speed_colour($oSnapshot->timeTakenInMilliSecs);
						$sSnapQS = cHttp::build_QS($sTransQS, cRenderQS::SNAP_GUID_QS, $oSnapshot->guuid);
						$sSnapQS = cHttp::build_QS($sSnapQS, cRenderQS::SNAP_URL_QS, $sOriginalUrl);
						$sSnapQS = cHttp::build_QS($sSnapQS, cRenderQS::SNAP_TIME_QS, $oSnapshot->starttime);
						
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
			<script>
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