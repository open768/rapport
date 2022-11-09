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


//-----------------------------------------------
$oApp = cRenderObjs::get_current_app();
$gsAppQS = cRenderQS::get_base_app_QS($oApp);


//####################################################################
function render_pojo_item($poItem){
	$sType = $poItem->definition->matchType;
	switch($sType){
		case "MATCHES_CLASS":
		case "INHERITS_FROM_CLASS":
			echo "class: ".cRenderW3::tag($poItem->definition->className)."<br>";
			echo "method: ".cRenderW3::tag($poItem->definition->methodName)."<br>";
			break;
		default:
			cCommon::messagebox("unknown type: $sType");
			cDebug::vardump($poItem->definition);
	}
	if (count($poItem->methodDataGathererConfigs) > 0){
		echo "<p>Getters:<ul>";
		foreach ($poItem->methodDataGathererConfigs as $oItem){
			$sLine = cRenderW3::tag($oItem->name);
			if ($oItem->returnValue)
				$sLine .= " - use return value";
			else{
				$sLine .= ", Parameter($oItem->position)";
				$oTransformer = $oItem->objectDataTransformer;
				if ($oTransformer->useToString)
					$sLine .= ".toString()";
				else
					foreach ($oTransformer->objectStateGetterMethods as $sGetter)
						$sLine .= ".$sGetter";
			}
			echo "<li>$sLine";
			cDebug::vardump($oItem);
		};
	}
}

//********************************************************************
function render_sql_item($poItem){
	echo "SQL: ".$poItem->sqlQuery;
}

//********************************************************************
function render_http_item($poItem){
	//-----------------------------------------------------------
	$aData = $poItem->requestParameters ;
	if ($aData == null || count($aData) == 0)
		echo cRenderW3::tag("No Request parameters collected", "w3-pale-yellow");
	else{
		echo "<b>Request Parameters</b><br>";
		foreach ($aData as $oItem)
			echo cRenderW3::tag($oItem->name);
	}
	echo "<p>";
	
	//-----------------------------------------------------------
	$aData = $poItem->cookieNames ;
	if ($aData == null || count($aData) == 0)
		echo cRenderW3::tag("No Cookies collected", "w3-pale-yellow");
	else{
		echo "<b>Cookies</b><br>";
		foreach ($aData as $sItem)
			echo cRenderW3::tag($sItem);
	}
}

//********************************************************************
function render_item($poItem){
	switch ($poItem->type){
		case "http":
			render_http_item($poItem);
			break;
			
		case "pojo":
			render_pojo_item($poItem);
			break;
			
		case "sql":
			render_sql_item($poItem);
			break;
			
		default:
			cCommon::messagebox("unknown type: $poItem->type");
			cDebug::vardump($poItem);
	}
}

//####################################################################
$title ="$oApp->name Data Collectors";
cRenderHtml::header("$title");
cRender::force_login();

$oTimes = cRender::get_times();

$oCred = cRenderObjs::get_AD_credentials();
if ($oCred->restricted_login ){
	cRenderHtml::footer();
	exit;
}
cRenderCards::card_start();
cRenderCards::body_start();
	cRender::add_filter_box("span[type=datacollector]","value",".mdl-card");
cRenderCards::body_end();
cRenderCards::action_start();
	cADCommon::button(cADControllerUI::data_collectors($oApp));
	cRenderMenus::show_apps_menu("change app");
	$sUrl = cHttp::build_url("$home/pages/trans/apptrans.php", $gsAppQS);
	cRender::button("transactions", $sUrl);
cRenderCards::action_end();
cRenderCards::card_end();		

//******************************************************************
$aData = $oApp->GET_data_collectors();
if (!$aData || count($aData) ==0){
	cCommon::messagebox("no Data collectors configured");
	cRenderHtml::footer();
	exit;
}

foreach ($aData as $oItem){
	cRenderCards::card_start("<span type='datacollector' value='$oItem->name'>$oItem->name ($oItem->type)</span>");
	cRenderCards::body_start();
		render_item($oItem);
	cRenderCards::body_end();
	cRenderCards::card_end();		
}

cRenderHtml::footer();
?>
