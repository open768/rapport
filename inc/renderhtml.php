<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2021 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/
$js_extra = "$jsinc/extra";

//#######################################################################
//#######################################################################
class cRenderHtml{
	const CONTROLLER_ID = "RenderCID";
	const TIME_ID = "RenderTMID";
	const TITLE_ID = "RenderTID";
	const NAVIGATION_ID = "RenderNID";
	const DIV_COLUMNS = 4;
	static $load_google_charts = false;

	//**************************************************************************
	public static function common_header (){
		global $jsinc, $js_extra, $home;
		?>
			<!-- 
				############################################################################
				# COMMON HEADER START
				############################################################################ 
			-->
			<!-- analytics tags -->
			<?php
				if (cSecret::ENABLE_GOOGLE_ANALYTICS) 
					cGoogleAnalytics::browser_agent( cSecret::$GOOGLE_TAG_ID);
				if (cSecret::ENABLE_NR_EUM)
					cNewRelic::browser_agent();// TBD pass secret data
			?>
			
			<!-- W3schools widgets -->
			<link rel="stylesheet" type="text/css" href="https://www.w3schools.com/w3css/4/w3.css">			
			
			<!-- google fonts fonts.googleapis.com -->			
			<link href="https://fonts.googleapis.com/css?family=Courgette" rel="stylesheet">
			<link href="https://fonts.googleapis.com/css?family=Material+Icons%7CMaterial+Icons+Outlined%7CMaterial+Icons+Two+Tone%7CMaterial+Icons+Round%7CMaterial+Icons+Sharp" rel="stylesheet">
			<link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.indigo-pink.min.css">
			<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" type="text/css">

			<!-- Material Design Lite https://getmdl.io/components/index.html -->			
			<script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<link rel="stylesheet" href="<?=$js_extra?>/jquery-qtip/jquery.qtip.min.css">			
			<link rel="stylesheet" href="<?=$js_extra?>/jquery-mdl-dialog/mdl-jquery-modal-dialog.css">

			<!-- additional style sheets -->			
			<link rel="stylesheet" href="<?=$js_extra?>/jquery-spinner/css/gspinner.min.css">			
			<LINK rel="stylesheet" type="text/css" href="<?=$home?>/css/rapport.css" >			
			
			<!-- google charts JS  -->
			<?php
				if (self::$load_google_charts){ ?>
					<script src="https://www.gstatic.com/charts/loader.js"></script>
					<script >
						google.charts.load('current', {packages: ['corechart']});
					</script>
				<?php }
			?>
			
			<!-- jquery -->
			<link rel="stylesheet" type="text/css" href="<?=$home?>/css/jquery-ui/jquery-ui.min.css">
			<script src="<?=$js_extra?>/jquery/jquery-3.2.1.min.js"></script>
			<script src="<?=$js_extra?>/jquery-ui/jquery-ui.min.js"></script>
			<script src="<?=$js_extra?>/tablesorter/jquery.tablesorter.min.js"></script>
			<script src="<?=$js_extra?>/jquery-inview/jquery.inview.min.js"></script>
			<script src="<?=$js_extra?>/jquery-visible/jquery.visible.min.js"></script>
			<script src="<?=$js_extra?>/jquery-qtip/jquery.qtip.min.js"></script>
			<script src="<?=$js_extra?>/jquery-mdl-dialog/mdl-jquery-modal-dialog.js"></script>
			<script src="<?=$js_extra?>/jquery-flowtype/flowtype.js"></script>
			<script src="<?=$js_extra?>/jquery-spinner/g-spinner.min.js"></script>
			
			<!-- bean -->
			<script src="<?=$js_extra?>/bean/bean.js"></script>
					
			<!-- common JS  -->
			<script src="<?=$jsinc?>/ck-inc/debug.js"></script>
			<script src="<?=$jsinc?>/ck-inc/common.js"></script>
			<script src="<?=$jsinc?>/ck-inc/http.js"></script>
			<script src="<?=$jsinc?>/ck-inc/httpqueue.js"></script>
			<script src="<?=$jsinc?>/ck-inc/jquery/jquery.inviewport.js"></script>
			<script src="<?=$jsinc?>/ck-inc/queueifvisible.js"></script>
			<script src="<?=$jsinc?>/ck-inc/render.js"></script>
			
			<!-- Rapport JS  -->
			<script src="<?=$home?>/js/menus.js"></script>
			<script src="<?=$home?>/js/common.js"></script>
			<script src="<?=$home?>/js/renderqs.js"></script>
			
			<!-- widgets  -->
			<script src="<?=$home?>/js/widgets/common.js"></script>
			<script >
				var cLocations = {
					home: "<?=$home?>",
					rest: "<?=$home?>/rest"
				};
			</script>
			<!-- 
				############################################################################
				# COMMON HEADER END
				############################################################################ 
			-->
		<?php
	}
	
