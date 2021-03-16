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
require_once("../../inc/root.php");
cRoot::set_root("../..");
require_once("$root/inc/common.php");


cRenderHtml::header("All Agent Versions");

//####################################################################
cRender::show_top_banner("Appdynamics Versions"); 

cRender::button("Back to Agent Versions", "allagentversions.php");	
cRender::button("AppDynamics Downloads", "https://download.appdynamics.com/download/");	
cRender::appdButton(cAppDynControllerUI::agents(), "Agent Settings");
?>
<h2>Latest Appdynamics Versions</h2>

<?php
//####################################################################
$aDownloads = cAppDynWebsite::GET_latest_downloads();
if (count($aDownloads) == 0){
	cRender::errorbox("DEBUG in progress");
}else{
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
}


//####################################################################
cRenderHtml::footer();
?>
 