<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2018 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

//####################################################################
//####################################################################
$home = "../..";
$root=realpath($home);
$phpinc = realpath("$root/../phpinc");
$jsinc = "$home/../jsinc";

require_once("$phpinc/ckinc/debug.php");
require_once("$phpinc/ckinc/session.php");
require_once("$phpinc/ckinc/common.php");
require_once("$phpinc/ckinc/http.php");
require_once("$phpinc/ckinc/header.php");
require_once("$phpinc/ckinc/array.php");

cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();
const HOWMANY=10;

//####################################################################
require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$phpinc/appdynamics/common.php");
require_once("$root/inc/inc-charts.php");
require_once("$root/inc/inc-secret.php");
require_once("$root/inc/inc-render.php");
require_once("$root/inc/inc-filter.php");

const COLUMNS=6;
const FLOW_ID = "trflw";
const MIN_TRANS_TIME=150;

//####################################################################
cRender::html_header("top ".HOWMANY." slowest Transactions analysis");
cRender::force_login();
cChart::do_header();

//####################################################
//display the results
$oApp = cRenderObjs::get_current_app();
$tier = cHeader::get(cRender::TIER_QS);
$tid = cHeader::get(cRender::TIER_ID_QS);
$trans = cHeader::get(cRender::TRANS_QS);
$trid = cHeader::get(cRender::TRANS_ID_QS);

$sAppQS = cRender::get_base_app_QS();
$sTierQS = cRender::get_base_tier_QS();
$sTransQS = cHttp::build_QS($sTierQS, cRender::TRANS_QS,$trans);
$sTransQS = cHttp::build_QS($sTransQS, cRender::TRANS_ID_QS,$trid);

$sFilterTierQS = cFilter::makeTierFilter($tier);
$sFilterTierQS = cHttp::build_QS($sAppQS, $sFilterTierQS);
$oTimes = cRender::get_times();


cRender::show_time_options("$oApp->name&gt;$oApp->name&gt;$tier&gt;$trans"); 
//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRender::html_footer();
	exit;
}
//********************************************************************
cRenderMenus::show_tier_functions();
cRender::appdButton(cAppDynControllerUI::transaction($oApp,$trid));
cRender::button("back to Transaction details", "transdetails.php?$sTransQS");
cDebug::flush();

$oTable = new c2DArray;
//#####################################################################
function sort_time($a, $b){
	return (($a->timeTakenInMilliSecs<$b->timeTakenInMilliSecs?1:-1));
}

//*********************************************************************
function analyse_snapshot($poSnapshot){
	global $oTable;
	
	$aExtCalls = cAppDynUtil::count_snapshot_ext_calls($poSnapshot);
	if ($aExtCalls == null) return null;
	
	$oTable->add_col_data($poSnapshot->requestGUID, $aExtCalls);
	$oTable->set_col_info($poSnapshot->requestGUID, $poSnapshot);
}

//#####################################################################
?>
<h2><a name="5">Top <?=HOWMANY?> slowest Transaction Snapshots</a></h2>
<?php
$bProceed = true;
$aSnapshots = cAppdyn::GET_snaphot_info($oApp->name, $trid, $oTimes);
if (count($aSnapshots) == 0){
	?><div class="maintable">No Snapshots found</div><?php
	$bProceed = false;
}

