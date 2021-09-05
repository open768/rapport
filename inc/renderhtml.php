<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2021 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

//#######################################################################
//#######################################################################
class cRenderHtml{
	const CONTROLLER_ID = "RenderCID";
	const TIME_ID = "RenderTMID";
	const TITLE_ID = "RenderTID";
	const NAVIGATION_ID = "RenderNID";

	//**************************************************************************
	public static function header ($psTitle){
		global $jsinc, $home;
		cDebug::enter();
		$bLoggedin = true;

		if (cDebug::is_debugging()) return;

		//-------------------------------------------------------------
		//getting credentials to pre-fill the form
		cDebug::extra_debug("getting credentials");
		try{
			$oCred = cRenderObjs::get_appd_credentials();
		}
		catch( Exception $e)
		{
			$bLoggedin = false;
		}
		
		//-------------------------------------------------------------
		cDebug::extra_debug("displaying page");
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<title><?=$psTitle?></title>
			<LINK rel="stylesheet" type="text/css" href="<?=$home?>/css/reporter.css" >
			<link rel="stylesheet" type="text/css" href="<?=$home?>/css/jquery-ui/jquery-ui.min.css">
			<link rel="stylesheet" href="<?=$jsinc?>/jquery-spinner/css/gspinner.min.css">			
			<link rel="stylesheet" href="<?=$jsinc?>/jquery-qtip/jquery.qtip.min.css">			
			<link rel="stylesheet" href="<?=$jsinc?>/jquery-mdl-dialog/mdl-jquery-modal-dialog.css">
			
			<!-- google fonts fonts.googleapis.com -->			
			<link href="https://fonts.googleapis.com/css?family=Courgette" rel="stylesheet">
			<link href="https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined|Material+Icons+Two+Tone|Material+Icons+Round|Material+Icons+Sharp" rel="stylesheet">
			<link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.indigo-pink.min.css">
			<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" type="text/css">

			<!-- Material Design Lite https://getmdl.io/components/index.html -->			
			<script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
			<meta name="viewport" content="width=device-width, initial-scale=1.0">

			<!-- Global site tag (gtag.js) - Google Analytics -->
			<script async src="https://www.googletagmanager.com/gtag/js?id=UA-51550338-2"></script>
			<script>
			  window.dataLayer = window.dataLayer || [];
			  function gtag(){dataLayer.push(arguments);}
			  gtag('js', new Date());

			  gtag('config', 'UA-51550338-2');
			</script>			
			<!-- End Google Tag Manager -->
			<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
			
			<script src="<?=$jsinc?>/ck-inc/debug.js"></script>
			<script src="<?=$jsinc?>/jquery/jquery-3.2.1.min.js"></script>
			<script src="<?=$jsinc?>/jquery-ui/jquery-ui.min.js"></script>
			<script src="<?=$jsinc?>/tablesorter/jquery.tablesorter.min.js"></script>
			<script type="text/javascript" src="<?=$jsinc?>/jquery-inview/jquery.inview.min.js"></script>
			<script type="text/javascript" src="<?=$jsinc?>/jquery-visible/jquery.visible.min.js"></script>
			<script type="text/javascript" src="<?=$jsinc?>/jquery-spinner/g-spinner.min.js"></script>
			<script type="text/javascript" src="<?=$jsinc?>/jquery-qtip/jquery.qtip.min.js"></script>
			<script type="text/javascript" src="<?=$jsinc?>/jquery-mdl-dialog/mdl-jquery-modal-dialog.js"></script>
			<script type="text/javascript" src="<?=$jsinc?>/jquery-flowtype/flowtype.js"></script>
			<script type="text/javascript" src="<?=$jsinc?>/bean/bean.js"></script>

			<script type="text/javascript" src="<?=$jsinc?>/ck-inc/debug.js"></script>
			<script type="text/javascript" src="<?=$jsinc?>/ck-inc/common.js"></script>
			<script type="text/javascript" src="<?=$jsinc?>/ck-inc/http.js"></script>
			<script type="text/javascript" src="<?=$jsinc?>/ck-inc/httpqueue.js"></script>
			<script type="text/javascript" src="<?=$jsinc?>/ck-inc/jquery/jquery.inviewport.js"></script>
			<script type="text/javascript" src="<?=$jsinc?>/ck-inc/jquery/jqueryui.slideout.js"></script>
			
			<script src="<?=$home?>/js/widgets/chart.js"></script>
			<script src="<?=$home?>/js/menus.js"></script>
			<script src="<?=$home?>/js/widgets/appdmenu.js"></script>
			<script src="<?=$home?>/js/common.js"></script>
			<script src="<?=$home?>/js/qtip-init.js"></script>
			<script src="<?=$home?>/js/dialog-init.js"></script>
		</head>
		<BODY>
			<div class="mdl-layout mdl-js-layout mdl-color--light-blue-200 mdl-color-text--blue-grey-500">
				<div class="mdl-layout__drawer">
					<span class="mdl-layout__title">Reporter</span>
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
								echo "Account: $oCred->account &mdash;&nbsp;".cAppDynCommon::get_time_label();
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
				<main class="mdl-layout__content" style="flex: 1 0 auto;">
  <?php
		//show the navigation menu - this updates the material design navigation menu
		cDebug::extra_debug("showing top menu");
		cRenderMenus::top_menu();
		cDebug::flush();
		cDebug::leave();
		//error_reporting(E_ALL & ~E_WARNING);
	}
	
