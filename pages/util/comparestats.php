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


//get passed in details
$oApp = cRenderObjs::get_current_app();
$sBaseMetric = cHeader::get(cRender::METRIC_QS);
$sCaption = cHeader::get(cRender::TITLE_QS);
$sAppQS = cRenderQS::get_base_app_QS($oApp);


//####################################################################
cRenderHtml::header("compare statistics: $sCaption");
cRender::force_login();


//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}


?>
<LINK rel="stylesheet" type="text/css" href="<?=$jsinc?>/jquery-datetimepicker/jquery.datetimepicker.min.css" >
<script language="javascript" src="<?=$home?>/js/widgets/comparestats.js"></script>
<script language="javascript" src="<?=$jsinc?>/jquery-datetimepicker/jquery.datetimepicker.min.js"></script>
<?php

//********************************************************************
function show_dates($psCaption, $poDate){
	echo "$psCaption: ".$poDate->toString()."<br>";
}


//********************************************************************

//####################################################################
$oCred = cRenderObjs::get_AD_credentials();
try{
$oToday = cRender::get_times();
}
catch (Exception $e){
	cCommon::errorbox("unable to read date:<p>".$e->getMessage());
	cRenderHtml::footer();
	exit;	
}


$oYesterday = clone $oToday;
$oYesterday->start = $oYesterday->start - (3600*24*1000);
$oYesterday->end = $oYesterday->end - (3600*24*1000);

$oLastWeek = clone $oToday;
$oLastWeek->start = $oLastWeek->start - (3600*24*7*1000);
$oLastWeek->end = $oLastWeek->end - (3600*24*7*1000);

$sLastYr = cHeader::get(cRender::LAST_YEAR_QS);
$oLastYear = clone $oToday;
if ($sLastYr){
	$iTime = strtotime($sLastYr);
	$oLastYear->start = $iTime *1000;
	$oLastYear->set_duration(cHeader::get(cRender::TIME_DURATION_QS));
}else{
	$oLastYear->start = $oLastYear->start - (3600*24*365*1000);
	$oLastYear->end = $oLastYear->end - (3600*24*365*1000);
}


//####################################################################
cRenderCards::card_start("Statistics: $sCaption");
	cRenderCards::body_start();
		$sStart = $oToday->start_time()->format(cCommon::PHP_UK_DATE_FORMAT);
		$sLastYr = $oLastYear->start_time()->format(cCommon::PHP_UK_DATE_FORMAT);
		?>
			<form method="GET">
				<input type="hidden" name="<?=cRender::APP_ID_QS?>" value="<?=$oApp->id?>">
				<input type="hidden" name="<?=cRender::METRIC_QS?>" value="<?=$sBaseMetric?>">
				<input type="hidden" name="<?=cRender::TITLE_QS?>" value="<?=$sCaption?>">
				start date:	<input type="text" size="16" id="startdate" name="<?=cRender::TIME_START_QS?>" value="<?=$sStart?>"> -
				last year date:	<input type="text" size="16" id="lastyeardate" name="<?=cRender::LAST_YEAR_QS?>" value="<?=$sLastYr?>"> -
				Duration in minutes: <input type="text" size="5" name="<?=cRender::TIME_DURATION_QS?>" value="<?=$oToday->duration?>"> 
				<input type="submit">
			</form>
			<script>
				function init_datepicker(){
					$("#startdate").datetimepicker({format:'d-m-Y H:i',step:15});
					var iYear = new Date().getFullYear()
					$("#lastyeardate").datetimepicker({format:'d-m-Y H:i',step:15,yearStart:iYear-1, yearEnd:iYear});;
				}
				$(init_datepicker);
			</script>
		<?php
	cRenderCards::body_end();
	cRenderCards::action_start();
		cRenderMenus::show_app_functions($oApp);
	cRenderCards::action_end();
cRenderCards::card_end();

//####################################################################

function add_widget($psLabel, $poTimes){
	global $oApp, $home, $sBaseMetric;
	?>
			<tr 
				type="widget" 
				<?=cRender::LABEL_QS?>="<?=$psLabel?>"
				<?=cRender::HOME_QS?>="<?=$home?>"
				<?=cRender::APP_ID_QS?>="<?=$oApp->id?>"
				<?=cRender::METRIC_QS?>="<?=$sBaseMetric?>"
				<?=cRender::TIME_START_QS?>="<?=$poTimes->start?>"
				<?=cRender::TIME_END_QS?>="<?=$poTimes->end?>">
					<td>Please Wait</td> 
			</tr>
	<?php
}

cRenderCards::card_start("statistics");
	cRenderCards::body_start();
		echo "<table border='1' class='maintable' cellpadding='5' cellspacing='0'>";
		echo "<tr>";
			echo "<th width='150'>Label</th>";
			echo "<th width='100'>Start date</th>";
			echo "<th width='100'>Total Calls</th>";
			echo "<th width='100'>Calls Per Minute</th>";
			echo "<th width='100'>Average Response Times</th>";
			echo "<th width='100'>Max Response Times</th>";
			echo "<th width='100'>errors</th>";
		echo "</tr>";
		add_widget("Today", $oToday);
		add_widget("Yesterday", $oYesterday);
		add_widget("last week", $oLastWeek);
		add_widget("last year", $oLastYear);
		echo "</table	>";
	cRenderCards::body_end();
	cRenderCards::action_start();
		$sUrl = cHttp::build_url("daystats.php",$sAppQS);
		$sUrl = cHttp::build_url($sUrl,cRender::METRIC_QS, cHeader::get(cRender::METRIC_QS));
		$sUrl = cHttp::build_url($sUrl,cRender::TITLE_QS, cHeader::get(cRender::TITLE_QS));
		$sUrl = cHttp::build_url($sUrl,cRender::TIME_START_QS, cHeader::get(cRender::TIME_START_QS));
		$sUrl = cHttp::build_url($sUrl,cRender::LAST_YEAR_QS, cHeader::get(cRender::LAST_YEAR_QS));
		$sUrl = cHttp::build_url($sUrl,cRender::TIME_DURATION_QS, cHeader::get(cRender::TIME_DURATION_QS));
		cRender::button("Show stats for the day", $sUrl);
	cRenderCards::action_end();
cRenderCards::card_end();


	?><script language="javascript">
		function init_widget(piIndex, poElement){
			$(poElement).adcomparestats();
		}
		
		function init_widgets(){
			$("tr[type='widget']").each(init_widget);
		}
		
		$( init_widgets);
	</script><?php


//####################################################################
cRenderHtml::footer();
?>
