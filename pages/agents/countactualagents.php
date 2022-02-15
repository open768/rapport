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

cRenderHtml::header("Actual Agent Counts");
cRender::force_login();

//********************************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}

?>
	<script language="javascript" src="<?=$jsWidgets?>/allagents.js"></script>
<?php

//#############################################################
cRenderCards::card_start();
	cRenderCards::body_start();
		?>counts Agents using metrics uploaded<?php
	cRenderCards::body_end();
	cRenderCards::action_start();
		cADCommon::button(cADControllerUI::agents());
		cRender::button("Controller App Agent Counts", "countappagents.php");	
		cRender::button("Show All Agent Versions", "allagentversions.php");	
	cRenderCards::action_end();
cRenderCards::card_end();

//####################################################################
?>
	<div 
		id="allagentwidget" 
		<?=cRenderQS::HOME_QS?>='<?=$home?>'
		<?=cRenderQS::AGENT_COUNT_TYPE_QS?>='<?=cRenderQS::COUNT_TYPE_ACTUAL?>' 
		>
		Please Wait...
	</div>
	<script language="javascript">
		function init_widget(){
			$("#allagentwidget").adallagents();
		}
		
		$( init_widget);
	</script><?php
cRenderHtml::footer();
?>
