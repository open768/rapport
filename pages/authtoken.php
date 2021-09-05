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
$home="..";
require_once "$home/inc/common.php";
require_once "$root/inc/charts.php";

//####################################################################
require_once("$root/inc/link.php");


//####################################################################
cRenderHtml::header("AuthToken");
cRender::force_login();

cRender::show_top_banner("AuthToken"); 
$sToken = cAppDynCredentials::get_login_token();
$sURL = cHeader::get_server().dirname($_SERVER["SCRIPT_NAME"])."/$home/index.php";
$sURL = cHttp::build_URL($sURL,cRender::LOGIN_TOKEN_QS, $sToken);
?>

<p>
<h2>Auth Token</h2>
<table class="maintable"><TR><TD>
	Copy the following link:
	<p>
	<input type="text" value="<?=$sURL?>" class="clipbox">
	<p>
	<?=cRender::button("Go back to page", cHeader::get_referer())?>
</TD></TR></TABLE>
<?php
cRenderHtml::footer();
?>
