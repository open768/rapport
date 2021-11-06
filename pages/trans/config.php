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
//####################################################################
$home="../..";
require_once "$home/inc/common.php";
require_once "$root/inc/charts.php";

require_once("$root/inc/charts.php");

//####################################################################
// common functions

function render_php_cfg($poItem){
	$oRule = $poItem->rule;
	switch($oRule->type){
		case "TX_MATCH_RULE":
			$oMatchRule = $oRule->txMatchRule;
			switch($oMatchRule->type){
				//-----------------------------------------------------------
				case "AUTOMATIC_DISCOVERY":
					$aConfigs = $oMatchRule->txAutoDiscoveryRule->autoDiscoveryConfigs;
					echo cRenderW3::panel_start("w3-sand w3-padding-16");
						echo "Auto Discovery<ul>";
						foreach ($aConfigs as $oItem){
							if (!$oItem->monitoringEnabled) continue;
								echo "<li>".cRenderW3::tag($oItem->txEntryPointType).". Naming scheme: ".cRenderW3::tag($oItem->namingSchemeType).". ";
								switch ($oItem->namingSchemeType){
									case "SERVICE_NAME_AND_OPERATION_NAME":
										break;
									case "URI":
										$oDiscovery = $oItem->httpAutoDiscovery;
										if ($oDiscovery->useFullURI)
											echo "uses full uri";
										else{
											$oSegments= $oDiscovery->partURISegments;
											echo cRenderW3::tag($oSegments->type." ".$oSegments->numSegments)." URL Segments";
										}
										break;
											cCommon::messagebox("unknown naming scheme: $oItem->namingSchemeType");
									default:
								}
						}
					echo "</ul></div>";
					break;
				//-----------------------------------------------------------
				case "CUSTOM":
					$oCustomRule = $oMatchRule->txCustomRule;
					echo cRenderW3::panel_start("w3-sand w3-padding-16");
						echo "<code>$oCustomRule->type</code>:".cRenderW3::tag("Custom Rule").". ";
						echo "EntryPoint:".cRenderW3::tag($oCustomRule->txEntryPointType);
						$aMatchCond = $oCustomRule->matchConditions;
						echo "<br><dl><dt>Conditions";
						foreach ($aMatchCond as $oCondition){
							switch($oCondition->type){
								case "HTTP":
									$sHTML = "<dd><li>";
									$oMatch = $oCondition->httpMatch;
									if (property_exists($oMatch, "httpMethod"))
										$sHTML.="Method: ".cRenderW3::tag($oMatch->httpMethod);
									else
										$sHTML.="Method ".cRenderW3::tag("not specified");
									
									//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
									if (property_exists($oMatch, "uri")){
										$oUri = $oMatch->uri;
										if (property_exists($oUri,"isNot"))
											if ($oUri->isNot) $sHTML.=" doesnot ";
										
										$sHTML.=$oUri->type.":".cRenderW3::tag($oUri->matchStrings[0]).".";
										
									//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
									}elseif (property_exists($oMatch, "headers")){
										$sHTML.="<ul>";
										$aHeaders = $oMatch->headers;
										//cDebug::vardump($aHeaders);
										foreach ($aHeaders as $oHeader)
											switch(	$oHeader->comparisonType){
												case "CHECK_VALUE":
													$sFragment = "Header: ";
													if (property_exists($oHeader, "name")){
														$oName = $oHeader->name;
														$sFragment.= "name ".cRenderW3::tag($oName->type)." ";
														$sFragment.= cRenderW3::tag(implode(",",$oName->matchStrings));
													}
													if (property_exists($oHeader, "value")){
														$oValue = $oHeader->value;
														$sFragment.= ", value ".cRenderW3::tag($oValue->type)." ";
														$sFragment.= cRenderW3::tag(implode(",",$oValue->matchStrings));
													}
													$sHTML .= "<li>$sFragment";
													break;
												default:
													$sHTML .= "<li>".cRenderW3::tag("Unknown comparison type $oHeader->comparisonType", "w3-blue");											}
										
										$sHTML .= "</ul>";
										
									//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
									}elseif (property_exists($oMatch, "parameters")){
										$aParams = $oMatch->parameters;
										foreach ($aParams as $oParam){
											switch(	$oParam->comparisonType){
												case "CHECK_VALUE":
													$sFragment = "Parameter: ";
													if (property_exists($oParam, "name")){
														$oName = $oParam->name;
														if ($oName->isNot) $sFragment.= "NOT ";
														$sFragment.= "name ".cRenderW3::tag($oName->type)." ";
														$sFragment.= cRenderW3::tag(implode(",",$oName->matchStrings));
													}
													if (property_exists($oParam, "value")){
														$oValue = $oParam->value;
														if ($oValue->isNot) $sFragment.= "NOT ";
														$sFragment.= ", value ".cRenderW3::tag($oValue->type)." ";
														$sFragment.= cRenderW3::tag(implode(",",$oValue->matchStrings));
													}
													$sHTML .= "<li>$sFragment";
													break;
												default:
													$sHTML .= "<li>".cRenderW3::tag("Unknown comparison type $oHeader->comparisonType", "w3-blue");											
											}
										}
									//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
									}else{
										$sHTML.=" Unknown match type";
										cDebug::vardump($oMatch);
									}
									echo $sHTML;
									break;
								default;
									cCommon::messagebox("unknown condition type $oCondition->type");
							}
						}
						echo "</dl>";
					echo "</ul></div>";
					break;
				
				//-----------------------------------------------------------
				default:
					cCommon::messagebox("unknown match typerule : $oMatchRule->type");
			}
			break;
		default:
			cCommon::messagebox("unknown rule type: $oRule>type");
	}
}

