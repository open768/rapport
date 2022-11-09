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
cRenderHtml::header("configuration");
cRender::force_login();

//####################################################################
$sUsage = cHeader::get(cRenderQS::USAGE_QS);
if (!$sUsage) $sUsage = 1;

//####################################################################
function sort_config($a,$b){
	return strcasecmp($a->name, $b->name );
}
//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************
?>
	<style>
		table {table-layout:fixed;}
		table td {word-wrap:break-word}
	</style>
<?php

//----------------- get the config 
$aConfig=cADController::GET_configuration();

//----------------- sanitise the config 
uasort($aConfig,"sort_config");
cDebug::vardump($aConfig[0]);

//----------------- render  the config 
$sStyle = cRender::getRowClass();			
$sLastCh = "";

//cDebug::vardump($aConfig,true);
cRenderCards::card_start("Controller Configuration");
	cRenderCards::body_start();
		foreach ( $aConfig as $oItem){
			$sValue = str_replace( ",", ", ", $oItem->value);
			$sValue = str_replace( ",  ", ", ", $sValue);
			
			$sDesc = $oItem->description;
			$sCh = strtoupper(($oItem->name)[0]);
			if ($sCh !== $sLastCh){
				if ($sLastCh) {
					?></table><p><?php
				}
				?>
					<h3><a name="<?=$sCh?>"><?=$sCh?></a></h3>
					<table width="100%" border="1" cellspacing="0" cellpadding="2">
						<tr>
							<th width="300">Name</th>
							<th width="*">Value</th>
							<th width="300">Description</th>
						</tr>
				<?php
				$sLastCh = $sCh;
			} 
			
			if (stristr($sDesc,"password")) $sValue = "**********";
			$sDesc = str_replace( ".", " ", $sDesc);
			?><tr>
				<td valign='top'><?=$oItem->name?></td>
				<td><font face="courier"><?=$sValue?></font></td>
				<td valign='top'><?=$sDesc?></td>
			</tr><?php
		}
		?></table><?php
	cRenderCards::body_end();
cRenderCards::card_end();

cRenderHtml::footer();
?>
