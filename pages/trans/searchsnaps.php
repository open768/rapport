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
//####################################################################
$home="../..";
require_once "$home/inc/common.php";

//####################################################################
$oTrans = cRenderObjs::get_current_trans();
$oTier = $oTrans->tier;
$oApp = $oTier->app;

//####################################################################
cRenderHtml::header("Search Snapshots for Transaction");
cRender::force_login();
?>
	<script type="text/javascript" src="<?=$home?>/js/widgets/snapsearch.js"></script>	
<?php

//####################################################
//display the results

$sTierQS = cRenderQS::get_base_tier_QS($oTier);
$sTransQS = cHttp::build_QS($sTierQS, cRender::TRANS_QS,$oTrans->name);
$sTransQS = cHttp::build_QS($sTransQS, cRender::TRANS_ID_QS,$oTrans->id);


//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************

//TODO make this a widget
$oCred = cRenderObjs::get_AD_credentials();


//#####################################################################################################
cRenderCards::card_start("Search $oTrans->name");
	cRenderCards::body_start();
		cCommon::messagebox("work in progress");
		?><form onsubmit="return false;">
			<div class="mdl-textfield mdl-js-textfield">
				<input class="mdl-textfield__input" type="text" id="search" disabled>
				<label class="mdl-textfield__label" for="search">Search...</label>
				<input type="hidden" ID="transqs" value="<?=$sTransQS?>">
			</div>
			<button class="mdl-button mdl-js-button mdl-button--fab mdl-button--mini-fab" id="submit" disabled onclick="onClick();">
				<i class="material-icons">search</i>
			</button>
		</form>
		<script language="javascript">
			function onClick(){
				window.stop();
				getSnapshotList();
			}
			
			function getSnapshotList(){
				var oElement = $("#results");
				var oQueue = new cQueueifVisible();
				bean.on(oQueue, "status", 	onListStatus);				
				bean.on(oQueue, "result", 	onListResult);				
				bean.on(oQueue, "error", 	onListError	);				
				var sQS  = $("#transqs").val();
				oQueue.go(oElement, "<?=$home?>/rest/listsnapshots.php?"+sQS);
			}
			
			//-------------------------------------------------------------
			function onKeyUp(poEvent){
				var iLen= $("#search").val().length;
				$("#submit").prop("disabled", (iLen<3));
			}
			
			function init(){
				$("#search").prop( "disabled", false );
				$("#search").keyup(onKeyUp);
			}

			//-------------------------------------------------------------
			function onListError(poHttp){
				var oElement = $("#results");
				oElement.empty();
				oElement.addClass("ui-state-error");
				oElement.append("There was an error  getting data  ");
			}
			
			function onListStatus(psStatus){
				var oElement = $("#results");
				oElement.empty();
				oElement.append("status: " + psStatus);
			}
			
			function onListResult(poHttp){
				var oElement = $("#results");
				var aResponse = poHttp.response;
				oElement.empty();
				oElement.append("work in progress");
				
				var sHTML = "<table border=1 cellspacing=0 class='maintable'>";
					sHTML += "<tr>";
						sHTML += "<th>date</th><th>Summary</th><th>results</th><th>link</th>";
					sHTML += "</tr>";
					
					var sSearch = $("#search").val();
					for (var i=0 ; i<aResponse.length; i++){
						var oItem = aResponse[i];
						var oTrans = oItem.trans;
						var oParams = {
							<?=cRender::TRANS_QS?>:oTrans.name,
							<?=cRender::TIER_ID_QS?>:oTrans.tier.id,
							<?=cRender::APP_ID_QS?>:oTrans.tier.app.id,
							<?=cRender::TRANS_ID_QS?>:oTrans.id,
							<?=cRender::SNAP_TIME_QS?>:oItem.startTime,
							<?=cRender::SNAP_GUID_QS?>:oItem.guuid
						};
						var sUrl = cBrowser.buildUrl("snapdetails.php", oParams);
						var sDiv = 
							"<div type='searchsnap'" +
								" <?=cRender::HOME_QS?>='<?=$home?>'" +
								" <?=cRender::APP_ID_QS?>='<?=$oApp->id?>'" +
								" <?=cRender::TIER_ID_QS?>='<?=$oTier->id?>'" +
								" <?=cRender::TRANS_QS?>='<?=$oTrans->name?>'" +
								" <?=cRender::TRANS_ID_QS?>='<?=$oTrans->id?>'" +
								" <?=cRender::SNAP_TIME_QS?>='" + oItem.startTime +"'" +
								" <?=cRender::SNAP_GUID_QS?>='" + oItem.guuid +"'" +
								" <?=cRender::SEARCH_QS?>='" + sSearch +"'" +
							">Searching ... please Wait ...</div>";
								
						var sFragment = "<tr>";
							sFragment += "<td>"+ oItem.startDate + "</td>";
							sFragment += "<td>"+ oItem.summary + "</td>";
							sFragment += "<td>" + sDiv + "</td>";
							sFragment += "<td><a href='" + sUrl + "' target='snapdetails'>snapshot</a>";
						sFragment += "</tr>";
						
						sHTML += sFragment;
					}
				sHTML += "</table>";
				oElement.append(sHTML);
				
				$("DIV[type=searchsnap]").each( function(piIndex, poEl){ $(poEl).adsnapsearch()});
			}
			
			$(init);
		</script>
		<?php
	cRenderCards::body_end();
	cRenderCards::action_start();
		cRender::button("Back to transaction", "transdetails.php?$sTransQS");
	cRenderCards::action_end();
cRenderCards::card_end();

//#####################################################################################################
cRenderCards::card_start();
cRenderCards::body_start();
	?><div class="note" id="results">enter some characters and press search</div><?php
cRenderCards::body_end();
cRenderCards::card_end();


// ################################################################################
// ################################################################################

cRenderHtml::footer();
?>