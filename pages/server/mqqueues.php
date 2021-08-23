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
require_once "$root/inc/inc-charts.php";


//####################################################################
cRenderHtml::header("MQ Queues");
cRender::force_login(); 
cChart::do_header();
cChart::$hideGroupIfNoData = true;

//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}

$sNode = cHeader::get(cRender::NODE_QS);
if (!$sNode){
	cRender::errorbox("no Node specified");
	cRenderHtml::footer();
	exit;
}
$sQManager = cHeader::get(cRender::SERVER_MQ_MANAGER_QS);
if (!$sQManager){
	cRender::errorbox("no Queue Manager specified");
	cRenderHtml::footer();
	exit;
}

//####################################################################
cRender::show_time_options( "Queues for $sQManager on node $sNode - MQ"); 		

//####################################################################
?>
<div id="page_content">
	<?php
		cRender::button("Back to MQ nodes", "mq.php");	
		cRender::button("Back to $sNode", cHttp::build_url("mqnode.php",cRender::NODE_QS, $sNode));	
		$sUrl = cHttp::build_url("mqqueues.php", cRender::NODE_QS, $sNode);
		$sUrl = cHttp::build_qs($sUrl, cRender::SERVER_MQ_MANAGER_QS, $sQManager);
		if (cRender::is_list_mode())
			cRender::button("show as buttons", $sUrl);
		else
			cRender::button("show as list", $sUrl."&".cRender::LIST_MODE_QS);
	?>
	<p>
	<h1>Queues for <?=$sQManager?> on node <?=$sNode?></h1>
	<?php
		// get the list of all queues for this manager
		$sMetricPath= cAppDynMetric::serverMQQueues($sNode, $sQManager);  
		$aData = cAppdynCore::GET_Metric_heirarchy(cAppDynCore::SERVER_APPLICATION, $sMetricPath, true);
		$iCount = count($aData);
		if ($iCount == 0){
			cRender::errorbox("sorry - no Queues found");
			cRenderHtml::footer();
			exit;
		}
		
		if ($iCount > cRender::$MAX_ITEMS_PER_PAGE)
			echo "thats a lot of charts - i have to paginate<p>&nbsp;<p>";

		//echo "found $iCount nodes<p>";
		uasort($aData,"sort_by_app_name" );
		$aMetrics = [];
		foreach ($aData as $oItem){
			if ($oItem->type === "folder")
				if (cRender::is_list_mode())
					echo "$oItem->name<br>";	
				else{
					$sMetric = cAppDynMetric::serverMQQueueCurrent($sNode, $sQManager, $oItem->name);
					$sTitle = $oItem->name." : Current";
					$aMetrics[] = [cChart::LABEL=>$sTitle, cChart::METRIC=>$sMetric];
				}
		}
		$srv_app = new cAppDApp(cAppDynCore::SERVER_APPLICATION,cAppDynCore::SERVER_APPLICATION);
		cChart::metrics_table($srv_app, $aMetrics, 6, cRender::getRowClass(),cChart::CHART_HEIGHT_SMALL,cChart::CHART_WIDTH_LARGER/6);
	?>
</div>
<?php

//####################################################################
cChart::do_footer();
cRenderHtml::footer();
?>
