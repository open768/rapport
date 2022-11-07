<?php
	$home="../..";
	require_once "$home/inc/common.php";
?>
<!-- ################################################################ -->
<!-- # Footer begins here
<!-- ################################################################ -->
<footer class="mdl-mini-footer">
	<div class="mdl-mini-footer__left-section" id="footer_leftsection">
		<!-- ******************************************************************* -->
		<script>
			function init_modal_dialog(psButtonID, psTxtID){
				$(psButtonID).click(
					function (){
						showDialog( { text: $(psTxtID).html() });
					}
				)
			}
		</script>
		<!-- ******************************************************************* -->
		<div class="mdl-logo" id="footer_logo">
			<img id="cklogo" class="cklogo" src="<?=$home?>/images/chicken_icon.png">
			<div class="mdl-tooltip" for="cklogo">
				We are Chicken Katsu.
			</div>		
			<script>
			$(
				function(){
					$('#cklogo').click( function(){window.open("https://www.chickenkatsu.co.uk");});
				}
			);
			</script>
		</div><!-- id="footer_logo" -->

		<!-- ******************************************************************* -->
		<div style="display:inline" id="footer_about">
			<button class="mdl-button mdl-js-button mdl-button--raised" id="ftrabout" onclick="document.location.href='<?=$home?>/pages/about/about.php';return false">About</button>
		</div><!--  id="footer_about" -->
		
		<!-- ******************************************************************* -->
		<div style="display:inline" id="footer_copyright">
			<button class="mdl-button mdl-js-button mdl-button--raised" id="ftrcopy">Copyright</button>
			<div style="display:none" id="dlg_copy">
				<b>Copyright (c) 2013-2021 <a target="katsu" href="https://www.chickenkatsu.co.uk/">ChickenKatsu</a></b>
				<p>
				This software is protected by copyright under the terms of the 
				<a href="http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode">Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License</a>. 
				For licenses that allow for commercial evaluation please contact cluck@chickenkatsu.co.uk
				<p>
				Licensed to : <?=cSecret::LICENSED_TO?><!-- <?=cSecret::LICENSE_COMMENT?>-->
				USE AT YOUR OWN RISK - NO GUARANTEES OF ANY FORM ARE EITHER EXPRESSED OR IMPLIED.
			</div>
			<script>init_modal_dialog('#ftrcopy', "#dlg_copy");</script>
		</div><!--  id="footer_copyright" -->
		
		<!-- ******************************************************************* -->
		<div style="display:inline" id="footer_info">
			<button class="mdl-button mdl-js-button mdl-button--raised" id="ftrinfo">Information</button>
			<div style="display:none" id="dlg_info">
				We're on <a href="https://github.com/open768/rapport">Github</a><br>
				No passwords are stored by this application.<br>
				Appdynamics is a trademark of Appdynamics LLC which is part of Cisco. This site is not affiliated with either Appdynamics or Cisco.
			</div>
			<script>init_modal_dialog('#ftrinfo', "#dlg_info");</script>
		</div><!-- id="footer_info" -->
		
		<!-- ******************************************************************* -->
		<div style="display:inline" id="footer_lib">
			<button class="mdl-button mdl-js-button mdl-button--raised" id="ftrlib">Libraries Used</button>
			<div style="display:none" id="dlg_lib">
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
					<li class="mdl-list__item mdl-list__item--three-line">
						<span class="mdl-list__item-primary-content">
							<a target="new" href="https://github.com/SheetJS/"
								><span class="material-icons mdl-list__item-icon">launch</span>
							</a>
							<span>SheetJS</span>
							<span class="mdl-list__item-sub-title">
								licensed under the MIT License
							</span>
						</span>
					</li>
				</ul>
			</div><!-- id="dlg_lib" -->
			<script>init_modal_dialog('#ftrlib', "#dlg_lib");</script>
		</div ><!-- id="footer_lib" -->
		
		<!-- ******************************************************************* -->
		<div style="display:inline" id="footer_feedback">
			<button class="mdl-button mdl-js-button mdl-button--raised" id="ftrabout" onclick="window.open('https://github.com/open768/rapport/issues');return false">Feedback</button>
		</div><!--  id="footer_feedback" -->
		
	</div> <!-- id="footer_leftsection" -->
</footer>
<!-- ################################################################ -->
<!-- # Footer ends here
<!-- ################################################################ -->
