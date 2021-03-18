<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

//see 
require_once("$phpinc/ckinc/header.php");
require_once("$phpinc/ckinc/hash.php");
require_once("$phpinc/ckinc/http.php");
require_once("$appdlib/common.php");
require_once("$phpinc/ckinc/debug.php");
require_once("$phpinc/ckinc/common.php");
require_once("$phpinc/php-openssl-crypt/cryptor.php");

//#################################################################
//# 
//#################################################################
//##################################################################
class cLogin{
	const KEY_HOST = "h";
	const KEY_ACCOUNT = "a";
	const KEY_USERNAME = "u";
	const KEY_PASSWORD = "p";
	const KEY_HTTPS = "ss";
	const KEY_REFERRER = "r";
	const KEY_SUBMIT = "s";
}

//#################################################################
//# 
//#################################################################
class cAppDynAuditAccount{
	public $host = null;
	public $account = null;
	public $user = null;
	public $timestamp = null;
	public $IP = null;
}

class cAppDynAudit{
	const ACCOUNTS_KEY = "cAppDynAudit.accounts.key";
	const ACCOUNT_BASE_KEY = "cAppDynAudit.account.basekey.";
	const MAX_ENTRIES_PER_USER = 100;
	
	//**************************************************************************************
	public static function audit ($poCredentials){
		cDebug::enter();
		if (! ($poCredentials instanceof cAppDynCredentials )) cDebug::error("cAppDynCredentials not provided");
			
		$oAccount = new cAppDynAuditAccount;
		$oAccount->host = $poCredentials->host;
		$oAccount->account = $poCredentials->account;
		$oAccount->user = $poCredentials->get_username();
		$oAccount->timstamp = $sDate = date('d-m-Y H:i:s');
		
		//if this this account hasnt been audited before add to the Audited customers table
		cDebug::write("checking known accounts");
		$sAccountHash = self::pr__get_account_key($oAccount);
		if ( ! cHash::exists($sAccountHash) ){
			cDebug::write("adding to Audited Customers table");
			self::pr__add_audited_account($oAccount);
		}
		
		//if this username hasnt been audited before, add to the known accounts for the account
		cDebug::write("checking known users of account");
		$sUserHash = self::pr__get_user_key($oAccount);
		if ( ! cHash::exists($sUserHash) ){
			cDebug::write("adding username to known Customers table");
			self::pr__add_known_user($oAccount);
		}
		
		//finally add the user audit entry
		cDebug::write("writing audit entry");
		self::pr_add_user_entry($oAccount);
		cDebug::leave();
	}
	
	
	//**************************************************************************************
	private static function pr__get_user_key($poAccount){
		return self::pr__get_account_key($poAccount).$poAccount->user;
	}
	
	//**************************************************************************************
	private static function pr_add_user_entry($poAccount){
		$sHash = self::pr__get_user_key($poAccount);
		
		$aAuditLines = cHash::get($sHash);
		if ($aAuditLines == null) $aAuditLines = [];

		//check size of array - mustnt grow too large
		$iCount = count($aAuditLines);
		while ($iCount >= self::MAX_ENTRIES_PER_USER){
			array_shift($aAuditLines);
			$iCount = count($aAuditLines);
		}
		
		$aAuditLines[] = $poAccount;
		cHash::put($sHash, $aAuditLines, true);
	}
	
	//**************************************************************************************
	public static function get_user_entries($poAccount){
		return cHash::get(self::pr__get_user_key($poAccount));
	}

	//**************************************************************************************
	private static function pr__get_account_key($poAccount){
		return self::ACCOUNT_BASE_KEY.$poAccount->host.$poAccount->account;
	}
	
	//**************************************************************************************
	private static function pr__add_known_user($poAccount){
		$sHash = self::pr__get_account_key($poAccount);
		$aUsers = cHash::get($sHash);
		if ($aUsers == null) $aUsers = [];
		$aUsers[] = $poAccount;
		cHash::put($sHash, $aUsers, true);
	}
	
	//**************************************************************************************
	public static function get_known_users($poAccount){
		return cHash::get(self::pr__get_account_key($poAccount));
	}
	
	//**************************************************************************************
	private static function pr__add_audited_account($poAccount){
		$aAccounts = self::get_audited_accounts();
		if ($aAccounts == null) $aAccounts = [];
		
		$aAccounts[] = $poAccount;
		cHash::put( self::ACCOUNTS_KEY, $aAccounts, true);
	}
	//**************************************************************************************
	public static function get_audited_accounts(){
		return cHash::get(self::ACCOUNTS_KEY);
	}
	
}

