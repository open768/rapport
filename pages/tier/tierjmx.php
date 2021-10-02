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


//choose a default duration


$CHART_IGNORE_ZEROS = false;

//####################################################################
cRenderHtml::header("tier JMX Database Pools");
cRender::force_login();
cChart::do_header();
cChart::$width=cChart::CHART_WIDTH_LARGE;

//####################################################################
// huge time limit as this takes a long time//display the results


//get passed in values
$oTier = cRenderObjs::get_current_tier();
$oApp = $oTier->app;
$node = cHeader::get(cRender::NODE_QS);
$gsMetric = cHeader::get(cRender::METRIC_TYPE_QS);

// show time options
$title = "$oApp->name&gt;$oTier->name&gt;Infrastructure&gt;JMX";

$showlink = cCommon::get_session($LINK_SESS_KEY);
if (!$oTier->name){
	cCommon::errorbox("no Tier parameter found");
	exit;
}
if (!$gsMetric){
	cCommon::errorbox("no Metric found");
	exit;
}

//stuff for later
$sBaseQS = cRenderQS::get_base_tier_QS($oTier);
$sBaseQS = cHttp::build_qs($sBaseQS, cRender::METRIC_TYPE_QS, $gsMetric);
$sBaseUrl = cHttp::build_url("tierjmx.php", $sBaseQS);


//####################################################################
//other buttons

$oCred = cRenderObjs::get_appd_credentials();

cDebug::flush();
$aNodes = $oTier->GET_Nodes();	
?><select id="menuNodes">
	<option selected disabled>Show Details for</option>
	<optgroup label="tiers"><?php
		$aTiers = $oApp->GET_Tiers();
		$sBaseTierQS = cRenderQS::get_base_app_QS($oApp);
		$sBaseTierQS = cHttp::build_qs($sBaseTierQS, cRender::METRIC_TYPE_QS, $gsMetric);
		$sBaseTierUrl = cHttp::build_url("tierjmx.php", $sBaseTierQS);
		
		foreach ($aTiers as $oTier){
			$sTierUrl = cHttp::build_url($sBaseTierUrl, cRender::METRIC_TYPE_QS, $gsMetric);
			$sTierUrl = cHttp::build_url($sTierUrl, cRender::TIER_QS, $oTier->name);
			$sTierUrl = cHttp::build_url($sTierUrl, cRender::TIER_ID_QS, $oTier->id);
			
			$bDisabled = (($oTier->name == $oTier->name) && ($node==null));
			$sDisabled = ($bDisabled?"disabled":"");
			?><option <?=$sDisabled?> value="<?=$sTierUrl?>"><?=cRender::show_name(cRender::NAME_TIER,$oTier)?> tier</option><?php
		}
	?></optgroup>
	<optgroup label="Individual Servers"><?php
		foreach ($aNodes as $oNode){
			$sNode = $oNode->name;
			?><option <?=(($sNode == $node)?"disabled":"")?> value="<?=cHttp::build_url($sBaseUrl, cRender::NODE_QS, $sNode)?>"><?=$sNode?></option><?php
		}
	?></optgroup>
</select>

<script language="javascript">
$(  
	function(){
		$("#menuNodes").selectmenu({change:common_onListChange});
	}  
);
</script><?php
if ($oCred->restricted_login == null) cRenderMenus::show_tier_functions();



//####################################################################
$aPools = $oTier->GET_JDBC_Pools($node);
if (count($aPools) == 0)
	cCommon::messagebox("No JDBC Pools Found");
else{
	?><h2>JDBC Pools for <?=cRender::show_name(cRender::NAME_TIER,$oTier)?></h2><?php
	if ($node){
		?><h3>(<?=$node?>) Node</h3><?php
	}
	?>
	<p>
	<table class="maintable"><?php
		foreach ($aPools as $oPool){
			$sPool = $oPool->name;
			?><tr class="<?=cRender::getRowClass()?>">
				<td><?=$sPool?></td>
				<td><?php
					$sMetric = cADMetric::InfrastructureJDBCPoolActive($oTier->name,$node, $sPool);
					cChart::add("active connections" , $sMetric, $oApp->name, 100);
				?></td>
				<td><?php
					$sMetric = cADMetric::InfrastructureJDBCPoolMax($oTier->name,$node, $sPool);
					cChart::add("Max connections" , $sMetric, $oApp->name, 100);
				?></td>
			</tr><?php
		}
	?></table><?php
}
cChart::do_footer();

cRenderHtml::footer();
?>
