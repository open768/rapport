<?php
require_once "$home/inc/root.php";
cAppGlobals::init($home);

//####################################################################
require_once(cAppGlobals::$ckPhpInc."/debug.php");
cDebug::check_GET_or_POST();

//####################################################################
require_once(cAppGlobals::$ckPhpInc."/session.php");
require_once(cAppGlobals::$ckPhpInc."/common.php");
require_once(cAppGlobals::$ckPhpInc."/http.php");
require_once(cAppGlobals::$ckPhpInc."/header.php");
require_once(cAppGlobals::$ckPhpInc."/rendercards.php");
require_once(cAppGlobals::$ckPhpInc."/renderw3.php");
require_once(cAppGlobals::$ckPhpInc."/google.php");
require_once(cAppGlobals::$ckPhpInc."/newrelic.php");
require_once(cAppGlobals::$ckPhpInc."/colour.php");

//####################################################################
require_once(cAppGlobals::$root."/inc/render.php");
require_once(cAppGlobals::$root."/inc/metrics.php");
require_once(cAppGlobals::$root."/inc/secret.php");

//####################################################################
require_once(cAppGlobals::$ADlib."/AD.php");

//####################################################################
//no cacheing allowed
//header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
//header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
date_default_timezone_set('Europe/London');
ob_end_flush(); //no buffering allowed, content is written to screen as soon as available
?>