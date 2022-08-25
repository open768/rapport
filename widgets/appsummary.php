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
				Usage: <?=cCommon::filename()?>?&lt;Parameters&gt;
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
		cADCredentials::login_with_token( $sLoginToken);
		$sApps = cHeader::get(cRenderQS::APPS_QS);
		if (!cCommon::is_string_set($sApps))	cDebug::Error("must give a list of apps. See <a target='usage' href='$sUsageLink'>usage</a>");
		
		$aApps = explode(",",$sApps);
		if (gettype($aApps) !== "array")	cDebug::error("no list of apps found");
		
		//build  list of app ids - regardless if a name was provided
		$sInApps =[];
		foreach ($aApps as $sInApp){
			$oApp= null;
			if(is_numeric($sInApp))
				$oApp = new cADApp(null, $sInApp);
			else
				$oApp = new cADApp($sInApp);
			$sInApps[$oApp->id] = true;
		}
		$aAppIds = array_keys($sInApps);
		
		$aDetails = cADRestUI::get_applications_status_from_ids($aAppIds, ["APP_OVERALL_HEALTH","CALLS","CALLS_PER_MINUTE","AVERAGE_RESPONSE_TIME","ERRORS_PER_MINUTE"]);
		//cDebug::vardump($aDetails);
		
		cRenderHtml::widget_header();
		?><div class="w3-container w3-light-grey w3-round-large"><table border="1" cellspacing="0" cellpadding="4">
			<tr>
				<th>Name</th>
				<th>Health</th>
				<th>Calls</th>
				<th>Response Time (ms)</th>
				<th>Errors</th>
			</tr><?php
			foreach ($aDetails as $oDetail){ ?>
				<tr>
					<td width="200" align="right"><?=$oDetail->name?></td>
					<td width="100"><?=$oDetail->severitySummary->performanceState?></td>
					<td width="100" align="right"><?=$oDetail->callsPerMinute?></td>
					<td width="100" align="right"><?=$oDetail->averageResponseTime?></td>
					<td width="100" align="right"><?=$oDetail->errorsPerMinute?></td>
				</tr>
			<?php }
		?></table></div><?php

		cRenderHtml::widget_footer();
	}
}
	


?>
