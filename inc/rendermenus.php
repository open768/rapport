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
class cRenderMenus{
	
	//******************************************************************************************
	// menus are rendered by clientside js in the footer() function in renderhtml.php
	//******************************************************************************************
	public static function show_app_functions(cADApp $poApp=null){
		global $home;
		cDebug::enter();
		$oCred = cRenderObjs::get_AD_credentials();
		if ($oCred->restricted_login) {
			cRender::button($poApp->name,null);
			cDebug::leave();
			return;
		}
		
		if ($poApp == null) $poApp = cRenderObjs::get_current_app();
		?>
			<div
				type="admenus" menu="appfunctions" 
				<?=cRenderQs::HOME_QS?>="<?=$home?>"
				<?=cRenderQs::APP_QS?>="<?=$poApp->name?>" 
				<?=cRenderQs::APP_ID_QS?>="<?=$poApp->id?>">
				<i>loading menu:<?=$poApp->name?></i>
			</div>
		<?php
		cDebug::leave();
	}

	//******************************************************************************************
	public static function show_app_change_menu($psCaption, $psURLFragment=null, $psExtraQS=""){
		global $home;
	
		cDebug::enter();
		$oCred = cRenderObjs::get_AD_credentials();
		if ($oCred->restricted_login) {
			cRender::button($psCaption,null);
			cDebug::leave();
			return;
		}
		
		$sApps_fragment = self::pr__get_apps_fragment();
		if ($psURLFragment == null)
			$psURLFragment=cCommon::filename();

		//TBD change to a DIV - widget can replace with a select menu
		?>
			<DIV
				type="admenus" menu="appchange" 
				<?=cRenderQs::HOME_QS?>="<?=$home?>"
				caption="<?=$psCaption?>" url="<?=$psURLFragment?>" 
				extra="<?=$psExtraQS?>" <?=$sApps_fragment?>
				>
				 - please wait
			</DIV>
		<?php
		self::show_app_functions();
		cDebug::leave();
	}
	
	//******************************************************************************************
	public static function show_app_agent_menu(cADApp $poApp = null){
		global $home;

		cDebug::enter();
		$oCred = cRenderObjs::get_AD_credentials();
		if ($oCred->restricted_login) {
			cDebug::leave();
			return;
		}
		
		if ($poApp == null) $poApp = cRenderObjs::get_current_app();
		//TBD change to a DIV - widget can replace with a select menu
		?>
			<DIV 
				type="admenus" menu="appagents" 				
				<?=cRenderQs::HOME_QS?>="<?=$home?>"
				<?=cRenderQs::APP_QS?>="<?=$poApp->name?>" <?=cRenderQs::APP_ID_QS?>="<?=$poApp->id?>">
			</DIV>
		<?php
		cDebug::leave();
	}


	//******************************************************************************************
	private static function pr__get_apps_fragment(){

		cDebug::enter();
		try{
			$aApps = cADController::GET_all_Applications();
		}
		catch (Exception $e)
		{
			cCommon::errorbox("Oops unable to get application data from controller");
			cDebug::leave();
			exit;
		}
		uasort($aApps,"sort_by_app_name" );
		$iCount=0;
		$sApps_fragment = "";
		foreach ($aApps as $oApp){
			$iCount++;
			$sApps_fragment.= " ".cRenderQS::APP_QS.".$iCount ='$oApp->name' ".cRenderQS::APP_ID_QS.".$iCount='$oApp->id' ";
		}
		
		cDebug::leave();
		return $sApps_fragment;
	}

	//******************************************************************************************
	//******************************************************************************************
	// this is a slightly different type of menu - so initialises differently
	public static function top_menu(){
		global $home;

		cDebug::enter();
		
		$oCred = cRenderObjs::get_AD_credentials();
		if ($oCred == null) return;

		?>
			<script>
				function init_top_menu(){
					$oDiv = $("#<?=cRenderHtml::NAVIGATION_ID?>");
					$oDiv.attr({
						home:"<?=$home?>",
						controller:"<?=$oCred->host?>",
						restricted:"<?=$oCred->restricted_login?>",
					})
										
					//now trigger the rendering of the top menu
					cTopMenu.render( $oDiv);
				}
				
				$(init_top_menu);
			</script>
		<?php
		cDebug::leave();
	}
	
	//******************************************************************************************
	//******************************************************************************************
	public static function show_license_menu(){
		$aProps = array(
			"type" => "admenus",
			"menu" => "licenses",
		);
		cRenderHtml::write_div("ID_mnu_lic", $aProps, "please wait" );
	}

	//******************************************************************************************
	//******************************************************************************************
	public static function show_tier_functions(cAdTier $poTier = null, $psNode=null){
		global $home;

		cDebug::enter();
		$oCred = cRenderObjs::get_AD_credentials();
	
		if ($oCred->restricted_login) {
			cRender::button($poTier->name,null);
			cDebug::leave();
			return;
		}
		if ($poTier == null){
			$poTier = cRenderObjs::get_current_tier();
		}

		$aProps = array(
			"type" => "admenus",
			"menu" => "tierfunctions",
		);
		$aProps[cRenderQS::HOME_QS]=$home;
		$aProps[cRenderQS::TIER_QS] = $poTier->name;
		$aProps[cRenderQS::TIER_ID_QS] = $poTier->id; 
		$aProps[cRenderQS::NODE_QS] = $psNode;

		cRenderHtml::write_div("ID_mnu_tierfn".$poTier->name, $aProps, "please wait" );
		cDebug::leave();
	}