//#################################################################
//# encrypt with a key that is randomly generated - 
//# so that even the person hosting cant easily find the details
//#################################################################
class cAppDynCrypt{
	public static $credentials = null;
	
	private static function pr__check_credentials(){
		if (self::$credentials == null) cDebug::error("account credentials missing");
		if (self::$credentials->host == null) cDebug::error("host missing");
		if (self::$credentials->account == null) cDebug::error("account missing");
	}
	
	private static function get_key(){
		self::pr__check_credentials();
		$sHash = "cAppDynCrypt.key.".(self::$credentials->host).(self::$credentials->account);
		$sKey = "##key is not set##";
		if (cHash::exists($sHash)){
			$sKey = cHash::get($sHash);
		}else{
			$sKey = uniqid("",true);
			cHash::put($sHash, $sKey);
		}
		
		//return cAppDSecret::SESSION_ENCRYPTION_KEY;
		return $sKey;
	}
	
	public static function encrypt($psWhat){
		self::pr__check_credentials();
		return Cryptor::Encrypt($psWhat, self::get_key());
	}
	public static function decrypt($psWhat){
		self::pr__check_credentials();
		return Cryptor::Decrypt($psWhat, self::get_key());		
	}
}

//#################################################################
//# 
//#################################################################
class cAppDynCredentials{
	const HOST_KEY = "apple";
	const ACCOUNT_KEY = "pear";
	const USERNAME_KEY = "orange";
	const PASSWORD_KEY = "lemon";
	const USE_HTTPS_KEY = "quince";
	const PROXY_KEY = "melon";
	const RESTRICTED_LOGIN_KEY = "basil";
	const PROXY_PORT_KEY = "grape";
	const PROXY_CRED_KEY = "diet";
	const LOGGEDIN_KEY = "log";
	const JSESSION_KEY = "boar";
	const CSRF_TOKEN_KEY = "spike";
	
	const DEMO_USER = "demo";
	const DEMO_PASS = "d3m0";
	const DEMO_ACCOUNT = "demo";
	
	public $account;
	public $host;
	public $encrypted_username;
	public $jsessionid;
	public $csrftoken;
	public $encrypted_password;
	public $use_https;
	public $restricted_login = null;
	private $mbLogged_in = false;
	public $encryption_key = "no encryption key set";
	
	//**************************************************************************************
	function check(){
		if(!$this->account) cDebug::error("Couldnt get account from session");
		if(!$this->encrypted_username ) cDebug::error("Couldnt get username from session");
		if(!$this->encrypted_password) cDebug::error("Couldnt get password from session");

		if (!$this->is_demo()){
			if(!$this->host) cDebug::error("Couldnt get host from session");
		}
	}
	
	//**************************************************************************************
	function load_from_header(){
		cDebug::enter();
		$username = null;
		$password = null;
		
		$this->host = cHeader::get(cLogin::KEY_HOST);
		$this->account  = cHeader::get(cLogin::KEY_ACCOUNT);
		cAppDynCrypt::$credentials = $this;
		
		$username  = cHeader::get(cLogin::KEY_USERNAME);
		if ($username)	$this->encrypted_username = cAppDynCrypt::encrypt($username);
		$password  = cHeader::get(cLogin::KEY_PASSWORD);
		if ($password)	$this->encrypted_password = cAppDynCrypt::encrypt($password);
		
		$sUse_https = cHeader::get(cLogin::KEY_HTTPS);
		
		$this->use_https = ($sUse_https=="yes");		
		
		$this->save();		//populate the session
		cDebug::leave();
	}
	
	//**************************************************************************************
	//this performs the login
	public function save(){
		cDebug::enter();
		cDebug::write("saving TO SESSION");
		
		$_SESSION[self::HOST_KEY]  = $this->host;
		$_SESSION[self::ACCOUNT_KEY]  = $this->account;
		$_SESSION[self::USERNAME_KEY]  = $this->encrypted_username;
		$_SESSION[self::PASSWORD_KEY]  = $this->encrypted_password;
		$_SESSION[self::USE_HTTPS_KEY]  = $this->use_https;
		$_SESSION[self::RESTRICTED_LOGIN_KEY]  = $this->restricted_login;
		
		//try to login - if it worked you are logged in
		cAppDynCore::login();
		cDebug::write("logged in");
		cAppDynAudit::audit($this); //audit on success
		
		$_SESSION[self::LOGGEDIN_KEY] = true;
		$this->mbLogged_in = true;
		cDebug::leave();
	}
	
