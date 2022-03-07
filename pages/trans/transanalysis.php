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


//####################################################################
cRenderHtml::header("top 10 slowest Transactions analysis");
cRender::force_login();


//####################################################
//display the results
$oTrans = cRenderObjs::get_current_trans();
$oTier = $oTrans->tier;
$oApp = $oTier->app;

$sTierQS = cRenderQS::get_base_tier_QS($oTier);
$sTransQS = cHttp::build_QS($sTierQS, cRenderQS::TRANS_QS,$oTrans->name);
$sTransQS = cHttp::build_QS($sTransQS, cRenderQS::TRANS_ID_QS,$oTrans->id);

$oTimes = cRender::get_times();

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}

//*********************************************************************
function analyse_snapshot($poSnapshot){
	global $oTable;
	
	$oExtCalls = $poSnapshot->count_ext_calls();
	if ($oExtCalls == null) return null;
	
	$oTable->add_col_data($poSnapshot->guuid, $oExtCalls);
	$oTable->set_col_info($poSnapshot->guuid, $poSnapshot);
}

//#####################################################################
cRenderCards::card_start("top 10 slowest snapshots for :$oTrans->name");
cRenderCards::action_start();
	cRenderMenus::show_tier_functions();
	cADCommon::button(cADControllerUI::transaction($oTrans));
	cRender::button("back to Transaction details", "transdetails.php?$sTransQS");
cRenderCards::action_end();
cRenderCards::card_end();



//#####################################################################
$bProceed = true;
cDebug::vardump($oTrans);
$aTopTen = $oTrans->GET_top_10_snapshots($oTimes);
if (count($aTopTen) == 0){
	cCommon::messagebox("No Snapshots found");
	$bProceed = false;
}

//#####################################################################
if ($bProceed){
	//get the top ten slowest
	cRenderCards::card_start($oTrans->name);
	cRenderCards::body_start();
	?>
	<table class="maintable" id="trans" border="1" cellspacing="0">
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
				$iEpoch = (int) ($oSnapshot->starttime/1000);
				
				$sAppdUrl = cADControllerUI::snapshot($oSnapshot);
				$sImgUrl = cRender::get_trans_speed_colour($oSnapshot->timeTakenInMilliSecs);
				$sSnapQS = cHttp::build_QS($sTransQS, cRenderQS::SNAP_GUID_QS, $oSnapshot->guuid);
				$sSnapQS = cHttp::build_QS($sSnapQS, cRenderQS::SNAP_URL_QS, $oSnapshot->url);
				$sSnapQS = cHttp::build_QS($sSnapQS, cRenderQS::SNAP_TIME_QS, $oSnapshot->starttime);
				
				?>
				<tr>
					<td><?=$oSnapshot->startdate?></td>
					<td><img src="<?=$home?>/<?=$sImgUrl?>"></td>
					<td align="middle"><?=$oSnapshot->timeTakenInMilliSecs?></td>
					<td><?=cADUtil::get_node_name($oApp,$oSnapshot->applicationComponentNodeId)?></td>
					<td><div style="max-width:200px;overflow-wrap:break-word;">
						<a href="snapdetails.php?<?=$sSnapQS?>" target="_blank"><?=$oSnapshot->url?></a>
					</div></td>
					<td><?=cCommon::fixed_width_div(600, $oSnapshot->summary)?></div></td>
					<td><?=cADCommon::button($sAppdUrl, "Go")?></td>
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
	cRenderCards::body_end();
	cRenderCards::card_end();
}

//#####################################################################
//TBD make this asynchronous
if ($bProceed){
	cRenderCards::card_start("Analysis of Transactions External Calls");
	cRenderCards::body_start();
	?>
	<div ID="progress"><?php
		$oTable = new c2DArray;
		foreach ($aTopTen as $oSnapshot){
			echo "analysing Snaphot $oSnapshot->url with $oSnapshot->timeTakenInMilliSecs ms elapsed... ";
			cDebug::flush();

			analyse_snapshot($oSnapshot);

			echo "Done<br>";
			cDebug::flush();
		}
		echo "Analysis Complete<p>";
	?></div><?php
	if ($oTable->length() == 0)
		cCommon::messagebox("no data found to analyse");
	else{
		?><script language="javascript">$(function(){ $("#progress").empty()});</script>
	
		<table border="1" cellpadding="2" cellspacing="0">
			<thead><tr>
				<td>url</td>
				<td>start time</td>
				<td><?=cCommon::fixed_width_div(50,"total elapsed time")?></td><?php
				$aExtCalls = $oTable->rowNames();
				foreach ($aExtCalls as $sExtName){	?><th><?=cCommon::fixed_width_div(100,$sExtName)?></th><?php	}
			?></tr></thead><?php
				
			$aSnaphots = $oTable->colNames();
			foreach ($aSnaphots as $sSnapshot){
				?><tr><?php
					$iEpoch = (int) ($oSnapshot->starttime/1000);
					$sDate = date(cCommon::ENGLISH_DATE_FORMAT, $iEpoch);
					
					

					$oSnapshot = $oTable->get_col_info($sSnapshot);
					$sSnapQS = cHttp::build_QS($sTransQS, cRenderQS::SNAP_GUID_QS, $oSnapshot->guuid);
					$sSnapQS = cHttp::build_QS($sSnapQS, cRenderQS::SNAP_URL_QS, $oSnapshot->url);
					$sSnapQS = cHttp::build_QS($sSnapQS, cRenderQS::SNAP_TIME_QS, $oSnapshot->starttime);
					?><td><div style="max-width:200px;overflow-wrap:break-word;">
						<a href="snapdetails.php?<?=$sSnapQS?>" target="_blank"><?=$sOriginalUrl?></a>
					</div></td>
					<td><?=$sDate?></td>
					<td><?=$oSnapshot->timeTakenInMilliSecs?>ms</td><?php
					
					foreach ($aExtCalls as $sExtName){
						$oData = $oTable->get($sExtName, $sSnapshot);
						?><td align="middle"><?php
							if ($oData !== null) echo $oData->count;
						?></td><?php
					}
				?></tr><?php
			}
		?></table><?php
	}
	cRenderCards::body_end();
	cRenderCards::card_end();
		
}


// ################################################################################

cRenderHtml::footer();
?>