	//******************************************************************************************
	public static function show_tiers_custom_menu(cADApp $poApp, $psCaption, $psUrl){
		cDebug::enter();
		global $home;

		$aTiers = $poApp->GET_Tiers();

		$aProps = array(
			"type" => "admenus",
			"menu" => "tiersCustom",
		);
		$aProps[cRenderQs::TITLE_QS] = $psCaption;
		$aProps[cRenderQs::BASE_URL_QS] = $psUrl;
		
		$aProps[cRenderQs::HOME_QS] = $home;
		$aProps[cRenderQs::APP_QS] = $poApp->name;
		$aProps[cRenderQs::APP_ID_QS] = $poApp->id;

		$iCount =0;
		foreach ($aTiers as $oTier){
			$iCount++;
			$aProps[cRenderQs::TIER_QS.$iCount] = $oTier->name;
			$aProps[cRenderQs::TIER_ID_QS.$iCount] = $oTier->id;
		}
		cRenderHtml::write_div("ID_mnu_tiercustfn".$poApp->name, $aProps, "please wait" );
		cDebug::leave();
	}
	
	//******************************************************************************************
	public static function show_tier_infra_menu(cADTier $poTier, $psNode=null){
		global $home;

		$aProps = array(
			"type" => "admenus",
			"menu" => "tierinframenu",
		);
		$aProps[cRenderQs::HOME_QS] = $home;
		$aProps[cRenderQs::APP_QS] = $poTier->app->name;
		$aProps[cRenderQs::APP_ID_QS] = $poTier->app->id;
		$aProps[cRenderQs::TIER_QS] = $poTier->name;
		$aProps[cRenderQs::TIER_ID_QS] = $poTier->id;
		$aProps[cRenderQs::NODE_QS] = $psNode;
		$iNode=1;
		$aNodes = $poTier->GET_Nodes();	
		foreach ($aNodes as $oNode){
			$aProps[cRenderQs::NODE_QS.".$iNode"] = $oNode->name;
			$iNode++;
		}
		
		cRenderHtml::write_div("ID_mnu_tierinfra".$poTier->name, $aProps, "please wait" );
	}

	//******************************************************************************************
	public static function show_tier_menu(string $psCaption, $psURLFragment=null, $psExtraQS=""){
		global $home;
		
		cDebug::enter();
		$oCred = cRenderObjs::get_AD_credentials();
		if ($oCred->restricted_login){
			cDebug::leave();
			return;
		}

		$oApp = cRenderObjs::get_current_app();
		$sFragment = self::pr__get_tiers_fragment($oApp);
		if ($psURLFragment == null)
			$psURLFragment=cCommon::filename();
		
		//TBD change to a DIV - widget can replace with a select menu
		?>
			<div 
				type="admenus" menu="tierchangemenu" 
				<?=cRenderQs::HOME_QS?>="<?=$home?>"
				caption="<?=$psCaption?>" url="<?=$psURLFragment?>" 
				extra="<?=$psExtraQS?>" <?=$sFragment?>>
				please wait
			</div>
		<?php
		cDebug::leave();
	}	

	//******************************************************************************************
	public static function show_all_node_infra_menu( cADTier $poTier, $poInfraType){
		global $home;

		$aProps = array(
			"type" => "admenus",
			"menu" => "allnodeinframenu",
		);
		$aProps[cRenderQs::HOME_QS] = $home;
		$aProps[cRenderQs::APP_QS] = $poTier->app->name;
		$aProps[cRenderQs::APP_ID_QS] = $poTier->app->id;
		$aProps[cRenderQs::TIER_QS] = $poTier->name;
		$aProps[cRenderQs::TIER_ID_QS] = $poTier->id;
		$aProps[cRenderQs::INFRA_METRIC_TYPE_QS] = $poInfraType->type;
		$aProps[cRenderQs::INFRA_METRIC_NAME_QS] = $poInfraType->short;

		$aMetrics = cADInfraMetric::getInfrastructureMetricDetails($poTier);
		$iCount = 1;
		foreach ( $aMetrics as $oType){
			$aProps[cRenderQs::INFRA_METRIC_TYPE_QS.$iCount] = $oType->type;
			$aProps[cRenderQs::INFRA_METRIC_NAME_QS.$iCount] = $oType->metric->short;
			$iCount ++;
		}
		cRenderHtml::write_div("ID_mnu_allnodeinfra".$poTier->name, $aProps, "please wait" );
	}

	//******************************************************************************************
	private static function pr__get_tiers_fragment( cADApp $poApp){

		cDebug::enter();
		try{
			$aTiers = $poApp->GET_Tiers();
		}
		catch (Exception $e)
		{
			cCommon::errorbox("Oops unable to get tier data from controller");
			cDebug::leave();
			exit;
		}
		
		uasort($aTiers,"sort_by_app_name" );
		$iCount=0;
		$sFragment = "";
		foreach ($aTiers as $oTier){
			$iCount++;
			$sFragment .= " tname.$iCount=\"".$oTier->name."\" tid.$iCount=\"$oTier->id\" ";		
		}
		
		cDebug::leave();
		return $sFragment;
	}

}
?>
