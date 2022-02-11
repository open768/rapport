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
require_once "$phpinc/ckinc/audit.php";

//####################################################################
cRenderHtml::header("Admin Auditing");
cRender::force_login();


//####################################################################
$sAccount = cHeader::get(cRenderQS::AUDIT_ACCOUNT_QS);
$sHost = cHeader::get(cRenderQS::AUDIT_HOST_QS);
$sUser = cHeader::get(cRenderQS::AUDIT_USER_QS);
$sPage = cCommon::filename();

// Auditing is used purely for the application, this is not the same as auditing AD
?>
<h2>Audit users of this application</h2>
<?php
cCommon::messagebox("work in progress");
if ($sAccount){
	$oAccount = new cAuditAccount;
	$oAccount->account = $sAccount;
	$oAccount->host = $sHost;
	if ($sUser == null){
		$sBaseUrl = cHttp::build_url($sPage, cRenderQS::AUDIT_ACCOUNT_QS, $oAccount->account );
		$sBaseUrl = cHttp::build_url($sBaseUrl, cRenderQS::AUDIT_HOST_QS, $oAccount->host );
		$aUsers = cAudit::get_known_users($oAccount);
		?><ul><?php
		foreach ($aUsers as $oUser){
			$sUrl = cHttp::build_url($sBaseUrl, cRenderQS::AUDIT_USER_QS, $oUser->user );
			?><li><a href="<?=$sUrl?>"><?=$oUser->user?></a><?php
		}
		?></ul><?php
	}else{
		$oAccount->user = $sUser;
		?><ul>
			<li>Account: <?=$oAccount->account?>
			<li>User: <?=$oAccount->user?><br>
			<ul><?php
				$aEntries = cAudit::get_user_entries($oAccount);
				foreach ($aEntries as $oEntry){
					?><li><?=$oEntry->timstamp?><?php
				}
			?></ul>
		</ul>
		<?php
	}
}else{
	$aAccounts = cAudit::get_audited_accounts();
	if ($aAccounts == null){
		cCommon::messagebox("no Accounts found");
	}else{
		?><ul><?php
			foreach ($aAccounts as $oAccount){
				$sUrl = cHttp::build_url($sPage, cRenderQS::AUDIT_ACCOUNT_QS, $oAccount->account );
				$sUrl = cHttp::build_url($sUrl, cRenderQS::AUDIT_HOST_QS, $oAccount->host );
				?><li><a href="<?=$sUrl?>"><?=$oAccount->account?></a><?php
			}
		?></ul><?php
	}
}

cRenderHtml::footer();
?>
