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
//####################################################################
$home="../..";
require_once "$home/inc/common.php";


//####################################################################
// common functions
//####################################################################
//get passed in values
$oApp = cRenderObjs::get_current_app();
$gsAppQS = cRenderQS::get_base_app_QS($oApp);

cRenderHtml::header("All Other Transactions for $oApp->name");
cRender::force_login();

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************
?><script language="javascript" src="<?=$home?>/js/widgets/tierothertrans.js"></script><?php

//********************************************************************
cRenderCards::card_start();
cRenderCards::body_start();
	cRender::add_filter_box("span[type=tiertrans]","name",".mdl-card");
cRenderCards::body_end();
cRenderCards::action_start();
	$sUrl = cHttp::build_url("apptrans.php", $gsAppQS);
	cRender::button("back to transactions", $sUrl);
	cRenderMenus::show_apps_menu("Change Application");
cRenderCards::action_end();
cRenderCards::card_end();

//********************************************************************
$aTiers =$oApp->GET_Tiers();
foreach ( $aTiers as $oTier){
	$oTier->app = $oApp;
	
	//get the transaction names for the Tier
	cRenderCards::card_start();
		cRenderCards::body_start();
			?><span type='tiertrans' name='$oTier->name'></span>
			<div 
				type="adtierothertrans" 
				home="<?=$home?>" 
				<?=cRender::APP_ID_QS?>="<?=$oApp->id?>" 
				<?=cRender::TIER_ID_QS?>="<?=$oTier->id?>">
					please wait...
			</div><?php
		cRenderCards::body_end();
		cRenderCards::action_start();
			cRenderMenus::show_tier_functions($oTier);
		cRenderCards::action_end();
	cRenderCards::card_end();
}

?>
<script language="javascript">
	function init_widget(piIndex, poElement){
		$(poElement).adtierothertrans();
	}
	$("div[type=adtierothertrans]").each( init_widget);
</script>
<?php


//********************************************************************
cRenderHtml::footer();
?>