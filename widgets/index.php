<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2021 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED**************************************************************************/

//####################################################################
$home="..";
require_once "$home/inc/common.php";


//####################################################################
cRenderHtml::header("Widgets");

cRenderCards::card_start("Widgets");
	cRenderCards::body_start();
	?>
		we are providing some widgets that will be useful to other web applications.<p>
		To use the widgets you will need to pass a login token and parameters into the query string.
		
		<h3>Widgets</h3>
		<ul>
			<li><a href="appsummary.php">Application summary - shows the status of some or all applications</a>
			<li>Application detail
		</ul>
	<?php
	cRenderCards::body_end();
cRenderCards::card_end();


cRenderHtml::footer();
?>
