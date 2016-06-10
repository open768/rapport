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
require_once("$phpinc/ckinc/header.php");
require_once("$phpinc/ckinc/session.php");
require_once("$phpinc/ckinc/common.php");
require_once("$phpinc/ckinc/http.php");
require_once("$phpinc/ckinc/header.php");
require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$phpinc/appdynamics/common.php");

require_once("inc/inc-charts.php");
require_once("inc/inc-charts.php");
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");
	
cSession::set_folder();
session_start();
cAppDynCredentials::clear_session();
cDebug::check_GET_or_POST();

//####################################################################
class cLogin{
	const KEY_HOST = "h";
	const KEY_ACCOUNT = "a";
	const KEY_USERNAME = "u";
	const KEY_PASSWORD = "p";
	const KEY_HTTPS = "ss";
	const KEY_REFERRER = "r";
	const KEY_SUBMIT = "s";
}

//####################################################################

if (cHeader::get(cLogin::KEY_SUBMIT))
{
	$oCred = new cAppDynCredentials();
	$oCred->host = cHeader::get(cLogin::KEY_HOST);
	$oCred->account  = cHeader::get(cLogin::KEY_ACCOUNT);
	$oCred->username  = cHeader::get(cLogin::KEY_USERNAME);
	$oCred->password  = cHeader::get(cLogin::KEY_PASSWORD);
	
	$sUse_https = cHeader::get(cLogin::KEY_HTTPS);
	
	$oCred->use_https = ($sUse_https=="yes");
	try{
		$oCred->save();
	}
	
	catch (Exception $e)
	{
		$sError = $e->getMessage();
		cRender::show_top_banner("Unable to Login !"); 
		?>
			<p><!-- error was '<?=$sError?>' -->
			<div class='errorbox'>
				Oops there was a problem logging in
				<?=cRender::button("Back to login", "index.php", false);?>
			</div>
		<?php
		exit;
	}
	
	//---------- where are we going
	$sReferrer = cHeader::get(cLogin::KEY_REFERRER);
	$sIgnoreReferrer = cHeader::get(cRender::IGNORE_REF_QS);
	$sLocation = cHttp::build_url("all.php", cRender::METRIC_TYPE_QS, cRender::METRIC_TYPE_RESPONSE_TIMES);

	if ($sReferrer && !$sIgnoreReferrer){
		$aUrl = parse_url($sReferrer);
		if ( $aUrl["host"] == $_SERVER["SERVER_NAME"])
			$sLocation = $sReferrer;
	}
	
	if (cDebug::is_debugging()) 
		$sLocation = cHttp::build_url($sLocation, "debug");
	
	//----------- redirect
	if (! headers_sent ())
		header("Location:  $sLocation");
	else{
		?>
			<script src="<?=$jsinc?>/ck-inc/common.js"></script>
			<script>
				cBrowser.openWindow("<?=$sLocation?>", "apps");
			</script>
		<?php
	}
	exit();
}else{
	cRender::html_header("login");
	?>
		<form method="POST" action="index.php">
		<table width="100%" height="100%"><tr><td align="center" valign="middle">
			<table class="loginbox">
				<tr>
					<td colspan=3><div class="logotable">
						<table width="100%"><tr>
							<td align="left"><font class="logintext">Login</font></td>
							<td align="right"><span class="loginimage"></span></td>
						</tr></table>
					</div></td>
				</tr>
				<tr>
					<td align="right">Account:</td>
					<td><input type="text" name="<?=cLogin::KEY_ACCOUNT?>"></td>
				</tr>
				<tr>
					<td align="right">username:</td>
					<td><input type="text" name="<?=cLogin::KEY_USERNAME?>"></td>
				</tr>
				<tr>
					<td align="right">password:</td>
					<td><input type="password" name="<?=cLogin::KEY_PASSWORD?>"></td>
				</tr>
				<tr>
					<td align="right">Controller hostname:</td>
					<td><input type="text" name="<?=cLogin::KEY_HOST?>" size="40"></td>
				</tr>
				<tr>
					<td align="right">use https:</td>
					<td><select name="<?=cLogin::KEY_HTTPS?>">
						<option selected value="yes">yes</option>
						<option  value="no">no</option>
					</select></td>
				</tr>
				<tr><td colspan="2"><input type="submit" name="<?=cLogin::KEY_SUBMIT?>" class="blue_button"></td></tr>
			</table>
			<input type="hidden" name="<?=cLogin::KEY_REFERRER?>" value="<?=$_SERVER['HTTP_REFERER']?>">
			<input type="hidden" name="<?=cRender::IGNORE_REF_QS?>" value="<?=cHeader::get(cRender::IGNORE_REF_QS)?>">
		</td></tr></table>
		</form>
	<?php
	cRender::html_footer();
}
?>
