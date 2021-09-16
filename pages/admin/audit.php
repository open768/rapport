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
cRenderHtml::header("Admin Auditing");
cRender::force_login();

cRender::show_top_banner("Admin Auditing");

//####################################################################
const HOST_ID="5";
const ACCOUNT_ID="2";
const USER_ID="3";

$sAccount = cHeader::get(ACCOUNT_ID);
$sHost = cHeader::get(HOST_ID);
$sUser = cHeader::get(USER_ID);

?>
<h2>Audit</h2>
<?php
if ($sAccount){
	$oAccount = new cADAuditAccount;
	$oAccount->account = $sAccount;
	$oAccount->host = $sHost;
	if ($sUser == null){
		$sBaseUrl = cHttp::build_url("audit.php", ACCOUNT_ID, $oAccount->account );
		$sBaseUrl = cHttp::build_url($sBaseUrl, HOST_ID, $oAccount->host );
		$aUsers = cADAudit::get_known_users($oAccount);
		?><ul><?php
		foreach ($aUsers as $oUser){
			$sUrl = cHttp::build_url($sBaseUrl, USER_ID, $oUser->user );
			?><li><a href="<?=$sUrl?>"><?=$oUser->user?></a><?php
		}
		?></ul><?php
	}else{
		$oAccount->user = $sUser;
		?><ul>
			<li>Account: <?=$oAccount->account?>
			<li>User: <?=$oAccount->user?><br>
			<ul><?php
				$aEntries = cADAudit::get_user_entries($oAccount);
				foreach ($aEntries as $oEntry){
					?><li><?=$oEntry->timstamp?><?php
				}
			?></ul>
		</ul>
		<?php
	}
}else{
	$aAccounts = cADAudit::get_audited_accounts();
	if ($aAccounts == null){
		cRender::messagebox("no Accounts found");
	}else{
		?><ul><?php
			foreach ($aAccounts as $oAccount){
				$sUrl = cHttp::build_url("audit.php", ACCOUNT_ID, $oAccount->account );
				$sUrl = cHttp::build_url($sUrl, HOST_ID, $oAccount->host );
				?><li><a href="<?=$sUrl?>"><?=$oAccount->account?></a><?php
			}
		?></ul><?php
	}
}

cRenderHtml::footer();
?>
