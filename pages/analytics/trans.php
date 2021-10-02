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


//####################################################################
cRenderHtml::header("transaction Analytics");
cRender::force_login();

//####################################################################
cRenderCards::card_start();
	cRenderCards::body_start();
		cCommon::messagebox("work in progress");
	cRenderCards::body_end();
	cRenderCards::action_start();
		cADCommon::button(cADControllerUI::analytics_config(), "Analytics configuration");
		cRender::button("back to analytics","analytics.php");
		cRender::button("log analytics","log.php");
	cRenderCards::action_end();
cRenderCards::card_end();

cRenderHtml::footer();
?>