	//**************************************************************************
	public static function footer (){
		global $home;
		if (cDebug::is_debugging()) return;
		?>
				</main><!-- page content -->
				<div class="footer-container">
					<footer class="mdl-mini-footer">
						<div class="mdl-mini-footer__left-section">
							<div class="mdl-logo">
								<img id="cklogo" class="cklogo" src="<?=$home?>/images/chicken_icon.png">
								<div class="mdl-tooltip" for="cklogo">
									We are Chicken Katsu.
								</div>		
								<script language="javascript">
								$(
									function(){
										$('#cklogo').click( function(){window.open("https://www.chickenkatsu.co.uk");});
									}
								);
								</script>
							</div>
							<span>
								<button class="mdl-button mdl-js-button mdl-button--raised" id="ftrabout" onclick="document.location.href='<?=$home?>/pages/about.php';return false">About</button>
							</span>
							<span>
								<button class="mdl-button mdl-js-button mdl-button--raised" id="ftrcopy">Copyright</button>
								<div style="display:none" class="dialog" for="ftrcopy" title="Copyright">
									<b>Copyright (c) 2013-2021 <a target="katsu" href="https://www.chickenkatsu.co.uk/">ChickenKatsu</a></b>
									<p/>
									This software is protected by copyright under the terms of the 
									<a href="http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode">Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License</a>. 
									For licenses that allow for commercial evaluation please contact cluck@chickenkatsu.co.uk
									<p/>
									Licensed to : <?=cSecret::LICENSED_TO?><!-- <?=cSecret::LICENSE_COMMENT?>-->
									USE AT YOUR OWN RISK - NO GUARANTEES OF ANY FORM ARE EITHER EXPRESSED OR IMPLIED.
									<p>
								</div>
							</span>
							<span>
								<button class="mdl-button mdl-js-button mdl-button--raised" id="ftrinfo">Information</button>
								<div style="display:none" class="dialog" for="ftrinfo" title="Information">
									We're on <a href="https://github.com/open768/appdynamics-reporter">Github</a><br>
									No passwords are stored by this application.<br>
									Appdynamics is a trademark of Appdynamics LLC which is part of Cisco. This site is not affiliated with either Appdynamics or Cisco.
								</div>
							</span>
							<span>
								<button class="mdl-button mdl-js-button mdl-button--raised" id="ftrLibraries">Libraries Used</button>
								<div style="display:none" class="dialog" for="ftrLibraries" title="Libraries Used">
									<ul class="mdl-list">
										<li class="mdl-list__item mdl-list__item--three-line">
											<span class="mdl-list__item-primary-content">
												<a target="new" href="https://developers.google.com/chart/"
													><span class="material-icons mdl-list__item-icon">launch</span>
												</a>
												<span>Google charts</span>
												<span class="mdl-list__item-sub-title">
													licensed under the Creative Commons Attribution license.
												</span>
											</span>
										</li>
										<li class="mdl-list__item mdl-list__item--three-line">
											<span class="mdl-list__item-primary-content">
												<a target="new" href="http://tablesorter.com/"
													><span class="material-icons mdl-list__item-icon">launch</span>
												</a>
												<span>tablesorter</span>
												<span class="mdl-list__item-sub-title">
													by Christian Bach licensed under the MIT license.
												</span>
											</span>
										</li>
										<li class="mdl-list__item mdl-list__item--three-line">
											<span class="mdl-list__item-primary-content">
												<a target="new" href="https://gist.github.com/umidjons/8396981"
													><span class="material-icons mdl-list__item-icon">launch</span>
												</a>
												<span>pub sub pattern</span>
												<span class="mdl-list__item-sub-title">
													by Baylor Rae licensed under the GNU General Public license
												</span>
											</span>
										</li>
										<li class="mdl-list__item mdl-list__item--three-line">
											<span class="mdl-list__item-primary-content">
												<a target="new" href="https://getmdl.io/"
													><span class="material-icons mdl-list__item-icon">launch</span>
												</a>
												<span>Google material-design-lite</span>
												<span class="mdl-list__item-sub-title">
													licensed under the Apache License 2.0
												</span>
											</span>
										</li>
										<li class="mdl-list__item mdl-list__item--three-line">
											<span class="mdl-list__item-primary-content">
												<a target="new" href="https://github.com/oRRs/mdl-jquery-modal-dialog/"
													><span class="material-icons mdl-list__item-icon">launch</span>
												</a>
												<span>jquery modal dialog</span>
												<span class="mdl-list__item-sub-title">
													By Oliver Rennies: The MIT License (MIT)
												</span>
											</span>
										</li>
										<li class="mdl-list__item mdl-list__item--three-line">
											<span class="mdl-list__item-primary-content">
												<a target="new" href="http://simplefocus.com/flowtype/"
													><span class="material-icons mdl-list__item-icon">launch</span>
												</a>
												<span>flowtype</span>
												<span class="mdl-list__item-sub-title">
													licensed under the MIT License
												</span>
											</span>
										</li>
										
									</ul>
								</div idref="foot_libs_text">
							</span>
						</div>
					</footer>
				</div> <!-- footer container -->
			</div> <!-- page layout -->
			<script language="javascript">
				$(
					function(){
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
