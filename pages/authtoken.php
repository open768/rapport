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
$root=realpath(".");
$phpinc = realpath("$root/../phpinc");
$jsinc = "../jsinc";

require_once("$phpinc/ckinc/debug.php");
require_once("$phpinc/ckinc/session.php");
require_once("$phpinc/ckinc/common.php");
require_once("$phpinc/ckinc/http.php");
require_once("$root/../inc/secret.php");
	
cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();

//####################################################################

require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$root/inc/inc-secret.php");
require_once("$root/inc/inc-render.php");
require_once("$root/inc/inc-link.php");


//####################################################################
cRender::html_header("AuthToken");
cRender::force_login();

cRender::show_top_banner("AuthToken"); 
cAppDynCredentials::$encryption_key
$sToken = cAppDynCredentials::get_login_token();
$sURL = cHeader::get_server().dirname($_SERVER["SCRIPT_NAME"])."/index.php";
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
cRender::html_footer();
?>
