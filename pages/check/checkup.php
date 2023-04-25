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

//####################################################################
cRenderHtml::header("One Click Checkup");
cRender::force_login();
//TODO if application passed only do a checkup for that application.

?><script src="<?=$jsWidgets?>/appcheckup.js"></script><?php

//**********************************************************************************
function render_summary($paApps){
	cRenderCards::card_start("Checkup");
		cRenderCards::body_start();
			$sPrevious = "";
			echo "<h3>There are ".count($paApps)." Applications</h3>";
			cCommon::div_with_cols(cRenderHTML::DIV_COLUMNS);
				foreach ( $paApps as $oApp){
					$sChar = strtolower(($oApp->name)[0]);
					if ($sChar !== $sPrevious){
						echo "<h3>".strtoupper($sChar)."</h3>";
						$sPrevious = $sChar;
					}
					echo "<a href='#$oApp->id'>$oApp->name</a><br>";
				}
			echo "</div>";	
		cRenderCards::body_end();
		cRenderCards::action_start();
			$sUrl = cHttp::build_url(cCommon::filename(), cRenderQS::APP_ID_QS, cHeader::GET(cRenderQS::APP_ID_QS));
			$checkQS = cHeader::GET(cRenderQS::CHECK_ONLY_QS);
			if ($checkQS === cRenderQS::CHECK_ONLY_BT)
				cRender::button("show all checks", $sUrl);
			else{
				$sUrl = cHttp::build_url($sUrl, cRenderQS::CHECK_ONLY_QS, cRenderQS::CHECK_ONLY_BT);
				cRender::button("show only BT Checks", $sUrl);
			}
			
			
			cRender::add_filter_box("div[type=admenus]",cRenderQS::APP_QS,".mdl-card");
		cRenderCards::action_end();
	cRenderCards::card_end();
}

//**********************************************************************************
function render_app($poApp){
	global $home;
	cRenderCards::card_start("<a name='$poApp->id'>$poApp->name</a>");
	cRenderCards::body_start();
	?><div 
			type="appcheckup" 
			<?=cRenderQS::APP_QS?>="<?=$poApp->name?>"
			<?=cRenderQS::APP_ID_QS?>="<?=$poApp->id?>"
			<?=cRenderQS::HOME_QS?>="<?=$home?>"
			<?=cRenderQS::CHECK_ONLY_QS?>="<?=cHeader::GET(cRenderQS::CHECK_ONLY_QS)?>">
			loading...
	</div><?php
	cRenderCards::body_end();
	cRenderCards::action_start();
		cRenderMenus::show_app_functions($poApp);
		cADCommon::button(cADControllerUI::app_BT_config($poApp));
	cRenderCards::action_end();
	cRenderCards::card_end();
}

//**********************************************************************************
$oApp = cRenderObjs::get_current_app();
if ($oApp)
	render_app($oApp);
else{
	$aResponse = cADController::GET_all_Applications();
	render_summary($aResponse);

	//TODO list of applications can be fetched asynchronously
	foreach ( $aResponse as $oApp2)
		render_app($oApp2);
}

?>
	<script>
		function init_a_checkup_widget(piIndex, poElement){
			$(poElement).adappcheckup();
		}
		function init_checkup_widgets(){
			$("DIV[type=appcheckup]").each(init_a_checkup_widget);
		}
		
		$(init_checkup_widgets);
	</script>
<?php

cRenderHtml::footer();
?>
