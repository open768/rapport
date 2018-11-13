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
		cRenderHtml::header("unable to login");
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
		cRenderHtml::header("unable to login");
		$sError = $e->getMessage();
		cRender::show_top_banner("Unable to Login !"); 
		cRender::errorbox($sError);
		cRender::button("Back to login", "$home/index.php", false);
		exit;
	}
	cHeader::redirect("$home/pages/all/all.php");
	exit();
	
}else{
	cRenderHtml::header("login");
	?>
		<!-- Login Box -->

		<div class="mdl-card mdl-shadow--6dp login_box" >
			<div class="mdl-card__title-text mdl-color--blue-900 ">
				<span class="mdl-color-text--grey-50 login_title">
						Welcome to the Reporter for Appdynamics&trade;
				</span>
			</div>
			<div class="mdl-card__supporting-text">
				<form method="POST" action="index.php">
					<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
						<input class="mdl-textfield__input" type="text" name="<?=cLogin::KEY_ACCOUNT?>">
						<label class="mdl-textfield__label" for="<?=cLogin::KEY_ACCOUNT?>">Account...</label>
					</div>
					<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
						<input class="mdl-textfield__input" id="txtUsername" type="text" name="<?=cLogin::KEY_USERNAME?>">
						<label class="mdl-textfield__label" for="<?=cLogin::KEY_USERNAME?>">Username...</label>
						<div class="mdl-tooltip" for="txtUsername">
							to use this application we recommend creating a local user with limited access in your controller.
						</div>
					</div>
					<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
						<input class="mdl-textfield__input" id="txtpass" type="password" name="<?=cLogin::KEY_PASSWORD?>">
						<label class="mdl-textfield__label" for="<?=cLogin::KEY_PASSWORD?>">Password...</label>
						<div class="mdl-tooltip" for="txtpass">
							this password is not stored by this web application.
						</div>
					</div>
					<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
						<input class="mdl-textfield__input" type="text" name="<?=cLogin::KEY_HOST?>">
						<label class="mdl-textfield__label" for="<?=cLogin::KEY_HOST?>">Controller Hostname...</label>
					</div>
					<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
						<select name="<?=cLogin::KEY_HTTPS?>" class="mdl-textfield__input">
							<option selected value="yes">yes</option>
							<option  value="no">no</option>
						</select>
						<label class="mdl-textfield__label" for="<?=cLogin::KEY_HTTPS?>">use https:</label>
					</div>

					<?php
						if (cDebug::is_debugging()){
							?><input type="hidden" name="<?=cDebug::DEBUG_STR?>" value="1"><?php
						}
						if (cDebug::is_extra_debugging()){
							?><input type="hidden" name="<?=cDebug::DEBUG2_STR?>" value="1"><?php
						}
					?>
					<button id="btnlogin" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color-text--white login_submit" type="submit" value="1" name="<?=cLogin::KEY_SUBMIT?>">Login</button>
					<div class="mdl-tooltip" for="btnlogin">
						No login credentials are stored by this application, we really mean it
					</div>
				</form>
			</div>
			<div class="mdl-card__actions mdl-card--border">
				<form action="index.php" method="POST" >
					<input type="hidden" name="<?=cLogin::KEY_ACCOUNT?>" value="<?=cAppDynCredentials::DEMO_ACCOUNT?>">
					<input type="hidden" name="<?=cLogin::KEY_USERNAME?>" value="<?=cAppDynCredentials::DEMO_USER?>">
					<input type="hidden" name="<?=cLogin::KEY_PASSWORD?>" value="<?=cAppDynCredentials::DEMO_PASS?>">
					<input type="hidden" name="<?=cLogin::KEY_HOST?>" value="<?=cAppDynCore::DEMO_HOST?>">
					<input type="hidden" name="<?=cLogin::KEY_HTTPS?>" value="no">
					<button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect login_submit" id="demologin" name="<?=cLogin::KEY_SUBMIT?>" value="1" type="submit">Demo Mode</button>
					<div class="mdl-tooltip" for="demologin">
						look at our lovely features.
					</div>
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
		</div>

	<?php
	cRenderHtml::footer();
}
?>
