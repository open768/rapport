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



//####################################################################
cRenderHtml::header("All Servers - MQ");
cRender::force_login(); 

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}

//####################################################################
$aData = cADController::GET_server_nodes_with_MQ();
$iCount = count($aData);

//####################################################################
cRenderCards::card_start(($iCount==0?"MQ Nodes":"Pick a Node"));
	cRenderCards::body_start();
		if ($iCount == 0)
			cCommon::errorbox("sorry - no nodes found");
		else
			echo "All these nodes have MQ Queue managers associated with them";
	cRenderCards::body_end();
	cRenderCards::action_start();
		cRender::button("Back to servers", "servers.php");	
		if (cRender::is_list_mode())
			cRender::button("show as buttons", cCommon::filename());
		else
			cRender::button("show as list", cCommon::filename()."?".cRenderQS::LIST_MODE_QS);
	cRenderCards::action_end();
cRenderCards::card_end();

// get the list of all servers that have MQ metrics
if ($iCount >= 0){
	if (cRender::is_list_mode()){
		cRenderCards::card_start();
			cRenderCards::body_start();
			foreach ($aData as $sNode)
				echo "$sNode<br>";	
			cRenderCards::body_end();
		cRenderCards::card_end();
	}else{
		$sPrevious = "";
		$iColumn=0;
		foreach ($aData as $sNode){
			$sChar = strtolower($sNode[0]);
			if ($sChar !== $sPrevious){
				if ($sPrevious !== "") {
					cRenderCards::body_end();
					cRenderCards::card_end();
				}
				cRenderCards::card_start($sChar);
				cRenderCards::body_start();
			}
			$sUrl=cHttp::build_url("mqnode.php", cRenderQS::NODE_QS, $sNode);
			cRender::button($sNode, $sUrl);	
			$sPrevious = $sChar;
		}
		cRenderCards::body_end();
		cRenderCards::card_end();
	}
}

//####################################################################
cRenderHtml::footer();
?>
