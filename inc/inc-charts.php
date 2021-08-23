<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2021 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

//array of charts to display
class cChartMetricItem{
	public $metric;
	public $caption;
}

class cChartItem{
	public $type = "unknown";
	public $caption = null;
	public $metrics = [];
	public $app = null;
	public $go_URL = null;
	public $go_hint = "Go";
	public $height = 250;
	public $width = 1000;
	public $hideIfNoData = false;
	public $hideGroupIfNoData = false;

	
	public function write_html(){
		global $home;
		?><DIV
			type="appdchart" 
			home="<?=$home?>"
			appName="<?=$this->app->name?>" previous="<?=cChart::$showPreviousPeriod?>"
			width="<?=$this->width?>" height="<?=$this->height?>" 
			style="position:relative;max-width:<?=$this->width?>px;width:<?=$this->width?>px;height=<?=$this->height?>px;overflow-wrap:break-all"
			showZoom="<?=cChart::$show_zoom?>"
			showCompare="<?=cChart::$show_compare?>"
			hideIfNoData="<?=$this->hideIfNoData?>"
			hideGroupIfNoData="<?=cChart::$hideGroupIfNoData?>"
			<?php if($this->go_URL){?>
				goUrl="<?=$this->go_URL?>" 
				goLabel="<?=$this->go_hint?>"
			<?php }?>
			<?php
				for ($i=0; $i< count($this->metrics); $i++){
					$oMetric = $this->metrics[$i];
					?>metric<?=$i?>="<?=$oMetric->metric?>" title<?=$i?>="<?=$oMetric->caption?>"<?php
				}
			?>
		>
			Waiting for charts: <?=$this->metrics[0]->caption?>
		</DIV><?php
	}
}

class cChart{
	public static $width=1000;
	public static $show_zoom = 1;
	public static $show_compare = 1;
	public static $show_export_all = 1;
	public static $showPreviousPeriod = 0;
	public static $hideGroupIfNoData = 0;
	const METRIC="m";
	const TYPE="t";
	const LABEL="l";
	const HIDEIFNODATA="hind";
	const BUTTON="b";
	const GO_URL="gu";
	const GO_HINT="gh";
	const APP="a";
	const URL="u";
	const STYLE="s";
	const WIDTH="w";
	
	//************************************************************
	const CHART_WIDTH_LARGER = 1800;
	const CHART_WIDTH_LARGE = 1024;
	const CHART_HEIGHT_LARGE = 700;
	const CHART_HEIGHT_SMALL = 125;
	const CHART_HEIGHT_TINY = 75;

	const CHART_WIDTH_LETTERBOX = 1024;
	const CHART_HEIGHT_LETTERBOX = 250;
	
	const CHART_WIDTH_LETTERBOX2 = 1024;
	const CHART_HEIGHT_LETTERBOX2 = 200;
	
	//****************************************************************************
	public static function do_header(){
	}
	
	//****************************************************************************
	public static function do_footer(){		
		global $home;
		?>
		<div id="AllMetrics">...</div>
		<script language="javascript">
			$(	function(){cCharts.show_export_all=<?=self::$show_export_all?>;cCharts.init("<?=$home?>");}	);
		</script>
		<?php
	}

	//#####################################################################################
	// this renders the placeholder on the page. The code to populate the placeholder is in the javascript.
	//#####################################################################################
	private static function pr_render_item($poApp, $paItem, $piHeight = null, $piWidth=null, $paHeaders=null){
		$sType = "graph";
		if (isset($paItem[self::TYPE])) $sType = $paItem[self::TYPE];

		switch ($sType){
			case self::LABEL:
				?><?=$paItem[self::LABEL]?><?php
				break;
			case self::BUTTON:
				cRender::button($paItem[self::LABEL],$paItem[self::URL]);
				break;
			default:
				if (!isset($paItem[self::METRIC])) throw new Exception("No Metric Provided");
				
				$oItem = new cChartItem();
				$oItem->app = $poApp;
				if (isset($paItem[self::HIDEIFNODATA]))
					$oItem->hideIfNoData = $paItem[self::HIDEIFNODATA];
				
				$oItem->hideGroupIfNoData = cChart::$hideGroupIfNoData;

				//--------------------------------------------------
				$oMetricItem = new cChartMetricItem();
				$oMetricItem->metric = $paItem[self::METRIC];
				
				if (isset($paItem[self::LABEL])){ 
					$sLabel = $paItem[self::LABEL];
					if ($sLabel == null) throw new Exception("No Label Provided");
					$oMetricItem->caption = $sLabel;
				}else
					$oMetricItem->caption = $paItem[self::METRIC];
				$oItem->metrics[] = $oMetricItem;
				
				//--------------------------------------------------
				$oItem->width = self::$width;
				if ($piWidth) $oItem->width = $piWidth;
				if ($piHeight) $oItem->height = $piHeight;
				
				if (isset($paItem[self::APP])) $oItem->app = $paItem[self::APP];
				if (isset($paItem[self::GO_URL])) $oItem->go_URL = $paItem[self::GO_URL];
				if (isset($paItem[self::GO_HINT])) $oItem->go_hint = $paItem[self::GO_HINT];
				
				//--------------------------------------------------
				$oItem->write_html();
		}		
	}
	//#####################################################################################
	//#####################################################################################
	public static function render_metrics($poApp, $paItems, $piWidth=cChart::CHART_WIDTH_LETTERBOX, $piHeight = cChart::CHART_HEIGHT_SMALL ){ 
		$sClass= cRender::getRowClass();
		?><div class="<?=$sClass?>"><?php
		foreach ($paItems as $aItem){
			self::pr_render_item($poApp, $aItem,$piHeight, $piWidth);
		}
		?></div><?php
	}
	
	//*********************************************************************************************
	public static function metrics_table($poApp, $paItems, $piMaxCols, $psRowClass, $piHeight = cChart::CHART_HEIGHT_SMALL, $piWidth=null, $paHeaders=null){ 
		if (gettype($poApp) !== "object"){
			cDebug::error("app must be an object");
		}
			
		$iCol = 0;
		$iOldWidth = self::$width;
		self::$width = ($piWidth?$piWidth:cChart::CHART_WIDTH_LETTERBOX / $piMaxCols);
		if ($piHeight == null) $piHeight= cChart::CHART_HEIGHT_SMALL;
		
		
		?><table class="maintable"><?php
			if ($paHeaders){
				?><tr><?php
					foreach ($paHeaders as $sItem){
						?><th><?=$sItem?></th><?php
					}
				?></tr><?php
			}
			foreach ($paItems as $aItem){
				$sType = "graph";
				if (isset($aItem[self::TYPE])){
					$sType = $aItem[self::TYPE];
				}
				
				if ($iCol == 0) {
					$sClass = $psRowClass;
					if (isset($aItem[self::STYLE])) $sClass = $aItem[self::STYLE];
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
				if ( isset( $aItem[self::WIDTH])) {
					$iWidth = $aItem[self::WIDTH];
					$start_tag .= "<div style='width:${iWidth}px;max-width:${iWidth}px;word-break:break-all'>";
					$end_tag = "</div>$end_tag";
				}

				?><?=$start_tag?><?php
					self::pr_render_item($poApp, $aItem,$piHeight, $piWidth);
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
