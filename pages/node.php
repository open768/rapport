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
$home="..";
require_once "$home/inc/common.php";
require_once "$root/inc/charts.php";

//####################################################################
require_once("$root/inc/charts.php");


cRenderHtml::header("Applications");
cRender::force_login();
$oApp = cRenderObjs::get_current_app();
$node = cHeader::get(cRender::NODE_QS);
$nodeID = cHeader::get(cRender::NODE_ID_QS);
$sAppQS = cRenderQS::get_base_app_QS($oApp);

cRender::button("Back to all nodes", "appagents.php?$sAppQS");
cADCommon::button(cADControllerUI::nodeDashboard($oApp,$nodeID));

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************

?>
<h1>UNDER CONSTRUCTION</h1>

<?php
//####################################################################
$oResult = cAD_RestUI::GET_Node_details($oApp->id, $nodeID);

//####################################################################
cRenderHtml::footer();
?>
