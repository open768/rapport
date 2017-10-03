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


//####################################################################
cRender::html_header("All Applications - Databases");
cRender::force_login();
?>
	<script type="text/javascript" src="js/remote.js"></script>
	
<?php
cChart::do_header();
cChart::$width=cRender::CHART_WIDTH_LARGE;


//####################################################################
cRender::show_time_options( "All Applications - Time in Databases"); 
cRender::button("back to apps", "apps.php",false);
cRender::button("Events", "events.php?".cRender::APP_QS."=".cAppDynCore::DATABASE_APPLICATION."&".cRender::APP_ID_QS."=null");
cRender::button("All RUM Page Requests", "all.php?".cRender::METRIC_TYPE_QS."=".cRender::METRIC_TYPE_RUMCALLS,false);
cRender::button("All RUM Response times", "all.php?".cRender::METRIC_TYPE_QS."=".cRender::METRIC_TYPE_RUMRESPONSE,false);
cRender::button("All Application Response times", "all.php?".cRender::METRIC_TYPE_QS."=".cRender::METRIC_TYPE_TRANSRESPONSE,false);
echo "All Time in Databases";
cRender::appdButton(cAppDynControllerUI::databases());


//####################################################################
$oResponse = cAppDyn::GET_Databases();
if (count($oResponse) == 0){
	?>
		<div class='maintable'>No Monitored Databases found</div>
	<?php
}
else{
	?>
		<table class="maintable">
		<?php	
			//display the results
			foreach ( $oResponse as $oDB){
				$class=cRender::getRowClass();
				$sDB=$oDB->name;
				$sMetric = cAppDynMetric::databaseTimeSpent($sDB);

				echo "<tr>";
					echo "<td class='$class'>";
						cRender::button($sDB, "db.php?".cRender::DB_QS."=$sDB", false);
						echo "<br>";
						cChart::add($sDB, $sMetric, cAppDynCore::DATABASE_APPLICATION, 200);
					echo "</td>";
				echo "</tr>";
			}
		?>
		</table>
	<?php
	}
	cChart::do_footer("chart_getUrl", "chart_jsonCallBack");

	cRender::html_footer();
?>
