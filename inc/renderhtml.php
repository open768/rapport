<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2018 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

//#######################################################################
//#######################################################################
class cRenderHtml{
	//**************************************************************************
	public static function header ($psTitle){
		global $jsinc, $home;
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
			
			<!-- Material Design Lite https://getmdl.io/components/index.html -->
			<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
			<link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.indigo-pink.min.css">
			<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Roboto:300,400,500,700" type="text/css">
			<script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
			<link href="https://fonts.googleapis.com/css?family=Courgette" rel="stylesheet">

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
			<script type="text/javascript" src="<?=$jsinc?>/bean/bean.js"></script>

			<script type="text/javascript" src="<?=$jsinc?>/ck-inc/debug.js"></script>
			<script type="text/javascript" src="<?=$jsinc?>/ck-inc/common.js"></script>
			<script type="text/javascript" src="<?=$jsinc?>/ck-inc/http.js"></script>
			<script type="text/javascript" src="<?=$jsinc?>/ck-inc/httpqueue.js"></script>
			<script type="text/javascript" src="<?=$jsinc?>/ck-inc/jquery/jquery.inviewport.js"></script>
			
			<script src="<?=$home?>/js/widgets/chart.js"></script>
			<script src="<?=$home?>/js/widgets/menus.js"></script>
			<script src="<?=$home?>/js/common.js"></script>
			<script src="<?=$home?>/js/qtip-init.js"></script>
		
		</head>
		<BODY>
			<div id="page_layout" class="mdl-layout mdl-js-layout mdl-color--light-blue-200 mdl-color-text--blue-grey-500">
				<div id= "page_content" class="mdl-layout__content">
		<?php
		cDebug::flush();
		//error_reporting(E_ALL & ~E_WARNING);
	}
	
	//**************************************************************************
	public static function footer (){
		?>
				<p>
				<div id="page_footer" class="mdl-mega-footer">
					<div class="mdl-mega-footer__middle-section"><div class="mdl-grid">
						<div class="mdl-cell mdl-cell-6-col">
							<b>Copyright (c) 2013-2018 <a target="katsu" href="https://www.chickenkatsu.co.uk/">ChickenKatsu</a></b>
							<p>
							This software is protected by copyright under the terms of the 
							<a href="http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode">Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License</a>. 
							For licenses that allow for commercial evaluation please contact cluck@chickenkatsu.co.uk
						</div>
						<div class="mdl-cell mdl-cell--2-col">
							We're on <a href="https://github.com/open768/appdynamics-reporter">Github</a><br>
							No passwords are stored by this application.<br>
							AppDynamics is a registered trademark of <a href="http://www.appdynamics.com/">AppDynamics, Inc</a>
						</div>
						<div class="mdl-cell mdl-cell--4-col">
							<button class="mdl-button mdl-js-button mdl-button--raised" id="foot_libs">Libraries Used</button>
							<div style="display:none" id="foot_libs_text">
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
								</ul>
							</div idref="foot_libs_text">
						</div>
					</div>
				</div>
					<div class="mdl-mega-footer__bottom-section">
						Licensed to : <?=cSecret::LICENSED_TO?><!-- <?=cSecret::LICENSE_COMMENT?>-->
						USE AT YOUR OWN RISK - NO GUARANTEES OF ANY FORM ARE EITHER EXPRESSED OR IMPLIED.
					</div>
				</div><!-- page footer -->
			</div><!-- page content -->
		</div> <!-- page layout -->
		<script language="javascript">

			function onclick_footlibs(){
				showDialog({
					title: 'Libraries Used',
					text: $('#foot_libs_text').html()
				})
			}
			
			$(
				function(){
					$("button.blue_button").removeAttr("class").button();
					cMenus.renderMenus();
					$('#foot_libs').click(	onclick_footlibs		);
				}
			);
		</script>
	</BODY></HTML><?php
	}	
}
?>
