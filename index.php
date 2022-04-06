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
$home=".";
require_once "$home/inc/common.php";
	
cDebug::extra_debug("Page Initialising - started");
cSession::clear_session();
cDebug::check_GET_or_POST();
cDebug::extra_debug("Page Initialising - finished");

//####################################################################
function pr__show_error($psTitle, $psMessage){
	global $home;
	
	cRenderHtml::header($psTitle);
	
	cRenderCards::card_start($psTitle);
		cRenderCards::body_start();
			cCommon::errorbox("check login details: Error was: $psMessage");
		cRenderCards::body_end();
		cRenderCards::action_start();
			cRender::button("Back to login", "$home/index.php", false);
		cRenderCards::action_end();
	cRenderCards::card_end();
}

//####################################################################
if (cHeader::get(cADLogin::KEY_SUBMIT))
{
	//cDebug::on(true);
	cDebug::extra_debug("form submitted");
	$oCred = new cADCredentials();
	try{
		$oCred->load_from_header();
	}	
	catch (Exception $e)
	{
		pr__show_error("unable to login", $e->getMessage());
		exit;
	}
	
	//---------- where are we going
	$sReferrer = cHeader::get(cADLogin::KEY_REFERRER);
	$sIgnoreReferrer = cHeader::get(cRenderQS::IGNORE_REF_QS);
	$sLocation = cHeader::get(cRenderQS::LOCATION_QS);
	if (!$sLocation) 	$sLocation = "$home/pages/all/all.php";

	if ($sReferrer && !$sIgnoreReferrer){
		$aUrl = parse_url($sReferrer);
		if ( $aUrl["host"] == $_SERVER["SERVER_NAME"])
			$sLocation = $sReferrer;
	}
	
	if (!$oCred->is_logged_in && cCommon::is_string_set($oCred->jsessionid))
		$sLocation = "$home/pages/jsession/home.php";
	
	if (cDebug::is_debugging()) 
		$sLocation = cHttp::build_url($sLocation, "debug");
	
	//----------- redirect
	cHeader::redirect($sLocation);
	exit();
//#########################################################################
}else if (cHeader::get(cRenderQS::LOGIN_TOKEN_QS)){
	cDebug::extra_debug("token found ");
	try{
		$sToken = cHeader::get(cRenderQS::LOGIN_TOKEN_QS);
		cADCredentials::login_with_token($sToken);
	}	
	catch (Exception $e)
	{
		pr__show_error("unable to login with token", $e->getMessage());
		exit;
	}
	cHeader::redirect("$home/pages/all/all.php");
	exit();
	
//#########################################################################
}else{
	cDebug::extra_debug("showing login screen ");
	cRenderHtml::header("login");
	?>
		<!-- Login Box -->
		<script type="text/javascript" src="js/index.js"></script>

		<div class="mdl-card mdl-shadow--6dp login_box" >
			<div class="mdl-card__title-text mdl-color--blue-900 ">
				<span class="mdl-color-text--grey-50 login_title">
						Welcome to RAPPORT
				</span>
			</div>
			<div class="mdl-card__supporting-text" id="divCard">
				<form method="POST" action="index.php">
					<input type="hidden" name="<?=cRenderQS::LOCATION_QS?>" value="<?=cHeader::get(cRenderQS::LOCATION_QS)?>">
					
					<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
						<input class="mdl-textfield__input" id="<?=cADLogin::KEY_HOST?>" type="text" name="<?=cADLogin::KEY_HOST?>">
						<label class="mdl-textfield__label" for="<?=cADLogin::KEY_HOST?>">Controller Hostname...</label>
						<div class="mdl-tooltip" for="<?=cADLogin::KEY_HOST?>">
							usually &lt;account&gt;.saas.appdynamics.com
						</div>
					</div>
					<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
						<input class="mdl-textfield__input" id="<?=cADLogin::KEY_ACCOUNT?>" type="text" name="<?=cADLogin::KEY_ACCOUNT?>">
						<label class="mdl-textfield__label" for="<?=cADLogin::KEY_ACCOUNT?>">Account...</label>
						<div class="mdl-tooltip" for="<?=cADLogin::KEY_ACCOUNT?>">
							use the same account details as you would when logging into your controller.
						</div>
					</div>
					<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
						<input class="mdl-textfield__input" id="<?=cADLogin::KEY_USERNAME?>" type="text" name="<?=cADLogin::KEY_USERNAME?>">
						<label class="mdl-textfield__label" for="<?=cADLogin::KEY_USERNAME?>">Username...</label>
						<div class="mdl-tooltip" for="<?=cADLogin::KEY_USERNAME?>">
							to use this application we recommend creating a local user with limited access in your controller.
						</div>
					</div>
					<div class="w3-border w3-padding" id="tabs_container">
						<div class="w3-bar" id="tabs">
							<button class="w3-bar-item w3-button tabbut" id="BUTP" tab="TABP">Password</button>
							<button class="w3-bar-item w3-button tabbut" id="BUTA" tab="TABAT">API token</button>
							<button class="w3-bar-item w3-button tabbut" id="BUTS" tab="TABAS">API Secret</button>
							<button class="w3-bar-item w3-button tabbut" id="BUTJ" tab="TABJ">JSession</button>
						</div><!-- tabs -->
						<div id="tab panels" >
							<!-- ******************************************************************** -->
							<div class="tab w3-padding" id="TABP">
								<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
									<input class="mdl-textfield__input" id="<?=cADLogin::KEY_PASSWORD?>" type="password" name="<?=cADLogin::KEY_PASSWORD?>">
									<label class="mdl-textfield__label" for="<?=cADLogin::KEY_PASSWORD?>">Password...</label>
									<div class="mdl-tooltip" for="<?=cADLogin::KEY_PASSWORD?>">
										this password is not stored by this web application.
									</div>
								<p>
								</div>
								Note: using passwords will not work for SAML authenticated logins.
							</div> <!-- P Panel -->
							
							<!-- ******************************************************************** -->
							<div class="tab w3-padding" id="TABAT" style="display:none">
								<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
									<input class="mdl-textfield__input" id="<?=cADLogin::KEY_APITOKEN?>" type="text" name="<?=cADLogin::KEY_APITOKEN?>">
									<label class="mdl-textfield__label" for="<?=cADLogin::KEY_APITOKEN?>">API Token...</label>
									<div class="mdl-tooltip" for="<?=cADLogin::KEY_APITOKEN?>">
										Use the API token provided by the controller administrator. This token is not stored by this application
									</div>
								</div>
							</div> <!-- AT Panel -->
							
							<!-- ******************************************************************** -->
							<div class="tab w3-padding" id="TABAS" style="display:none">
								<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
									<input class="mdl-textfield__input" id="<?=cADLogin::KEY_APIAPP?>" type="text" name="<?=cADLogin::KEY_APIAPP?>">
									<label class="mdl-textfield__label" for="<?=cADLogin::KEY_APIAPP?>">API Application...</label>
									<div class="mdl-tooltip" for="<?=cADLogin::KEY_APIAPP?>">
										Use the API Application name you have defined in the controller.
									</div>
								</div>
								<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
									<input class="mdl-textfield__input" id="<?=cADLogin::KEY_APISECRET?>" type="text" name="<?=cADLogin::KEY_APISECRET?>">
									<label class="mdl-textfield__label" for="<?=cADLogin::KEY_APISECRET?>">API Secret...</label>
									<div class="mdl-tooltip" for="<?=cADLogin::KEY_APISECRET?>">
										Use the API Secret details that you have defined in the controller. This token is not stored by this application
									</div>
								</div>
							</div> <!-- AS Panel -->
							
							<!-- ******************************************************************** -->
							<div class="tab w3-padding" id="TABJ" style="display:none">
								<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
									<input class="mdl-textfield__input" id="<?=cADLogin::KEY_JSESSION_ID?>" type="text" name="<?=cADLogin::KEY_JSESSION_ID?>">
									<label class="mdl-textfield__label" for="<?=cADLogin::KEY_JSESSION_ID?>">JsessionID...</label>
									<div class="mdl-tooltip" for="<?=cADLogin::KEY_JSESSION_ID?>">
										see instructions below
									</div>
								</div>
								<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
									<input class="mdl-textfield__input" id="<?=cADLogin::KEY_XCSRFTOKEN?>" type="text" name="<?=cADLogin::KEY_XCSRFTOKEN?>">
									<label class="mdl-textfield__label" for="<?=cADLogin::KEY_XCSRFTOKEN?>">XCSRFToken...</label>
									<div class="mdl-tooltip" for="<?=cADLogin::KEY_XCSRFTOKEN?>">
										see instructions below
									</div>
								</div>
								This hack is used for SAML authenticated logins when no access to the controller REST apis have been provided.
								A restricted set of functions is available using this method.
								<ul>
									<li>using a seperate browser tab login to the controller 
									<li>from the browsers site information button (or an extension) extract the values of the JSESSIONID and X-CSRF-TOKEN cookie
									<li>copy and paste the cookies values above
								</ul>
							</div> <!-- J Panel -->
							
						</div><!-- tab panels -->
					</div><!-- tabs contrainer-->

					<?php
						if (cDebug::is_debugging()){
							?><input type="hidden" name="<?=cDebug::DEBUG_STR?>" value="1"><?php
						}
						if (cDebug::is_extra_debugging()){
							?><input type="hidden" name="<?=cDebug::DEBUG2_STR?>" value="1"><?php
						}
					?>
					<button id="btnlogin" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color-text--white login_submit" type="submit" value="1" name="<?=cADLogin::KEY_SUBMIT?>">Login</button>
					<div class="mdl-tooltip" for="btnlogin">
						No login credentials are stored by this application, we really mean it
					</div>
				</form>
			</div>
			<div class="mdl-card__actions mdl-card--border">
			Rapport gets the stuff you need to focus on from your AppDynamics &tm; SAAS controller. 
			Absolutely no credentials are stored by this application. For security reasons we recommend creating a limited access local user in your controller for use with this application.
			</div>
			<div class="mdl-card__actions mdl-card--border">
			Appdynamics is a trademark of Appdynamics LLC which is part of Cisco. This site is not affiliated with either appdynamics or cisco.
			</div>
			<div class="mdl-card__actions mdl-card--border">
				<form action="index.php" method="POST" >
					<input type="hidden" name="<?=cADLogin::KEY_ACCOUNT?>" value="<?=cADCredentials::DEMO_ACCOUNT?>">
					<input type="hidden" name="<?=cADLogin::KEY_USERNAME?>" value="<?=cADCredentials::DEMO_USER?>">
					<input type="hidden" name="<?=cADLogin::KEY_PASSWORD?>" value="<?=cADCredentials::DEMO_PASS?>">
					<input type="hidden" name="<?=cADLogin::KEY_HOST?>" value="<?=cADCore::DEMO_HOST?>">
					<button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect login_submit" id="demologin" name="<?=cADLogin::KEY_SUBMIT?>" value="1" type="submit">Demo Mode</button>
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
