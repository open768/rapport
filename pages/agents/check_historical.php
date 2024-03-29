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

$oApp = cRenderObjs::get_current_app();
cRenderHtml::header("$oApp->name check historical agents");
cRender::force_login();


//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
?>
	<script src="<?=$jsWidgets?>/historicalAgents.js"></script>
	<script src="<?=$jsWidgets?>/agentcount.js"></script>
<?php
	cRenderCards::card_start("Historical Agents");
	cRenderCards::action_start();
		cRenderMenus::show_app_change_menu("Change App");
		$sUrl = cHttp::build_url("appagents.php", cRenderQS::APP_ID_QS, $oApp->id);
		cRender::button("Back to agents: $oApp->name", $sUrl);
		//cRender::add_filter_box("a[type=tier]","name",".mdl-card");
		
	cRenderCards::action_end();
	cRenderCards::card_end();
	?>
		<div id="tiers" type="widget" 
			<?=cRenderQS::APP_ID_QS?>='<?=$oApp->id?>' 
			<?=cRenderQS::HOME_QS?>='<?=$home?>'>
			loading...
		</div>
	<?
?>
<script>
	function init_widget(){
		$("DIV[type=widget]").adhistagentstiers();
	}
	
	$( init_widget);
</script>

<?php
cRenderHtml::footer();
?>
