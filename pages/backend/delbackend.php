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
cRenderHtml::header("Delete Backend");
cRender::force_login();


//********************************************************************
if (cAD::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************
$iBackend = cHeader::get(cRender::BACKEND_QS);


//####################################################################
?><h2> Delete Backend <?=$iBackend?></h2>
<?php
	try {
		cAD_RestUI::DELETE_backend($iBackend);
	}catch (Exception $e){
		//ignore all exceptions?
	}
?>	
Done

<?php
//####################################################################
cRenderHtml::footer();
?>
