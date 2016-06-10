<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2016 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/
require_once("$phpinc/appdynamics/common.php");

//array of charts to display

class cChart{
	public static $width=1000;
	public static $json_data_fn = null;
	public static $json_callback_fn = null;
	public static $csv_url = null;
	public static $zoom_url = null;
	public static $compare_url = null;
	public static $save_fn = null;
	public static $metric_qs = null;
	public static $title_qs = null;
	public static $app_qs = null;

	//**************************************************************************
	public static $showZoom = true;
	public static $showCSV = true;
	public static $showSave = true;
	public static $showCompare = true;
	public static $showShortNoData = false;
	
	const KEY__METRIC = "m";
	const KEY__APP = "a";
	const KEY__CHART = "c";
	const KEY__PREVIOUS = "p";
	const KEY__CAPTION = "n`";
	
	private static $chart_items = array();
	
	//****************************************************************************
	//move this into inc-render
	public static function add( $psCaption, $psMetric, $psApp, $piHeight=250, $pbPreviousPeriod=false){ 


		if (!self::$metric_qs) cDebug::error("metric_qs class property not set");
		if (!self::$title_qs) cDebug::error("title_qs  class property not set");
		if (!self::$app_qs) cDebug::error("app_qs class property not set");
		if (!self::$csv_url) cDebug::error("csv url class property not set");
		if (self::$showZoom && !self::$zoom_url) cDebug::error("zoom url not set");
		if (self::$showSave && !self::$save_fn) cDebug::error("save function not set");
		if (self::$showCompare && !self::$compare_url) cDebug::error("compare url not set");
		
		cDebug::write("adding chart $psMetric");
		
		$iCount = count( self::$chart_items);
		$sDivID = "chart".$iCount;
		$sCSVUrl = self::$csv_url."?".self::$metric_qs."=$psMetric&".self::$app_qs."=$psApp&csv=1";
		$sZoomUrl = self::$zoom_url."?".self::$metric_qs."=$psMetric&".self::$app_qs."=$psApp&".self::$title_qs."=$psCaption";
		$sCompareUrl = self::$compare_url."?".self::$metric_qs."=$psMetric&".self::$app_qs."=$psApp&".self::$title_qs."=$psCaption";
		self::$chart_items[] = [self::KEY__METRIC=>$psMetric, self::KEY__APP=>$psApp, self::KEY__CHART=>$sDivID, self::KEY__PREVIOUS=>$pbPreviousPeriod, self::KEY__CAPTION=>$psCaption];
		
		
		?>
			<table border=0 width=100%><tr>
				<td><div id="<?=$sDivID?>" class="chartdiv" style="width:<?=self::$width?>px;height:<?=$piHeight?>px">
					Please wait ...
				</div></td>
				<td width="200" align="right" id="<?=$sDivID?>numbers" style="display:none">
					<nobr>Max: <span id="<?=$sDivID?>max">?</nobr>
					<p>
					<b>Observed</b><br>
					<nobr>Max: <span id="<?=$sDivID?>maxo">?</nobr><br>
					<nobr>Avg: <span id="<?=$sDivID?>avgo">?</nobr>
				</td>
			</table>
			
			<div id="<?=$sDivID?>buttons" style="display: none;">
			<?php
				if (self::$showCSV && !$pbPreviousPeriod){ ?>
					<button target="new" class="csv_button" onclick="window.open('<?=$sCSVUrl?>');return false;">CSV</button>
			<?php }
				if (self::$showZoom && !$pbPreviousPeriod){ ?>
					<button target="new" class="csv_button" onclick="window.open('<?=$sZoomUrl?>');return false;">Zoom</button>
			<?php }
				if (self::$showSave && !$pbPreviousPeriod){ ?>
					<button target="new" class="csv_button" onclick="<?=self::$save_fn?>('<?=$psApp?>','<?=$psMetric?>');return false;">Save</button>
			<?php }
				if (self::$showCompare && !$pbPreviousPeriod){ ?>
					<button target="new" class="csv_button" onclick="window.open('<?=$sCompareUrl?>');return false;")>Compare</button>
			<?php }
			echo "</div>";
			
			return $sDivID;
	}
	
	
	//****************************************************************************
	public static function do_header(){
		global $jsinc;
	?>
		<script type="text/javascript" src="<?=$jsinc?>/bean/bean.js"></script>
		<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
 	<?php
	}
	
	//****************************************************************************
	public static function do_footer(){
		if (!self::$json_data_fn) cDebug::error("data function not set");
		if (!self::$json_callback_fn) cDebug::error("JSON callback not set");
		cDebug::enter();
		?>
		<script type="text/javascript">
		    google.charts.load('current', {'packages':['corechart']});
			google.charts.setOnLoadCallback(load_charts);
			
			function load_charts(){
				oRemote = new cRemote();
				var oChart;
				<?php
					foreach (self::$chart_items as $aRow) {
						?>
						oChartData = new chartData();
						oChartData.app="<?=$aRow[self::KEY__APP]?>";
						oChartData.chart="<?=$aRow[self::KEY__CHART]?>";
						oChartData.metric="<?=urlencode($aRow[self::KEY__METRIC])?>";
						oChartData.previous=<?=($aRow[self::KEY__PREVIOUS]?"true":"false")?>;
						oChartData.caption="<?=$aRow[self::KEY__CAPTION]?>";
						oRemote.aItems.push( oChartData);
						<?php
					}
				?>
				oRemote.fnUrlCallBack=chart_getUrl;
				bean.on(oRemote, 'onJSON',onChartJson);
				bean.on(oRemote, 'onJSONError',onChartError);
				oRemote.go();
			}		
		</script>
		
		<!-- THE FORM TO EXTRACT ALL THE CHART DATA INTO A CORRELATED csv FILE -->
		<form method="POST" action="all_csv.php" target="_blank">
		<?php
		$i=0;
		foreach (self::$chart_items as $aRow){
			$sMetric = $aRow[self::KEY__METRIC];
			$sApp = $aRow[self::KEY__APP];
			$sCaption = $aRow[self::KEY__CAPTION];
			$i++;
			?>
			<input type="hidden" name="<?=cRender::CHART_APP_FIELD?>.<?=$i?>" value="<?=$sApp?>">
			<input type="hidden" name="<?=cRender::CHART_METRIC_FIELD?>.<?=$i?>" value="<?=$sMetric?>">
			<input type="hidden" name="<?=cRender::CHART_TITLE_FIELD?>.<?=$i?>" value="<?=$sCaption?>">
			<?php
		}
		?>
		<input type="hidden" name="<?=cRender::CHART_COUNT_FIELD?>" value="<?=$i?>">
		<input type="submit" name="submit" value="Export All chart data on this screen">
			
		</form>
		<?php
		cDebug::leave();
	}
}
?>
