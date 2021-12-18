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
	const GOOGLE_TAG_ID = "UA-51550338-2";
	
	const LICENSED_TO = "Chicken Katsu";
	const LICENSE_COMMENT = "** For Demonstration Purposes only **";
	const ENCRYPTION_KEY = "woeic87u5yt oisaljrhdfmoliasvjhd";
	
	const LINKEDIN_CLIENTID = "78y5i1whb7s9ve";
	const LINKEDIN_SECRET = "A3qzKko0pAIbcOHA";
	
	const ENABLE_NR_MONITORING = true;
	static $NR_DEV ;
	static $NR_PROD ;
}
if (cSecret::ENABLE_NR_MONITORING){
	cSecret::$NR_DEV = new cNewRelicEUMAccount("3356962","322543097","NRJS-f66e15d6a38a9fd5f21", "322543097" );
	cSecret::$NR_PROD = new cNewRelicEUMAccount("3356962","322543097","NRJS-f66e15d6a38a9fd5f21", "322543097" );
}
?>