	//**************************************************************************************
	public function save_restui_auth( $poHttp){
		$aHeaders = $poHttp->response_headers;
		
		foreach ($poHttp->response_headers as $oTuple)
			if ($oTuple->key === "Set-Cookie"){
				$aSplit = preg_split("/=/",$oTuple->value);
				if (count($aSplit) == 2)
					if ($aSplit[0] === "JSESSIONID"){
						$this->jsessionid = $aSplit[1];
						$_SESSION[self::JSESSION_KEY] = $aSplit[1];
					}elseif($aSplit[0] === "X-CSRF-TOKEN"){
						$this->csrftoken = $aSplit[1];
						$_SESSION[self::CSRF_TOKEN_KEY] = $aSplit[1];
					}
			}
	}

	//**************************************************************************************
	public function logged_in(){
		if (!$this->mbLogged_in)
			cDebug::error("not logged in");
			
		if ($this->restricted_login)
			if (!cHttp::page_matches($this->restricted_login))
				cDebug::error("restricted login");
		return true;
	}
	
	//**************************************************************************************
	public function get_username(){
		cAppDynCrypt::$credentials = $this;
		return cAppDynCrypt::decrypt($this->encrypted_username);
	}
	
	//**************************************************************************************
	public function get_password(){
		cAppDynCrypt::$credentials = $this;
		return cAppDynCrypt::decrypt($this->encrypted_password);
	}
	
	//**************************************************************************************
	public function encode(){
		return urlencode(urlencode($this->get_username())."@".$this->account);
	}

	//**************************************************************************************
	function __construct() {
		//retrieves stored values from the session $_SESSION
		$this->account = cCommon::get_session(self::ACCOUNT_KEY);
		$this->encrypted_username = cCommon::get_session(self::USERNAME_KEY);
		$this->encrypted_password = cCommon::get_session(self::PASSWORD_KEY); 

		$this->host = cCommon::get_session(self::HOST_KEY);
		$this->use_https = cCommon::get_session(self::USE_HTTPS_KEY);

		$this->restricted_login = cCommon::get_session(self::RESTRICTED_LOGIN_KEY);
		$this->mbLogged_in = cCommon::get_session(self::LOGGEDIN_KEY);  
		$this->jsessionid = cCommon::get_session(self::JSESSION_KEY);  
		$this->csrftoken = cCommon::get_session(self::CSRF_TOKEN_KEY);  
	}
	
	//**************************************************************************************
	public function is_demo(){
		cDebug::enter();
		if ($this->account == self::DEMO_ACCOUNT){
			if (($this->get_username() == self::DEMO_USER) && ( $this->get_password() == self::DEMO_PASS)){
				cDebug::write("this is a demo login");
				return true;
			}else
				cDebug::error("wrong demo login details");
		}
		cDebug::write("this is not a demo login");
		cDebug::leave();
		return false;
	}
	
	//**************************************************************************************
	//* STATICS
	//**************************************************************************************	
	public static function clear_session(){
		@session_destroy ();
		session_start ();
	}
	
	//**************************************************************************************
	public static function get_login_token(){
		cDebug::ENTER();
		
		//------------- check login credentials --------------------------
		$oCred = new cAppDynCredentials;
		if (!$oCred->logged_in()) cDebug::error("must be logged in");
		if ($oCred->restricted_login) cDebug::error("token not available in restricted login");
		
		//------------- generate the token --------------------------------
		$sKey = cCommon::my_IP_address().$oCred->host.$oCred->account.$oCred->get_username();
		$sHash = cHash::hash($sKey);
		cDebug::write("Key is $sKey, hash is $sHash");
		cHash::pr__put_obj($sHash, $oCred, true );
			
		return $sHash;
	}
	
	//**************************************************************************************
	public static function login_with_token($psToken ){
		$oCred = cHash::pr__get_obj($psToken);
		if ($oCred == null) cDebug::error("token not found");
		if (get_class($oCred) !== "cAppDynCredentials") cDebug::error("unexpected class");

		//perform the login
		$oCred->save();
	}
	

}
?>