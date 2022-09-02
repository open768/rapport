<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2021 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED**************************************************************************/

//####################################################################
$home="..";
require_once "$home/inc/common.php";

//####################################################################
$sLoginToken = cHeader::get(cRenderQS::LOGIN_TOKEN_QS);
$sUsageLink = cCommon::filename()."?".cRenderQS::HELP_QS;
if (cHeader::is_set(cRenderQS::HELP_QS)){
	cRenderHtml::header("help");
	cRenderCards::card_start("Usage");
		cRenderCards::body_start();
		//*********************** USAGE *******************************************************************************************
		?>
			<code>
				Usage: <?=cHeader::get_page_url()?>?&lt;Parameters&gt;
				<dl>
					<dt><b>mandatory parameters: </b></dt>
					<dd><table border=1>
						<tr><th>Parameter</th><th>Description</th>
						<tr><th><?=cRenderQS::LOGIN_TOKEN_QS?>=...</th><td>login token, obtain from main menu</td></tr>
						<tr><th><?=cRenderQS::APPS_QS?>=...</th><td>comma separated list of app ids or names</td></tr>
					</table><p></dd>
					<dt><b>Optional parameters: </b></dt>
					<dd><table border=1>
						<tr><th>Parameter</th><th>Description</th>
						<tr><th><?=cRenderQS::WIDGET_NO_DETAIL_QS?></th><td>dont show details - set to 1 to only show application health</td></tr>
					</table></dd>
				</dl>
			</code>
		<?php
		cRenderCards::body_end();
	cRenderCards::card_end();
	cRenderHtml::footer();
}else{
	//*********************** WIDGET ****************************************************************************
	if (!$sLoginToken)
		cDebug::Error("no login token present please see <a target='usage' href='$sUsageLink'>usage</a>");
	else{
		$sApps = cHeader::get(cRenderQS::APPS_QS);
		if (!cCommon::is_string_set($sApps))	cDebug::Error("must give a list of apps. See <a target='usage' href='$sUsageLink'>usage</a>");
		
		$aApps = explode(",",$sApps);
		if (gettype($aApps) !== "array")	cDebug::error("no list of apps found");
		
		cRenderHtml::widget_header();
		?>
			<script language="javascript" src="<?=$jsWidgets?>/appsummary.js"></script>
			<div 
				id='widget' 
				<?=cRenderQS::LOGIN_TOKEN_QS?>="<?=cHeader::get(cRenderQS::LOGIN_TOKEN_QS)?>"
				<?=cRenderQS::APPS_QS?>="<?=cHeader::get(cRenderQS::APPS_QS)?>"
				<?=cRenderQS::HOME_QS?>="<?=$home?>"
				<?=cRenderQS::CONTROLLER_URL_QS?>="<?=cADCore::GET_controller()?>"
			>
					Loading please wait
			</div>
			<script language="javascript">
				function init_widget(){
					$("#widget").adappsummary(); //need to work with RUM too
				}
				
				$( init_widget);
			</script>
		<?php
		cRenderHtml::widget_footer();
	}
}
?>
