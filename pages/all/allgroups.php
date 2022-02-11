<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2021 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED**************************************************************************/

//####################################################################
$home="../..";
require_once "$home/inc/common.php";

//####################################################################
cRenderHtml::header("Account Groups");
cRender::force_login();

//####################################################################
$aGroups = cAD_RBAC::get_all_groups();
cDebug::vardump($aGroups);

if (cArrayUtil::array_is_empty($aGroups)){
	cCommon::errorbox("no groups found");
}
?><script language="javascript" src="<?=$jsWidgets?>/rbac_group_user.js"></script><?php


//####################################################################
cRenderCards::card_start("Group Users");
	cRenderCards::body_start();
		cRender::add_filter_box("span[type=group]","name",".mdl-card");
	cRenderCards::body_end();
	cRenderCards::action_start();
		cADCommon::button(cADControllerUI::account_groups());
		cRender::button("All Users", "allusers.php");
	cRenderCards::action_end();
cRenderCards::card_end();

foreach ($aGroups as $oGroup){
	$sTitle = "<span type='group' name='$oGroup->name'>$oGroup->name</span>";
	cRenderCards::card_start( $sTitle);
	cRenderCards::body_start();
	?> 
		<div 
			type='widget' 
			home='<?=$home?>' 
			<?=cRenderQS::GROUP_NAME_QS?>='<?=$oGroup->name?>'
			<?=cRenderQS::GROUP_ID_QS?>='<?=$oGroup->id?>'
				please wait...
		</div>
	<?php	
	cRenderCards::body_end();
	cRenderCards::card_end();
}
?><script language="javascript">
	
	function init_widgets(){
		$("DIV[type='widget']").each(
			function (piIndex, poElement){
				$(poElement).adrbacgroupusers();
			}
		);
	}
	
	$( init_widgets);
</script><?php
 
cRenderHtml::footer();
?>
