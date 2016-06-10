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
require_once("$phpinc/ckinc/http.php");
require_once("$phpinc/ckinc/header.php");
	
cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();

//####################################################################
require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$phpinc/appdynamics/common.php");
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");
require_once("inc/inc-remote.php");


$duration = get_duration();

set_time_limit(200); // huge time limit as this takes a long time

//####################################################################
cRender::html_header("tier transactions");
cRender::force_login();
?>
	<script type="text/javascript" src="js/remote.js"></script>
	<script type="text/javascript" src="js/remote_data.js"></script>
<?php
cRemote::do_header();
	
//display the results
$app = cHeader::get(cRender::APP_QS);
$tier = cHeader::get(cRender::TIER_QS);
$aid = cHeader::get(cRender::APP_ID_QS);
$gsTierQs = cRender::get_base_tier_qs();

$title= "$app>$tier>transaction counts and avg response times(ms)";
cRender::show_time_options($title); 

//---------------------------------------------------------------
$oCred = cRender::get_appd_credentials();
if ($oCred->restricted_login == null)	cRender::show_tier_functions();

//---------------------------------------------------------------
//###############################################
$aTransactions =cAppdyn::GET_tier_transaction_names($app, $tier);
?>
<p>
<table class='maintable'>
	<tr>
		<th>Transaction</th>
		<th>errors</th>
		<th>Max Response Time</th>
		<th>average Response time</th>
	</tr>
<?php
	$iCount = 1;
	$sBaseUrl = cHttp::build_url("transdetails.php", $gsTierQs);
	foreach ( $aTransactions as $oRow){
		$class=cRender::getRowClass();
		$trid=$oRow->id;
		$trans=$oRow->name;
		$sURL=cHttp::build_url($sBaseUrl, cRender::TRANS_QS, $trans);
		$sURL=cHttp::build_url($sURL, cRender::TRANS_ID_QS, $trid);
		cRemote::add($app, $tier, $trans, $iCount);
		?>
			<tr id="R<?=$iCount?>">
				<td class="<?=$class?>"><a href="<?=$sURL?>"><?=$trans?></a></td>
				<td class="<?=$class?>"><span id="R<?=$iCount?>_err">?</span></td>
				<td class="<?=$class?>"><span id="R<?=$iCount?>_max">?</span></td>
				<td class="<?=$class?>"><span id="R<?=$iCount?>_avg">?</span></td>
			</tr>
		<?php
		$iCount++;
	}
echo "</table>";
cRemote::do_footer();

/*
//---------------------------------------------------------------
cCommon::flushprint( "<h2>Errors</H2>");
$oData = cAppdyn::GET_Tier_Errors($app, $tier);
echo "<TABLE border=1 cellpadding=0 cellspacing=0>";
echo "<TH width=300>Error</th><th width=100>count</th>";
foreach ( $oData as $oRow){
	$oAnalysis = $oRow->calls;
	echo "<TR><TD><font color=red>$oRow->name</font></TD><td>$oAnalysis->count</td></TR>";
}
echo "</TABLE>";
echo "<p>";
*/
cRender::html_footer();
?>
