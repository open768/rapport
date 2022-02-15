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

cRenderHtml::header("AppDynamics Latest  Versions");

cRenderCards::card_start("Latest Agent Versions");
	cRenderCards::action_start();
		cRender::button("Agent Versions", "allagentversions.php");	
		cRender::button("AppDynamics Downloads", "https://download.appdynamics.com/download/");	
		cADCommon::button(cADControllerUI::agents(), "Agent Settings");
	cRenderCards::action_end();
cRenderCards::card_end();

//####################################################################
$aDownloads = cADWebsite::GET_latest_downloads();
if (count($aDownloads) == 0){
	cCommon::errorbox("nothing found!");
}else{
	cRenderCards::card_start();
	cRenderCards::body_start();

	?><table border="1" class="maintable">
		<tr>
			<th width="150">Title</th>
			<th width="400">Description</th>
			<th>download link</th>
		</tr><?php
		foreach ($aDownloads as $oItem){
			?><tr>
				<td width="150"><?=$oItem->title?></td>
				<td width="400"><?=$oItem->description?></td>
				<td align="middle"><a target="download" href="<?=$oItem->download_path?>"><?=$oItem->version?></a></td>
			</tr><?php
		}
		
	?></table><?php
	cRenderCards::body_end();
	cRenderCards::card_end();
}


//####################################################################
cRenderHtml::footer();
?>
 