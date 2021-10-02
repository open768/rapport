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

//-----------------------------------------------
$oApp = cRenderObjs::get_current_app();

cRenderHtml::header("Health Rules for $oApp->name");
cRender::force_login();

//####################################################################

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}

$aRules = $oApp->GET_HealthRules();
$iCount = ($aRules==null?0:count($aRules));
if ( $iCount == 0){
	cCommon::errorbox("no health rules found for this application");
	cRenderHtml::footer();
	exit;
}

//######################################################################################
//----count disabled
$iDisabled = 0;
foreach ($aRules as $oRule)
	if (!$oRule->enabled)
		$iDisabled++;

if ($iDisabled == $iCount){
	cCommon::errorbox("all rules are disabled - nothing to show here");
	cRenderHtml::footer();
	exit;
}

//######################################################################################
cRenderCards::card_start("Overview");
cRenderCards::body_start();
	echo "there are $iCount Rules of which $iDisabled are disabled";
	cRender::add_filter_box("SPAN[type=filter]", "name", ".mdl-card");
cRenderCards::body_end();
cRenderCards::action_start();
	$sADUrl = cADControllerUI::app_health_rules($oApp);
	$sBaseUrl = cHttp::build_url("healthrules.php", cRenderQS::get_base_app_QS($oApp));
	cADCommon::button($sADUrl);
	cRenderMenus::show_apps_menu("Health rules for:","healthrules.php");
	cRender::button("back to events", cHttp::build_url("events.php", cRender::APP_QS, $oApp->name));
	if (cRender::is_list_mode())
		cRender::button("show details", $sBaseUrl);
	else
		cRender::button("show as list", $sBaseUrl."&".cRender::LIST_MODE_QS);
cRenderCards::action_end();
cRenderCards::card_end();


//######################################################################################
if (cRender::is_list_mode()){
	cRenderCards::card_start("Health Rules");
	cRenderCards::body_start();
		echo '<DIV style="column-count:3">';
		$sLastCh = "";
		foreach ($aRules as $oRule){
			$sRule = $oRule->name;
			$sCh = strtoupper($sRule[0]);
			if ($sCh !== $sLastCh){
				echo "<h3>$sCh</h3>";
				$sLastCh = $sCh;
			}
			echo "$sRule<br>";
		}	
		echo '</DIV>';
	cRenderCards::body_end();
	cRenderCards::card_end();
}else{
	foreach ($aRules as $oRule){
		if ($oRule->enabled){
			cRenderCards::card_start("<span type='filter' name='$oRule->name'>Rule</span>: $oRule->name");
			cRenderCards::body_start();
				//display widgets for health rules
				//they will be asynchronously fetched by the javascript using a http queue;
				echo "<div type='appdhealthrule' home='$home' ".
						cRender::APP_QS."='$oApp->name' ".
						cRender::APP_ID_QS."='$oApp->id' ".
						cRender::HEALTH_ID_QS."='$oRule->id'>".
							"please Wait - loading details for rule $oRule->name".
					"</div>";
			cRenderCards::body_end();
			cRenderCards::card_end();
		}
	}
	?><script language="javascript">
		function init_widget(piIndex, poElement){
			$(poElement).adhealthdetail();
		}
		
		function init_health_widgets(){
			$("DIV[type='appdhealthrule']").each(init_widget);
		}
		
		$( init_health_widgets);
	</script><?php
}

cRenderHtml::footer();
?>
