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
	public static $show_export_all = true;
	const METRIC="m";
	const TYPE="t";
	const LABEL="l";
	const APP="a";
	const STYLE="s";
	const WIDTH="w";

	//****************************************************************************
	public static function add( $psCaption, $psMetric, $psApp, $piHeight=250, $pbPreviousPeriod=false){ ?>
		<DIV 
			type="appdchart" 
			appName="<?=$psApp?>" metric="<?=$psMetric?>" title="<?=$psCaption?>" previous="<?=$pbPreviousPeriod?>"
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
		<div id="AllMetrics">...</div>
		<script language="javascript">
			$(cCharts.loadCharts(<?=self::$show_export_all?>));
		</script>
		<?php
	}
	//#####################################################################################
	//#####################################################################################
	//* 2 column table with captions and metrics
	public static function metrics_table($poApp, $paTable, $piMaxCols, $psRowClass, $piHeight = null, $piWidth=null, $paHeaders=null){ 
		if (gettype($poApp) !== "object"){
			cDebug::error("app must be an object");
		}
			
		$iCol = 0;
		$iOldWidth = self::$width;
		self::$width = ($piWidth?$piWidth:cRender::CHART_WIDTH_LETTERBOX / $piMaxCols);
		if ($piHeight==null) $piHeight = cRender::CHART_HEIGHT_SMALL;
		
		
		?><table class="maintable"><?php
			if ($paHeaders){
				?><tr><?php
					foreach ($paHeaders as $sItem){
						?><th><?=$sItem?></th><?php
					}
				?></tr><?php
			}
			foreach ($paTable as $aItem){
				$sType = "graph";
				if (array_key_exists(self::TYPE,$aItem)){
					$sType = $aItem[self::TYPE];
				}
				
				if ($iCol == 0) {
					$sClass = $psRowClass;
					if (array_key_exists(self::STYLE, $aItem)) $sClass = $aItem[self::STYLE];
					?><tr class="<?=$sClass?>"><?php
				}
				
				$iCol++;
				if ($sType === self::LABEL){
					$sWidth = "";
					?><th><?php
					if ( array_key_exists( self::WIDTH, $aItem)){
						?><DIV style='max-width:<?=$aItem[self::WIDTH]?>px'><?=$aItem[self::LABEL]?></DIV><?php
					}else{
						?><?=$aItem[self::LABEL]?><?php
					}
					?></th><?php
				}else{
					?><td><?php
						$sApp = $poApp->name;
						if (array_key_exists(self::APP,$aItem)){
							$sApp = $aItem[self::APP];
						}
						if (! array_key_exists(self::LABEL, $aItem)){
							cDebug::write("no label");
							cDebug::vardump($aItem,true);
						}elseif (! array_key_exists(self::METRIC, $aItem)){
							cDebug::write("no metric");
							cDebug::vardump($aItem,true);
						}else{
							self::add($aItem[self::LABEL], $aItem[self::METRIC], $sApp, $piHeight);
						}
					?></td><?php
				}
				if ($iCol==$piMaxCols){
					?></tr><?php
					$iCol = 0;
				}
			}
			if ($iCol !== 0) echo "</tr>";
		?></table><?php 
		
		self::$width = $iOldWidth;
	}
}
?>
