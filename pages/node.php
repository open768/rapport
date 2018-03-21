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
$root=realpath(".");
$phpinc = realpath("$root/../phpinc");
$jsinc = "../jsinc";

require_once("$phpinc/ckinc/debug.php");
require_once("$phpinc/ckinc/session.php");
require_once("$phpinc/ckinc/common.php");
require_once("$phpinc/ckinc/header.php");
require_once("$phpinc/ckinc/http.php");
	
cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();

//####################################################################

require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$root/inc/inc-charts.php");
require_once("$root/inc/inc-secret.php");
require_once("$root/inc/inc-render.php");


cRender::html_header("Applications");
cRender::force_login();
$oApp = cRender::get_current_app();
$node = cHeader::get(cRender::NODE_QS);
$nodeID = cHeader::get(cRender::NODE_ID_QS);
$sAppQS = cRender::get_base_app_QS();

cRender::show_top_banner("Node details: $node"); 
cRender::button("Back to all nodes", "appagents.php?$sAppQS");
cRender::appdButton(cAppDynControllerUI::nodeDashboard($oApp->id,$nodeID));

//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRender::html_footer();
	exit;
}
//********************************************************************

?>
<h1>UNDER CONSTRUCTION</h1>

<?php
//####################################################################
$oResult = cAppDynRestUI::GET_Node_details($oApp->id, $nodeID);

//####################################################################
cRender::html_footer();
?>
