<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2016 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

//array of charts to display

class cChart{
	public static $width=1000;
	public static $show_zoom = true;
	public static $show_compare = true;

	//****************************************************************************
	public static function add( $psCaption, $psMetric, $psApp, $piHeight=250, $pbPreviousPeriod=false){ ?>
		<DIV 
			type="appdchart" 
			appName="<?=$psApp?>" metric="<?=$psMetric?>" title="<?=$psCaption?>" 
			width="<?=self::$width?>" height="<?=$piHeight?>" 
			showZoom="<?=self::$show_zoom?>"
			showCompare="<?=self::$show_compare?>"
		>
			Initialising...
		</DIV>
	<?php }
	
	
	//****************************************************************************
	public static function do_header(){
	}
	
	//****************************************************************************
	public static function do_footer(){		
		?>
		<script language="javascript">
			$(cCharts.loadCharts());
		</script>
		<?php
		/*
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
		*/
	}
}
?>
