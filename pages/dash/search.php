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
cRenderHtml::header("Search Dashboards");
?>
	<script type="text/javascript" src="<?=$home?>/js/widgets/dashsearch.js"></script>	
<?php
cRender::force_login();
$oTimes = cRender::get_times();
$sTemplate = cADControllerUI::dashboard_detail("-tmp-", $oTimes);


//cDebug::vardump($aData[0]);
cRenderCards::card_start("Dashboards");
cRenderCards::body_start();
	?><form onsubmit="return false;">
		<div class="mdl-textfield mdl-js-textfield">
			<input class="mdl-textfield__input" type="text" id="search" disabled>
			<label class="mdl-textfield__label" for="search">Search...</label>
		</div>
		<button class="mdl-button mdl-js-button mdl-button--fab mdl-button--mini-fab" id="submit" disabled onclick="onClick();">
			<i class="material-icons">search</i>
		</button>
	</form>
	<script language="javascript">
		function onClick(){
			window.stop();
			getDashList();
		}
		
		function getDashList(){
			var oElement = $("#results");
			var oQueue = new cQueueifVisible();
			bean.on(oQueue, "status", 	onListStatus);				
			bean.on(oQueue, "result", 	onListResult);				
			bean.on(oQueue, "error", 	onListError	);				
			oQueue.go(oElement, "<?=$home?>/rest/listdash.php");
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
			if (aResponse.length == 0){
				oElement.addClass("ui-state-error");
				oElement.append("there are no dashboards configured");
			}else{
				var sSearch = $("#search").val();
				oElement.removeClass();
				for (var i=0 ; i<aResponse.length; i++){
					var oDash = aResponse[i];
					var sFragment = 
						"<div style='border:1px solid black' type='dashsearch'" +
							" <?=cRender::DASH_NAME_QS?>='" +oDash.name + "'" + 
							" <?=cRender::DASH_ID_QS?>='" +oDash.id+"'" +
							" <?=cRender::SEARCH_QS?>='"+ sSearch + "'" +
							" <?=cRender::DASH_URL_TEMPLATE?>='<?=$sTemplate?>'" +
							" home='<?=$home?>'"+ 
						">" +
								"Searching dashboard " + oDash.name + "..." +
						"</div>";
					oElement.append(sFragment);
				}
			}
			$("DIV[type=dashsearch]").each( function(piIndex, poEl){ $(poEl).addashsearch()});
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
		$(init);
	</script>
<?php
cRenderCards::body_end();
cRenderCards::action_start();
	cADCommon::button(cADControllerUI::dashboard_home());
	cRender::button("back to check","check.php");
cRenderCards::action_end();
cRenderCards::card_end();

//#####################################################################################################
cRenderCards::card_start();
cRenderCards::body_start();
	?><div class="note" id="results">enter some characters and press search</div><?php
cRenderCards::body_end();
cRenderCards::card_end();


//####################################################################
cRenderHtml::footer();
?>
