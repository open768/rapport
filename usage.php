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
	
<?php
cChart::do_header();

//####################################################################
$sUsage = cHeader::get(cRender::USAGE_QS);
if (!$sUsage) $sUsage = 1;
cRender::show_top_banner("License Usage for $sUsage month(s)"); 

//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRender::html_footer();
	exit;
}
//********************************************************************

?>
<h2>License Usage</h2>
login account needs administrator role for this to work
<p>
<select id="menuTime">
	<option selected disabled>Show Licenses for</option>
	<?php
		foreach ([1,2,3,4,5,6,12] as $iOption ){
			$sDisabled = ($sUsage == $iOption?"disabled":"");
			?>
				<option <?=$sDisabled?> value="<?=cHttp::build_url("usage.php",cRender::USAGE_QS,$iOption)?>"><?=$iOption?> Months</option>
			<?php
		}
	?></optgroup>
</select>

<script language="javascript">
$(  function(){
		$("#menuTime").selectmenu({change:common_onListChange,width:300});
} );
</script><?php

//####################################################################
?>
<?php
$oMods=cAppDynAccount::GET_license_modules();
$aMods = $oMods->modules;
sort ($aMods);
$aMetrics = [];
foreach ($aMods as $oModule)
	$aMetrics[] = [cChart::LABEL=>$oModule->name, cChart::METRIC=>cAppdynMetric::moduleUsage($oModule->name, $sUsage)];

$sClass=cRender::getRowClass();
cChart::metrics_table(cAppDApp::$null_app, $aMetrics,3, $sClass, null);

cChart::do_footer();
cRender::html_footer();
?>
