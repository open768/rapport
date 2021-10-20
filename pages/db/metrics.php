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



//####################################################################
cRenderHtml::header("Databases - custom metrics");
cRender::force_login();
cChart::do_header();
cChart::$width=cChart::CHART_WIDTH_LARGE -200;


//####################################################################


//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************

//####################################################################

cRenderCards::card_start();
cRenderCards::body_start();
		cRender::add_filter_box("span[type=name]","value",".mdl-card");
cRenderCards::body_end();
cRenderCards::action_start();
	cADCommon::button(cADControllerUI::db_custom_metrics());
cRenderCards::action_end();
cRenderCards::card_end();

$aData = cADDB::GET_all_custom_metrics();
foreach ($aData as $sDB=>$aEntries){
	cRenderCards::card_start("<span type='name' value='$sDB'>$sDB</span>");
	cRenderCards::body_start();
		$sHTML = "<table border='1' cellspacing='0' width='100%'>";
		foreach ($aEntries as $oItem){
			$sHTML .= "<tr>";
				$sHTML .= "<th width='200'><span type='name' value='$oItem->name'>$oItem->name</span></th>";
				$sHTML .= "<td width='*'>".htmlspecialchars($oItem->query)."</td>";
			$sHTML .= "</tr>";
		}
		$sHTML .= "</table>";
		echo $sHTML;
	cRenderCards::body_end();
	cRenderCards::card_end();
}

cChart::do_footer();
cRenderHtml::footer();
?>
