<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2016 

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
require_once("inc/inc-charts.php");
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");


cRender::html_header("Applications");
cRender::force_login();
$app = cHeader::get(cRender::APP_QS);
$aid = cHeader::get(cRender::APP_ID_QS);
$node = cHeader::get(cRender::NODE_QS);
$nodeID = cHeader::get(cRender::NODE_ID_QS);

cRender::show_top_banner("Node details: $node"); 
cRender::button("Back to all nodes", "appnodes.php?".cRender::APP_QS."=$app&".cRender::APP_ID_QS."=$aid");
cRender::appdButton(cAppDynControllerUI::nodeDashboard($aid,$nodeID));

?>
<h1>UNDER CONSTRUCTION</h1>

<?php
//####################################################################
$oResult = cAppDyn::GET_Node_details($aid, $nodeID);

//####################################################################
cRender::html_footer();
?>
