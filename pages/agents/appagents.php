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

$oApp = cRenderObjs::get_current_app();
cRenderHtml::header("$oApp->name Application Agent Versions");
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
	<script language="javascript" src="<?=$home?>/js/widgets/allagentversions.js"></script>
	
<?php

//************************************************************************************
function add_card( $psCaption, $psAnchor, $psType, $psGoUrl = null){
	global $home;
	global $oApp;
	
	cRenderCards::card_start("<a name='$psAnchor'>$psCaption</a>");
	cRenderCards::body_start();
	?>
		<div 
			type="widget" 
			<?=cRender::APP_ID_QS?>='<?=$oApp->id?>' 
			<?=cRender::HOME_QS?>='<?=$home?>' 
			<?=cRender::TYPE_QS?>='<?=$psType?>'
			<?=cRender::TOTALS_QS?>='<?=cHeader::GET(cRender::TOTALS_QS)?>'
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

//************************************************************************************
cRenderCards::card_start("Contents");
	cRenderCards::body_start();
		cCommon::messagebox("may show disabled nodes - please confirm in controller. Page uses cacheing");		
		?>
		<ul>
			<li><a href="#m">Machine Agents</a>
			<li><a href="#a">App Agents</a>
			<li><a href="#d">Database Agents</a>
			<!-- <li><a href="#o">Other Agents</a> -->
		</ul>
		<?php
	cRenderCards::body_end();
	cRenderCards::action_start();
		cRenderMenus::show_apps_menu("change App");
		cRender::button("All Agent Versions", "../agents/allagentversions.php");	
	cRenderCards::action_end();
cRenderCards::card_end();

//************************************************************************************
add_card("Machine agents", "m", "machine");
add_card("App agents", "a", "app");
add_card("Database Agents", "d", "db");

//************************************************************************************
//*****************************************************************
?>
<script language="javascript">
	function init_widget(){
		$("DIV[type=widget]").adallagentversions();
	}
	
	$( init_widget);
</script>

<?php
cRenderHtml::footer();
?>