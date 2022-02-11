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


set_time_limit(200); // huge time limit as this could takes a long time


//###################### DATA #############################################
$oApp = cRenderObjs::get_current_app();
$sHeathRuleID = cHeader::get(cRenderQS::HEALTH_ID_QS);

//*************************************************************************
cDebug::write("getting health rule detail  - $sHeathRuleID");
$oResult = $oApp->GET_HealthRuleDetail($sHeathRuleID);

//*************************************************************************
//* output
//*************************************************************************

cDebug::write("outputting json");
cCommon::write_json($oResult);	
return;
?>
