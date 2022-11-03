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

cRenderHtml::header("Audit Logs");
cRender::force_login();
$sType = cHeader::GET(cRenderQS::AUDIT_TYPE_QS);
if ($sType == null) $sType = cRenderQS::AUDIT_TYPE_LIST_ACTIONS;

if ($sType === cRenderQS::AUDIT_TYPE_ACTION){
	$sFilter = cHeader::GET(cRenderQS::AUDIT_FILTER);
}

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}

?>
	<script src="<?=$jsWidgets?>/audit.js"></script>
<?php

//####################################################################
if ($sType !== cRenderQS::AUDIT_TYPE_LIST_ACTIONS ){
	cRenderCards::card_start("Audit Logs");
		cRenderCards::action_start();
			//TBD add a calender to pick audit events from different days
			cRender::button("Back to actions list", "audit.php");
			if ($sType === cRenderQS::AUDIT_TYPE_ACTION)
				switch ( $sFilter){
					case cRenderQS::AUDIT_FILTER_LOGIN:
					case cRenderQS::AUDIT_FILTER_LOGIN_FAILED:
					case cRenderQS::AUDIT_FILTER_LOGOUT:
						cRender::button("Users", "../all/allusers.php");
				}
		cRenderCards::action_end();
	cRenderCards::card_end();
}

cRenderCards::card_start();
	cRenderCards::body_start();
	?><div 
		id="widget" 
		<?=cRenderQS::HOME_QS?>='<?=$home?>'
		<?=cRenderQS::AUDIT_TYPE_QS?>='<?=$sType?>' 
		<?=cRenderQS::AUDIT_FILTER?>='<?=$sFilter?>' 
	>
		Please Wait...
	</div>
	<script>
		function init_widget(){
			$("#widget").adaudit();
		}
		
		$( init_widget);
	</script><?php
	cRenderCards::body_end();
cRenderCards::card_end();


//####################################################################
cRenderHtml::footer();
?>