	//**************************************************************************
	public static function widget_header (){
		?><!DOCTYPE html>
			<html lang="en">
			<head>
				<?php 		
					self::common_header();
				?>
			</head>
			<BODY id="widget_content">
		<?php
		//check for login or a login token_get_all
	}
	
	public static function widget_footer(){
		?></BODY></HTML><?php
	}
	
	//**************************************************************************
	public static function header ($psTitle){
		global $jsinc, $js_extra, $home;
		cDebug::enter();
		$bLoggedin = true;

		//-------------------------------------------------------------
		//getting credentials to pre-fill the form
		cDebug::extra_debug("getting credentials");
		$oCred = cRenderObjs::get_AD_credentials();
		$bLoggedin = ($oCred != null);
		
		//-------------------------------------------------------------
		cDebug::extra_debug("displaying page");
		?><!DOCTYPE html>
			<html lang="en">
			<!-- 
				############################################################################
				# HEADER START
				############################################################################ 
			-->
			<head>
				<title><?=$psTitle?></title>
				<?php 		
					if (cDebug::is_debugging()) {
						echo "</head><body>";
						return;				
					}
					self::common_header();
				?>
				<script src="<?=$home?>/js/widgets/menu.js"></script>
			</head>
			<BODY>
				<div class="mdl-layout mdl-js-layout mdl-color--light-blue-200 mdl-color-text--blue-grey-500" id="mdl-container">
					<div class="mdl-layout__drawer">
						<span class="mdl-layout__title">Rapport</span>
						<nav class="mdl-navigation" id="<?=self::NAVIGATION_ID?>"></nav>
					</div>
					<header class="mdl-layout__header">
						<div class="mdl-layout-icon"></div>
						<div class="mdl-layout__header-row">
							<span class="mdl-layout__title" id="<?=self::TITLE_ID?>"><?=$psTitle?></span>
							<div class="mdl-layout-spacer"></div>
							<nav class="mdl-navigation" id="<?=self::CONTROLLER_ID?>">
							<?php
								if ($bLoggedin){
									echo "Account: $oCred->account &mdash;&nbsp;".cADCommon::get_time_label();
									cRender::show_time_options();
								}else
									echo "not logged in";
							?>
							</nav>
							<div class="mdl-layout-spacer"></div>
							<nav class="mdl-navigation" id="<?=self::TIME_ID?>">
								<?=($bLoggedin?"initialising..":"")?>
							</nav>
						</div>
					</header>
					<!-- 
						############################################################################
						# HEADER END
						############################################################################ 
					-->

					<main class="mdl-layout__content" style="flex: 1 0 auto;">
						<div id="page_content">
						<!-- ####### PAGE CONTENT GOES BELOW HERE -->
  		<?php
		//show the navigation menu - this updates the material design navigation menu
		if ($bLoggedin)	cRenderMenus::top_menu();

		cDebug::flush();
		cDebug::leave();
		//error_reporting(E_ALL & ~E_WARNING);
	}
	
	//**************************************************************************
	public static function footer (){
		global $home;
		if (cDebug::is_debugging()) return;
		?>
						<!-- #######  PAGE CONTENT STOPS ABOVE HERE-->
					</div> <!-- id=page_content -->
				</main>
				<div class="footer-container" id="page_footer">
					Loading Footer...
				</div><!--  id="page_footer" -->
			</div><!-- id="mdl-container" -->
			<script>
				$(
					function(){
						$("#page_footer").load("<?=$home?>/pages/content/footer.php")
						$("button.blue_button").removeAttr("class").button();
						cMenus.renderMenus();
						//$('body').flowtype();
					}
				);
			</script>
		</BODY>
	</HTML><?php
	}	
}
?>