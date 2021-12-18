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

cRenderHtml::header("Agent Counts");
cRender::force_login();

?>
	<script language="javascript" src="<?=$home?>/js/widgets/allagents.js"></script>
	<script language="javascript" src="<?=$home?>/js/widgets/agentcount.js"></script>
<?php

//********************************************************************************
class cAgentTotals {
	public $total=0;
	public $machine=0;
	public $appserver=0;
	
	public function add($poAgentTotals){
		$this->total += $poAgentTotals->total;
		$this->machine += $poAgentTotals->machine;
		$this->appserver += $poAgentTotals->appserver;
	}
}


//********************************************************************************
const BLANK_WIDTH=200;
const TIERCOL_WIDTH=300;
const TOTALCOL_WIDTH=150;

$moApps = cADController::GET_all_Applications();
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}

//#############################################################
cRenderCards::card_start();
	cRenderCards::body_start();
		cCommon::messagebox("may show disabled nodes - please confirm in controller");
		if (!cHeader::GET(cRender::TOTALS_QS))
			cRender::add_filter_box("a[type=app]","name",".mdl-card");
	cRenderCards::body_end();
	cRenderCards::action_start();
		cADCommon::button(cADControllerUI::agents());
		cRender::button("Show All Agent Versions", "allagentversions.php");	
		if (cHeader::GET(cRender::TOTALS_QS))
			cRender::button("show details", cCommon::filename());
		else
			cRender::button("show totals", cCommon::filename()."?".cRender::TOTALS_QS."=1");
	cRenderCards::action_end();
cRenderCards::card_end();

//####################################################################
?>
	<div id="allagentwidget" <?=cRender::HOME_QS?>='<?=$home?>' <?=cRender::TOTALS_QS?>='<?=cHeader::GET(cRender::TOTALS_QS)?>'>Please Wait...</div>
	<script language="javascript">
		function init_widget(){
			$("#allagentwidget").adallagents();
		}
		
		$( init_widget);
	</script><?php
cRenderHtml::footer();
?>
