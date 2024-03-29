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
require_once "$root/inc/charts.php";

//-----------------------------------------------
$oApp = cRenderObjs::get_current_app();
$sAppQS = cRenderQS::get_base_app_QS($oApp);


//####################################################################
cRenderHtml::$load_google_charts = true;
cRenderHtml::header("Web browser - All Synthetics");
cRender::force_login();

$title ="$oApp->name&gt;Web Real User Monitoring&gt;All Synthetic jobs";

$oTimes = cRender::get_times();


//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}

//####################################################################
?><h2>All Synthetics</h2>
<script src="<?=$jsWidgets?>/synthetics.js"></script>
<div id="container">
<?php
	$aResponse = cADController::GET_all_Applications();
	if ( count($aResponse) == 0)
		cCommon::messagebox("Nothing found");
	else{
		foreach ( $aResponse as $oApp){		?>
			<div <?=cRenderQS::HOME_QS?>="<?=$home?>" <?=cRenderQS::APP_QS?>="<?=$oApp->name?>" <?=cRenderQS::APP_ID_QS?>="<?=$oApp->id?>">
				Loading synthetic data for ...<?=$oApp->name?>
			</div>
		<?php	}
	}
?>
</div>
<script>
$( function(){
	$("#container > div").each( 
		function (){
			var oElement = $(this);
			oElement.adsynlist( {
				home:"<?=$home?>",
				app:oElement.attr(cRenderQS.APP_QS),
				app_id:oElement.attr(cRenderQS.APP_ID_QS)
			} );
		}
	);
})
	
</script>

<?php
cRenderHtml::footer();
?>
