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
require_once "$root/inc/charts.php";

$sNode = cHeader::get(cRender::NODE_QS);

//####################################################################
cRenderHtml::header("pick a Queue Manager on Node $sNode");
cRender::force_login(); 

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}

if (!$sNode){
	cCommon::errorbox("no Node specified");
	cRenderHtml::footer();
	exit;
}

//####################################################################
$sMetricPath= cADMetricPaths::serverMQManagers($sNode);  
$aData = (cADApp::$server_app)->GET_Metric_heirarchy($sMetricPath, true);
$iCount = count($aData);

//####################################################################
cRenderCards::card_start(($iCount==0?"Queue Managers":"Pick a Queue Manager"));
	cRenderCards::body_start();
		if ($iCount == 0)
			cCommon::errorbox("sorry - no Queue Managers found");
		else
			echo "All these Queue managers have Queues";
	cRenderCards::body_end();
	cRenderCards::action_start();
		cRender::button("Back to nodes", "mq.php");	
		$sUrl = cHttp::build_url(cCommon::filename(), cRender::NODE_QS, $sNode);
		if (cRender::is_list_mode())
			cRender::button("show as buttons", $sUrl);
		else
			cRender::button("show as list", $sUrl."&".cRender::LIST_MODE_QS);
	cRenderCards::action_end();
cRenderCards::card_end();
		
//echo "found $iCount nodes<p>";
if ($iCount > 0){
	uasort($aData,"sort_by_app_name" );
	if (cRender::is_list_mode()){
		cRenderCards::card_start();
			cRenderCards::body_start();
				echo "<div  style='column-count:3'>";
				foreach ($aData as $oItem)
					if ($oItem->type === "folder")
						echo "$oItem->name<br>";
				echo "</div>";
			cRenderCards::body_end();
		cRenderCards::card_end();
	}else{
		$sPrevious = "";
		foreach ($aData as $oItem){
			if ($oItem->type === "folder"){
				$sChar = strtolower(($oItem->name)[0]);
				if ($sChar !== $sPrevious){
					if ($sPrevious !== "") {
						cRenderCards::body_end();
						cRenderCards::card_end();
					}
					cRenderCards::card_start($sChar);
					cRenderCards::body_start();
				}
				$sUrl=cHttp::build_url("mqqueues.php", cRender::NODE_QS, $sNode);
				$sUrl=cHttp::build_qs($sUrl, cRender::SERVER_MQ_MANAGER_QS, $oItem->name);
				cRender::button($oItem->name, $sUrl);	
				$sPrevious = $sChar;
			}
		}
		cRenderCards::body_end();
		cRenderCards::card_end();
	}
}

//####################################################################
cRenderHtml::footer();
?>
