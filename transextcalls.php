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
//####################################################################
$root=realpath(".");
$phpinc = realpath("$root/../phpinc");
$jsinc = "../jsinc";

require_once("$phpinc/ckinc/debug.php");
require_once("$phpinc/ckinc/session.php");
require_once("$phpinc/ckinc/common.php");
require_once("$phpinc/ckinc/http.php");
require_once("$phpinc/ckinc/header.php");
	
cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();

//####################################################################
require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$phpinc/appdynamics/common.php");
require_once("inc/inc-charts.php");
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");


set_time_limit(200); // huge time limit as this takes a long time

	
//display the results
$app = cHeader::get(cRender::APP_QS);
$tier = cHeader::get(cRender::TIER_QS);
$trid = cHeader::get(cRender::TRANS_ID_QS);
$trans = cHeader::get(cRender::TRANS_QS);

$gsTier = cRender::get_base_tier_qs();

//####################################################################
cRender::html_header("Transaction external calls");
cRender::force_login();


cRender::show_time_options("transaction external calls"); 

$sBaseUrl = cHttp::build_url("tiertrans.php",$gsTier );
$sBaseUrl = cHttp::build_url($sBaseUrl,cRender::TRANS_QS, $trans );
$sBaseUrl = cHttp::build_url($sBaseUrl,cRender::TRANS_ID_QS , $trid);

$sTierUrl="tiertrans.php?$baseQuery";

$oResponse =cAppdyn::GET_transExtCalls($app, $tier, $trans);
?>

<h3>external calls from <?=$trans?> in tier <a href="<?=$sTierUrl?>"><?=$tier?></a> in <?=$app?></h3>
<table class="maintable">
	<tr>
		<th>other trans</th>
		<th>Metric Path</th>
		<th>Calls Per Min</th>
	</tr>
	<?php
	foreach ( $oResponse as $oItem){
		if (sizeof($oItem->calls) >0){
			$oData = array_pop($oItem->calls);
			$sMetricPath = $oData->metricPath;
			$url = 	cAppDynCore::GET_controller();

			$oValue = array_pop($oData->metricValues);
			if ($oValue)
				$iValue = $oValue->value ;
			else
				$iValue = "";
			
			?>
				<tr>
					<td align="top"><?=$oItem->trans2?></td>
					<td align="top"><?=$sMetricPath?></td>
					<td><?=$iValue?></td>
				</tr>
			<?php
		}
	}
	?>
</table>
<?php
cRender::html_footer();
?>
