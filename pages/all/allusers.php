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

function display_name_sort_fn($po1, $po2){
	return strcasecmp ($po1->display_name, $po2->display_name);
}

//####################################################################
cRenderHtml::header("Account Users");
cRender::force_login();

//####################################################################
try{
	$aUsers = cAD_RBAC::get_all_users();
}catch(Exception $e){
	cCommon::errorbox("unable to get users.");
	cRenderHtml::footer();
	return;
}

if (cArrayUtil::array_is_empty($aUsers)){
	cCommon::errorbox("no users found");
	cRenderHtml::footer();
	return;
}

uasort($aUsers,"display_name_sort_fn");

//####################################################################
cRenderCards::card_start("Users");
	cRenderCards::body_start();
		?>List of all users in the controller<?php
	cRenderCards::body_end();
	cRenderCards::action_start();
		cADCommon::button(cADControllerUI::account_users());
		cRender::button("All Groups", "allgroups.php");
	cRenderCards::action_end();
cRenderCards::card_end();

cRenderCards::card_start();
	cRenderCards::body_start();
	?><table border="1" cellspacing="0" cellpadding="2">
		<tr>
			<th>display name</th>
			<th>username</th>
			<th>email</th>
		</tr><?php
		foreach ($aUsers as $sKey=>$oUser){
		?><tr>
			<td align="right"><?=$oUser->display_name?></td>
			<td align="right"><?=$oUser->username?></td>
			<td><?=$oUser->email?></td>
		</tr><?php
		}
	?></table><php
			
	cRenderCards::body_end();
cRenderCards::card_end();
 
cRenderHtml::footer();
?>
