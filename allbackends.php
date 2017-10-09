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
require_once("$phpinc/appdynamics/common.php");
require_once("inc/inc-charts.php");
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");


//-----------------------------------------------

//####################################################################
cRender::html_header("All Remote Services");
cRender::force_login();
?>
	<script type="text/javascript" src="<?=$jsinc?>/bean/bean.js"></script>
	<script type="text/javascript" src="js/remote.js"></script>
	
	
<?php
cChart::do_header();
cChart::$width=cRender::CHART_WIDTH_LARGE;

$title ="All Remote Services";
cRender::show_time_options( $title); 

//####################################################################
$oApps = cAppDyn::GET_Applications();
cRender::button("Sort by Backend Name", "allbackendsbyname.php");
?>

<h2><?=$title?></h2>
<ul>
<?php
	foreach ($oApps as $oApp){
		$sApp = $oApp->name;
		$sID = $oApp->id;
		?>
		<li><a href="#<?=$sID?>"><?=$sApp?></a>
		<?php
	}?>
</ul>
<?php
//####################################################################
foreach ($oApps as $oApp){
	$sApp = $oApp->name;
	$sID = $oApp->id;
	$sUrl = cHttp::build_url("backends.php", cRender::APP_QS, $sApp);
	$sUrl = cHttp::build_url($sUrl, cRender::APP_ID_QS, $sID);
	?>
		<div class="<?=cRender::getRowClass();?>">
		<a name="<?=$sID?>"><?=cRender::button("$oApp->name", $sUrl);?></a>
		<table class="maintable"><?php
			$aBackends = cAppdyn::GET_Backends($sApp);
			foreach ($aBackends as $oItem){
				$sMetric = cAppDynMetric::backendResponseTimes($oItem->name);
				?>
					<tr class="<?=cRender::getRowClass()?>"><td><?php 
						cChart::add("Backend Response Times: $oItem->name", $sMetric, $sApp, 100);
					?></td></tr>
				<?php
			}
		?></table>
	<?php
}

	cChart::do_footer("chart_getUrl", "chart_jsonCallBack");
	cRender::html_footer();
?>
