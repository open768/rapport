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

$aResponse = cADController::GET_all_Applications();

//####################################################################
?><script language="javascript" src="<?=$jsWidgets?>/appcheckup.js"></script><?php

cRenderCards::card_start("Checkup");
cRenderCards::body_start();
	$sPrevious = "";
	echo "<div style='column-count:4'>";
		foreach ( $aResponse as $oApp){
			$sChar = strtolower(($oApp->name)[0]);
			if ($sChar !== $sPrevious){
				echo "<h3>".strtoupper($sChar)."</h3>";
				$sPrevious = $sChar;
			}
			echo "<a href='#$oApp->id'>$oApp->name</a><br>";
		}
	echo "</div>";	
	cRender::add_filter_box("div[type=admenus]","appname",".mdl-card");
cRenderCards::body_end();
cRenderCards::card_end();

//####################################################################
//this needs to be asynchronous as when there are a lot of applications that page times out
foreach ( $aResponse as $oApp){
	cRenderCards::card_start("<a name='$oApp->id'>$oApp->name</a>");
	cRenderCards::body_start();
	?><div 
			type="appcheckup" 
			<?=cRender::APP_QS?>="<?=$oApp->name?>"
			<?=cRender::APP_ID_QS?>="<?=$oApp->id?>"
			<?=cRender::HOME_QS?>="<?=$home?>">
			loading...
	</div><?php
	cRenderCards::body_end();
	cRenderCards::action_start();
		cRenderMenus::show_app_functions($oApp);
		cADCommon::button(cADControllerUI::app_BT_config($oApp));
	cRenderCards::action_end();
	cRenderCards::card_end();
}
?>
	<script language="javascript">
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