//********************************************************************
function render_nodejs_cfg($poItem){
	echo cRenderW3::panel_start("w3-sand w3-padding-16");
		cCommon::messagebox("NJS work in progress");
	echo "</div>";
}

//********************************************************************
function render_java_cfg($poItem){
	echo cRenderW3::panel_start("w3-sand w3-padding-16");
		cCommon::messagebox("java work in progress");
		cDebug::vardump($poItem);
	echo "</div>";
}

//********************************************************************
function render_dotnet_cfg($poItem){
	echo cRenderW3::panel_start("w3-sand w3-padding-16");
		cCommon::messagebox("dotnet work in progress");
		cDebug::vardump($poItem);
	echo "</div>";
}

//********************************************************************
function render_python_cfg($poItem){
	echo cRenderW3::panel_start("w3-sand w3-padding-16");
		cCommon::messagebox("python work in progress");
		cDebug::vardump($poItem);
	echo "</div>";
}

//********************************************************************
function render_native_cfg($poItem){
	echo cRenderW3::panel_start("w3-sand w3-padding-16");
		cCommon::messagebox("native work in progress");
		cDebug::vardump($poItem);
	echo "</div>";
}

//####################################################################
//get passed in values
$oApp = cRenderObjs::get_current_app();
$gsAppQS = cRenderQS::get_base_app_QS($oApp);

cRenderHtml::header("Transaction config for $oApp->name");
cRender::force_login();
//header

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************


cRenderCards::card_start();
cRenderCards::body_start();
	cRender::add_filter_box("span[type=btrule]","name",".mdl-card");
	cCommon::messagebox("work in progress");
cRenderCards::body_end();
cRenderCards::action_start();
	cRenderMenus::show_apps_menu("Change Application", "config.php");
	cADCommon::button(cADControllerUI::app_BT_config($oApp));
	$sUrl = cHttp::build_url("apptrans.php", $gsAppQS);
	cRender::button("Back to transactions", $sUrl);
cRenderCards::action_end();
cRenderCards::card_end();

$aConfigs = $oApp->GET_Transaction_configs();
if (!$aConfigs || count($aConfigs) == 0){
	cCommon::messagebox("no configurations found");
	cRenderHtml::footer();
	exit;
}

if (cDebug::is_debugging()){
	$aTypes = [];
	foreach ($aConfigs as $oItem){
		$oRule = $oItem->rule;
		$sType = $oRule->agentType;
		$aTypes[$sType] = 1;
	}
	ksort($aTypes);
	foreach ($aTypes as $sKey=>$sValue){
		cDebug::write($sKey);
	}
	//cDebug::vardump($aConfigs[0]);
}

foreach ($aConfigs as $oItem){
	$oRule = $oItem->rule;
	$sName = $oRule->summary->name;
	if (!$oRule->enabled) {
		cDebug::extra_debug("skipping disabled rule: $sName");
		continue;
	}

	$sTitle = "<span type='btrule' name='$sName'>$sName</span>";
	cRenderCards::card_start($sTitle);
	cRenderCards::body_start();
		$sPri = str_pad($oRule->priority, 3, "0", STR_PAD_LEFT);
		$sAgentType = $oRule->agentType;
		if (property_exists($oItem, "scopeSummaries"))
			$aScopes = $oItem->scopeSummaries;
		else
			$aScopes = [(object)["name"=>"Default Scope"]];
		
		$sType = $oRule->type;
		switch($sType){
			case "TX_MATCH_RULE":
				$sType = "Transaction Match rule";
				break;
			default:
				$sType = "<font color='red'>Unknown type $sType";
		}

		echo "<table border='1' cellspacing='0' width='100%'>".
			"<tr>";
				echo "<td width='200' valign='top'>Agent Type<br>";
					echo cRenderW3::tag($sAgentType);
				echo "</td>";
				echo "<td width='50' valign='top'>Priority<br>";
					echo cRenderW3::tag($sPri);
				echo "</td>";
				
				echo "<td width='200' valign='top'>Type<br>";
					echo cRenderW3::tag($sType);
				echo "</td>";
				
				echo "<td width='*' valign='top'>Scopes<br>";
					foreach ($aScopes as $oScope)
						echo cRenderW3::tag($oScope->name);
				echo "</td>";
			echo "</tr><tr><td colspan='4'>";
			switch ($sAgentType){
				case "PHP_APPLICATION_SERVER":
					render_php_cfg($oItem);
					break;
				case "NODE_JS_SERVER":
					render_nodejs_cfg($oItem);
					break;
				case "APPLICATION_SERVER":
					render_java_cfg($oItem);
					break;
				case "DOT_NET_APPLICATION_SERVER":
					render_dotnet_cfg($oItem);
					break;
				case "NATIVE_WEB_SERVER":
					render_native_cfg($oItem);
					break;
				case "PYTHON_SERVER":		
					render_python_cfg($oItem);
					break;
				default:
					cCommon::messagebox("unknown type $sType");
			}
		echo "</td></tr></table>";
	cRenderCards::body_end();
	cRenderCards::card_end();
}


cRenderHtml::footer();
?>
