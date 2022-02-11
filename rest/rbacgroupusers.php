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


//###################### DATA #############################################
$oGroup =  new cAD_RBAC_Group();
$oGroup->name = cHeader::GET(cRenderQS::GROUP_NAME_QS);
$oGroup->id = cHeader::GET(cRenderQS::GROUP_ID_QS);

//*************************************************************************
cDebug::write("getting group info for $oGroup->name");
$oGroup->get_security_provider_type();


cDebug::write("getting users for group $oGroup->name");
$oGroup->get_users();


//*************************************************************************
//* output
//*************************************************************************

cDebug::write("outputting json");
cCommon::write_json($oGroup);	
return;
?>
