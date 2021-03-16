<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2018 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

//####################################################################
require_once("../../inc/root.php");
cRoot::set_root("../..");
require_once("$root/inc/common.php");
require_once("$root/inc/inc-charts.php");

//-----------------------------------------------
$oApp = cRenderObjs::get_current_app();
$sAppQS = cRenderQS::get_base_app_QS($oApp);


//####################################################################
cRenderHtml::header("Web browser - All Synthetics");
cRender::force_login();

$title ="$oApp->name&gt;Web Real User Monitoring&gt;All Synthetic jobs";
cRender::show_time_options( $title); 
$oTimes = cRender::get_times();


//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}

//####################################################################
?><h2>All Synthetics</h2>
<script src="<?=$home?>/js/widgets/synthetics.js"></script>
<div id="container">
<?php
	$aResponse = cAppDynController::GET_Applications();
	if ( count($aResponse) == 0)
		cRender::messagebox("Nothing found");
	else{
		foreach ( $aResponse as $oApp){		?>
			<div app="<?=$oApp->name?>" aid="<?=$oApp->id?>">Loading synthetic data for ...<?=$oApp->name?></div>
		<?php	}
	}
?>
</div>
<script>
$( function(){
	$("#container > div").each( 
		function (){
			var oElement = $(this);
			oElement.appdsynlist( {
				home:"<?=$home?>",
				app:oElement.attr("app"),
				app_id:oElement.attr("aid")
			} );
		}
	);
})
	
</script>

<?php
cRenderHtml::footer();
?>
