<?php
/**************************************************************************
Copyright (C) Chicken Katsu 2018

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/
class cSecret{
	const ENABLE_GOOGLE_ANALYTICS = false;
	static $GOOGLE_TAG_ID;
	
	const LICENSED_TO = "Licensed for non commercial use only";
	const LICENSE_COMMENT = "** contact cluck@chickenkatsu.co.uk for commercial licenses";
	const LICENSE_ENCRYPTION_KEY = "";
	
	const ENABLE_NR_EUM = false;
	static $NR_DEV = "";
	static $NR_PROD = "";
	
	const ENABLE_APPD_EUM = false;
	static $APPD_DEV ;
	static $APPD_PROD ;
}

if (cSecret::ENABLE_APPD_EUM){
	//if using new Appd EUM monitoring provide the correct values below
	//TBD - create a demo account
}

if (cSecret::ENABLE_NR_EUM){
	//if using new relic EUM monitoring provide the correct values below
	cSecret::$NR_DEV = new cNewRelicEUMAccount("AccountID","AgentID","LicenseKey","ApplicationID");
	cSecret::$NR_PROD = new cNewRelicEUMAccount("AccountID","AgentID","LicenseKey","ApplicationID");
}

if (cSecret::ENABLE_GOOGLE_ANALYTICS){
	//if using new Google analytics provide the correct values below
	cSecret::$GOOGLE_TAG_ID = "UA-XXXXXXX";
}
?>