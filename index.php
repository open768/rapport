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
$home=".";
$root=realpath($home);
$phpinc = realpath("$root/../phpinc");
$jsinc = "$home/../jsinc";

require_once("$phpinc/ckinc/debug.php");
require_once("$phpinc/ckinc/header.php");
require_once("$phpinc/ckinc/session.php");
require_once("$phpinc/ckinc/common.php");
require_once("$phpinc/ckinc/http.php");
require_once("$phpinc/ckinc/header.php");
require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$phpinc/appdynamics/common.php");

require_once("$root/inc/inc-charts.php");
require_once("$root/inc/inc-charts.php");
require_once("$root/inc/inc-secret.php");
require_once("$root/inc/inc-render.php");
	
cSession::set_folder();
session_start();
cAppDynCredentials::clear_session();
cDebug::check_GET_or_POST();

//####################################################################
if (cHeader::get(cLogin::KEY_SUBMIT))
{
	cDebug::write("submit seen");
	$oCred = new cAppDynCredentials();
	try{
		$oCred->load_from_header();
	}	
	catch (Exception $e)
	{
		cRender::html_header("unable to login");
		$sError = $e->getMessage();
		cRender::show_top_banner("Unable to Login !"); 
		cRender::errorbox($sError);
		try{
			cRender::button("Back to login", "index.php", false);
		} catch (Exception $e){}
		exit;
	}
	
	//---------- where are we going
	$sReferrer = cHeader::get(cLogin::KEY_REFERRER);
	$sIgnoreReferrer = cHeader::get(cRender::IGNORE_REF_QS);
	$sLocation = "$home/pages/all/all.php";

	if ($sReferrer && !$sIgnoreReferrer){
		$aUrl = parse_url($sReferrer);
		if ( $aUrl["host"] == $_SERVER["SERVER_NAME"])
			$sLocation = $sReferrer;
	}
	
	if (cDebug::is_debugging()) 
		$sLocation = cHttp::build_url($sLocation, "debug");
	
	//----------- redirect
	cHeader::redirect($sLocation);
	exit();
}else if (cHeader::get(cRender::LOGIN_TOKEN_QS)){
	cDebug::write("token seen");
	try{
		$sToken = cHeader::get(cRender::LOGIN_TOKEN_QS);
		cAppDynCredentials::login_with_token($sToken);
	}	
	catch (Exception $e)
	{
		cRender::html_header("unable to login");
		$sError = $e->getMessage();
		cRender::show_top_banner("Unable to Login !"); 
		cRender::errorbox($sError);
		cRender::button("Back to login", "$home/index.php", false);
		exit;
	}
	cHeader::redirect("$home/pages/all/all.php");
	exit();
	
}else{
	cRender::html_header("login");
	?>
		<table width="100%" height="75%"><tr><td align="center" valign="middle">
			<form method="GET" action="index.php">
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
				<?php
					if (cDebug::is_debugging()){
						?><input type="hidden" name="<?=cDebug::DEBUG_STR?>" value="1"><?php
					}
					if (cDebug::is_extra_debugging()){
						?><input type="hidden" name="<?=cDebug::DEBUG2_STR?>" value="1"><?php
					}
				?>
			</form>
			<p>
			<div class="recommend">
				No login credentials are stored by this application.
				<p>
				please ensure that a local user with limited access rights is created in your controller to provide access using this application.
				<p>
				<form action='index.php' method='POST'>
					<input type='hidden' name='a' value='demo'>
					<input type='hidden' name='u' value='demo'>
					<input type='hidden' name='p' value='d3m0'>
					<input type='hidden' name='h' value='<?=cAppDynCore::DEMO_HOST?>'>
					<input type='hidden' name='ss' value='no'>
					<input type='submit' name='s' value='See this application in Demo Mode' class="blue_button">
				<?php
					if (cDebug::is_debugging()){
						?><input type="hidden" name="<?=cDebug::DEBUG_STR?>" value="1"><?php
					}
					if (cDebug::is_extra_debugging()){
						?><input type="hidden" name="<?=cDebug::DEBUG2_STR?>" value="1"><?php
					}
				?>
				</form>
			</div>
		</td></tr></table>
	<?php
	cRender::html_footer();
}
?>
