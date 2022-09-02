<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2021 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

$home="..";
require_once "$home/inc/common.php";

//*************************************************************************
$sLoginToken = cHeader::get(cRenderQS::LOGIN_TOKEN_QS);
if (!$sLoginToken)	cDebug::Error("login token missing");
$sApps = cHeader::get(cRenderQS::APPS_QS);
if (!$sApps)	cDebug::Error("list of apps missing");

//login with the token
cADCredentials::login_with_token( $sLoginToken);
		
//build  list of app ids - regardless if a name was provided
$aApps = explode(",",$sApps);
if (gettype($aApps) !== "array")	cDebug::error("no list of apps found");
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

//get the summaries
$aDetails = cADRestUI::get_applications_status_from_ids($aAppIds, ["APP_OVERALL_HEALTH","CALLS","AVERAGE_RESPONSE_TIME","ERRORS"]);
//tbd cut down data returned to whats necessary
cDebug::vardump($aDetails);

//*************************************************************************
//* output
//*************************************************************************
cDebug::write("outputting json");
cCommon::write_json($aDetails);	
?>
