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
//TBD make asynchronous - separate calls for machine/db/app agents
$home="../..";
require_once "$home/inc/common.php";


cRenderHtml::header("All Agent Versions");
cRender::force_login();


//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}

?>
	<style>
		table {table-layout:fixed;}
		table td {word-wrap:break-word;font-size:10px}
	</style>
	<script src="<?=$jsWidgets?>/allagentversions.js"></script>
<?php

function add_card( $psCaption, $psAnchor, $psType, $psGoUrl = null){
	global $home;
	
	cRenderCards::card_start("<a name='$psAnchor'>$psCaption</a>");
	cRenderCards::body_start();
	?>
		<div 
			type="widget" 
			<?=cRenderQS::HOME_QS?>='<?=$home?>' 
			<?=cRenderQS::TYPE_QS?>='<?=$psType?>'
			<?=cRenderQS::TOTALS_QS?>='<?=cHeader::GET(cRenderQS::TOTALS_QS)?>'
				>Please Wait...</div>
	<?php
	cRenderCards::body_end();
	if ($psGoUrl){
		cRenderCards::action_start();
			cRender::button($psCaption, $psGoUrl);	
		cRenderCards::action_end();
	}
	cRenderCards::card_end();
}

//####################################################################
cRenderCards::card_start("Contents");
	cRenderCards::body_start();
		cCommon::messagebox("may show disabled nodes - please confirm in controller. This page uses cached data");
		?>
		<ul>
			<li><a href="#m">Machine Agents</a>
			<li><a href="#a">App Agents</a>
			<li><a href="#d">Database Agents</a>
			<!-- <li><a href="#o">Other Agents</a> -->
		</ul>
		Controller Version: <?=cADController::GET_Controller_version();?>
		<?php
	cRenderCards::body_end();
	cRenderCards::action_start();
		cRender::button("latest AppDynamics versions", "appdversions.php");	
		cRender::button("AppDynamics Downloads", "https://download.appdynamics.com/download/");	
		cADCommon::button(cADControllerUI::agents(), "Agent Settings");
		cRender::button("Agents per App", "countappagents.php");	
		cRender::button("Count Actual Agents", "countactualagents.php");	
		if (cHeader::GET(cRenderQS::TOTALS_QS))
			cRender::button("show details", cCommon::filename());
		else
			cRender::button("show totals", cCommon::filename()."?".cRenderQS::TOTALS_QS."=1");
	cRenderCards::action_end();
cRenderCards::card_end();

add_card("Machine agents", "m", "machine");
add_card("App agents", "a", "app");
add_card("Database Agents", "d", "db", "../db/alldb.php");
//add_card("Other", "o", "other");


//*****************************************************************
?>
<script>
	function init_widget(){
		$("DIV[type=widget]").adallagentversions();
	}
	
	$( init_widget);
</script>

<?php
//####################################################################
cRenderHtml::footer();
?>