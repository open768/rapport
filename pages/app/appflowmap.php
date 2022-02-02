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

//####################################################################
cRenderHtml::header("Flowmap for Application $oApp->name");
cRender::force_login();
?><script src="https://d3js.org/d3.v7.min.js"></script><?php
cCommon::messagebox("work in progress");

cDebug::write("fetching flowmap");
cDebug::on(true);
$oData = $oApp->GET_flowmap();
cDebug::write("got data");
cDebug::vardump($oData, true);
cDebug::off();

cRenderHtml::footer();
?>