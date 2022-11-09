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



//####################################################################
cRenderHtml::$load_google_charts = true;
cRenderHtml::header("All Servers");
cRender::force_login(); 
cChart::do_header();
cChart::$hideGroupIfNoData = true;

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}

//####################################################################

cADCommon::button(cADControllerUI::servers());

//####################################################################
?>
<div id="page_content">
<?php
	cRender::button("MQ dashboard", "mq.php");	
?>
<?php

//####################################################################
cChart::do_footer();
cRenderHtml::footer();
?>
