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
class cRenderCards{
	private static $iID = 0;
	//**************************************************************************
	public static function card_start($psTitle="", $psExtraClass=""){
		self::$iID++;
		$sClass = "mdl-card mdl-shadow--2dp rapport-card";
		if ($psExtraClass !== "") $sClass.=" $psExtraClass";
		?><div class='<?=$sClass?>' id="RCID_<?=self::$iID?>"><?php
		if ($psTitle !== ""){
			self::title_start();
			echo "<font class='card_title'>$psTitle</font>";
			self::title_end();
		}
	}
	//**************************************************************************
	public static function title_start(){
		?><div class='mdl-card__title'><?php
	}
		//**************************************************************************
	public static function action_start(){
		?><div class='mdl-card__actions mdl-card--border'><?php
	}

	//**************************************************************************
	public static function body_start(){
		?><div class='mdl-card__supporting-text'><?php
	}
	//**************************************************************************
	public static function action_end(){
		?></div><?php
	}
	//**************************************************************************
	public static function title_end(){
		?></div><?php
	}
	//**************************************************************************
	public static function body_end(){
		?></div><?php
	}
	public static function card_end(){
		?></div><p><!--mdl-card--><?php
	}
	
	public static function chip($psContent){
		?><span class="mdl-chip">
			<span class="mdl-chip__text"><?=$psContent?></span>
		</span>&nbsp;<?php
	}
}
?>
