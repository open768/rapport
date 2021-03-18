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
$home="../..";
require_once "$home/inc/common.php";
require_once "$root/inc/inc-charts.php";



//####################################################################
cRenderHtml::header("Dashboards");
cRender::force_login();
cChart::do_header();

//####################################################################
$sUsage = cHeader::get(cRender::USAGE_QS);
if (!$sUsage) $sUsage = 1;
cRender::show_top_banner("Dashboards"); 
?>
<h2>Dashboards</h2>
not implemented 
<ul>
	<li>drag and drop interface_exists
	<li>pick any metrics
	<li>many types of representation (Kibana?)
	<ul>
		<li>GRaphs
		<li>charts
		<li>traffic lights etc
		<li>use rules to display something
	</ul>
	<li>reuse with different applications/tiers/etc
	<li>share dashboards
	<li>copy dashboards
</ul>
<?php
cRenderHtml::footer();
?>
