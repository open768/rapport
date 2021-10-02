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
	private static $iCardID = 0;
	private static $iChipID = 0;

	//**************************************************************************
	//*
	//**************************************************************************
	public static function card_start($psTitle="", $psExtraClass=""){
		self::$iCardID++;
		$sClass = "mdl-card mdl-shadow--2dp rapport-card";
		if ($psExtraClass !== "") $sClass.=" $psExtraClass";
		?><div class='<?=$sClass?>' id="CARDID_<?=self::$iCardID?>"><?php
		if ($psTitle !== ""){
			self::title_start();
			echo "<font class='card_title'>$psTitle</font>";
			self::title_end();
		}
		return self::$iCardID;
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
	//*
	//**************************************************************************
	public static function action_end(){
		?></div><!-- mdl actions --><?php
	}
	//**************************************************************************
	public static function title_end(){
		?></div><!-- mdl title --><?php
	}
	//**************************************************************************
	public static function body_end(){
		?></div><!-- mdl body --><?php
	}
	//**************************************************************************
	public static function card_end(){
		?></div><p><!--mdl-card--><?php
	}
	
	//**************************************************************************
	//*
	//**************************************************************************
	public static function chip($psContent, $psTooltip=null){
		self::$iChipID++;
		?><span class="mdl-chip">
			<span class="mdl-chip__text" id="CHIPID_<?=self::$iChipID?>"><?=$psContent?></span>
		</span>&nbsp;<?php
		
		if ($psTooltip){ 
			?><div class="mdl-tooltip" data-mdl-for="CHIPID_<?=self::$iChipID?>"><?=$psTooltip?></div><?php 
		}
		return 	self::$iChipID;
	}
}
?>
