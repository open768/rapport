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
require_once "$root/inc/charts.php";

//-----------------------------------------------
$oApp = cRenderObjs::get_current_app();
$sAppQS = cRenderQS::get_base_app_QS($oApp);


//####################################################################
cRenderHtml::$load_google_charts = true;
cRenderHtml::header("Web browser - Synthetics");
cRender::force_login();
?>
<script src="//cdnjs.cloudflare.com/ajax/libs/vis/4.21.0/vis.min.js"></script>
<link href="//cdnjs.cloudflare.com/ajax/libs/vis/4.21.0/vis.min.css" rel="stylesheet" type="text/css" />
<?php

$title ="$oApp->name&gt;Web Real User Monitoring&gt;Synthetic Jobs";

$oTimes = cRender::get_times();

cRenderMenus::show_app_change_menu("Show Synthetics for:");
cRender::button("All Synthetics", "$home/pages/all/allsynth.php");
cADCommon::button(cADControllerUI::webrum_synthetics($oApp, $oTimes));

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************

//####################################################################
?><h2>Synthetics for <?=$oApp->name?></h2>

<script src="<?=$js_extra?>/uri-parser/parse.js"></script>
<script src="<?=$jsWidgets?>/synthetics.js"></script>
<div id="container">Loading Synthetic data...</div>
<script>
$( function(){
	$("#container").appdsyntimeline({
		home:"<?=$home?>"
	});
})
	
</script>

<?php
cRenderHtml::footer();
?>
