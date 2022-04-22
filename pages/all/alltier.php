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
cRenderHtml::header("All Tiers");
cRender::force_login();

//####################################################################

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************
	

try{
	$aApps = cADController::GET_all_Applications();
}catch( Exception $e){
	cCommon::errorbox("Error retrieving applications");
	cDebug::error($e);
}
if (count($aApps) == 0) {
	cCommon::errorbox("No Applications found");
	cRenderHtml::footer();
	return;
}
cChart::do_header();
?>
	<script language="javascript" src="<?=$jsWidgets?>/showtiers.js"></script>
	<script language="javascript" src="<?=$jsinc?>/extra/appd/metrics.js"></script>
<?php

//####################################################################
function app_toc($paApps){
	echo "<div style='column-count:3'>";
	$chLast = "";
	foreach ($paApps as $oApp){
		$ch = strtolower($oApp->name[0]);
		if ($ch !== $chLast){
			echo "<h2>".strtoupper($ch)."</h2>";
			$chLast = $ch;
		}
		echo "<a href='#$oApp->id'>$oApp->name</a><br>";
	}
	echo "</div>";
}


//####################################################################
cRenderCards::card_start("Applications");
	cRenderCards::body_start();
		app_toc($aApps);
	cRenderCards::body_end();
	cRenderCards::action_start();
		cRender::add_filter_box("a[appname]","appname",".mdl-card");
		cADCommon::button(cADControllerUI::apps_home());
		$sUrl = cCommon::filename();
		if (!cRender::is_list_mode()){
			$sUrl = cHttp::build_url($sUrl,cRenderQS::LIST_MODE_QS,"1");
			cRender::button("list mode", $sUrl);
		}else			
			cRender::button("chart mode", $sUrl);
	cRenderCards::action_end();
cRenderCards::card_end();

//####################################################################
//todo should be asynchronous - page will crash when there are too many applications

foreach ( $aApps as $oApp){
	cRenderCards::card_start("Tiers in <a name='$oApp->id' appname='$oApp->name'>$oApp->name</a>");
		cRenderCards::body_start();
			?>
			<div type="adWidget" 
				<?=cRenderQS::LIST_MODE_QS?>="<?=cHeader::get(cRenderQS::LIST_MODE_QS)?>"
				<?=cRenderQS::APP_ID_QS?>="<?=$oApp->id?>"
				<?=cRenderQS::HOME_QS?>="<?=$home?>"
			>please wait...</div>
			<?php
		cRenderCards::body_end();
		cRenderCards::action_start();
			cRenderMenus::show_app_functions($oApp);
		cRenderCards::action_end();
	cRenderCards::card_end();
}
?><script language="javascript">
		function init_widget(piIndex, poElement){
			$(poElement).adshowtiers();
		}
		function init_widgets(){
			$("DIV[type='adWidget']").each(init_widget);
		}
		
		$( init_widgets	);
</script><?php

cRenderHtml::footer();
?>
