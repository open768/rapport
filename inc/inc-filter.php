<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2016 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/
require_once("$phpinc/ckinc/header.php");
require_once("$phpinc/ckinc/debug.php");

//#######################################################################
//#######################################################################
final class cFilterRule{
	public $type;
	public $regex;
}

//#######################################################################
// rules are of the form "i,thing|o,thing"

//#######################################################################
class cFilterRules{
	const RULE_DELIM_CHAR = "|";
	const RULE_SPLIT_CHAR = ",";
	const RULE_TYPE_IN = "i";
	const RULE_TYPE_OUT = "o";
	
	private $maRules = [];

	//****************************************************************************
	public function is_filtered( $psWhat){
		$bFirstRule = true;
		$bFilteredOut = false;
		
		foreach ($this->maRules as $oRule){
			if ($bFirstRule && ($oRule->type == self::RULE_TYPE_IN))	$bFilteredOut = true;
			$bFirstRule = false;
			$sExpression = $oRule->regex;
			if (preg_match("/$sExpression/",$psWhat))
				$bFilteredOut = ($oRule->type == self::RULE_TYPE_OUT);
		}
		
		return $bFilteredOut;
	}
	
	//****************************************************************************
	public function parse_rules($psRules){
		if (($psRules == null) || ($psRules == "")) return;
		
		$aRules = explode(self::RULE_DELIM_CHAR, $psRules);
		foreach ($aRules as $sRule){
			
			$aParts = explode(self::RULE_SPLIT_CHAR, $sRule);
			if (count($aParts) !== 2) cDebug::error("rule must have 2 parts");
			
			$oRule = new cFilterRule();
			$sRuleType = $aParts[0];
			switch ($sRuleType){
				case self::RULE_TYPE_IN:
				case self::RULE_TYPE_OUT:
					$oRule->type = $sRuleType;
					$oRule->regex = $aParts[1];
					$this->maRules[] = $oRule;
					break;
				default:
					cDebug::error("unrecognised rule type $sRuleType");
			}
		}
	}
}

//#######################################################################
//#######################################################################
class cFilter{
	const FILTER_APP_QS = "fa";
	const FILTER_TIER_QS = "ft";
	const FILTER_NODE_QS = "fn";
	
	private static $praFilters = [];
	
	//************************************************************
	public static function isFiltered(){
		if (cHeader::get(self::FILTER_APP_QS)) return true;
		if (cHeader::get(self::FILTER_TIER_QS)) return true;
		if (cHeader::get(self::FILTER_NODE_QS)) return true;
		return false;
	}
	
	public static function isAppFilteredOut($psThing){ return self::pr__isFilteredOut(self::FILTER_APP_QS, $psThing);	}
	public static function isTierFilteredOut($psThing){ return self::pr__isFilteredOut(self::FILTER_TIER_QS, $psThing);	}
	public static function isNodeFilteredOut($psThing){ return self::pr__isFilteredOut(self::FILTER_NODE_QS, $psThing);	}
	
	//************************************************************
	public static function makeTierFilter($psTier, $pbFilterIn = true){
		$sFilterChar = ($pbFilterIn? cFilterRules::RULE_TYPE_IN: cFilterRules::RULE_TYPE_OUT);
		return cHttp::build_qs(null,self::FILTER_TIER_QS , $sFilterChar.cFilterRules::RULE_SPLIT_CHAR.$psTier);
	}
	
	//************************************************************
	//************************************************************
	private static function pr__isFilteredOut( $psWhat, $psThing ){
		$oRules = self::pr__read_filter($psWhat);
		return $oRules->is_filtered($psThing);
	}
	
	//************************************************************
	private static function pr__read_filter( $psWhat ){
		if (!array_key_exists($psWhat, self::$praFilters)){
			$oRules = new cFilterRules;
			$oRules->parse_rules( cHeader::get($psWhat));
			self::$praFilters[$psWhat] = $oRules;
		}
			
		return self::$praFilters[$psWhat];
	}
}
?>