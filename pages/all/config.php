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
$home = "../..";
$root=realpath($home);
$phpinc = realpath("$root/../phpinc");
$jsinc = "$home/../jsinc";

require_once("$phpinc/ckinc/debug.php");
require_once("$phpinc/ckinc/session.php");
require_once("$phpinc/ckinc/common.php");
require_once("$phpinc/ckinc/header.php");
require_once("$phpinc/ckinc/http.php");
require_once("$root/inc/inc-secret.php");
require_once("$root/inc/inc-render.php");
	
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

//####################################################################
$sUsage = cHeader::get(cRender::USAGE_QS);
if (!$sUsage) $sUsage = 1;

cRender::show_top_banner("Configuration"); 


//####################################################################
function sort_config($a,$b){
	return strcmp($a->description, $b->description );
}
//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRender::html_footer();
	exit;
}
//********************************************************************

$aConfig=cAppDynController::GET_configuration();
uasort($aConfig,"sort_config");
$sStyle = cRender::getRowClass();			

//cDebug::vardump($aConfig,true);
?>
<h2>Controller Configuration</h2>
	<table border="1" cellspacing=0 cellpadding=2>
		<tr class="<?=$sStyle?>">
			<th>Description</th>
			<th>Value</th>
		</tr>
		<?php
			foreach ( $aConfig as $oItem){
				$sValue = str_replace( ",", ", ", $oItem->value);
				$sValue = str_replace( ",  ", ", ", $sValue);
				
				$sDesc = $oItem->description;
				if (stristr($sDesc,"password")) $sValue = "**********";
				?><tr>
					<td valign='top'><?=$sDesc?></td>
					<td><font face="courier"><?=$sValue?></font></td>
				</tr><?php
			}
		?>
	</table><?php

cRender::html_footer();
?>
