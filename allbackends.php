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
$title ="All Remote Services";
cRender::show_time_options( $title); 

//####################################################################
$oApps = cAppDyn::GET_Applications();
cRender::button("Sort by Backend Name", "allbackendsbyname.php");
	//********************************************************************
	if (cAppdyn::is_demo()){
		cRender::errorbox("function not support ed for Demo");
		exit;
	}
	//********************************************************************

?>

<h2><?=$title?></h2>
<ul><?php
	foreach ($oApps as $oApp){
		?><li><a href="#<?=$oApp->id?>"><?=$oApp->name?></a><?php
	}
?></ul><?php

//####################################################################
foreach ($oApps as $oApp){
	$sApp = $oApp->name;
	$sID = $oApp->id;
	$sUrl = cHttp::build_url("backends.php", cRender::APP_QS, $sApp);
	$sUrl = cHttp::build_url($sUrl, cRender::APP_ID_QS, $sID);
	
	?><div class="<?=cRender::getRowClass();?>">
		<a name="<?=$sID?>"><?=cRender::button("$oApp->name", $sUrl);?></a>
		<?php
			$aBackends = cAppdyn::GET_Backends($sApp);
			$aMetrics = [];
			foreach ($aBackends as $oItem){
				$sMetric = cAppDynMetric::backendResponseTimes($oItem->name);
				$aMetrics[] = [cChart::LABEL=>"Backend Response Times: $oItem->name", cChart::METRIC=>$sMetric];
			}
			cChart::metrics_table($oApp, $aMetrics, 3, cRender::getRowClass());
		?>
	</div><?php
}
cChart::do_footer();
cRender::html_footer();
?>
