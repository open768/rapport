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

cRenderHtml::header("Agent Counts");
cRender::force_login();

?>
	<script src="<?=$jsWidgets?>/allagents.js"></script>
	<script src="<?=$jsWidgets?>/agentcount.js"></script>
<?php


//********************************************************************************

if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}

//#############################################################
cRenderCards::card_start("Agent Counts reported by the controller");
	cRenderCards::body_start();
		cCommon::messagebox("may show disabled nodes - please confirm in controller");
		if (!cHeader::GET(cRenderQS::TOTALS_QS))
			cRender::add_filter_box("a[type=app]","name",".mdl-card");
	cRenderCards::body_end();
	cRenderCards::action_start();
		cADCommon::button(cADControllerUI::agents());
		cRender::button("Show All Agent Versions", "allagentversions.php");	
		cRender::button("Count Actual Agents", "countactualagents.php");	
		if (cHeader::GET(cRenderQS::TOTALS_QS))
			cRender::button("show details", cCommon::filename());
		else
			cRender::button("show totals", cCommon::filename()."?".cRenderQS::TOTALS_QS."=1");
	cRenderCards::action_end();
cRenderCards::card_end();

//####################################################################
?>
	<div 
		id="allagentwidget" 
		<?=cRenderQS::HOME_QS?>='<?=$home?>' 
		<?=cRenderQS::TOTALS_QS?>='<?=cHeader::GET(cRenderQS::TOTALS_QS)?>'
		<?=cRenderQS::AGENT_COUNT_TYPE_QS?>='<?=cRenderQS::COUNT_TYPE_APPD?>' >
			Please Wait...
	</div>
	<script>
		function init_widget(){
			$("#allagentwidget").adallagents();
		}
		
		$( init_widget);
	</script><?php
cRenderHtml::footer();
?>
