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
cRenderHtml::header("Agent License Usage");
cRender::force_login();

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************

try{
	$aRules=cADAccount::GET_LicenseRules();
}
catch (Exception $e){
	cCommon::errorbox("unable to get license details - $e");
	cRenderHtml::footer();
	exit;	
}

function filter_sort($a,$b){
	$k1 = "$a->type.$a->operator.$a->entityName";
	$k2 = "$b->type.$b->operator.$b->entityName";
	return strnatcasecmp($k1,$k2);
}

cADCommon::button(cADControllerUI::licenses());

$iRuleCount = 0;
cRenderCards::card_start("Summary");
	cRenderCards::body_start();
		echo "<ul>";
			foreach ($aRules as $oRule){
				echo "<li><a href='#$iRuleCount'>$oRule->allocationName</a>";
				$iRuleCount++;
			}
		echo "</ul>";
	cRenderCards::body_end();
cRenderCards::card_end();

$iRuleCount = 0;
foreach ($aRules as $oRule){
	cRenderCards::card_start("<a name='$iRuleCount'>rule: $oRule->allocationName</a>");
		cRenderCards::body_start();
			//cDebug::vardump($oRule);
			$sAllocID = $oRule->allocationId;
			echo "License Key:";
			cRenderW3::tag("$oRule->licenseKey");
			echo "<hr>";
			
			//----------------------------------------------------------------------
			$aPackages = $oRule->allocatedPackages;
			echo "Packages:<br>";
			foreach ($aPackages as $oPackage)
				cRenderW3::tag("$oPackage->packageName : $oPackage->allocatedUnits");
			echo "<hr>";
			
			//----------------------------------------------------------------------
			$aFilters = $oRule->filters;
			if (count($aFilters) ==0)
				echo "Applies to all agents";
			else{
				uasort($aFilters, "filter_sort");
				echo "Filters:<br>";
				foreach ($aFilters as $oFilter){
					$sEntity = 	$oFilter->entityName;
					$sOperator = $oFilter->operator;
					if ($sOperator === "ID_EQUALS") $sOperator = "=";
					cRenderW3::tag ("$oFilter->type <i>$sOperator</i> $sEntity");
				}
			}
			echo "<hr>";
			cDebug::flush();
			
			//----------------------------------------------------------------------
			echo "connected agents:<br>";
			$aAllocHosts = cADRestUI::GET_allocationHosts($sAllocID);
			$aHosts = [];
			foreach ($aAllocHosts as $oHost)
				$aHosts[] = $oHost->hostId;
				
			if (count($aHosts) == 0)
				echo "<b>No connected hosts found!</b>";
			else{
				$aUsage = 	cADRestUI::GET_license_usage($sAllocID, $aHosts);
				$aAnalysed = cADAnalysis::analyse_license_usage($aUsage);
				$aKeys = array_keys($aAnalysed);

				$iMax = 0;
				?><table class="maintable" border="1">
					<tr><?php
						foreach ($aKeys as $sKey){
							echo "<th width='33%'>";
								echo "$sKey ";
								cRenderW3::tag("".count($aAnalysed[$sKey])." agents");
							echo "</th>";
						}
					?></tr>
					<tr><?php
						foreach ($aKeys as $sKey){
							echo "<td valign='top'><font size='-1'>";
								foreach ($aAnalysed[$sKey] as $sHostID)
									echo "$sHostID, ";
							echo "</font></td>";
						}
					?></tr>
				</table><?php
			}
			
		cRenderCards::body_end();
	cRenderCards::card_end();	
	$iRuleCount ++;
}


cRenderHtml::footer();
?>
