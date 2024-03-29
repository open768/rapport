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


//-----------------------------------------------

//####################################################################
cRenderHtml::$load_google_charts = true;
cRenderHtml::header("All Remote Services");
cRender::force_login();
cChart::do_header();
$title ="All Remote Services";
?><script src="<?=$jsWidgets?>/appbackends.js"></script><?php

//####################################################################
$oApps = cADController::GET_all_Applications();

//********************************************************************
if (cAD::is_demo()){
	cRenderCards::card_start("Not Supported");
	cRenderCards::body_start();
		cCommon::errorbox("function not supported for Demo");
	cRenderCards::body_end();
	cRenderCards::action_end();
	cRenderHtml::footer();
	exit;
}
//********************************************************************
cRenderCards::card_start("Applications");
	cRenderCards::body_start();
		cCommon::div_with_cols(cRenderHTML::DIV_COLUMNS);
			$sLastCh = "";
			foreach ($oApps as $oApp){
				$sCh = strtoupper(($oApp->name)[0]);
				if ($sCh !== $sLastCh){
					echo "<h3>$sCh</h3>";
					$sLastCh = $sCh;
				}
				echo "<a href='#$oApp->id'>$oApp->name</a><br>";
			}
		echo "</div>";
	cRenderCards::body_end();
	cRenderCards::action_start();
		cRender::button("Sort by Backend Name", "allbackendsbyname.php");
		$sUrl = "allbackends.php";
		if (!cRender::is_list_mode()){
			$sUrl.= "?".cRenderQS::LIST_MODE_QS;
			cRender::button("list mode", $sUrl);
		}else			
			cRender::button("chart mode", $sUrl);
	cRenderCards::action_end();	
cRenderCards::card_end();

cRenderCards::card_start();
	cRenderCards::action_start();
		cRender::$FORCE_FILTERBOX_DISPLAY = true;
		cRender::add_filter_box("a[appname]","appname",".mdl-card");
	cRenderCards::action_end();	
cRenderCards::card_end();

//####################################################################
foreach ($oApps as $oApp){
	$sApp = $oApp->name;
	$sID = $oApp->id;
	$sUrl = cHttp::build_url("../app/appext.php", cRenderQS::APP_QS, $sApp);
	$sUrl = cHttp::build_url($sUrl, cRenderQS::APP_ID_QS, $sID);
	
	cRenderCards::card_start("<a name='$sID' appname='$sApp'>$sApp</a>");
	cRenderCards::body_start();
		echo "<div type='adWidget' home='$home' ".
				cRenderQS::APP_QS."='$oApp->name' ".
				cRenderQS::APP_ID_QS."='$oApp->id' ".
				cRenderQS::LIST_MODE_QS."=".cRender::is_list_mode().">".
				"please Wait - loading backend  for app $oApp->name".
		"</div>";
	cRenderCards::body_end();
	cRenderCards::action_start();
		cRender::button("See Details...", $sUrl);
		cRenderMenus::show_app_functions($oApp);
	cRenderCards::action_end();
	cRenderCards::card_end();
}

	?><script>
		function init_widget(piIndex, poElement){
			$(poElement).adappbackend();
		}
		
		function init_health_widgets(){
			$("DIV[type='adWidget']").each(init_widget);
		}
		
		$( init_health_widgets);
	</script><?php

cChart::do_footer();
cRenderHtml::footer();
?>
