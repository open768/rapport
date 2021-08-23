<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2018 

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
cRenderHtml::header("pick a Queue Manager");
cRender::force_login(); 

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

//####################################################################
cRender::show_top_banner( "MQ Queue Managers for $sNode"); 		

//####################################################################
?>
<div id="page_content">
	<?php
		cRender::button("Back to nodes", "mq.php");	
		$sUrl = cHttp::build_url("mqnode.php", cRender::NODE_QS, $sNode);
		if (cRender::is_list_mode())
			cRender::button("show as buttons", $sUrl);
		else
			cRender::button("show as list", $sUrl."&".cRender::LIST_MODE_QS);
	?>
	<p>
	<h2>Pick a Queue Manager for node: <?=$sNode?></h2>
	<?php
		// get the list of all queue managers for the node
		$sMetricPath= cAppDynMetric::serverMQManagers($sNode);  
		$aData = cAppdynCore::GET_Metric_heirarchy(cAppDynCore::SERVER_APPLICATION, $sMetricPath, true);
		$iCount = count($aData);
		if ($iCount == 0){
			cRender::errorbox("sorry - no QueueManagers found");
			cRenderHtml::footer();
			exit;
		}
		//echo "found $iCount nodes<p>";
		uasort($aData,"sort_by_app_name" );
		$sPrevious = "";
		$iColumn=0;
		foreach ($aData as $oItem){
			if ($oItem->type === "folder")
				if (cRender::is_list_mode())
					echo "$oItem->name<br>";	
				else{
					$sChar = strtolower(($oItem->name)[0]);
					if ($sChar !== $sPrevious){
						$sPrevious = $sChar;
						echo "<h2>$sChar</h2>";
					}
					$sUrl=cHttp::build_url("mqqueues.php", cRender::NODE_QS, $sNode);
					$sUrl=cHttp::build_qs($sUrl, cRender::SERVER_MQ_MANAGER_QS, $oItem->name);
					cRender::button($oItem->name, $sUrl);	
				}
		}
	?>
</div>
<?php

//####################################################################
cRenderHtml::footer();
?>
