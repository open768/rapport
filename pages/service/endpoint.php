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
require_once("$root/inc/filter.php");

//####################################################################
cRenderHtml::header("Service End Point");
cRender::force_login();
cChart::do_header();

//####################################################
//display the results
$sService = cHeader::get( cRender::SERVICE_QS);
$sServiceID = cHeader::get( cRender::SERVICE_ID_QS);
$oTier = cRenderObjs::get_current_tier();
$oApp = $oTier->app;

$sAppQS = cRenderQS::get_base_app_QS($oApp);
$sTierQS = cRenderQS::get_base_tier_QS($oTier);

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}

$oCred = cRenderObjs::get_AD_credentials();
cRenderMenus::show_tier_functions();
cADCommon::button(cADControllerUI::serviceEndPoint($oTier,$sServiceID));
cDebug::flush();

?>
<H2>Service End Point <?=cRender::show_name(cRender::NAME_OTHER,$sService)?></h2>
<?php
	$aMetrics = [];
	$aMetrics[] = [cChart::LABEL=>"Calls", cChart::METRIC=>cADMetric::endPointCallsPerMin($oTier->name, $sService)];
	$aMetrics[] = [cChart::LABEL=>"Response", cChart::METRIC=>cADMetric::endPointResponseTimes($oTier->name, $sService)];
	$aMetrics[] = [cChart::LABEL=>"Errors", cChart::METRIC=>cADMetric::endPointErrorsPerMin($oTier->name, $sService), cChart::HIDEIFNODATA=>true];
	cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX);

	
// ################################################################################
?><h2>Snapshots</h2><?php
$oTimes = cRender::get_times();
$oData = cADRestUI::GET_Service_end_point_snapshots($oTier, $sServiceID, $oTimes);
$aSnapshots = $oData->requestSegmentDataListItems;
if (count($aSnapshots) == 0){
	?><div class="maintable">No Snapshots found</div><?php
}else{
	?>
		<table class="maintable" id="trans">
			<thead><tr class="tableheader">
				<th width="140">start time</th>
				<th width="10"></th>
				<th width="50">Experience</th>
				<th width="80">Duration</th>
				<th>Server</th>
				<th>URL</th>
				<th>Summary</th>
				<th width="80"></th>
			</tr></thead>
			<tbody><?php
				foreach ($aSnapshots as $oSnapshot){
					$sOriginalUrl = $oSnapshot->url;
					if ($sOriginalUrl === "") $sOriginalUrl = $sService;
					
					$iEpoch = (int) ($oSnapshot->serverStartTime/1000);
					$sDate = date(cCommon::ENGLISH_DATE_FORMAT, $iEpoch);
					$sAppdUrl = cADControllerUI::snapshot($oApp, $oSnapshot->businessTransactionId, $oSnapshot->requestGUID, $oTimes);
					$sImgUrl = cRender::get_trans_speed_colour($oSnapshot->timeTakenInMilliSecs);
					$sTransQS = cHttp::build_QS($sTierQS, cRender::TRANS_QS, $oSnapshot->businessTransactionName);
					$sTransQS = cHttp::build_QS($sTransQS, cRender::TRANS_ID_QS, $oSnapshot->businessTransactionId);
					$sSnapQS = cHttp::build_QS($sTransQS, cRender::SNAP_GUID_QS, $oSnapshot->requestGUID);
					$sSnapQS = cHttp::build_QS($sSnapQS, cRender::SNAP_URL_QS, $sOriginalUrl);
					$sSnapQS = cHttp::build_QS($sSnapQS, cRender::SNAP_TIME_QS, $oSnapshot->serverStartTime);
					
					?>
					<tr class="<?=cRender::getRowClass()?>">
						<td><?=$sDate?></td>
						<td><img src="<?=$home?>/<?=$sImgUrl?>"></td>
						<td><?=$oSnapshot->userExperience?></td>
						<td align="middle"><?=$oSnapshot->timeTakenInMilliSecs?></td>
						<td><?=cADUtil::get_node_name($oApp,$oSnapshot->applicationComponentNodeId)?></td>
						<td><a href="<?=$home?>/pages/trans/snapdetails.php?<?=$sSnapQS?>" target="_blank"><div style="max-width:200px;overflow-wrap:break-word;"><?=$sOriginalUrl?></div></a></td>
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
}


// ################################################################################
?><?php

// ################################################################################
cChart::do_footer();

cRenderHtml::footer();
?>