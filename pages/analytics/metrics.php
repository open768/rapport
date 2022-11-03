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
$home="../..";
require_once "$home/inc/common.php";


//####################################################################
cRenderHtml::header("Metrics");
cRender::force_login();

$aMetrics = cADAnalytics::list_metrics();
$aTmpMetrics = [];
$iMetricCount= count($aMetrics);

if ($iMetricCount > 0){
	foreach ( $aMetrics as $oMetric){
		$sName = preg_replace("/[^a-zA-Z]/", "", $oMetric->queryName);
		$sName = strtoupper($sName);
		$aTmpMetrics[$sName] = $oMetric;
	}
	ksort($aTmpMetrics);
}


//####################################################################
cRenderCards::card_start("<a type='metric'>Analytics metrics</a>");
	cRenderCards::body_start();
		if ($iMetricCount == 0)
			cCommon::messagebox("no Metrics found");
		else{
			echo "There are $iMetricCount Metrics";
			
			$sPrevious = null;
			echo "<div><H3>";
				foreach ( $aTmpMetrics as $sKey => $oMetric){
					$sChar = $sKey[0];
					if ($sChar !== $sPrevious){
						echo "<a href='#$sChar'>$sChar</a> ";
						$sPrevious = $sChar;
					}
				}
			echo "</H3></div>";
		}
	cRenderCards::body_end();
	cRenderCards::action_start();
		$sUrl = cCommon::filename();
		if (!cRender::is_list_mode()){
			$sUrl.= "?".cRenderQS::LIST_MODE_QS;
			cRender::button("list mode", $sUrl);
		}else			
			cRender::button("check mode", $sUrl);
		cRender::button("back to analytics","analytics.php");
		cRender::button("Create Metric", "create_metric.php");
	cRenderCards::action_end();
cRenderCards::card_end();

//####################################################################
if ($iMetricCount >0){
	if (cRender::is_list_mode()){
		//-----------------list metric names
		$bInCard = false;
		$sPrevious = null;
		foreach ( $aTmpMetrics as $sKey => $oMetric){
			$sChar = $sKey[0];
			if ($sChar !== $sPrevious){
				if ($bInCard){
					echo "</table></div>";
					cRenderCards::body_end();
					cRenderCards::card_end();
				}

				//-----------------------------------------------------------------------------------
				cRenderCards::card_start("<a name='$sChar'>$sChar</a>");
				cRenderCards::body_start();
				
				//-----------------------------------------------------------------------------------
				echo "<div><table border='1' cellspacing='0'>";
				echo "<tr><th>Name</th><th>Query</th></tr>";
				$sPrevious = $sChar;
				$bInCard = true;
			}
			echo "<tr>
				<td width='200' style='max-width:200px;overflow-wrap:break-word'>$oMetric->queryName</td>
				<td width='*'>$oMetric->adqlQueryString</td>
			</tr>";
		}
		
		if ($bInCard){
			echo "</table></div>";
			cRenderCards::body_end();
			cRenderCards::card_end();
		}
	}else{
		//-----------------render filterbox
		cRenderCards::card_start("Filter");
			cRenderCards::body_start();
				cRender::add_filter_box("a[type=metric]","",".mdl-card");
			cRenderCards::body_end();
		cRenderCards::card_end();
		
		//-----------------render contents
		cRenderCards::card_start("<a type='metric'>Contents</a>");
			cRenderCards::body_start();
				echo "<div style='column-count:4;overflow-wrap:break-word''>";
					cDebug::vardump($aMetrics[0]);
					$iCount = 1;
					$sPrevious=null;
					foreach ( $aTmpMetrics as $sKey => $oMetric){
						$sChar = $sKey[0];
						if ($sChar !== $sPrevious){
							echo "<h3><a name='$sChar'>$sChar</a></h3>";
							$sPrevious = $sChar;
						}
						echo "<li><a href='#$iCount'>$oMetric->queryName</a>";
						$iCount ++;
					}
				echo "</div>";
			cRenderCards::body_end();
		cRenderCards::card_end();
		
		//-----------------render each metric
		foreach ( $aTmpMetrics as $sKey => $oMetric){
			cRenderCards::card_start("<a type='metric' name='$iCount'>$oMetric->queryName</a>");
				cRenderCards::body_start();
					echo $oMetric->adqlQueryString;
				cRenderCards::body_end();	
				cRenderCards::action_start();
					$sUrl = cHttp::build_url("edit_metric.php",cRenderQS::ANALYTICS_METRIC_QS, $oMetric->queryName);
			 		cRender::button("Edit", $sUrl);	
				cRenderCards::action_end();
			cRenderCards::card_end();
		}
	}
}
		
cRenderHtml::footer();
?>