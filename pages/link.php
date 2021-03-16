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
require_once("../inc/root.php");
cRoot::set_root("..");
require_once("$root/inc/common.php");

//####################################################################
require_once("$root/inc/inc-link.php");



//####################################################################
$sGoKey = cHeader::get(cLinkPage::GO_QS);
if ($sGoKey){
	$oLinkData = cLinkPage::get_obj($sGoKey);
	
	//perform the login
	$oLinkData->credentials->save();
	
	//Set the time window
	if (property_exists($oLinkData,"time_window")){
		cDebug::write("setting time window");
		$_SESSION[cAppDynCommon::TIME_SESS_KEY] = cAppDynCommon::TIME_CUSTOM ;
		$_SESSION[cAppDynCommon::TIME_CUSTOM_FROM_KEY] = $oLinkData->time_window->start ;
		$_SESSION[cAppDynCommon::TIME_CUSTOM_TO_KEY] = $oLinkData->time_window->end;
	}
	
	//then redirect
	$sPage = $oLinkData->page;
	cHeader::redirect($sPage);
	exit;
}

//####################################################################
cRenderHtml::header("link");
cRender::force_login();

cRender::show_top_banner("link"); 
$sUrl = cLinkPage::get_referrer_link();
$sLinkID = cLinkPage::get_link_id();
$oLinkData = cLinkPage::get_obj($sLinkID);
$sPage = $oLinkData->page;
?>

<p>
<h2>Link to page</h2>
<table class="maintable"><TR><TD>
	Copy the following link:
	<p>
	<input type="text" value="<?=$sUrl?>" class="clipbox">
	<p>
	<?=cRender::button("Go back to page", $sPage)?>
</TD></TR></TABLE>
<?php
cRenderHtml::footer();
?>
