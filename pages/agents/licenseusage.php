<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2021 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/
//####################################################################
$home="../..";
require_once "$home/inc/common.php";
require_once "$root/inc/charts.php";

//####################################################################
cRenderHtml::header("License Usage");
cRender::force_login();
cChart::do_header();

//####################################################################
$sUsage = cHeader::get(cRenderQS::USAGE_QS);
if (!$sUsage) $sUsage = 1;

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************

?>
<h2>License Usage</h2>
<?php
try{
	$oMods=cADAccount::GET_license_modules();
}
catch (Exception $e){
	cCommon::errorbox("unable to get license details - check whether user has Site Owner role");
	cRenderHtml::footer();
	exit;	
}

cRenderCards::card_start("Licences");
	cRenderCards::action_start();
		cADCommon::button(cADControllerUI::licenses());
		?><p>
		<select id="menuTime">
			<option selected disabled>Show Licenses for</option>
			<?php
				foreach ([1,2,3,4,5,6,12] as $iOption ){
					$sDisabled = ($sUsage == $iOption?"disabled":"");
					?>
						<option <?=$sDisabled?> value="<?=cHttp::build_url("licenseusage.php",cRenderQS::USAGE_QS,$iOption)?>"><?=$iOption?> Months</option>
					<?php
				}
			?></optgroup>
		</select>

		<script language="javascript">
		$(  function(){
				$("#menuTime").selectmenu({change:common_onListChange,width:300});
		} );
		</script><?php
	cRenderCards::action_end();
cRenderCards::card_end();

//####################################################################

$aMods = $oMods->modules;
sort ($aMods);
$aMetrics = [];
cRenderCards::card_start();
	cRenderCards::body_start();
		foreach ($aMods as $oModule)
			$aMetrics[] = [cChart::LABEL=>$oModule->name, cChart::METRIC=>cADMetricPaths::moduleUsage($oModule->name, $sUsage)];

		cChart::render_metrics(null, $aMetrics,300);
	cRenderCards::body_end();
cRenderCards::card_end();

cChart::do_footer();
cRenderHtml::footer();
?>