//#####################################################################
if ($bProceed){
	//get the top ten slowest
	uasort($aSnapshots , "sort_time");	
	$aTopTen = [];
	foreach ($aSnapshots as $oSnapshot){
		if (count($aTopTen) >=HOWMANY) break;				
		$aTopTen[] = $oSnapshot;
	}
	?>
	<table class="maintable" id="trans">
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
			foreach ($aTopTen as $oSnapshot){
				$iEpoch = (int) ($oSnapshot->serverStartTime/1000);
				$sDate = date(cCommon::ENGLISH_DATE_FORMAT, $iEpoch);
				$sAppdUrl = cAppDynControllerUI::snapshot($oApp, $trid, $oSnapshot->requestGUID, $oTimes);
				$sImgUrl = cRender::get_trans_speed_colour($oSnapshot->timeTakenInMilliSecs);
				$sSnapQS = cHttp::build_QS($sTransQS, cRender::SNAP_GUID_QS, $oSnapshot->requestGUID);
				$sSnapQS = cHttp::build_QS($sSnapQS, cRender::SNAP_URL_QS, $oSnapshot->URL);
				$sSnapQS = cHttp::build_QS($sSnapQS, cRender::SNAP_TIME_QS, $oSnapshot->serverStartTime);
				
				?>
				<tr class="<?=cRender::getRowClass()?>">
					<td><?=$sDate?></td>
					<td><img src="<?=$home?>/<?=$sImgUrl?>"></td>
					<td align="middle"><?=$oSnapshot->timeTakenInMilliSecs?></td>
					<td><?=cAppdynUtil::get_node_name($oApp->id,$oSnapshot->applicationComponentNodeId)?></td>
					<td><div style="max-width:200px;overflow-wrap:break-word;">
						<a href="snapdetails.php?<?=$sSnapQS?>" target="_blank"><?=$oSnapshot->URL?></a>
					</div></td>
					<td><?=cCommon::fixed_width_div(600, $oSnapshot->summary)?></div></td>
					<td><?=cRender::appdButton($sAppdUrl, "Go")?></td>
				</tr>
			<?php }
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

	</script>
	<?php
}

//#####################################################################
if ($bProceed){
	?>
	<h2>Analysis of Transactions External Calls</h2>
	<div ID="progress"><?php
		$i=0;
		foreach ($aTopTen as $oSnapshot){
			echo "analysing Snaphot $oSnapshot->URL with $oSnapshot->timeTakenInMilliSecs ms elapsed... ";
			cDebug::flush();

			analyse_snapshot($oSnapshot);

			echo "Done<br>";
			cDebug::flush();
		}
		echo "Analysis Complete";
		//cDebug::on(true);
	?></div>
	<script language="javascript">$(function(){ $("#progress").hide()});</script>
	
	<table border="1" cellpadding="2" cellspacing="0">
		<tr>
			<td></td><?php
			$aCols = $oTable->colNames();
			foreach ($aCols as $sCol){
				$iEpoch = (int) ($oSnapshot->serverStartTime/1000);
				$sDate = date(cCommon::ENGLISH_DATE_FORMAT, $iEpoch);
				$oSnapshot = $oTable->get_col_info($sCol);
				$sSnapQS = cHttp::build_QS($sTransQS, cRender::SNAP_GUID_QS, $oSnapshot->requestGUID);
				$sSnapQS = cHttp::build_QS($sSnapQS, cRender::SNAP_URL_QS, $oSnapshot->URL);
				$sSnapQS = cHttp::build_QS($sSnapQS, cRender::SNAP_TIME_QS, $oSnapshot->serverStartTime);
				?><td>
					<div style="max-width:150px;overflow-wrap:break-word;"><b>
						<a href="snapdetails.php?<?=$sSnapQS?>" target="_blank"><?=$oSnapshot->URL?></a>
					</b></div>
					<?=$sDate?><br>
					<b><?=$oSnapshot->timeTakenInMilliSecs?>ms</b>
				</td><?php
			}
		?></tr><?php
			
		$aRows = $oTable->rowNames();
		foreach ($aRows as $sRow){
			?><tr>
				<th><?=$sRow?></th><?php
					foreach ($aCols as $sCol){
						$oData = $oTable->get($sRow, $sCol);
						?><td><?php
							if ($oData !== null) echo $oData->count;
						?></td><?php
					}
				?>
			</tr><?php
		}
	?></table><?php
		
}


// ################################################################################
// ################################################################################
cChart::do_footer();

cRender::html_footer();
?>