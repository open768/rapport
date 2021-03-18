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
$home="..";
require_once "$home/inc/common.php";
require_once "$root/inc/inc-charts.php";


$link = cCommon::get_session($LINK_SESS_KEY);
if ($link ==1 )
	$_SESSION[$LINK_SESS_KEY] = 0;
else
	$_SESSION[$LINK_SESS_KEY] = 1;

$sUrl = cHeader::get("url");
header( "Location: $sUrl" ) ;
?>
