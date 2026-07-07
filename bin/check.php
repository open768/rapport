<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013 -2024

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
 **************************************************************************/


//*******************************************************************************
$home = "..";
$sCommonFile = "$home/php/fragments/app-common.php";

if (!is_file($sCommonFile)) {
    print "common file not found $sCommonFile - edit \$home variable in " . __FILE__;
    exit(1);
}

//-------------------------------------------------------------------
$bErr = false;
print "loading common file \n";
try {
    require_once($sCommonFile);
} catch (Exception $e) {
    $sMsg = $e->getMessage();
    cDebug::on(true);   //turn on debugging since there was an error
    cDebug::write("Oops there was an Error: $sMsg");
    $bErr = true;
}
if (!$bErr)
    cDebug::write("no immediate problems with $sCommonFile");

if (!cDebug::is_debugging()) cDebug::on(true);

//-------------------------------------------------------------------
$sIniFile = php_ini_loaded_file();
//check for extensions
if (!extension_loaded("curl"))
    cDebug::write("curl extension is not loaded - check " . $sIniFile);
else
    cDebug::write("curl extension is loaded");

if (!extension_loaded("sqlite3"))
    cDebug::write("sqlite3 extension is not loaded\n\t- check " . $sIniFile);
else
    cDebug::write("sqlite3 extension is loaded \n");

//-------------------------------------------------------------------
//check for existance of phpinc
if (is_dir(cAppGlobals::$phpInc))
    cDebug::write("\$phpInc found " . cAppGlobals::$phpInc);
else {
    cDebug::write(print "couldnt find \$phpInc: " . cAppGlobals::$phpInc);
    //lets find it
}

//-------------------------------------------------------------------
//check for existance of jsinc no longer needed as PHP doesnt access it
