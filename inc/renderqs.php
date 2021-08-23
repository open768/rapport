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
class cRenderQS{
	public static function get_base_app_QS( $poApp){
		//cDebug::enter();
		$appQS = cHttp::build_qs(null, cRender::APP_QS, $poApp->name);
		$appQS = cHttp::build_qs($appQS, cRender::APP_ID_QS, $poApp->id);
		//cDebug::leave();
		return $appQS;
	}

	//******************************************************************************************
	public static function get_base_tier_QS( $poTier){
		//cDebug::enter();
		$sAppQs = self::get_base_app_QS($poTier->app);
		$sTierQs = cHttp::build_qs($sAppQs, cRender::TIER_QS, $poTier->name);
		$sTierQs = cHttp::build_qs($sTierQs, cRender::TIER_ID_QS, $poTier->id);
		//cDebug::leave();
		return $sTierQs;
	}
	
	//******************************************************************************************
	public static function get_base_node_QS( $poTier, $piNode, $psNode){
		//cDebug::enter();
		$sTierQS = self::get_base_tier_QS($poTier);
		$sNodeQs = cHttp::build_qs($sTierQS, cRender::NODE_QS, $psNode);
		$sNodeQs = cHttp::build_qs($sNodeQs, cRender::NODE_ID_QS, $piNode);
		//cDebug::leave();
		return $sNodeQs;
	}

}
?>
