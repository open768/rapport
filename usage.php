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
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");
	
cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();

//####################################################################

require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$phpinc/appdynamics/metrics.php");
require_once("inc/inc-charts.php");
require_once("$phpinc/appdynamics/account.php");


//####################################################################
cRender::html_header("License Usage");
cRender::force_login();
?>
	<script type="text/javascript" src="js/remote.js"></script>
	<script type="text/javascript" src="js/chart.php"></script>
<?php
cChart::do_header();
cChart::$width=940;
cChart::$json_data_fn = "chart_getUrl";
cChart::$json_callback_fn = "chart_jsonCallBack";
cChart::$csv_url = "rest/getMetric.php";
cChart::$zoom_url = "metriczoom.php";
cChart::$save_fn = "save_fave_chart";
cChart::$compare_url = "compare.php";

cChart::$metric_qs = cRender::METRIC_QS;
cChart::$title_qs = cRender::TITLE_QS;
cChart::$app_qs = cRender::APP_QS;
cChart::$showCompare = false;

//####################################################################
$sUsage = cHeader::get(cRender::USAGE_QS);
if (!$sUsage) $sUsage = 1;
cRender::show_top_banner("License Usage for $sUsage month(s)"); 


?><select id="menuTime">
	<option selected disabled>Show Licenses for</option>
	<?php
		foreach ([1,2,3,6,12] as $iOption ){
			$sDisabled = ($sUsage == $iOption?"disabled":"");
			?>
				<option <?=$sDisabled?> value="<?=cHttp::build_url("usage.php",cRender::USAGE_QS,$iOption)?>"><?=$iOption?> Months</option>
			<?php
		}
	?></optgroup>
</select>

<script language="javascript">
$(  
	function(){
		$("#menuTime").selectmenu({change:common_onListChange});
	}  
);
</script><?php


//####################################################################
$oMods=cAppDynAccount::GET_license_modules();
$aMods = $oMods->modules;
//cDebug::vardump($aMods,true);
?>
	<h2>License Usage</h2>
	<h3>login account needs administrator role for this to work</h3>
	<table class="maintable"><tr><td>
	<?php
		foreach ($oMods->modules as $oModule){
			$sModule = $oModule->name;
			$class=cRender::getRowClass();

			echo "<tr class='$class'><td>";
				cChart::add("Usage for $sModule", cAppDynMetric::USAGE_METRIC."/$sModule/$sUsage", null, 200);
			echo "</td></tr>";
		}
	?>
	</td></tr></table><?php

cChart::do_footer();
cRender::html_footer();
?>
