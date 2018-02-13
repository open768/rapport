<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2018 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

//array of charts to display
class cChartItem{
	public $type = "unknown";
	public $caption = "not set";
	public $metric = "not set";
	public $app = null;
	public $go_URL = null;
	public $go_hint = "Go";
	public $height = 250;
	public $width = 1000;
	
	public function write_html(){
		?><DIV 
			type="appdchart" 
			appName="<?=$this->app?>" metric="<?=$this->metric?>" title="<?=$this->caption?>" previous="<?=cChart::$showPreviousPeriod?>"
			width="<?=$this->width?>" height="<?=$this->height?>" 
			showZoom="<?=cChart::$show_zoom?>"
			showCompare="<?=cChart::$show_compare?>"
			<?php if($this->go_URL){?>
				goUrl="<?=$this->go_URL?>" goLabel="<?=$this->go_hint?>"
			<?php }?>
		>
			Initialising: <?=$this->caption?>
		</DIV><?php
	}
}

class cChart{
	public static $width=1000;
	public static $show_zoom = true;
	public static $show_compare = true;
	public static $show_export_all = true;
	public static $showPreviousPeriod = true;
	public static $show_ = true;
	const METRIC="m";
	const TYPE="t";
	const LABEL="l";
	const BUTTON="b";
	const GO_URL="gu";
	const GO_HINT="gh";
	const APP="a";
	const URL="u";
	const STYLE="s";
	const WIDTH="w";
	
	//****************************************************************************
	public static function do_header(){
	}
	
	//****************************************************************************
	public static function do_footer(){		
		?>
		<div id="AllMetrics">...</div>
		<script language="javascript">
			$(	function(){cCharts.show_export_all=<?=self::$show_export_all?>;cCharts.init();}	);
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
				

				$start_tag = "<td>";
				$end_tag = "</td>";
				if ($sType == self::LABEL){
					$start_tag = "<th>";
					$end_tag = "</th>";
				}
				
				$iWidth = null;
				if ( array_key_exists( self::WIDTH, $aItem)) {
					$iWidth = $aItem[self::WIDTH];
					$start_tag .= "<div style='width:${iWidth}px;max-width:${iWidth}px;word-break:break-all'>";
					$end_tag = "</div>$end_tag";
				}

				?><?=$start_tag?><?php
				switch ($sType){
					case self::LABEL:
						?><?=$aItem[self::LABEL]?><?php
						break;
					case self::BUTTON:
						cRender::button($aItem[self::LABEL],$aItem[self::URL]);
						break;
					default:
						if (!array_key_exists(self::METRIC, $aItem)) throw new Exception("No Metric Provided");
						
						$oItem = new cChartItem();
						$oItem->app = $poApp->name;
						$oItem->metric = $aItem[self::METRIC];
						$oItem->width = self::$width;
						if ($piHeight) $oItem->height = $piHeight;
						
						if (array_key_exists(self::APP,$aItem)) $oItem->app = $aItem[self::APP];
						if (array_key_exists(self::GO_URL,$aItem)) $oItem->go_URL = $aItem[self::GO_URL];
						if (array_key_exists(self::GO_HINT,$aItem)) $oItem->go_hint = $aItem[self::GO_HINT];
						if (array_key_exists(self::LABEL, $aItem)) 
							$oItem->caption = $aItem[self::LABEL];
						else
							$oItem->caption = $aItem[self::METRIC];
						
						$oItem->write_html();
				}
				?><?=$end_tag?><?php
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
