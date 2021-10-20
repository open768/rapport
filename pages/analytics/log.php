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
cRenderHtml::header("Log Analytics");
cRender::force_login();

//####################################################################
cRenderCards::card_start();
	cRenderCards::body_start();
		cCommon::messagebox("work in progress");
		cRender::add_filter_box("span[type=lasource]","name",".mdl-card");
	cRenderCards::body_end();
	cRenderCards::action_start();
		cADCommon::button(cADControllerUI::log_analytics_config());
		cRender::button("back to analytics","analytics.php");
		cRender::button("transaction analytics","trans.php");
	cRenderCards::action_end();
cRenderCards::card_end();

$aData = cADRestUI::GET_log_analytics_sources();
cDebug::vardump($aData);

foreach ($aData as $oSource){
	if ($oSource->enabled){
		cRenderCards::card_start("<span type='lasource' name='$oSource->name'>$oSource->name</span>");
		cRenderCards::body_start();
			$oConfig = $oSource->fileCollectionConfiguration;
			echo "Created By:: $oSource->createdBy <br>";
			echo "Source Type: $oSource->sourceType <br>";
			echo "Filename: $oConfig->path <br>";
			if ($oConfig->pathExtractedFieldsGrok)
				echo "grok: $oConfig->pathExtractedFieldsGrok";
			?><div type="logsource" 
					<?=cRender::LOG_ID_QS?>="<?=$oSource->id?>" 
					home="<?=$home?>">
				loading extractions...
			</div><?php
		cRenderCards::body_end();
		cRenderCards::card_end();
	}
}
?>
<script language="javascript">
	function init_widget(piIndex, poElement){
		$(poElement).adlogdetail();
	}
	$("div[type=logsource]").each( init_widget);
</script>
<?php
cRenderHtml::footer();
?>
