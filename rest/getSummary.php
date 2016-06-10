<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2016 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

$root=realpath("..");
$phpinc = realpath("$root/../phpinc");
require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$phpinc/appdynamics/common.php");

$duration = get_duration();
set_time_limit(200); // huge time limit as this could takes a long time

require_once("$phpinc/ckinc/debug.php");
require_once("$phpinc/ckinc/session.php");
require_once("$phpinc/ckinc/common.php");
require_once("$phpinc/ckinc/header.php");
require_once("../inc/inc-render.php");

cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();


//###################### DATA #############################################################
$app = cHeader::get(cRender::APP_QS);
$tier = cHeader::get(cRender::TIER_QS);
$trans = cHeader::get(cRender::TRANS_QS);
$index = cHeader::get("id"); //id of HTML div

$aResult = array( "id"=>$index);

$oResponse=cAppdyn::GET_TransResponse($app, $tier, $trans, "true");
if ($oResponse){
	$aResult["max"] = $oResponse[0];
	$oErrors=cAppdyn::GET_TransErrors($app, $tier, $trans, "true");
	if ($oErrors)
		$aResult["transErrors"] = $oErrors[0];
}else{
	$aResult["error"] = "no data";
}

echo json_encode($aResult);	
?>
