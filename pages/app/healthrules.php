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

cRenderHtml::header("Health Rules for $oApp->name");
cRender::force_login();

//####################################################################
cRender::show_top_banner("Health rules");
$sUrl = cAppDynControllerUI::app_health_rules($oApp);
cDebug::extra_debug($sUrl);
cRender::appdButton($sUrl);

cRender::button("back to events", cHttp::build_url("events.php", cRender::APP_QS, $oApp->name));

//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************
	$aRules = $oApp->GET_HealthRules();
	cDebug::vardump($aRules);
	if (count($aRules) == 0){
		cRender::errorbox("no health rules found for this application");
		cRenderHtml::footer();
		exit;
	}

	//display widgets for health rules
	//they will be asynchronously fetched by the javascript using a http queue;
	cRender::messagebox("under construction - use debug2");
	cRenderHtml::footer();
?>
