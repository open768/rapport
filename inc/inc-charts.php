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
		$iOldWidth = cChart::$width;
		cChart::$width = ($piWidth?$piWidth:cRender::CHART_WIDTH_LETTERBOX / $piMaxCols);
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
				if (array_key_exists(cChart::TYPE,$aItem)){
					$sType = $aItem[cChart::TYPE];
				}
				
				if ($iCol == 0) {
					$sClass = $psRowClass;
					if (array_key_exists(cChart::STYLE, $aItem)) $sClass = $aItem[cChart::STYLE];
					?><tr class="<?=$sClass?>"><?php
				}
				
				$iCol++;
				if ($sType === cChart::LABEL){
					?><th><?=$aItem[cChart::LABEL]?></th><?php
				}else{
					?><td><?php
						$sApp = $poApp->name;
						if (array_key_exists(cChart::APP,$aItem)){
							$sApp = $aItem[cChart::APP];
						}
						if (! array_key_exists(cChart::LABEL, $aItem)){
							cDebug::write("no label");
							cDebug::vardump($aItem,true);
						}elseif (! array_key_exists(cChart::METRIC, $aItem)){
							cDebug::write("no metric");
							cDebug::vardump($aItem,true);
						}else{
							cChart::add($aItem[cChart::LABEL], $aItem[cChart::METRIC], $sApp, $piHeight);
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
		
		cChart::$width = $iOldWidth;
	}
}
?>
