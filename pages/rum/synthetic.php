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
require_once("../../inc/root.php");
cRoot::set_root("../..");

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
require_once("$phpinc/appdynamics/common.php");
require_once("$root/inc/inc-charts.php");
require_once("$root/inc/inc-secret.php");
require_once("$root/inc/inc-render.php");


//-----------------------------------------------
$oApp = cRenderObjs::get_current_app();
$sAppQS = cRenderQS::get_base_app_QS($oApp);


//####################################################################
cRenderHtml::header("Web browser - Synthetics");
cRender::force_login();

$title ="$oApp->name&gt;Web Real User Monitoring&gt;Synthetic Jobs";
cRender::show_time_options( $title); 
$oTimes = cRender::get_times();

cRenderMenus::show_apps_menu("Show Synthetics for:", "synthetic.php");
cRender::button("All Synthetics", "$home/pages/all/allsynth.php");
cRender::appdButton(cAppDynControllerUI::webrum_synthetics($oApp, $oTimes));

//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************

//####################################################################
?><h2>Synthetics</h2>

<script src="<?=$jsinc?>/uri-parser/parse.js"></script>
<script src="<?=$home?>/js/widgets/synthetics.js"></script>
<div id="container">Loading Synthetic data...</div>
<script>
$( function(){
	$("#container").appdsyntimeline({
		home:"<?=$home?>",
		jobs_metric:"<?=cAppDynWebRumMetric::jobs()?>"
	});
})
	
</script>

<?php
cRenderHtml::footer();
?>
