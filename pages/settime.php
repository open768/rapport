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
$sDuration = cHeader::get("duration");
$sUrl = cHeader::get("url");

$_SESSION[ cADCommon::TIME_SESS_KEY] = $sDuration;
cDebug::write("setting duration to $sDuration");
cDebug::write("setting Location to $sUrl");
header( "Location: $sUrl" ) ;
?>
