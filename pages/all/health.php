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
cRender::force_login();
cRenderHtml::header("All Applications Health ");

function do_inactive($paData){
	foreach ($paData as $oNode)
		if (!$oNode->has_activity)
			cRenderMenus::show_app_functions($oNode->app);
}

function do_active($paData){
	?><div class="note" id="notem">
		<div id="p0" class="mdl-progress mdl-js-progress mdl-progress__indeterminate"></div>
	</div>
	<table border="1" cellspacing="0" id="tblsummary">
		<thead><tr>
			<th width="*"></th> <!-- button -->
			<th width="50">Avg Response (ms)</th>
			<th width="50">Calls Per Min</th>
			<th width="50">Error Per Min</th>
			<th width="50">normal nodes</th>
			<th width="50">warning nodes</th>
			<th width="50">critical nodes</th>
		</tr></thead>
		<tbody><?php
			foreach ($paData as $oNode){
				if ($oNode->has_activity){
					?><tr>
						<td align="right"><?php 
							cRenderMenus::show_app_functions($oNode->app);
						?></td>
						<td align="middle"><?=$oNode->response?></td>
						<td align="middle"><?=$oNode->calls?></td>
						<td align="middle"><font color="red"><b><?=$oNode->errors?></b></font></td>
						<td align="middle"><font color="green"><?=$oNode->normal?></font></td>
						<td align="middle"><font color="orange"><b><?=$oNode->warning?></b></font></td>
						<td align="middle"><font color="red"><b><?=$oNode->critical?></b></font></td>
					</tr><?php 
				}
			}
		?></tbody>
	</table>
	<script>
		$(function(){ 
			$("#tblsummary").tablesorter();
			$("#notem").html("click on table heading to sort");
		})
	</script><?php
}

//####################################################################
$aData = cADRestUI::GET_account_flowmap(); //TBD make asynchronous this is slow
$aData = cADAnalysis::analyse_account_flowmap($aData);

//####################################################################
cRenderCards::card_start("active applications");
	cRenderCards::body_start();
		do_active($aData);
	cRenderCards::body_end();
	cRenderCards::action_start();
		cADCommon::button(cADControllerUI::apps_home());
	cRenderCards::action_end();
cRenderCards::card_end();

//####################################################################
cRenderCards::card_start("inactive applications");
	cRenderCards::body_start();
		do_inactive($aData);
	cRenderCards::body_end();
cRenderCards::card_end();


//####################################################################
cRenderHtml::footer();
?>
