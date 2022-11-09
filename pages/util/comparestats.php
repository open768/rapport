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
$sBaseMetric = cHeader::get(cRenderQS::METRIC_QS);
$sCaption = cHeader::get(cRenderQS::TITLE_QS);
$sAppQS = cRenderQS::get_base_app_QS($oApp);


//####################################################################
cRenderHtml::$load_google_charts = true;
cRenderHtml::header("compare statistics: $sCaption");
cRender::force_login();


//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}


?>
<LINK rel="stylesheet" type="text/css" href="<?=$js_extra?>/jquery-datetimepicker/jquery.datetimepicker.min.css" >
<script src="<?=$jsWidgets?>/comparestats.js"></script>
<script src="<?=$js_extra?>/jquery-datetimepicker/jquery.datetimepicker.min.js"></script>
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

$sLastYr = cHeader::get(cRenderQS::LAST_YEAR_QS);
$oLastYear = clone $oToday;
if ($sLastYr){
	$iTime = strtotime($sLastYr);
	$oLastYear->start = $iTime *1000;
	$oLastYear->set_duration(cHeader::get(cRenderQS::TIME_DURATION_QS));
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
				<input type="hidden" name="<?=cRenderQS::APP_ID_QS?>" value="<?=$oApp->id?>">
				<input type="hidden" name="<?=cRenderQS::METRIC_QS?>" value="<?=$sBaseMetric?>">
				<input type="hidden" name="<?=cRenderQS::TITLE_QS?>" value="<?=$sCaption?>">
				start date:	<input type="text" size="16" id="startdate" name="<?=cRenderQS::TIME_START_QS?>" value="<?=$sStart?>"> -
				last year date:	<input type="text" size="16" id="lastyeardate" name="<?=cRenderQS::LAST_YEAR_QS?>" value="<?=$sLastYr?>"> -
				Duration in minutes: <input type="text" size="5" name="<?=cRenderQS::TIME_DURATION_QS?>" value="<?=$oToday->duration?>"> 
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
				<?=cRenderQS::LABEL_QS?>="<?=$psLabel?>"
				<?=cRenderQS::HOME_QS?>="<?=$home?>"
				<?=cRenderQS::APP_ID_QS?>="<?=$oApp->id?>"
				<?=cRenderQS::METRIC_QS?>="<?=$sBaseMetric?>"
				<?=cRenderQS::TIME_START_QS?>="<?=$poTimes->start?>"
				<?=cRenderQS::TIME_END_QS?>="<?=$poTimes->end?>">
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
		$sUrl = cHttp::build_url($sUrl,cRenderQS::METRIC_QS, cHeader::get(cRenderQS::METRIC_QS));
		$sUrl = cHttp::build_url($sUrl,cRenderQS::TITLE_QS, cHeader::get(cRenderQS::TITLE_QS));
		$sUrl = cHttp::build_url($sUrl,cRenderQS::TIME_START_QS, cHeader::get(cRenderQS::TIME_START_QS));
		$sUrl = cHttp::build_url($sUrl,cRenderQS::LAST_YEAR_QS, cHeader::get(cRenderQS::LAST_YEAR_QS));
		$sUrl = cHttp::build_url($sUrl,cRenderQS::TIME_DURATION_QS, cHeader::get(cRenderQS::TIME_DURATION_QS));
		cRender::button("Show stats for the day", $sUrl);
	cRenderCards::action_end();
cRenderCards::card_end();


	?><script>
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
