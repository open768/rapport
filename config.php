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
require_once("$phpinc/appdynamics/account.php");


//####################################################################
cRender::html_header("configuration");
cRender::force_login();
?>
	<script type="text/javascript" src="js/remote.js"></script>
<?php

//####################################################################
$sUsage = cHeader::get(cRender::USAGE_QS);
if (!$sUsage) $sUsage = 1;

cRender::show_top_banner("Configuration"); 
cRender::button("Back to Apps", "apps.php", false);
cRender::button("License Usage", "usage.php", false);
echo "Configuration ";
cRender::button("All Browser RUM", "all.php?".cRender::METRIC_TYPE_QS."=".cRender::METRIC_TYPE_RUMCALLS, false);
cRender::button("All Time in Databases", "alldb.php",false);

function sort_config($a,$b){
	return strcmp($a->description, $b->description );
}

//####################################################################
$aConfig=cAppDyn::GET_configuration();
uasort($aConfig,"sort_config");

//cDebug::vardump($aConfig,true);
?>
	<table class="maintable" border="1" cellspacing=0 cellpadding=2>
		<tr>
			<th>Description</th>
			<th>Value</th>
		</tr>
		<?php
			foreach ( $aConfig as $oItem){
				$sValue = str_replace( ",", ", ", $oItem->value);
				$sValue = str_replace( ",  ", ", ", $sValue);
				$sDesc = $oItem->description;
				if (stristr($sDesc,"password")) $sValue = "**********";
				echo "<tr>";
					echo "<td valign='top'>$sDesc</td>";
					echo "<td>$sValue</td>";
				echo "</tr>";
			}
		?>
	</table><?php

cRender::html_footer();
?>
