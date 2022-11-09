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
cRenderHtml::header("Schemas");
cRender::force_login();

$aSchemas = cADAnalytics::list_schemas();

//####################################################################
cRenderCards::card_start("Analytics schemas");
	cRenderCards::body_start();
		echo "there are ".count($aSchemas)." analytics schemas<p>";
		cCommon::div_with_cols(cRenderHTML::DIV_COLUMNS);
			foreach ($aSchemas as $sSchemaName)
				echo "<a href='#$sSchemaName'>$sSchemaName</a><br>";
		echo '</DIV>';
	cRenderCards::body_end();
	cRenderCards::action_start();
		cRender::button("back to analytics","analytics.php");
	cRenderCards::action_end();
cRenderCards::card_end();
		
foreach ($aSchemas as $sSchemaName){
	cRenderCards::card_start("<a name='$sSchemaName'>$sSchemaName</a>");
		cRenderCards::body_start();
			cCommon::div_with_cols(cRenderHTML::DIV_COLUMNS);
				$aFields = cADAnalytics::schema_fields($sSchemaName);
				foreach ($aFields as $oField)
					echo "$oField->fieldName: $oField->fieldType<br>";
			echo '</DIV>';
		cRenderCards::body_end();
	cRenderCards::card_end();
}
cRenderHtml::footer();
?>