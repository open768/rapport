<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2021 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED**************************************************************************/

//####################################################################
$home="../..";
require_once "$home/inc/common.php";

//####################################################################
cRenderHtml::header("jsession healthchecks ");
?><script language="javascript" src="<?=$jsWidgets?>/appcheckup.js"></script><?php

//cDebug::on(true);

$aApps = cADController::GET_all_Applications();
//####################################################################
cRenderCards::card_start();
	cRenderCards::body_start();
		echo "<div style='column-count:3'>";
			$chLast = null;
			foreach ($aApps as $oApp){
				$sApp = $oApp->name;
				$ch = $sApp[0];
				if ($ch !== $chLast){
					echo "<h3>$ch</h3>";
					$chLast = $ch;
				}
				echo "<a href='#$oApp->id'>$oApp->name</a><br>";
			}
		echo "</div>";
	cRenderCards::body_end();
	cRenderCards::action_start();
		cRender::add_filter_box("a[name]","appname",".mdl-card");
	cRenderCards::action_end();
cRenderCards::card_end();

//####################################################################

foreach ($aApps as $oApp){
	cRenderCards::card_start("<a name='$oApp->id' appname='$oApp->name'>$oApp->name</a>");
		cRenderCards::body_start();
		?><div 
				type="appcheckup" 
				<?=cRenderQS::APP_QS?>="<?=$oApp->name?>"
				<?=cRenderQS::APP_ID_QS?>="<?=$oApp->id?>"
				<?=cRenderQS::HOME_QS?>="<?=$home?>">
				loading...
		</div><?php
		cRenderCards::body_end();
	cRenderCards::card_end();
}
?>
	<script language="javascript">
		function init_a_checkup_widget(piIndex, poElement){
			$(poElement).adappcheckup();
		}
		function init_checkup_widgets(){
			$("DIV[type=appcheckup]").each(init_a_checkup_widget);
		}
		
		$(init_checkup_widgets);
	</script>
<?php


cRenderHtml::footer();
?>
