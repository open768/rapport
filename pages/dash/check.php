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



//####################################################################
cRenderHtml::header("Dashboards");
cRender::force_login();
$oTimes = cRender::get_times();
?><script language="javascript" src="<?=$jsWidgets?>/dashhealth.js"></script><?php

//####################################################################
$aData = cADRestUI::GET_dashboards();
if (count($aData) == 0){
	cCommon::messagebox("no dashboards found");
	cRenderHtml::footer();
	exit;
}
//cDebug::vardump($aData[0]);
cRenderCards::card_start("Dashboards");
cRenderCards::body_start();
	cCommon::messagebox("work in progress");
	cRender::add_filter_box("span[type=filter]","value",".tblrow");
cRenderCards::body_end();
cRenderCards::action_start();
	cADCommon::button(cADControllerUI::dashboard_home());
	cRender::button("search", "search.php");
cRenderCards::action_end();
cRenderCards::card_end();

cRenderCards::card_start();
cRenderCards::body_start();
	?>
		<table class="maintable" id="tbldash">
		<thead><tr>
			<th width="*">Name</th>
			<th width="200">modified By</th>
			<th width="150">date</th>
			<th width="100">health</th>
			<th width="100">link</th>
		</tr></thead>
		<tbody><?php
		foreach ($aData as $oDash){
			$sUrl = cADControllerUI::dashboard_detail($oDash->id, $oTimes);
			
			?><tr class="tblrow">
				<td><span type="filter" value="<?=$oDash->name?>"><?=$oDash->name?> </span></td>
				<td><span type="filter" value="<?=$oDash->modifiedBy?>"><?=$oDash->modifiedBy?></span></td>
				<td><?=date("d-m-Y H:i", $oDash->modifiedOn/1000)?></td>
				<td align="middle"`>
					<div type="dashdetail" <?=cRenderQS::HOME_QS?>="<?=$home?>" <?=cRenderQS::DASH_ID_QS?>="<?=$oDash->id?>">
						wait...
					</div>
				</td>
				<td><?php
					cADCommon::button($sUrl,"");
				?></td>
			</tr><?php
		}
		?></tbody>
	</table>
	<script language="javascript">

		function init_a_dash_health_widget(piIndex, poElement){
			$(poElement).addashhealth();
		}
		
		function init_dash_health_widgets(){
			$("DIV[type=dashdetail]").each( init_a_dash_health_widget);
		}
		
		$(function(){ 
			$("#tbldash").tablesorter();
			$("#notem").html("click on table heading to sort");
			$(init_dash_health_widgets);
		})
	</script>
<?php
cRenderCards::body_end();
cRenderCards::card_end();


//####################################################################
cRenderHtml::footer();
?>
