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

//####################################################################
?><script language="javascript" src="<?=$home?>/js/widgets/appcheckup.js"></script><?php

function output_row($pbBad, $psCaption, $psContent, $psAction=null){
	$sClass = ($pbBad?"bad_row":"good_row");
	?><tr class="<?=$sClass?>">
		<th align='left' width='400'><?=$psCaption?>: </th>
		<td><?=$psContent?></td>
		<?php
			if ($psAction !== null){
				echo "<td>";
				cRender::button('<span class="material-icons-outlined">arrow_circle_right</span>', $psAction);
				echo "</td>";
			}
		?>
	</tr><?php
}
cRenderCards::card_start("Checkup");
cRenderCards::body_start();
	cRender::add_filter_box("div[type=admenus]","appname",".mdl-card");
cRenderCards::body_end();
cRenderCards::card_end();

//####################################################################
//this needs to be asynchronous as when there are a lot of applications that page times out
$aResponse = cADApp::GET_Applications();
foreach ( $aResponse as $oApp){
	cRenderCards::card_start();
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
